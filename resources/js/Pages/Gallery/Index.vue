<script setup>
import { Head, Link, useForm, router, usePage } from '@inertiajs/vue3';
import { ref, onMounted, computed, nextTick } from 'vue';
import ProtectedImage from '@/Components/ProtectedImage.vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import axios from 'axios';
import { useToast } from '@/Composables/useToast';
import {
    MagnifyingGlassIcon,
    AdjustmentsHorizontalIcon,
    ShoppingCartIcon,
    XMarkIcon,
    SparklesIcon,
    FaceSmileIcon,
    HashtagIcon,
    CheckIcon
} from '@heroicons/vue/24/outline';
import * as faceapi from 'face-api.js';

const randomHeroImage = computed(() => {
    if (allPhotos.value && allPhotos.value.length > 0) {
        const randomIndex = Math.floor(Math.random() * allPhotos.value.length);
        return allPhotos.value[randomIndex].thumbnail_url;
    }
    return '/banners/portada.jpg';
});
const { success, error } = useToast();
const page = usePage();

const isAuthenticated = computed(() => page.props.auth.user !== null);

const addingToCartIds = ref([]);
const addToCart = async (photo) => {
    if (!isAuthenticated.value) {
        window.location.href = route('login');
        return;
    }

    if (addingToCartIds.value.includes(photo.id)) return;

    addingToCartIds.value.push(photo.id);

    try {
        const response = await axios.post(route('cart.add', photo.id));

        if (response.data.success) {
            success('Fotografía agregada al carrito');
            window.dispatchEvent(new Event('cart-updated'));
        } else {
            error('La fotografía ya está en el carrito');
        }
    } catch (err) {
        console.error('Error agregando al carrito:', err);
        error('Error de conexión');
    } finally {
        addingToCartIds.value = addingToCartIds.value.filter(id => id !== photo.id);
    }
};


const props = defineProps({
    photos: Object,
    events: Array,
    regions: Array,
    filters: Object,
});

const allPhotos = ref(props.photos.data);
const nextUrl = ref(props.photos.next_page_url);
const loadingMore = ref(false);

const showingFaceResults = ref(false);
const faceSearchResults = ref(null);
const isLoadingModels = ref(true);
const isSearching = ref(false);
const selectedFile = ref(null);
const previewUrl = ref(null);
const errorMessage = ref('');
const progressMessage = ref('CARGANDO MODELOS IA...');

const showingBibResults = ref(false);
const bibSearchResults = ref(null);
const isSearchingBib = ref(false);
const bibNumber = ref('');
const bibErrorMessage = ref('');

const gridKey = ref(Date.now());


const initGlitch = () => {
    const glitchContainers = document.querySelectorAll('.glitch-image-container');
    glitchContainers.forEach(container => {
        const imgUrl = container.getAttribute('data-img');
        if (!imgUrl) return;

        const height = container.clientHeight || 220;
        const width = container.clientWidth;
        let i = 0;
        let html = '';

        const random = (min, max) => Math.random() * (max - min) + min;

        while (i < height) {
            const stripHeight = Math.floor(Math.random() * 6) + 2;
            const actualHeight = (i + stripHeight < height) ? stripHeight : (height - i);
            const gx1 = random(-25, 25).toFixed(1) + 'px';
            const gx2 = random(-25, 25).toFixed(1) + 'px';
            const gh1 = random(-30, 30).toFixed(1) + 'deg';
            const gh2 = random(-30, 30).toFixed(1) + 'deg';
            const duration = random(3, 8).toFixed(1) + 's';
            const delay = random(0, 3).toFixed(1) + 's';

            html += `
                <div class="glitch-strip" 
                     style="
                        height: ${actualHeight}px; 
                        background-image: url('${imgUrl}');
                        background-size: ${width}px ${height}px; 
                        background-position: 0px -${i}px;
                        --glitch-x-1: ${gx1};
                        --glitch-x-2: ${gx2};
                        --glitch-hue-1: ${gh1};
                        --glitch-hue-2: ${gh2};
                        animation-duration: ${duration};
                        animation-delay: -${delay};
                     ">
                </div>`;
            i += actualHeight;
        }
        container.innerHTML = html;
    });
};

onMounted(async () => {
    initGlitch();

    try {
        const MODEL_URL = '/models';
        await faceapi.nets.ssdMobilenetv1.loadFromUri(MODEL_URL);
        await faceapi.nets.faceLandmark68Net.loadFromUri(MODEL_URL);
        await faceapi.nets.faceRecognitionNet.loadFromUri(MODEL_URL);
        isLoadingModels.value = false;
        progressMessage.value = '';
    } catch (error) {
        console.error('Error cargando modelos:', error);
        errorMessage.value = 'ERROR DE CARGA DE MODELOS. RECARGA LA PÁGINA.';
        isLoadingModels.value = false;
    }
});

const handleFileSelect = (event) => {
    const file = event.target.files[0];
    if (!file) return;
    if (!file.type.startsWith('image/')) { errorMessage.value = 'ARCHIVO INVÁLIDO. SOLO IMÁGENES.'; return; }
    if (file.size > 10 * 1024 * 1024) { errorMessage.value = 'EXCESO DE PESO. LÍMITE: 10MB.'; return; }
    selectedFile.value = file;
    errorMessage.value = '';
    const reader = new FileReader();
    reader.onload = (e) => { previewUrl.value = e.target.result; };
    reader.readAsDataURL(file);
};

const performFaceSearch = async () => {
    if (!selectedFile.value) { errorMessage.value = 'SELECCIONA REFERENCIA.'; return; }
    isSearching.value = true;
    errorMessage.value = '';
    progressMessage.value = 'ESCANEO FACIAL...';
    try {
        const img = await faceapi.bufferToImage(selectedFile.value);
        progressMessage.value = 'EXTRAYENDO VECTORES...';
        const detection = await faceapi.detectSingleFace(img).withFaceLandmarks().withFaceDescriptor();
        if (!detection) { errorMessage.value = 'ROSTRO NO DETECTADO. ABORTANDO.'; isSearching.value = false; progressMessage.value = ''; return; }
        const descriptor = Array.from(detection.descriptor);
        progressMessage.value = 'BÚSQUEDA EN BASE DE DATOS...';
        const response = await axios.post(route('gallery.face-search'), { face_descriptor: descriptor, threshold: 0.6 });
        if (response.data.success) {
            clearFaceSearch();
            bibSearchResults.value = response.data;
            showingBibResults.value = true;
            allPhotos.value = [...response.data.results];
            gridKey.value = Date.now();
            nextUrl.value = null;
            showFilters.value = false;
        }
    } catch (error) {
        errorMessage.value = error.response?.data?.message || 'ERROR DEL SERVIDOR. REINTENTAR.';
    } finally {
        isSearching.value = false;
        progressMessage.value = '';
    }
};

const clearFaceSearch = () => {
    showingFaceResults.value = false;
    faceSearchResults.value = null;
    selectedFile.value = null;
    previewUrl.value = null;
    errorMessage.value = '';
    allPhotos.value = [...props.photos.data];
    nextUrl.value = props.photos.next_page_url;
    gridKey.value = Date.now();
};

const performBibSearch = async () => {
    if (!bibNumber.value.trim()) { bibErrorMessage.value = 'DORSAL REQUERIDO.'; return; }
    isSearchingBib.value = true;
    bibErrorMessage.value = '';
    try {
        const response = await axios.post(route('gallery.bib-search'), { bib_number: bibNumber.value.trim() });
        if (response.data.success) {
            clearFaceSearch();
            bibSearchResults.value = response.data;
            showingBibResults.value = true;
            allPhotos.value = [];
            await nextTick();
            allPhotos.value = [...response.data.results];
            gridKey.value = Date.now();
            nextUrl.value = null;
            showFilters.value = false;
        }
    } catch (error) {
        bibErrorMessage.value = error.response?.data?.message || 'ERROR DEL SERVIDOR. REINTENTAR.';
    } finally {
        isSearchingBib.value = false;
    }
};

const clearBibSearch = () => {
    showingBibResults.value = false;
    bibSearchResults.value = null;
    bibNumber.value = '';
    bibErrorMessage.value = '';
    if (!showingFaceResults.value) {
        allPhotos.value = [...props.photos.data];
        nextUrl.value = props.photos.next_page_url;
        gridKey.value = Date.now();
    }
};

const loadMore = () => {
    if (!nextUrl.value || loadingMore.value) return;
    loadingMore.value = true;
    router.visit(nextUrl.value, {
        preserveState: true,
        preserveScroll: true,
        only: ['photos'],
        onSuccess: (page) => {
            allPhotos.value = [...allPhotos.value, ...page.props.photos.data];
            nextUrl.value = page.props.photos.next_page_url;
            loadingMore.value = false;
        },
        onError: () => { loadingMore.value = false; }
    });
};

const filterForm = useForm({
    search: props.filters.search || '',
    region: props.filters.region || 'all',
    event: props.filters.event || '',
    sort: props.filters.sort || 'recent',
});

const showFilters = ref(false);

const applyFilters = () => {
    if (showingFaceResults.value) clearFaceSearch();
    if (showingBibResults.value) clearBibSearch();
    filterForm.get(route('gallery.index'), {
        preserveState: true,
        preserveScroll: true,
        only: ['photos'],
        onSuccess: (page) => {
            allPhotos.value = page.props.photos.data;
            nextUrl.value = page.props.photos.next_page_url;
        }
    });
};

const clearFilters = () => {
    filterForm.reset();
    filterForm.region = 'all';
    filterForm.event = '';
    filterForm.search = '';
    if (showingFaceResults.value) clearFaceSearch();
    if (showingBibResults.value) clearBibSearch();
    applyFilters();
};

const changeSort = (sortValue) => {
    filterForm.sort = sortValue;
    applyFilters();
};

const sortOptions = [
    { value: 'recent', label: 'Recientes' },
    { value: 'popular', label: 'Populares' },
    { value: 'price_low', label: 'Menor precio' },
    { value: 'price_high', label: 'Mayor precio' },
];

const handleImageError = (e) => {
    e.target.style.display = 'none';
    const parent = e.target.parentElement;
    if (parent && !parent.querySelector('.placeholder-img')) {
        const ph = document.createElement('div');
        ph.className = 'placeholder-img w-full h-full min-h-[200px] flex items-center justify-center bg-gray-900 border border-red-600/30';
        ph.innerHTML = `<span class="font-mono text-[10px] text-red-600 uppercase tracking-widest">IMAGEN_NO_DISPONIBLE</span>`;
        parent.appendChild(ph);
    }
};

const totalResults = () => {
    if (showingFaceResults.value) return faceSearchResults.value.count;
    if (showingBibResults.value) return bibSearchResults.value.count;
    return props.photos.total;
};
</script>
<template>

    <Head title="Archivos — F33.CLICK" />

    <AppLayout>

        <div class="pt-32 pb-12 px-4 md:px-8 max-w-7xl mx-auto">
            <div
                class="relative w-full h-[40vh] md:h-[45vh] rounded overflow-hidden shadow-2xl flex flex-col justify-end p-8 md:p-16">

                <div class="absolute inset-0 w-full h-full">
                    <img :src="randomHeroImage"
                        class="w-full h-full object-cover transition-opacity duration-1000 ease-in-out"
                        alt="Fondo catálogo aleatorio" />
                    <div class="absolute inset-0 bg-gradient-to-t from-black/90 via-black/40 to-transparent"></div>
                </div>
                <div class="relative z-10 flex flex-col md:flex-row md:items-end justify-between gap-6">
                    <div>

                        <h1 class="font-flux text-6xl md:text-8xl text-white leading-none tracking-wide">
                            Galería
                        </h1>
                    </div>

                    <div class="text-left md:text-right">
                        <span class="font-flux text-5xl md:text-7xl text-[#E30613] block leading-none drop-shadow-md">
                            {{ totalResults() }}
                        </span>
                        <span class="text-xs font-bold uppercase tracking-widest text-gray-300 mt-2 block">
                            {{ (showingFaceResults || showingBibResults) ? 'Coincidencias encontradas' : 'Fotos totales'
                            }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <div class="min-h-screen bg-[#F2F0EB] text-slate-800 pb-20">
            <div class="max-w-7xl mx-auto px-4 md:px-8">


                <div v-if="showingFaceResults"
                    class="bg-white/80 backdrop-blur-md border border-red-100 rounded p-6 mb-6 flex flex-col md:flex-row items-start md:items-center justify-between gap-4 shadow-[0_8px_30px_rgb(227,6,19,0.08)]">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-full bg-red-50 flex items-center justify-center text-[#E30613]">
                            <SparklesIcon class="w-6 h-6" />
                        </div>
                        <div>
                            <h3 class="font-bold text-lg text-black">Reconocimiento Activo</h3>
                            <p class="text-sm text-gray-500"><strong>{{ faceSearchResults.count }}</strong>
                                coincidencias biométricas localizadas.</p>
                        </div>
                    </div>
                    <button @click="clearFaceSearch"
                        class="px-6 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold text-xs uppercase tracking-wider rounded-full transition-colors">
                        Limpiar Búsqueda
                    </button>
                </div>

                <div v-if="showingBibResults"
                    class="bg-white/80 backdrop-blur-md border border-gray-200 rounded p-6 mb-6 flex flex-col md:flex-row items-start md:items-center justify-between gap-4 shadow-[0_8px_30px_rgb(0,0,0,0.04)]">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-full bg-gray-100 flex items-center justify-center text-black">
                            <HashtagIcon class="w-6 h-6" />
                        </div>
                        <div>
                            <h3 class="font-bold text-lg text-black">Dorsal encontrado: #{{ bibNumber }}</h3>
                            <p class="text-sm text-gray-500"><strong>{{ bibSearchResults.count }}</strong> registros OCR
                                coincidentes.</p>
                        </div>
                    </div>
                    <button @click="clearBibSearch"
                        class="px-6 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold text-xs uppercase tracking-wider rounded-full transition-colors">
                        Limpiar Búsqueda
                    </button>
                </div>


                <div
                    class="bg-white rounded shadow-[0_8px_30px_rgb(0,0,0,0.04)] mb-10 border border-gray-100 relative z-10 p-2 md:p-3">
                    <form @submit.prevent="applyFilters">
                        <div class="flex flex-col md:flex-row gap-2">

                            <div class="flex-1 relative">
                                <MagnifyingGlassIcon
                                    class="absolute left-6 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" />
                                <input v-model="filterForm.search" type="text"
                                    placeholder="Buscar por ID, fotógrafo o evento..."
                                    class="w-full pl-14 pr-6 py-4 bg-gray-50 hover:bg-gray-100 focus:bg-white border border-transparent focus:border-gray-300 rounded text-base focus:ring-4 focus:ring-gray-100 transition-all outline-none font-medium text-slate-700 placeholder-slate-400" />
                            </div>


                            <div class="flex gap-2">
                                <button type="button" @click="showFilters = !showFilters" :class="[
                                    'px-6 py-4 flex items-center gap-2 rounded font-bold text-xs uppercase tracking-wider transition-colors',
                                    showFilters ? 'bg-black text-white' : 'bg-gray-50 text-gray-600 hover:bg-gray-100'
                                ]">
                                    <AdjustmentsHorizontalIcon class="w-5 h-5" />
                                    Filtros
                                    <span v-if="showFilters" class="w-2 h-2 bg-[#E30613] rounded-full ml-1"></span>
                                </button>
                                <button type="submit"
                                    class="px-8 py-4 bg-[#E30613] hover:bg-red-700 text-white rounded font-bold text-xs uppercase tracking-wider transition-colors shadow-lg shadow-red-500/30">
                                    Buscar
                                </button>
                            </div>
                        </div>


                        <transition enter-active-class="transition duration-300 ease-out"
                            enter-from-class="opacity-0 -translate-y-4" enter-to-class="opacity-100 translate-y-0"
                            leave-active-class="transition duration-200 ease-in"
                            leave-from-class="opacity-100 translate-y-0" leave-to-class="opacity-0 -translate-y-4">

                            <div v-show="showFilters" class="mt-4 p-6 md:p-8 bg-gray-50 rounded border border-gray-100">

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                                    <div>
                                        <label
                                            class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2 ml-1">Región</label>
                                        <select v-model="filterForm.region"
                                            class="w-full bg-white border border-gray-200 text-slate-700 px-4 py-3.5 rounded focus:border-gray-300 focus:ring-4 focus:ring-gray-100 appearance-none font-medium outline-none">
                                            <option value="all">Todas las zonas</option>
                                            <option v-for="region in regions" :key="region" :value="region">{{ region }}
                                            </option>
                                        </select>
                                    </div>
                                    <div>
                                        <label
                                            class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2 ml-1">Evento</label>
                                        <select v-model="filterForm.event"
                                            class="w-full bg-white border border-gray-200 text-slate-700 px-4 py-3.5 rounded focus:border-gray-300 focus:ring-4 focus:ring-gray-100 appearance-none font-medium outline-none">
                                            <option value="">Todos los eventos</option>
                                            <option v-for="event in events" :key="event.id" :value="event.id">{{
                                                event.name }}</option>
                                        </select>
                                    </div>
                                </div>


                                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 border-t border-gray-200 pt-8">

                                    <div
                                        class="bg-white p-6 rounded border border-gray-100 shadow-sm relative overflow-hidden">
                                        <div
                                            class="absolute top-0 right-0 w-32 h-32 bg-red-50 rounded-full blur-3xl -mr-10 -mt-10 pointer-events-none">
                                        </div>

                                        <div class="flex items-center gap-3 mb-6 relative z-10">
                                            <div
                                                class="w-8 h-8 rounded-full bg-red-50 flex items-center justify-center">
                                                <SparklesIcon class="w-4 h-4 text-[#E30613]" />
                                            </div>
                                            <h3 class="font-bold text-lg text-black">Búsqueda facial</h3>
                                        </div>

                                        <div v-if="isLoadingModels"
                                            class="py-10 flex flex-col items-center justify-center text-center">
                                            <div
                                                class="w-6 h-6 border-2 border-[#E30613] border-t-transparent rounded-full animate-spin mb-3">
                                            </div>
                                            <span class="text-xs font-bold uppercase tracking-wider text-gray-500">{{
                                                progressMessage }}</span>
                                        </div>

                                        <div v-else class="space-y-4 relative z-10">
                                            <div v-if="!previewUrl"
                                                class="border-2 border-dashed border-gray-200 hover:border-red-300 hover:bg-red-50/50 rounded cursor-pointer py-10 text-center transition-all duration-300"
                                                @click="$refs.faceFileInput.click()">
                                                <FaceSmileIcon class="w-10 h-10 text-gray-400 mx-auto mb-3" />
                                                <p class="text-sm font-bold text-gray-600">Subí una selfie para buscarte
                                                </p>
                                                <p class="text-xs text-gray-400 mt-1">Formatos soportados: JPG, PNG</p>
                                                <input ref="faceFileInput" type="file" accept="image/*" class="hidden"
                                                    @change="handleFileSelect">
                                            </div>

                                            <div v-else
                                                class="relative rounded overflow-hidden shadow-sm border border-gray-100">
                                                <img :src="previewUrl" class="w-full h-48 object-cover" />
                                                <button @click="selectedFile = null; previewUrl = null"
                                                    class="absolute top-3 right-3 bg-white/90 backdrop-blur p-2 rounded-full text-black hover:text-[#E30613] hover:bg-white transition-colors shadow-sm">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                    </svg>
                                                </button>
                                            </div>

                                            <button @click="performFaceSearch" :disabled="isSearching"
                                                class="w-full bg-black text-white font-bold py-3.5 rounded text-xs uppercase tracking-wider hover:bg-gray-800 transition-colors disabled:opacity-50 flex items-center justify-center gap-2">
                                                {{ isSearching ? progressMessage : 'Escanear Galería' }}
                                            </button>
                                            <p v-if="errorMessage"
                                                class="text-[#E30613] text-xs font-bold mt-2 text-center">{{
                                                    errorMessage }}</p>
                                        </div>
                                    </div>


                                    <div class="bg-white p-6 rounded border border-gray-100 shadow-sm">
                                        <div class="flex items-center gap-3 mb-6">
                                            <div
                                                class="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center">
                                                <HashtagIcon class="w-4 h-4 text-black" />
                                            </div>
                                            <h3 class="font-bold text-lg text-black">Búsqueda por dorsal</h3>
                                        </div>

                                        <div class="space-y-4">
                                            <label
                                                class="block text-xs font-bold text-gray-500 uppercase tracking-wider">Número
                                                de corredor</label>
                                            <input v-model="bibNumber" type="text" placeholder="Ej: 529"
                                                class="w-full px-4 py-4 bg-gray-50 border border-transparent focus:border-gray-300 focus:bg-white rounded text-2xl font-bold text-center text-slate-800 placeholder-gray-300 focus:ring-4 focus:ring-gray-100 transition-all outline-none"
                                                @keyup.enter="performBibSearch" />

                                            <button @click="performBibSearch"
                                                :disabled="isSearchingBib || !bibNumber.trim()"
                                                class="w-full border-2 border-black text-black font-bold py-3 rounded text-xs uppercase tracking-wider hover:bg-black hover:text-white transition-colors disabled:opacity-50 disabled:hover:bg-transparent disabled:hover:text-black">
                                                {{ isSearchingBib ? 'Buscando...' : 'Buscar Número' }}
                                            </button>
                                            <p v-if="bibErrorMessage"
                                                class="text-[#E30613] text-xs font-bold mt-2 text-center">{{
                                                    bibErrorMessage }}</p>
                                        </div>
                                    </div>
                                </div>

                                <div class="mt-6 pt-6 border-t border-gray-200 flex justify-end">
                                    <button type="button" @click="clearFilters"
                                        class="text-xs font-bold text-gray-500 hover:text-[#E30613] uppercase tracking-wider transition-colors">
                                        Reiniciar Filtros
                                    </button>
                                </div>
                            </div>
                        </transition>
                    </form>
                </div>


                <div class="flex items-center justify-end mb-6">
                    <div class="flex bg-white rounded-lg shadow-sm border border-gray-200 p-1">
                        <button v-for="option in sortOptions" :key="option.value" @click="changeSort(option.value)"
                            :class="[
                                'px-4 py-1.5 text-xs font-bold uppercase tracking-wider rounded-md transition-colors',
                                filterForm.sort === option.value
                                    ? 'bg-gray-100 text-black shadow-sm'
                                    : 'text-gray-500 hover:text-black'
                            ]">
                            {{ option.label }}
                        </button>
                    </div>
                </div>


                <div v-if="allPhotos.length > 0" :key="gridKey">
                    <div class="columns-2 md:columns-3 lg:columns-4 xl:columns-5 gap-4 space-y-4 masonry-grid">
                        <div v-for="photo in allPhotos" :key="photo.id"
                            @click="router.visit(route('gallery.show', photo.unique_id))" @contextmenu.prevent
                            class="break-inside-avoid block group relative bg-white rounded overflow-hidden shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300 cursor-pointer border border-transparent hover:border-gray-200">

                            <div class="relative w-full h-auto">
                                <ProtectedImage :src="photo.thumbnail_url" :alt="photo.unique_id"
                                    class="w-full h-auto object-cover transition-transform duration-700  pointer-events-none"
                                    loading="lazy" @error="handleImageError" />


                                <div
                                    class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none">
                                </div>


                                <div
                                    class="absolute top-3 left-3 bg-white/90 backdrop-blur px-3 py-1.5 rounded-full text-xs font-bold text-black shadow-sm pointer-events-none">
                                    ${{ photo.price }}
                                </div>

                                <button @click.prevent.stop="addToCart(photo)" title="Añadir al carrito"
                                    class="absolute top-3 right-3 bg-white/90 backdrop-blur p-1.5 rounded-full shadow-sm flex items-center justify-center gap-0 group/cart hover:bg-black hover:text-white transition-all duration-300 pointer-events-auto z-20">

                                    <ShoppingCartIcon
                                        class="w-4 h-4 text-black group-hover/cart:text-white transition-colors shrink-0 m-0.5" />

                                    <span
                                        class="max-w-0 overflow-hidden whitespace-nowrap group-hover/cart:max-w-[100px] text-[10px] font-bold uppercase tracking-wider transition-all duration-300 ease-in-out group-hover/cart:px-1.5 group-hover/cart:mr-1">
                                        Añadir
                                    </span>
                                </button>

                                <div v-if="showingFaceResults && photo.similarity"
                                    class="absolute bottom-3 left-3 bg-[#E30613] text-white px-3 py-1.5 rounded-full font-bold text-[10px] tracking-wider shadow-md pointer-events-none z-10">
                                    Match: {{ Math.round(photo.similarity * 100) }}%
                                </div>
                                <div v-if="showingBibResults && photo.bib_numbers"
                                    class="absolute bottom-3 left-3 bg-black text-white px-3 py-1.5 rounded-full font-bold text-[10px] tracking-wider shadow-md pointer-events-none z-10">
                                    #{{ photo.bib_numbers.join(', ') }}
                                </div>


                                <div
                                    class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-12 h-12 bg-white/30 backdrop-blur-md rounded-full flex items-center justify-center text-white opacity-0 group-hover:opacity-100 transition-all duration-300  pointer-events-none">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </div>


                    <div v-if="nextUrl && !showingFaceResults && !showingBibResults" class="flex justify-center pt-16">
                        <button @click="loadMore" :disabled="loadingMore"
                            class="px-8 py-4 bg-white border border-gray-200 rounded-full text-black hover:bg-gray-50 hover:shadow-md font-bold text-xs uppercase tracking-wider transition-all disabled:opacity-50 flex items-center gap-2">
                            <span v-if="loadingMore"
                                class="w-4 h-4 border-2 border-black border-t-transparent rounded-full animate-spin"></span>
                            {{ loadingMore ? 'Cargando...' : 'Cargar más fotos' }}
                        </button>
                    </div>
                </div>





                <div v-else
                    class="flex flex-col items-center justify-center py-24 px-4 text-center bg-white rounded shadow-sm border border-gray-100">
                    <div class="w-20 h-20 bg-gray-50 rounded-full flex items-center justify-center mb-6">
                        <MagnifyingGlassIcon class="w-8 h-8 text-gray-300" />
                    </div>
                    <h3 class="font-flux text-4xl text-black mb-3">No hay resultados</h3>
                    <p class="text-gray-500 mb-8 max-w-md">No hemos encontrado fotografías que coincidan con tus filtros
                        actuales. Intenta modificar tu búsqueda.</p>
                    <button @click="clearFilters"
                        class="bg-black text-white px-8 py-3.5 rounded-full font-bold text-xs uppercase tracking-wider hover:bg-gray-800 transition-colors">
                        Limpiar Filtros
                    </button>
                </div>

            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
.masonry-grid {
    column-fill: balance;
}


::-webkit-scrollbar {
    width: 8px;
}

::-webkit-scrollbar-track {
    background: #F2F0EB;
}

::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 10px;
}

::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
}
</style>