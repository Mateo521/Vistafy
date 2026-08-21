<script setup>
import { ref, onMounted } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import ProtectedImage from '@/Components/ProtectedImage.vue';
import axios from 'axios';
import {
    PhotoIcon,
    MagnifyingGlassIcon,
    XMarkIcon,
    ExclamationTriangleIcon,
    ArrowLeftIcon,
    CloudArrowUpIcon,
    FaceSmileIcon,
    CheckBadgeIcon,
    InformationCircleIcon
} from '@heroicons/vue/24/outline';

const props = defineProps({
    event: Object,
});

const modelsLoaded = ref(false);
const modelsLoading = ref(false);
const faceError = ref(null);
const uploadedImage = ref(null);
const uploadedImageUrl = ref(null);
const searching = ref(false);
const results = ref([]);
const faceDescriptor = ref(null);
const processingStage = ref('');

async function loadModels() {
    if (modelsLoaded.value) return;

    modelsLoading.value = true;

    try {
        if (typeof window.faceapi === 'undefined') {
            throw new Error('face-api.js no está disponible. Espera un momento...');
        }

        const MODEL_URL = '/models';

        await Promise.all([
            window.faceapi.nets.ssdMobilenetv1.loadFromUri(MODEL_URL),
            window.faceapi.nets.faceLandmark68Net.loadFromUri(MODEL_URL),
            window.faceapi.nets.faceRecognitionNet.loadFromUri(MODEL_URL),
        ]);

        modelsLoaded.value = true;

    } catch (err) {
        console.error('Error cargando modelos:', err);
        faceError.value = 'Error cargando modelos de IA: ' + err.message;
    } finally {
        modelsLoading.value = false;
    }
}

async function handleImageSelect(event) {
    const file = event.target.files[0];
    if (!file) return;

    if (!file.type.startsWith('image/')) {
        alert('Por favor selecciona una imagen');
        return;
    }

    if (file.size > 5 * 1024 * 1024) {
        alert('La imagen es muy grande. Máximo 5MB');
        return;
    }

    uploadedImage.value = file;
    results.value = [];
    faceDescriptor.value = null;
    faceError.value = null;
    processingStage.value = '';

    const reader = new FileReader();
    reader.onload = (e) => {
        uploadedImageUrl.value = e.target.result;
    };
    reader.readAsDataURL(file);
}

async function searchByFace() {
    if (!uploadedImageUrl.value) return;

    if (!modelsLoaded.value) {
        alert('Los modelos aún no están cargados. Espera un momento.');
        return;
    }

    searching.value = true;
    processingStage.value = 'detecting';
    faceError.value = null;

    try {
        const img = document.getElementById('uploaded-preview');

        if (!img || !img.complete) {
            throw new Error('La imagen no se cargó correctamente');
        }

        const detections = await window.faceapi
            .detectAllFaces(img, new window.faceapi.SsdMobilenetv1Options({ minConfidence: 0.5 }))
            .withFaceLandmarks()
            .withFaceDescriptors();

        if (detections.length === 0) {
            faceError.value = 'No se detectó ningún rostro en la imagen';
            alert('No se detectó ningún rostro. Intenta con otra foto más clara.');
            searching.value = false;
            processingStage.value = '';
            return;
        }

        if (detections.length > 1) {
            console.warn(`Se detectaron ${detections.length} rostros, usando el primero`);
        }

        faceDescriptor.value = Array.from(detections[0].descriptor);
        processingStage.value = 'searching';

        const response = await axios.post(
            route('events.face-search.submit', props.event.slug),
            {
                face_descriptor: faceDescriptor.value,
                threshold: 0.6
            }
        );

        results.value = response.data.results || [];
        processingStage.value = 'done';

        if (results.value.length === 0) {
            faceError.value = 'No se encontraron coincidencias';
        }

    } catch (error) {
        console.error('Error:', error);
        faceError.value = error.response?.data?.message || error.message || 'Error al procesar la búsqueda';
        alert('Error: ' + faceError.value);
        processingStage.value = '';
    } finally {
        searching.value = false;
    }
}

function resetSearch() {
    uploadedImage.value = null;
    uploadedImageUrl.value = null;
    results.value = [];
    faceDescriptor.value = null;
    processingStage.value = '';
    faceError.value = null;
}

onMounted(async () => {
    let attempts = 0;
    while (typeof window.faceapi === 'undefined' && attempts < 50) {
        await new Promise(resolve => setTimeout(resolve, 100));
        attempts++;
    }

    if (typeof window.faceapi === 'undefined') {
        console.error('face-api.js no se cargó');
        faceError.value = 'Error: Sistema de Reconocimiento facial no disponible';
        return;
    }

    await loadModels();
});
</script>

<template>
    <Head :title="`Escáner Facial - ${event.name} | F33`" />

    <AppLayout>
        <div class="min-h-screen bg-[#F8F9FA] text-slate-800 font-sans selection:bg-[#E30613] selection:text-white pb-24 pt-24 md:pt-28">

            <div class="max-w-[1500px] mx-auto px-4 sm:px-6 lg:px-8">
                
            
                <div class="mb-10">
                    <Link :href="route('events.show', event.slug)"
                        class="inline-flex items-center gap-2 bg-white px-4 py-2 rounded-full text-xs font-bold uppercase tracking-wider text-gray-500 hover:text-black hover:shadow-sm border border-gray-200 transition-all mb-6">
                        <ArrowLeftIcon class="w-4 h-4" /> Volver a la galería
                    </Link>
                    
                    <div class="flex items-center gap-3 mb-2">
                        <span class="text-xs font-bold text-gray-500 uppercase tracking-wider flex items-center gap-2">
                            <FaceSmileIcon class="w-4 h-4 text-[#E30613]" /> Escáner facial
                        </span>
                    </div>
                    <h1 class="font-flux text-5xl md:text-7xl text-black tracking-wide leading-none">
                        Búsqueda <span class="text-[#E30613]">biométrica</span>
                    </h1>
                </div>

            
                <div class="mb-8 max-w-xl">
                    <div v-if="modelsLoading" class="bg-blue-50 border border-blue-100 text-blue-700 px-5 py-3 rounded text-xs font-bold flex items-center gap-3 shadow-sm">
                        <div class="w-4 h-4 border-2 border-blue-300 border-t-blue-700 rounded-full animate-spin"></div>
                        Iniciando...
                    </div>

                    <div v-else-if="modelsLoaded && !faceError" class="bg-green-50 border border-green-100 text-green-700 px-5 py-3 rounded text-xs font-bold flex items-center gap-3 shadow-sm">
                        <CheckBadgeIcon class="w-5 h-5 text-green-500" />
                    Listo para recibir imágenes.
                    </div>

                    <div v-if="faceError" class="bg-red-50 border border-red-100 text-[#E30613] px-5 py-3 rounded text-xs font-bold flex items-center gap-3 shadow-sm">
                        <ExclamationTriangleIcon class="w-5 h-5 shrink-0" />
                        {{ faceError }}
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12">

                
                    <div class="lg:col-span-4 flex flex-col gap-6">
                        <div class="bg-white border border-gray-100 rounded p-6 md:p-8 shadow-sm sticky top-28">
                            
                            <h3 class="text-xs font-bold uppercase tracking-widest text-gray-400 mb-6 flex items-center gap-2 border-b border-gray-100 pb-4">
                                <span class="w-4 h-px bg-gray-200"></span> Imagen de Referencia
                            </h3>

                        
                            <div v-if="uploadedImageUrl" class="mb-6 relative rounded overflow-hidden border border-gray-200 shadow-inner group bg-gray-50 aspect-square">
                                <img :src="uploadedImageUrl" id="uploaded-preview"
                                    class="w-full h-full object-cover" crossorigin="anonymous" />
                                
                                <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                                    <button @click="resetSearch" class="bg-white text-black hover:bg-[#E30613] hover:text-white px-5 py-2.5 rounded-full font-bold text-xs uppercase tracking-wider flex items-center gap-2 shadow-sm transition-colors">
                                        <XMarkIcon class="w-4 h-4" /> Cambiar Foto
                                    </button>
                                </div>
                            </div>

                        
                            <div v-else class="border-2 border-dashed border-gray-300 hover:border-black bg-gray-50 hover:bg-gray-100 rounded p-8 text-center transition-all duration-300 mb-6 group cursor-pointer relative aspect-square flex flex-col items-center justify-center">
                                <input type="file" @change="handleImageSelect" accept="image/*" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10" id="face-upload-input" />
                                <CloudArrowUpIcon class="w-12 h-12 text-gray-400 group-hover:text-black transition-colors mb-4" />
                                <h4 class="font-bold text-sm text-slate-800 mb-2 group-hover:text-black">Sube una selfie</h4>
                                <p class="text-[10px] font-bold uppercase tracking-wider text-gray-400">JPG o PNG (Máx 5MB)</p>
                            </div>

                        
                            <button v-if="uploadedImageUrl" @click="searchByFace"
                                :disabled="searching || modelsLoading || !modelsLoaded"
                                class="w-full bg-black text-white font-bold text-xs uppercase tracking-wider py-4 rounded-full hover:bg-[#E30613] hover:shadow-lg hover:shadow-red-500/30 transition-all flex items-center justify-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed group">
                                
                                <MagnifyingGlassIcon v-if="!searching && !modelsLoading" class="w-4 h-4 group-hover:-rotate-12 transition-transform" />
                                <div v-if="searching || modelsLoading" class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin"></div>

                                <span v-if="processingStage === 'detecting'">Analizando rostro...</span>
                                <span v-else-if="processingStage === 'searching'">Buscando en galería...</span>
                                <span v-else-if="modelsLoading">Sistema cargando...</span>
                                <span v-else>Iniciar Escáner</span>
                            </button>

                        
                            <div class="mt-8 bg-blue-50 rounded p-5">
                                <p class="text-xs font-bold text-blue-800 uppercase tracking-wider mb-3 flex items-center gap-1.5">
                                    <InformationCircleIcon class="w-4 h-4" /> Consejos
                                </p>
                                <ul class="space-y-2 text-xs font-medium text-blue-700/80">
                                    <li class="flex items-start gap-2"><span class="text-blue-500">•</span> Subí una foto de frente.</li>
                                    <li class="flex items-start gap-2"><span class="text-blue-500">•</span> Buena iluminación natural.</li>
                                    <li class="flex items-start gap-2"><span class="text-blue-500">•</span> Evita fotos grupales.</li>
                                    <li class="flex items-start gap-2"><span class="text-[#E30613] font-bold">×</span> Evita lentes de sol.</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    
                    <div class="lg:col-span-8">
                        
                        
                        <div v-if="results.length > 0" class="flex flex-col sm:flex-row sm:items-end justify-between border-b border-gray-200 pb-4 mb-8 gap-4">
                            <div>
                                <h2 class="font-flux text-3xl md:text-4xl text-black">
                                    Coincidencias <span class="text-[#E30613]">detectadas</span>
                                </h2>
                            </div>
                            <div class="bg-gray-100 text-slate-700 px-4 py-2 rounded-full text-xs font-bold uppercase tracking-wider shadow-inner w-max">
                                {{ results.length }} {{ results.length === 1 ? 'Fotografía' : 'Fotografías' }}
                            </div>
                        </div>

                    
                        <div v-if="results.length > 0" class="columns-2 md:columns-3 xl:columns-4 gap-4 space-y-4">
                            <Link v-for="result in results" :key="result.id"
                                :href="route('gallery.show', result.unique_id)"
                                class="break-inside-avoid block group relative bg-gray-100 rounded overflow-hidden border border-gray-200 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300 w-full h-auto">

                                <ProtectedImage :src="result.thumbnail_url" :alt="result.unique_id"
                                    class="w-full h-auto object-cover pointer-events-none"
                                    loading="lazy" />

                                <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none"></div>

                            
                                <div class="absolute top-3 left-3 pointer-events-none">
                                    <span class="bg-white/90 backdrop-blur-md text-[#E30613] font-bold text-[10px] uppercase tracking-wider px-2.5 py-1 rounded-full shadow-sm flex items-center gap-1">
                                        Match {{ Math.round(result.similarity * 100) }}%
                                    </span>
                                </div>

                                <div class="absolute bottom-3 right-3 bg-white text-black px-3 py-1.5 rounded-full text-[10px] font-bold uppercase tracking-wider opacity-0 group-hover:opacity-100 transition-opacity shadow-sm flex items-center gap-1">
                                    Ver Foto <ArrowRightIcon class="w-3 h-3" />
                                </div>
                            </Link>
                        </div>

                    
                        <div v-else-if="!uploadedImageUrl" class="flex flex-col items-center justify-center py-24 bg-white border border-gray-100 rounded shadow-sm h-full min-h-[400px]">
                            <div class="w-20 h-20 bg-gray-50 rounded-full flex items-center justify-center mb-6">
                                <FaceSmileIcon class="w-10 h-10 text-gray-300" />
                            </div>
                            <h3 class="font-flux text-3xl text-black mb-3">Esperando datos</h3>
                            <p class="text-sm font-medium text-gray-500 max-w-md text-center leading-relaxed">
                                Subí una foto de referencia en el panel izquierdo para que nuestra IA encuentre todas las fotos en las que aparecés.
                            </p>
                        </div>

                    
                        <div v-else-if="processingStage === 'done' && results.length === 0" class="flex flex-col items-center justify-center py-24 bg-white border border-red-100 rounded shadow-sm h-full min-h-[400px]">
                            <div class="w-20 h-20 bg-red-50 rounded-full flex items-center justify-center mb-6">
                                <ExclamationTriangleIcon class="w-10 h-10 text-[#E30613]" />
                            </div>
                            <h3 class="font-flux text-3xl text-black mb-3">Búsqueda fallida</h3>
                            <p class="text-sm font-medium text-gray-500 mb-8 max-w-md text-center leading-relaxed">
                                No logramos encontrar coincidencias exactas en esta galería. Probá subiendo otra foto con mejor iluminación.
                            </p>
                            <button @click="resetSearch" class="bg-black text-white hover:bg-[#E30613] px-6 py-3 rounded-full text-xs font-bold uppercase tracking-wider transition-colors shadow-md">
                                Reintentar
                            </button>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>

::-webkit-scrollbar {
    width: 6px;
}
::-webkit-scrollbar-track {
    background: transparent;
}
::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 10px;
}
::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
}
</style>