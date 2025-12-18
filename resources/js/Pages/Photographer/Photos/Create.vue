<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';
import {
    CloudArrowUpIcon,
    XMarkIcon,
    PhotoIcon,
    InformationCircleIcon,
    HashtagIcon // Importante para el editor de dorsales
} from '@heroicons/vue/24/outline';
import { useToast } from '@/Composables/useToast';

// --- IMPORTS DE IA ---
import * as faceapi from 'face-api.js';
import '@tensorflow/tfjs-backend-webgl';
import Tesseract from 'tesseract.js';

const props = defineProps({
    events: Array,
    errors: Object,
});

const form = useForm({
    photos: [],
    price: 5.00,
    event_id: null,
    is_active: true,
    face_data: null, // Campo para enviar los metadatos
});

const { success, error } = useToast();

// Estado de Archivos
const selectedFiles = ref([]);
const dragOver = ref(false);
const uploading = ref(false);
const uploadProgress = ref({ current: 0, total: 0, percentage: 0 });
const fileInput = ref(null);

// Estado de IA
const modelsLoaded = ref(false);
const processingFaces = ref(false);
const processingBibs = ref(false);
const faceDetectionResults = ref([]);
const bibDetectionResults = ref([]);

// ----------------------------------------------------------------------
// 1. CARGA DE MODELOS (Igual que antes)
// ----------------------------------------------------------------------
onMounted(async () => {
    try {
        if (!faceapi) throw new Error('face-api.js no disponible');

        await faceapi.tf.setBackend('webgl');
        await faceapi.tf.ready();

        const MODEL_URL = '/models';
        await Promise.all([
            faceapi.nets.ssdMobilenetv1.loadFromUri(MODEL_URL),
            faceapi.nets.faceLandmark68Net.loadFromUri(MODEL_URL),
            faceapi.nets.faceRecognitionNet.loadFromUri(MODEL_URL),
        ]);

        modelsLoaded.value = true;
    } catch (err) {
        console.error('Error cargando modelos:', err);
        // Fallback a CPU si falla WebGL
        try {
            await faceapi.tf.setBackend('cpu');
            await faceapi.tf.ready();
            modelsLoaded.value = true;
        } catch (e) { console.error('Error fatal IA:', e); }
    }
});

// ----------------------------------------------------------------------
// 2. MANEJO DE ARCHIVOS (Modificado para soportar Async/Await)
// ----------------------------------------------------------------------
const handleFileSelect = (event) => {
    const files = Array.from(event.target.files);
    addFiles(files);
};

const handleDrop = (event) => {
    dragOver.value = false;
    const files = Array.from(event.dataTransfer.files);
    addFiles(files);
};

const addFiles = async (files) => {
    // Validaciones
    const validFiles = files.filter(file => {
        const validTypes = ['image/jpeg', 'image/jpg', 'image/png'];
        const maxSize = 10 * 1024 * 1024; // 10MB
        if (!validTypes.includes(file.type)) {
            error(`${file.name} no es un formato válido.`);
            return false;
        }
        if (file.size > maxSize) {
            error(`${file.name} excede el límite.`);
            return false;
        }
        return true;
    });

    const remainingSlots = 50 - selectedFiles.value.length;
    const filesToAdd = validFiles.slice(0, remainingSlots);

    if (validFiles.length > remainingSlots) {
        error(`Límite de 50 fotos. Se agregaron ${remainingSlots}.`);
    }

    // Convertir a Promesas para esperar a que todas se lean
    const readingPromises = filesToAdd.map(file => {
        return new Promise((resolve) => {
            const reader = new FileReader();
            reader.onload = (e) => {
                resolve({
                    file: file,
                    name: file.name,
                    preview: e.target.result,
                });
            };
            reader.readAsDataURL(file);
        });
    });

    // Esperar a que se generen las previews
    const newFileObjects = await Promise.all(readingPromises);
    selectedFiles.value.push(...newFileObjects);

    // DISPARAR DETECCIÓN DE IA AUTOMÁTICAMENTE
    if (modelsLoaded.value) {
        runAIDetection();
    }
};

const removeFile = (index) => {
    selectedFiles.value.splice(index, 1);
    // Sincronizar arrays de IA eliminando el índice correspondiente
    // Nota: Esto es simple, en producción idealmente recalcularíamos por ID único
    if (faceDetectionResults.value[index]) faceDetectionResults.value.splice(index, 1);
    if (bibDetectionResults.value[index]) bibDetectionResults.value.splice(index, 1);
};

const clearFiles = () => {
    selectedFiles.value = [];
    faceDetectionResults.value = [];
    bibDetectionResults.value = [];
    if (fileInput.value) fileInput.value.value = '';
};

// ----------------------------------------------------------------------
// 3. LÓGICA DE DETECCIÓN (Copiada y adaptada a selectedFiles)
// ----------------------------------------------------------------------

const runAIDetection = async () => {
    // Limpiamos resultados previos para re-procesar todo (o podríamos procesar solo nuevos)
    // Para simplificar, reprocesamos el array actual que coincida con selectedFiles
    processingFaces.value = true;
    await detectFacesInImages();
    processingFaces.value = false;

    processingBibs.value = true;
    await detectBibNumbers(faceDetectionResults.value);
    processingBibs.value = false;
};

// --- DETECCIÓN DE ROSTROS ---
const detectFacesInImages = async () => {
    faceDetectionResults.value = []; // Reset o merge logic needed if appending

    for (let i = 0; i < selectedFiles.value.length; i++) {
        try {
            const img = document.createElement('img');
            img.src = selectedFiles.value[i].preview;

            await new Promise(r => { img.onload = r; });

            const detections = await faceapi
                .detectAllFaces(img, new faceapi.SsdMobilenetv1Options({ minConfidence: 0.5 }))
                .withFaceLandmarks()
                .withFaceDescriptors();

            const descriptors = detections.map(d => Array.from(d.descriptor));
            const allBoxes = detections.map(d => d.detection.box);

            faceDetectionResults.value.push({
                index: i,
                count: detections.length,
                descriptors: descriptors,
                boxes: allBoxes
            });
        } catch (error) {
            console.error(`Error IA foto ${i}:`, error);
            faceDetectionResults.value.push({ index: i, count: 0, descriptors: [], boxes: [] });
        }
    }
};

// --- DETECCIÓN DE DORSALES (OCR) ---
const detectBibNumbers = async (facesData) => {
    bibDetectionResults.value = [];

    const worker = await Tesseract.createWorker('eng');
    await worker.setParameters({
        tessedit_char_whitelist: '0123456789',
        tessedit_pageseg_mode: Tesseract.PSM.SPARSE_TEXT,
    });

    for (let i = 0; i < selectedFiles.value.length; i++) {
        try {
            const faceInfo = facesData.find(f => f.index === i);
            const boxes = (faceInfo && faceInfo.boxes) ? faceInfo.boxes : [];
            let uniqueNumbers = new Set();

            if (boxes.length > 0) {
                // Procesar cada persona detectada
                for (const faceBox of boxes) {
                    const roiDataUrl = await cropTorsoFromFace(selectedFiles.value[i].preview, faceBox);
                    const cleanedDataUrl = await preprocessForOCR(roiDataUrl);
                    const result = await worker.recognize(cleanedDataUrl);
                    const found = result.data.text.match(/\d+/g);
                    if (found) found.forEach(num => { if (num.length >= 2) uniqueNumbers.add(num); });
                }
            } else {
                // Fallback sin rostros
                const roiDataUrl = await cropTorsoFromFace(selectedFiles.value[i].preview, null);
                const cleanedDataUrl = await preprocessForOCR(roiDataUrl);
                const result = await worker.recognize(cleanedDataUrl);
                const found = result.data.text.match(/\d+/g);
                if (found) found.forEach(num => { if (num.length >= 2) uniqueNumbers.add(num); });
            }

            bibDetectionResults.value.push({
                index: i,
                numbers: Array.from(uniqueNumbers),
                raw_text: '',
            });

        } catch (error) {
            console.error(`Error OCR foto ${i}:`, error);
            bibDetectionResults.value.push({ index: i, numbers: [], raw_text: '' });
        }
    }
    await worker.terminate();
};

// --- UTILIDADES DE IMAGEN (Iguales) ---
const cropTorsoFromFace = async (imageUrl, faceBox) => {
    return new Promise((resolve) => {
        const img = new Image();
        img.onload = () => {
            const canvas = document.createElement('canvas');
            const ctx = canvas.getContext('2d');
            let rx, ry, rw, rh;

            if (faceBox) {
                rw = faceBox.width * 2.2;
                rh = faceBox.height * 2.0;
                rx = faceBox.x - (rw - faceBox.width) / 2;
                ry = faceBox.y + (faceBox.height * 1.8);
            } else {
                rw = img.width * 0.5; rh = img.height * 0.4;
                rx = (img.width - rw) / 2; ry = img.height * 0.35;
            }

            rx = Math.max(0, rx); ry = Math.max(0, ry);
            rw = Math.min(rw, img.width - rx); rh = Math.min(rh, img.height - ry);

            canvas.width = rw * 2; canvas.height = rh * 2; // Upscale
            ctx.drawImage(img, rx, ry, rw, rh, 0, 0, canvas.width, canvas.height);
            resolve(canvas.toDataURL());
        };
        img.src = imageUrl;
    });
};

const preprocessForOCR = async (imageUrl) => {
    return new Promise((resolve) => {
        const img = new Image();
        img.onload = () => {
            const canvas = document.createElement('canvas');
            canvas.width = img.width + 40; canvas.height = img.height + 40;
            const ctx = canvas.getContext('2d');
            ctx.fillStyle = "#FFFFFF"; ctx.fillRect(0, 0, canvas.width, canvas.height);
            ctx.drawImage(img, 20, 20);

            const imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);
            const data = imageData.data;
            // Canal Verde
            for (let i = 0; i < data.length; i += 4) {
                const g = data[i + 1];
                const val = g > 130 ? 255 : 0;
                data[i] = data[i + 1] = data[i + 2] = val;
            }
            ctx.putImageData(imageData, 0, 0);
            resolve(canvas.toDataURL());
        };
        img.src = imageUrl;
    });
};

// --- EDICIÓN MANUAL DE DORSALES ---
const addBibTag = (index, event) => {
    const val = event.target.value.trim();
    if (!val) return;
    if (!bibDetectionResults.value[index]) bibDetectionResults.value[index] = { index, numbers: [] };
    if (!bibDetectionResults.value[index].numbers.includes(val)) bibDetectionResults.value[index].numbers.push(val);
    event.target.value = '';
};

const removeBibTag = (index, numberToRemove) => {
    if (bibDetectionResults.value[index]) {
        bibDetectionResults.value[index].numbers = bibDetectionResults.value[index].numbers.filter(n => n !== numberToRemove);
    }
};

const handleBackspace = (index, event) => {
    if (event.target.value === '' && bibDetectionResults.value[index]?.numbers?.length > 0) {
        bibDetectionResults.value[index].numbers.pop();
    }
};

// ----------------------------------------------------------------------
// 4. SUBMIT (Unificación de datos)
// ----------------------------------------------------------------------
const submitPhotos = () => {
    if (selectedFiles.value.length === 0) return error('Seleccione al menos una foto.');
    if (!form.price || form.price <= 0) return error('Establezca un precio válido.');

    uploading.value = true;
    uploadProgress.value = { current: 0, total: selectedFiles.value.length, percentage: 0 };

    //  PREPARAR DATOS DE ROSTROS
    const facesData = selectedFiles.value.map((_, index) => {
        const faceResult = faceDetectionResults.value[index];
        return {
            index: index,
            count: faceResult ? faceResult.count : 0,
            descriptors: faceResult ? faceResult.descriptors : [],
            boxes: faceResult ? faceResult.boxes : [],
        };
    });

    //  PREPARAR DATOS DE DORSALES
    const bibsData = selectedFiles.value.map((_, index) => {
        const bibResult = bibDetectionResults.value[index];
        return {
            index: index,
            numbers: bibResult ? bibResult.numbers : [],
            raw_text: bibResult ? bibResult.raw_text : '',
        };
    });

    const formData = new FormData();

    // Agregar archivos
    selectedFiles.value.forEach((item, index) => {
        formData.append(`photos[${index}]`, item.file);
    });

    // Agregar parámetros básicos
    formData.append('price', form.price);
    formData.append('is_active', form.is_active ? 1 : 0);
    if (form.event_id) formData.append('event_id', form.event_id);

    //  ADJUNTAR JSON CON LA ESTRUCTURA CORRECTA
    formData.append('face_data', JSON.stringify({
        faces: facesData,
        bibs: bibsData
    }));

    // Debug: Ver qué se está enviando
    console.log('📤 Enviando datos:', {
        fotos: selectedFiles.value.length,
        event_id: form.event_id,
        price: form.price,
        faces_detectadas: facesData.filter(f => f.count > 0).length,
        dorsales_detectados: bibsData.filter(b => b.numbers.length > 0).length,
    });

    router.post(route('photographer.photos.store'), formData, {
        forceFormData: true,
        preserveScroll: true,
        onProgress: (progress) => {
            uploadProgress.value.percentage = Math.round(progress.percentage);
        },
        onSuccess: () => {
            uploading.value = false;
            clearFiles();
            success('Material cargado exitosamente.');
        },
        onError: (err) => {
            uploading.value = false;
            console.error('❌ Error en carga:', err);
            error('Hubo un error en la carga.');
        },
    });
};

const formatDate = (date) => {
    if (!date) return '';
    return new Date(date).toLocaleDateString('es-ES', { year: 'numeric', month: 'short', day: 'numeric' });
};
</script>

<template>

    <Head title="Carga de Material" />

    <AuthenticatedLayout>
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

                <div
                    class="mb-10 border-b border-gray-200 pb-6 flex flex-col md:flex-row md:items-end justify-between gap-4">
                    <div>
                        <Link :href="route('photographer.photos.index')"
                            class="text-[10px] font-bold uppercase tracking-widest text-slate-400 hover:text-slate-900 mb-2 block transition-colors">
                            ← Volver al Archivo
                        </Link>
                        <h1 class="text-3xl font-sans font-bold text-slate-900">
                            Carga de Material
                        </h1>
                        <p class="text-sm text-slate-500 font-light mt-1">Gestión de activos digitales y asignación de
                            precios.</p>
                    </div>
                </div>

                <form @submit.prevent="submitPhotos" class="grid grid-cols-1 lg:grid-cols-3 gap-8">

                    <div class="lg:col-span-1 space-y-6">
                        <div class="bg-white border border-gray-200 rounded-sm p-6 shadow-sm">
                            <h2
                                class="text-xs font-bold uppercase tracking-widest text-slate-900 mb-6 border-b border-gray-100 pb-2">
                                Parámetros de Venta
                            </h2>

                            <div class="space-y-6">
                                <div>
                                    <label
                                        class="block text-xs font-bold uppercase tracking-wide text-slate-500 mb-2">Precio
                                        Unitario (USD)</label>
                                    <input v-model="form.price" type="number" step="0.01" min="0.01" required
                                        class="w-full border-gray-300 rounded-sm focus:border-slate-900 focus:ring-0 text-slate-900 font-mono"
                                        placeholder="0.00">
                                    <p v-if="errors.price" class="text-red-600 text-xs mt-1">{{ errors.price }}</p>
                                </div>

                                <div>
                                    <label
                                        class="block text-xs font-bold uppercase tracking-wide text-slate-500 mb-2">Asignar
                                        a Evento</label>
                                    <select v-model="form.event_id"
                                        class="w-full border-gray-300 rounded-sm focus:border-slate-900 focus:ring-0 text-sm text-slate-700 bg-white">
                                        <option :value="null">-- Sin asignar --</option>
                                        <option v-for="event in events" :key="event.id" :value="event.id">
                                            {{ event.name }}
                                        </option>
                                    </select>
                                    <p class="text-[10px] text-slate-400 mt-1">Opcional. Agrupa las fotos para facilitar
                                        la búsqueda.</p>
                                </div>

                                <div class="flex items-start pt-2">
                                    <div class="flex items-center h-5">
                                        <input id="is_active" v-model="form.is_active" type="checkbox"
                                            class="focus:ring-0 h-4 w-4 text-slate-900 border-gray-300 rounded-sm">
                                    </div>
                                    <div class="ml-3 text-sm">
                                        <label for="is_active" class="font-medium text-slate-700">Publicación
                                            Inmediata</label>
                                        <p class="text-xs text-slate-500">Las fotos serán visibles en la galería al
                                            terminar la carga.</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="bg-gray-50 border border-gray-200 p-5 rounded-sm">
                            <div class="flex items-start gap-3">
                                <InformationCircleIcon class="w-5 h-5 text-slate-400 flex-shrink-0" />
                                <div>
                                    <h4 class="text-xs font-bold uppercase tracking-widest text-slate-700 mb-2">
                                        Protección de Activos</h4>
                                    <p class="text-xs text-slate-500 font-light leading-relaxed">
                                        El sistema aplicará automáticamente marcas de agua a las vistas previas. Los
                                        archivos originales se almacenan en servidores seguros y solo se liberan tras la
                                        confirmación del pago.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="lg:col-span-2">

                        <div @dragover.prevent="dragOver = true" @dragleave.prevent="dragOver = false"
                            @drop.prevent="handleDrop" :class="[
                                'border border-dashed rounded-sm p-12 text-center transition-all duration-300 flex flex-col items-center justify-center min-h-[300px]',
                                dragOver
                                    ? 'border-slate-900 bg-slate-50'
                                    : 'border-gray-300 bg-white hover:border-slate-400'
                            ]">
                            <input type="file" ref="fileInput" @change="handleFileSelect" multiple accept="image/*"
                                class="hidden">

                            <div v-if="selectedFiles.length === 0" class="flex flex-col items-center">
                                <CloudArrowUpIcon class="w-12 h-12 text-slate-300 mb-4 stroke-1" />
                                <h3 class="text-lg font-sans font-medium text-slate-900 mb-2">
                                    Arrastre sus archivos acá
                                </h3>
                                <p class="text-sm text-slate-500 mb-6 font-light">
                                    o haga clic para explorar su dispositivo
                                </p>
                                <button type="button" @click="$refs.fileInput.click()"
                                    class="px-6 py-2 border border-slate-300 text-slate-600 text-xs font-bold uppercase tracking-widest hover:border-slate-900 hover:text-slate-900 transition rounded-sm">
                                    Seleccionar
                                </button>
                                <div
                                    class="mt-8 text-[10px] uppercase tracking-widest text-slate-400 border-t border-gray-100 pt-4 w-full">
                                    JPG, PNG • Max 10MB • Límite 50
                                </div>
                            </div>

                            <div v-else class="w-full">
                                <div class="flex justify-between items-center mb-6 border-b border-gray-100 pb-4">
                                    <span class="text-sm font-bold text-slate-900">{{ selectedFiles.length }} archivos
                                        listos</span>
                                    <div class="flex gap-4">
                                        <button type="button" @click="$refs.fileInput.click()"
                                            class="text-xs font-bold uppercase tracking-widest text-slate-500 hover:text-slate-900">Agregar
                                            más</button>
                                        <button type="button" @click="clearFiles"
                                            class="text-xs font-bold uppercase tracking-widest text-red-600 hover:text-red-800">Limpiar
                                            todo</button>
                                    </div>
                                </div>

                                <div v-if="processingFaces || processingBibs"
                                    class="mb-4 bg-blue-50 border border-blue-200 p-3 rounded-sm flex items-center gap-3">
                                    <div
                                        class="animate-spin rounded-full h-4 w-4 border-2 border-blue-600 border-t-transparent">
                                    </div>
                                    <span class="text-xs text-blue-700 font-medium">
                                        Analizando imágenes ({{ selectedFiles.length }} archivos)...
                                    </span>
                                </div>

                                <div
                                    class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3 max-h-[600px] overflow-y-auto pr-2 custom-scrollbar">
                                    <div v-for="(file, index) in selectedFiles" :key="index"
                                        class="relative group aspect-square bg-gray-100 border border-gray-200 overflow-hidden">

                                        <img :src="file.preview"
                                            class="w-full h-full object-cover filter grayscale-[0.1] group-hover:grayscale-0 transition duration-300">

                                        <div v-if="faceDetectionResults[index]"
                                            class="absolute top-2 right-2 px-2 py-1 text-[10px] font-bold shadow-lg z-10 transition-opacity"
                                            :class="faceDetectionResults[index].count > 0 ? 'bg-white text-black' : 'bg-gray-400 text-white'">
                                            <span v-if="faceDetectionResults[index].count > 0">
                                                {{ faceDetectionResults[index].count }} Cara(s)
                                            </span>
                                            <span v-else>—</span>
                                        </div>

                                        <div
                                            class="absolute bottom-0 left-0 right-0 p-1.5 bg-gradient-to-t from-black/90 via-black/60 to-transparent transition-all duration-300 hover:bg-slate-900/95 z-20 group/edit">
                                            <div class="flex flex-wrap gap-1 items-center">
                                                <HashtagIcon class="w-3 h-3 text-white/40 shrink-0" />

                                                <template v-if="bibDetectionResults[index]?.numbers?.length">
                                                    <div v-for="number in bibDetectionResults[index].numbers"
                                                        :key="number"
                                                        class="bg-white/10 hover:bg-white/20 text-white text-[10px] font-mono px-1.5 py-0.5 rounded-sm flex items-center gap-1 border border-white/10 backdrop-blur-sm">
                                                        <span>{{ number }}</span>
                                                        <button type="button" @click.stop="removeBibTag(index, number)"
                                                            class="text-white/50 hover:text-red-400 focus:outline-none">
                                                            <XMarkIcon class="w-3 h-3" />
                                                        </button>
                                                    </div>
                                                </template>

                                                <input type="text" placeholder="+"
                                                    @keydown.enter.prevent="addBibTag(index, $event)"
                                                    @keydown.backspace="handleBackspace(index, $event)"
                                                    class="flex-1 min-w-[30px] bg-transparent border-none text-white text-[11px] font-bold p-0 focus:ring-0 placeholder-white/30 focus:placeholder-white/50 outline-none h-5" />
                                            </div>
                                        </div>

                                        <div
                                            class="absolute inset-0 bg-slate-900/0 group-hover:bg-slate-900/10 transition-colors flex items-center justify-center pointer-events-none">
                                            <button type="button" @click="removeFile(index)"
                                                class="opacity-0 group-hover:opacity-100 bg-red-600 text-white p-2 rounded-full hover:bg-red-700 transition-all pointer-events-auto shadow-lg transform scale-90 hover:scale-100">
                                                <XMarkIcon class="w-5 h-5" />
                                            </button>
                                        </div>

                                        <div v-if="(processingFaces && !faceDetectionResults[index]) || (processingBibs && !bibDetectionResults[index])"
                                            class="absolute inset-0 bg-black/20 flex items-center justify-center z-30">
                                            <div
                                                class="animate-spin rounded-full h-6 w-6 border-2 border-white border-t-transparent">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div v-if="uploading" class="mt-6 p-6 bg-slate-50 border border-gray-200 rounded-sm">
                            <div
                                class="flex justify-between text-xs font-bold uppercase tracking-widest text-slate-500 mb-2">
                                <span>Procesando</span>
                                <span>{{ uploadProgress.percentage }}%</span>
                            </div>
                            <div class="w-full bg-gray-200 h-1 rounded-full overflow-hidden">
                                <div class="bg-slate-900 h-1 transition-all duration-300 ease-out"
                                    :style="{ width: uploadProgress.percentage + '%' }"></div>
                            </div>
                            <p class="text-xs text-slate-400 mt-2 text-center font-light">Por favor no cierre esta
                                ventana.</p>
                        </div>

                        <div class="mt-6 flex justify-end">
                            <button type="button" @click="submitPhotos"
                                :disabled="uploading || selectedFiles.length === 0" :class="[
                                    'px-8 py-4 text-xs font-bold uppercase tracking-widest transition-all duration-300 rounded-sm w-full md:w-auto',
                                    uploading || selectedFiles.length === 0
                                        ? 'bg-gray-200 text-gray-400 cursor-not-allowed'
                                        : 'bg-slate-900 text-white hover:bg-slate-800 shadow-md hover:shadow-lg'
                                ]">
                                <span v-if="uploading">Subiendo...</span>
                                <span v-else>Iniciar Carga</span>
                            </button>
                        </div>

                        <div v-if="Object.keys(errors).length > 0"
                            class="mt-6 p-4 border border-red-200 bg-red-50 rounded-sm">
                            <div class="flex gap-3">
                                <XMarkIcon class="w-5 h-5 text-red-500" />
                                <div>
                                    <h4 class="text-xs font-bold text-red-700 uppercase tracking-wide mb-1">Error en la
                                        carga</h4>
                                    <ul class="list-disc list-inside text-xs text-red-600 font-light">
                                        <li v-for="(error, key) in errors" :key="key">{{ error }}</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
/* Scrollbar fina para la grilla de preview */
.custom-scrollbar::-webkit-scrollbar {
    width: 4px;
}

.custom-scrollbar::-webkit-scrollbar-track {
    background: #f1f1f1;
}

.custom-scrollbar::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 2px;
}

.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
}
</style>