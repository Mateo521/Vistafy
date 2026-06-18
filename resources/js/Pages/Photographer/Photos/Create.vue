<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';
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
    errors: Object,
});

const form = useForm({
    photos: [],
    price: 5.00,
    event_id: null,
    is_active: true,
    read_bibs: true,
    face_data: null,
});
const { success, error } = useToast();


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

// ----------------------------------------------------------------------
// 2. MANEJO DE ARCHIVOS (Con Compresión)
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

// ----------------------------------------------------------------------
// 4. SUBMIT
// ----------------------------------------------------------------------
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
        <div class="py-12 bg-[#050505] min-h-screen text-[#F2F0EB] selection:bg-[#FF0000] selection:text-white antialiased">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

                <div class="mb-10 border-b-4 border-zinc-800 pb-6 flex flex-col md:flex-row md:items-end justify-between gap-4">
                    <div>
                        <Link :href="route('photographer.photos.index')"
                            class="font-mono text-[10px] font-bold uppercase tracking-widest text-zinc-400 hover:text-[#050505] hover:bg-[#FF0000] p-1 mb-4 inline-flex items-center gap-2 transition-colors border border-transparent hover:border-[#FF0000]">
                            ← Volver_al_Archivo
                        </Link>
                        <h1 class="text-4xl md:text-6xl font-black uppercase tracking-tighter text-[#F2F0EB] leading-none mt-2">
                            Ingesta de Material
                        </h1>
                        <p class="font-mono text-xs text-[#FF0000] font-bold mt-3 uppercase tracking-widest">
                            > Sistema de compresión e IA activo
                        </p>
                    </div>
                </div>

                <form @submit.prevent="submitPhotos" class="grid grid-cols-1 lg:grid-cols-3 gap-10">

                    <div class="lg:col-span-1 space-y-8">
                        
                        <div class="bg-[#0a0a0a] border-4 border-[#F2F0EB] rounded-none shadow-[6px_6px_0px_0px_rgba(255,0,0,1)]">
                            <div class="px-6 py-4 border-b-4 border-[#F2F0EB] bg-[#F2F0EB] text-[#050505]">
                                <h2 class="font-mono text-[10px] font-bold uppercase tracking-widest">
                                    PARÁMETROS_DE_VENTA
                                </h2>
                            </div>

                            <div class="p-6 space-y-6">
                                <div>
                                    <label class="block font-mono text-[10px] font-bold uppercase tracking-widest text-zinc-400 mb-2">
                                        Precio Unitario (ARS)
                                    </label>
                                    <div class="relative">
                                        <span class="absolute left-4 top-1/2 -translate-y-1/2 text-2xl font-black text-[#FF0000]">$</span>
                                        <input v-model="form.price" type="number" step="0.01" min="0.01" required
                                            class="w-full bg-[#111] border-2 border-[#F2F0EB] text-[#F2F0EB] font-mono text-2xl font-bold py-3 pl-10 focus:border-[#FF0000] focus:ring-0 rounded-none transition-colors"
                                            placeholder="0.00">
                                    </div>
                                    <p v-if="errors.price" class="text-[#FF0000] font-mono text-[10px] font-bold mt-2 uppercase">{{ errors.price }}</p>
                                </div>

                                <div>
                                    <label class="block font-mono text-[10px] font-bold uppercase tracking-widest text-zinc-400 mb-2">
                                        Asignar a Evento
                                    </label>
                                    <select v-model="form.event_id"
                                        class="w-full bg-[#111] border-2 border-[#F2F0EB] text-[#F2F0EB] font-mono text-xs uppercase font-bold py-3 focus:border-[#FF0000] focus:ring-0 rounded-none cursor-pointer">
                                        <option :value="null">-- SIN_ASIGNAR --</option>
                                        <option v-for="event in events" :key="event.id" :value="event.id">
                                            {{ event.name }}
                                        </option>
                                    </select>
                                </div>

                                <div class="pt-4 border-t-2 border-zinc-800 border-dashed">
                                    <label class="flex items-start gap-4 cursor-pointer group">
                                        <div class="relative flex items-center justify-center w-6 h-6 border-2 border-[#F2F0EB] bg-[#111] group-hover:border-[#FF0000] transition-colors mt-0.5">
                                            <input type="checkbox" v-model="form.is_active" class="peer sr-only">
                                            <div class="w-3 h-3 bg-[#FF0000] opacity-0 peer-checked:opacity-100 transition-opacity"></div>
                                        </div>
                                        <div>
                                            <span class="block font-mono text-xs font-bold uppercase tracking-widest text-[#F2F0EB] group-hover:text-[#FF0000] transition-colors">
                                                Publicación Inmediata
                                            </span>
                                            <span class="block text-[10px] text-zinc-500 font-mono mt-1 uppercase">
                                                Activos visibles tras ingesta
                                            </span>
                                        </div>
                                    </label>
                                </div>

                                <div class="pt-4 border-t-2 border-zinc-800 border-dashed">
                                    <label class="flex items-start gap-4 cursor-pointer group">
                                        <div class="relative flex items-center justify-center w-6 h-6 border-2 border-[#F2F0EB] bg-[#111] group-hover:border-[#FF0000] transition-colors mt-0.5">
                                            <input type="checkbox" v-model="form.read_bibs" class="peer sr-only">
                                            <div class="w-3 h-3 bg-[#FF0000] opacity-0 peer-checked:opacity-100 transition-opacity"></div>
                                        </div>
                                        <div>
                                            <span class="block font-mono text-xs font-bold uppercase tracking-widest text-[#F2F0EB] group-hover:text-[#FF0000] transition-colors">
                                                Leer Dorsales (OCR)
                                            </span>
                                            <span class="block text-[10px] text-zinc-500 font-mono mt-1 uppercase">
                                                Desactivar para eventos no deportivos
                                            </span>
                                        </div>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="bg-[#111] border-2 border-zinc-800 p-6 rounded-none">
                            <div class="flex items-start gap-4">
                                <InformationCircleIcon class="w-6 h-6 text-[#FF0000] flex-shrink-0" stroke-width="2" />
                                <div>
                                    <h4 class="font-mono text-[10px] font-bold uppercase tracking-widest text-[#F2F0EB] mb-2">
                                        SYS_SECURITY
                                    </h4>
                                    <p class="font-mono text-[10px] text-zinc-500 leading-relaxed uppercase">
                                        Marca de agua aplicada automáticamente. Originales resguardados en bóveda segura hasta confirmación de transacción.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="lg:col-span-2 flex flex-col gap-8">

                        <div @dragover.prevent="dragOver = true" @dragleave.prevent="dragOver = false"
                            @drop.prevent="handleDrop" :class="[
                                'border-4 border-dashed rounded-none p-12 text-center transition-all duration-300 flex flex-col items-center justify-center min-h-[300px]',
                                dragOver
                                    ? 'border-[#FF0000] bg-[#1a0505]'
                                    : 'border-zinc-700 bg-[#0a0a0a] hover:border-[#F2F0EB]'
                            ]">
                            <input type="file" ref="fileInput" @change="handleFileSelect" multiple accept="image/*" class="hidden">

                            <div v-if="selectedFiles.length === 0" class="flex flex-col items-center">
                                <CloudArrowUpIcon class="w-16 h-16 text-zinc-600 mb-6 stroke-1" />
                                <h3 class="text-2xl font-black uppercase tracking-tight text-[#F2F0EB] mb-2">
                                    Zona de Ingesta
                                </h3>
                                <p class="font-mono text-xs text-zinc-500 mb-8 uppercase">
                                    Arrastre activos físicos aquí o explore
                                </p>
                                <button type="button" @click="$refs.fileInput.click()"
                                    class="px-8 py-3 border-2 border-[#F2F0EB] bg-transparent text-[#F2F0EB] font-mono text-[10px] font-bold uppercase tracking-widest hover:bg-[#F2F0EB] hover:text-[#050505] transition-colors rounded-none">
                                    Seleccionar_Archivos
                                </button>
                                <div class="mt-8 font-mono text-[10px] uppercase tracking-widest text-zinc-600 border-t-2 border-zinc-800 pt-4 w-full">
                                    JPG, PNG // MAX 50MB // LÍMITE 50
                                </div>
                            </div>

                            <div v-else class="w-full flex flex-col h-full">
                                <div class="flex justify-between items-end mb-6 border-b-2 border-zinc-800 pb-4">
                                    <span class="font-mono text-2xl font-black text-[#F2F0EB]">
                                        {{ selectedFiles.length }} <span class="text-[#FF0000] text-sm">ARCHIVOS EN COLA</span>
                                    </span>
                                    <div class="flex gap-4">
                                        <button type="button" @click="$refs.fileInput.click()"
                                            class="font-mono text-[10px] font-bold uppercase tracking-widest text-zinc-400 hover:text-[#F2F0EB] transition-colors">
                                            + Agregar
                                        </button>
                                        <button type="button" @click="clearFiles"
                                            class="font-mono text-[10px] font-bold uppercase tracking-widest text-[#FF0000] hover:text-red-400 transition-colors">
                                            [ Purgar_Todo ]
                                        </button>
                                    </div>
                                </div>

                                <div v-if="processingFaces || processingBibs"
                                    class="mb-6 bg-[#1a0505] border-2 border-[#FF0000] p-4 rounded-none flex items-center gap-4">
                                    <div class="animate-spin rounded-none h-5 w-5 border-4 border-[#FF0000] border-t-transparent"></div>
                                    <span class="font-mono text-xs text-[#FF0000] font-bold uppercase tracking-widest">
                                        Ejecutando Modelos IA ({{ selectedFiles.length }} activos)...
                                    </span>
                                </div>

                                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4 max-h-[500px] overflow-y-auto pr-2 custom-scrollbar">
                                    <div v-for="(file, index) in selectedFiles" :key="index"
                                        class="relative group aspect-square bg-[#050505] border-2 border-zinc-800 overflow-hidden hover:border-[#F2F0EB] transition-colors">

                                        <img :src="file.preview"
                                            class="w-full h-full object-cover filter grayscale contrast-125 opacity-70 group-hover:grayscale-0 group-hover:opacity-100 transition-all duration-300">

                                        <div v-if="faceDetectionResults[index]"
                                            class="absolute top-2 right-2 px-2 py-1 font-mono text-[10px] font-bold shadow-[2px_2px_0_0_rgba(0,0,0,1)] z-10 border-2 border-black"
                                            :class="faceDetectionResults[index].count > 0 ? 'bg-[#FF0000] text-black' : 'bg-zinc-800 text-zinc-400'">
                                            <span v-if="faceDetectionResults[index].count > 0">
                                                [{{ faceDetectionResults[index].count }}] ROSTROS
                                            </span>
                                            <span v-else>0</span>
                                        </div>

                                        <div class="absolute bottom-0 left-0 right-0 p-2 bg-gradient-to-t from-black via-black/80 to-transparent transition-all duration-300 z-20 group/edit border-t border-transparent group-hover:border-[#FF0000]">
                                            <div class="flex flex-wrap gap-1.5 items-center">
                                                <HashtagIcon class="w-3 h-3 text-[#FF0000] shrink-0" stroke-width="3" />

                                                <template v-if="bibDetectionResults[index]?.numbers?.length">
                                                    <div v-for="number in bibDetectionResults[index].numbers" :key="number"
                                                        class="bg-[#F2F0EB] text-black text-[10px] font-black font-mono px-1 py-0.5 flex items-center gap-1 border-2 border-black">
                                                        <span>{{ number }}</span>
                                                        <button type="button" @click.stop="removeBibTag(index, number)"
                                                            class="text-[#FF0000] hover:text-black focus:outline-none">
                                                            <XMarkIcon class="w-3 h-3" stroke-width="3" />
                                                        </button>
                                                    </div>
                                                </template>

                                                <input type="text" placeholder="+"
                                                    @keydown.enter.prevent="addBibTag(index, $event)"
                                                    @keydown.backspace="handleBackspace(index, $event)"
                                                    class="flex-1 min-w-[30px] bg-transparent border-none text-[#F2F0EB] font-mono text-[11px] font-bold p-0 focus:ring-0 placeholder-zinc-600 focus:placeholder-zinc-400 outline-none h-5" />
                                            </div>
                                        </div>

                                        <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center pointer-events-none z-30">
                                            <button type="button" @click="removeFile(index)"
                                                class="bg-[#FF0000] text-black p-3 border-2 border-black hover:bg-white transition-colors pointer-events-auto shadow-[4px_4px_0_0_rgba(0,0,0,1)]">
                                                <XMarkIcon class="w-6 h-6" stroke-width="2" />
                                            </button>
                                        </div>

                                        <div v-if="(processingFaces && !faceDetectionResults[index]) || (processingBibs && !bibDetectionResults[index])"
                                            class="absolute inset-0 bg-black/80 flex items-center justify-center z-40 backdrop-blur-sm">
                                            <div class="animate-spin rounded-none h-6 w-6 border-2 border-zinc-600 border-t-[#FF0000]"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div v-if="uploading" class="p-6 bg-[#111] border-2 border-zinc-800 rounded-none">
                            <div class="flex justify-between font-mono text-[10px] font-bold uppercase tracking-widest text-zinc-400 mb-3">
                                <span>TX_EN_PROGRESO</span>
                                <span class="text-[#FF0000]">{{ uploadProgress.percentage }}%</span>
                            </div>
                            <div class="w-full bg-[#050505] h-3 border-2 border-zinc-800 rounded-none overflow-hidden p-0.5">
                                <div class="bg-[#FF0000] h-full transition-all duration-200 ease-out"
                                    :style="{ width: uploadProgress.percentage + '%' }"></div>
                            </div>
                            <p class="font-mono text-[10px] text-[#FF0000] mt-3 uppercase tracking-widest animate-pulse">
                                [ No interrumpir conexión ]
                            </p>
                        </div>

                        <div class="flex justify-end">
                            <button type="button" @click="submitPhotos"
                                :disabled="uploading || selectedFiles.length === 0" :class="[
                                    'px-10 py-5 font-mono text-[12px] font-black uppercase tracking-widest transition-all duration-200 rounded-none w-full md:w-auto border-4',
                                    uploading || selectedFiles.length === 0
                                        ? 'bg-[#111] text-zinc-600 border-zinc-800 cursor-not-allowed'
                                        : 'bg-[#FF0000] text-black border-black hover:bg-[#F2F0EB] hover:text-black shadow-[6px_6px_0_0_rgba(255,255,255,1)] hover:shadow-none hover:translate-x-[6px] hover:translate-y-[6px]'
                                ]">
                                <span v-if="uploading">Transmitiendo...</span>
                                <span v-else>Iniciar Ingesta //</span>
                            </button>
                        </div>

                        <div v-if="Object.keys(errors).length > 0"
                            class="p-6 border-4 border-[#FF0000] bg-[#1a0505] rounded-none shadow-[6px_6px_0_0_rgba(255,0,0,1)]">
                            <div class="flex gap-4">
                                <XMarkIcon class="w-6 h-6 text-[#FF0000]" stroke-width="2" />
                                <div>
                                    <h4 class="font-mono text-[10px] font-bold text-[#FF0000] uppercase tracking-widest mb-2 border-b-2 border-[#FF0000] pb-1 w-fit">
                                        FALLO_EN_SISTEMA
                                    </h4>
                                    <ul class="list-none font-mono text-xs text-[#F2F0EB] space-y-1">
                                        <li v-for="(error, key) in errors" :key="key" class="flex gap-2 before:content-['>'] before:text-[#FF0000]">
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

.custom-scrollbar::-webkit-scrollbar {
    width: 12px;
}

.custom-scrollbar::-webkit-scrollbar-track {
    background: #050505;
    border-left: 2px solid #27272a; 
}

.custom-scrollbar::-webkit-scrollbar-thumb {
    background: #3f3f46; 
    border-left: 2px solid #27272a;
    border-radius: 0px;
}

.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: #FF0000;
}
</style>