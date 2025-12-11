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

//  DEFINIR FUNCIÓN ANTES DE onMounted
async function loadModels() {
    if (modelsLoaded.value) return;

    modelsLoading.value = true;
    
    try {
        console.log(' Cargando modelos de reconocimiento facial...');
        
        // Verificar que faceapi esté disponible
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
        console.log(' Modelos cargados correctamente');
    } catch (err) {
        console.error(' Error cargando modelos:', err);
        faceError.value = 'Error cargando modelos de IA: ' + err.message;
    } finally {
        modelsLoading.value = false;
    }
}

//  DEFINIR FUNCIONES AUXILIARES
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

        console.log('🔍 Detectando rostros...');

        const detections = await window.faceapi
            .detectAllFaces(img, new window.faceapi.SsdMobilenetv1Options({ minConfidence: 0.5 }))
            .withFaceLandmarks()
            .withFaceDescriptors();

        console.log(` Detectados ${detections.length} rostro(s)`);

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

        console.log('📡 Buscando coincidencias en el servidor...');

        const response = await axios.post(
            route('events.face-search.submit', props.event.slug),
            { 
                face_descriptor: faceDescriptor.value,
                threshold: 0.6
            }
        );

        results.value = response.data.results || [];
        processingStage.value = 'done';

        console.log(` Encontradas ${results.value.length} coincidencias`);

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

//  onMounted AL FINAL
onMounted(async () => {
    // Esperar a que AppLayout inicialice faceapi
    let attempts = 0;
    while (typeof window.faceapi === 'undefined' && attempts < 50) {
        await new Promise(resolve => setTimeout(resolve, 100));
        attempts++;
    }

    if (typeof window.faceapi === 'undefined') {
        console.error(' face-api.js no se cargó');
        faceError.value = 'Error: Sistema de reconocimiento facial no disponible';
        return;
    }

    console.log(' face-api.js disponible, cargando modelos...');
    await loadModels();
});
</script>

<template>
    <Head :title="`Buscar por Rostro - ${event.name}`" />

    <AppLayout>
        <div class="min-h-screen bg-gray-50 py-12">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                
                <!-- Header -->
                <div class="mb-8">
                    <Link 
                        :href="route('events.show', event.slug)"
                        class="text-sm text-slate-500 hover:text-slate-900 mb-4 inline-flex items-center gap-2"
                    >
                        ← Volver al evento
                    </Link>
                    <h1 class="text-4xl font-serif font-bold text-slate-900 mb-2">
                        Búsqueda por Reconocimiento Facial
                    </h1>
                    <p class="text-slate-600">
                        Sube una foto de tu rostro y encontraremos todas las fotos donde apareces en 
                        <strong>{{ event.name }}</strong>
                    </p>
                </div>

                <!-- Alert de modelos cargando -->
                <div v-if="modelsLoading" class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                    <div class="flex items-center gap-3">
                        <div class="animate-spin rounded-full h-5 w-5 border-2 border-blue-500 border-t-transparent"></div>
                        <span class="text-sm text-blue-800">Cargando modelos de inteligencia artificial...</span>
                    </div>
                </div>

                <!-- Modelos listos -->
                <div v-else-if="modelsLoaded" class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6">
                    <div class="flex items-center gap-3">
                        <CheckCircleIcon class="w-5 h-5 text-green-600" />
                        <span class="text-sm text-green-800">Sistema de reconocimiento facial listo</span>
                    </div>
                </div>

                <!-- Error -->
                <div v-if="faceError" class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
                    <div class="flex items-center gap-3">
                        <ExclamationTriangleIcon class="w-5 h-5 text-red-500" />
                        <span class="text-sm text-red-800">{{ faceError }}</span>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    
                    <!-- Panel de carga -->
                    <div class="lg:col-span-1">
                        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 sticky top-4">
                            <h3 class="text-lg font-bold text-slate-900 mb-4">1. Sube tu foto</h3>

                            <!-- Preview -->
                            <div v-if="uploadedImageUrl" class="mb-4 relative">
                                <img 
                                    :src="uploadedImageUrl" 
                                    id="uploaded-preview"
                                    class="w-full rounded-lg border border-gray-200"
                                    crossorigin="anonymous"
                                    @load="console.log(' Imagen cargada en el DOM')"
                                />
                                <button 
                                    @click="resetSearch"
                                    class="absolute top-2 right-2 bg-red-500 text-white p-2 rounded-full hover:bg-red-600 transition"
                                >
                                    <XMarkIcon class="w-4 h-4" />
                                </button>
                            </div>

                            <!-- Upload input -->
                            <div v-else class="border-2 border-dashed border-gray-300 rounded-lg p-8 text-center hover:border-slate-400 transition">
                                <input 
                                    type="file" 
                                    @change="handleImageSelect"
                                    accept="image/*"
                                    class="hidden"
                                    id="face-upload-input"
                                />
                                <label 
                                    for="face-upload-input"
                                    class="cursor-pointer flex flex-col items-center"
                                >
                                    <PhotoIcon class="w-12 h-12 text-gray-400 mb-3" />
                                    <span class="text-sm font-medium text-slate-900">Seleccionar imagen</span>
                                    <span class="text-xs text-slate-500 mt-1">JPG, PNG (máx. 5MB)</span>
                                </label>
                            </div>

                            <!-- Botón buscar -->
                            <button 
                                v-if="uploadedImageUrl"
                                @click="searchByFace"
                                :disabled="searching || modelsLoading || !modelsLoaded"
                                class="w-full mt-4 bg-slate-900 text-white py-3 rounded-lg font-medium hover:bg-slate-800 transition disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2"
                            >
                                <MagnifyingGlassIcon v-if="!searching" class="w-5 h-5" />
                                <div v-else class="animate-spin rounded-full h-5 w-5 border-2 border-white border-t-transparent"></div>
                                <span v-if="processingStage === 'detecting'">Detectando rostro...</span>
                                <span v-else-if="processingStage === 'searching'">Buscando coincidencias...</span>
                                <span v-else-if="modelsLoading">Cargando modelos...</span>
                                <span v-else>Buscar mis fotos</span>
                            </button>

                            <!-- Tips -->
                            <div class="mt-6 bg-gray-50 rounded-lg p-4">
                                <p class="text-xs font-bold uppercase tracking-wide text-slate-700 mb-2">
                                    💡 Consejos
                                </p>
                                <ul class="text-xs text-slate-600 space-y-1">
                                    <li>✓ Usa una foto clara de tu rostro</li>
                                    <li>✓ Evita fotos con muchas personas</li>
                                    <li>✓ Mejor con buena iluminación</li>
                                    <li>✓ Sin lentes oscuros o sombreros</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- Resultados -->
                    <div class="lg:col-span-2">
                        <div v-if="results.length > 0" class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                            <div class="flex items-center justify-between mb-6">
                                <h3 class="text-lg font-bold text-slate-900">
                                    Encontramos {{ results.length }} {{ results.length === 1 ? 'foto' : 'fotos' }}
                                </h3>
                                <div class="flex items-center gap-2 text-green-600">
                                    <CheckCircleIcon class="w-5 h-5" />
                                    <span class="text-sm font-medium">Búsqueda completada</span>
                                </div>
                            </div>

                            <!-- Grid de resultados -->
                            <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                                <Link 
                                    v-for="result in results" 
                                    :key="result.id"
                                    :href="route('gallery.show', result.unique_id)"
                                    class="group relative aspect-square bg-gray-100 rounded-lg overflow-hidden hover:ring-2 hover:ring-slate-900 transition"
                                >
                                    <ProtectedImage 
                                        :src="result.thumbnail_url"
                                        class="w-full h-full object-cover group-hover:scale-105 transition duration-300"
                                    />
                                    
                                    <!-- Badge de similitud -->
                                    <div class="absolute top-2 right-2 bg-black/70 text-white text-xs font-bold px-2 py-1 rounded-full">
                                        {{ Math.round(result.similarity * 100) }}%
                                    </div>

                                    <!-- Overlay -->
                                    <div class="absolute inset-0 bg-black/0 group-hover:bg-black/20 transition flex items-center justify-center">
                                        <span class="text-white text-sm font-bold opacity-0 group-hover:opacity-100 transition">
                                            Ver foto
                                        </span>
                                    </div>
                                </Link>
                            </div>
                        </div>

                        <!-- Estado vacío -->
                        <div v-else-if="!uploadedImageUrl" class="bg-white rounded-lg shadow-sm border border-gray-200 p-12 text-center">
                            <MagnifyingGlassIcon class="w-16 h-16 text-gray-300 mx-auto mb-4" />
                            <h3 class="text-lg font-medium text-slate-900 mb-2">
                                Comienza subiendo tu foto
                            </h3>
                            <p class="text-sm text-slate-500">
                                Usa el panel de la izquierda para cargar una imagen
                            </p>
                        </div>

                        <!-- Sin resultados -->
                        <div v-else-if="processingStage === 'done' && results.length === 0" class="bg-white rounded-lg shadow-sm border border-gray-200 p-12 text-center">
                            <ExclamationTriangleIcon class="w-16 h-16 text-amber-400 mx-auto mb-4" />
                            <h3 class="text-lg font-medium text-slate-900 mb-2">
                                No encontramos coincidencias
                            </h3>
                            <p class="text-sm text-slate-500 mb-4">
                                Intenta con otra foto o verifica que estés en el evento correcto
                            </p>
                            <button 
                                @click="resetSearch"
                                class="text-sm text-slate-600 hover:text-slate-900 underline"
                            >
                                Intentar con otra foto
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
