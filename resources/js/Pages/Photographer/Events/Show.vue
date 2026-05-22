<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ref, computed, onMounted } from 'vue';
import { useConfirm } from '@/Composables/useConfirm';
import { useToast } from '@/Composables/useToast';
import * as faceapi from 'face-api.js';
import '@tensorflow/tfjs-backend-webgl';
import Tesseract from 'tesseract.js';



import {
    CalendarIcon,
    MapPinIcon,
    PhotoIcon,
    ArrowDownTrayIcon,
    TrashIcon,
    CloudArrowUpIcon,
    HashtagIcon,
    CheckIcon,
    XMarkIcon
} from '@heroicons/vue/24/outline';

const props = defineProps({
    event: { type: Object, required: true },
    photos: { type: Object, default: () => ({ data: [], links: [] }) },
    stats: {
        type: Object,
        default: () => ({ total_photos: 0, active_photos: 0, total_downloads: 0 })
    },
    unassignedPhotos: { type: Array, default: () => [] },
});

const { confirm } = useConfirm();
const { success } = useToast();
const copyEventUrl = () => {
    navigator.clipboard.writeText(window.location.href);
    success('ENLACE EN PORTAPAPELES');
};

const modelsLoaded = ref(false);
const processingFaces = ref(false);
const faceDetectionResults = ref([]);
const processingBibs = ref(false);
const bibDetectionResults = ref([]);
const uploadMode = ref('upload');
const selectedExistingPhotos = ref([]);

// NUEVO: Bandera para activar/desactivar OCR
const readBibs = ref(true);

const totalFacesDetected = computed(() => faceDetectionResults.value.reduce((sum, result) => sum + result.count, 0));
const totalBibsDetected = computed(() => bibDetectionResults.value.reduce((sum, result) => sum + result.numbers.length, 0));
const photosWithFaces = computed(() => faceDetectionResults.value.filter(r => r.count > 0).length);
const photosWithBibs = computed(() => bibDetectionResults.value.filter(r => r.numbers.length > 0).length);

const addBibTag = (index, event) => {
    const val = event.target.value.trim();
    if (!val) return;
    if (!bibDetectionResults.value[index]) {
        bibDetectionResults.value[index] = { index: index, numbers: [], raw_text: '' };
    }
    if (!bibDetectionResults.value[index].numbers.includes(val)) {
        bibDetectionResults.value[index].numbers.push(val);
    }
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

const togglePhotoSelection = (photoId) => {
    const index = selectedExistingPhotos.value.indexOf(photoId);
    if (index > -1) {
        selectedExistingPhotos.value.splice(index, 1);
    } else {
        selectedExistingPhotos.value.push(photoId);
    }
};

const assignExistingPhotos = () => {
    if (selectedExistingPhotos.value.length === 0) return;
    router.post(route('photographer.photos.assign-to-event'), {
        photo_ids: selectedExistingPhotos.value,
        event_id: props.event.id,
    }, {
        preserveScroll: true,
        onSuccess: () => {
            closeModal();
            success('Fotos asignadas al evento');
        },
    });
};

const closeModal = () => {
    showUploadModal.value = false;
    uploadMode.value = 'upload';
    selectedFiles.value = [];
    previewUrls.value = [];
    faceDetectionResults.value = [];
    bibDetectionResults.value = [];
    selectedExistingPhotos.value = [];
    uploadForm.reset('photos', 'face_data');
};

onMounted(async () => {
    try {
        if (!faceapi) throw new Error('face-api.js no está disponible');
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
        try {
            await faceapi.tf.setBackend('cpu');
            await faceapi.tf.ready();
            const MODEL_URL = '/models';
            await Promise.all([
                faceapi.nets.ssdMobilenetv1.loadFromUri(MODEL_URL),
                faceapi.nets.faceLandmark68Net.loadFromUri(MODEL_URL),
                faceapi.nets.faceRecognitionNet.loadFromUri(MODEL_URL),
            ]);
            modelsLoaded.value = true;
        } catch (cpuErr) {
            console.error('Error con CPU también:', cpuErr);
        }
    }
});

const showUploadModal = ref(false);
const selectedFiles = ref([]);
const previewUrls = ref([]);

const uploadForm = useForm({
    photos: [],
    event_id: props.event.id,
    face_data: null,
});

// NUEVO: Función para comprimir imagen en el cliente
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
                        preview: canvas.toDataURL('image/jpeg', 0.8)
                    });
                }, 'image/jpeg', 0.8);
            };
            img.src = e.target.result;
        };
        reader.readAsDataURL(file);
    });
};

const handleFileSelect = async (e) => {
    const files = Array.from(e.target.files);


    selectedFiles.value = [];
    previewUrls.value = [];
    faceDetectionResults.value = [];
    bibDetectionResults.value = [];


    const compressingPromises = files.map(file => compressImage(file));
    const processedFiles = await Promise.all(compressingPromises);

    selectedFiles.value = processedFiles.map(pf => pf.file);
    uploadForm.photos = selectedFiles.value;
    previewUrls.value = processedFiles.map(pf => pf.preview);

    if (modelsLoaded.value) {
        try {
            processingFaces.value = true;
            await detectFacesInImages();
            processingFaces.value = false;


            if (readBibs.value) {
                processingBibs.value = true;
                await detectBibNumbers(faceDetectionResults.value);
                processingBibs.value = false;
            } else {
                bibDetectionResults.value = selectedFiles.value.map((_, i) => ({ index: i, numbers: [], raw_text: '' }));
            }
        } catch (error) {
            console.error("Error en detección:", error);
            processingFaces.value = false;
            processingBibs.value = false;
        }
    }
};

const cropTorsoFromFace = async (imageUrl, faceBox) => {
    return new Promise((resolve) => {
        const img = new Image();
        img.crossOrigin = 'anonymous';
        img.onload = () => {
            const canvas = document.createElement('canvas');
            const ctx = canvas.getContext('2d');
            let roiX, roiY, roiW, roiH;

            if (faceBox) {
                roiW = faceBox.width * 2.2;
                roiH = faceBox.height * 2.5;
                roiX = faceBox.x - (roiW - faceBox.width) / 2;
                roiY = faceBox.y + (faceBox.height * 1.1);
            } else {
                roiW = img.width * 0.5;
                roiH = img.height * 0.4;
                roiX = (img.width - roiW) / 2;
                roiY = img.height * 0.35;
            }

            roiX = Math.max(0, roiX);
            roiY = Math.max(0, roiY);
            roiW = Math.min(roiW, img.width - roiX);
            roiH = Math.min(roiH, img.height - roiY);

            const scaleFactor = 2;
            canvas.width = roiW * scaleFactor;
            canvas.height = roiH * scaleFactor;
            ctx.drawImage(img, roiX, roiY, roiW, roiH, 0, 0, canvas.width, canvas.height);
            resolve(canvas.toDataURL());
        };
        img.src = imageUrl;
    });
};

const preprocessForOCR = async (imageUrl) => {
    return new Promise((resolve) => {
        const img = new Image();
        img.crossOrigin = 'anonymous';
        img.onload = () => {
            const canvas = document.createElement('canvas');
            const ctx = canvas.getContext('2d');
            const padding = 20;
            canvas.width = img.width + (padding * 2);
            canvas.height = img.height + (padding * 2);
            ctx.fillStyle = "#FFFFFF";
            ctx.fillRect(0, 0, canvas.width, canvas.height);
            ctx.drawImage(img, padding, padding);

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

const detectBibNumbers = async (facesData) => {
    bibDetectionResults.value = [];
    processingBibs.value = true;
    const worker = await Tesseract.createWorker('eng');
    await worker.setParameters({
        tessedit_char_whitelist: '0123456789',
        tessedit_pageseg_mode: Tesseract.PSM.SPARSE_TEXT,
    });

    for (let i = 0; i < previewUrls.value.length; i++) {
        try {
            const faceInfo = facesData.find(f => f.index === i);
            const boxes = (faceInfo && faceInfo.boxes) ? faceInfo.boxes : [];
            let uniqueNumbers = new Set();
            let accumulatedText = "";

            if (boxes.length > 0) {
                for (const faceBox of boxes) {
                    const roiDataUrl = await cropTorsoFromFace(previewUrls.value[i], faceBox);
                    const cleanedDataUrl = await preprocessForOCR(roiDataUrl);
                    const result = await worker.recognize(cleanedDataUrl);
                    const text = result.data.text;
                    accumulatedText += text + " ";
                    const found = text.match(/\d+/g);
                    if (found) found.forEach(num => { if (num.length >= 2) uniqueNumbers.add(num); });
                }
            } else {
                const roiDataUrl = await cropTorsoFromFace(previewUrls.value[i], null);
                const cleanedDataUrl = await preprocessForOCR(roiDataUrl);
                const result = await worker.recognize(cleanedDataUrl);
                const text = result.data.text;
                accumulatedText = text;
                const found = text.match(/\d+/g);
                if (found) found.forEach(num => { if (num.length >= 2) uniqueNumbers.add(num); });
            }

            bibDetectionResults.value.push({ index: i, numbers: Array.from(uniqueNumbers), raw_text: accumulatedText });
        } catch (error) {
            bibDetectionResults.value.push({ index: i, numbers: [], raw_text: '' });
        }
    }
    await worker.terminate();
    processingBibs.value = false;
};

const detectFacesInImages = async () => {
    processingFaces.value = true;
    faceDetectionResults.value = [];
    if (!faceapi || !faceapi.detectAllFaces) {
        processingFaces.value = false;
        return;
    }

    for (let i = 0; i < previewUrls.value.length; i++) {
        try {
            const img = document.createElement('img');
            img.src = previewUrls.value[i];
            await new Promise((resolve, reject) => {
                img.onload = resolve;
                img.onerror = reject;
                setTimeout(() => reject(new Error('Timeout')), 10000);
            });
            const detections = await faceapi.detectAllFaces(img, new faceapi.SsdMobilenetv1Options({ minConfidence: 0.5 })).withFaceLandmarks().withFaceDescriptors();
            const descriptors = detections.map(d => Array.from(d.descriptor));
            const allBoxes = detections.map(d => d.detection.box);
            faceDetectionResults.value.push({ index: i, count: detections.length, descriptors: descriptors, boxes: allBoxes });
        } catch (error) {
            faceDetectionResults.value.push({ index: i, count: 0, descriptors: [], boxes: [] });
        }
    }
    processingFaces.value = false;
};

const uploadPhotos = () => {
    const combinedData = {
        faces: faceDetectionResults.value,
        bibs: bibDetectionResults.value,
    };
    uploadForm.face_data = JSON.stringify(combinedData);
    uploadForm.post(route('photographer.photos.store'), {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
            closeModal();
            success('Fotos subidas correctamente');
        },
    });
};

const deletePhoto = async (photoId) => {
    const confirmed = await confirm({ title: 'Eliminar Fotografía', message: '¿Confirmar eliminación definitiva?', confirmText: 'Eliminar', cancelText: 'Cancelar', type: 'danger' });
    if (confirmed) {
        router.delete(route('photographer.photos.destroy', photoId), {
            preserveScroll: true,
            onSuccess: () => success('Fotografía eliminada')
        });
    }
};

const updateCoverImage = async (photoId) => {
    const confirmed = await confirm({ title: 'Cambiar Portada', message: '¿Establecer esta foto como portada?', confirmText: 'Establecer', cancelText: 'Cancelar', type: 'info' });
    if (confirmed) {
        router.post(route('photographer.events.cover-image', props.event.id), { photo_id: photoId }, { preserveScroll: true, onSuccess: () => success('Portada actualizada') });
    }
};

const formatDate = (dateString) => {
    if (!dateString) return 'S/F';
    return new Date(dateString).toLocaleDateString('es-ES', { year: 'numeric', month: '2-digit', day: '2-digit' }).replace(/\//g, '.');
};

const paginationPages = computed(() => {
    const current = props.photos.current_page;
    const last = props.photos.last_page;
    const delta = 2;
    const pages = [];
    pages.push(1);
    const rangeStart = Math.max(2, current - delta);
    const rangeEnd = Math.min(last - 1, current + delta);
    if (rangeStart > 2) pages.push('...');
    for (let i = rangeStart; i <= rangeEnd; i++) pages.push(i);
    if (rangeEnd < last - 1) pages.push('...');
    if (last > 1) pages.push(last);
    return pages;
});
</script>

<template>

    <Head :title="event.name" />

    <AuthenticatedLayout>

        <div class="relative h-80 bg-black overflow-hidden border-b border-red-600">
            <img v-if="event.cover_image_url" :src="event.cover_image_url"
                class="absolute inset-0 w-full h-full object-cover opacity-40 filter grayscale contrast-125" />
            <div v-else class="absolute inset-0 bg-zinc-900 flex items-center justify-center opacity-50">
                <PhotoIcon class="w-24 h-24 text-zinc-800" />
            </div>

            <div
                class="absolute inset-0 bg-[linear-gradient(rgba(255,255,255,.03)_1px,transparent_1px),linear-gradient(90deg,rgba(255,255,255,.03)_1px,transparent_1px)] bg-[size:32px_32px] pointer-events-none">
            </div>

            <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-full flex flex-col justify-end pb-10 z-10">
                <div class="flex flex-col md:flex-row md:items-end justify-between gap-6">
                    <div>
                        <div
                            class="flex items-center gap-3 mb-3 text-[10px] font-mono font-bold uppercase tracking-widest text-red-600">
                            <Link :href="route('photographer.events.index')" class="hover:text-white transition">Eventos
                            </Link>
                            <span>/</span>
                            <span class="text-white">{{ event.name }}</span>
                        </div>
                        <h1
                            class="text-4xl md:text-5xl font-sans font-black text-white uppercase tracking-tighter mb-2">
                            {{ event.name }}</h1>
                        <div class="flex items-center gap-6 text-xs font-mono text-gray-400">
                            <span class="flex items-center gap-2" v-if="event.event_date">
                                <CalendarIcon class="w-4 h-4 text-red-600" /> {{ formatDate(event.event_date) }}
                            </span>
                            <span class="flex items-center gap-2" v-if="event.location">
                                <MapPinIcon class="w-4 h-4 text-red-600" /> {{ event.location }}
                            </span>
                        </div>
                    </div>

                    <div class="flex gap-3">
                        <Link :href="route('photographer.events.edit', event.id)"
                            class="px-5 py-3 border border-white text-white text-[10px] font-mono font-bold uppercase tracking-widest hover:bg-white hover:text-black transition-colors rounded-none">
                            [ Editar ]
                        </Link>
                        <button @click="showUploadModal = true"
                            class="px-5 py-3 bg-red-600 border border-red-600 text-black text-[10px] font-mono font-bold uppercase tracking-widest hover:bg-black hover:text-red-600 transition-colors rounded-none flex items-center gap-2">
                            <CloudArrowUpIcon class="w-4 h-4" /> Subir Fotos
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="py-12 bg-black min-h-screen text-white selection:bg-red-600 selection:text-white relative">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 relative z-10">

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

                    <div class="lg:col-span-1 space-y-8">


                        <div class="bg-zinc-950 border border-white/10 p-6">
                            <h3
                                class="font-mono text-[10px] font-bold uppercase tracking-widest text-red-600 border-b border-white/10 pb-2 mb-6">
                                Métricas de Operación</h3>
                            <div class="space-y-6 font-mono text-xs uppercase tracking-widest text-gray-400">
                                <div class="flex justify-between items-center">
                                    <span>Total Archivos</span>
                                    <span class="text-xl font-sans font-black text-white">{{ stats.total_photos
                                    }}</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span>Públicas</span>
                                    <span class="text-xl font-sans font-black text-white">{{ stats.active_photos
                                    }}</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span>Extracciones</span>
                                    <span class="text-xl font-sans font-black text-white">{{ stats.total_downloads
                                    }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="mt-8 pt-6 border-t border-white/10 flex flex-wrap gap-4">
                            <button @click="copyEventUrl"
                                class="border-2 border-white bg-black hover:bg-white text-white hover:text-black transition-none px-6 py-3 font-mono text-[10px] font-bold uppercase tracking-widest flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="square" stroke-linejoin="miter" stroke-width="2"
                                        d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1">
                                    </path>
                                </svg>
                                [ COPIAR ENLACE DEL NODO ]
                            </button>
                        </div>

                        <div v-if="$page.props.auth.user"
                            class="bg-gray-950 border-[4px] border-red-600 p-6 relative overflow-hidden transition-none group mb-6">
                            <div class="absolute -right-4 -top-4 text-red-600 opacity-10 pointer-events-none">
                                <svg class="w-32 h-32" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M12 2L2 22h20L12 2zm0 4.5l6.5 13h-13L12 6.5z" />
                                </svg>
                            </div>

                            <h3
                                class="font-mono text-[10px] font-bold uppercase tracking-[0.2em] text-red-600 mb-6 border-b border-red-600/30 pb-2">
                                [ ACCESO RESTRINGIDO // GESTIÓN ]
                            </h3>

                            <div class="flex flex-col gap-3 relative z-10">
                                <Link :href="route('photographer.events.edit', event.slug)"
                                    class="w-full flex items-center justify-between bg-red-600 text-black hover:bg-white hover:text-black transition-none px-4 py-3 font-black font-sans text-sm uppercase tracking-tighter">
                                    <span class="flex items-center gap-2">
                                        + GESTIONAR OPERADORES
                                    </span>
                                    <span class="font-mono text-xs font-bold">></span>
                                </Link>

                                <Link :href="route('photographer.events.upload', event.slug)"
                                    class="w-full flex items-center justify-between border-2 border-red-600 bg-black text-red-600 hover:bg-red-600 hover:text-black transition-none px-4 py-3 font-black font-sans text-sm uppercase tracking-tighter">
                                    <span class="flex items-center gap-2">
                                        ↑ SUBIR FRAGMENTOS (FOTOS)
                                    </span>
                                    <span class="font-mono text-xs font-bold">></span>
                                </Link>
                            </div>




                        </div>


                    </div>

                    <div class="lg:col-span-2">
                        <div v-if="!photos.data || photos.data.length === 0"
                            class="text-center py-24 border border-white/10 bg-zinc-950">
                            <PhotoIcon class="h-12 w-12 mx-auto text-gray-600 mb-4" />
                            <h4 class="font-sans font-black text-white uppercase tracking-tighter mb-1">Bóveda Vacía
                            </h4>
                            <p class="font-mono text-[10px] text-gray-500 uppercase tracking-widest mb-6">No hay datos
                                en esta operación.</p>
                            <button @click="showUploadModal = true"
                                class="text-[10px] font-mono font-bold uppercase tracking-widest text-white border-b border-white hover:text-red-600 hover:border-red-600 pb-0.5 transition">
                                Iniciar Carga
                            </button>
                        </div>

                        <div v-else>
                            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-2 mb-8">
                                <div v-for="photo in photos.data" :key="photo.id"
                                    class="group relative aspect-square bg-zinc-950 border border-white/10 overflow-hidden cursor-crosshair">
                                    <img :src="photo.thumbnail_url" :alt="photo.unique_id"
                                        class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105 filter grayscale contrast-125 group-hover:grayscale-0 opacity-80 group-hover:opacity-100" />

                                    <div
                                        class="absolute inset-0 bg-black/80 opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex flex-col items-center justify-center gap-3 p-4">
                                        <div class="flex gap-2">
                                            <button @click="updateCoverImage(photo.id)" title="Usar como portada"
                                                class="p-2 border border-white text-white hover:bg-white hover:text-black transition">
                                                <PhotoIcon class="w-4 h-4" />
                                            </button>
                                            <button @click="deletePhoto(photo.id)" title="Eliminar"
                                                class="p-2 border border-red-600 text-red-600 hover:bg-red-600 hover:text-white transition">
                                                <TrashIcon class="w-4 h-4" />
                                            </button>
                                        </div>
                                        <div class="text-[9px] text-white/50 font-mono uppercase tracking-widest mt-2">
                                            {{ photo.unique_id }}</div>
                                    </div>

                                    <div v-if="photo.downloads > 0"
                                        class="absolute bottom-1 right-1 bg-black text-red-600 border border-red-600 text-[9px] font-mono px-1.5 py-0.5 flex items-center gap-1">
                                        <ArrowDownTrayIcon class="w-3 h-3" /> {{ photo.downloads }}
                                    </div>
                                </div>
                            </div>

                            <div v-if="photos.last_page > 1"
                                class="flex items-center justify-center gap-2 pt-6 border-t border-white/10">
                                <Link v-if="photos.prev_page_url" :href="photos.prev_page_url"
                                    class="h-8 px-3 flex items-center justify-center font-mono text-xs text-white border border-white/20 hover:border-red-600 hover:text-red-600 transition-colors">
                                    [ ATRÁS ]
                                </Link>
                                <span v-else
                                    class="h-8 px-3 flex items-center justify-center font-mono text-xs text-gray-600 border border-white/5 cursor-not-allowed">
                                    [ ATRÁS ]
                                </span>

                                <div class="flex items-center gap-2">
                                    <template v-for="(page, index) in paginationPages" :key="index">
                                        <span v-if="page === photos.current_page"
                                            class="h-8 w-8 flex items-center justify-center font-mono text-xs font-bold bg-white text-black">
                                            {{ page }}
                                        </span>
                                        <span v-else-if="page === '...'"
                                            class="h-8 w-8 flex items-center justify-center font-mono text-xs text-gray-500">
                                            ...
                                        </span>
                                        <Link v-else :href="photos.path + '?page=' + page"
                                            class="h-8 w-8 flex items-center justify-center font-mono text-xs text-gray-400 border border-white/10 hover:border-white hover:text-white transition-colors">
                                            {{ page }}
                                        </Link>
                                    </template>
                                </div>

                                <Link v-if="photos.next_page_url" :href="photos.next_page_url"
                                    class="h-8 px-3 flex items-center justify-center font-mono text-xs text-white border border-white/20 hover:border-red-600 hover:text-red-600 transition-colors">
                                    [ SIGUIENTE ]
                                </Link>
                                <span v-else
                                    class="h-8 px-3 flex items-center justify-center font-mono text-xs text-gray-600 border border-white/5 cursor-not-allowed">
                                    [ SIGUIENTE ]
                                </span>
                            </div>
                        </div>

                    </div>
                </div>

            </div>
        </div>

        <div v-if="showUploadModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-black/90 backdrop-blur-sm" @click="closeModal"></div>
            <div
                class="relative bg-zinc-950 border border-white/20 shadow-2xl max-w-4xl w-full max-h-[85vh] overflow-hidden flex flex-col text-white font-sans">

                <div class="border-b border-white/10 bg-zinc-900">
                    <div class="p-6 pb-0 flex justify-between items-start mb-4">
                        <h3 class="font-sans font-black text-xl uppercase tracking-tighter">Agregar Datos</h3>
                        <button @click="closeModal" class="text-gray-500 hover:text-red-600">
                            <XMarkIcon class="w-6 h-6" />
                        </button>
                    </div>

                    <div class="flex px-6 gap-4">
                        <button @click="uploadMode = 'upload'"
                            :class="['pb-3 font-mono text-[10px] font-bold uppercase tracking-widest transition-colors border-b-2', uploadMode === 'upload' ? 'border-red-600 text-red-600' : 'border-transparent text-gray-500 hover:text-white']">
                            [ Carga Local ]
                        </button>
                        <button @click="uploadMode = 'existing'"
                            :class="['pb-3 font-mono text-[10px] font-bold uppercase tracking-widest transition-colors border-b-2', uploadMode === 'existing' ? 'border-red-600 text-red-600' : 'border-transparent text-gray-500 hover:text-white']">
                            [ Archivo Interno ]
                        </button>
                    </div>
                </div>

                <div class="p-6 overflow-y-auto flex-1 scrollbar-hide">

                    <div v-if="uploadMode === 'upload'" class="space-y-6">

                        <div class="flex items-center gap-3 border-b border-white/10 pb-4">
                            <input type="checkbox" id="read_bibs" v-model="readBibs"
                                class="bg-black border-gray-600 text-red-600 focus:ring-red-600 rounded-sm">
                            <label for="read_bibs"
                                class="font-mono text-[10px] uppercase tracking-widest text-gray-300">Activar
                                detección de dorsales (OCR)</label>
                        </div>

                        <div v-if="processingFaces || processingBibs"
                            class="bg-zinc-900 border border-white/10 p-4 space-y-3 font-mono text-xs uppercase tracking-widest">
                            <div v-if="processingFaces" class="flex items-center gap-3 text-white">
                                <div class="w-2 h-2 bg-red-600 rounded-full animate-pulse"></div>
                                Analizando biometría: {{ faceDetectionResults.length }}/{{ previewUrls.length }}
                            </div>
                            <div v-if="processingBibs" class="flex items-center gap-3 text-white">
                                <div class="w-2 h-2 bg-red-600 rounded-full animate-pulse"></div>
                                Extrayendo dorsales: {{ bibDetectionResults.length }}/{{ previewUrls.length }}
                            </div>
                        </div>

                        <div v-if="selectedFiles.length === 0"
                            class="border border-dashed border-gray-700 bg-zinc-900 p-12 text-center hover:border-red-600 transition-colors">
                            <input type="file" multiple accept="image/*" @change="handleFileSelect" class="hidden"
                                id="file-upload">
                            <label for="file-upload" class="cursor-pointer block h-full">
                                <CloudArrowUpIcon class="w-12 h-12 mx-auto text-gray-600 mb-4" />
                                <span
                                    class="font-sans font-black uppercase text-white tracking-tighter block mb-2 text-xl">Seleccionar
                                    Archivos</span>
                                <span class="font-mono text-[10px] uppercase tracking-widest text-gray-500">JPG, PNG •
                                    Máx
                                    10MB</span>
                            </label>
                        </div>

                        <div v-else class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-5 gap-2">
                            <div v-for="(url, index) in previewUrls" :key="index"
                                class="relative aspect-square bg-black overflow-hidden border border-white/10 group">
                                <img :src="url" class="w-full h-full object-cover grayscale contrast-125" />

                                <div v-if="faceDetectionResults[index]"
                                    class="absolute top-1 right-1 px-1.5 py-0.5 font-mono text-[8px] border"
                                    :class="faceDetectionResults[index].count > 0 ? 'bg-white text-black border-white' : 'bg-black text-gray-500 border-gray-700'">
                                    {{ faceDetectionResults[index].count > 0 ? faceDetectionResults[index].count + 'BIO' : '0 BIO' }}
                                </div>

                                <div
                                    class="absolute bottom-0 left-0 w-full p-1 bg-black/80 flex gap-1 flex-wrap items-center opacity-0 group-hover:opacity-100 transition-opacity">
                                    <template v-if="bibDetectionResults[index]?.numbers?.length">
                                        <div v-for="number in bibDetectionResults[index].numbers" :key="number"
                                            class="flex items-center gap-1 font-mono text-[8px] bg-red-600 text-white px-1 py-0.5">
                                            <span>{{ number }}</span>
                                            <button @click.stop="removeBibTag(index, number)" class="hover:text-black">
                                                <XMarkIcon class="w-2.5 h-2.5" />
                                            </button>
                                        </div>
                                    </template>
                                    <input type="text" placeholder="+" @keydown.enter.prevent="addBibTag(index, $event)"
                                        @keydown.backspace="handleBackspace(index, $event)"
                                        class="w-full bg-transparent border-none text-white font-mono text-[8px] focus:ring-0 p-0" />
                                </div>
                            </div>
                        </div>
                    </div>

                    <div v-else-if="uploadMode === 'existing'">
                        <div v-if="unassignedPhotos.length === 0"
                            class="text-center py-12 border border-white/10 bg-zinc-900">
                            <PhotoIcon class="w-12 h-12 mx-auto text-gray-600 mb-3" />
                            <p class="font-mono text-xs uppercase text-gray-400">Sin datos residuales.</p>
                        </div>
                        <div v-else>
                            <p class="font-mono text-[10px] text-gray-400 uppercase tracking-widest mb-4">Selección
                                manual ({{
                                    selectedExistingPhotos.length }})</p>
                            <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-5 gap-2">
                                <div v-for="photo in unassignedPhotos" :key="photo.id"
                                    @click="togglePhotoSelection(photo.id)"
                                    class="relative aspect-square bg-black border cursor-pointer transition-all grayscale contrast-125"
                                    :class="selectedExistingPhotos.includes(photo.id) ? 'border-red-600 grayscale-0' : 'border-white/10'">
                                    <img :src="photo.thumbnail_url" class="w-full h-full object-cover" />
                                    <div v-if="selectedExistingPhotos.includes(photo.id)"
                                        class="absolute inset-0 bg-red-600/20 flex items-center justify-center">
                                        <div class="w-6 h-6 bg-red-600 text-white flex items-center justify-center">
                                            <CheckIcon class="w-4 h-4" />
                                        </div>
                                    </div>
                                    <div
                                        class="absolute bottom-1 left-1 bg-black text-white font-mono text-[8px] px-1 py-0.5">
                                        #{{ photo.unique_id.substring(0, 4) }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="p-6 border-t border-white/10 bg-zinc-900 flex justify-between items-center">
                    <div class="font-mono text-[10px] text-gray-400 uppercase tracking-widest">
                        <span v-if="uploadMode === 'upload'">{{ selectedFiles.length }} DATOS</span>
                        <span v-else>{{ selectedExistingPhotos.length }} DATOS</span>
                    </div>
                    <div class="flex gap-3">
                        <button @click="closeModal"
                            class="px-5 py-2 border border-white text-white font-mono text-[10px] font-bold uppercase tracking-widest hover:bg-white hover:text-black transition">Abortar</button>
                        <button v-if="uploadMode === 'upload'" @click="uploadPhotos"
                            :disabled="uploadForm.processing || selectedFiles.length === 0 || processingFaces || processingBibs"
                            class="px-5 py-2 bg-red-600 text-white font-mono text-[10px] font-bold uppercase tracking-widest hover:bg-red-700 transition disabled:opacity-50">
                            {{ processingFaces || processingBibs ? 'Procesando...' : (uploadForm.processing ?
                                'Transfiriendo...'
                                : 'Transmitir') }}
                        </button>
                        <button v-else @click="assignExistingPhotos" :disabled="selectedExistingPhotos.length === 0"
                            class="px-5 py-2 bg-red-600 text-white font-mono text-[10px] font-bold uppercase tracking-widest hover:bg-red-700 transition disabled:opacity-50">
                            Asignar
                        </button>
                    </div>
                </div>
            </div>
        </div>

    </AuthenticatedLayout>
</template>

<style scoped>
.scrollbar-hide::-webkit-scrollbar {
    display: none;
}

.scrollbar-hide {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
</style>