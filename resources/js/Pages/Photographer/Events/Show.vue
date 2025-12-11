<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ref, computed, onMounted } from 'vue';
import { useConfirm } from '@/Composables/useConfirm';
import { useToast } from '@/Composables/useToast';
import * as faceapi from 'face-api.js';
import '@tensorflow/tfjs-backend-webgl';
import {
    CalendarIcon,
    MapPinIcon,
    PhotoIcon,
    ArrowDownTrayIcon,
    EyeIcon,
    UserGroupIcon,
    TrashIcon,
    CloudArrowUpIcon,
    ClipboardDocumentIcon,
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
});

const { confirm } = useConfirm();
const { success } = useToast();

const modelsLoaded = ref(false);
const processingFaces = ref(false);
const faceDetectionResults = ref([]);

onMounted(async () => {
    try {
        console.log(' Cargando modelos de reconocimiento facial...');

        //  Verificar que faceapi existe
        if (!faceapi) {
            throw new Error('face-api.js no está disponible');
        }

        await faceapi.tf.setBackend('webgl');
        await faceapi.tf.ready();
        console.log(' Backend:', faceapi.tf.getBackend());

        const MODEL_URL = '/models';

        await Promise.all([
            faceapi.nets.ssdMobilenetv1.loadFromUri(MODEL_URL),
            faceapi.nets.faceLandmark68Net.loadFromUri(MODEL_URL),
            faceapi.nets.faceRecognitionNet.loadFromUri(MODEL_URL),
        ]);

        modelsLoaded.value = true;
        console.log(' Modelos cargados correctamente');
    } catch (err) {
        console.error(' Error:', err);
        try {
            console.log(' Intentando con CPU...');
            await faceapi.tf.setBackend('cpu');
            await faceapi.tf.ready();

            const MODEL_URL = '/models';
            await Promise.all([
                faceapi.nets.ssdMobilenetv1.loadFromUri(MODEL_URL),
                faceapi.nets.faceLandmark68Net.loadFromUri(MODEL_URL),
                faceapi.nets.faceRecognitionNet.loadFromUri(MODEL_URL),
            ]);

            modelsLoaded.value = true;
            console.log(' Modelos cargados con CPU');
        } catch (cpuErr) {
            console.error(' Error con CPU también:', cpuErr);
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

const handleFileSelect = async (e) => {
    const files = Array.from(e.target.files);
    selectedFiles.value = files;
    uploadForm.photos = files;
    previewUrls.value = [];

    const previews = await Promise.all(
        files.map(file => {
            return new Promise(resolve => {
                const reader = new FileReader();
                reader.onload = (e) => resolve(e.target.result);
                reader.readAsDataURL(file);
            });
        })
    );

    previewUrls.value = previews;

    if (modelsLoaded.value) {
        detectFacesInImages();
    }
};

//  VERSIÓN CORREGIDA
const detectFacesInImages = async () => {
    processingFaces.value = true;
    faceDetectionResults.value = [];

    console.log('🔍 Detectando rostros en las imágenes...');

    //  Verificar que faceapi está disponible
    if (!faceapi || !faceapi.detectAllFaces) {
        console.error(' faceapi no está disponible');
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

            console.log(`  Procesando foto ${i + 1}...`);

            //  Usar faceapi directamente (ya importado arriba)
            const detections = await faceapi
                .detectAllFaces(img, new faceapi.SsdMobilenetv1Options({
                    minConfidence: 0.5
                }))
                .withFaceLandmarks()
                .withFaceDescriptors();

            const descriptors = detections.map(d => Array.from(d.descriptor));

            faceDetectionResults.value.push({
                index: i,
                count: detections.length,
                descriptors: descriptors,
            });

            console.log(`  ✓ Foto ${i + 1}: ${detections.length} rostro(s)`);

        } catch (error) {
            console.error(` Error en foto ${i + 1}:`, error);
            faceDetectionResults.value.push({
                index: i,
                count: 0,
                descriptors: [],
            });
        }
    }

    processingFaces.value = false;

    const totalFaces = faceDetectionResults.value.reduce((sum, r) => sum + r.count, 0);
    console.log(` ${totalFaces} rostros detectados`);
};

const uploadPhotos = () => {
    if (faceDetectionResults.value.length > 0) {
        uploadForm.face_data = JSON.stringify(faceDetectionResults.value);
    }

    uploadForm.post(route('photographer.photos.store'), {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
            showUploadModal.value = false;
            selectedFiles.value = [];
            previewUrls.value = [];
            faceDetectionResults.value = [];
            uploadForm.reset('photos', 'face_data');
            success('Fotos subidas correctamente');
        },
    });
};

const deletePhoto = async (photoId) => {
    const confirmed = await confirm({
        title: 'Eliminar Fotografía',
        message: '¿Confirmar eliminación definitiva?',
        confirmText: 'Eliminar',
        cancelText: 'Cancelar',
        type: 'danger'
    });

    if (confirmed) {
        router.delete(route('photographer.photos.destroy', photoId), {
            preserveScroll: true,
            onSuccess: () => success('Fotografía eliminada')
        });
    }
};

const updateCoverImage = async (photoId) => {
    const confirmed = await confirm({
        title: 'Cambiar Portada',
        message: '¿Establecer esta foto como portada?',
        confirmText: 'Establecer',
        cancelText: 'Cancelar',
        type: 'info'
    });

    if (confirmed) {
        router.post(
            route('photographer.events.cover-image', props.event.id),
            { photo_id: photoId },
            { preserveScroll: true, onSuccess: () => success('Portada actualizada') }
        );
    }
};

const formatDate = (dateString) => {
    if (!dateString) return 'S/F';
    return new Date(dateString).toLocaleDateString('es-ES', {
        year: 'numeric', month: 'long', day: 'numeric'
    });
};

const privateUrl = computed(() => `${window.location.origin}/eventos/${props.event.slug}?token=${props.event.private_token}`);
const publicUrl = computed(() => `${window.location.origin}/eventos/${props.event.slug}`);

const copyToClipboard = async (text) => {
    try {
        await navigator.clipboard.writeText(text);
        success('URL copiada al portapapeles');
    } catch (err) {
        console.error('Error copiando:', err);
    }
};
</script>


<template>

    <Head :title="event.name" />

    <AuthenticatedLayout>

        <div class="relative h-80 bg-slate-900 overflow-hidden">
            <img v-if="event.cover_image_url" :src="event.cover_image_url"
                class="absolute inset-0 w-full h-full object-cover opacity-50" />
            <div v-else class="absolute inset-0 bg-slate-800 flex items-center justify-center opacity-50">
                <PhotoIcon class="w-24 h-24 text-slate-700" />
            </div>

            <div class="absolute inset-0 bg-gradient-to-t from-slate-900 via-slate-900/40 to-transparent"></div>

            <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-full flex flex-col justify-end pb-10">
                <div class="flex flex-col md:flex-row md:items-end justify-between gap-6">
                    <div>
                        <div
                            class="flex items-center gap-3 mb-3 text-xs font-bold uppercase tracking-widest text-white/60">
                            <Link :href="route('photographer.events.index')" class="hover:text-white transition">Eventos
                            </Link>
                            <span>/</span>
                            <span class="text-white">{{ event.name }}</span>
                        </div>
                        <h1 class="text-4xl md:text-5xl font-serif font-bold text-white mb-2">{{ event.name }}</h1>
                        <div class="flex items-center gap-6 text-sm text-white/80 font-light">
                            <span class="flex items-center gap-2" v-if="event.event_date">
                                <CalendarIcon class="w-4 h-4" /> {{ formatDate(event.event_date) }}
                            </span>
                            <span class="flex items-center gap-2" v-if="event.location">
                                <MapPinIcon class="w-4 h-4" /> {{ event.location }}
                            </span>
                        </div>
                    </div>

                    <div class="flex gap-3">
                        <Link :href="route('photographer.events.edit', event.id)"
                            class="px-5 py-3 border border-white/30 text-white text-xs font-bold uppercase tracking-widest hover:bg-white hover:text-slate-900 transition rounded-sm">
                            Editar
                        </Link>
                        <button @click="showUploadModal = true"
                            class="px-5 py-3 bg-white text-slate-900 text-xs font-bold uppercase tracking-widest hover:bg-slate-200 transition rounded-sm shadow-lg flex items-center gap-2">
                            <CloudArrowUpIcon class="w-4 h-4" /> Subir Fotos
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="py-12 bg-gray-50 min-h-screen">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

                    <div class="lg:col-span-1 space-y-8">

                        <div class="bg-white border border-gray-200 rounded-sm p-6 shadow-sm">
                            <h3
                                class="text-xs font-bold uppercase tracking-widest text-slate-900 mb-6 border-b border-gray-100 pb-2">
                                Métricas</h3>
                            <div class="space-y-6">
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-slate-500">Total Archivos</span>
                                    <span class="text-xl font-serif font-bold text-slate-900">{{ stats.total_photos
                                    }}</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-slate-500">Públicas</span>
                                    <span class="text-xl font-serif font-bold text-emerald-600">{{ stats.active_photos
                                    }}</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-slate-500">Descargas</span>
                                    <span class="text-xl font-serif font-bold text-slate-900">{{ stats.total_downloads
                                    }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white border border-gray-200 rounded-sm p-6 shadow-sm">
                            <h3
                                class="text-xs font-bold uppercase tracking-widest text-slate-900 mb-6 border-b border-gray-100 pb-2">
                                Acceso & Compartir</h3>

                            <div v-if="event.is_private" class="mb-6">
                                <div class="flex items-center gap-2 mb-2 text-amber-600">
                                    <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                                    <span class="text-xs font-bold uppercase tracking-wide">Enlace Privado</span>
                                </div>
                                <div class="relative group">
                                    <input :value="privateUrl" readonly
                                        class="w-full bg-gray-50 border border-gray-200 text-xs text-slate-500 p-3 rounded-sm font-mono focus:outline-none" />
                                    <button @click="copyToClipboard(privateUrl)"
                                        class="absolute right-2 top-2 p-1 text-slate-400 hover:text-slate-900 transition">
                                        <ClipboardDocumentIcon class="w-4 h-4" />
                                    </button>
                                </div>
                                <p class="text-[10px] text-slate-400 mt-2 leading-relaxed">Requiere token de acceso.
                                    Solo usuarios con este link pueden ver la galería.</p>
                            </div>

                            <div v-if="!event.is_private && event.is_active">
                                <div class="flex items-center gap-2 mb-2 text-emerald-600">
                                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                    <span class="text-xs font-bold uppercase tracking-wide">Enlace Público</span>
                                </div>
                                <div class="relative group">
                                    <input :value="publicUrl" readonly
                                        class="w-full bg-gray-50 border border-gray-200 text-xs text-slate-500 p-3 rounded-sm font-mono focus:outline-none" />
                                    <button @click="copyToClipboard(publicUrl)"
                                        class="absolute right-2 top-2 p-1 text-slate-400 hover:text-slate-900 transition">
                                        <ClipboardDocumentIcon class="w-4 h-4" />
                                    </button>
                                </div>
                            </div>

                            <transition enter-active-class="transition ease-out duration-300"
                                enter-from-class="opacity-0 translate-y-2" enter-to-class="opacity-100 translate-y-0"
                                leave-active-class="transition ease-in duration-200" leave-from-class="opacity-100"
                                leave-to-class="opacity-0">
                                <div v-if="copyFeedback"
                                    class="absolute bottom-4 left-1/2 -translate-x-1/2 bg-slate-900 text-white text-xs px-4 py-2 rounded-sm shadow-lg flex items-center gap-2">
                                    <CheckIcon class="w-3 h-3" /> Copiado
                                </div>
                            </transition>
                        </div>

                        <div class="bg-white border border-gray-200 rounded-sm p-6 shadow-sm">
                            <div class="flex justify-between items-center mb-6 border-b border-gray-100 pb-2">
                                <h3
                                    class="text-xs font-bold uppercase tracking-widest text-slate-900 flex items-center gap-2">
                                    <UserGroupIcon class="w-4 h-4" /> Equipo
                                </h3>
                                <button
                                    class="text-[10px] font-bold uppercase tracking-widest text-indigo-600 hover:text-slate-900 transition">
                                    + Invitar
                                </button>
                            </div>

                            <div class="space-y-4">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="w-8 h-8 rounded-full bg-slate-900 flex items-center justify-center text-white text-xs font-serif border-2 border-white shadow-sm ring-1 ring-gray-100">
                                        {{ event.photographer?.business_name?.charAt(0) || 'YO' }}
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-xs font-bold text-slate-900 truncate">
                                            {{ event.photographer?.business_name || 'Tú' }}
                                        </p>
                                        <p class="text-[10px] text-slate-400 uppercase tracking-wider">Organizador</p>
                                    </div>
                                </div>

                                <div v-if="event.collaborators && event.collaborators.length > 0"
                                    v-for="collab in event.collaborators" :key="collab.id"
                                    class="flex items-center gap-3 pt-3 border-t border-gray-50">

                                    <img v-if="collab.profile_photo_url" :src="collab.profile_photo_url"
                                        class="w-8 h-8 rounded-full object-cover border-2 border-white shadow-sm ring-1 ring-gray-100">
                                    <div v-else
                                        class="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center text-gray-500 text-xs font-serif border-2 border-white shadow-sm ring-1 ring-gray-100">
                                        {{ collab.business_name.charAt(0) }}
                                    </div>

                                    <div class="flex-1 min-w-0">
                                        <p class="text-xs font-bold text-slate-700 truncate">{{ collab.business_name }}
                                        </p>
                                        <p class="text-[10px] text-slate-400 uppercase tracking-wider">Colaborador</p>
                                    </div>

                                    <button class="text-slate-300 hover:text-red-500 transition">
                                        <XMarkIcon class="w-4 h-4" />
                                    </button>
                                </div>

                                <div v-else class="pt-2">
                                    <p class="text-xs text-slate-400 font-light italic">
                                        Aún no hay colaboradores asignados.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div v-if="event.description" class="bg-white border border-gray-200 rounded-sm p-6 shadow-sm">
                            <h3
                                class="text-xs font-bold uppercase tracking-widest text-slate-900 mb-4 border-b border-gray-100 pb-2">
                                Notas</h3>
                            <p class="text-sm text-slate-600 font-light leading-relaxed">{{ event.description }}</p>
                        </div>

                    </div>

                    <div class="lg:col-span-2">

                        <div v-if="!photos.data || photos.data.length === 0"
                            class="text-center py-24 border border-dashed border-gray-300 rounded-sm bg-white">
                            <PhotoIcon class="h-12 w-12 mx-auto text-gray-300 mb-4 stroke-1" />
                            <h4 class="text-lg font-serif font-medium text-slate-900 mb-1">Sin material</h4>
                            <p class="text-xs text-slate-500 font-light mb-6">Esta galería está vacía.</p>
                            <button @click="showUploadModal = true"
                                class="text-xs font-bold uppercase tracking-widest text-slate-900 border-b border-slate-900 pb-0.5 hover:text-slate-600 hover:border-slate-600 transition">
                                Iniciar Carga
                            </button>
                        </div>

                        <div v-else>
                            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4 mb-8">
                                <div v-for="photo in photos.data" :key="photo.id"
                                    class="group relative aspect-square bg-gray-100 border border-gray-200 rounded-sm overflow-hidden">
                                    <img :src="photo.thumbnail_url" :alt="photo.unique_id"
                                        class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105 filter grayscale-[0.3] group-hover:grayscale-0" />

                                    <div
                                        class="absolute inset-0 bg-slate-900/80 opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex flex-col items-center justify-center gap-3 p-4">
                                        <div class="flex gap-2">
                                            <button @click="updateCoverImage(photo.id)" title="Usar como portada"
                                                class="p-2 bg-white/10 hover:bg-white text-white hover:text-slate-900 rounded-sm transition">
                                                <PhotoIcon class="w-4 h-4" />
                                            </button>
                                            <button @click="deletePhoto(photo.id)" title="Eliminar"
                                                class="p-2 bg-white/10 hover:bg-red-600 text-white rounded-sm transition">
                                                <TrashIcon class="w-4 h-4" />
                                            </button>
                                        </div>
                                        <div class="text-[9px] text-white/60 font-mono mt-2">{{ photo.unique_id }}</div>
                                    </div>

                                    <div v-if="photo.downloads > 0"
                                        class="absolute bottom-1 right-1 bg-slate-900/80 text-white text-[9px] px-1.5 py-0.5 rounded-sm flex items-center gap-1">
                                        <ArrowDownTrayIcon class="w-3 h-3" /> {{ photo.downloads }}
                                    </div>
                                </div>
                            </div>

                            <div v-if="photos.last_page > 1"
                                class="flex justify-center gap-1 pt-6 border-t border-gray-200">
                                <Link v-for="(link, index) in photos.links" :key="index" :href="link.url || '#'"
                                    v-html="link.label"
                                    :class="['h-8 min-w-[2rem] px-2 flex items-center justify-center text-xs font-medium rounded-sm border transition-colors', link.active ? 'bg-slate-900 text-white border-slate-900' : 'bg-white text-slate-600 border-gray-200 hover:border-slate-400']" />
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <!--  MODAL MODIFICADO -->
        <div v-if="showUploadModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-slate-900/80 backdrop-blur-sm" @click="showUploadModal = false"></div>
            <div
                class="relative bg-white rounded-sm shadow-2xl max-w-3xl w-full max-h-[85vh] overflow-hidden flex flex-col">

                <!-- Header con indicador de IA -->
                <div class="p-6 border-b border-gray-100 flex justify-between items-center bg-white">
                    <div>
                        <h3 class="text-lg font-serif font-bold text-slate-900">Cargar Material</h3>
                        <!--  NUEVO: Indicador de estado de IA -->
                        <p v-if="modelsLoaded" class="text-xs text-green-600 mt-1 flex items-center gap-1">
                            <CheckIcon class="w-3 h-3" />
                            Reconocimiento facial activo
                        </p>
                        <p v-else class="text-xs text-gray-400 mt-1">
                            Reconocimiento facial no disponible
                        </p>
                    </div>
                    <button @click="showUploadModal = false" class="text-slate-400 hover:text-slate-900">
                        <XMarkIcon class="w-6 h-6" />
                    </button>
                </div>

                <!-- Body -->
                <div class="p-6 overflow-y-auto flex-1">
                    <!--  NUEVO: Banner de procesamiento -->
                    <div v-if="processingFaces" class="bg-blue-50 border border-blue-200 rounded-sm p-4 mb-4">
                        <div class="flex items-center gap-3">
                            <div
                                class="animate-spin rounded-full h-5 w-5 border-2 border-blue-600 border-t-transparent">
                            </div>
                            <div>
                                <p class="text-sm font-medium text-blue-900">Detectando rostros...</p>
                                <p class="text-xs text-blue-700 mt-0.5">Esto puede tardar unos segundos</p>
                            </div>
                        </div>
                    </div>

                    <!-- Zona de carga -->
                    <div v-if="selectedFiles.length === 0"
                        class="border-2 border-dashed border-gray-300 rounded-sm p-12 text-center hover:border-slate-400 transition-colors bg-gray-50">
                        <input type="file" multiple accept="image/*" @change="handleFileSelect" class="hidden"
                            id="file-upload">
                        <label for="file-upload" class="cursor-pointer block h-full">
                            <CloudArrowUpIcon class="w-12 h-12 mx-auto text-slate-300 mb-4" />
                            <span
                                class="text-xs font-bold uppercase tracking-widest text-slate-900 block mb-2">Seleccionar
                                Archivos</span>
                            <span class="text-xs text-slate-500 font-light">JPG, PNG • Máx 10MB</span>
                        </label>
                    </div>

                    <!-- Previews con badges de rostros -->
                    <div v-else>
                        <div class="grid grid-cols-4 sm:grid-cols-5 gap-3 mb-6">
                            <div v-for="(url, index) in previewUrls" :key="index"
                                class="relative aspect-square bg-gray-100 rounded-sm overflow-hidden border border-gray-200 group">
                                <img :src="url" class="w-full h-full object-cover" />

                                <!--  NUEVO: Badge con número de rostros -->
                                <div v-if="faceDetectionResults[index]"
                                    class="absolute top-2 right-2 px-2 py-1 rounded-full text-xs font-bold shadow-lg"
                                    :class="faceDetectionResults[index].count > 0
                                        ? 'bg-green-500 text-white'
                                        : 'bg-gray-400 text-white'">
                                    <span v-if="faceDetectionResults[index].count > 0">
                                        {{ faceDetectionResults[index].count }} 
                                    </span>
                                    <span v-else>—</span>
                                </div>

                                <!--  NUEVO: Loading spinner mientras procesa -->
                                <div v-else-if="processingFaces"
                                    class="absolute inset-0 bg-black/30 flex items-center justify-center">
                                    <div
                                        class="animate-spin rounded-full h-6 w-6 border-2 border-white border-t-transparent">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!--  NUEVO: Resumen de detección -->
                        <div class="text-right space-y-1">
                            <p class="text-xs text-slate-500">
                                {{ selectedFiles.length }} archivo(s) seleccionado(s)
                            </p>
                            <p v-if="faceDetectionResults.length > 0 && !processingFaces" class="text-xs font-medium"
                                :class="faceDetectionResults.filter(r => r.count > 0).length > 0
                                    ? 'text-green-600'
                                    : 'text-gray-500'">
                                <CheckIcon class="w-3 h-3 inline" />
                                {{faceDetectionResults.filter(r => r.count > 0).length}} foto(s) con rostros
                                detectados
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <div class="p-6 border-t border-gray-100 bg-gray-50 flex justify-end gap-3">
                    <button @click="showUploadModal = false"
                        class="px-6 py-3 border border-gray-300 text-slate-600 text-xs font-bold uppercase tracking-widest hover:bg-white transition rounded-sm">
                        Cancelar
                    </button>
                    <button @click="uploadPhotos"
                        :disabled="uploadForm.processing || selectedFiles.length === 0 || processingFaces"
                        class="px-6 py-3 bg-slate-900 text-white text-xs font-bold uppercase tracking-widest hover:bg-slate-800 transition rounded-sm disabled:opacity-50 disabled:cursor-not-allowed">
                        <span v-if="processingFaces">Procesando rostros...</span>
                        <span v-else-if="uploadForm.processing">Subiendo...</span>
                        <span v-else>Iniciar Carga</span>
                    </button>
                </div>
            </div>
        </div>

    </AuthenticatedLayout>
</template>