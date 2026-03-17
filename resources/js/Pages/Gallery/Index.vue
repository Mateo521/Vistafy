<script setup>
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { ref, onMounted ,nextTick } from 'vue';
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

// --- BÚSQUEDA FACIAL ---
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

// Cargar modelos de Face-API al montar el componente
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

// Manejar selección de archivo
const handleFileSelect = (event) => {
    const file = event.target.files[0];
    if (!file) return;

    if (!file.type.startsWith('image/')) {
        errorMessage.value = 'Por favor selecciona una imagen válida';
        return;
    }

    if (file.size > 10 * 1024 * 1024) {
        errorMessage.value = 'La imagen es demasiado grande. Máximo 10MB';
        return;
    }

    selectedFile.value = file;
    errorMessage.value = '';

    const reader = new FileReader();
    reader.onload = (e) => {
        previewUrl.value = e.target.result;
    };
    reader.readAsDataURL(file);
};

// Realizar búsqueda facial
const performFaceSearch = async () => {
    if (!selectedFile.value) {
        errorMessage.value = 'Por favor selecciona una foto';
        return;
    }

    isSearching.value = true;
    errorMessage.value = '';
    progressMessage.value = 'Detectando rostro...';

    try {
        const img = await faceapi.bufferToImage(selectedFile.value);

        progressMessage.value = 'Analizando características faciales...';
        const detection = await faceapi
            .detectSingleFace(img)
            .withFaceLandmarks()
            .withFaceDescriptor();

        if (!detection) {
            errorMessage.value = 'No se detectó ningún rostro. Intenta con otra foto.';
            isSearching.value = false;
            progressMessage.value = '';
            return;
        }

        const descriptor = Array.from(detection.descriptor);

        progressMessage.value = 'Buscando en toda la galería...';

        const response = await axios.post(route('gallery.face-search'), {
            face_descriptor: descriptor,
            threshold: 0.6
        });


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
        console.error('Error en búsqueda:', error);
        errorMessage.value = error.response?.data?.message || 'Error al buscar. Intenta nuevamente.';
    } finally {
        isSearching.value = false;
        progressMessage.value = '';
    }
};

// Limpiar búsqueda facial
const clearFaceSearch = () => {
    showingFaceResults.value = false;
    faceSearchResults.value = null;
    selectedFile.value = null;
    previewUrl.value = null;
    errorMessage.value = '';

    allPhotos.value = [...props.photos.data];
    nextUrl.value = props.photos.next_page_url;
    gridKey.value = Date.now(); //  Forzar re-render
};
const performBibSearch = async () => {
    if (!bibNumber.value.trim()) {
        bibErrorMessage.value = 'Por favor ingresa un número de dorsal';
        return;
    }

    isSearchingBib.value = true;
    bibErrorMessage.value = '';

    try {
        const response = await axios.post(route('gallery.bib-search'), {
            bib_number: bibNumber.value.trim()
        });



             if (response.data.success) {
            clearFaceSearch();
            
            bibSearchResults.value = response.data;
            showingBibResults.value = true;
            
            //  LIMPIAR PRIMERO
            allPhotos.value = [];
            
            //  ESPERAR UN TICK
            await nextTick();
            
            //  AHORA SÍ ACTUALIZAR
            allPhotos.value = [...response.data.results];
            gridKey.value = Date.now();
            
            nextUrl.value = null;
            showFilters.value = false;

           
        }

    } catch (error) {
        console.error('Error en búsqueda por dorsal:', error);
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
        gridKey.value = Date.now(); //  Forzar re-render
    }
};

// --- PAGINACIÓN ---
const loadMore = () => {
    if (!nextUrl.value || loadingMore.value) return;

    loadingMore.value = true;

    router.visit(nextUrl.value, {
        preserveState: true,
        preserveScroll: true,
        only: ['photos'],
        onSuccess: (page) => {
            const newPhotos = page.props.photos.data;
            allPhotos.value = [...allPhotos.value, ...newPhotos];
            nextUrl.value = page.props.photos.next_page_url;
            loadingMore.value = false;
        },
        onError: () => {
            loadingMore.value = false;
        }
    });
};

// --- FILTROS ---
const filterForm = useForm({
    search: props.filters.search || '',
    region: props.filters.region || 'all',
    event: props.filters.event || '',
    sort: props.filters.sort || 'recent',
});

const showFilters = ref(false);


const applyFilters = () => {
    // Si hay búsqueda facial o de dorsales activa, limpiarlas primero
    if (showingFaceResults.value) {
        clearFaceSearch();
    }
    if (showingBibResults.value) {
        clearBibSearch();
    }

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

    // También limpiar búsqueda facial y de dorsales si están activas
    if (showingFaceResults.value) {
        clearFaceSearch();
    }
    if (showingBibResults.value) {
        clearBibSearch();
    }

    applyFilters();
};

const changeSort = (sortValue) => {
    filterForm.sort = sortValue;
    applyFilters();
};

const sortOptions = [
    { value: 'recent', label: 'Recientes' },
    { value: 'popular', label: 'Populares' },
    { value: 'price_low', label: 'Precio: Menor' },
    { value: 'price_high', label: 'Precio: Mayor' },
];

const handleImageError = (e) => {
    e.target.style.display = 'none';
    const parent = e.target.parentElement;
    if (parent && !parent.querySelector('.placeholder-institutional')) {
        const placeholder = document.createElement('div');
        placeholder.className = 'placeholder-institutional w-full h-full flex items-center justify-center bg-gray-100';
        placeholder.innerHTML = `
            <svg class="w-12 h-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1"
                    d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
        `;
        parent.appendChild(placeholder);
    }
};
</script>

<template>

    <Head title="Galería de Fotos" />

    <AppLayout>
        <!-- Header -->
        <div class="bg-white border-b border-gray-100 pt-24 pb-12 md:pt-32 md:pb-12">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-6">
                    <div class="max-w-3xl">
                        <span class="text-xs font-bold tracking-[0.2em] text-slate-400 uppercase mb-2 block">
                            Catálogo Completo
                        </span>
                        <h1 class="text-4xl md:text-5xl font-sans font-bold text-slate-900">
                            Galería de Fotos
                        </h1>
                    </div>

                    <div class="text-right hidden md:block">
                        <span class="text-3xl font-sans font-bold text-slate-900 block leading-none">
                            {{ showingFaceResults ? faceSearchResults.count : (showingBibResults ?
                                bibSearchResults.count : photos.total) }}
                        </span>
                        <span class="text-[10px] font-bold uppercase tracking-widest text-slate-400">
                            {{ (showingFaceResults || showingBibResults) ? 'Coincidencias Encontradas' : 'Fotografías Disponibles' }}
                        </span>
                    </div>



                </div>
            </div>
        </div>

        <div class="min-h-screen bg-gray-50 py-8">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

                <!-- Banner de resultados faciales -->
                <div v-if="showingFaceResults"
                    class="bg-gradient-to-r from-purple-50 to-pink-50 border-2 border-purple-200 rounded-lg p-6 mb-8">
                    <div class="flex items-start justify-between">
                        <div class="flex items-start gap-4">
                            <div
                                class="w-12 h-12 bg-gradient-to-br from-purple-500 to-pink-500 rounded-lg flex items-center justify-center flex-shrink-0">
                                <SparklesIcon class="w-6 h-6 text-white" />
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-slate-900 mb-1">
                                    Resultados de Búsqueda por cara
                                </h3>
                                <p class="text-sm text-slate-600 mb-3">
                                    Encontramos <strong>{{ faceSearchResults.count }}</strong> fotos donde apareces
                                    <span v-if="faceSearchResults.count > 0"> ordenadas por similitud</span>
                                </p>
                                <button @click="clearFaceSearch"
                                    class="text-sm font-semibold text-purple-600 hover:text-purple-800 underline">
                                    ← Volver a la galería completa
                                </button>
                            </div>
                        </div>
                        <button @click="clearFaceSearch" class="p-2 hover:bg-white rounded-lg transition">
                            <XMarkIcon class="w-5 h-5 text-slate-500" />
                        </button>
                    </div>
                </div>


                <div v-if="showingBibResults"
                    class="bg-gradient-to-r from-blue-50 to-cyan-50 border-2 border-blue-200 rounded-lg p-6 mb-8">
                    <div class="flex items-start justify-between">
                        <div class="flex items-start gap-4">
                            <div
                                class="w-12 h-12 bg-gradient-to-br from-blue-500 to-cyan-500 rounded-lg flex items-center justify-center flex-shrink-0">
                                <HashtagIcon class="w-6 h-6 text-white" />
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-slate-900 mb-1">
                                    Resultados de Búsqueda por Dorsal
                                </h3>
                                <p class="text-sm text-slate-600 mb-3">
                                    Encontramos <strong>{{ bibSearchResults.count }}</strong> fotos con el dorsal
                                    <strong class="font-mono text-blue-600">#{{ bibNumber }}</strong>
                                </p>
                                <button @click="clearBibSearch"
                                    class="text-sm font-semibold text-blue-600 hover:text-blue-800 underline">
                                    ← Volver a la galería completa
                                </button>
                            </div>
                        </div>
                        <button @click="clearBibSearch" class="p-2 hover:bg-white rounded-lg transition">
                            <XMarkIcon class="w-5 h-5 text-slate-500" />
                        </button>
                    </div>
                </div>

                <!-- Panel de Filtros -->
                <div class="bg-white border border-gray-200 p-6 mb-8 rounded-sm shadow-sm">
                    <form @submit.prevent="applyFilters">
                        <div class="flex flex-col md:flex-row gap-4">
                            <div class="flex-1 relative">
                                <input v-model="filterForm.search" type="text"
                                    placeholder="Buscar por ID, título o fotógrafo..."
                                    class="w-full pl-10 pr-4 py-3 rounded-sm border-gray-300 text-sm focus:border-slate-900 focus:ring-0 placeholder-gray-400 transition-colors" />
                                <div class="absolute left-3 top-3 text-slate-400">
                                    <MagnifyingGlassIcon class="w-5 h-5" />
                                </div>
                            </div>

                            <div class="flex gap-3">
                                <button type="button" @click="showFilters = !showFilters" :class="[
                                    'px-6 py-3 rounded-sm border text-xs font-bold uppercase tracking-widest transition-all duration-300 flex items-center gap-2 whitespace-nowrap',
                                    showFilters
                                        ? 'bg-slate-100 border-slate-300 text-slate-900'
                                        : 'bg-white border-gray-300 text-slate-600 hover:border-slate-900 hover:text-slate-900'
                                ]">
                                    <AdjustmentsHorizontalIcon class="w-4 h-4" />
                                    <span>Filtros</span>
                                </button>
                                <button type="submit"
                                    class="bg-slate-900 text-white px-8 py-3 rounded-sm text-xs font-bold uppercase tracking-widest hover:bg-slate-800 transition shadow-sm">
                                    Buscar
                                </button>
                            </div>
                        </div>

                        <!-- Panel expandible de filtros -->
                        <div v-show="showFilters"
                            class="grid grid-cols-1 gap-6 pt-6 mt-6 border-t border-gray-100 animate-fade-in-down">

                            <!-- Filtros tradicionales -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-xs font-bold uppercase tracking-wide text-slate-500 mb-2">
                                        Región
                                    </label>
                                    <select v-model="filterForm.region"
                                        class="w-full rounded-sm border-gray-300 text-sm focus:border-slate-900 focus:ring-0 bg-gray-50">
                                        <option value="all">Todas las regiones</option>
                                        <option v-for="region in regions" :key="region" :value="region">
                                            {{ region }}
                                        </option>
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-xs font-bold uppercase tracking-wide text-slate-500 mb-2">
                                        Evento
                                    </label>
                                    <select v-model="filterForm.event"
                                        class="w-full rounded-sm border-gray-300 text-sm focus:border-slate-900 focus:ring-0 bg-gray-50">
                                        <option value="">Todos los eventos</option>
                                        <option v-for="event in events" :key="event.id" :value="event.id">
                                            {{ event.name }}
                                        </option>
                                    </select>
                                </div>
                            </div>

                            <!-- Separador -->
                            <div class="relative">
                                <div class="absolute inset-0 flex items-center">
                                    <div class="w-full border-t border-gray-200"></div>
                                </div>
                                <div class="relative flex justify-center">
                                    <span
                                        class="bg-white px-4 text-xs font-bold uppercase tracking-widest text-slate-400">
                                        O usa búsqueda inteligente
                                    </span>
                                </div>
                            </div>


                            <div class="bg-white border border-zinc-200 p-8 shadow-sm ">
                                <div class="flex flex-col gap-2 mb-8 border-b border-zinc-100 pb-6">
                                    <div class="flex items-center justify-between">
                                        <div class="p-2 border border-zinc-200 inline-flex">
                                            <SparklesIcon class="w-5 h-5 text-black stroke-1" />
                                        </div>
                                        <span
                                            class="text-[10px] font-bold tracking-[0.2em] uppercase text-zinc-400 border border-zinc-200 px-2 py-1">
                                            AI Beta v1.0
                                        </span>
                                    </div>
                                    <div>
                                        <h3 class="font-sans text-2xl text-black mt-4 mb-2">
                                            Reconocimiento Facial
                                        </h3>
                                        <p class="text-xs font-light text-zinc-500 leading-relaxed">
                                            Algoritmo de detección biométrica. Subí una referencia visual para indexar y
                                            localizar coincidencias en la base de datos.
                                        </p>
                                    </div>
                                </div>

                                <div v-if="isLoadingModels" class="py-12 flex flex-col items-center justify-center">
                                    <div
                                        class="w-6 h-6 border-2 border-zinc-200 border-t-black rounded-full animate-spin mb-4">
                                    </div>
                                    <p class="text-xs font-bold tracking-widest text-zinc-500 uppercase animate-pulse">
                                        {{ progressMessage }}
                                    </p>
                                </div>

                                <div v-else>
                                    <div v-if="!previewUrl"
                                        class="group relative border border-dashed border-zinc-300 hover:border-black transition-colors duration-300 cursor-pointer bg-zinc-50/50 hover:bg-white p-10 text-center"
                                        @click="$refs.faceFileInput.click()">

                                        <div
                                            class="absolute top-2 right-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                            <span class="text-[10px] text-zinc-400 uppercase">Clic para subir</span>
                                        </div>

                                        <FaceSmileIcon
                                            class="w-10 h-10 text-zinc-300 group-hover:text-black transition-colors stroke-1 mx-auto mb-4" />

                                        <p class="font-sans text-lg text-black mb-1">
                                            Seleccionar Referencia
                                        </p>
                                        <p class="text-[10px] uppercase tracking-widest text-zinc-400">
                                            JPG, PNG • Alta Resolución
                                        </p>

                                        <input ref="faceFileInput" type="file" accept="image/*" class="hidden"
                                            @change="handleFileSelect">
                                    </div>

                                    <div v-else class="space-y-6">
                                        <div class="relative group">
                                            <div class="absolute -inset-1 border border-zinc-100 bg-zinc-50"></div>
                                            <img :src="previewUrl" alt="Preview"
                                                class="relative w-full h-64 object-contain bg-zinc-50 border border-zinc-200 p-2 grayscale hover:grayscale-0 transition-all duration-500">

                                            <button type="button"
                                                @click="selectedFile = null; previewUrl = null; errorMessage = ''"
                                                class="absolute top-4 right-4 bg-white border border-zinc-200 p-2 hover:bg-black hover:text-white hover:border-black transition-colors z-10">
                                                <XMarkIcon class="w-4 h-4 stroke-1" />
                                            </button>
                                        </div>

                                        <button type="button" @click="performFaceSearch" :disabled="isSearching"
                                            class="w-full bg-black text-white py-4 text-xs font-bold tracking-[0.2em] uppercase hover:bg-zinc-800 transition-colors disabled:opacity-50 disabled:cursor-not-allowed border border-black flex items-center justify-center gap-3">
                                            <div v-if="isSearching"
                                                class="w-3 h-3 border border-white border-t-transparent rounded-full animate-spin">
                                            </div>
                                            <SparklesIcon v-else class="w-4 h-4 stroke-1" />
                                            <span>{{ isSearching ? 'Analizando...' : 'Iniciar Escaneo' }}</span>
                                        </button>
                                    </div>

                                    <div v-if="progressMessage" class="mt-4 text-center border-t border-zinc-100 pt-4">
                                        <p class="text-xs font-mono text-zinc-500">{{ progressMessage }}</p>
                                    </div>

                                    <div v-if="errorMessage"
                                        class="mt-4 p-4 bg-zinc-50 border border-red-900/20 text-center">
                                        <p class="text-xs text-red-900 font-medium">{{ errorMessage }}</p>
                                    </div>

                                    <div class="mt-8 pt-6 border-t border-zinc-200">
                                        <div class="flex items-center gap-2 mb-3">
                                            <div class="w-1 h-1 bg-black rounded-full"></div>
                                            <p class="text-[10px] font-bold tracking-widest uppercase text-zinc-900">
                                                Parámetros de Entrada</p>
                                        </div>
                                        <ul class="grid grid-cols-2 gap-2">
                                            <li class="text-[10px] text-zinc-500 font-light flex items-center gap-2">
                                                <CheckIcon class="w-3 h-3 text-zinc-300" /> Iluminación frontal
                                            </li>
                                            <li class="text-[10px] text-zinc-500 font-light flex items-center gap-2">
                                                <CheckIcon class="w-3 h-3 text-zinc-300" /> Sin accesorios
                                            </li>
                                            <li class="text-[10px] text-zinc-500 font-light flex items-center gap-2">
                                                <CheckIcon class="w-3 h-3 text-zinc-300" /> Rostro descubierto
                                            </li>
                                            <li class="text-[10px] text-zinc-500 font-light flex items-center gap-2">
                                                <CheckIcon class="w-3 h-3 text-zinc-300" /> Foco nítido
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>



                            <!--  NUEVO: Búsqueda por DORSAL -->
                            <div class="bg-white border border-blue-200 p-8 shadow-sm">
                                <div class="flex flex-col gap-2 mb-8 border-b border-blue-100 pb-6">
                                    <div class="flex items-center justify-between">
                                        <div class="p-2 border border-blue-200 inline-flex bg-blue-50">
                                            <HashtagIcon class="w-5 h-5 text-blue-600 stroke-1" />
                                        </div>
                                        <span
                                            class="text-[10px] font-bold tracking-[0.2em] uppercase text-blue-400 border border-blue-200 px-2 py-1 bg-blue-50">
                                            OCR v2.0
                                        </span>
                                    </div>
                                    <div>
                                        <h3 class="font-sans text-2xl text-black mt-4 mb-2">
                                            Búsqueda por Dorsal
                                        </h3>
                                        <p class="text-xs font-light text-zinc-500 leading-relaxed">
                                            Sistema de reconocimiento óptico. Ingresá tu número de competidor para
                                            localizar todas tus fotografías del evento.
                                        </p>
                                    </div>
                                </div>

                                <div class="space-y-6">
                                    <div>
                                        <label
                                            class="block text-xs font-bold uppercase tracking-widest text-zinc-900 mb-3">
                                            Número de Dorsal
                                        </label>
                                        <input v-model="bibNumber" type="text" placeholder="Ej: 529, 1234..."
                                            class="w-full px-4 py-4 border-2 border-zinc-200 focus:border-blue-500 focus:ring-0 text-lg font-mono text-center bg-zinc-50"
                                            @keyup.enter="performBibSearch" />
                                        <p class="mt-2 text-[10px] text-zinc-400 uppercase tracking-widest text-center">
                                            Solo números, sin letras
                                        </p>
                                    </div>

                                    <button type="button" @click="performBibSearch"
                                        :disabled="isSearchingBib || !bibNumber.trim()"
                                        class="w-full bg-blue-600 text-white py-4 text-xs font-bold tracking-[0.2em] uppercase hover:bg-blue-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed border border-blue-600 flex items-center justify-center gap-3">
                                        <div v-if="isSearchingBib"
                                            class="w-3 h-3 border border-white border-t-transparent rounded-full animate-spin">
                                        </div>
                                        <HashtagIcon v-else class="w-4 h-4 stroke-1" />
                                        <span>{{ isSearchingBib ? 'Buscando...' : 'Buscar Dorsal' }}</span>
                                    </button>

                                    <div v-if="bibErrorMessage" class="p-4 bg-red-50 border border-red-200 text-center">
                                        <p class="text-xs text-red-900 font-medium">{{ bibErrorMessage }}</p>
                                    </div>

                                    <div class="pt-6 border-t border-blue-200">
                                        <div class="flex items-center gap-2 mb-3">
                                            <div class="w-1 h-1 bg-blue-600 rounded-full"></div>
                                            <p class="text-[10px] font-bold tracking-widest uppercase text-zinc-900">
                                                Recomendaciones
                                            </p>
                                        </div>
                                        <ul class="space-y-2">
                                            <li class="text-[10px] text-zinc-500 font-light flex items-start gap-2">
                                                <CheckIcon class="w-3 h-3 text-blue-300 flex-shrink-0 mt-0.5" />
                                                Ingresa el número exacto que usaste
                                            </li>
                                            <li class="text-[10px] text-zinc-500 font-light flex items-start gap-2">
                                                <CheckIcon class="w-3 h-3 text-blue-300 flex-shrink-0 mt-0.5" />
                                                Sin ceros a la izquierda (529, no 0529)
                                            </li>
                                            <li class="text-[10px] text-zinc-500 font-light flex items-start gap-2">
                                                <CheckIcon class="w-3 h-3 text-blue-300 flex-shrink-0 mt-0.5" />
                                                Verifica que esté bien visible en las fotos
                                            </li>
                                            <li class="text-[10px] text-zinc-500 font-light flex items-start gap-2">
                                                <CheckIcon class="w-3 h-3 text-blue-300 flex-shrink-0 mt-0.5" />
                                                Solo números detectados por IA
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>



                            <!-- Botón limpiar filtros -->
                            <div class="flex justify-end">
                                <button type="button" @click="clearFilters"
                                    class="text-xs font-bold uppercase tracking-widest text-red-600 hover:text-red-800 flex items-center gap-1 transition border-b border-transparent hover:border-red-600 pb-0.5">
                                    <XMarkIcon class="w-4 h-4" />
                                    Limpiar Todo
                                </button>
                            </div>
                        </div>
                    </form>
                </div>



                <!-- Ordenamiento -->
                <div class="flex flex-col md:flex-row justify-between items-center mb-8 border-b border-gray-200 pb-2">
                    <div class="text-sm text-slate-500 mb-4 md:mb-0 md:hidden">
                        {{ showingFaceResults ? faceSearchResults.count : (showingBibResults ? bibSearchResults.count :
                            photos.total) }} resultados
                    </div>
                    <div class="flex gap-6 overflow-x-auto pb-2 md:pb-0 w-full md:w-auto">
                        <button v-for="option in sortOptions" :key="option.value" @click="changeSort(option.value)"
                            :class="[
                                'text-xs font-bold uppercase tracking-widest pb-3 transition border-b-2 whitespace-nowrap',
                                filterForm.sort === option.value
                                    ? 'text-slate-900 border-slate-900'
                                    : 'text-slate-400 border-transparent hover:text-slate-600'
                            ]">
                            {{ option.label }}
                        </button>
                    </div>
                </div>

                <!-- Grid de fotos -->
                <div v-if="allPhotos.length > 0" :key="gridKey">
                    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-6 mb-12">
                        <Link v-for="photo in allPhotos" :key="photo.id" :href="route('gallery.show', photo.unique_id)"
                            class="group bg-white border border-gray-100 hover:border-gray-300 transition-all duration-300 hover:shadow-lg rounded-sm overflow-hidden flex flex-col">

                            <div class="aspect-square bg-gray-100 relative overflow-hidden">
                                <img :src="photo.watermarked_url || photo.thumbnail_url" :alt="photo.unique_id"
                                    class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105 filter grayscale-[0.3] group-hover:grayscale-0"
                                    loading="lazy" @error="handleImageError" />

                                <div
                                    class="absolute top-2 right-2 bg-white/95 backdrop-blur-sm border border-gray-200 px-2 py-1 text-[10px] font-bold text-slate-900 shadow-sm">
                                    ${{ photo.price }}
                                </div>

                                <!-- Badge de similitud -->
                                <div v-if="showingFaceResults && photo.similarity"
                                    class="absolute top-0 left-0 bg-black/90 backdrop-blur-sm border-r border-b border-white/20 px-3 py-2 z-10">
                                    <div class="flex items-center gap-2">
                                        <div class="w-1.5 h-1.5 bg-white rounded-full animate-pulse"></div>

                                        <span class="font-mono text-xs text-white tracking-[0.1em]">
                                            {{ Math.round(photo.similarity * 100) }}%
                                            <span
                                                class="text-zinc-500 ml-1 font-sans text-[9px] font-bold tracking-widest uppercase">COINCIDENCIA</span>
                                        </span>
                                    </div>
                                </div>


                                <div v-if="showingBibResults && photo.bib_numbers"
                                    class="absolute top-0 left-0 bg-gradient-to-br from-blue-600 to-cyan-600 backdrop-blur-sm border-r border-b border-white/20 px-3 py-2 z-10">
                                    <div class="flex items-center gap-2">
                                        <HashtagIcon class="w-3 h-3 text-white stroke-2" />
                                        <span class="font-mono text-xs text-white tracking-[0.1em] font-bold">
                                            {{ photo.bib_numbers.join(', ') }}
                                        </span>
                                    </div>
                                </div>

                            </div>

                            <div class="p-4 bg-white border-t border-gray-50 flex flex-col items-start">
                                <span
                                    class="text-[10px] uppercase tracking-widest text-slate-400 font-bold truncate w-full">
                                    {{ photo.unique_id }}
                                </span>
                                <span
                                    class="text-xs font-sans font-bold text-slate-900 truncate w-full mt-1 group-hover:text-blue-900 transition-colors">
                                    {{ photo.photographer }}
                                </span>
                            </div>
                        </Link>
                    </div>

                    <!-- Paginación -->
                    <div v-if="nextUrl && !showingFaceResults"
                        class="flex justify-center pt-8 border-t border-gray-100">
                        <button @click="loadMore" :disabled="loadingMore"
                            class="px-8 py-3 bg-white border border-slate-900 text-slate-900 text-xs font-bold uppercase tracking-widest hover:bg-slate-900 hover:text-white transition-all duration-300 rounded-sm disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2">
                            <svg v-if="loadingMore" class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg"
                                fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                    stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                </path>
                            </svg>
                            <span v-else>Cargar Más</span>
                        </button>
                    </div>
                </div>

                <!-- Sin resultados -->
                <div v-else class="bg-white border border-dashed border-gray-300 p-24 text-center rounded-sm">
                    <div class="w-16 h-16 mx-auto text-gray-200 mb-6">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-sans text-slate-900 mb-2">Sin resultados</h3>
                    <p class="text-slate-500 font-light mb-8 max-w-md mx-auto">
                        No hemos encontrado fotografías que coincidan con sus criterios de búsqueda actuales.
                    </p>
                    <button @click="clearFilters"
                        class="px-6 py-2 border border-slate-900 text-slate-900 text-xs font-bold uppercase tracking-widest hover:bg-slate-900 hover:text-white transition-colors">
                        Ver todo el catálogo
                    </button>
                </div>

            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
@keyframes fadeInDown {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }

    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.animate-fade-in-down {
    animation: fadeInDown 0.3s ease-out;
}
</style>
