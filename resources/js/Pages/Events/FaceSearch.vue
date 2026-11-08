<script setup>
import { ref } from 'vue';
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
    InformationCircleIcon
} from '@heroicons/vue/24/outline';

const props = defineProps({
    event: Object,
});

const uploadedImage = ref(null);
const uploadedImageUrl = ref(null);
const searching = ref(false);
const results = ref([]);
const faceError = ref(null);
const processingStage = ref('');


function handleImageSelect(event) {
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
    faceError.value = null;
    processingStage.value = '';

    const reader = new FileReader();
    reader.onload = (e) => uploadedImageUrl.value = e.target.result;
    reader.readAsDataURL(file);
}


async function searchByFace() {
    if (!uploadedImage.value) return;

    searching.value = true;
    processingStage.value = 'searching';
    faceError.value = null;


    const formData = new FormData();
    formData.append('selfie', uploadedImage.value);

    try {

        const response = await axios.post(
            route('events.face-search.submit', props.event.slug),
            formData,
            { headers: { 'Content-Type': 'multipart/form-data' } }
        );

        results.value = response.data.results || [];
        processingStage.value = 'done';

        if (results.value.length === 0) {
            faceError.value = 'No se encontraron coincidencias exactas en este evento.';
        }
    } catch (error) {
        console.error('Error:', error);
        faceError.value = error.response?.data?.error || 'Error al procesar la búsqueda en el servidor.';
        processingStage.value = 'done';
    } finally {
        searching.value = false;
    }
}

function resetSearch() {
    uploadedImage.value = null;
    uploadedImageUrl.value = null;
    results.value = [];
    processingStage.value = '';
    faceError.value = null;
    document.getElementById('face-upload-input').value = '';
}
</script>

<template>
    <Head :title="`Escáner Facial - ${event.name}`" />

    <AppLayout>
        <div class="min-h-screen bg-[#F8F9FA] text-slate-800 font-sans selection:bg-[#E30613] selection:text-white pb-24 pt-24 md:pt-28">
            <div class="max-w-[1500px] mx-auto px-4 sm:px-6 lg:px-8">
                
                <div class="mb-10">
                    <Link :href="route('events.show', event.slug)" class="inline-flex items-center gap-2 bg-white px-4 py-2 rounded-full text-xs font-bold uppercase tracking-wider text-gray-500 hover:text-black hover:shadow-sm border border-gray-200 transition-all mb-6">
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

                <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12">
                

                    <div class="lg:col-span-4 flex flex-col gap-6">
                        <div class="bg-white border border-gray-100 rounded p-6 md:p-8 shadow-sm sticky top-28">
                            <h3 class="text-xs font-bold uppercase tracking-widest text-gray-400 mb-6 flex items-center gap-2 border-b border-gray-100 pb-4">
                                <span class="w-4 h-px bg-gray-200"></span> Imagen de Referencia
                            </h3>

                            <div v-if="uploadedImageUrl" class="mb-6 relative rounded overflow-hidden border border-gray-200 shadow-inner group bg-gray-50 aspect-square">
                                <img :src="uploadedImageUrl" id="uploaded-preview" class="w-full h-full object-cover" />
                                <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                                    <button @click="resetSearch" class="bg-white text-black hover:bg-[#E30613] hover:text-white px-5 py-2.5 rounded-full font-bold text-xs uppercase tracking-wider flex items-center gap-2 shadow-sm transition-colors">
                                        <XMarkIcon class="w-4 h-4" /> Cambiar Foto
                                    </button>
                                </div>
                            </div>

                            <div v-else class="border-2 border-dashed border-gray-300 hover:border-black bg-gray-50 hover:bg-gray-100 rounded p-8 text-center transition-all duration-300 mb-6 group cursor-pointer relative aspect-square flex flex-col items-center justify-center">
                                <input type="file" @change="handleImageSelect" accept="image/*" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10" id="face-upload-input" />
                                <CloudArrowUpIcon class="w-12 h-12 text-gray-400 group-hover:text-black transition-colors mb-4" />
                                <h4 class="font-bold text-sm text-slate-800 mb-2 group-hover:text-black">Subí una selfie</h4>
                                <p class="text-[10px] font-bold uppercase tracking-wider text-gray-400">JPG o PNG (Máx 5MB)</p>
                            </div>

                            <button v-if="uploadedImageUrl" @click="searchByFace" :disabled="searching" class="w-full bg-black text-white font-bold text-xs uppercase tracking-wider py-4 rounded-full hover:bg-[#E30613] hover:shadow-lg hover:shadow-red-500/30 transition-all flex items-center justify-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed group">
                                <MagnifyingGlassIcon v-if="!searching" class="w-4 h-4 group-hover:-rotate-12 transition-transform" />
                                <div v-if="searching" class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin"></div>
                                <span>{{ searching ? 'Buscando rostros...' : 'Iniciar Escáner' }}</span>
                            </button>

                            <div class="mt-8 bg-blue-50 rounded p-5">
                                <p class="text-xs font-bold text-blue-800 uppercase tracking-wider mb-3 flex items-center gap-1.5"><InformationCircleIcon class="w-4 h-4" /> Consejos</p>
                                <ul class="space-y-2 text-xs font-medium text-blue-700/80">
                                    <li class="flex items-start gap-2"><span class="text-blue-500">•</span> Subí una foto de frente.</li>
                                    <li class="flex items-start gap-2"><span class="text-blue-500">•</span> Buena iluminación natural.</li>
                                    <li class="flex items-start gap-2"><span class="text-[#E30613] font-bold">×</span> Evitá lentes de sol.</li>
                                </ul>
                            </div>
                        </div>
                    </div>


                    <div class="lg:col-span-8">
                        <div v-if="faceError" class="mb-6 bg-red-50 border border-red-100 text-[#E30613] px-5 py-4 rounded text-sm font-bold flex items-center gap-3 shadow-sm">
                            <ExclamationTriangleIcon class="w-6 h-6 shrink-0" /> {{ faceError }}
                        </div>

                        <div v-if="results.length > 0" class="flex flex-col sm:flex-row sm:items-end justify-between border-b border-gray-200 pb-4 mb-8 gap-4">
                            <div>
                                <h2 class="font-flux text-3xl md:text-4xl text-black">Coincidencias <span class="text-[#E30613]">detectadas</span></h2>
                            </div>
                            <div class="bg-gray-100 text-slate-700 px-4 py-2 rounded-full text-xs font-bold uppercase tracking-wider shadow-inner w-max">
                                {{ results.length }} {{ results.length === 1 ? 'Fotografía' : 'Fotografías' }}
                            </div>
                        </div>

                        <div v-if="results.length > 0" class="columns-2 md:columns-3 xl:columns-4 gap-4 space-y-4">
                            <Link v-for="result in results" :key="result.id" :href="route('gallery.show', result.unique_id)" class="break-inside-avoid block group relative bg-gray-100 rounded overflow-hidden border border-gray-200 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300 w-full h-auto">
                                <ProtectedImage :src="result.thumbnail_url" :alt="result.unique_id" class="w-full h-auto object-cover pointer-events-none" loading="lazy" />
                                <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none"></div>
                                <div class="absolute top-3 left-3 pointer-events-none">
                                    <span class="bg-white/90 backdrop-blur-md text-[#E30613] font-bold text-[10px] uppercase tracking-wider px-2.5 py-1 rounded-full shadow-sm flex items-center gap-1">
                                        Match {{ Math.round(result.similarity * 100) }}%
                                    </span>
                                </div>
                            </Link>
                        </div>

                        <div v-else-if="!uploadedImageUrl" class="flex flex-col items-center justify-center py-24 bg-white border border-gray-100 rounded shadow-sm h-full min-h-[400px]">
                            <div class="w-20 h-20 bg-gray-50 rounded-full flex items-center justify-center mb-6"><FaceSmileIcon class="w-10 h-10 text-gray-300" /></div>
                            <h3 class="font-flux text-3xl text-black mb-3">Esperando datos</h3>
                            <p class="text-sm font-medium text-gray-500 max-w-md text-center leading-relaxed">Subí una foto de referencia en el panel izquierdo para que nuestra IA encuentre todas las fotos en las que aparecés.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>