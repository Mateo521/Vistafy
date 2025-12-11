<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, watch, computed } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { ArrowLeftIcon, PhotoIcon, XMarkIcon, MapPinIcon } from '@heroicons/vue/24/outline';
import MapPicker from '@/Components/MapPicker.vue';
const props = defineProps({
    opportunity: {
        type: Object,
        required: true,
    },
});


// Preview de imagen (usa la existente o nueva)
const imagePreview = ref(props.opportunity.cover_image);

const handleImageUpload = (event) => {
    const file = event.target.files[0];
    if (file) {
        form.cover_image = file;
        form.remove_image = false;
        imagePreview.value = URL.createObjectURL(file);
    }
};

const removeImage = () => {
    form.cover_image = null;
    form.remove_image = true;
    imagePreview.value = null;
};

const submit = () => {
    form.post(route('photographer.opportunities.update', props.opportunity.id), {
        preserveScroll: true,
        forceFormData: true, //  Necesario para enviar archivos con POST simulando PUT
        onSuccess: () => {
            // No reset porque es edición
        },
    });
};


// Helper para asegurar números
const getNumericValue = (value, defaultVal) => {
    const num = parseFloat(value);
    return isNaN(num) ? defaultVal : num;
};

const initialLat = getNumericValue(props.opportunity.latitude, -34.6037);
const initialLng = getNumericValue(props.opportunity.longitude, -58.3816);

const form = useForm({
    title: props.opportunity.title,
    description: props.opportunity.description,
    location: props.opportunity.location,
    latitude: initialLat,           // 
    longitude: initialLng,          // 
    event_date: props.opportunity.event_date,
    event_time: props.opportunity.event_time || '',
    cover_image: null,
    remove_image: false,
});

// Coordenadas reactivas para el mapa
const coordinates = ref({
    lat: initialLat,
    lng: initialLng,
});



// Computed para mostrar coordenadas formateadas
const formattedCoordinates = computed(() => {
    const lat = parseFloat(coordinates.value.lat);
    const lng = parseFloat(coordinates.value.lng);
    if (isNaN(lat) || isNaN(lng)) return 'Coordenadas no válidas';
    return `${lat.toFixed(6)}, ${lng.toFixed(6)}`;
});

// Actualizar lat/long en el form cuando se mueve el marcador
watch(coordinates, (newVal) => {
    const lat = parseFloat(newVal.lat);
    const lng = parseFloat(newVal.lng);
    if (!isNaN(lat) && !isNaN(lng)) {
        form.latitude = lat;
        form.longitude = lng;
    }
}, { deep: true });




// Cuando el mapa hace geocoding inverso, actualiza location
const updateLocation = (address) => {
    form.location = address;
};


</script>

<template>
    <AuthenticatedLayout>

        <Head title="Editar Oportunidad" />

        <div class="max-w-7xl mx-auto px-4 py-6 sm:px-6 lg:px-8">

            <!-- Header -->
            <div class="mb-8">
                <Link :href="route('photographer.opportunities.index')"
                    class="inline-flex items-center text-slate-600 hover:text-slate-900 text-sm font-medium mb-4">
                    <ArrowLeftIcon class="w-4 h-4 mr-2" />
                    Volver a Oportunidades
                </Link>

                <h1 class="text-3xl font-serif font-bold text-slate-900">
                    Editar Oportunidad
                </h1>
                <p class="text-slate-600 mt-2">
                    Modificá los detalles de tu evento futuro
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

                <div class="mb-6">
                    <label class="block text-sm font-bold text-slate-900 mb-2 flex items-center gap-2">
                        <MapPinIcon class="w-5 h-5" />
                        Ubicación del Evento *
                    </label>

                    <MapPicker v-model="coordinates" :initial-center="{ lat: initialLat, lng: initialLng }" :zoom="12"
                        @update:location="updateLocation" />

                    <input v-model="form.location" type="text" placeholder="La dirección se actualizará automáticamente"
                        class="w-full px-4 py-3 border border-gray-300 rounded-sm focus:ring-2 focus:ring-slate-900 focus:border-transparent mt-4"
                        required />

                    <p class="text-xs text-slate-500 mt-2">
                        Coordenadas: {{ formattedCoordinates }}
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
                            {{ imagePreview ? 'Click para cambiar imagen' : 'Click para subir imagen (opcional)' }}
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
                        {{ form.processing ? 'Guardando...' : 'Guardar Cambios' }}
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
