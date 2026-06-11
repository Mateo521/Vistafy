<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import {
    MapPinIcon,
    GlobeAltIcon,
    CalendarIcon,
    PhotoIcon,
    XMarkIcon,
    ArrowLongRightIcon,
    EnvelopeIcon,
    PhoneIcon,
    CameraIcon,
    HandThumbUpIcon
} from '@heroicons/vue/24/outline';

const props = defineProps({
    photographer: Object,
    events: Object,
    photos: Object,
    stats: Object,
    activeTab: String,
    filters: Object,
});

const currentTab = ref(props.activeTab);
const selectedEventId = ref(props.filters.event_id || '');

const switchTab = (tab) => {
    currentTab.value = tab;
    router.get(route('photographers.show', props.photographer.slug), {
        tab: tab,
        event_id: selectedEventId.value,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
};

const clearPhotoFilters = () => {
    selectedEventId.value = '';
    router.get(route('photographers.show', props.photographer.slug), {
        tab: 'photos',
    });
};

const getInitials = (name) => {
    return name.split(' ').map(word => word[0]).join('').toUpperCase().substring(0, 2);
};

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString('es-ES', {
        year: 'numeric', month: 'long', day: 'numeric'
    });
};
</script>

<template>
    <AppLayout>

        <Head :title="photographer.business_name" />

        <div class="min-h-screen bg-white">

            <div class="relative h-64 md:h-96 border-b-4 border-black bg-black overflow-hidden group">
                <img v-if="photographer.banner_photo_url" :src="photographer.banner_photo_url"
                    :alt="photographer.business_name" 
                    class="w-full h-full object-cover grayscale opacity-60 group-hover:grayscale-0 group-hover:opacity-100 transition-all duration-700" />
                <div v-else class="w-full h-full flex items-center justify-center bg-zinc-900 text-zinc-800">
                    <PhotoIcon class="w-32 h-32" />
                </div>
                
                <div class="absolute inset-0 bg-[url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAiIGhlaWdodD0iMjAiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PGNpcmNsZSBjeD0iMSIgY3k9IjEiIHI9IjEiIGZpbGw9InJnYmEoMjU1LDI1NSwyNTUsMC4wNSkiLz48L3N2Zz4=')]"></div>
            </div>

            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative -mt-20 md:-mt-24 pb-12">
                <div class="flex flex-col md:flex-row gap-6 md:gap-10 items-start">

                    <div class="w-32 h-32 md:w-48 md:h-48 bg-white p-1 border-4 border-black flex-shrink-0 shadow-[8px_8px_0px_0px_rgba(227,6,19,1)] rounded-none relative z-10">
                        <img v-if="photographer.profile_photo_url" :src="photographer.profile_photo_url"
                            :alt="photographer.business_name" class="w-full h-full object-cover filter grayscale" />
                        <div v-else class="w-full h-full bg-black flex items-center justify-center">
                            <span class="text-5xl font-bebas text-white tracking-widest">
                                {{ getInitials(photographer.business_name) }}
                            </span>
                        </div>
                    </div>

                    <div class="flex-1 pt-4 md:pt-28 w-full">
                        <div class="flex flex-col xl:flex-row justify-between items-start gap-8">
                            
                            <div class="flex-1">
                                <h1 class="font-bebas text-6xl md:text-8xl text-black leading-none tracking-tighter uppercase mb-2 group cursor-default">
                                    <span class="group-hover:text-[#E30613] transition-colors duration-300">{{ photographer.business_name }}</span>
                                </h1>

                                <div class="flex flex-wrap items-center gap-3 font-mono text-[10px] font-bold uppercase tracking-widest mb-8">
                                    <span v-if="photographer.region" class="flex items-center bg-black text-white px-3 py-1.5 border-2 border-black">
                                        <MapPinIcon class="w-3 h-3 mr-2" />
                                        {{ photographer.region }}
                                    </span>
                                    <span class="flex items-center bg-[#E30613] text-white px-3 py-1.5 border-2 border-[#E30613]">
                                        PRO_NODE Verificado
                                    </span>
                                </div>
                            </div>

                            <div class="flex gap-4 w-full xl:w-auto">
                                <div class="flex-1 xl:flex-none border-2 border-black bg-white p-4 text-center min-w-[120px] shadow-[4px_4px_0px_0px_rgba(0,0,0,1)] hover:-translate-y-1 hover:shadow-[6px_6px_0px_0px_rgba(227,6,19,1)] transition-all">
                                    <span class="block font-bebas text-5xl text-black leading-none">{{ stats.total_events }}</span>
                                    <span class="font-mono text-[10px] uppercase font-bold text-zinc-500 mt-1 block tracking-widest">Eventos</span>
                                </div>
                                <div class="flex-1 xl:flex-none border-2 border-black bg-white p-4 text-center min-w-[120px] shadow-[4px_4px_0px_0px_rgba(0,0,0,1)] hover:-translate-y-1 hover:shadow-[6px_6px_0px_0px_rgba(227,6,19,1)] transition-all">
                                    <span class="block font-bebas text-5xl text-[#E30613] leading-none">{{ stats.total_photos }}</span>
                                    <span class="font-mono text-[10px] uppercase font-bold text-zinc-500 mt-1 block tracking-widest">Fotografías</span>
                                </div>
                            </div>

                        </div>

                        <div class="mt-10 grid grid-cols-1 lg:grid-cols-3 gap-8 border-t-4 border-black pt-8">
                            <div class="lg:col-span-2">
                                <p v-if="photographer.bio" class="font-mono text-xs text-zinc-700 leading-relaxed border-l-4 border-[#E30613] pl-4">
                                    {{ photographer.bio }}
                                </p>
                                <p v-else class="font-mono text-xs text-zinc-400 italic">No hay biografía disponible.</p>
                            </div>

                            <div class="flex flex-wrap gap-3 content-start">
                                <a v-if="photographer.email" :href="`mailto:${photographer.email}`"
                                    class="flex items-center justify-center gap-2 px-4 py-2 border-2 border-black bg-white font-mono text-[10px] font-bold uppercase tracking-widest text-black hover:bg-black hover:text-white transition-colors">
                                    <EnvelopeIcon class="w-4 h-4" /> Email
                                </a>

                                <a v-if="photographer.phone"
                                    :href="`https://wa.me/${photographer.phone.replace(/[^0-9]/g, '')}`" target="_blank"
                                    class="flex items-center justify-center gap-2 px-4 py-2 border-2 border-black bg-white font-mono text-[10px] font-bold uppercase tracking-widest text-black hover:bg-green-600 hover:border-green-600 hover:text-white transition-colors">
                                    <PhoneIcon class="w-4 h-4" /> WhatsApp
                                </a>

                                <a v-if="photographer.website" :href="photographer.website" target="_blank"
                                    class="w-10 h-10 flex items-center justify-center border-2 border-black bg-white text-black hover:bg-black hover:text-white transition-colors" title="Sitio Web">
                                    <GlobeAltIcon class="w-4 h-4" />
                                </a>

                                <a v-if="photographer.instagram" :href="`https://instagram.com/${photographer.instagram}`" target="_blank"
                                    class="w-10 h-10 flex items-center justify-center border-2 border-black bg-white text-black hover:bg-[#E30613] hover:border-[#E30613] hover:text-white transition-colors" title="Instagram">
                                    <CameraIcon class="w-4 h-4" />
                                </a>

                                <a v-if="photographer.facebook" :href="`https://facebook.com/${photographer.facebook}`" target="_blank"
                                    class="w-10 h-10 flex items-center justify-center border-2 border-black bg-white text-black hover:bg-[#1877F2] hover:border-[#1877F2] hover:text-white transition-colors" title="Facebook">
                                    <HandThumbUpIcon class="w-4 h-4" />
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
                <div class="flex border-b-4 border-black">
                    <button @click="switchTab('events')"
                        :class="['flex-1 py-4 font-bebas text-3xl uppercase tracking-wider transition-all border-r-4 border-l-4 border-t-4 border-black',
                            currentTab === 'events' ? 'bg-black text-white translate-y-1 border-b-0' : 'bg-white text-zinc-400 hover:text-black hover:bg-zinc-100 border-b-4 border-l-4 border-transparent border-t-transparent']">
                        Portafolio
                    </button>
                    <button @click="switchTab('photos')"
                        :class="['flex-1 py-4 font-bebas text-3xl uppercase tracking-wider transition-all border-r-4 border-t-4 border-black',
                            currentTab === 'photos' ? 'bg-black text-white translate-y-1 border-b-0 border-l-4' : 'bg-white text-zinc-400 hover:text-black hover:bg-zinc-100 border-b-4 border-r-4 border-transparent border-t-transparent']">
                        Archivo Fotográfico
                    </button>
                </div>
            </div>

            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">

                <div v-show="currentTab === 'events'">
                    <div v-if="events.data.length > 0" class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <Link v-for="event in events.data" :key="event.id" :href="route('events.show', event.slug)"
                            class="group flex flex-col md:flex-row bg-white border-4 border-black hover:shadow-[8px_8px_0px_0px_rgba(227,6,19,1)] hover:-translate-y-1 transition-all duration-300 rounded-none overflow-hidden">

                            <div class="w-full md:w-2/5 h-48 md:h-auto border-b-4 md:border-b-0 md:border-r-4 border-black relative overflow-hidden bg-black">
                                <img v-if="event.cover_image_url" :src="event.cover_image_url" :alt="event.name"
                                    class="w-full h-full object-cover grayscale opacity-80 group-hover:grayscale-0 group-hover:opacity-100 group-hover:scale-105 transition-all duration-500" />
                                <div v-else class="w-full h-full flex items-center justify-center text-zinc-800">
                                    <PhotoIcon class="w-12 h-12" />
                                </div>
                            </div>

                            <div class="w-full md:w-3/5 p-6 flex flex-col justify-between">
                                <div>
                                    <span class="inline-block bg-black text-white font-mono text-[10px] font-bold uppercase tracking-widest px-2 py-1 mb-4">
                                        {{ formatDate(event.event_date) }}
                                    </span>
                                    <h3 class="text-3xl font-bebas text-black group-hover:text-[#E30613] uppercase leading-none tracking-tight transition-colors mb-3">
                                        {{ event.name }}
                                    </h3>
                                    <p v-if="event.location" class="font-mono text-[10px] text-zinc-500 flex items-center gap-2 mb-4 uppercase font-bold">
                                        <MapPinIcon class="w-3 h-3 text-[#E30613]" /> {{ event.location }}
                                    </p>
                                </div>
                                <div class="flex items-center font-mono text-[10px] font-bold uppercase tracking-widest text-black group-hover:translate-x-2 transition-transform duration-300 border-t-2 border-black pt-4">
                                    Explorar_Carpeta <ArrowLongRightIcon class="w-4 h-4 ml-auto text-[#E30613]" />
                                </div>
                            </div>
                        </Link>
                    </div>

                    <div v-else class="text-center py-24 border-4 border-black border-dashed bg-zinc-50">
                        <CalendarIcon class="w-16 h-16 text-zinc-300 mx-auto mb-4" />
                        <p class="font-mono text-xs font-bold uppercase tracking-widest text-black">NO_DATA_FOUND // 0 Eventos</p>
                    </div>
                </div>

                <div v-show="currentTab === 'photos'">

                    <div v-if="selectedEventId"
                        class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-8 p-4 bg-black border-4 border-black">
                        <span class="font-mono text-[10px] font-bold uppercase tracking-widest text-white mb-3 sm:mb-0">
                            > SYSTEM_FILTER_ACTIVE: EVENT_{{ selectedEventId }}
                        </span>
                        <button @click="clearPhotoFilters"
                            class="bg-[#E30613] text-white px-4 py-2 font-mono text-[10px] font-bold uppercase tracking-widest hover:bg-white hover:text-[#E30613] transition-colors flex items-center gap-2">
                            <XMarkIcon class="w-3 h-3" /> Purgar Filtro
                        </button>
                    </div>

                    <div v-if="photos.data.length > 0">
                        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-5 gap-1 p-1 bg-black border-4 border-black">

                            <div v-for="photo in photos.data" :key="photo.id"
                                @click="router.visit(route('gallery.show', photo.unique_id))" @contextmenu.prevent
                                class="group aspect-square bg-zinc-900 overflow-hidden relative cursor-pointer">

                                <img :src="photo.watermarked_url" :alt="photo.unique_id" draggable="false"
                                    @contextmenu.prevent
                                    class="w-full h-full object-cover grayscale opacity-80 group-hover:grayscale-0 group-hover:opacity-100 group-hover:scale-110 transition-all duration-500 select-none pointer-events-none" />

                                <div class="absolute inset-0 bg-[#E30613]/0 group-hover:bg-[#E30613]/20 transition-colors duration-300 pointer-events-none border-2 border-transparent group-hover:border-[#E30613] mix-blend-multiply">
                                </div>
                                
                                <div class="absolute bottom-2 left-2 bg-black text-white font-mono text-[8px] px-1 py-0.5 opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none">
                                    {{ photo.unique_id }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <div v-else class="text-center py-24 border-4 border-black border-dashed bg-zinc-50">
                        <PhotoIcon class="w-16 h-16 text-zinc-300 mx-auto mb-4" />
                        <p class="font-mono text-xs font-bold uppercase tracking-widest text-black">STORAGE_EMPTY // 0 Fotografías</p>
                    </div>
                </div>

            </div>
        </div>
    </AppLayout>
</template>