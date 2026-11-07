<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { ref, watch, computed } from 'vue';
import {
    CloudArrowUpIcon,
    XMarkIcon,
    ArrowLeftIcon,
    InformationCircleIcon
} from '@heroicons/vue/24/outline';
import { useToast } from '@/Composables/useToast';
import axios from 'axios';


const nsfwWorker = new Worker('/nsfw-worker-v7.js');
let isNsfwReady = false;
const isScanningNSFW = ref(false);

nsfwWorker.onmessage = (e) => {
    if (e.data.status === 'READY') {
        isNsfwReady = true;
    }
};

const props = defineProps({
    events: Array,
    eventRoles: { type: Object, default: () => ({}) },
    errors: Object,
});

const form = useForm({
    price: 5.00,
    event_id: null,
    location_role: '',
    is_active: true,
});

const { success, error } = useToast();

const isTypingCustomRole = ref(false);

const currentEventRoles = computed(() => {
    if (!form.event_id || !props.eventRoles) return [];
    const roles = props.eventRoles[form.event_id] || props.eventRoles[String(form.event_id)];
    return Array.isArray(roles) ? roles : [];
});

const selectedFiles = ref([]);
const dragOver = ref(false);
const fileInput = ref(null);

const isProcessing = ref(false);
const processingStep = ref('');
const processingProgress = ref(0);


const isImageSafe = (file) => {
    if (!isNsfwReady) return Promise.resolve(true);
    return new Promise((resolve) => {
        const fileId = Math.random().toString(36).substring(7);
        const handleMessage = (e) => {
            if (e.data.fileId === fileId) {
                nsfwWorker.removeEventListener('message', handleMessage);
                resolve(e.data.isSafe);
            }
        };
        nsfwWorker.addEventListener('message', handleMessage);
        nsfwWorker.postMessage({ fileId, file });
    });
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
                    
                
                    const previewUrl = URL.createObjectURL(blob);

                    resolve({
                        file: compressedFile,
                        name: compressedFile.name,
                        preview: previewUrl 
                    });
                }, 'image/jpeg', 0.8);
            };
            img.src = e.target.result;
        };
        reader.readAsDataURL(file);
    });
};
const handleFileSelect = (event) => addFiles(Array.from(event.target.files));
const handleDrop = (event) => { dragOver.value = false; addFiles(Array.from(event.dataTransfer.files)); };

const addFiles = async (files) => {
    const validFiles = files.filter(file => {
        const validTypes = ['image/jpeg', 'image/jpg', 'image/png'];
        if (!validTypes.includes(file.type)) { error(`${file.name} no es un formato válido.`); return false; }
        if (file.size > 50 * 1024 * 1024) { error(`${file.name} excede el límite de 50MB.`); return false; }
        if (selectedFiles.value.some(existing => existing.name === file.name)) { error(`"${file.name}" ya está en la cola.`); return false; }
        return true;
    });

    const MAX_FILES = 150;
    const remainingSlots = MAX_FILES - selectedFiles.value.length;
    const filesToProcess = validFiles.slice(0, remainingSlots);

    if (validFiles.length > remainingSlots) error(`Límite de ${MAX_FILES} fotos. Se procesarán ${remainingSlots}.`);
    if (filesToProcess.length === 0) return;

    isProcessing.value = true;
    

    /*
    isScanningNSFW.value = true;
    processingStep.value = 'Escaneando contenido seguro...';
    processingProgress.value = 0;

    const safeFiles = [];
    let blockedCount = 0;

    for (let i = 0; i < filesToProcess.length; i++) {
        const file = filesToProcess[i];
        const isSafe = await isImageSafe(file);
        if (isSafe) safeFiles.push(file); else blockedCount++;
        processingProgress.value = Math.round(((i + 1) / filesToProcess.length) * 100);
        await new Promise(resolve => setTimeout(resolve, 10));
    }
    isScanningNSFW.value = false;
    if (blockedCount > 0) error(`Se bloquearon ${blockedCount} fotografía(s) inapropiadas.`);
    if (safeFiles.length === 0) { isProcessing.value = false; return; }
    */


    const safeFiles = filesToProcess;



    processingStep.value = 'Comprimiendo imágenes...';
    processingProgress.value = 0;

    const BATCH_SIZE = 5;
    let newFileObjects = [];

    for (let i = 0; i < safeFiles.length; i += BATCH_SIZE) {
        const batch = safeFiles.slice(i, i + BATCH_SIZE);
        const batchResults = await Promise.all(batch.map(file => compressImage(file)));
        newFileObjects = newFileObjects.concat(batchResults);
        processingProgress.value = Math.round(((i + batch.length) / safeFiles.length) * 100);
        await new Promise(resolve => setTimeout(resolve, 20));
    }

    newFileObjects.forEach((obj, index) => obj.name = safeFiles[index].name);
    selectedFiles.value.push(...newFileObjects);

    processingStep.value = 'Preparación local completada';
    processingProgress.value = 100;
    setTimeout(() => { isProcessing.value = false; }, 1000);
};
watch(() => form.event_id, () => { form.location_role = ''; isTypingCustomRole.value = false; });
watch(() => form.location_role, (newValue) => { if (newValue === 'custom') { isTypingCustomRole.value = true; form.location_role = ''; } });

const removeFile = (index) => selectedFiles.value.splice(index, 1);
const clearFiles = () => { 

    selectedFiles.value.forEach(item => {
        if (item.preview) URL.revokeObjectURL(item.preview);
    });
    selectedFiles.value = []; 
    if (fileInput.value) fileInput.value.value = ''; 
};


const uploading = ref(false);
const isUploadingChunk = ref(false);
const uploadProgress = ref({ current: 0, total: 0, percentage: 0 });
const uploadStats = ref({ success: 0, failed: 0, total: 0 });

const submitPhotos = async () => {
    if (selectedFiles.value.length === 0) return error('Seleccione al menos una foto.');
    if (!form.price || form.price <= 0) return error('Establezca un precio válido.');

    uploading.value = true;
    uploadStats.value = { success: 0, failed: 0, total: selectedFiles.value.length };
    uploadProgress.value = { current: 0, total: selectedFiles.value.length, percentage: 0 };

    for (let i = 0; i < selectedFiles.value.length; i++) {
        const item = selectedFiles.value[i];
        isUploadingChunk.value = true;

        try {
            const formData = new FormData();
            formData.append('photos[0]', item.file);
            formData.append('price', form.price);
            formData.append('is_active', form.is_active ? 1 : 0);
            if (form.event_id) formData.append('event_id', form.event_id);
            if (form.location_role) formData.append('location_role', form.location_role);

            await axios.post(route('photographer.photos.store'), formData, {
                headers: { 'Content-Type': 'multipart/form-data' }
            });

            uploadStats.value.success++;
        } catch (err) {
            console.error(`Error subiendo foto ${i}:`, err);
            uploadStats.value.failed++;
        }

        uploadProgress.value.current = i + 1;
        uploadProgress.value.percentage = Math.round(((i + 1) / selectedFiles.value.length) * 100);
    }

    uploading.value = false;
    isUploadingChunk.value = false;

    if (uploadStats.value.failed === 0) {
        success('Todas las fotos cargadas exitosamente.');
        clearFiles();
        router.visit(route('photographer.photos.index'));
    } else {
        error(`Se subieron ${uploadStats.value.success} fotos. Hubo ${uploadStats.value.failed} errores.`);
        selectedFiles.value = selectedFiles.value.filter((_, idx) => idx >= uploadStats.value.success);
    }
};
</script>

<template>
    <Head title="Carga de material" />

    <AuthenticatedLayout>
        <div class="py-12 bg-[#F8F9FA] min-h-screen text-slate-800 antialiased pt-28">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                
                <div class="mb-10 flex flex-col md:flex-row md:items-end justify-between gap-6 border-b border-gray-200 pb-8">
                    <div>
                        <Link :href="route('photographer.photos.index')" class="inline-flex items-center gap-2 bg-white px-4 py-2 rounded-full text-xs font-bold uppercase tracking-wider text-gray-500 hover:text-black hover:shadow-sm border border-gray-200 transition-all mb-6">
                            <ArrowLeftIcon class="w-4 h-4" /> Volver al Archivo
                        </Link>
                        <h1 class="font-flux text-5xl md:text-7xl text-black leading-none tracking-wide">
                            Subida de <span class="text-[#E30613]">fotos</span>
                        </h1>
                        <p class="text-sm font-bold text-gray-500 mt-2 flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-[#E30613] animate-pulse"></span> Sis. de subida
                        </p>
                    </div>
                </div>

                <form @submit.prevent="submitPhotos" class="grid grid-cols-1 lg:grid-cols-3 gap-8 xl:gap-12">
                    
                    <div class="lg:col-span-1 space-y-6">
                        <div class="bg-white border border-gray-100 rounded shadow-sm overflow-hidden">
                            <div class="px-6 md:px-8 py-5 border-b border-gray-100 bg-gray-50/50">
                                <h2 class="text-xs font-bold uppercase tracking-widest text-gray-500 flex items-center gap-2">
                                    <span class="w-4 h-px bg-gray-300"></span> Parámetros de Venta
                                </h2>
                            </div>

                            <div class="p-6 md:p-8 space-y-8">
                                <div>
                                    <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 mb-2 ml-1">Precio unitario (ARS)</label>
                                    <div class="relative">
                                        <span class="absolute left-5 top-1/2 -translate-y-1/2 text-xl font-bold text-gray-400">$</span>
                                        <input v-model="form.price" type="number" step="0.01" min="0.01" required class="w-full bg-gray-50 border border-transparent focus:bg-white focus:border-gray-300 focus:ring-4 focus:ring-gray-100 text-slate-800 font-bold text-2xl py-3 pl-10 pr-4 rounded-xl transition-all outline-none" placeholder="0.00">
                                    </div>
                                    <p v-if="errors.price" class="text-[#E30613] text-xs font-bold mt-2">{{ errors.price }}</p>
                                </div>

                                <div>
                                    <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 mb-2 ml-1">Asignar a evento</label>
                                    <select v-model="form.event_id" class="w-full bg-gray-50 border border-transparent focus:bg-white focus:border-gray-300 focus:ring-4 focus:ring-gray-100 text-slate-700 font-bold text-sm py-3.5 px-4 rounded-xl transition-all outline-none appearance-none cursor-pointer">
                                        <option :value="null">-- Guardar en mis archivos --</option>
                                        <option v-for="event in events" :key="event.id" :value="event.id">{{ event.name }}</option>
                                    </select>
                                </div>

                                <div v-if="form.event_id" class="animate-fade-in transition-all duration-300">
                                    <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 mb-2 ml-1">Rol / Lugar asignado (opcional)</label>
                                    <div class="relative">
                                        <select v-if="!isTypingCustomRole && currentEventRoles.length > 0" v-model="form.location_role" class="w-full bg-gray-50 border border-transparent focus:bg-white focus:border-gray-300 focus:ring-4 focus:ring-gray-100 text-slate-700 font-bold text-sm py-3.5 px-4 rounded-xl transition-all outline-none appearance-none cursor-pointer">
                                            <option value="">-- Seleccionar lugar / rol --</option>
                                            <option v-for="role in currentEventRoles" :key="role" :value="role">{{ role }}</option>
                                            <option value="custom">+ Escribir uno nuevo...</option>
                                        </select>

                                        <div v-if="isTypingCustomRole || currentEventRoles.length === 0" class="flex gap-2">
                                            <input v-model="form.location_role" type="text" class="w-full bg-gray-50 border border-transparent focus:bg-white focus:border-gray-300 focus:ring-4 focus:ring-gray-100 text-slate-700 font-bold text-sm py-3.5 px-4 rounded-xl transition-all outline-none" placeholder="Ej: Línea de meta..." maxlength="100">
                                            <button v-if="currentEventRoles.length > 0" type="button" @click="isTypingCustomRole = false; form.location_role = ''" class="px-4 bg-gray-100 text-gray-500 rounded-xl hover:bg-gray-200 transition-colors">
                                                <XMarkIcon class="w-5 h-5" />
                                            </button>
                                        </div>
                                    </div>
                                    <p class="text-[10px] text-gray-400 mt-2 ml-1">Ayudá a las personas a encontrarte más fácil.</p>
                                </div>

                                <div class="space-y-6 pt-6 border-t border-gray-100">
                                    <label class="relative flex justify-between items-start cursor-pointer group">
                                        <div class="pr-4">
                                            <span class="block text-sm font-bold text-slate-700 group-hover:text-black transition-colors">Publicación inmediata</span>
                                            <span class="block text-xs text-gray-400 mt-1">Las fotos van a ser visibles tras la carga.</span>
                                        </div>
                                        <div class="relative inline-flex items-center mt-1">
                                            <input type="checkbox" v-model="form.is_active" class="sr-only peer">
                                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#E30613]"></div>
                                        </div>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="bg-gray-50 border border-gray-200 p-6 rounded">
                            <div class="flex items-start gap-4">
                                <div class="w-10 h-10 rounded-full bg-white shadow-sm flex items-center justify-center shrink-0">
                                    <InformationCircleIcon class="w-5 h-5 text-gray-500" stroke-width="2" />
                                </div>
                                <div>
                                    <h4 class="text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">IA en Segundo Plano</h4>
                                    <p class="text-xs text-gray-500 leading-relaxed">
                                        El reconocimiento facial y OCR se ejecutan en nuestros servidores después de subir las fotos.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="lg:col-span-2 flex flex-col gap-6">
                        <div @dragover.prevent="dragOver = true" @dragleave.prevent="dragOver = false" @drop.prevent="handleDrop" :class="[
                                'border-2 border-dashed rounded p-8 md:p-12 text-center transition-all duration-300 flex flex-col items-center justify-center min-h-[250px] relative overflow-hidden bg-white',
                                dragOver ? 'border-[#E30613] bg-red-50' : 'border-gray-300 hover:border-gray-400 hover:bg-gray-50'
                            ]">
                            <input type="file" ref="fileInput" @change="handleFileSelect" multiple accept="image/*" class="hidden" id="file-upload">

                            <div v-if="selectedFiles.length === 0" class="flex flex-col items-center">
                                <div class="w-20 h-20 bg-gray-50 rounded-full flex items-center justify-center mb-6 shadow-sm border border-gray-100">
                                    <CloudArrowUpIcon class="w-10 h-10 text-gray-400 stroke-1" />
                                </div>
                                <h3 class="font-flux text-4xl text-black mb-2">Carga</h3>
                                <p class="text-gray-500 mb-6">Arrastrá tus fotografías acá o explora tus archivos</p>
                                <button type="button" @click="$refs.fileInput.click()" class="px-8 py-3.5 bg-black text-white rounded-full font-bold text-xs uppercase tracking-wider hover:bg-gray-800 transition-colors shadow-md">
                                    Seleccionar archivos
                                </button>
                                <p class="mt-6 text-[10px] font-bold uppercase tracking-widest text-gray-400">JPG, PNG • Máx 150 archivos</p>
                            </div>

                            <div v-else class="w-full flex flex-col h-full">
                                <div class="flex justify-between items-center mb-6 border-b border-gray-100 pb-4">
                                    <span class="text-xl font-bold text-black flex items-center gap-2">
                                        {{ selectedFiles.length }} <span class="text-sm font-medium text-gray-500">Archivos listos</span>
                                    </span>
                                    <div class="flex gap-2">
                                        <button type="button" @click="$refs.fileInput.click()" class="px-4 py-2 bg-gray-100 text-slate-700 rounded-full font-bold text-xs hover:bg-gray-200 transition-colors">+ Agregar</button>
                                        <button type="button" @click="clearFiles" class="px-4 py-2 bg-red-50 text-[#E30613] rounded-full font-bold text-xs hover:bg-red-100 transition-colors">Limpiar todo</button>
                                    </div>
                                </div>

                                <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-5 lg:grid-cols-6 gap-2 overflow-y-auto pr-2 custom-scrollbar text-left content-start">
                                    <div v-for="(file, index) in selectedFiles" :key="index" class="relative group aspect-square bg-gray-100 rounded overflow-hidden border border-gray-200 hover:shadow-md transition-all">
                                        <img :src="file.preview" class="w-full h-full object-cover transition-transform duration-500">
                                        <div class="absolute inset-0 bg-black/20 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center pointer-events-none z-30">
                                            <button type="button" @click="removeFile(index)" class="w-8 h-8 bg-white rounded-full text-[#E30613] shadow-lg flex items-center justify-center hover:bg-[#E30613] hover:text-white transition-colors pointer-events-auto">
                                                <XMarkIcon class="w-4 h-4" stroke-width="2" />
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div v-if="isProcessing" class="p-6 bg-white border border-gray-100 rounded shadow-sm mb-4">
                            <div class="flex justify-between text-xs font-bold uppercase tracking-wider text-slate-700 mb-3">
                                <span>{{ processingStep }}</span>
                                <span class="text-black">{{ processingProgress }}%</span>
                            </div>
                            <div class="w-full bg-gray-100 h-3 rounded-full overflow-hidden">
                                <div class="bg-black h-full transition-all duration-300 ease-out" :style="{ width: processingProgress + '%' }"></div>
                            </div>
                        </div>

                        <div v-if="uploading" class="p-6 bg-white border border-gray-100 rounded shadow-sm">
                            <div class="flex justify-between text-xs font-bold uppercase tracking-wider text-slate-700 mb-3">
                                <span>Subiendo archivos a la nube... ({{ uploadProgress.current }} de {{ uploadProgress.total }})</span>
                                <span class="text-[#E30613]">{{ uploadProgress.percentage }}%</span>
                            </div>
                            <div class="w-full bg-gray-100 h-3 rounded-full overflow-hidden">
                                <div class="bg-gradient-to-r from-red-500 to-[#E30613] h-full transition-all duration-200 ease-out" :style="{ width: uploadProgress.percentage + '%' }"></div>
                            </div>
                        </div>

                        <div class="flex justify-end mt-2">
                            <button type="button" @click="submitPhotos" :disabled="uploading || selectedFiles.length === 0" :class="[
                                    'px-10 py-4 font-bold text-sm uppercase tracking-wider transition-all duration-200 rounded-full w-full md:w-auto flex justify-center items-center gap-2',
                                    uploading || selectedFiles.length === 0 ? 'bg-gray-100 text-gray-400 cursor-not-allowed' : 'bg-black text-white hover:bg-[#E30613] hover:shadow-lg hover:shadow-red-500/30 hover:-translate-y-1'
                                ]">
                                <span v-if="uploading" class="flex items-center gap-2">
                                    <div class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin"></div>
                                    Subiendo...
                                </span>
                                <span v-else>Subir fotos</span>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
@keyframes fadeIn { from { opacity: 0; transform: translateY(-10px); } to { opacity: 1; transform: translateY(0); } }
.animate-fade-in { animation: fadeIn 0.3s ease-out forwards; }
.custom-scrollbar::-webkit-scrollbar { width: 6px; }
.custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
.custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
</style>