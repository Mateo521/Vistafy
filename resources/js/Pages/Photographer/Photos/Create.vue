<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { ref, onMounted, watch, computed } from 'vue';
import {
    CloudArrowUpIcon,
    XMarkIcon,
    PhotoIcon,
    InformationCircleIcon,
    HashtagIcon
} from '@heroicons/vue/24/outline';
import { useToast } from '@/Composables/useToast';


import * as faceapi from 'face-api.js';
import '@tensorflow/tfjs-backend-webgl';
import Tesseract from 'tesseract.js';

const props = defineProps({
    events: Array,
    eventRoles: {
        type: Object,
        default: () => ({})
    },
    errors: Object,
});

const form = useForm({
    photos: [],
    price: 5.00,
    event_id: null,
    location_role: '',
    is_active: true,
    read_bibs: true,
    face_data: null,
});
const { success, error } = useToast();

const isTypingCustomRole = ref(false);

const currentEventRoles = computed(() => {
    if (!form.event_id || !props.eventRoles) {
        return [];
    }

    const roles = props.eventRoles[form.event_id] || props.eventRoles[String(form.event_id)];

    return Array.isArray(roles) ? roles : [];
});

const selectedFiles = ref([]);
const dragOver = ref(false);
const uploading = ref(false);
const uploadProgress = ref({ current: 0, total: 0, percentage: 0 });
const fileInput = ref(null);


const modelsLoaded = ref(false);
const processingFaces = ref(false);
const processingBibs = ref(false);
const faceDetectionResults = ref([]);
const bibDetectionResults = ref([]);


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
        try {
            await faceapi.tf.setBackend('cpu');
            await faceapi.tf.ready();
            modelsLoaded.value = true;
        } catch (e) { console.error('Error fatal IA:', e); }
    }
});


const handleFileSelect = (event) => {
    const files = Array.from(event.target.files);
    addFiles(files);
};

const handleDrop = (event) => {
    dragOver.value = false;
    const files = Array.from(event.dataTransfer.files);
    addFiles(files);
};

const compressImage = async (file) => {
    return new Promise((resolve) => {
        const reader = new FileReader();
        reader.onload = (e) => {
            const img = new Image();
            img.onload = () => {
                const canvas = document.createElement('canvas');
                let width = img.width;
                let height = img.height;
                const maxSize = 2500;

                if (width > height && width > maxSize) {
                    height = Math.round((height * maxSize) / width);
                    width = maxSize;
                } else if (height > maxSize) {
                    width = Math.round((width * maxSize) / height);
                    height = maxSize;
                }

                canvas.width = width;
                canvas.height = height;
                const ctx = canvas.getContext('2d');
                ctx.drawImage(img, 0, 0, width, height);

                canvas.toBlob((blob) => {
                    const compressedFile = new File([blob], file.name.replace(/\.[^/.]+$/, "") + ".jpg", {
                        type: 'image/jpeg',
                        lastModified: Date.now()
                    });

                    resolve({
                        file: compressedFile,
                        name: compressedFile.name,
                        preview: canvas.toDataURL('image/jpeg', 0.8)
                    });
                }, 'image/jpeg', 0.8);
            };
            img.src = e.target.result;
        };
        reader.readAsDataURL(file);
    });
};

const addFiles = async (files) => {
    const validFiles = files.filter(file => {
        const validTypes = ['image/jpeg', 'image/jpg', 'image/png'];
        const maxSize = 50 * 1024 * 1024;
        if (!validTypes.includes(file.type)) {
            error(`${file.name} no es un formato válido.`);
            return false;
        }
        if (file.size > maxSize) {
            error(`${file.name} excede el límite de 50MB.`);
            return false;
        }
        return true;
    });

    const remainingSlots = 50 - selectedFiles.value.length;
    const filesToAdd = validFiles.slice(0, remainingSlots);

    if (validFiles.length > remainingSlots) {
        error(`Límite de 50 fotos. Se agregaron ${remainingSlots}.`);
    }

    const compressingPromises = filesToAdd.map(file => compressImage(file));
    const newFileObjects = await Promise.all(compressingPromises);
    selectedFiles.value.push(...newFileObjects);

    if (modelsLoaded.value) {
        runAIDetection();
    }
};

watch(() => form.event_id, (newVal) => {
    form.location_role = '';
    isTypingCustomRole.value = false;
});

watch(() => form.location_role, (newValue) => {
    if (newValue === 'custom') {
        isTypingCustomRole.value = true;
        form.location_role = '';
    }
});

const removeFile = (index) => {
    selectedFiles.value.splice(index, 1);
    if (faceDetectionResults.value[index]) faceDetectionResults.value.splice(index, 1);
    if (bibDetectionResults.value[index]) bibDetectionResults.value.splice(index, 1);
};

const clearFiles = () => {
    selectedFiles.value = [];
    faceDetectionResults.value = [];
    bibDetectionResults.value = [];
    if (fileInput.value) fileInput.value.value = '';
};

const runAIDetection = async () => {
    processingFaces.value = true;
    await detectFacesInImages();
    processingFaces.value = false;

    if (form.read_bibs) {
        processingBibs.value = true;
        await detectBibNumbers(faceDetectionResults.value);
        processingBibs.value = false;
    } else {
        bibDetectionResults.value = selectedFiles.value.map((_, i) => ({ index: i, numbers: [], raw_text: '' }));
    }
};

const detectFacesInImages = async () => {
    faceDetectionResults.value = [];
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
                for (const faceBox of boxes) {
                    const roiDataUrl = await cropTorsoFromFace(selectedFiles.value[i].preview, faceBox);
                    const cleanedDataUrl = await preprocessForOCR(roiDataUrl);
                    const result = await worker.recognize(cleanedDataUrl);
                    const found = result.data.text.match(/\d+/g);
                    if (found) found.forEach(num => { if (num.length >= 2) uniqueNumbers.add(num); });
                }
            } else {
                const roiDataUrl = await cropTorsoFromFace(selectedFiles.value[i].preview, null);
                const cleanedDataUrl = await preprocessForOCR(roiDataUrl);
                const result = await worker.recognize(cleanedDataUrl);
                const found = result.data.text.match(/\d+/g);
                if (found) found.forEach(num => { if (num.length >= 2) uniqueNumbers.add(num); });
            }

            bibDetectionResults.value.push({ index: i, numbers: Array.from(uniqueNumbers), raw_text: '' });
        } catch (error) {
            console.error(`Error OCR foto ${i}:`, error);
            bibDetectionResults.value.push({ index: i, numbers: [], raw_text: '' });
        }
    }
    await worker.terminate();
};

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

            canvas.width = rw * 2; canvas.height = rh * 2;
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


const submitPhotos = () => {
    if (selectedFiles.value.length === 0) return error('Seleccione al menos una foto.');
    if (!form.price || form.price <= 0) return error('Establezca un precio válido.');

    uploading.value = true;
    uploadProgress.value = { current: 0, total: selectedFiles.value.length, percentage: 0 };

    const facesData = selectedFiles.value.map((_, index) => {
        const faceResult = faceDetectionResults.value[index];
        return {
            index: index,
            count: faceResult ? faceResult.count : 0,
            descriptors: faceResult ? faceResult.descriptors : [],
            boxes: faceResult ? faceResult.boxes : [],
        };
    });

    const bibsData = selectedFiles.value.map((_, index) => {
        const bibResult = bibDetectionResults.value[index];
        return {
            index: index,
            numbers: bibResult ? bibResult.numbers : [],
            raw_text: bibResult ? bibResult.raw_text : '',
        };
    });

    const formData = new FormData();

    selectedFiles.value.forEach((item, index) => {
        formData.append(`photos[${index}]`, item.file);
    });

    formData.append('price', form.price);
    formData.append('is_active', form.is_active ? 1 : 0);
    if (form.event_id) formData.append('event_id', form.event_id);

    if (form.location_role) formData.append('location_role', form.location_role);

    formData.append('face_data', JSON.stringify({
        faces: facesData,
        bibs: bibsData
    }));

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
            console.error(' Error en carga:', err);
            error('Hubo un error en la carga.');
        },
    });
};
</script>
<template>

    <Head title="Carga de Material" />

    <AuthenticatedLayout>
        <div class="py-12 bg-[#F8F9FA] min-h-screen text-slate-800 antialiased pt-28">
            <div class="max-w-[1500px] mx-auto sm:px-6 lg:px-8">


                <div
                    class="mb-10 flex flex-col md:flex-row md:items-end justify-between gap-6 border-b border-gray-200 pb-8">
                    <div>
                        <Link :href="route('photographer.photos.index')"
                            class="inline-flex items-center gap-2 bg-white px-4 py-2 rounded-full text-xs font-bold uppercase tracking-wider text-gray-500 hover:text-black hover:shadow-sm border border-gray-200 transition-all mb-6">
                            <ArrowLeftIcon class="w-4 h-4" /> Volver al Archivo
                        </Link>
                        <h1 class="font-flux text-5xl md:text-7xl text-black leading-none tracking-wide">
                            Subida de <span class="text-[#E30613]">fotos</span>
                        </h1>
                        <p class="text-sm font-bold text-gray-500 mt-2 flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-[#E30613] animate-pulse"></span> Sistema de compresión
                            e IA activo
                        </p>
                    </div>
                </div>

                <form @submit.prevent="submitPhotos" class="grid grid-cols-1 lg:grid-cols-3 gap-8 xl:gap-12">


                    <div class="lg:col-span-1 space-y-6">

                        <div class="bg-white border border-gray-100 rounded shadow-sm overflow-hidden">
                            <div class="px-6 md:px-8 py-5 border-b border-gray-100 bg-gray-50/50">
                                <h2
                                    class="text-xs font-bold uppercase tracking-widest text-gray-500 flex items-center gap-2">
                                    <span class="w-4 h-px bg-gray-300"></span> Parámetros de Venta
                                </h2>
                            </div>

                            <div class="p-6 md:p-8 space-y-8">

                                <div>
                                    <label
                                        class="block text-xs font-bold uppercase tracking-wider text-gray-500 mb-2 ml-1">
                                        Precio unitario (ARS)
                                    </label>
                                    <div class="relative">
                                        <span
                                            class="absolute left-5 top-1/2 -translate-y-1/2 text-xl font-bold text-gray-400">$</span>
                                        <input v-model="form.price" type="number" step="0.01" min="0.01" required
                                            class="w-full bg-gray-50 border border-transparent focus:bg-white focus:border-gray-300 focus:ring-4 focus:ring-gray-100 text-slate-800 font-bold text-2xl py-3 pl-10 pr-4 rounded-xl transition-all outline-none"
                                            placeholder="0.00">
                                    </div>
                                    <p v-if="errors.price" class="text-[#E30613] text-xs font-bold mt-2">{{ errors.price
                                        }}</p>
                                </div>


                                <div>
                                    <label
                                        class="block text-xs font-bold uppercase tracking-wider text-gray-500 mb-2 ml-1">
                                        Asignar a evento
                                    </label>
                                    <select v-model="form.event_id"
                                        class="w-full bg-gray-50 border border-transparent focus:bg-white focus:border-gray-300 focus:ring-4 focus:ring-gray-100 text-slate-700 font-bold text-sm py-3.5 px-4 rounded-xl transition-all outline-none appearance-none cursor-pointer">
                                        <option :value="null">-- Guardar en mi bóveda personal --</option>
                                        <option v-for="event in events" :key="event.id" :value="event.id">
                                            {{ event.name }}
                                        </option>
                                    </select>

                                
                                </div>





                                <div v-if="form.event_id" class="animate-fade-in transition-all duration-300">
                                    <label
                                        class="block text-xs font-bold uppercase tracking-wider text-gray-500 mb-2 ml-1">
                                        Rol / Lugar asignado (opcional)
                                    </label>

                                    <div class="relative">

                                        <select v-if="!isTypingCustomRole && currentEventRoles.length > 0"
                                            v-model="form.location_role"
                                            class="w-full bg-gray-50 border border-transparent focus:bg-white focus:border-gray-300 focus:ring-4 focus:ring-gray-100 text-slate-700 font-bold text-sm py-3.5 px-4 rounded-xl transition-all outline-none appearance-none cursor-pointer">
                                            <option value="">-- Seleccionar lugar / rol --</option>
                                            <option v-for="role in currentEventRoles" :key="role" :value="role">
                                                {{ role }}
                                            </option>
                                            <option value="custom">+ Escribir uno nuevo...</option>
                                        </select>


                                        <div v-if="isTypingCustomRole || currentEventRoles.length === 0"
                                            class="flex gap-2">
                                            <input v-model="form.location_role" type="text"
                                                class="w-full bg-gray-50 border border-transparent focus:bg-white focus:border-gray-300 focus:ring-4 focus:ring-gray-100 text-slate-700 font-bold text-sm py-3.5 px-4 rounded-xl transition-all outline-none"
                                                placeholder="Ej: Línea de meta, podio, curva 3..." maxlength="100">


                                            <button v-if="currentEventRoles.length > 0" type="button"
                                                @click="isTypingCustomRole = false; form.location_role = ''"
                                                class="px-4 bg-gray-100 text-gray-500 rounded-xl hover:bg-gray-200 transition-colors">
                                                <XMarkIcon class="w-5 h-5" />
                                            </button>
                                        </div>
                                    </div>
                                    <p class="text-[10px] text-gray-400 mt-2 ml-1">
                                        Ayudá a las personas a encontrarte más fácil en el evento.
                                    </p>
                                    <p v-if="errors.location_role" class="text-[#E30613] text-xs font-bold mt-2">{{
                                        errors.location_role }}</p>
                                </div>

                                <div class="space-y-6 pt-6 border-t border-gray-100">


                                    <label class="relative flex justify-between items-start cursor-pointer group">
                                        <div class="pr-4">
                                            <span
                                                class="block text-sm font-bold text-slate-700 group-hover:text-black transition-colors">
                                                Publicación inmediata
                                            </span>
                                            <span class="block text-xs text-gray-400 mt-1">
                                                Las fotos van a ser visibles tras la carga.
                                            </span>
                                        </div>
                                        <div class="relative inline-flex items-center mt-1">
                                            <input type="checkbox" v-model="form.is_active" class="sr-only peer">
                                            <div
                                                class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#E30613]">
                                            </div>
                                        </div>
                                    </label>


                                    <label class="relative flex justify-between items-start cursor-pointer group">
                                        <div class="pr-4">
                                            <span
                                                class="block text-sm font-bold text-slate-700 group-hover:text-black transition-colors">
                                                Leer dorsales (OCR)
                                            </span>
                                            <span class="block text-xs text-gray-400 mt-1">
                                                Identificación automática de números
                                            </span>
                                        </div>
                                        <div class="relative inline-flex items-center mt-1">
                                            <input type="checkbox" v-model="form.read_bibs" class="sr-only peer">
                                            <div
                                                class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-black">
                                            </div>
                                        </div>
                                    </label>

                                </div>
                            </div>
                        </div>


                        <div class="bg-gray-50 border border-gray-200 p-6 rounded">
                            <div class="flex items-start gap-4">
                                <div
                                    class="w-10 h-10 rounded-full bg-white shadow-sm flex items-center justify-center shrink-0">
                                    <InformationCircleIcon class="w-5 h-5 text-gray-500" stroke-width="2" />
                                </div>
                                <div>
                                    <h4 class="text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">
                                        Protección F33
                                    </h4>
                                    <p class="text-xs text-gray-500 leading-relaxed">
                                        Se va a aplicar una marca de agua automáticamente. Los originales se guardan en
                                        bóveda segura hasta la confirmación de transacción.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>


                    <div class="lg:col-span-2 flex flex-col gap-6">


                        <div @dragover.prevent="dragOver = true" @dragleave.prevent="dragOver = false"
                            @drop.prevent="handleDrop" :class="[
                                'border-2 border-dashed rounded p-8 md:p-12 text-center transition-all duration-300 flex flex-col items-center justify-center min-h-[250px] relative overflow-hidden bg-white',
                                dragOver
                                    ? 'border-[#E30613] bg-red-50'
                                    : 'border-gray-300 hover:border-gray-400 hover:bg-gray-50'
                            ]">
                            <input type="file" ref="fileInput" @change="handleFileSelect" multiple accept="image/*"
                                class="hidden" id="file-upload">

                            <div v-if="selectedFiles.length === 0" class="flex flex-col items-center">
                                <div
                                    class="w-20 h-20 bg-gray-50 rounded-full flex items-center justify-center mb-6 shadow-sm border border-gray-100">
                                    <CloudArrowUpIcon class="w-10 h-10 text-gray-400 stroke-1" />
                                </div>
                                <h3 class="font-flux text-4xl text-black mb-2">Carga</h3>
                                <p class="text-gray-500 mb-6">Arrastrá tus fotografías acá o explora tus archivos</p>
                                <button type="button" @click="$refs.fileInput.click()"
                                    class="px-8 py-3.5 bg-black text-white rounded-full font-bold text-xs uppercase tracking-wider hover:bg-gray-800 transition-colors shadow-md">
                                    Seleccionar archivos
                                </button>
                                <p class="mt-6 text-[10px] font-bold uppercase tracking-widest text-gray-400">
                                    JPG, PNG • Máx 50MB • Límite 50 archivos
                                </p>
                            </div>

                            <div v-else class="w-full flex flex-col h-full">

                                <div class="flex justify-between items-center mb-6 border-b border-gray-100 pb-4">
                                    <span class="text-xl font-bold text-black flex items-center gap-2">
                                        {{ selectedFiles.length }} <span
                                            class="text-sm font-medium text-gray-500">Archivos en cola</span>
                                    </span>
                                    <div class="flex gap-2">
                                        <button type="button" @click="$refs.fileInput.click()"
                                            class="px-4 py-2 bg-gray-100 text-slate-700 rounded-full font-bold text-xs hover:bg-gray-200 transition-colors">
                                            + Agregar
                                        </button>
                                        <button type="button" @click="clearFiles"
                                            class="px-4 py-2 bg-red-50 text-[#E30613] rounded-full font-bold text-xs hover:bg-red-100 transition-colors">
                                            Limpiar Todo
                                        </button>
                                    </div>
                                </div>


                                <div v-if="processingFaces || processingBibs"
                                    class="mb-6 bg-red-50 border border-red-100 p-4 rounded flex items-center gap-4 text-[#E30613]">
                                    <div
                                        class="animate-spin rounded-full h-5 w-5 border-2 border-[#E30613] border-t-transparent">
                                    </div>
                                    <span class="text-xs font-bold uppercase tracking-wider">
                                        Ejecutando modelos IA ({{ selectedFiles.length }} activos)...
                                    </span>
                                </div>


                                <div
                                    class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4 max-h-[500px] overflow-y-auto pr-2 custom-scrollbar text-left">
                                    <div v-for="(file, index) in selectedFiles" :key="index"
                                        class="relative group aspect-square bg-gray-100 rounded overflow-hidden border border-gray-200 hover:shadow-md transition-all">

                                        <img :src="file.preview"
                                            class="w-full h-full object-cover transition-transform duration-500 ">


                                        <div v-if="faceDetectionResults[index]"
                                            class="absolute top-2 right-2 px-2 py-1 rounded-md text-[9px] font-bold shadow-sm z-10"
                                            :class="faceDetectionResults[index].count > 0 ? 'bg-[#E30613] text-white' : 'bg-black/50 text-white backdrop-blur'">
                                            <span v-if="faceDetectionResults[index].count > 0">
                                                {{ faceDetectionResults[index].count }} Rostro(s)
                                            </span>
                                            <span v-else>0 Rostros</span>
                                        </div>


                                        <div
                                            class="absolute bottom-0 left-0 w-full p-2 bg-gradient-to-t from-black/80 via-black/40 to-transparent transition-opacity z-20">
                                            <div class="flex flex-wrap gap-1.5 items-center">
                                                <HashtagIcon class="w-3 h-3 text-white shrink-0" stroke-width="3" />

                                                <template v-if="bibDetectionResults[index]?.numbers?.length">
                                                    <div v-for="number in bibDetectionResults[index].numbers"
                                                        :key="number"
                                                        class="bg-white/90 backdrop-blur text-black text-[10px] font-bold px-1.5 py-0.5 rounded flex items-center gap-1">
                                                        <span>{{ number }}</span>
                                                        <button type="button" @click.stop="removeBibTag(index, number)"
                                                            class="text-gray-400 hover:text-[#E30613] focus:outline-none">
                                                            <XMarkIcon class="w-2.5 h-2.5" stroke-width="3" />
                                                        </button>
                                                    </div>
                                                </template>

                                                <input type="text" placeholder="Añadir..."
                                                    @keydown.enter.prevent="addBibTag(index, $event)"
                                                    @keydown.backspace="handleBackspace(index, $event)"
                                                    class="flex-1 min-w-[50px] bg-transparent border-none text-white text-[10px] font-bold p-0 focus:ring-0 placeholder-gray-300 outline-none h-5" />
                                            </div>
                                        </div>


                                        <div
                                            class="absolute inset-0 bg-black/20 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center pointer-events-none z-30">
                                            <button type="button" @click="removeFile(index)"
                                                class="w-10 h-10 bg-white rounded-full text-[#E30613] shadow-lg flex items-center justify-center hover:bg-[#E30613] hover:text-white transition-colors pointer-events-auto transform ">
                                                <XMarkIcon class="w-5 h-5" stroke-width="2" />
                                            </button>
                                        </div>


                                        <div v-if="(processingFaces && !faceDetectionResults[index]) || (processingBibs && !bibDetectionResults[index])"
                                            class="absolute inset-0 bg-white/80 flex items-center justify-center z-40 backdrop-blur-sm">
                                            <div
                                                class="animate-spin rounded-full h-6 w-6 border-2 border-gray-200 border-t-[#E30613]">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>


                        <div v-if="uploading" class="p-6 bg-white border border-gray-100 rounded shadow-sm">
                            <div
                                class="flex justify-between text-xs font-bold uppercase tracking-wider text-slate-700 mb-3">
                                <span>Subiendo archivos...</span>
                                <span class="text-[#E30613]">{{ uploadProgress.percentage }}%</span>
                            </div>
                            <div class="w-full bg-gray-100 h-3 rounded-full overflow-hidden">
                                <div class="bg-gradient-to-r from-red-500 to-[#E30613] h-full transition-all duration-200 ease-out"
                                    :style="{ width: uploadProgress.percentage + '%' }"></div>
                            </div>
                            <p class="text-xs text-gray-400 mt-3 text-center">
                                Por favor, no cerrés esta ventana hasta que termine el proceso.
                            </p>
                        </div>


                        <div class="flex justify-end mt-2">
                            <button type="button" @click="submitPhotos"
                                :disabled="uploading || selectedFiles.length === 0" :class="[
                                    'px-10 py-4 font-bold text-sm uppercase tracking-wider transition-all duration-200 rounded-full w-full md:w-auto flex justify-center items-center gap-2',
                                    uploading || selectedFiles.length === 0
                                        ? 'bg-gray-100 text-gray-400 cursor-not-allowed'
                                        : 'bg-black text-white hover:bg-[#E30613] hover:shadow-lg hover:shadow-red-500/30 hover:-translate-y-1'
                                ]">
                                <span v-if="uploading" class="flex items-center gap-2">
                                    <div
                                        class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin">
                                    </div>
                                    Subiendo...
                                </span>
                                <span v-else>Subir fotos</span>
                            </button>
                        </div>


                        <div v-if="Object.keys(errors).length > 0"
                            class="p-6 bg-red-50 border border-red-100 rounded mt-4">
                            <div class="flex gap-4">
                                <div class="w-8 h-8 rounded-full bg-red-100 flex items-center justify-center shrink-0">
                                    <XMarkIcon class="w-5 h-5 text-[#E30613]" stroke-width="2" />
                                </div>
                                <div>
                                    <h4 class="text-sm font-bold text-[#E30613] mb-2">
                                        Error en el sistema
                                    </h4>
                                    <ul class="list-disc pl-4 text-xs text-red-800 space-y-1 font-medium">
                                        <li v-for="(error, key) in errors" :key="key">
                                            {{ error }}
                                        </li>
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
@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }

    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.animate-fade-in {
    animation: fadeIn 0.3s ease-out forwards;
}

.custom-scrollbar::-webkit-scrollbar {
    width: 6px;
}

.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}

.custom-scrollbar::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 10px;
}

.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
}
</style>