<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ref, computed, onMounted } from 'vue';
import { useConfirm } from '@/Composables/useConfirm';
import { useToast } from '@/Composables/useToast';
import * as faceapi from 'face-api.js';
import '@tensorflow/tfjs-backend-webgl';
import Tesseract from 'tesseract.js';


const showInviteModal = ref(false);
const inviteForm = useForm({
    email: '',
});


const invitePhotographer = () => {
    inviteForm.post(route('photographer.events.invite', props.event.id), {
        preserveScroll: true,
        onSuccess: () => {
            showInviteModal.value = false;
            inviteForm.reset();
            success('Invitación enviada correctamente al correo.');
        },
        onError: () => {
            error('Hubo un problema al enviar la invitación.');
        }
    });
};


import {
    CalendarIcon,
    MapPinIcon,
    UserPlusIcon,
    EnvelopeIcon,
    PhotoIcon,
    ArrowDownTrayIcon,
    TrashIcon,
    MagnifyingGlassIcon,
    Cog6ToothIcon,
    PlusCircleIcon,
    CloudArrowUpIcon,
    LinkIcon,
    HashtagIcon,
    CheckIcon,
    XMarkIcon,
    UserIcon
} from '@heroicons/vue/24/outline';

const props = defineProps({
    event: { type: Object, required: true },
    photos: { type: Object, default: () => ({ data: [], links: [] }) },
    stats: {
        type: Object,
        default: () => ({ total_photos: 0, active_photos: 0, total_downloads: 0 })
    },
    unassignedPhotos: { type: Array, default: () => [] },
    permissions: { type: Object, required: true },
    current_photographer_id: { type: Number, required: true },
});

const { confirm } = useConfirm();
const { success, error } = useToast();

const copyEventUrl = async () => {
    try {
        let url = route('events.show', props.event.slug);
        
        if (props.event.is_private && props.event.private_token) {
            url += `?token=${props.event.private_token}`;
        }
        
        await navigator.clipboard.writeText(url);
        success(props.event.is_private ? 'Enlace privado copiado' : 'Enlace público copiado');
    } catch (err) {
        console.error('Error al copiar:', err);
        error('No se pudo copiar el enlace.');
    }
};

const modelsLoaded = ref(false);
const processingFaces = ref(false);
const faceDetectionResults = ref([]);
const processingBibs = ref(false);
const bibDetectionResults = ref([]);
const uploadMode = ref('upload');
const selectedExistingPhotos = ref([]);


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
    const confirmed = await confirm({ title: 'Cambiar portada', message: '¿Establecer esta foto como portada?', confirmText: 'Establecer', cancelText: 'Cancelar', type: 'info' });
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


        <div class="relative w-full h-[40vh] min-h-[350px] bg-slate-900 overflow-hidden">
            <img v-if="event.cover_image_url" :src="event.cover_image_url"
                class="absolute inset-0 w-full h-full object-cover opacity-60 mix-blend-overlay" />
            <div v-else class="absolute inset-0 bg-gradient-to-br from-slate-800 to-black"></div>

            <div class="absolute inset-0 bg-gradient-to-t from-black/90 via-black/30 to-transparent"></div>

            <div
                class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-full flex flex-col justify-between pt-8 pb-10 z-10">

                <div>
                    <Link :href="route('photographer.events.index')"
                        class="inline-flex items-center gap-2 bg-white/20 backdrop-blur-md px-4 py-2 rounded-full text-xs font-bold uppercase tracking-wider text-white hover:bg-white hover:text-black transition-colors border border-white/30">
                        <ArrowLeftIcon class="w-4 h-4" /> Volver a mis eventos
                    </Link>
                </div>


                <div class="flex flex-col md:flex-row md:items-end justify-between gap-6">
                    <div>
                        <h1 class="font-flux text-5xl md:text-7xl text-white leading-none tracking-wide mb-4">
                            {{ event.name }}
                        </h1>
                        <div
                            class="flex flex-wrap items-center gap-4 text-xs font-bold uppercase tracking-wider text-white/80">
                            <span v-if="event.event_date"
                                class="flex items-center gap-1.5 bg-black/40 backdrop-blur-md px-3 py-1.5 rounded-full">
                                <CalendarIcon class="w-4 h-4 text-[#E30613]" /> {{ formatDate(event.event_date) }}
                            </span>
                            <span v-if="event.location"
                                class="flex items-center gap-1.5 bg-black/40 backdrop-blur-md px-3 py-1.5 rounded-full">
                                <MapPinIcon class="w-4 h-4 text-[#E30613]" /> {{ event.location }}
                            </span>
                        </div>
                    </div>

                    <div class="flex flex-wrap gap-3">
                        <Link :href="route('photographer.events.edit', event.id)"
                            class="px-6 py-3 bg-white/10 backdrop-blur-md border border-white/30 text-white text-xs font-bold uppercase tracking-wider hover:bg-white hover:text-black transition-colors rounded-full flex items-center gap-2">
                            <Cog6ToothIcon class="w-4 h-4" /> Editar evento
                        </Link>
                        <button @click="showUploadModal = true"
                            class="px-6 py-3 bg-[#E30613] text-white text-xs font-bold uppercase tracking-wider hover:bg-red-700 transition-colors rounded-full flex items-center gap-2 shadow-lg shadow-red-500/30">
                            <PlusCircleIcon class="w-5 h-5" /> Asignar fotos
                        </button>
                    </div>
                </div>
            </div>
        </div>


        <div class="py-12 bg-[#F8F9FA] min-h-screen text-slate-800">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 xl:gap-12">


                    <div class="lg:col-span-1 space-y-6">


                        <div class="bg-white rounded p-6 md:p-8 shadow-sm border border-gray-100">
                            <h3
                                class="text-xs font-bold uppercase tracking-widest text-gray-400 mb-6 flex items-center gap-2">
                                <span class="w-4 h-px bg-gray-200"></span> Métricas de Operación
                            </h3>

                            <div class="space-y-4">
                                <div class="flex justify-between items-center bg-gray-50 p-4 rounded">
                                    <span class="text-sm font-medium text-gray-600">Total archivos</span>
                                    <span class="text-xl font-black text-black">{{ stats?.total_photos || 0 }}</span>
                                </div>
                                <div class="flex justify-between items-center bg-gray-50 p-4 rounded">
                                    <span class="text-sm font-medium text-gray-600">Públicas</span>
                                    <span class="text-xl font-black text-black">{{ stats?.active_photos || 0 }}</span>
                                </div>
                                <div class="flex justify-between items-center bg-gray-50 p-4 rounded">
                                    <span class="text-sm font-medium text-gray-600">Descargas totales</span>
                                    <span class="text-xl font-black text-[#E30613]">{{ stats?.total_downloads || 0
                                    }}</span>
                                </div>
                            </div>

                            <button @click="copyEventUrl"
                                class="mt-6 w-full border-2 transition-colors px-4 py-3.5 rounded text-xs font-bold uppercase tracking-wider flex items-center justify-center gap-2"
                                :class="event.is_private ? 'bg-red-50 border-red-200 hover:border-[#E30613] text-[#E30613] hover:text-[#E30613]' : 'bg-white border-gray-200 hover:border-black text-gray-600 hover:text-black'">
                                <LinkIcon class="w-4 h-4" />
                                {{ event.is_private ? 'Copiar enlace privado' : 'Copiar enlace público' }}
                            </button>
                        </div>


                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-1 gap-4">
                            <Link :href="route('events.face-search', event.slug)"
                                class="group bg-white border border-gray-100 p-5 rounded shadow-sm hover:shadow-md transition-all flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 bg-red-50 rounded-full flex items-center justify-center">
                                        <MagnifyingGlassIcon class="w-5 h-5 text-[#E30613]" />
                                    </div>
                                    <div>
                                        <h4 class="font-bold text-sm text-black">Escáner facial</h4>
                                        <p class="text-xs text-gray-500">Probar búsqueda</p>
                                    </div>
                                </div>
                                <ArrowLeftIcon
                                    class="w-4 h-4 text-gray-400 rotate-180 group-hover:text-[#E30613] group-hover:translate-x-1 transition-all" />
                            </Link>

                            <Link :href="route('events.bib-search', event.slug)"
                                class="group bg-white border border-gray-100 p-5 rounded shadow-sm hover:shadow-md transition-all flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 bg-gray-100 rounded-full flex items-center justify-center">
                                        <HashtagIcon class="w-5 h-5 text-black" />
                                    </div>
                                    <div>
                                        <h4 class="font-bold text-sm text-black">Búsqueda OCR</h4>
                                        <p class="text-xs text-gray-500">Dorsales</p>
                                    </div>
                                </div>
                                <ArrowLeftIcon
                                    class="w-4 h-4 text-gray-400 rotate-180 group-hover:text-black group-hover:translate-x-1 transition-all" />
                            </Link>
                        </div>




                        <div class="bg-white rounded p-6 md:p-8 shadow-sm border border-gray-100">
                            <div class="flex justify-between items-center mb-6">
                                <h3
                                    class="text-xs font-bold uppercase tracking-widest text-gray-400 flex items-center gap-2">
                                    <span class="w-4 h-px bg-gray-200"></span> Equipo asignado
                                </h3>

                                <button @click="showInviteModal = true"
                                    class="text-[10px] font-bold uppercase text-red-600 hover:text-black transition-colors flex items-center gap-1">
                                    <UserPlusIcon class="w-3 h-3" /> Invitar
                                </button>
                            </div>

                            <div class="space-y-4">

                                <div class="flex items-center gap-4 bg-gray-50 p-3 rounded border border-gray-100">
                                    <div
                                        class="w-10 h-10 bg-black rounded-full flex items-center justify-center text-white font-bold shadow-sm">
                                        {{ event.photographer?.business_name?.charAt(0) || 'Y' }}
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-bold text-black truncate">{{
                                            event.photographer?.business_name || 'Tú' }}</p>
                                        <p class="text-[10px] font-bold text-[#E30613] uppercase tracking-wider">
                                            Administrador</p>
                                    </div>
                                </div>


                                <template v-if="event.collaborators && event.collaborators.length > 0">
                                    <div v-for="collab in event.collaborators" :key="collab.id"
                                        class="flex items-center gap-4 p-2 border-b border-gray-50 last:border-0">
                                        <img v-if="collab.profile_photo_url" :src="collab.profile_photo_url"
                                            class="w-10 h-10 rounded-full object-cover border border-gray-200 shadow-sm">
                                        <div v-else
                                            class="w-10 h-10 bg-gray-200 rounded-full flex items-center justify-center text-gray-500 font-bold shadow-sm">
                                            {{ collab.business_name.charAt(0) }}
                                        </div>
                                        <div class="flex-1 min-w-0 flex justify-between items-center">
                                            <div>
                                                <p class="text-sm font-bold text-slate-700 truncate">{{
                                                    collab.business_name }}</p>
                                                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">
                                                    Colaborador</p>
                                            </div>

                                            <span
                                                v-if="collab.pivot?.status === 'pending' || collab.pivot?.status === 'invited'"
                                                class="bg-yellow-50 text-yellow-600 border border-yellow-200 px-2 py-0.5 rounded text-[9px] font-bold uppercase tracking-widest">
                                                Pendiente
                                            </span>
                                            <span v-else-if="collab.pivot?.status === 'approved'"
                                                class="bg-green-50 text-green-600 border border-green-200 px-2 py-0.5 rounded text-[9px] font-bold uppercase tracking-widest">
                                                Confirmado
                                            </span>
                                        </div>
                                    </div>
                                </template>
                                <div v-else>
                                    <p class="text-xs text-gray-500 italic">No hay colaboradores asignados.</p>
                                </div>
                            </div>
                        </div>



                    </div>


                    <div class="lg:col-span-2">


                        <div
                            class="bg-white p-4 rounded shadow-sm border border-gray-100 mb-8 overflow-x-auto hide-scrollbar">
                            <div class="flex flex-nowrap gap-2 min-w-max">
                                <label class="cursor-pointer">
                                    <input type="radio" v-model="selectedPhotographer" value="all"
                                        @change="filterByPhotographer" class="sr-only">
                                    <div :class="[
                                        'px-5 py-2.5 rounded-full text-xs font-bold uppercase tracking-wider transition-all duration-200 border',
                                        selectedPhotographer === 'all'
                                            ? 'bg-black text-white border-black shadow-md'
                                            : 'bg-gray-50 text-gray-500 border-gray-200 hover:bg-gray-100 hover:text-black'
                                    ]">
                                        Todos los archivos
                                    </div>
                                </label>

                                <label v-for="photographer in photographers" :key="photographer.id"
                                    class="cursor-pointer">
                                    <input type="radio" v-model="selectedPhotographer" :value="photographer.id"
                                        @change="filterByPhotographer" class="sr-only">
                                    <div :class="[
                                        'px-5 py-2.5 rounded-full text-xs font-bold uppercase tracking-wider transition-all duration-200 border flex items-center gap-2',
                                        selectedPhotographer === photographer.id
                                            ? 'bg-black text-white border-black shadow-md'
                                            : 'bg-gray-50 text-gray-500 border-gray-200 hover:bg-gray-100 hover:text-black'
                                    ]">
                                        {{ photographer.business_name || photographer.user.name }}
                                        <span :class="[
                                            'px-1.5 py-0.5 rounded-full text-[9px] leading-none',
                                            selectedPhotographer === photographer.id ? 'bg-white/20 text-white' : 'bg-gray-200 text-gray-600'
                                        ]">
                                            {{ photographer.photos_count }}
                                        </span>
                                    </div>
                                </label>
                            </div>
                        </div>


                        <div v-if="!photos.data || photos.data.length === 0"
                            class="bg-white rounded border border-gray-100 shadow-sm flex flex-col items-center justify-center py-32 px-4 text-center">
                            <div class="w-20 h-20 bg-gray-50 rounded-full flex items-center justify-center mb-6">
                                <PhotoIcon class="h-10 w-10 text-gray-300" />
                            </div>
                            <h4 class="font-flux text-4xl text-black mb-2">Bóveda Vacía</h4>
                            <p class="text-gray-500 mb-8 max-w-sm">No hay fotografías disponibles bajo estos filtros o
                                aún no se han
                                asignado fotos.</p>
                            <button @click="showUploadModal = true"
                                class="bg-black text-white px-8 py-3.5 rounded-full font-bold text-xs uppercase tracking-wider hover:bg-gray-800 transition-colors shadow-md">
                                Asignar Fotografías
                            </button>
                        </div>

                        <div v-else>
                            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4 mb-10">
                                <div v-for="photo in photos.data" :key="photo.id"
                                    class="group relative aspect-square bg-gray-100 rounded overflow-hidden shadow-sm hover:shadow-lg transition-all duration-300 border border-gray-200 hover:border-gray-300">

                                    <img :src="photo.thumbnail_url" :alt="photo.unique_id"
                                        class="w-full h-full object-cover transition-transform duration-700" />


                                    <div class="absolute top-2 left-2 z-20 flex items-center group/tooltip">
                                        <div
                                            class="bg-black/60 backdrop-blur-md p-1.5 rounded-full text-white cursor-help shadow-sm group-hover/tooltip:bg-[#E30613] transition-colors">
                                            <UserIcon class="w-4 h-4" />
                                        </div>

                                        <span
                                            class="absolute left-full ml-2 px-2.5 py-1.5 bg-white text-black text-[9px] font-bold uppercase tracking-wider rounded shadow-xl opacity-0 group-hover/tooltip:opacity-100 transition-opacity duration-200 whitespace-nowrap pointer-events-none border border-gray-100 z-30">
                                            {{ photo.photographer?.business_name || 'Fotógrafo F33' }}
                                        </span>
                                    </div>


                                    <div
                                        class="absolute inset-0 bg-black/60 opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex flex-col justify-between p-3 z-10">

                                        <div class="flex justify-end">
                                            <span
                                                class="bg-white/90 backdrop-blur-sm text-black px-2 py-1 rounded text-[9px] font-bold tracking-wider">
                                                #{{ photo.unique_id }}
                                            </span>
                                        </div>

                                        <div
                                            class="flex items-center justify-center gap-3 translate-y-4 group-hover:translate-y-0 transition-transform duration-300">

                                            <button v-if="permissions.is_creator" @click="updateCoverImage(photo.id)"
                                                title="Fijar como portada"
                                                class="w-10 h-10 bg-white/20 backdrop-blur-md rounded-full flex items-center justify-center text-white hover:bg-white hover:text-black transition-colors">
                                                <PhotoIcon class="w-5 h-5" />
                                            </button>

                                            <button
                                                v-if="permissions.is_creator || photo.photographer_id === current_photographer_id"
                                                @click="deletePhoto(photo.id)" title="Eliminar captura"
                                                class="w-10 h-10 bg-[#E30613]/90 backdrop-blur-md rounded-full flex items-center justify-center text-white hover:bg-[#E30613] transition-colors shadow-lg">
                                                <TrashIcon class="w-5 h-5" />
                                            </button>

                                        </div>



                                        <div class="flex justify-end">
                                            <div v-if="photo.downloads > 0"
                                                class="bg-white text-black px-2 py-1 rounded flex items-center gap-1 text-[10px] font-bold">
                                                <ArrowDownTrayIcon class="w-3 h-3 text-[#E30613]" /> {{ photo.downloads
                                                }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>


                            <div v-if="photos.last_page > 1" class="flex justify-center pt-8 border-t border-gray-200">
                                <div
                                    class="flex flex-wrap items-center gap-2 bg-white p-2 rounded-full shadow-sm border border-gray-100">

                                    <Link v-if="photos.prev_page_url" :href="photos.prev_page_url"
                                        class="h-10 px-4 flex items-center justify-center text-xs font-bold uppercase tracking-wider text-gray-600 hover:bg-gray-100 rounded-full transition-colors">
                                        Ant
                                    </Link>
                                    <span v-else
                                        class="h-10 px-4 flex items-center justify-center text-xs font-bold uppercase tracking-wider text-gray-300 cursor-not-allowed">Ant</span>


                                    <template v-for="(page, index) in paginationPages" :key="index">
                                        <span v-if="page === photos.current_page"
                                            class="h-10 w-10 flex items-center justify-center text-xs font-bold bg-black text-white rounded-full shadow-md">
                                            {{ page }}
                                        </span>
                                        <span v-else-if="page === '...'"
                                            class="h-10 w-10 flex items-center justify-center text-xs text-gray-400">...</span>
                                        <Link v-else :href="photos.path + '?page=' + page"
                                            class="h-10 w-10 flex items-center justify-center text-xs font-bold text-gray-600 hover:bg-gray-100 rounded-full transition-colors">
                                            {{ page }}
                                        </Link>
                                    </template>


                                    <Link v-if="photos.next_page_url" :href="photos.next_page_url"
                                        class="h-10 px-4 flex items-center justify-center text-xs font-bold uppercase tracking-wider text-gray-600 hover:bg-gray-100 rounded-full transition-colors">
                                        Sig
                                    </Link>
                                    <span v-else
                                        class="h-10 px-4 flex items-center justify-center text-xs font-bold uppercase tracking-wider text-gray-300 cursor-not-allowed">Sig</span>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

            </div>
        </div>


        <transition enter-active-class="transition duration-300 ease-out" enter-from-class="opacity-0"
            enter-to-class="opacity-100" leave-active-class="transition duration-200 ease-in"
            leave-from-class="opacity-100" leave-to-class="opacity-0">

            <div v-if="showUploadModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6">
                <!-- Backdrop -->
                <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" @click="closeModal"></div>


                <div
                    class="relative bg-white rounded shadow-2xl max-w-4xl w-full max-h-[85vh] flex flex-col overflow-hidden z-10">


                    <div class="px-6 py-5 border-b border-gray-100 bg-white flex justify-between items-center z-10">
                        <div>
                            <h3 class="font-bold text-xl text-black">Asignar fotografías cargadas anteriormente</h3>
                            <p class="text-xs text-gray-500 mt-1">Seleccioná las fotografías disponibles en tu bóveda
                                personal
                                para vincularlas a este evento.</p>
                        </div>
                        <button @click="closeModal"
                            class="w-10 h-10 rounded-full bg-gray-50 flex items-center justify-center text-gray-500 hover:bg-red-50 hover:text-[#E30613] transition-colors">
                            <XMarkIcon class="w-5 h-5" />
                        </button>
                    </div>


                    <div class="p-6 overflow-y-auto flex-1 bg-gray-50 hide-scrollbar">
                        <div v-if="!unassignedPhotos || unassignedPhotos.length === 0"
                            class="flex flex-col items-center justify-center py-16 text-center">
                            <div
                                class="w-16 h-16 bg-white rounded-full flex items-center justify-center shadow-sm border border-gray-100 mb-4">
                                <PhotoIcon class="w-8 h-8 text-gray-300" />
                            </div>
                            <h4 class="font-bold text-lg text-slate-700 mb-1">Sin fotografías</h4>
                            <p class="text-sm text-gray-500">No tenés fotografías pendientes de asignación en tu bóveda.
                            </p>
                        </div>

                        <div v-else>
                            <div class="flex justify-between items-center mb-4">
                                <span class="text-xs font-bold uppercase tracking-wider text-gray-500">
                                    Disponibles ({{ unassignedPhotos.length }})
                                </span>
                                <span v-if="selectedExistingPhotos.length > 0"
                                    class="text-xs font-bold text-[#E30613] bg-red-50 px-3 py-1 rounded-full">
                                    {{ selectedExistingPhotos.length }} seleccionadas
                                </span>
                            </div>

                            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-3">
                                <div v-for="photo in unassignedPhotos" :key="photo.id"
                                    @click="togglePhotoSelection(photo.id)"
                                    class="relative aspect-square rounded overflow-hidden cursor-pointer shadow-sm transition-all duration-200 group"
                                    :class="selectedExistingPhotos.includes(photo.id) ? 'ring-4 ring-[#E30613] ring-offset-2 scale-95' : 'hover:shadow-md'">

                                    <img :src="photo.thumbnail_url"
                                        class="w-full h-full object-cover transition-transform duration-500 " />


                                    <div class="absolute inset-0 transition-colors duration-200"
                                        :class="selectedExistingPhotos.includes(photo.id) ? 'bg-[#E30613]/20' : 'bg-black/0 group-hover:bg-black/10'">
                                        <div v-if="selectedExistingPhotos.includes(photo.id)"
                                            class="absolute top-2 right-2 w-6 h-6 bg-[#E30613] text-white rounded-full flex items-center justify-center shadow-md transform scale-100 transition-transform">
                                            <CheckIcon class="w-4 h-4" />
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>


                    <div class="p-6 border-t border-gray-100 bg-white flex justify-end gap-3 z-10">
                        <button @click="closeModal"
                            class="px-6 py-3 rounded-full border border-gray-200 text-gray-600 font-bold text-xs uppercase tracking-wider hover:bg-gray-50 transition-colors">
                            Cancelar
                        </button>
                        <button @click="assignExistingPhotos" :disabled="selectedExistingPhotos.length === 0"
                            class="px-8 py-3 rounded-full bg-black text-white font-bold text-xs uppercase tracking-wider hover:bg-gray-800 transition-colors disabled:opacity-50 disabled:cursor-not-allowed shadow-md">
                            Asignar fotografías
                        </button>
                    </div>

                </div>
            </div>
        </transition>

        <transition enter-active-class="transition duration-300 ease-out" enter-from-class="opacity-0"
            enter-to-class="opacity-100" leave-active-class="transition duration-200 ease-in"
            leave-from-class="opacity-100" leave-to-class="opacity-0">

            <div v-if="showInviteModal" class="fixed inset-0 z-[60] flex items-center justify-center p-4 sm:p-6">

                <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" @click="showInviteModal = false"></div>

                <div class="relative bg-white rounded shadow-2xl max-w-lg w-full flex flex-col overflow-hidden z-10">
                    <div class="px-6 py-5 border-b border-gray-100 bg-white flex justify-between items-center z-10">
                        <div>
                            <h3 class="font-bold text-xl text-black">Invitar fotógrafo</h3>
                            <p class="text-xs text-gray-500 mt-1">Ingresá el correo con el que está registrado en F33.
                            </p>
                        </div>
                        <button @click="showInviteModal = false"
                            class="w-10 h-10 rounded-full bg-gray-50 flex items-center justify-center text-gray-500 hover:bg-red-50 hover:text-[#E30613] transition-colors">
                            <XMarkIcon class="w-5 h-5" />
                        </button>
                    </div>

                    <form @submit.prevent="invitePhotographer" class="p-6 bg-gray-50">
                        <div class="mb-6">
                            <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 mb-2 ml-1">
                                Correo del fotografo
                            </label>
                            <div class="relative">
                                <EnvelopeIcon class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" />
                                <input v-model="inviteForm.email" type="email" required
                                    class="w-full bg-white border border-gray-200 focus:bg-white focus:border-red-600 focus:ring-1 focus:ring-red-600 text-slate-800 font-bold text-sm py-3 pl-12 pr-4 rounded-xl transition-all outline-none"
                                    placeholder="ejemplo@correo.com">
                            </div>
                            <p v-if="inviteForm.errors.email" class="text-[#E30613] text-xs font-bold mt-2">{{
                                inviteForm.errors.email }}</p>
                        </div>

                        <div class="flex justify-end gap-3">
                            <button type="button" @click="showInviteModal = false"
                                class="px-6 py-3 rounded-full border border-gray-200 text-gray-600 font-bold text-xs uppercase tracking-wider hover:bg-white transition-colors">
                                Cancelar
                            </button>
                            <button type="submit" :disabled="inviteForm.processing"
                                class="px-8 py-3 rounded-full bg-red-600 text-white font-bold text-xs uppercase tracking-wider hover:bg-black transition-colors disabled:opacity-50 shadow-md">
                                <span v-if="inviteForm.processing">Enviando...</span>
                                <span v-else>Enviar Invitación</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </transition>

    </AuthenticatedLayout>
</template>

<style scoped>
.masonry-grid {
    column-fill: balance;
}

.hide-scrollbar::-webkit-scrollbar {
    display: none;
}

.hide-scrollbar {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
</style>

<style scoped>
.scrollbar-hide::-webkit-scrollbar {
    display: none;
}

.scrollbar-hide {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
</style>