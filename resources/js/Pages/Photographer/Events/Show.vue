<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ref, computed, onMounted } from 'vue';
import { useConfirm } from '@/Composables/useConfirm';
import { useToast } from '@/Composables/useToast';
import * as faceapi from 'face-api.js';
import '@tensorflow/tfjs-backend-webgl'
import Tesseract from 'tesseract.js';

import {
    CalendarIcon,
    MapPinIcon,
    PhotoIcon,
    ArrowDownTrayIcon,
    EyeIcon,
    UserGroupIcon,
    TrashIcon,
    CloudArrowUpIcon,
    HashtagIcon,
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
    unassignedPhotos: { type: Array, default: () => [] },
});

const { confirm } = useConfirm();
const { success } = useToast();

const modelsLoaded = ref(false);
const processingFaces = ref(false);
const faceDetectionResults = ref([]);


const processingBibs = ref(false);
const bibDetectionResults = ref([]);


const uploadMode = ref('upload'); // 'upload' o 'existing'
const selectedExistingPhotos = ref([]);



//  Estadísticas computadas
const totalFacesDetected = computed(() => {
    return faceDetectionResults.value.reduce((sum, result) => sum + result.count, 0);
});

const totalBibsDetected = computed(() => {
    return bibDetectionResults.value.reduce((sum, result) => sum + result.numbers.length, 0);
});

const photosWithFaces = computed(() => {
    return faceDetectionResults.value.filter(r => r.count > 0).length;
});

const photosWithBibs = computed(() => {
    return bibDetectionResults.value.filter(r => r.numbers.length > 0).length;
});




// Variable temporal para lo que el usuario está escribiendo en el input nuevo
const tempBibInput = ref({});

// Agregar dorsal al presionar Enter
const addBibTag = (index, event) => {
    const val = event.target.value.trim();
    if (!val) return;

    // Inicializar si no existe
    if (!bibDetectionResults.value[index]) {
        bibDetectionResults.value[index] = { index: index, numbers: [], raw_text: '' };
    }

    // Evitar duplicados
    if (!bibDetectionResults.value[index].numbers.includes(val)) {
        bibDetectionResults.value[index].numbers.push(val);
    }

    // Limpiar input temporal
    event.target.value = '';
    // Opcional: limpiar variable reactiva si la usaras, acá usamos el evento directo
};

// Eliminar un dorsal específico
const removeBibTag = (index, numberToRemove) => {
    if (bibDetectionResults.value[index]) {
        bibDetectionResults.value[index].numbers = bibDetectionResults.value[index].numbers.filter(n => n !== numberToRemove);
    }
};

// Eliminar el último con Backspace si el input está vacío
const handleBackspace = (index, event) => {
    if (event.target.value === '' && bibDetectionResults.value[index]?.numbers?.length > 0) {
        bibDetectionResults.value[index].numbers.pop();
    }
};



//  NUEVA FUNCIÓN: Alternar selección de fotos existentes
const togglePhotoSelection = (photoId) => {
    const index = selectedExistingPhotos.value.indexOf(photoId);
    if (index > -1) {
        selectedExistingPhotos.value.splice(index, 1);
    } else {
        selectedExistingPhotos.value.push(photoId);
    }
};

//  NUEVA FUNCIÓN: Asignar fotos existentes
const assignExistingPhotos = () => {
    if (selectedExistingPhotos.value.length === 0) return;

    router.post(route('photographer.photos.assign-to-event'), {
        photo_ids: selectedExistingPhotos.value,
        event_id: props.event.id,
    }, {
        preserveScroll: true,
        onSuccess: () => {
            showUploadModal.value = false;
            selectedExistingPhotos.value = [];
            success('Fotos asignadas al evento');
        },
    });
};

// MODIFICAR: Reset al cerrar modal
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


        //  Verificar que faceapi existe
        if (!faceapi) {
            throw new Error('face-api.js no está disponible');
        }

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
        console.error(' Error:', err);
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

    // Limpieza de estados anteriores
    previewUrls.value = [];
    faceDetectionResults.value = [];
    bibDetectionResults.value = [];

    // Generación de previsualizaciones
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

    // Lógica secuencial de detección
    if (modelsLoaded.value) {
        try {
            // PASO 1: Detectar rostros y obtener coordenadas (x, y, w, h)
            processingFaces.value = true;
            await detectFacesInImages();
            processingFaces.value = false;

            // PASO 2: Usar las coordenadas de los rostros para buscar dorsales
            // Pasamos los resultados obtenidos en el paso 1
            processingBibs.value = true;
            await detectBibNumbers(faceDetectionResults.value);
            processingBibs.value = false;

        } catch (error) {
            console.error("Error en el proceso de detección:", error);
            processingFaces.value = false;
            processingBibs.value = false;
        }
    }
};


// ============================================
//  UTILITIES
// ============================================

// Función para ver qué está pasando (SOLO PARA DEBUG)
const appendDebugImage = (dataUrl, label) => {
    const debugContainer = document.getElementById('ocr-debug-container') || document.createElement('div');
    debugContainer.id = 'ocr-debug-container';
    debugContainer.style.cssText = 'position: fixed; bottom: 0; left: 0; background: rgba(0,0,0,0.8); color: white; z-index: 9999; max-height: 200px; overflow: auto; width: 100%; display: flex; gap: 10px; padding: 10px;';

    if (!document.body.contains(debugContainer)) {
        document.body.appendChild(debugContainer);
    }

    const wrapper = document.createElement('div');
    wrapper.innerHTML = `<p style="font-size: 10px; margin:0;">${label}</p>`;
    const img = document.createElement('img');
    img.src = dataUrl;
    img.style.height = '100px';
    img.style.border = '2px solid red';

    wrapper.appendChild(img);
    debugContainer.appendChild(wrapper);
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
                // AJUSTE: Recorte más estrecho y centrado para evitar árboles/brazos
                roiW = faceBox.width * 2.2; // Antes 3.2 (demasiado ancho)
                roiH = faceBox.height * 2.5; // Antes 3.5 (demasiado alto)

                roiX = faceBox.x - (roiW - faceBox.width) / 2;
                roiY = faceBox.y + (faceBox.height * 1.1); // Bajamos un poco más del mentón
            } else {
                roiW = img.width * 0.5;
                roiH = img.height * 0.4;
                roiX = (img.width - roiW) / 2;
                roiY = img.height * 0.35;
            }

            // Validar límites
            roiX = Math.max(0, roiX);
            roiY = Math.max(0, roiY);
            roiW = Math.min(roiW, img.width - roiX);
            roiH = Math.min(roiH, img.height - roiY);

            // UPSCALING: Hacemos el canvas 2 veces más grande que el recorte
            // Tesseract lee mejor si las letras son grandes.
            const scaleFactor = 2;
            canvas.width = roiW * scaleFactor;
            canvas.height = roiH * scaleFactor;

            // Dibujamos escalado
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

            // 1. PADDING (Margen blanco para que Tesseract respire)
            const padding = 20;
            canvas.width = img.width + (padding * 2);
            canvas.height = img.height + (padding * 2);

            ctx.fillStyle = "#FFFFFF"; // Fondo base blanco
            ctx.fillRect(0, 0, canvas.width, canvas.height);

            // Dibujamos la imagen centrada
            ctx.drawImage(img, padding, padding);

            const imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);
            const data = imageData.data;

            // 2. MAGIA DEL CANAL VERDE
            // Recorremos pixel por pixel
            for (let i = 0; i < data.length; i += 4) {
                // data[i] es Rojo
                const g = data[i + 1]; // data[i+1] es VERDE <--- ESTE ES LA CLAVE
                // data[i+2] es Azul

                // En lugar de calcular promedios, miramos SOLO el verde.
                // El papel blanco tiene verde alto (200-255).
                // El dorsal rosa tiene verde bajo (0-100).

                // Si el verde es bajo (es rosa/rojo), lo forzamos a NEGRO ABSOLUTO.
                // Si el verde es alto (es blanco), lo forzamos a BLANCO ABSOLUTO.

                // UMBRAL: Ajustable. 130 suele separar bien tinta de color vs papel blanco.
                const threshold = 130;

                // Lógica inversa: Si tiene mucho verde (papel), es blanco (255). Si no, negro (0).
                const val = g > threshold ? 255 : 0;

                data[i] = val;     // R
                data[i + 1] = val; // G
                data[i + 2] = val; // B
                // data[i+3] es Alpha, no lo tocamos
            }

            ctx.putImageData(imageData, 0, 0);
            resolve(canvas.toDataURL());
        };
        img.src = imageUrl;
    });
};


const pickBestBib = (candidates) => {
    if (!candidates || candidates.length === 0) return null;

    // Normalizar y filtrar
    const validCandidates = candidates
        .map(n => String(parseInt(n, 10))) // Elimina ceros a la izq y caracteres raros
        .filter(n => {
            const val = parseInt(n, 10);
            const len = n.length;
            // Regla: Dorsales suelen tener entre 1 y 5 dígitos
            return !isNaN(val) && len >= 1 && len <= 5;
        });

    if (validCandidates.length === 0) return null;

    // Ordenar: Preferimos números más largos (menos probabilidad de ruido)
    // Ejemplo: Preferir "120" sobre "20"
    validCandidates.sort((a, b) => b.length - a.length);

    return validCandidates[0];
};





const detectBibNumbers = async (facesData) => {
    bibDetectionResults.value = [];
    processingBibs.value = true;

    const debugContainer = document.getElementById('ocr-debug-container');
    if (debugContainer) debugContainer.innerHTML = '';

 

    const worker = await Tesseract.createWorker('eng');

    await worker.setParameters({
        tessedit_char_whitelist: '0123456789',
        tessedit_pageseg_mode: Tesseract.PSM.SPARSE_TEXT,
    });

    for (let i = 0; i < previewUrls.value.length; i++) {
        try {
            // Obtenemos los datos de rostros de esta foto
            const faceInfo = facesData.find(f => f.index === i);
            // Obtenemos TODAS las cajas (si no hay, array vacío)
            const boxes = (faceInfo && faceInfo.boxes) ? faceInfo.boxes : [];

            // Set para evitar números duplicados en la misma foto
            let uniqueNumbers = new Set();
            let accumulatedText = "";

            // SI HAY ROSTROS: Procesamos cada uno
            if (boxes.length > 0) {
              

                for (const faceBox of boxes) {
                    // 1. Recorte por cada persona
                    const roiDataUrl = await cropTorsoFromFace(previewUrls.value[i], faceBox);
                    // 2. Preproceso
                    const cleanedDataUrl = await preprocessForOCR(roiDataUrl);
                    // 3. Reconocimiento
                    const result = await worker.recognize(cleanedDataUrl);
                    const text = result.data.text;
                    accumulatedText += text + " ";

                    // 4. Extracción
                    const found = text.match(/\d+/g);
                    if (found) {
                        // Filtramos números muy cortos (ruido)
                        found.forEach(num => {
                            if (num.length >= 2) uniqueNumbers.add(num);
                        });
                    }
                }
            } else {
                // SI NO HAY ROSTROS: Intentamos escaneo general (fallback)
             
                const roiDataUrl = await cropTorsoFromFace(previewUrls.value[i], null); // null usa recorte central
                const cleanedDataUrl = await preprocessForOCR(roiDataUrl);
                const result = await worker.recognize(cleanedDataUrl);
                const text = result.data.text;
                accumulatedText = text;
                const found = text.match(/\d+/g);
                if (found) {
                    found.forEach(num => {
                        if (num.length >= 2) uniqueNumbers.add(num);
                    });
                }
            }

            // Convertimos el Set a Array
            const finalNumbers = Array.from(uniqueNumbers);
           

            bibDetectionResults.value.push({
                index: i,
                numbers: finalNumbers, // Ahora es un array con todos los encontrados
                raw_text: accumulatedText,
            });

        } catch (error) {
            console.error(`Error en foto ${i + 1}:`, error);
            bibDetectionResults.value.push({
                index: i,
                numbers: [],
                raw_text: '',
            });
        }
    }

    await worker.terminate();
    processingBibs.value = false;
};


// Función para actualizar los números cuando el usuario escribe manualmente
const updateBibsManually = (index, event) => {
    const text = event.target.value;
    // Separamos por comas o espacios y filtramos vacíos
    // Ejemplo: "120, 55" -> ["120", "55"]
    const newNumbers = text.split(/[\s,]+/).filter(n => n.trim().length > 0);

    // Aseguramos que existe el objeto antes de asignar
    if (!bibDetectionResults.value[index]) {
        bibDetectionResults.value[index] = { index: index, numbers: [], raw_text: '' };
    }

    bibDetectionResults.value[index].numbers = newNumbers;
};

//  VERSIÓN CORREGIDA
const detectFacesInImages = async () => {
    processingFaces.value = true;
    faceDetectionResults.value = [];

  

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

           

            const detections = await faceapi
                .detectAllFaces(img, new faceapi.SsdMobilenetv1Options({
                    minConfidence: 0.5
                }))
                .withFaceLandmarks()
                .withFaceDescriptors();

            const descriptors = detections.map(d => Array.from(d.descriptor));

            // CAMBIO: Guardamos TODAS las cajas, no solo la primera
            const allBoxes = detections.map(d => d.detection.box);

            faceDetectionResults.value.push({
                index: i,
                count: detections.length,
                descriptors: descriptors,
                boxes: allBoxes // <--- Aquí guardamos todas las ubicaciones
            });

          

        } catch (error) {
            console.error(` Error en foto ${i + 1}:`, error);
            faceDetectionResults.value.push({
                index: i,
                count: 0,
                descriptors: [],
                boxes: []
            });
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
            showUploadModal.value = false;
            selectedFiles.value = [];
            previewUrls.value = [];
            faceDetectionResults.value = [];
            bibDetectionResults.value = [];
            uploadForm.reset('photos', 'face_data');
            success('Fotos subidas correctamente con detección de rostros y dorsales');
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


const paginationPages = computed(() => {
    const current = props.photos.current_page;
    const last = props.photos.last_page;
    const delta = 2;
    const pages = [];

    pages.push(1);

    const rangeStart = Math.max(2, current - delta);
    const rangeEnd = Math.min(last - 1, current + delta);

    if (rangeStart > 2) {
        pages.push('...');
    }

    for (let i = rangeStart; i <= rangeEnd; i++) {
        pages.push(i);
    }

    if (rangeEnd < last - 1) {
        pages.push('...');
    }

    if (last > 1) {
        pages.push(last);
    }

    return pages;
});

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
                                class="flex items-center justify-center gap-2 pt-6 border-t border-gray-200">

                                <!-- Botón Anterior -->
                                <Link v-if="photos.prev_page_url" :href="photos.prev_page_url"
                                    class="h-8 px-3 flex items-center justify-center text-xs font-bold uppercase tracking-wider rounded-sm transition-colors bg-white text-slate-600 hover:bg-slate-900 hover:text-white border border-gray-200">
                                    ←
                                </Link>
                                <span v-else
                                    class="h-8 px-3 flex items-center justify-center text-xs text-gray-300 cursor-not-allowed">
                                    ←
                                </span>

                                <!-- Páginas con Ellipsis -->
                                <div class="flex items-center gap-2">
                                    <template v-for="(page, index) in paginationPages" :key="index">
                                        <!-- Página actual -->
                                        <span v-if="page === photos.current_page"
                                            class="h-8 w-8 flex items-center justify-center text-xs font-bold rounded-sm bg-slate-900 text-white">
                                            {{ page }}
                                        </span>

                                        <!-- Ellipsis -->
                                        <span v-else-if="page === '...'"
                                            class="h-8 w-8 flex items-center justify-center text-slate-400 font-bold">
                                            ···
                                        </span>

                                        <!-- Página clickeable -->
                                        <Link v-else :href="photos.path + '?page=' + page"
                                            class="h-8 w-8 flex items-center justify-center text-xs font-medium rounded-sm transition-colors bg-white text-slate-600 hover:bg-gray-100 border border-gray-200 hover:border-slate-300">
                                            {{ page }}
                                        </Link>
                                    </template>
                                </div>

                                <!-- Botón Siguiente -->
                                <Link v-if="photos.next_page_url" :href="photos.next_page_url"
                                    class="h-8 px-3 flex items-center justify-center text-xs font-bold uppercase tracking-wider rounded-sm transition-colors bg-white text-slate-600 hover:bg-slate-900 hover:text-white border border-gray-200">
                                    →
                                </Link>
                                <span v-else
                                    class="h-8 px-3 flex items-center justify-center text-xs text-gray-300 cursor-not-allowed">
                                    →
                                </span>
                            </div>



                        </div>
                    </div>
                </div>

            </div>
        </div>

        <div v-if="showUploadModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-slate-900/80 backdrop-blur-sm" @click="closeModal"></div>
            <div
                class="relative bg-white rounded-sm shadow-2xl max-w-4xl w-full max-h-[85vh] overflow-hidden flex flex-col">

                <!-- Header con Tabs -->
                <div class="border-b border-gray-200 bg-white">
                    <div class="p-6 pb-0">
                        <div class="flex justify-between items-start mb-4">
                            <h3 class="text-lg font-serif font-bold text-slate-900">Agregar Fotos</h3>
                            <button @click="closeModal" class="text-slate-400 hover:text-slate-900">
                                <XMarkIcon class="w-6 h-6" />
                            </button>
                        </div>
                    </div>

                    <!--  TABS -->
                    <div class="flex border-b border-gray-200 px-6">
                        <button @click="uploadMode = 'upload'" :class="[
                            'px-4 py-3 text-sm font-bold uppercase tracking-wider transition-colors border-b-2',
                            uploadMode === 'upload'
                                ? 'border-slate-900 text-slate-900'
                                : 'border-transparent text-slate-400 hover:text-slate-600'
                        ]">
                            <CloudArrowUpIcon class="w-4 h-4 inline mr-2" />
                            Subir desde PC
                        </button>
                        <button @click="uploadMode = 'existing'" :class="[
                            'px-4 py-3 text-sm font-bold uppercase tracking-wider transition-colors border-b-2',
                            uploadMode === 'existing'
                                ? 'border-slate-900 text-slate-900'
                                : 'border-transparent text-slate-400 hover:text-slate-600'
                        ]">
                            <PhotoIcon class="w-4 h-4 inline mr-2" />
                            Mis Fotos ({{ unassignedPhotos.length }})
                        </button>
                    </div>

                    <!-- Indicador de IA (solo en modo upload) -->
                    <div v-if="uploadMode === 'upload'" class="px-6 py-2 bg-gray-50">
                        <p v-if="modelsLoaded" class="text-xs text-green-600 flex items-center gap-1">
                            <CheckIcon class="w-3 h-3" />
                            Reconocimiento facial activo
                        </p>
                        <p v-else class="text-xs text-gray-400">
                            Reconocimiento facial no disponible
                        </p>
                    </div>
                </div>

                <!-- Body -->
                <div class="p-6 overflow-y-auto flex-1">

                    <!--  TAB 1: SUBIR DESDE PC -->
                    <div v-if="uploadMode === 'upload'">
                        <!--  Banner de procesamiento DUAL -->
                        <div v-if="processingFaces || processingBibs"
                            class="bg-blue-50 border border-blue-200 rounded-sm p-4 mb-4 space-y-3">

                            <!-- Rostros -->
                            <div v-if="processingFaces" class="flex items-center gap-3">
                                <div
                                    class="animate-spin rounded-full h-5 w-5 border-2 border-blue-600 border-t-transparent">
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-blue-900"> Detectando rostros...</p>
                                    <p class="text-xs text-blue-700 mt-0.5">
                                        {{ faceDetectionResults.length }}/{{ previewUrls.length }} procesadas
                                    </p>
                                </div>
                            </div>

                            <!--  Dorsales -->
                            <div v-if="processingBibs" class="flex items-center gap-3">
                                <div
                                    class="animate-spin rounded-full h-5 w-5 border-2 border-purple-600 border-t-transparent">
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-purple-900"> Detectando dorsales...</p>
                                    <p class="text-xs text-purple-700 mt-0.5">
                                        {{ bibDetectionResults.length }}/{{ previewUrls.length }} procesadas
                                    </p>
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
                                <span class="text-xs font-bold uppercase tracking-widest text-slate-900 block mb-2">
                                    Seleccionar Archivos
                                </span>
                                <span class="text-xs text-slate-500 font-light">JPG, PNG • Máx 10MB</span>
                            </label>
                        </div>

                        <!-- Previews -->
                        <div v-else>
                            <div class="grid grid-cols-4 sm:grid-cols-5 gap-3 mb-6">

                                <div v-for="(url, index) in previewUrls" :key="index"
                                    class="relative aspect-square bg-gray-100 rounded-sm overflow-hidden border border-gray-200 group">

                                    <img :src="url" class="w-full h-full object-cover" />

                                    <!--  Badge de rostros (arriba derecha) -->
                                    <div v-if="faceDetectionResults[index]"
                                        class="absolute top-2 right-2 px-2 py-1  text-[10px] font-bold shadow-lg"
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
                                                <div v-for="number in bibDetectionResults[index].numbers" :key="number"
                                                    class="bg-white/10 hover:bg-white/20 text-white text-[10px] font-mono px-1.5 py-0.5 rounded-sm flex items-center gap-1 border border-white/10 backdrop-blur-sm">
                                                    <span>{{ number }}</span>
                                                    <button @click.stop="removeBibTag(index, number)"
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

                                        <div
                                            class="hidden group-hover/edit:block w-full text-[9px] text-white/30 text-center mt-1 border-t border-white/10 pt-1">
                                            Enter para agregar • Backspace para borrar
                                        </div>
                                    </div>


                                    <!-- Loading overlay -->
                                    <div v-if="(processingFaces && !faceDetectionResults[index]) || (processingBibs && !bibDetectionResults[index])"
                                        class="absolute inset-0 bg-black/40 flex items-center justify-center">
                                        <div
                                            class="animate-spin rounded-full h-6 w-6 border-2 border-white border-t-transparent">
                                        </div>
                                    </div>
                                </div>



                            </div>

                            <!--  Estadísticas finales -->
                            <div class="border-t border-gray-200 pt-4 space-y-2">
                                <p class="text-xs text-slate-500 text-right">
                                    {{ selectedFiles.length }} archivo(s) seleccionado(s)
                                </p>

                                <!-- Rostros -->
                                <div v-if="faceDetectionResults.length > 0 && !processingFaces"
                                    class="flex items-center justify-end gap-2">
                                    <CheckIcon class="w-3 h-3 text-green-600" />
                                    <p class="text-xs font-medium"
                                        :class="photosWithFaces > 0 ? 'text-green-600' : 'text-gray-500'">
                                        {{ totalFacesDetected }} rostro(s) en {{ photosWithFaces }} foto(s)
                                    </p>
                                </div>

                                <!--  Dorsales -->
                                <div v-if="bibDetectionResults.length > 0 && !processingBibs"
                                    class="flex items-center justify-end gap-2">
                                    <CheckIcon class="w-3 h-3 text-purple-600" />
                                    <p class="text-xs font-medium"
                                        :class="photosWithBibs > 0 ? 'text-purple-600' : 'text-gray-500'">
                                        {{ totalBibsDetected }} dorsal(es) en {{ photosWithBibs }} foto(s)
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>


                    <!--  TAB 2: FOTOS EXISTENTES -->
                    <div v-else-if="uploadMode === 'existing'">
                        <div v-if="unassignedPhotos.length === 0"
                            class="text-center py-12 border border-dashed border-gray-300 rounded-sm bg-gray-50">
                            <PhotoIcon class="w-12 h-12 mx-auto text-gray-300 mb-3" />
                            <p class="text-sm text-slate-600 font-medium mb-1">Sin fotos disponibles</p>
                            <p class="text-xs text-slate-400">Todas tus fotos ya están asignadas a eventos.</p>
                        </div>

                        <div v-else>
                            <p class="text-xs text-slate-500 mb-4">
                                Seleccioná las fotos que querés agregar a este evento ({{ selectedExistingPhotos.length
                                }}
                                seleccionadas)
                            </p>
                            <div class="grid grid-cols-4 sm:grid-cols-5 gap-3">
                                <div v-for="photo in unassignedPhotos" :key="photo.id"
                                    @click="togglePhotoSelection(photo.id)"
                                    class="relative aspect-square bg-gray-100 rounded-sm overflow-hidden border-2 cursor-pointer transition-all"
                                    :class="selectedExistingPhotos.includes(photo.id)
                                        ? 'border-slate-900 ring-2 ring-slate-900 ring-offset-2'
                                        : 'border-gray-200 hover:border-slate-400'">

                                    <img :src="photo.thumbnail_url" :alt="photo.unique_id"
                                        class="w-full h-full object-cover" />

                                    <!-- Checkmark -->
                                    <div v-if="selectedExistingPhotos.includes(photo.id)"
                                        class="absolute inset-0 bg-slate-900/60 flex items-center justify-center">
                                        <div class="w-8 h-8 rounded-full bg-white flex items-center justify-center">
                                            <CheckIcon class="w-5 h-5 text-slate-900" />
                                        </div>
                                    </div>

                                    <!-- ID -->
                                    <div
                                        class="absolute bottom-1 left-1 bg-black/60 text-white text-[9px] px-1.5 py-0.5 rounded-sm font-mono">
                                        #{{ photo.unique_id }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- Footer -->
                <div class="p-6 border-t border-gray-100 bg-gray-50 flex justify-between items-center">
                    <div class="text-xs text-slate-500">
                        <span v-if="uploadMode === 'upload'">
                            {{ selectedFiles.length }} archivo(s) para subir
                        </span>
                        <span v-else>
                            {{ selectedExistingPhotos.length }} foto(s) seleccionada(s)
                        </span>
                    </div>
                    <div class="flex gap-3">
                        <button @click="closeModal"
                            class="px-6 py-3 border border-gray-300 text-slate-600 text-xs font-bold uppercase tracking-widest hover:bg-white transition rounded-sm">
                            Cancelar
                        </button>

                        <!-- Botón para SUBIR -->
                        <button v-if="uploadMode === 'upload'" @click="uploadPhotos"
                            :disabled="uploadForm.processing || selectedFiles.length === 0 || processingFaces"
                            class="px-6 py-3 bg-slate-900 text-white text-xs font-bold uppercase tracking-widest hover:bg-slate-800 transition rounded-sm disabled:opacity-50 disabled:cursor-not-allowed">
                            <span v-if="processingFaces">Procesando...</span>
                            <span v-else-if="uploadForm.processing">Subiendo...</span>
                            <span v-else>Subir Fotos</span>
                        </button>

                        <!-- Botón para ASIGNAR -->
                        <button v-else @click="assignExistingPhotos" :disabled="selectedExistingPhotos.length === 0"
                            class="px-6 py-3 bg-slate-900 text-white text-xs font-bold uppercase tracking-widest hover:bg-slate-800 transition rounded-sm disabled:opacity-50 disabled:cursor-not-allowed">
                            Asignar al Evento
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>