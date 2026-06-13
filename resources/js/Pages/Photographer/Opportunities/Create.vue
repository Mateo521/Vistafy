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
            return 'ERR_COORD_INVALID';
        }

        return `${lat.toFixed(6)}, ${lng.toFixed(6)}`;
    } catch (error) {
        console.error('Error formatting coordinates:', error);
        return 'ERR_COORD_FAULT';
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
    <Head title="Inicializar Oportunidad" />

    <AuthenticatedLayout>
        <div class="min-h-screen bg-[#050505] text-white selection:bg-[#E30613] selection:text-black py-12">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">

                <div class="mb-10">
                    <Link :href="route('photographer.opportunities.index')"
                        class="inline-flex items-center font-mono text-[10px] font-bold uppercase tracking-widest text-zinc-500 hover:text-white transition-colors mb-6 border border-zinc-800 bg-[#09090b] px-4 py-2 hover:border-white">
                        <ArrowLeftIcon class="w-3 h-3 mr-2" />
                        Cancelar y Volver
                    </Link>

                    <span class="font-mono text-[10px] font-bold text-[#E30613] uppercase tracking-widest mb-2 block flex items-center gap-2">
                        <span class="w-2 h-2 bg-[#E30613] animate-pulse"></span>
                        >_ NUEVA_OPORTUNIDAD
                    </span>
                    <h1 class="text-4xl md:text-5xl font-flux font-black text-white uppercase tracking-tighter leading-none">
                        Inicializar Oportunidad
                    </h1>
                    <p class="font-mono text-xs text-zinc-500 mt-4 uppercase tracking-widest border-l-2 border-[#E30613] pl-3">
                        Registrá un evento futuro para habilitar postulaciones de cobertura
                    </p>
                </div>

                <form @submit.prevent="submit" class="bg-[#09090b] border border-zinc-800 p-8 shadow-[8px_8px_0px_0px_rgba(255,255,255,0.05)]">

                    <div class="mb-8">
                        <label class="block font-mono text-[10px] font-bold uppercase tracking-widest text-zinc-400 mb-2">
                            > Identificador del Evento (Título) *
                        </label>
                        <input v-model="form.title" type="text" placeholder="Ej: EVENTO CORPORATIVO TECH 2026"
                            class="w-full px-4 py-3 bg-black border border-zinc-700 text-white font-mono text-xs placeholder-zinc-700 focus:border-[#E30613] focus:ring-0 rounded-none transition-colors uppercase"
                            required />
                        <span v-if="form.errors.title" class="font-mono text-[10px] text-[#E30613] mt-2 block tracking-widest uppercase">
                            ERR: {{ form.errors.title }}
                        </span>
                    </div>

                    <div class="mb-8">
                        <label class="block font-mono text-[10px] font-bold uppercase tracking-widest text-zinc-400 mb-2">
                            > Parámetros y Requisitos (Descripción) *
                        </label>
                        <textarea v-model="form.description" rows="5"
                            placeholder="ESPECIFICAR TIPO DE COBERTURA, EQUIPO REQUERIDO, CÓDIGO DE VESTIMENTA..."
                            class="w-full px-4 py-3 bg-black border border-zinc-700 text-white font-mono text-xs placeholder-zinc-700 focus:border-[#E30613] focus:ring-0 rounded-none transition-colors resize-none uppercase"
                            required></textarea>
                        <span v-if="form.errors.description" class="font-mono text-[10px] text-[#E30613] mt-2 block tracking-widest uppercase">
                            ERR: {{ form.errors.description }}
                        </span>
                    </div>

                    <div class="mb-8 p-6 bg-black border border-zinc-800">
                        <label class="block font-mono text-[10px] font-bold uppercase tracking-widest text-[#E30613] mb-4 flex items-center gap-2">
                            <MapPinIcon class="w-4 h-4" />
                            Ubicación Geográfica *
                        </label>

                        <div class="border border-zinc-700">
                            <MapPicker v-model="coordinates"
                                :initial-center="{ lat: photographer.latitude, lng: photographer.longitude }" :zoom="10"
                                @update:location="updateLocation" />
                        </div>

                        <input v-model="form.location" type="text" placeholder="DATOS DE UBICACIÓN AUTO-GENERADOS"
                            class="w-full px-4 py-3 bg-[#09090b] border border-zinc-700 text-white font-mono text-[10px] placeholder-zinc-600 focus:border-[#E30613] focus:ring-0 rounded-none transition-colors mt-4 uppercase tracking-widest"
                            required readonly />

                        <div class="mt-3 flex items-center gap-2">
                            <div class="font-mono text-[10px] font-bold uppercase tracking-widest text-zinc-500 bg-[#09090b] px-3 py-1 border border-zinc-800">
                                COORD: {{ formattedCoordinates }}
                            </div>
                        </div>

                        <span v-if="form.errors.location" class="font-mono text-[10px] text-[#E30613] mt-2 block tracking-widest uppercase">
                            ERR: {{ form.errors.location }}
                        </span>
                        <span v-if="form.errors.latitude || form.errors.longitude" class="font-mono text-[10px] text-[#E30613] mt-2 block tracking-widest uppercase">
                            ERR_GEO: ERROR EN SISTEMA DE COORDENADAS
                        </span>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                        <div>
                            <label class="block font-mono text-[10px] font-bold uppercase tracking-widest text-zinc-400 mb-2">
                                > Fecha Programada *
                            </label>
                            <input v-model="form.event_date" type="date"
                                class="w-full px-4 py-3 bg-black border border-zinc-700 text-white font-mono text-xs focus:border-[#E30613] focus:ring-0 rounded-none transition-colors uppercase"
                                required />
                            <span v-if="form.errors.event_date" class="font-mono text-[10px] text-[#E30613] mt-2 block tracking-widest uppercase">
                                ERR: {{ form.errors.event_date }}
                            </span>
                        </div>

                        <div>
                            <label class="block font-mono text-[10px] font-bold uppercase tracking-widest text-zinc-400 mb-2">
                                > Hora de inicio (Opcional)
                            </label>
                            <input v-model="form.event_time" type="time"
                                class="w-full px-4 py-3 bg-black border border-zinc-700 text-white font-mono text-xs focus:border-[#E30613] focus:ring-0 rounded-none transition-colors uppercase" />
                        </div>
                    </div>

                    <div class="mb-10">
                        <label class="block font-mono text-[10px] font-bold uppercase tracking-widest text-zinc-400 mb-4 border-t border-zinc-800 pt-6">
                            > Imagen de Referencia / Portada
                        </label>

                        <div v-if="imagePreview" class="relative mb-4 border border-zinc-700 p-1 bg-black w-max">
                            <img :src="imagePreview" class="w-full max-w-sm h-48 object-cover filter grayscale hover:grayscale-0 transition-all duration-500" />
                            <button type="button" @click="removeImage"
                                class="absolute top-3 right-3 p-2 bg-[#E30613] text-black hover:bg-white transition-colors border border-black" title="Purgar Imagen">
                                <XMarkIcon class="w-4 h-4" />
                            </button>
                        </div>

                        <label v-else
                            class="flex flex-col items-center justify-center w-full h-32 border-2 border-dashed border-zinc-700 bg-black cursor-pointer hover:border-[#E30613] hover:bg-[#E30613]/5 transition-colors group">
                            <PhotoIcon class="w-8 h-8 text-zinc-600 mb-2 group-hover:text-[#E30613] transition-colors" />
                            <span class="font-mono text-[10px] font-bold uppercase tracking-widest text-zinc-500 group-hover:text-zinc-300 transition-colors">
                                CLICK PARA CARGAR ARCHIVO
                            </span>
                            <input type="file" accept="image/*" @change="handleImageUpload" class="hidden" />
                        </label>

                        <span v-if="form.errors.cover_image" class="font-mono text-[10px] text-[#E30613] mt-2 block tracking-widest uppercase">
                            ERR: {{ form.errors.cover_image }}
                        </span>
                    </div>

                    <div class="flex flex-col sm:flex-row gap-4 border-t border-zinc-800 pt-8">
                        <button type="submit" :disabled="form.processing"
                            class="flex-1 py-4 bg-[#E30613] text-black font-mono text-[10px] font-bold uppercase tracking-widest hover:bg-white transition-colors disabled:opacity-50 text-center border border-[#E30613] hover:border-white">
                            {{ form.processing ? 'PROCESANDO...' : 'EJECUTAR Y CREAR' }}
                        </button>

                        <Link :href="route('photographer.opportunities.index')"
                            class="px-8 py-4 bg-transparent border border-zinc-700 text-zinc-400 font-mono text-[10px] font-bold uppercase tracking-widest hover:border-white hover:text-white transition-colors text-center">
                            CANCELAR
                        </Link>
                    </div>

                </form>

            </div>
        </div>
    </AuthenticatedLayout>
</template>