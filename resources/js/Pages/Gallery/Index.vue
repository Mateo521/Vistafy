<script setup>
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { ref, onMounted, nextTick } from 'vue';
import ProtectedImage from '@/Components/ProtectedImage.vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import {
    MagnifyingGlassIcon,
    AdjustmentsHorizontalIcon,
    XMarkIcon,
    SparklesIcon,
    FaceSmileIcon,
    HashtagIcon,
    CheckIcon
} from '@heroicons/vue/24/outline';
import * as faceapi from 'face-api.js';

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

// Función para inicializar el Glitch en elementos dinámicos
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
    // Inicializar Glitch de cabecera
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

    <Head title="Archivo Vivo — F33.CLICK" />

    <AppLayout>

        <div
            class="relative bg-black pt-32 pb-16 md:pt-40 md:pb-24 border-b-[12px] border-white group  overflow-hidden">
            <div class="glitch-image-container absolute inset-0 w-full h-full overflow-hidden -z-10 opacity-30"
                data-img="/05a8862db26a1bed8ac22cdbf6944145.jpg"></div>

            <div class="max-w-[1500px] mx-auto px-4 md:px-8">
                <div class="flex flex-col md:flex-row md:items-end justify-between gap-8 pointer-events-none">
                    <div>
                        <p class="font-mono text-xs uppercase tracking-[0.45em] text-red-600 mb-4  border-red-600 pl-3">
                            Catálogo F33
                        </p>
                        <h1
                            class="font-black text-[15vw] md:text-[8rem] leading-[0.8] text-white tracking-tighter mix-blend-difference">
                            ARCHIVOS.
                        </h1>
                    </div>

                    <div class="text-right border-t md:border-t-0 border-white/20 pt-4 md:pt-0">
                        <span class="font-black text-5xl md:text-8xl text-red-600 leading-none block mix-blend-screen">
                            {{ totalResults() }}
                        </span>
                        <span class="font-mono text-[10px] uppercase tracking-[0.35em] text-gray-500 block mt-2">
                            {{ (showingFaceResults || showingBibResults) ? 'Coincidencias' : 'Fotografías Totales' }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <div class="min-h-screen bg-black text-white">
            <div class="max-w-[1500px] mx-auto px-4 md:px-8 py-10">

                <div v-if="showingFaceResults"
                    class="bg-red-600 text-black border-[4px] border-white p-6 mb-8 flex flex-col md:flex-row items-start md:items-center justify-between gap-4 font-mono uppercase tracking-widest">
                    <div>
                        <h3 class="font-black text-2xl tracking-tighter mb-1">
                            <SparklesIcon class="inline w-6 h-6 mr-2" /> RECONOCIMIENTO ACTIVO
                        </h3>
                        <p class="text-xs"><strong>{{ faceSearchResults.count }}</strong> COINCIDENCIAS BIOMÉTRICAS
                            LOCALIZADAS</p>
                    </div>
                    <button @click="clearFaceSearch"
                        class="border-2 border-black px-4 py-2 hover:bg-black hover:text-red-600 transition-none font-bold text-[10px]">
                        [ PURGAR RESULTADOS ]
                    </button>
                </div>

                <div v-if="showingBibResults"
                    class="bg-white text-black border-[4px] border-black p-6 mb-8 flex flex-col md:flex-row items-start md:items-center justify-between gap-4 font-mono uppercase tracking-widest">
                    <div>
                        <h3 class="font-black text-2xl tracking-tighter mb-1">
                            <HashtagIcon class="inline w-6 h-6 mr-2" /> DORSAL ENCONTRADO: #{{ bibNumber }}
                        </h3>
                        <p class="text-xs"><strong>{{ bibSearchResults.count }}</strong> REGISTROS OCR COINCIDENTES</p>
                    </div>
                    <button @click="clearBibSearch"
                        class="border-2 border-black px-4 py-2 hover:bg-black hover:text-white transition-none font-bold text-[10px]">
                        [ PURGAR RESULTADOS ]
                    </button>
                </div>

                <div class="bg-black border border-white/20 mb-12">
                    <form @submit.prevent="applyFilters">
                        <div
                            class="flex flex-col md:flex-row gap-0 border-b border-white/20 font-mono text-xs uppercase tracking-widest">
                            <div class="flex-1 relative">
                                <MagnifyingGlassIcon
                                    class="absolute left-5 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-600" />
                                <input v-model="filterForm.search" type="text"
                                    placeholder="Buscar por ID, fotógrafo o evento..."
                                    class="w-full pl-12 pr-5 py-5 bg-transparent text-white placeholder-gray-600 border-0 focus:ring-0 focus:outline-none" />
                            </div>
                            <div class="flex border-t md:border-t-0 border-white/20">
                                <button type="button" @click="showFilters = !showFilters" :class="[
                                    'px-6 py-5 flex items-center gap-2 border-r border-white/20 transition-none whitespace-nowrap hover:bg-white hover:text-black',
                                    showFilters ? 'bg-white text-black' : 'text-gray-400'
                                ]">
                                    <AdjustmentsHorizontalIcon class="w-4 h-4" />
                                    FILTROS
                                    <span v-if="showFilters" class="w-2 h-2 bg-red-600 ml-2"></span>
                                </button>
                                <button type="submit"
                                    class="px-8 py-5 bg-red-600 hover:bg-white text-black font-black transition-none whitespace-nowrap">
                                    EJECUTAR
                                </button>
                            </div>
                        </div>

                        <div v-show="showFilters" class="p-8 border-b border-white/20 bg-gray-950">
                            <div
                                class="grid grid-cols-1 md:grid-cols-2 gap-8 font-mono text-[10px] tracking-widest uppercase">
                                <div>
                                    <label class="block text-red-600 mb-2">/ REGIÓN</label>
                                    <select v-model="filterForm.region"
                                        class="w-full bg-black border border-white/20 text-white px-4 py-3 focus:border-red-600 focus:ring-0 appearance-none rounded-none">
                                        <option value="all">TODAS LAS ZONAS</option>
                                        <option v-for="region in regions" :key="region" :value="region">{{ region }}
                                        </option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-red-600 mb-2">/ EVENTO</label>
                                    <select v-model="filterForm.event"
                                        class="w-full bg-black border border-white/20 text-white px-4 py-3 focus:border-red-600 focus:ring-0 appearance-none rounded-none">
                                        <option value="">TODOS LOS EVENTOS</option>
                                        <option v-for="event in events" :key="event.id" :value="event.id">{{ event.name
                                        }}</option>
                                    </select>
                                </div>
                            </div>

                            <div
                                class="mt-8 pt-8 border-t border-dashed border-white/20 grid grid-cols-1 lg:grid-cols-2 gap-8">
                                <div
                                    class="border border-white/20 p-6 bg-black hover:border-red-600 transition-none group relative">
                                    <div class="flex items-center gap-3 mb-6 pb-4 border-b border-white/10">
                                        <div
                                            class="w-8 h-8 border border-red-600 bg-red-600/10 flex items-center justify-center">
                                            <SparklesIcon class="w-4 h-4 text-red-600" />
                                        </div>
                                        <h3 class="font-black text-xl uppercase tracking-tighter">ANÁLISIS BIOMÉTRICO
                                        </h3>
                                    </div>

                                    <div v-if="isLoadingModels"
                                        class="py-8 text-center text-red-600 font-mono text-[10px] animate-pulse">
                                        {{ progressMessage }}
                                    </div>

                                    <div v-else class="space-y-4">
                                        <div v-if="!previewUrl"
                                            class="border border-dashed border-gray-600 hover:border-white cursor-pointer py-10 text-center transition-none"
                                            @click="$refs.faceFileInput.click()">
                                            <FaceSmileIcon
                                                class="w-8 h-8 text-gray-500 group-hover:text-white mx-auto mb-2" />
                                            <p class="font-mono text-xs text-gray-400">CARGAR REFERENCIA FACIAL</p>
                                            <input ref="faceFileInput" type="file" accept="image/*" class="hidden"
                                                @change="handleFileSelect">
                                        </div>
                                        <div v-else class="relative border border-white/20 p-2">
                                            <img :src="previewUrl" class="w-full h-40 object-cover grayscale" />
                                            <button @click="selectedFile = null; previewUrl = null"
                                                class="absolute top-4 right-4 bg-red-600 text-black px-2 py-1 font-mono text-[10px] hover:bg-white transition-none">[X]</button>
                                        </div>

                                        <button @click="performFaceSearch" :disabled="isSearching"
                                            class="w-full bg-white text-black font-black py-3 uppercase tracking-widest hover:bg-red-600 hover:text-white transition-none disabled:opacity-50 flex items-center justify-center gap-2">
                                            {{ isSearching ? progressMessage : 'EJECUTAR MOTOR IA' }}
                                        </button>
                                        <p v-if="errorMessage"
                                            class="text-red-600 text-[10px] font-mono mt-2 uppercase">{{ errorMessage }}
                                        </p>
                                    </div>
                                </div>

                                <div class="border border-white/20 p-6 bg-black hover:border-white transition-none">
                                    <div class="flex items-center gap-3 mb-6 pb-4 border-b border-white/10">
                                        <div
                                            class="w-8 h-8 border border-white bg-white/10 flex items-center justify-center">
                                            <HashtagIcon class="w-4 h-4 text-white" />
                                        </div>
                                        <h3 class="font-black text-xl uppercase tracking-tighter">ANÁLISIS OCR (DORSAL)
                                        </h3>
                                    </div>
                                    <div class="space-y-4">
                                        <label
                                            class="block font-mono text-[10px] text-gray-500 uppercase tracking-widest">INGRESAR
                                            IDENTIFICADOR NUMÉRICO</label>
                                        <input v-model="bibNumber" type="text" placeholder="EJ: 529"
                                            class="w-full px-4 py-4 bg-transparent border border-gray-600 focus:border-white focus:ring-0 text-2xl font-mono text-center text-white placeholder-gray-800"
                                            @keyup.enter="performBibSearch" />
                                        <button @click="performBibSearch"
                                            :disabled="isSearchingBib || !bibNumber.trim()"
                                            class="w-full border-2 border-white text-white font-black py-3 uppercase tracking-widest hover:bg-white hover:text-black transition-none disabled:opacity-50">
                                            {{ isSearchingBib ? 'PROCESANDO...' : 'EJECUTAR MOTOR OCR' }}
                                        </button>
                                        <p v-if="bibErrorMessage"
                                            class="text-red-600 text-[10px] font-mono mt-2 uppercase">{{ bibErrorMessage
                                            }}</p>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-6 flex justify-end">
                                <button type="button" @click="clearFilters"
                                    class="font-mono text-[10px] text-red-600 hover:text-white uppercase tracking-[0.2em] transition-none border-b border-red-600 hover:border-white pb-1">
                                    [ REINICIAR PARÁMETROS ]
                                </button>
                            </div>
                        </div>
                    </form>
                </div>

                <div
                    class="flex flex-col md:flex-row items-start md:items-center justify-between mb-8 gap-4 border-b border-white/20 pb-4">
                    <div class="flex flex-wrap gap-2">
                        <button v-for="option in sortOptions" :key="option.value" @click="changeSort(option.value)"
                            :class="[
                                'px-4 py-2 text-[10px] font-bold uppercase tracking-[0.15em] transition-none border',
                                filterForm.sort === option.value
                                    ? 'bg-white text-black border-white'
                                    : 'bg-black text-gray-500 border-white/20 hover:border-white hover:text-white'
                            ]">
                            {{ option.label }}
                        </button>
                    </div>
                </div>

                <div v-if="allPhotos.length > 0" :key="gridKey">
                    <div class="columns-2 md:columns-3 lg:columns-4 xl:columns-5 gap-2 space-y-2 masonry-grid">
                        <div v-for="photo in allPhotos" :key="photo.id"
                            @click="router.visit(route('gallery.show', photo.unique_id))" @contextmenu.prevent
                            class="break-inside-avoid block group relative bg-gray-950 overflow-hidden border-[6px] border-black hover:border-red-600 transition-none ">

                            <div class="relative w-full h-auto">
                                <ProtectedImage :src="photo.thumbnail_url" :alt="photo.unique_id"
                                    class="w-full h-auto object-cover grayscale opacity-70 group-hover:grayscale-0 group-hover:opacity-100 transition-none pointer-events-none"
                                    loading="lazy" @error="handleImageError" />

                                <div
                                    class="absolute top-3 left-3 bg-black border border-white px-2 py-1 text-[9px] font-mono text-white tracking-widest pointer-events-none">
                                    ${{ photo.price }}
                                </div>

                                <div v-if="showingFaceResults && photo.similarity"
                                    class="absolute bottom-3 left-3 bg-red-600 text-black px-2 py-1 font-bold text-[9px] font-mono tracking-widest pointer-events-none">
                                    MATCH: {{ Math.round(photo.similarity * 100) }}%
                                </div>
                                <div v-if="showingBibResults && photo.bib_numbers"
                                    class="absolute bottom-3 left-3 bg-white text-black px-2 py-1 font-bold text-[9px] font-mono tracking-widest pointer-events-none">
                                    #{{ photo.bib_numbers.join(', ') }}
                                </div>

                                <div
                                    class="absolute bottom-3 right-3 opacity-0 group-hover:opacity-100 transition-none pointer-events-none bg-black text-white p-1 border border-white">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="square" stroke-width="2" d="M7 17L17 7M17 7H7M17 7v10" />
                                    </svg>
                                </div>
                            </div>



                        </div>
                    </div>

                    <div v-if="nextUrl && !showingFaceResults && !showingBibResults" class="flex justify-center pt-16">
                        <button @click="loadMore" :disabled="loadingMore"
                            class="border-[4px] border-white px-12 py-4 bg-black text-white hover:bg-white hover:text-black font-black uppercase tracking-widest transition-none disabled:opacity-50 disabled:cursor-not-allowed">
                            {{ loadingMore ? '[ DESCARGANDO DATOS... ]' : 'CARGAR MÁS FRAGMENTOS' }}
                        </button>
                    </div>
                </div>

                <div v-else
                    class="flex flex-col items-center justify-center py-32 border-4 border-dashed border-gray-800 text-center bg-gray-950">
                    <h3 class="font-black text-6xl text-gray-700 tracking-tighter mb-4 uppercase">VACÍO.</h3>
                    <p class="font-mono text-xs text-gray-500 tracking-widest mb-8 uppercase">NO EXISTEN REGISTROS EN LA
                        BASE DE DATOS.</p>
                    <button @click="clearFilters"
                        class="border-2 border-red-600 bg-black text-red-600 hover:bg-red-600 hover:text-black px-8 py-3 font-mono text-[10px] font-bold uppercase tracking-widest transition-none">
                        [ REINICIAR CATÁLOGO ]
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
    background: #000000;
    border-left: 1px solid #333;
}

::-webkit-scrollbar-thumb {
    background: #ffffff;
    border-radius: 0;
}

::-webkit-scrollbar-thumb:hover {
    background: #dc2626;
}


@keyframes precise-glitch {

    0%,
    33.33%,
    43.33%,
    66.67%,
    76.67%,
    100% {
        transform: none;
        filter: hue-rotate(0) drop-shadow(0 0 0 transparent);
    }

    33.43%,
    43.23% {
        transform: translateX(var(--glitch-x-1));
        filter: hue-rotate(var(--glitch-hue-1)) drop-shadow(3px 0 0 rgba(220, 38, 38, 0.6));
    }

    66.77%,
    76.57% {
        transform: translateX(var(--glitch-x-2));
        filter: hue-rotate(var(--glitch-hue-2)) drop-shadow(-3px 0 0 rgba(255, 255, 255, 0.4));
    }
}

:deep(.glitch-strip) {
    width: 100%;
    background-repeat: no-repeat;
    animation-name: precise-glitch;
    animation-timing-function: linear;
    animation-iteration-count: infinite;
    animation-play-state: paused;
}

.group:hover :deep(.glitch-strip) {
    animation-play-state: running;
}
</style>