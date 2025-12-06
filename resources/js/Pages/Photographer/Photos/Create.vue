<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { CloudArrowUpIcon, XMarkIcon, PhotoIcon, InformationCircleIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
    events: Array,
    errors: Object,
});

const form = useForm({
    photos: [],
    price: 5.00,
    event_id: null,
    is_active: true,
});

const selectedFiles = ref([]);
const dragOver = ref(false);
const uploading = ref(false);
const uploadProgress = ref({
    current: 0,
    total: 0,
    percentage: 0,
});

const fileInput = ref(null);

const handleFileSelect = (event) => {
    const files = Array.from(event.target.files);
    addFiles(files);
};

const handleDrop = (event) => {
    dragOver.value = false;
    const files = Array.from(event.dataTransfer.files);
    addFiles(files);
};

const addFiles = (files) => {
    const validFiles = files.filter(file => {
        const validTypes = ['image/jpeg', 'image/jpg', 'image/png'];
        const maxSize = 10 * 1024 * 1024; // 10MB

        if (!validTypes.includes(file.type)) {
            alert(`${file.name} no es un formato válido.`);
            return false;
        }
        if (file.size > maxSize) {
            alert(`${file.name} excede el límite de 10MB.`);
            return false;
        }
        return true;
    });

    const remainingSlots = 50 - selectedFiles.value.length;
    const filesToAdd = validFiles.slice(0, remainingSlots);

    if (validFiles.length > remainingSlots) {
        alert(`Límite de 50 fotos por carga. Se agregaron ${remainingSlots}.`);
    }

    filesToAdd.forEach(file => {
        const reader = new FileReader();
        reader.onload = (e) => {
            selectedFiles.value.push({
                file: file,
                name: file.name,
                preview: e.target.result,
            });
        };
        reader.readAsDataURL(file);
    });
};

const removeFile = (index) => {
    selectedFiles.value.splice(index, 1);
};

const clearFiles = () => {
    selectedFiles.value = [];
    if (fileInput.value) fileInput.value.value = '';
};

const submitPhotos = () => {
    if (selectedFiles.value.length === 0) return alert('Seleccione al menos una foto.');
    if (!form.price || form.price <= 0) return alert('Establezca un precio válido.');

    uploading.value = true;
    uploadProgress.value = { current: 0, total: selectedFiles.value.length, percentage: 0 };

    const formData = new FormData();
    selectedFiles.value.forEach((item, index) => {
        formData.append(`photos[${index}]`, item.file);
    });

    formData.append('price', form.price);
    formData.append('is_active', form.is_active ? 1 : 0);
    if (form.event_id) formData.append('event_id', form.event_id);

    router.post(route('photographer.photos.store'), formData, {
        forceFormData: true,
        preserveScroll: true,
        onProgress: (progress) => {
            uploadProgress.value.percentage = Math.round(progress.percentage);
        },
        onSuccess: () => {
            uploading.value = false;
            clearFiles();
        },
        onError: (errors) => {
            uploading.value = false;
            console.error(errors);
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
                
                <div class="mb-10 border-b border-gray-200 pb-6 flex flex-col md:flex-row md:items-end justify-between gap-4">
                    <div>
                        <Link :href="route('photographer.photos.index')" class="text-[10px] font-bold uppercase tracking-widest text-slate-400 hover:text-slate-900 mb-2 block transition-colors">
                            ← Volver al Archivo
                        </Link>
                        <h1 class="text-3xl font-serif font-bold text-slate-900">
                            Carga de Material
                        </h1>
                        <p class="text-sm text-slate-500 font-light mt-1">Gestión de activos digitales y asignación de precios.</p>
                    </div>
                </div>

                <form @submit.prevent="submitPhotos" class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    
                    <div class="lg:col-span-1 space-y-6">
                        <div class="bg-white border border-gray-200 rounded-sm p-6 shadow-sm">
                            <h2 class="text-xs font-bold uppercase tracking-widest text-slate-900 mb-6 border-b border-gray-100 pb-2">
                                Parámetros de Venta
                            </h2>
                            
                            <div class="space-y-6">
                                <div>
                                    <label class="block text-xs font-bold uppercase tracking-wide text-slate-500 mb-2">Precio Unitario (USD)</label>
                                    <input 
                                        v-model="form.price"
                                        type="number" step="0.01" min="0.01" required
                                        class="w-full border-gray-300 rounded-sm focus:border-slate-900 focus:ring-0 text-slate-900 font-mono"
                                        placeholder="0.00"
                                    >
                                    <p v-if="errors.price" class="text-red-600 text-xs mt-1">{{ errors.price }}</p>
                                </div>

                                <div>
                                    <label class="block text-xs font-bold uppercase tracking-wide text-slate-500 mb-2">Asignar a Evento</label>
                                    <select v-model="form.event_id" class="w-full border-gray-300 rounded-sm focus:border-slate-900 focus:ring-0 text-sm text-slate-700 bg-white">
                                        <option :value="null">-- Sin asignar --</option>
                                        <option v-for="event in events" :key="event.id" :value="event.id">
                                            {{ event.name }}
                                        </option>
                                    </select>
                                    <p class="text-[10px] text-slate-400 mt-1">Opcional. Agrupa las fotos para facilitar la búsqueda.</p>
                                </div>

                                <div class="flex items-start pt-2">
                                    <div class="flex items-center h-5">
                                        <input id="is_active" v-model="form.is_active" type="checkbox" class="focus:ring-0 h-4 w-4 text-slate-900 border-gray-300 rounded-sm">
                                    </div>
                                    <div class="ml-3 text-sm">
                                        <label for="is_active" class="font-medium text-slate-700">Publicación Inmediata</label>
                                        <p class="text-xs text-slate-500">Las fotos serán visibles en la galería al terminar la carga.</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="bg-gray-50 border border-gray-200 p-5 rounded-sm">
                            <div class="flex items-start gap-3">
                                <InformationCircleIcon class="w-5 h-5 text-slate-400 flex-shrink-0" />
                                <div>
                                    <h4 class="text-xs font-bold uppercase tracking-widest text-slate-700 mb-2">Protección de Activos</h4>
                                    <p class="text-xs text-slate-500 font-light leading-relaxed">
                                        El sistema aplicará automáticamente marcas de agua a las vistas previas. Los archivos originales se almacenan en servidores seguros y solo se liberan tras la confirmación del pago.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="lg:col-span-2">
                        
                        <div 
                            @dragover.prevent="dragOver = true"
                            @dragleave.prevent="dragOver = false"
                            @drop.prevent="handleDrop"
                            :class="[
                                'border border-dashed rounded-sm p-12 text-center transition-all duration-300 flex flex-col items-center justify-center min-h-[300px]',
                                dragOver 
                                    ? 'border-slate-900 bg-slate-50' 
                                    : 'border-gray-300 bg-white hover:border-slate-400'
                            ]"
                        >
                            <input type="file" ref="fileInput" @change="handleFileSelect" multiple accept="image/*" class="hidden">

                            <div v-if="selectedFiles.length === 0" class="flex flex-col items-center">
                                <CloudArrowUpIcon class="w-12 h-12 text-slate-300 mb-4 stroke-1" />
                                <h3 class="text-lg font-serif font-medium text-slate-900 mb-2">
                                    Arrastre sus archivos aquí
                                </h3>
                                <p class="text-sm text-slate-500 mb-6 font-light">
                                    o haga clic para explorar su dispositivo
                                </p>
                                <button type="button" @click="$refs.fileInput.click()" 
                                    class="px-6 py-2 border border-slate-300 text-slate-600 text-xs font-bold uppercase tracking-widest hover:border-slate-900 hover:text-slate-900 transition rounded-sm">
                                    Seleccionar
                                </button>
                                <div class="mt-8 text-[10px] uppercase tracking-widest text-slate-400 border-t border-gray-100 pt-4 w-full">
                                    JPG, PNG • Max 10MB • Límite 50
                                </div>
                            </div>

                            <div v-else class="w-full">
                                <div class="flex justify-between items-center mb-6 border-b border-gray-100 pb-4">
                                    <span class="text-sm font-bold text-slate-900">{{ selectedFiles.length }} archivos listos</span>
                                    <div class="flex gap-4">
                                        <button type="button" @click="$refs.fileInput.click()" class="text-xs font-bold uppercase tracking-widest text-slate-500 hover:text-slate-900">Agregar más</button>
                                        <button type="button" @click="clearFiles" class="text-xs font-bold uppercase tracking-widest text-red-600 hover:text-red-800">Limpiar todo</button>
                                    </div>
                                </div>

                                <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-5 gap-3 max-h-[500px] overflow-y-auto pr-2 custom-scrollbar">
                                    <div v-for="(file, index) in selectedFiles" :key="index" class="relative group aspect-square bg-gray-100 border border-gray-200">
                                        <img :src="file.preview" class="w-full h-full object-cover filter grayscale-[0.2] group-hover:grayscale-0 transition duration-300">
                                        
                                        <div class="absolute inset-0 bg-slate-900/0 group-hover:bg-slate-900/40 transition-colors flex items-center justify-center">
                                            <button type="button" @click="removeFile(index)" 
                                                class="opacity-0 group-hover:opacity-100 text-white hover:text-red-200 transition-opacity p-2">
                                                <XMarkIcon class="w-6 h-6" />
                                            </button>
                                        </div>
                                        
                                        <div class="absolute bottom-0 left-0 w-full bg-white/90 backdrop-blur px-2 py-1 text-[9px] truncate text-slate-600 font-mono border-t border-gray-100">
                                            {{ (file.file.size / 1024 / 1024).toFixed(1) }} MB
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div v-if="uploading" class="mt-6 p-6 bg-slate-50 border border-gray-200 rounded-sm">
                            <div class="flex justify-between text-xs font-bold uppercase tracking-widest text-slate-500 mb-2">
                                <span>Procesando</span>
                                <span>{{ uploadProgress.percentage }}%</span>
                            </div>
                            <div class="w-full bg-gray-200 h-1 rounded-full overflow-hidden">
                                <div class="bg-slate-900 h-1 transition-all duration-300 ease-out" :style="{ width: uploadProgress.percentage + '%' }"></div>
                            </div>
                            <p class="text-xs text-slate-400 mt-2 text-center font-light">Por favor no cierre esta ventana.</p>
                        </div>

                        <div class="mt-6 flex justify-end">
                            <button 
                                type="button" 
                                @click="submitPhotos"
                                :disabled="uploading || selectedFiles.length === 0"
                                :class="[
                                    'px-8 py-4 text-xs font-bold uppercase tracking-widest transition-all duration-300 rounded-sm w-full md:w-auto',
                                    uploading || selectedFiles.length === 0
                                        ? 'bg-gray-200 text-gray-400 cursor-not-allowed'
                                        : 'bg-slate-900 text-white hover:bg-slate-800 shadow-md hover:shadow-lg'
                                ]"
                            >
                                <span v-if="uploading">Subiendo...</span>
                                <span v-else>Iniciar Carga</span>
                            </button>
                        </div>

                        <div v-if="Object.keys(errors).length > 0" class="mt-6 p-4 border border-red-200 bg-red-50 rounded-sm">
                            <div class="flex gap-3">
                                <XMarkIcon class="w-5 h-5 text-red-500" />
                                <div>
                                    <h4 class="text-xs font-bold text-red-700 uppercase tracking-wide mb-1">Error en la carga</h4>
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