<script setup>
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { ref, onMounted, nextTick } from 'vue';
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
const progressMessage = ref('Cargando modelos de IA...');

const showingBibResults = ref(false);
const bibSearchResults = ref(null);
const isSearchingBib = ref(false);
const bibNumber = ref('');
const bibErrorMessage = ref('');

const gridKey = ref(Date.now());

onMounted(async () => {
    try {
        const MODEL_URL = '/models';
        await faceapi.nets.ssdMobilenetv1.loadFromUri(MODEL_URL);
        await faceapi.nets.faceLandmark68Net.loadFromUri(MODEL_URL);
        await faceapi.nets.faceRecognitionNet.loadFromUri(MODEL_URL);
        isLoadingModels.value = false;
        progressMessage.value = '';
    } catch (error) {
        console.error('Error cargando modelos:', error);
        errorMessage.value = 'Error al cargar los modelos de IA. Recarga la página.';
        isLoadingModels.value = false;
    }
});

const handleFileSelect = (event) => {
    const file = event.target.files[0];
    if (!file) return;
    if (!file.type.startsWith('image/')) { errorMessage.value = 'Por favor selecciona una imagen válida'; return; }
    if (file.size > 10 * 1024 * 1024) { errorMessage.value = 'La imagen es demasiado grande. Máximo 10MB'; return; }
    selectedFile.value = file;
    errorMessage.value = '';
    const reader = new FileReader();
    reader.onload = (e) => { previewUrl.value = e.target.result; };
    reader.readAsDataURL(file);
};

const performFaceSearch = async () => {
    if (!selectedFile.value) { errorMessage.value = 'Por favor selecciona una foto'; return; }
    isSearching.value = true;
    errorMessage.value = '';
    progressMessage.value = 'Detectando rostro...';
    try {
        const img = await faceapi.bufferToImage(selectedFile.value);
        progressMessage.value = 'Analizando características faciales...';
        const detection = await faceapi.detectSingleFace(img).withFaceLandmarks().withFaceDescriptor();
        if (!detection) { errorMessage.value = 'No se detectó ningún rostro. Intenta con otra foto.'; isSearching.value = false; progressMessage.value = ''; return; }
        const descriptor = Array.from(detection.descriptor);
        progressMessage.value = 'Buscando en toda la galería...';
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
        errorMessage.value = error.response?.data?.message || 'Error al buscar. Intenta nuevamente.';
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
    if (!bibNumber.value.trim()) { bibErrorMessage.value = 'Por favor ingresa un número de dorsal'; return; }
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
        bibErrorMessage.value = error.response?.data?.message || 'Error al buscar. Intenta nuevamente.';
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
        ph.className = 'placeholder-img w-full h-48 flex items-center justify-center bg-[#1B2632]';
        ph.innerHTML = `<svg class="w-10 h-10 text-[#2C3B4D]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>`;
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
    <Head title="Galería — f33" />

    <AppLayout>

        <!-- ═══ HERO HEADER ═══ -->
        <div class="relative bg-[#1B2632] pt-28 pb-16 md:pt-36 md:pb-20 overflow-hidden">
            <!-- Fondo decorativo -->
            <div class="absolute inset-0 pointer-events-none">
                <div class="absolute top-0 right-0 w-[500px] h-[500px] rounded-full bg-[#FFB162]/5 blur-3xl translate-x-1/2 -translate-y-1/2"></div>
                <div class="absolute bottom-0 left-0 w-[300px] h-[300px] rounded-full bg-[#A35139]/10 blur-2xl -translate-x-1/3 translate-y-1/3"></div>
                <!-- Grilla sutil -->
                <div class="absolute inset-0 opacity-[0.03]"
                    style="background-image: linear-gradient(#EEE9DF 1px, transparent 1px), linear-gradient(90deg, #EEE9DF 1px, transparent 1px); background-size: 60px 60px;"></div>
            </div>

            <div class="relative max-w-7xl mx-auto px-6 lg:px-8">
                <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-8">
                    <div>
                        <span class="inline-flex items-center gap-2 text-[10px] font-bold tracking-[0.35em] text-[#FFB162] uppercase mb-5">
                            <span class="w-8 h-px bg-[#FFB162]"></span>
                            Catálogo Completo
                        </span>
                        <h1 class="font-serif text-5xl md:text-7xl font-light text-[#EEE9DF] leading-[0.95] mb-4">
                            Galería <em class="italic text-[#C9C1B1]">de Fotos</em>
                        </h1>
                        <p class="text-sm text-[#C9C1B1]/60 font-light tracking-wide max-w-sm">
                            Cada imagen, un instante capturado con precisión artística.
                        </p>
                    </div>

                    <div class="text-right">
                        <span class="font-serif text-6xl font-light text-[#FFB162] leading-none block">
                            {{ totalResults() }}
                        </span>
                        <span class="text-[10px] font-bold uppercase tracking-[0.25em] text-[#C9C1B1]/50 mt-1 block">
                            {{ (showingFaceResults || showingBibResults) ? 'Coincidencias' : 'Fotografías' }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <div class="min-h-screen bg-[#111920]">
            <div class="max-w-7xl mx-auto px-6 lg:px-8 py-10">

                <!-- ═══ BANNERS DE RESULTADO ═══ -->
                <Transition name="banner-slide">
                    <div v-if="showingFaceResults"
                        class="relative bg-[#1B2632] border border-[#FFB162]/20 p-6 mb-8 overflow-hidden">
                        <div class="absolute top-0 left-0 w-1 h-full bg-gradient-to-b from-[#FFB162] to-[#A35139]"></div>
                        <div class="absolute right-0 top-0 bottom-0 w-32 opacity-5"
                            style="background: radial-gradient(ellipse at right, #FFB162, transparent)"></div>
                        <div class="flex items-center justify-between pl-4">
                            <div class="flex items-center gap-5">
                                <div class="w-10 h-10 bg-[#FFB162]/10 border border-[#FFB162]/30 flex items-center justify-center flex-shrink-0">
                                    <SparklesIcon class="w-5 h-5 text-[#FFB162]" />
                                </div>
                                <div>
                                    <h3 class="text-sm font-bold text-[#EEE9DF] tracking-wide mb-0.5">
                                        Búsqueda Facial Activa
                                    </h3>
                                    <p class="text-xs text-[#C9C1B1]/60">
                                        <span class="text-[#FFB162] font-bold font-mono">{{ faceSearchResults.count }}</span> fotos encontradas · ordenadas por similitud
                                    </p>
                                </div>
                            </div>
                            <button @click="clearFaceSearch"
                                class="flex items-center gap-2 text-[10px] font-bold uppercase tracking-[0.2em] text-[#C9C1B1]/50 hover:text-[#FFB162] transition-colors border border-[#C9C1B1]/10 hover:border-[#FFB162]/30 px-4 py-2">
                                <XMarkIcon class="w-3.5 h-3.5" />
                                Limpiar
                            </button>
                        </div>
                    </div>
                </Transition>

                <Transition name="banner-slide">
                    <div v-if="showingBibResults"
                        class="relative bg-[#1B2632] border border-[#2C3B4D] p-6 mb-8 overflow-hidden">
                        <div class="absolute top-0 left-0 w-1 h-full bg-gradient-to-b from-[#2C3B4D] to-[#1B2632]"></div>
                        <div class="flex items-center justify-between pl-4">
                            <div class="flex items-center gap-5">
                                <div class="w-10 h-10 bg-[#2C3B4D]/50 border border-[#2C3B4D] flex items-center justify-center flex-shrink-0">
                                    <HashtagIcon class="w-5 h-5 text-[#C9C1B1]" />
                                </div>
                                <div>
                                    <h3 class="text-sm font-bold text-[#EEE9DF] tracking-wide mb-0.5">
                                        Dorsal <span class="font-mono text-[#FFB162]">#{{ bibNumber }}</span>
                                    </h3>
                                    <p class="text-xs text-[#C9C1B1]/60">
                                        <span class="text-[#EEE9DF] font-bold font-mono">{{ bibSearchResults.count }}</span> fotos localizadas por OCR
                                    </p>
                                </div>
                            </div>
                            <button @click="clearBibSearch"
                                class="flex items-center gap-2 text-[10px] font-bold uppercase tracking-[0.2em] text-[#C9C1B1]/50 hover:text-[#FFB162] transition-colors border border-[#C9C1B1]/10 hover:border-[#FFB162]/30 px-4 py-2">
                                <XMarkIcon class="w-3.5 h-3.5" />
                                Limpiar
                            </button>
                        </div>
                    </div>
                </Transition>

                <!-- ═══ PANEL DE BÚSQUEDA Y FILTROS ═══ -->
                <div class="bg-[#1B2632] border border-[#2C3B4D] mb-10">
                    <form @submit.prevent="applyFilters">
                        <!-- Barra principal -->
                        <div class="flex flex-col md:flex-row gap-0 border-b border-[#2C3B4D]">
                            <div class="flex-1 relative">
                                <MagnifyingGlassIcon class="absolute left-5 top-1/2 -translate-y-1/2 w-4 h-4 text-[#C9C1B1]/30" />
                                <input
                                    v-model="filterForm.search"
                                    type="text"
                                    placeholder="Buscar por ID, título o fotógrafo..."
                                    class="w-full pl-12 pr-5 py-4 bg-transparent text-[#EEE9DF] text-sm placeholder-[#C9C1B1]/25 border-0 focus:ring-0 focus:outline-none font-light tracking-wide"
                                />
                            </div>
                            <div class="flex border-t md:border-t-0 border-[#2C3B4D]">
                                <button
                                    type="button"
                                    @click="showFilters = !showFilters"
                                    :class="[
                                        'px-6 py-4 text-[10px] font-bold uppercase tracking-[0.2em] flex items-center gap-2.5 border-r border-[#2C3B4D] transition-all whitespace-nowrap',
                                        showFilters ? 'bg-[#2C3B4D] text-[#FFB162]' : 'text-[#C9C1B1]/50 hover:text-[#EEE9DF] hover:bg-[#2C3B4D]/40'
                                    ]">
                                    <AdjustmentsHorizontalIcon class="w-4 h-4" />
                                    Filtros
                                    <span v-if="showFilters" class="w-1.5 h-1.5 rounded-full bg-[#FFB162]"></span>
                                </button>
                                <button
                                    type="submit"
                                    class="px-8 py-4 bg-[#FFB162] hover:bg-[#A35139] text-[#1B2632] hover:text-[#EEE9DF] text-[10px] font-bold uppercase tracking-[0.25em] transition-all duration-300 whitespace-nowrap">
                                    Buscar
                                </button>
                            </div>
                        </div>

                        <!-- Panel expandible -->
                        <Transition name="filter-expand">
                            <div v-show="showFilters" class="p-8 space-y-10">

                                <!-- Filtros base -->
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label class="block text-[10px] font-bold uppercase tracking-[0.25em] text-[#C9C1B1]/50 mb-3">
                                            Región
                                        </label>
                                        <select
                                            v-model="filterForm.region"
                                            class="w-full bg-[#111920] border border-[#2C3B4D] text-[#EEE9DF] text-sm px-4 py-3 focus:border-[#FFB162] focus:ring-0 focus:outline-none appearance-none">
                                            <option value="all">Todas las regiones</option>
                                            <option v-for="region in regions" :key="region" :value="region">{{ region }}</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-bold uppercase tracking-[0.25em] text-[#C9C1B1]/50 mb-3">
                                            Evento
                                        </label>
                                        <select
                                            v-model="filterForm.event"
                                            class="w-full bg-[#111920] border border-[#2C3B4D] text-[#EEE9DF] text-sm px-4 py-3 focus:border-[#FFB162] focus:ring-0 focus:outline-none appearance-none">
                                            <option value="">Todos los eventos</option>
                                            <option v-for="event in events" :key="event.id" :value="event.id">{{ event.name }}</option>
                                        </select>
                                    </div>
                                </div>

                                <!-- Divisor -->
                                <div class="flex items-center gap-6">
                                    <div class="flex-1 h-px bg-[#2C3B4D]"></div>
                                    <span class="text-[10px] font-bold uppercase tracking-[0.3em] text-[#C9C1B1]/30">O usa búsqueda inteligente</span>
                                    <div class="flex-1 h-px bg-[#2C3B4D]"></div>
                                </div>

                                <!-- GRID DE BÚSQUEDA IA -->
                                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

                                    <!-- ── BÚSQUEDA FACIAL ── -->
                                    <div class="bg-[#111920] border border-[#2C3B4D] p-7 relative overflow-hidden group">
                                        <div class="absolute top-0 right-0 w-32 h-32 rounded-full bg-[#FFB162]/3 blur-2xl group-hover:bg-[#FFB162]/8 transition-all duration-700"></div>

                                        <!-- Header -->
                                        <div class="flex items-start justify-between mb-7 pb-6 border-b border-[#2C3B4D]">
                                            <div>
                                                <div class="flex items-center gap-3 mb-3">
                                                    <div class="w-8 h-8 bg-[#FFB162]/10 border border-[#FFB162]/20 flex items-center justify-center">
                                                        <SparklesIcon class="w-4 h-4 text-[#FFB162]" />
                                                    </div>
                                                    <h3 class="font-serif text-xl font-light text-[#EEE9DF]">Reconocimiento Facial</h3>
                                                </div>
                                                <p class="text-xs text-[#C9C1B1]/40 leading-relaxed font-light">
                                                    Detección biométrica por IA. Sube una foto para localizar coincidencias.
                                                </p>
                                            </div>
                                            <span class="text-[9px] font-bold tracking-[0.2em] uppercase text-[#FFB162]/60 border border-[#FFB162]/20 bg-[#FFB162]/5 px-2 py-1 flex-shrink-0">
                                                AI Beta
                                            </span>
                                        </div>

                                        <!-- Cargando modelos -->
                                        <div v-if="isLoadingModels" class="py-10 flex flex-col items-center justify-center gap-4">
                                            <div class="w-5 h-5 border-2 border-[#2C3B4D] border-t-[#FFB162] rounded-full animate-spin"></div>
                                            <p class="text-[10px] font-bold tracking-[0.2em] uppercase text-[#C9C1B1]/40 animate-pulse">{{ progressMessage }}</p>
                                        </div>

                                        <div v-else>
                                            <!-- Zona de upload -->
                                            <div v-if="!previewUrl"
                                                class="relative border border-dashed border-[#2C3B4D] hover:border-[#FFB162]/40 transition-colors duration-300 cursor-pointer py-12 text-center group/drop"
                                                @click="$refs.faceFileInput.click()">
                                                <FaceSmileIcon class="w-8 h-8 text-[#2C3B4D] group-hover/drop:text-[#FFB162]/50 transition-colors stroke-1 mx-auto mb-3" />
                                                <p class="font-serif text-base font-light text-[#C9C1B1]/50 group-hover/drop:text-[#EEE9DF] transition-colors mb-1">
                                                    Seleccionar Referencia
                                                </p>
                                                <p class="text-[9px] uppercase tracking-[0.25em] text-[#C9C1B1]/25">JPG, PNG · Máx 10MB</p>
                                                <input ref="faceFileInput" type="file" accept="image/*" class="hidden" @change="handleFileSelect">
                                            </div>

                                            <!-- Preview -->
                                            <div v-else class="space-y-5">
                                                <div class="relative">
                                                    <img :src="previewUrl" alt="Preview"
                                                        class="w-full h-52 object-contain bg-[#0d1820] border border-[#2C3B4D] grayscale hover:grayscale-0 transition-all duration-500" />
                                                    <button type="button"
                                                        @click="selectedFile = null; previewUrl = null; errorMessage = ''"
                                                        class="absolute top-3 right-3 w-7 h-7 bg-[#1B2632] border border-[#2C3B4D] hover:border-[#FFB162] flex items-center justify-center transition-colors">
                                                        <XMarkIcon class="w-3.5 h-3.5 text-[#C9C1B1]" />
                                                    </button>
                                                </div>
                                                <button type="button" @click="performFaceSearch" :disabled="isSearching"
                                                    class="w-full bg-[#FFB162] hover:bg-[#A35139] disabled:opacity-40 disabled:cursor-not-allowed text-[#1B2632] hover:text-[#EEE9DF] py-3.5 text-[10px] font-bold tracking-[0.25em] uppercase transition-all flex items-center justify-center gap-3">
                                                    <div v-if="isSearching" class="w-3 h-3 border border-current border-t-transparent rounded-full animate-spin"></div>
                                                    <SparklesIcon v-else class="w-3.5 h-3.5" />
                                                    {{ isSearching ? progressMessage || 'Analizando...' : 'Iniciar Escaneo' }}
                                                </button>
                                            </div>

                                            <div v-if="errorMessage" class="mt-4 p-3 bg-[#A35139]/10 border border-[#A35139]/20 text-center">
                                                <p class="text-xs text-[#FFB162] font-medium">{{ errorMessage }}</p>
                                            </div>

                                            <!-- Tips -->
                                            <div class="mt-6 pt-5 border-t border-[#2C3B4D]">
                                                <p class="text-[9px] font-bold tracking-[0.25em] uppercase text-[#C9C1B1]/30 mb-3">Parámetros óptimos</p>
                                                <div class="grid grid-cols-2 gap-1.5">
                                                    <span v-for="tip in ['Iluminación frontal', 'Sin accesorios', 'Rostro descubierto', 'Foco nítido']" :key="tip"
                                                        class="flex items-center gap-1.5 text-[9px] text-[#C9C1B1]/30 font-light">
                                                        <CheckIcon class="w-3 h-3 text-[#FFB162]/30 flex-shrink-0" />
                                                        {{ tip }}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- ── BÚSQUEDA POR DORSAL ── -->
                                    <div class="bg-[#111920] border border-[#2C3B4D] p-7 relative overflow-hidden group">
                                        <div class="absolute bottom-0 left-0 w-32 h-32 rounded-full bg-[#2C3B4D]/30 blur-2xl"></div>

                                        <!-- Header -->
                                        <div class="flex items-start justify-between mb-7 pb-6 border-b border-[#2C3B4D]">
                                            <div>
                                                <div class="flex items-center gap-3 mb-3">
                                                    <div class="w-8 h-8 bg-[#2C3B4D]/50 border border-[#2C3B4D] flex items-center justify-center">
                                                        <HashtagIcon class="w-4 h-4 text-[#C9C1B1]" />
                                                    </div>
                                                    <h3 class="font-serif text-xl font-light text-[#EEE9DF]">Búsqueda por Dorsal</h3>
                                                </div>
                                                <p class="text-xs text-[#C9C1B1]/40 leading-relaxed font-light">
                                                    Reconocimiento óptico de caracteres. Ingresa tu número de competidor.
                                                </p>
                                            </div>
                                            <span class="text-[9px] font-bold tracking-[0.2em] uppercase text-[#C9C1B1]/40 border border-[#2C3B4D] bg-[#2C3B4D]/30 px-2 py-1 flex-shrink-0">
                                                OCR v2
                                            </span>
                                        </div>

                                        <div class="space-y-5">
                                            <!-- Input dorsal -->
                                            <div>
                                                <label class="block text-[9px] font-bold uppercase tracking-[0.3em] text-[#C9C1B1]/30 mb-3">
                                                    Número de Dorsal
                                                </label>
                                                <input
                                                    v-model="bibNumber"
                                                    type="text"
                                                    placeholder="529"
                                                    class="w-full px-5 py-4 bg-[#0d1820] border border-[#2C3B4D] focus:border-[#FFB162]/50 focus:ring-0 focus:outline-none text-3xl font-mono font-light text-center text-[#EEE9DF] placeholder-[#2C3B4D] tracking-widest transition-colors"
                                                    @keyup.enter="performBibSearch" />
                                                <p class="mt-2 text-[9px] uppercase tracking-[0.25em] text-[#C9C1B1]/25 text-center">
                                                    Solo dígitos · Sin ceros a la izquierda
                                                </p>
                                            </div>

                                            <button type="button" @click="performBibSearch"
                                                :disabled="isSearchingBib || !bibNumber.trim()"
                                                class="w-full bg-[#2C3B4D] hover:bg-[#FFB162] hover:text-[#1B2632] disabled:opacity-30 disabled:cursor-not-allowed text-[#EEE9DF] py-3.5 text-[10px] font-bold tracking-[0.25em] uppercase transition-all duration-300 border border-[#2C3B4D] hover:border-[#FFB162] flex items-center justify-center gap-3">
                                                <div v-if="isSearchingBib" class="w-3 h-3 border border-current border-t-transparent rounded-full animate-spin"></div>
                                                <HashtagIcon v-else class="w-3.5 h-3.5" />
                                                {{ isSearchingBib ? 'Buscando...' : 'Buscar Dorsal' }}
                                            </button>

                                            <div v-if="bibErrorMessage" class="p-3 bg-[#A35139]/10 border border-[#A35139]/20 text-center">
                                                <p class="text-xs text-[#FFB162] font-medium">{{ bibErrorMessage }}</p>
                                            </div>

                                            <!-- Tips -->
                                            <div class="pt-5 border-t border-[#2C3B4D]">
                                                <p class="text-[9px] font-bold tracking-[0.25em] uppercase text-[#C9C1B1]/30 mb-3">Recomendaciones</p>
                                                <div class="space-y-2">
                                                    <span v-for="tip in ['Número exacto del evento', 'Sin ceros a la izquierda (529, no 0529)', 'Dorsal visible en las fotos', 'Solo números detectados por IA']" :key="tip"
                                                        class="flex items-start gap-2 text-[9px] text-[#C9C1B1]/30 font-light leading-relaxed">
                                                        <CheckIcon class="w-3 h-3 text-[#C9C1B1]/20 flex-shrink-0 mt-px" />
                                                        {{ tip }}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Limpiar todo -->
                                <div class="flex justify-end pt-2">
                                    <button type="button" @click="clearFilters"
                                        class="flex items-center gap-2 text-[10px] font-bold uppercase tracking-[0.2em] text-[#A35139] hover:text-[#FFB162] transition-colors pb-0.5 border-b border-[#A35139]/30 hover:border-[#FFB162]/50">
                                        <XMarkIcon class="w-3.5 h-3.5" />
                                        Limpiar todo
                                    </button>
                                </div>
                            </div>
                        </Transition>
                    </form>
                </div>

                <!-- ═══ SORT BAR ═══ -->
                <div class="flex items-center justify-between mb-8">
                    <div class="flex gap-1">
                        <button
                            v-for="option in sortOptions"
                            :key="option.value"
                            @click="changeSort(option.value)"
                            :class="[
                                'px-4 py-2 text-[10px] font-bold uppercase tracking-[0.15em] transition-all duration-200',
                                filterForm.sort === option.value
                                    ? 'bg-[#FFB162] text-[#1B2632]'
                                    : 'text-[#C9C1B1]/40 hover:text-[#C9C1B1] hover:bg-[#1B2632]'
                            ]">
                            {{ option.label }}
                        </button>
                    </div>

                    <span class="hidden md:block text-[10px] font-bold uppercase tracking-[0.2em] text-[#C9C1B1]/25">
                        {{ totalResults() }} resultados
                    </span>
                </div>

                <!-- ═══ MASONRY GRID ═══ -->
                <div v-if="allPhotos.length > 0" :key="gridKey">
                    <div class="columns-2 md:columns-3 lg:columns-4 xl:columns-5 gap-4 space-y-0 masonry-grid">
                        <Link
                            v-for="(photo, i) in allPhotos"
                            :key="photo.id"
                            :href="route('gallery.show', photo.unique_id)"
                            class="break-inside-avoid mb-4 block group relative bg-[#1B2632] overflow-hidden border border-[#2C3B4D]/50 hover:border-[#FFB162]/30 transition-all duration-500">

                            <!-- Imagen con altura variable para efecto masonry real -->
                            <div class="relative overflow-hidden"
                                :class="[
                                    (i % 7 === 0) ? 'aspect-[3/4]' :
                                    (i % 7 === 1) ? 'aspect-square' :
                                    (i % 7 === 2) ? 'aspect-[4/5]' :
                                    (i % 7 === 3) ? 'aspect-[3/5]' :
                                    (i % 7 === 4) ? 'aspect-square' :
                                    (i % 7 === 5) ? 'aspect-[2/3]' :
                                    'aspect-[4/3]'
                                ]">
                                <img
                                    :src="photo.watermarked_url || photo.thumbnail_url"
                                    :alt="photo.unique_id"
                                    class="absolute inset-0 w-full h-full object-cover transition-all duration-700 group-hover:scale-105 saturate-75 group-hover:saturate-100"
                                    loading="lazy"
                                    @error="handleImageError" />

                                <!-- Overlay gradiente -->
                                <div class="absolute inset-0 bg-gradient-to-t from-[#1B2632] via-transparent to-transparent opacity-60 group-hover:opacity-80 transition-opacity duration-500"></div>

                                <!-- Badge precio -->
                                <div class="absolute top-3 right-3 bg-[#1B2632]/90 backdrop-blur-sm border border-[#2C3B4D] px-2.5 py-1 text-[10px] font-bold text-[#FFB162] font-mono">
                                    ${{ photo.price }}
                                </div>

                                <!-- Badge similitud facial -->
                                <div v-if="showingFaceResults && photo.similarity"
                                    class="absolute top-3 left-0 bg-[#FFB162] pl-3 pr-3 py-1.5 z-10">
                                    <div class="flex items-center gap-1.5">
                                        <span class="w-1 h-1 rounded-full bg-[#1B2632] animate-pulse"></span>
                                        <span class="font-mono text-[10px] text-[#1B2632] font-bold tracking-wide">
                                            {{ Math.round(photo.similarity * 100) }}%
                                        </span>
                                    </div>
                                </div>

                                <!-- Badge dorsal -->
                                <div v-if="showingBibResults && photo.bib_numbers"
                                    class="absolute top-3 left-0 bg-[#2C3B4D] border-r border-b border-[#2C3B4D] pl-3 pr-3 py-1.5 z-10">
                                    <div class="flex items-center gap-1.5">
                                        <HashtagIcon class="w-3 h-3 text-[#C9C1B1]" />
                                        <span class="font-mono text-[10px] text-[#EEE9DF] font-bold">
                                            {{ photo.bib_numbers.join(', ') }}
                                        </span>
                                    </div>
                                </div>

                                <!-- Hover: flecha -->
                                <div class="absolute bottom-3 right-3 w-8 h-8 bg-[#FFB162] flex items-center justify-center opacity-0 group-hover:opacity-100 transition-all duration-300 translate-y-2 group-hover:translate-y-0">
                                    <svg class="w-3.5 h-3.5 text-[#1B2632]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M7 17L17 7M17 7H7M17 7v10" />
                                    </svg>
                                </div>
                            </div>

                            <!-- Info card -->
                            <div class="px-3 py-2.5 border-t border-[#2C3B4D]/50 group-hover:border-[#FFB162]/20 transition-colors">
                                <span class="block text-[9px] uppercase tracking-[0.2em] text-[#C9C1B1]/30 font-bold truncate">
                                    {{ photo.unique_id }}
                                </span>
                                <span class="block text-xs text-[#C9C1B1]/70 group-hover:text-[#FFB162] transition-colors font-light truncate mt-0.5">
                                    {{ photo.photographer }}
                                </span>
                            </div>
                        </Link>
                    </div>

                    <!-- Cargar más -->
                    <div v-if="nextUrl && !showingFaceResults && !showingBibResults" class="flex justify-center pt-14">
                        <button @click="loadMore" :disabled="loadingMore"
                            class="group relative px-12 py-4 border border-[#2C3B4D] hover:border-[#FFB162]/50 text-[10px] font-bold uppercase tracking-[0.3em] text-[#C9C1B1]/50 hover:text-[#FFB162] transition-all duration-300 disabled:opacity-30 disabled:cursor-not-allowed overflow-hidden">
                            <span class="absolute inset-0 bg-[#FFB162]/5 translate-y-full group-hover:translate-y-0 transition-transform duration-300"></span>
                            <span class="relative flex items-center gap-3">
                                <svg v-if="loadingMore" class="animate-spin h-3.5 w-3.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                {{ loadingMore ? 'Cargando...' : 'Cargar más fotos' }}
                            </span>
                        </button>
                    </div>
                </div>

                <!-- Sin resultados -->
                <div v-else class="flex flex-col items-center justify-center py-32 border border-dashed border-[#2C3B4D] text-center">
                    <div class="w-16 h-16 border border-[#2C3B4D] flex items-center justify-center mb-6">
                        <svg class="w-7 h-7 text-[#2C3B4D]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </div>
                    <h3 class="font-serif text-2xl font-light text-[#EEE9DF] mb-2">Sin resultados</h3>
                    <p class="text-sm text-[#C9C1B1]/40 font-light max-w-xs mb-8 leading-relaxed">
                        No encontramos fotografías que coincidan con los criterios actuales.
                    </p>
                    <button @click="clearFilters"
                        class="px-8 py-3 border border-[#2C3B4D] hover:border-[#FFB162]/40 text-[10px] font-bold uppercase tracking-[0.25em] text-[#C9C1B1]/50 hover:text-[#FFB162] transition-all">
                        Ver catálogo completo
                    </button>
                </div>

            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
/* Tipografía */
@import url('https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;1,300;1,400&family=Syne:wght@400;500;700;800&display=swap');

.font-serif { font-family: 'Cormorant Garamond', serif; }
.font-sans  { font-family: 'Syne', sans-serif; }

/* Masonry: soporte para Safari/Firefox vía columns */
.masonry-grid {
    column-fill: balance;
}

/* Transición banner */
.banner-slide-enter-active,
.banner-slide-leave-active { transition: all 0.4s ease; }
.banner-slide-enter-from    { opacity: 0; transform: translateY(-12px); }
.banner-slide-leave-to      { opacity: 0; transform: translateY(-8px); }

/* Transición filtros */
.filter-expand-enter-active,
.filter-expand-leave-active { transition: all 0.35s ease; overflow: hidden; }
.filter-expand-enter-from   { opacity: 0; max-height: 0; }
.filter-expand-enter-to     { opacity: 1; max-height: 9999px; }
.filter-expand-leave-from   { opacity: 1; max-height: 9999px; }
.filter-expand-leave-to     { opacity: 0; max-height: 0; }

/* Select arrow personalizado */
select {
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3E%3Cpath stroke='%23C9C1B1' stroke-opacity='0.3' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 12px center;
    background-size: 18px;
    padding-right: 40px !important;
}

/* Scrollbar minimal */
::-webkit-scrollbar       { width: 4px; }
::-webkit-scrollbar-track { background: #111920; }
::-webkit-scrollbar-thumb { background: #2C3B4D; border-radius: 0; }
::-webkit-scrollbar-thumb:hover { background: #FFB162; }
</style>