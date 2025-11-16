<template>
    <PhotographerLayout>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <!-- Header -->
            <div class="mb-8">
                <Link :href="route('photographer.photos.index')" class="text-indigo-600 hover:text-indigo-800 font-semibold mb-4 inline-block">
                    ← Volver a Mis Fotos
                </Link>
                <h1 class="text-3xl font-bold text-gray-900">Subir Nuevas Fotos</h1>
                <p class="text-gray-600 mt-2">Arrastra y suelta tus fotos o haz clic para seleccionarlas</p>
            </div>

            <form @submit.prevent="submitPhotos">
                <!-- Configuración Global -->
                <div class="bg-white rounded-lg shadow p-6 mb-6">
                    <h2 class="text-xl font-bold text-gray-900 mb-4">Configuración General</h2>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Precio -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                Precio por Foto *
                            </label>
                            <div class="relative">
                                <span class="absolute left-3 top-3 text-gray-500">$</span>
                                <input 
                                    v-model="form.price"
                                    type="number"
                                    step="0.01"
                                    min="0.01"
                                    required
                                    class="w-full pl-8 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                    placeholder="5.00"
                                >
                            </div>
                            <p v-if="errors.price" class="text-red-500 text-sm mt-1">{{ errors.price }}</p>
                        </div>

                        <!-- Evento -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                Asociar a Evento (Opcional)
                            </label>
                            <select 
                                v-model="form.event_id"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                            >
                                <option :value="null">Sin evento</option>
                                <option v-for="event in events" :key="event.id" :value="event.id">
                                    {{ event.name }} {{ event.event_date ? `(${formatDate(event.event_date)})` : '' }}
                                </option>
                            </select>
                        </div>

                        <!-- Estado -->
                        <div class="flex items-center">
                            <input 
                                v-model="form.is_active"
                                type="checkbox"
                                id="is_active"
                                class="w-5 h-5 text-indigo-600 border-gray-300 rounded focus:ring-2 focus:ring-indigo-500"
                            >
                            <label for="is_active" class="ml-3 text-sm font-semibold text-gray-700">
                                Publicar fotos inmediatamente
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Zona de Arrastre -->
                <div 
                    @dragover.prevent="dragOver = true"
                    @dragleave.prevent="dragOver = false"
                    @drop.prevent="handleDrop"
                    :class="[
                        'border-4 border-dashed rounded-xl p-12 text-center transition-all',
                        dragOver 
                            ? 'border-indigo-500 bg-indigo-50' 
                            : 'border-gray-300 bg-gray-50 hover:border-indigo-400 hover:bg-indigo-25'
                    ]"
                >
                    <input 
                        type="file"
                        ref="fileInput"
                        @change="handleFileSelect"
                        multiple
                        accept="image/jpeg,image/jpg,image/png"
                        class="hidden"
                    >

                    <div v-if="selectedFiles.length === 0">
                        <svg class="mx-auto h-24 w-24 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                        </svg>
                        <h3 class="text-xl font-bold text-gray-900 mb-2">
                            Arrastra tus fotos aquí
                        </h3>
                        <p class="text-gray-600 mb-4">
                            o haz clic para seleccionarlas
                        </p>
                        <button 
                            type="button"
                            @click="$refs.fileInput.click()"
                            class="bg-indigo-600 text-white px-8 py-3 rounded-lg hover:bg-indigo-700 transition font-semibold"
                        >
                            Seleccionar Fotos
                        </button>
                        <p class="text-sm text-gray-500 mt-4">
                            Formatos: JPG, PNG • Máximo: 10MB por foto • Hasta 50 fotos
                        </p>
                    </div>

                    <!-- Preview de fotos seleccionadas -->
                    <div v-else>
                        <div class="flex items-center justify-between mb-6">
                            <h3 class="text-xl font-bold text-gray-900">
                                {{ selectedFiles.length }} foto(s) seleccionada(s)
                            </h3>
                            <button 
                                type="button"
                                @click="clearFiles"
                                class="text-red-600 hover:text-red-800 font-semibold"
                            >
                                Limpiar Todo
                            </button>
                        </div>

                        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4 max-h-96 overflow-y-auto">
                            <div 
                                v-for="(file, index) in selectedFiles" 
                                :key="index"
                                class="relative group"
                            >
                                <img 
                                    :src="file.preview" 
                                    :alt="file.name"
                                    class="w-full h-32 object-cover rounded-lg border-2 border-gray-200"
                                >
                                <button 
                                    type="button"
                                    @click="removeFile(index)"
                                    class="absolute top-2 right-2 bg-red-600 text-white p-2 rounded-full opacity-0 group-hover:opacity-100 transition hover:bg-red-700"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                                <p class="text-xs text-gray-600 mt-1 truncate">{{ file.name }}</p>
                            </div>
                        </div>

                        <button 
                            type="button"
                            @click="$refs.fileInput.click()"
                            class="mt-6 bg-gray-200 text-gray-700 px-6 py-2 rounded-lg hover:bg-gray-300 transition font-semibold"
                        >
                            + Agregar Más Fotos
                        </button>
                    </div>
                </div>

                <!-- Barra de Progreso -->
                <div v-if="uploading" class="mt-6 bg-white rounded-lg shadow p-6">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-sm font-semibold text-gray-700">
                            Subiendo {{ uploadProgress.current }} de {{ uploadProgress.total }}...
                        </span>
                        <span class="text-sm font-semibold text-indigo-600">
                            {{ uploadProgress.percentage }}%
                        </span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-3 overflow-hidden">
                        <div 
                            class="bg-indigo-600 h-3 rounded-full transition-all duration-300"
                            :style="{ width: uploadProgress.percentage + '%' }"
                        ></div>
                    </div>
                    <p class="text-sm text-gray-500 mt-2">Por favor espera, no cierres esta página...</p>
                </div>

              <!-- ... código anterior ... -->

                <!-- Errores -->
                <div v-if="Object.keys(errors).length > 0" class="mt-6 bg-red-50 border-l-4 border-red-500 p-4 rounded">
                    <div class="flex items-start">
                        <svg class="h-6 w-6 text-red-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <div>
                            <h3 class="font-semibold text-red-800 mb-2">Se encontraron errores:</h3>
                            <ul class="list-disc list-inside text-red-700 text-sm">
                                <li v-for="(error, key) in errors" :key="key">{{ error }}</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Botones de Acción -->
                <div class="mt-8 flex items-center justify-end space-x-4">
                    <Link 
                        :href="route('photographer.photos.index')"
                        class="px-6 py-3 border border-gray-300 rounded-lg text-gray-700 font-semibold hover:bg-gray-50 transition"
                    >
                        Cancelar
                    </Link>
                    <button 
                        type="submit"
                        :disabled="uploading || selectedFiles.length === 0"
                        :class="[
                            'px-8 py-3 rounded-lg font-semibold transition flex items-center',
                            uploading || selectedFiles.length === 0
                                ? 'bg-gray-300 text-gray-500 cursor-not-allowed'
                                : 'bg-indigo-600 text-white hover:bg-indigo-700'
                        ]"
                    >
                        <svg v-if="uploading" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        {{ uploading ? 'Subiendo...' : `Subir ${selectedFiles.length} Foto(s)` }}
                    </button>
                </div>
            </form>

            <!-- Información sobre marca de agua -->
            <div class="mt-8 bg-blue-50 border-l-4 border-blue-500 p-4 rounded">
                <div class="flex items-start">
                    <svg class="h-6 w-6 text-blue-500 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <div>
                        <h4 class="font-semibold text-blue-800 mb-2">ℹ️ Sobre las marcas de agua</h4>
                        <ul class="text-blue-700 text-sm space-y-1">
                            <li>✅ <strong>Vista previa:</strong> Se mostrará con marca de agua "PREVIEW - NO USAR"</li>
                            <li>✅ <strong>Original:</strong> Se guardará sin marca de agua para la descarga después del pago</li>
                            <li>✅ <strong>Miniaturas:</strong> Tendrán marca de agua para proteger tu trabajo</li>
                            <li>✅ <strong>Protección:</strong> Solo los clientes que paguen recibirán la foto sin marca de agua</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </PhotographerLayout>
</template>

<script setup>
import PhotographerLayout from '@/Layouts/PhotographerLayout.vue';
import { Link, useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';

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

/**
 * Manejar selección de archivos
 */
const handleFileSelect = (event) => {
    const files = Array.from(event.target.files);
    addFiles(files);
};

/**
 * Manejar drag & drop
 */
const handleDrop = (event) => {
    dragOver.value = false;
    const files = Array.from(event.dataTransfer.files);
    addFiles(files);
};

/**
 * Agregar archivos a la lista
 */
const addFiles = (files) => {
    // Validar formato
    const validFiles = files.filter(file => {
        const validTypes = ['image/jpeg', 'image/jpg', 'image/png'];
        const maxSize = 10 * 1024 * 1024; // 10MB

        if (!validTypes.includes(file.type)) {
            alert(`${file.name} no es un formato válido. Solo JPG, JPEG o PNG.`);
            return false;
        }

        if (file.size > maxSize) {
            alert(`${file.name} es muy grande. Máximo 10MB por foto.`);
            return false;
        }

        return true;
    });

    // Limitar a 50 fotos
    const remainingSlots = 50 - selectedFiles.value.length;
    const filesToAdd = validFiles.slice(0, remainingSlots);

    if (validFiles.length > remainingSlots) {
        alert(`Solo puedes subir hasta 50 fotos a la vez. Se agregaron ${remainingSlots} de ${validFiles.length} fotos.`);
    }

    // Crear previews
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

/**
 * Eliminar un archivo
 */
const removeFile = (index) => {
    selectedFiles.value.splice(index, 1);
};

/**
 * Limpiar todos los archivos
 */
const clearFiles = () => {
    selectedFiles.value = [];
    if (fileInput.value) {
        fileInput.value.value = '';
    }
};

/**
 * Enviar fotos al servidor
 */
const submitPhotos = () => {
    if (selectedFiles.value.length === 0) {
        alert('Debes seleccionar al menos una foto');
        return;
    }

    if (!form.price || form.price <= 0) {
        alert('Debes establecer un precio válido');
        return;
    }

    uploading.value = true;
    uploadProgress.value = {
        current: 0,
        total: selectedFiles.value.length,
        percentage: 0,
    };

    // Crear FormData con todas las fotos
    const formData = new FormData();
    
    selectedFiles.value.forEach((item, index) => {
        formData.append(`photos[${index}]`, item.file);
    });

    formData.append('price', form.price);
    formData.append('is_active', form.is_active ? 1 : 0);
    
    if (form.event_id) {
        formData.append('event_id', form.event_id);
    }

    // Enviar con Inertia
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
            console.error('Errores:', errors);
        },
    });
};

/**
 * Formatear fecha
 */
const formatDate = (date) => {
    if (!date) return '';
    return new Date(date).toLocaleDateString('es-ES', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
};
</script>
