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
    CheckCircleIcon,
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
        console.error(' Error cargando modelos:', err);
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
            console.warn(` Se detectaron ${detections.length} rostros, usando el primero`);
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
        console.error(' Error:', error);
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
        console.error(' face-api.js no se cargó');
        faceError.value = 'Error: Sistema de Reconocimiento facial no disponible';
        return;
    }


    await loadModels();
});
</script>

<template>
    <Head :title="`Escáner Biométrico - ${event.name}`" />

    <AppLayout>
        <div class="min-h-screen bg-[#050505] text-white font-sans selection:bg-[#E30613] selection:text-white">

            <div class="border-b border-white/20 bg-[#050505]/90 backdrop-blur-sm sticky top-0 z-30 pt-16 md:pt-0">
                <div class="max-w-[1500px] mx-auto px-4 md:px-8 h-14 flex items-center justify-between font-mono text-[10px] uppercase tracking-widest">
                    <Link :href="route('events.show', event.slug)"
                        class="text-gray-400 hover:text-white flex items-center gap-3 transition-none border border-transparent hover:border-white px-3 py-1">
                        <ArrowLeftIcon class="w-3.5 h-3.5" /> [ CANCELAR ESCÁNER ]
                    </Link>
                    <span class="text-[#E30613] font-bold hidden sm:block">
                        // F33
                    </span>
                </div>
            </div>

            <div class="max-w-[1500px] mx-auto px-4 md:px-8 py-12 md:py-20">

                <div class="mb-12 border-b-[12px] border-white pb-8">
                    <span class="font-mono text-xs uppercase tracking-[0.45em] text-[#E30613] mb-4 block border-l-4 border-[#E30613] pl-3">
                         RECONOCIMIENTO FACIAL // {{ event.name }}
                    </span>
                    <h1 class="font-black text-6xl md:text-8xl lg:text-[9rem] leading-[0.85] tracking-tighter uppercase text-white font-bebas">
                        ESCÁNER<br><span class="text-[#E30613]">.</span>
                    </h1>
                </div>

                <div v-if="modelsLoading" class="bg-black border-2 border-white/20 p-4 mb-8 font-mono text-[10px] uppercase tracking-widest flex items-center gap-4">
                    <div class="animate-pulse bg-white text-black px-2 py-1 font-bold">PROCESANDO</div>
                    <span class="text-gray-400">CARGANDO MODELOS...</span>
                </div>

                <div v-else-if="modelsLoaded && !faceError" class="bg-black border-2 border-[#E30613] p-4 mb-8 font-mono text-[10px] uppercase tracking-widest flex items-center gap-4">
                    <div class="bg-[#E30613] text-black px-2 py-1 font-bold">ONLINE</div>
                    <span class="text-white">IA CARGADO. LISTO PARA RECIBIR DATOS.</span>
                </div>

                <div v-if="faceError" class="bg-[#09090b] border-[4px] border-[#E30613] p-4 mb-8 font-mono text-[10px] uppercase tracking-widest flex items-center gap-4">
                    <ExclamationTriangleIcon class="w-6 h-6 text-[#E30613] flex-shrink-0" />
                    <span class="text-[#E30613] font-bold">ERROR DE SISTEMA: {{ faceError }}</span>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 lg:gap-16">

                    <div class="lg:col-span-4 flex flex-col gap-6">
                        <div class="bg-[#09090b] border-[4px] border-white p-6 sticky top-24 shadow-[8px_8px_0_rgba(255,255,255,1)]">
                            
                            <h3 class="font-mono text-[10px] font-bold uppercase tracking-[0.2em] text-white mb-6 border-b border-white/20 pb-4">
                                [ FASE 1: ENTRADA ]
                            </h3>

                            <div v-if="uploadedImageUrl" class="mb-6 relative border-2 border-white bg-black p-1 group">
                                <img :src="uploadedImageUrl" id="uploaded-preview"
                                    class="w-full h-auto aspect-square object-cover grayscale contrast-125 group-hover:grayscale-0 transition-all duration-500" crossorigin="anonymous" />
                                
                                <div class="absolute inset-0 bg-gradient-to-t from-black/80 to-transparent pointer-events-none"></div>
                                
                                <button @click="resetSearch" class="absolute top-3 right-3 bg-black border border-white text-white hover:bg-[#E30613] hover:border-[#E30613] font-mono text-[9px] uppercase tracking-widest px-2 py-1 transition-none z-10">
                                    [ PURGAR ]
                                </button>
                                
                                <div class="absolute bottom-3 left-3 text-[#E30613] font-mono text-[9px] font-bold tracking-widest pointer-events-none">
                                    > IDENTIFICADO
                                </div>
                            </div>

                            <div v-else class="border-2 border-dashed border-white/30 hover:border-white bg-black p-8 text-center transition-none mb-6 group cursor-pointer relative overflow-hidden">
                                <input type="file" @change="handleImageSelect" accept="image/*" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10" id="face-upload-input" />
                                <PhotoIcon class="w-12 h-12 text-white/50 mx-auto mb-4 group-hover:text-white transition-none" />
                                <h4 class="font-sans font-black text-xl uppercase tracking-tighter text-white mb-2 group-hover:text-[#E30613]">SELECCIONAR FOTOGRAFÍA</h4>
                                <p class="font-mono text-[9px] uppercase tracking-widest text-gray-500">JPG, PNG / MÁXIMO 5MB</p>
                            </div>

                            <button v-if="uploadedImageUrl" @click="searchByFace"
                                :disabled="searching || modelsLoading || !modelsLoaded"
                                class="w-full bg-[#E30613] border-[4px] border-[#E30613] text-black font-black text-sm uppercase tracking-[0.25em] py-5 hover:bg-white hover:border-white transition-none flex items-center justify-center gap-3 disabled:opacity-30 disabled:cursor-not-allowed group">
                                
                                <MagnifyingGlassIcon v-if="!searching && !modelsLoading" class="w-5 h-5" />
                                <div v-if="searching || modelsLoading" class="w-4 h-4 bg-black animate-ping"></div>

                                <span v-if="processingStage === 'detecting'">ANALIZANDO MATRIZ...</span>
                                <span v-else-if="processingStage === 'searching'">COMPARANDO REGISTROS...</span>
                                <span v-else-if="modelsLoading">SISTEMA OFFLINE</span>
                                <span v-else>EJECUTAR ESCÁNER</span>
                            </button>

                            <div class="mt-8 bg-black border border-white/10 p-4 font-mono text-[9px] tracking-widest uppercase text-gray-500 space-y-2">
                                <p class="text-white border-b border-white/10 pb-2 mb-2 font-bold">> PARÁMETROS ÓPTIMOS_</p>
                                <p>+ ROSTRO VISIBLE Y FRONTAL</p>
                                <p>+ ILUMINACIÓN NATURAL</p>
                                <p>+ UN SOLO SUJETO EN CÁMARA</p>
                                <p class="text-[#E30613]">- EVITAR GAFAS O ACCESORIOS</p>
                            </div>
                        </div>
                    </div>

                    <div class="lg:col-span-8">
                        
                        <div v-if="results.length > 0" class="flex flex-col md:flex-row md:items-end justify-between border-b border-white/20 pb-4 mb-8">
                            <div>
                                <h2 class="font-sans font-black text-4xl md:text-5xl uppercase tracking-tighter text-white">
                                    COINCIDENCIAS <span class="text-[#E30613]">DETECTADAS</span>
                                </h2>
                            </div>
                            <div class="font-mono text-[10px] font-bold uppercase tracking-widest text-[#E30613] bg-[#E30613]/10 border border-[#E30613] px-3 py-1.5 w-max mt-4 md:mt-0">
                                {{ results.length }} {{ results.length === 1 ? 'REGISTRO' : 'REGISTROS' }}
                            </div>
                        </div>

                        <div v-if="results.length > 0" class="columns-2 md:columns-3 xl:columns-4 gap-2 space-y-2 masonry-grid">
                            <Link v-for="result in results" :key="result.id"
                                :href="route('gallery.show', result.unique_id)"
                                class="break-inside-avoid block group relative bg-[#09090b] overflow-hidden border-[4px] border-black hover:border-[#E30613] transition-none w-full h-auto">

                                <div class="relative w-full h-auto">
                                    <ProtectedImage :src="result.thumbnail_url" :alt="result.unique_id"
                                        class="w-full h-auto object-cover grayscale opacity-70 group-hover:grayscale-0 group-hover:opacity-100 transition-none pointer-events-none"
                                        loading="lazy" />

                                    <div class="absolute inset-0 bg-[#E30613] mix-blend-overlay opacity-0 group-hover:opacity-20 transition-none pointer-events-none"></div>

                                    <div class="absolute top-2 left-2 pointer-events-none">
                                        <span class="bg-[#E30613] text-black font-mono text-[9px] font-bold uppercase tracking-widest px-2 py-1 shadow-lg block">
                                            MATCH: {{ Math.round(result.similarity * 100) }}%
                                        </span>
                                    </div>

                                    <div class="absolute bottom-2 right-2 bg-black text-white px-2 py-1 text-[9px] font-mono font-bold uppercase tracking-widest border border-white/20 opacity-0 group-hover:opacity-100 transition-none pointer-events-none">
                                        [ INSPECCIONAR ]
                                    </div>
                                </div>
                            </Link>
                        </div>

                        <div v-else-if="!uploadedImageUrl" class="flex flex-col items-center justify-center py-32 border-4 border-dashed border-white/10 bg-black text-center h-full">
                            <MagnifyingGlassIcon class="w-16 h-16 text-gray-800 mb-6" />
                            <h3 class="font-black font-sans text-4xl md:text-6xl text-gray-600 tracking-tighter mb-4 uppercase">ESPERANDO DATOS.</h3>
                            <p class="font-mono text-xs text-gray-500 tracking-widest uppercase">
                                INGRESÁ UN PATRÓN FACIAL EN EL PANEL IZQUIERDO PARA INICIAR EL ESCÁNER.
                            </p>
                        </div>

                        <div v-else-if="processingStage === 'done' && results.length === 0" class="flex flex-col items-center justify-center py-32 border-4 border-[#E30613] bg-black text-center h-full">
                            <ExclamationTriangleIcon class="w-16 h-16 text-[#E30613] mb-6" />
                            <h3 class="font-black font-sans text-4xl md:text-6xl text-white tracking-tighter mb-4 uppercase">BÚSQUEDA FALLIDA.</h3>
                            <p class="font-mono text-xs text-gray-400 tracking-widest uppercase mb-8 max-w-lg">
                                NO SE ENCONTRARON COINCIDENCIAS
                            </p>
                            <button @click="resetSearch" class="border-2 border-white bg-white text-black hover:bg-black hover:text-white px-8 py-3 font-mono text-[10px] font-bold uppercase tracking-widest transition-none">
                                [  REINTENTAR ]
                            </button>
                        </div>

                    </div>
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
    background: #E30613;
}
</style>