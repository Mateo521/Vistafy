<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, watch, computed } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import MapPicker from '@/Components/MapPicker.vue';
import { 
    ArrowLeftIcon, 
    PhotoIcon, 
    XMarkIcon, 
    MapPinIcon,
    CloudArrowUpIcon
} from '@heroicons/vue/24/outline';

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

const getNumericValue = (value, defaultVal) => {
    const num = parseFloat(value);
    return isNaN(num) ? defaultVal : num;
};

const initialLat = getNumericValue(props.photographer?.latitude, -38.4161);
const initialLng = getNumericValue(props.photographer?.longitude, -63.6167);


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


const formattedCoordinates = computed(() => {
    try {
        const lat = parseFloat(coordinates.value.lat);
        const lng = parseFloat(coordinates.value.lng);

        if (isNaN(lat) || isNaN(lng)) {
            return 'Sin coordenadas válidas';
        }

        return `${lat.toFixed(6)}, ${lng.toFixed(6)}`;
    } catch (error) {
        console.error('Error formatting coordinates:', error);
        return 'Error de coordenadas';
    }
});

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
    <Head title="Nueva Oportunidad | F33" />

    <AuthenticatedLayout>
        <div class="min-h-screen bg-[#F8F9FA] text-slate-800 font-sans antialiased py-12 pt-28">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">


                <div class="mb-10 flex flex-col md:flex-row md:items-end justify-between gap-6 border-b border-gray-200 pb-8">
                    <div>
                        <Link :href="route('photographer.opportunities.index')"
                            class="inline-flex items-center gap-2 bg-white px-4 py-2 rounded-full text-xs font-bold uppercase tracking-wider text-gray-500 hover:text-black hover:shadow-sm border border-gray-200 transition-all mb-6">
                            <ArrowLeftIcon class="w-4 h-4" /> Volver a oportunidades
                        </Link>

                        <div class="flex items-center gap-2 mb-2">
                            <span class="w-2 h-2 rounded-full bg-[#E30613] animate-pulse"></span>
                            <span class="text-xs font-bold text-gray-500 uppercase tracking-wider">Planificador</span>
                        </div>

                        <h1 class="text-5xl md:text-7xl font-flux text-black tracking-wide leading-none mb-3">
                            Crear <span class="text-[#E30613]">oportunidad</span>
                        </h1>
                        <p class="text-sm font-medium text-gray-500">
                            Registrá un evento futuro para habilitar postulaciones de cobertura.
                        </p>
                    </div>
                </div>

                <form @submit.prevent="submit" class="space-y-8">


                    <div class="bg-white border border-gray-100 rounded shadow-[0_8px_30px_rgb(0,0,0,0.04)] p-6 md:p-10 transition-all duration-300 hover:shadow-md">
                        <h2 class="text-xs font-bold uppercase tracking-widest text-gray-400 mb-8 flex items-center gap-2">
                            <span class="w-4 h-px bg-gray-200"></span> Información principal
                        </h2>

                        <div class="space-y-6">
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 mb-2 ml-1">
                                    Título de la oportunidad <span class="text-[#E30613]">*</span>
                                </label>
                                <input v-model="form.title" type="text" placeholder="Ej: Evento Corporativo Tech 2026"
                                    class="w-full bg-gray-50 border border-transparent focus:bg-white focus:border-gray-300 focus:ring-4 focus:ring-gray-100 text-slate-800 font-bold text-lg py-3.5 px-4 rounded transition-all outline-none placeholder-gray-400"
                                    required />
                                <span v-if="form.errors.title" class="text-[#E30613] text-xs font-bold mt-2 ml-1 block">
                                    {{ form.errors.title }}
                                </span>
                            </div>

                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 mb-2 ml-1">
                                    Parámetros y requisitos <span class="text-[#E30613]">*</span>
                                </label>
                                <textarea v-model="form.description" rows="5"
                                    placeholder="Especificar tipo de cobertura, equipo requerido, código de vestimenta..."
                                    class="w-full bg-gray-50 border border-transparent focus:bg-white focus:border-gray-300 focus:ring-4 focus:ring-gray-100 text-slate-700 font-medium text-sm py-3.5 px-4 rounded transition-all outline-none resize-none placeholder-gray-400"
                                    required></textarea>
                                <span v-if="form.errors.description" class="text-[#E30613] text-xs font-bold mt-2 ml-1 block">
                                    {{ form.errors.description }}
                                </span>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 mb-2 ml-1">
                                        Fecha programada <span class="text-[#E30613]">*</span>
                                    </label>
                                    <input v-model="form.event_date" type="date"
                                        class="w-full bg-gray-50 border border-transparent focus:bg-white focus:border-gray-300 focus:ring-4 focus:ring-gray-100 text-slate-700 font-bold text-sm py-3.5 px-4 rounded transition-all outline-none"
                                        required />
                                    <span v-if="form.errors.event_date" class="text-[#E30613] text-xs font-bold mt-2 ml-1 block">
                                        {{ form.errors.event_date }}
                                    </span>
                                </div>

                                <div>
                                    <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 mb-2 ml-1">
                                        Hora de inicio <span class="font-normal normal-case text-gray-400">(opcional)</span>
                                    </label>
                                    <input v-model="form.event_time" type="time"
                                        class="w-full bg-gray-50 border border-transparent focus:bg-white focus:border-gray-300 focus:ring-4 focus:ring-gray-100 text-slate-700 font-bold text-sm py-3.5 px-4 rounded transition-all outline-none" />
                                </div>
                            </div>
                        </div>
                    </div>


                    <div class="bg-white border border-gray-100 rounded shadow-[0_8px_30px_rgb(0,0,0,0.04)] p-6 md:p-10 transition-all duration-300 hover:shadow-md">
                        <h2 class="text-xs font-bold uppercase tracking-widest text-[#E30613] mb-6 flex items-center gap-2 border-b border-gray-100 pb-4">
                            <MapPinIcon class="w-4 h-4" /> Ubicación Geográfica <span class="text-[#E30613]">*</span>
                        </h2>

                        <div class="space-y-4">

                            <div class="rounded overflow-hidden border border-gray-200 shadow-inner">
                                <MapPicker v-model="coordinates"
                                    :initial-center="{ lat: photographer.latitude, lng: photographer.longitude }" :zoom="10"
                                    @update:location="updateLocation" />
                            </div>

                            <input v-model="form.location" type="text" placeholder="Dirección auto-generada o ingresa manualmente"
                                class="w-full bg-gray-50 border border-transparent focus:bg-white focus:border-gray-300 focus:ring-4 focus:ring-gray-100 text-slate-700 font-bold text-sm py-3.5 px-4 rounded transition-all outline-none placeholder-gray-400 mt-2"
                                required readonly />

                            <div class="flex items-center gap-2">
                                <div class="text-[10px] font-bold uppercase tracking-wider text-gray-500 bg-gray-50 px-3 py-1.5 rounded-md border border-gray-200">
                                    Coord: {{ formattedCoordinates }}
                                </div>
                            </div>

                            <span v-if="form.errors.location" class="text-[#E30613] text-xs font-bold mt-2 ml-1 block">
                                {{ form.errors.location }}
                            </span>
                            <span v-if="form.errors.latitude || form.errors.longitude" class="text-[#E30613] text-xs font-bold mt-2 ml-1 block">
                                Error en el sistema de coordenadas.
                            </span>
                        </div>
                    </div>


                    <div class="bg-white border border-gray-100 rounded shadow-[0_8px_30px_rgb(0,0,0,0.04)] p-6 md:p-10 transition-all duration-300 hover:shadow-md">
                        <h2 class="text-xs font-bold uppercase tracking-widest text-gray-400 mb-8 flex items-center gap-2">
                            <span class="w-4 h-px bg-gray-200"></span> Imagen de referencia
                        </h2>

                        <div v-if="imagePreview" class="relative w-full max-w-md bg-gray-50 rounded border border-gray-200 overflow-hidden shadow-inner group">
                            <img :src="imagePreview" class="w-full h-56 object-cover transition-transform duration-500 " />
                            
                            <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center pointer-events-none"></div>
                            
                            <button type="button" @click="removeImage"
                                class="absolute top-3 right-3 p-2 bg-white text-gray-500 hover:text-[#E30613] hover:bg-red-50 rounded-full shadow-sm transition-colors z-10" title="Eliminar Imagen">
                                <XMarkIcon class="w-5 h-5" />
                            </button>
                        </div>


                        <label v-else
                            class="flex flex-col items-center justify-center w-full h-40 border-2 border-dashed border-gray-300 bg-gray-50 rounded cursor-pointer hover:bg-white hover:border-black transition-all group">
                            <CloudArrowUpIcon class="w-10 h-10 text-gray-400 mb-3 group-hover:text-black transition-colors" />
                            <span class="text-sm font-bold text-gray-600 group-hover:text-black transition-colors mb-1">
                                Hacé clic para cargar imagen
                            </span>
                            <span class="text-xs font-medium text-gray-400">
                                JPG, PNG (Max. 5MB)
                            </span>
                            <input type="file" accept="image/*" @change="handleImageUpload" class="hidden" />
                        </label>

                        <span v-if="form.errors.cover_image" class="text-[#E30613] text-xs font-bold mt-2 ml-1 block">
                            {{ form.errors.cover_image }}
                        </span>
                    </div>


                    <div class="flex flex-col-reverse sm:flex-row justify-end gap-3 pt-6 border-t border-gray-200">
                        <Link :href="route('photographer.opportunities.index')"
                            class="px-8 py-3.5 rounded-full border border-gray-200 bg-white text-gray-600 font-bold text-xs uppercase tracking-wider hover:bg-gray-50 hover:text-black transition-colors text-center shadow-sm">
                            Cancelar
                        </Link>

                        <button type="submit" :disabled="form.processing"
                            class="px-10 py-3.5 rounded-full bg-black text-white font-bold text-xs uppercase tracking-wider hover:bg-[#E30613] hover:shadow-lg hover:shadow-red-500/30 hover:-translate-y-1 transition-all disabled:opacity-50 disabled:hover:translate-y-0 disabled:hover:shadow-none text-center flex items-center justify-center gap-2">
                            <span v-if="form.processing" class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin"></span>
                            {{ form.processing ? 'Procesando...' : 'Crear Oportunidad' }}
                        </button>
                    </div>

                </form>

            </div>
        </div>
    </AuthenticatedLayout>
</template>