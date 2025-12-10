<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, watch, computed } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import MapPicker from '@/Components/MapPicker.vue';
import { ArrowLeftIcon, PhotoIcon, XMarkIcon, MapPinIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
    photographer: {
        type: Object,
        default: () => ({
            latitude: -38.4161,
            longitude: -63.6167,
            region: 'Argentina'
        })
    }
});

//  FORZAR conversión a número con parseFloat
const getNumericValue = (value, defaultVal) => {
    const num = parseFloat(value);
    return isNaN(num) ? defaultVal : num;
};

const initialLat = getNumericValue(props.photographer?.latitude, -38.4161);
const initialLng = getNumericValue(props.photographer?.longitude, -63.6167);

//  Inicializar coordinates como números puros
const coordinates = ref({
    lat: initialLat,
    lng: initialLng
});

const form = useForm({
    title: '',
    description: '',
    location: '',
    latitude: initialLat,
    longitude: initialLng,
    event_date: '',
    event_time: '',
    cover_image: null,
});

const imagePreview = ref(null);

//  Computed seguro para mostrar coordenadas
const formattedCoordinates = computed(() => {
    try {
        const lat = parseFloat(coordinates.value.lat);
        const lng = parseFloat(coordinates.value.lng);

        if (isNaN(lat) || isNaN(lng)) {
            return 'Coordenadas no válidas';
        }

        return `${lat.toFixed(6)}, ${lng.toFixed(6)}`;
    } catch (error) {
        console.error('Error formatting coordinates:', error);
        return 'Error en coordenadas';
    }
});

// Actualizar coordenadas en el form
watch(coordinates, (newVal) => {
    form.latitude = parseFloat(newVal.lat);
    form.longitude = parseFloat(newVal.lng);
}, { deep: true });

const handleImageUpload = (event) => {
    const file = event.target.files[0];
    if (file) {
        form.cover_image = file;
        imagePreview.value = URL.createObjectURL(file);
    }
};

const removeImage = () => {
    form.cover_image = null;
    imagePreview.value = null;
};

const updateLocation = (address) => {
    form.location = address;
};

const submit = () => {
    form.post(route('photographer.opportunities.store'), {
        preserveScroll: true,
        onSuccess: () => form.reset(),
    });
};
</script>

<template>
    <AuthenticatedLayout>

        <Head title="Crear Oportunidad" />

        <div class="max-w-7xl mx-auto px-4 py-6 sm:px-6 lg:px-8">

            <!-- Header -->
            <div class="mb-8">
                <Link :href="route('photographer.opportunities.index')"
                    class="inline-flex items-center text-slate-600 hover:text-slate-900 text-sm font-medium mb-4">
                    <ArrowLeftIcon class="w-4 h-4 mr-2" />
                    Volver a Oportunidades
                </Link>

                <h1 class="text-3xl font-serif font-bold text-slate-900">
                    Crear Nueva Oportunidad
                </h1>
                <p class="text-slate-600 mt-2">
                    Publicá un evento futuro para que otros fotógrafos puedan postularse
                </p>
            </div>

            <!-- Formulario -->
            <form @submit.prevent="submit" class="bg-white border border-gray-200 rounded-lg p-8">

                <!-- Título -->
                <div class="mb-6">
                    <label class="block text-sm font-bold text-slate-900 mb-2">
                        Título del Evento *
                    </label>
                    <input v-model="form.title" type="text" placeholder="Ej: Casamiento Juan y María"
                        class="w-full px-4 py-3 border border-gray-300 rounded-sm focus:ring-2 focus:ring-slate-900 focus:border-transparent"
                        required />
                    <span v-if="form.errors.title" class="text-xs text-red-600 mt-1">
                        {{ form.errors.title }}
                    </span>
                </div>

                <!-- Descripción -->
                <div class="mb-6">
                    <label class="block text-sm font-bold text-slate-900 mb-2">
                        Descripción *
                    </label>
                    <textarea v-model="form.description" rows="4"
                        placeholder="Describe los detalles del evento, qué tipo de cobertura necesitás, etc."
                        class="w-full px-4 py-3 border border-gray-300 rounded-sm focus:ring-2 focus:ring-slate-900 focus:border-transparent"
                        required></textarea>
                    <span v-if="form.errors.description" class="text-xs text-red-600 mt-1">
                        {{ form.errors.description }}
                    </span>
                </div>

                <!--  MAPA SELECTOR DE UBICACIÓN -->
                <div class="mb-6">
                    <label class="block text-sm font-bold text-slate-900 mb-2 flex items-center gap-2">
                        <MapPinIcon class="w-5 h-5" />
                        Ubicación del Evento *
                    </label>

                    <MapPicker v-model="coordinates"
                        :initial-center="{ lat: photographer.latitude, lng: photographer.longitude }" :zoom="10"
                        @update:location="updateLocation" />

                    <input v-model="form.location" type="text" placeholder="La dirección se actualizará automáticamente"
                        class="w-full px-4 py-3 border border-gray-300 rounded-sm focus:ring-2 focus:ring-slate-900 focus:border-transparent mt-4"
                        required />

                    <p class="text-xs text-slate-500 mt-2">
                         Coordenadas: {{ coordinates.lat.toFixed(6) }}, {{ coordinates.lng.toFixed(6) }}
                    </p>

                    <span v-if="form.errors.location" class="text-xs text-red-600 mt-1 block">
                        {{ form.errors.location }}
                    </span>
                    <span v-if="form.errors.latitude || form.errors.longitude" class="text-xs text-red-600 mt-1 block">
                        Error en coordenadas
                    </span>
                </div>

                <!-- Fecha y Hora -->
                <div class="grid grid-cols-2 gap-4 mb-6">
                    <div>
                        <label class="block text-sm font-bold text-slate-900 mb-2">
                            Fecha del Evento *
                        </label>
                        <input v-model="form.event_date" type="date"
                            class="w-full px-4 py-3 border border-gray-300 rounded-sm focus:ring-2 focus:ring-slate-900 focus:border-transparent"
                            required />
                        <span v-if="form.errors.event_date" class="text-xs text-red-600 mt-1">
                            {{ form.errors.event_date }}
                        </span>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-slate-900 mb-2">
                            Hora (Opcional)
                        </label>
                        <input v-model="form.event_time" type="time"
                            class="w-full px-4 py-3 border border-gray-300 rounded-sm focus:ring-2 focus:ring-slate-900 focus:border-transparent" />
                    </div>
                </div>

                <!-- Imagen de Portada -->
                <div class="mb-8">
                    <label class="block text-sm font-bold text-slate-900 mb-2">
                        Imagen de Portada
                    </label>

                    <!-- Preview -->
                    <div v-if="imagePreview" class="relative mb-4">
                        <img :src="imagePreview" class="w-full h-64 object-cover rounded-sm" />
                        <button type="button" @click="removeImage"
                            class="absolute top-2 right-2 p-2 bg-red-600 text-white rounded-full hover:bg-red-700 transition">
                            <XMarkIcon class="w-5 h-5" />
                        </button>
                    </div>

                    <!-- Upload -->
                    <label
                        class="flex flex-col items-center justify-center w-full h-32 border-2 border-dashed border-gray-300 rounded-sm cursor-pointer hover:bg-gray-50 transition">
                        <PhotoIcon class="w-8 h-8 text-gray-400 mb-2" />
                        <span class="text-sm text-gray-600">
                            Click para subir imagen (opcional)
                        </span>
                        <input type="file" accept="image/*" @change="handleImageUpload" class="hidden" />
                    </label>

                    <span v-if="form.errors.cover_image" class="text-xs text-red-600 mt-1">
                        {{ form.errors.cover_image }}
                    </span>
                </div>

                <!-- Botones -->
                <div class="flex gap-4">
                    <button type="submit" :disabled="form.processing"
                        class="flex-1 py-3 bg-slate-900 text-white font-bold text-sm uppercase tracking-widest hover:bg-slate-800 transition rounded-sm disabled:opacity-50">
                        {{ form.processing ? 'Creando...' : 'Crear Oportunidad' }}
                    </button>

                    <Link :href="route('photographer.opportunities.index')"
                        class="px-6 py-3 border-2 border-slate-900 text-slate-900 font-bold text-sm uppercase tracking-widest hover:bg-slate-900 hover:text-white transition rounded-sm">
                        Cancelar
                    </Link>
                </div>

            </form>

        </div>
    </AuthenticatedLayout>
</template>
