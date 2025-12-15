<script setup>
import { ref, onMounted } from 'vue';
import { XMarkIcon, MagnifyingGlassIcon, SparklesIcon } from '@heroicons/vue/24/outline';
import * as faceapi from 'face-api.js';

const emit = defineEmits(['close', 'results']);

const isLoadingModels = ref(true);
const isSearching = ref(false);
const selectedFile = ref(null);
const previewUrl = ref(null);
const errorMessage = ref('');
const progressMessage = ref('Cargando modelos de IA...');

// Cargar modelos de Face-API
onMounted(async () => {
    try {
        const MODEL_URL = '/models'; // Asume que están en public/models
        
        progressMessage.value = 'Cargando detector de rostros...';
        await faceapi.nets.ssdMobilenetv1.loadFromUri(MODEL_URL);
        
        progressMessage.value = 'Cargando reconocimiento facial...';
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

    // Validar tipo
    if (!file.type.startsWith('image/')) {
        errorMessage.value = 'Por favor selecciona una imagen válida';
        return;
    }

    // Validar tamaño (max 10MB)
    if (file.size > 10 * 1024 * 1024) {
        errorMessage.value = 'La imagen es demasiado grande. Máximo 10MB';
        return;
    }

    selectedFile.value = file;
    errorMessage.value = '';

    // Crear preview
    const reader = new FileReader();
    reader.onload = (e) => {
        previewUrl.value = e.target.result;
    };
    reader.readAsDataURL(file);
};

// Realizar búsqueda
const performSearch = async () => {
    if (!selectedFile.value) {
        errorMessage.value = 'Por favor selecciona una foto';
        return;
    }

    isSearching.value = true;
    errorMessage.value = '';
    progressMessage.value = 'Detectando rostro en tu foto...';

    try {
        // Cargar imagen
        const img = await faceapi.bufferToImage(selectedFile.value);
        
        // Detectar rostro
        progressMessage.value = 'Analizando características faciales...';
        const detection = await faceapi
            .detectSingleFace(img)
            .withFaceLandmarks()
            .withFaceDescriptor();

        if (!detection) {
            errorMessage.value = 'No se detectó ningún rostro en la imagen. Intenta con otra foto donde tu rostro sea visible.';
            isSearching.value = false;
            progressMessage.value = '';
            return;
        }

        // Extraer descriptor
        const descriptor = Array.from(detection.descriptor);

        // Enviar al servidor
        progressMessage.value = 'Buscando en toda la galería...';
        
        const response = await axios.post(route('gallery.face-search'), {
            face_descriptor: descriptor,
            threshold: 0.6
        });

        if (response.data.success) {
            emit('results', response.data);
            emit('close');
        }

    } catch (error) {
        console.error('Error en búsqueda:', error);
        errorMessage.value = error.response?.data?.message || 'Error al buscar. Intenta nuevamente.';
    } finally {
        isSearching.value = false;
        progressMessage.value = '';
    }
};

const clearSelection = () => {
    selectedFile.value = null;
    previewUrl.value = null;
    errorMessage.value = '';
};
</script>

<template>
    <!-- Overlay -->
    <div class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-center justify-center p-4"
         @click.self="$emit('close')">
        
        <!-- Modal -->
        <div class="bg-white rounded-lg shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
            
            <!-- Header -->
            <div class="sticky top-0 bg-white border-b border-gray-200 px-6 py-4 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-gradient-to-br from-purple-500 to-pink-500 rounded-lg flex items-center justify-center">
                        <SparklesIcon class="w-6 h-6 text-white" />
                    </div>
                    <div>
                        <h2 class="text-xl font-bold text-slate-900">Búsqueda por cara</h2>
                        <p class="text-xs text-slate-500">Encuentra todas tus fotos en segundos</p>
                    </div>
                </div>
                <button @click="$emit('close')" 
                        class="p-2 hover:bg-gray-100 rounded-lg transition">
                    <XMarkIcon class="w-6 h-6 text-slate-600" />
                </button>
            </div>

            <!-- Body -->
            <div class="p-6">
                
                <!-- Loading Models -->
                <div v-if="isLoadingModels" class="text-center py-12">
                    <div class="animate-spin w-12 h-12 border-4 border-purple-500 border-t-transparent rounded-full mx-auto mb-4"></div>
                    <p class="text-sm text-slate-600">{{ progressMessage }}</p>
                </div>

                <!-- Interface -->
                <div v-else>
                    
                    <!-- Instrucciones -->
                    <div class="bg-gradient-to-r from-purple-50 to-pink-50 border border-purple-200 rounded-lg p-4 mb-6">
                        <h3 class="font-semibold text-slate-900 mb-2 flex items-center gap-2">
                            <SparklesIcon class="w-5 h-5 text-purple-600" />
                            ¿Cómo funciona?
                        </h3>
                        <ol class="text-sm text-slate-700 space-y-1 ml-7 list-decimal">
                            <li>Subí una foto tuya donde tu rostro sea visible</li>
                            <li>Nuestra IA analizará tus características faciales</li>
                            <li>Buscaremos en <strong>toda la galería</strong></li>
                            <li>¡Te mostraremos todas las fotos donde apareces!</li>
                        </ol>
                    </div>

                    <!-- Upload Area -->
                    <div v-if="!previewUrl" 
                         class="border-2 border-dashed border-gray-300 rounded-lg p-12 text-center hover:border-purple-400 transition cursor-pointer"
                         @click="$refs.fileInput.click()">
                        <MagnifyingGlassIcon class="w-16 h-16 text-gray-300 mx-auto mb-4" />
                        <p class="text-slate-900 font-semibold mb-1">Subí tu foto</p>
                        <p class="text-sm text-slate-500 mb-4">Click aquí o arrastra una imagen</p>
                        <input ref="fileInput"
                               type="file"
                               accept="image/*"
                               class="hidden"
                               @change="handleFileSelect">
                        <button type="button"
                                class="bg-purple-600 text-white px-6 py-2 rounded-lg text-sm font-semibold hover:bg-purple-700 transition">
                            Seleccionar Foto
                        </button>
                    </div>

                    <!-- Preview -->
                    <div v-else class="space-y-4">
                        <div class="relative">
                            <img :src="previewUrl" 
                                 alt="Preview" 
                                 class="w-full max-h-96 object-contain rounded-lg border border-gray-200">
                            <button @click="clearSelection"
                                    class="absolute top-2 right-2 bg-white/90 backdrop-blur-sm p-2 rounded-lg hover:bg-white transition shadow-lg">
                                <XMarkIcon class="w-5 h-5 text-slate-600" />
                            </button>
                        </div>

                        <button @click="performSearch"
                                :disabled="isSearching"
                                class="w-full bg-gradient-to-r from-purple-600 to-pink-600 text-white py-4 rounded-lg font-semibold hover:from-purple-700 hover:to-pink-700 transition disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2">
                            <SparklesIcon v-if="!isSearching" class="w-5 h-5" />
                            <div v-else class="animate-spin w-5 h-5 border-2 border-white border-t-transparent rounded-full"></div>
                            <span>{{ isSearching ? 'Buscando...' : 'Buscar Mis Fotos' }}</span>
                        </button>
                    </div>

                    <!-- Progress Message -->
                    <div v-if="progressMessage" class="mt-4 text-center">
                        <p class="text-sm text-purple-600 animate-pulse">{{ progressMessage }}</p>
                    </div>

                    <!-- Error Message -->
                    <div v-if="errorMessage" 
                         class="mt-4 bg-red-50 border border-red-200 rounded-lg p-4">
                        <p class="text-sm text-red-800">{{ errorMessage }}</p>
                    </div>

                    <!-- Tips -->
                    <div class="mt-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
                        <p class="text-xs font-semibold text-blue-900 mb-2"> Consejos para mejores resultados:</p>
                        <ul class="text-xs text-blue-800 space-y-1 ml-4 list-disc">
                            <li>Usa una foto reciente y clara</li>
                            <li>Asegúrate que tu rostro esté bien iluminado</li>
                            <li>Evita lentes de sol o sombreros que cubran tu cara</li>
                            <li>La foto debe mostrar tu rostro de frente</li>
                        </ul>
                    </div>

                </div>
            </div>

        </div>
    </div>
</template>
