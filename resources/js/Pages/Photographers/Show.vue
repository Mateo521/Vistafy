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
    HandThumbUpIcon,
    CheckBadgeIcon
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

        <div class="min-h-screen bg-[#F8F9FA] text-slate-800 antialiased pb-24">

            
            <div class="relative h-64 md:h-80 bg-slate-900 overflow-hidden group">
                <img v-if="photographer.banner_photo_url" :src="photographer.banner_photo_url"
                    :alt="photographer.business_name" 
                    class="w-full h-full object-cover opacity-60 mix-blend-overlay transition-transform duration-1000 " />
                <div v-else class="w-full h-full flex items-center justify-center bg-slate-800 text-slate-700">
                    <PhotoIcon class="w-24 h-24 opacity-20" />
                </div>
                <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent"></div>
            </div>

            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative -mt-20 md:-mt-28 pb-12 z-10">
                
            
                <div class="bg-white rounded shadow-sm border border-gray-100 p-6 md:p-10 flex flex-col md:flex-row gap-8 items-center md:items-start text-center md:text-left">
                    
                    
                    <div class="w-32 h-32 md:w-40 md:h-40 bg-white rounded-full p-1.5 shadow-md flex-shrink-0 relative">
                        <img v-if="photographer.profile_photo_url" :src="photographer.profile_photo_url"
                            :alt="photographer.business_name" class="w-full h-full object-cover rounded-full" />
                        <div v-else class="w-full h-full bg-gray-50 rounded-full flex items-center justify-center border border-gray-100">
                            <span class="text-4xl font-black text-gray-400">
                                {{ getInitials(photographer.business_name) }}
                            </span>
                        </div>
                    </div>

                
                    <div class="flex-1 w-full mt-2 md:mt-6">
                        <div class="flex flex-col xl:flex-row justify-between items-center md:items-start gap-6">
                            
                            <div class="flex-1">
                                <h1 class="font-flux text-5xl md:text-6xl text-black tracking-wide leading-none mb-3">
                                    {{ photographer.business_name }}
                                </h1>

                                <div class="flex flex-wrap items-center justify-center md:justify-start gap-2 mb-6">
                                    <span v-if="photographer.region" class="flex items-center bg-gray-50 text-gray-600 px-3 py-1.5 rounded-md text-xs font-bold uppercase tracking-wider border border-gray-200">
                                        <MapPinIcon class="w-4 h-4 mr-1.5 text-[#E30613]" />
                                        {{ photographer.region }}
                                    </span>
                                    <span class="flex items-center bg-green-50 text-green-700 px-3 py-1.5 rounded-md text-xs font-bold uppercase tracking-wider border border-green-200 gap-1">
                                        <CheckBadgeIcon class="w-4 h-4" /> Verificado
                                    </span>
                                </div>

                                <p v-if="photographer.bio" class="text-sm text-gray-500 leading-relaxed max-w-2xl">
                                    {{ photographer.bio }}
                                </p>
                                <p v-else class="text-sm text-gray-400 italic">Sin biografía disponible.</p>
                            </div>

                        
                            <div class="flex gap-4 shrink-0">
                                <div class="bg-gray-50 rounded p-4 text-center min-w-[100px] border border-gray-100 hover:shadow-sm hover:border-gray-200 transition-all">
                                    <span class="block font-flux text-4xl text-black leading-none mb-1">{{ stats.total_events }}</span>
                                    <span class="text-[10px] font-bold uppercase tracking-wider text-gray-500">Eventos</span>
                                </div>
                                <div class="bg-gray-50 rounded p-4 text-center min-w-[100px] border border-gray-100 hover:shadow-sm hover:border-gray-200 transition-all">
                                    <span class="block font-flux text-4xl text-[#E30613] leading-none mb-1">{{ stats.total_photos }}</span>
                                    <span class="text-[10px] font-bold uppercase tracking-wider text-gray-500">Capturas</span>
                                </div>
                            </div>

                        </div>

                    
                        <div class="flex flex-wrap justify-center md:justify-start gap-3 mt-8 pt-6 border-t border-gray-100">
                            <a v-if="photographer.email" :href="`mailto:${photographer.email}`"
                                class="flex items-center gap-2 px-4 py-2.5 bg-white border border-gray-200 rounded-full text-xs font-bold uppercase tracking-wider text-gray-600 hover:bg-gray-50 hover:text-black transition-colors shadow-sm">
                                <EnvelopeIcon class="w-4 h-4" /> Email
                            </a>

                            <a v-if="photographer.phone"
                                :href="`https://wa.me/${photographer.phone.replace(/[^0-9]/g, '')}`" target="_blank"
                                class="flex items-center gap-2 px-4 py-2.5 bg-white border border-gray-200 rounded-full text-xs font-bold uppercase tracking-wider text-gray-600 hover:bg-green-50 hover:text-green-600 transition-colors shadow-sm">
                                <PhoneIcon class="w-4 h-4" /> WhatsApp
                            </a>

                            <a v-if="photographer.website" :href="photographer.website" target="_blank"
                                class="w-10 h-10 flex items-center justify-center bg-white border border-gray-200 rounded-full text-gray-400 hover:bg-gray-50 hover:text-black transition-colors shadow-sm" title="Sitio Web">
                                <GlobeAltIcon class="w-5 h-5" />
                            </a>

                            <a v-if="photographer.instagram" :href="`https://instagram.com/${photographer.instagram}`" target="_blank"
                                class="w-10 h-10 flex items-center justify-center bg-white border border-gray-200 rounded-full text-gray-400 hover:bg-red-50 hover:text-[#E30613] transition-colors shadow-sm" title="Instagram">
                                <CameraIcon class="w-5 h-5" />
                            </a>

                            <a v-if="photographer.facebook" :href="`https://facebook.com/${photographer.facebook}`" target="_blank"
                                class="w-10 h-10 flex items-center justify-center bg-white border border-gray-200 rounded-full text-gray-400 hover:bg-blue-50 hover:text-blue-600 transition-colors shadow-sm" title="Facebook">
                                <HandThumbUpIcon class="w-5 h-5" />
                            </a>
                        </div>
                    </div>
                </div>
            </div>

        
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mb-10">
                <div class="flex justify-center">
                    <div class="inline-flex bg-gray-200/60 p-1.5 rounded-full shadow-inner">
                        <button @click="switchTab('events')"
                            :class="['px-8 py-3 rounded-full text-xs font-bold uppercase tracking-widest transition-all duration-300',
                                currentTab === 'events' ? 'bg-white text-black shadow-sm' : 'text-gray-500 hover:text-black']">
                            Portafolio
                        </button>
                        <button @click="switchTab('photos')"
                            :class="['px-8 py-3 rounded-full text-xs font-bold uppercase tracking-widest transition-all duration-300',
                                currentTab === 'photos' ? 'bg-white text-black shadow-sm' : 'text-gray-500 hover:text-black']">
                            Archivo completo
                        </button>
                    </div>
                </div>
            </div>

            
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            
                <div v-show="currentTab === 'events'">
                    <div v-if="events.data.length > 0" class="grid grid-cols-1 lg:grid-cols-2 gap-6 lg:gap-8">
                        <Link v-for="event in events.data" :key="event.id" :href="route('events.show', event.slug)"
                            class="group bg-white border border-gray-100 rounded shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300 overflow-hidden flex flex-col sm:flex-row">

                            
                            <div class="w-full sm:w-2/5 h-56 sm:h-auto relative overflow-hidden bg-gray-100 shrink-0">
                                <img v-if="event.cover_image_url" :src="event.cover_image_url" :alt="event.name"
                                    class="w-full h-full object-cover transition-transform duration-700 " />
                                <div v-else class="w-full h-full flex items-center justify-center text-gray-300">
                                    <PhotoIcon class="w-12 h-12" />
                                </div>
                                <div class="absolute inset-0 bg-gradient-to-t from-black/40 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                            </div>

                            
                            <div class="p-6 sm:p-8 flex flex-col justify-between flex-1">
                                <div>
                                    <div class="flex items-center gap-2 mb-3">
                                        <span class="bg-gray-50 text-gray-500 border border-gray-200 text-[10px] font-bold uppercase tracking-widest px-2.5 py-1 rounded-md">
                                            {{ formatDate(event.event_date) }}
                                        </span>
                                    </div>
                                    <h3 class="text-2xl font-flux text-black group-hover:text-[#E30613] leading-none tracking-wide transition-colors mb-2 line-clamp-2">
                                        {{ event.name }}
                                    </h3>
                                    <p v-if="event.location" class="text-xs font-bold text-gray-400 uppercase tracking-wider flex items-center gap-1.5 mb-4">
                                        <MapPinIcon class="w-3.5 h-3.5 text-[#E30613]" /> {{ event.location }}
                                    </p>
                                </div>
                                
                                <div class="flex items-center justify-between border-t border-gray-100 pt-4 mt-4">
                                    <span class="text-xs font-bold text-gray-500 uppercase tracking-wider flex items-center gap-1.5">
                                        <PhotoIcon class="w-4 h-4" /> {{ event.photos_count || 0 }} Fotos
                                    </span>
                                    <span class="w-8 h-8 rounded-full bg-gray-50 flex items-center justify-center group-hover:bg-[#E30613] transition-colors">
                                        <ArrowLongRightIcon class="w-4 h-4 text-gray-400 group-hover:text-white" />
                                    </span>
                                </div>
                            </div>
                        </Link>
                    </div>

                
                    <div v-else class="text-center py-24 bg-white rounded border border-gray-100 shadow-sm flex flex-col items-center">
                        <div class="w-20 h-20 bg-gray-50 rounded-full flex items-center justify-center mb-6">
                            <CalendarIcon class="w-10 h-10 text-gray-300" />
                        </div>
                        <h4 class="text-3xl font-flux text-black mb-2">Portafolio vacío</h4>
                        <p class="text-sm text-gray-500">Este fotógrafo todavía no publicó galerías.</p>
                    </div>
                </div>

            
                <div v-show="currentTab === 'photos'">

                
                    <div v-if="selectedEventId"
                        class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-8 p-4 bg-red-50 border border-red-100 rounded">
                        <span class="text-xs font-bold uppercase tracking-wider text-[#E30613] flex items-center gap-2 mb-3 sm:mb-0">
                            <span class="w-2 h-2 bg-[#E30613] rounded-full animate-pulse"></span>
                            Filtro activo: Mostrando fotos de un evento específico
                        </span>
                        <button @click="clearPhotoFilters"
                            class="bg-white text-gray-600 px-4 py-2 rounded-full text-[10px] font-bold uppercase tracking-widest hover:text-black hover:shadow-sm border border-gray-200 transition-all flex items-center gap-1.5">
                            <XMarkIcon class="w-3 h-3" /> Limpiar filtro
                        </button>
                    </div>

                    <div v-if="photos.data.length > 0">
                        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4">
                            <div v-for="photo in photos.data" :key="photo.id"
                                @click="router.visit(route('gallery.show', photo.unique_id))" @contextmenu.prevent
                                class="group aspect-square bg-gray-100 rounded overflow-hidden relative cursor-pointer border border-gray-200 hover:border-gray-300 shadow-sm hover:shadow-lg transition-all duration-300">

                                <img :src="photo.watermarked_url" :alt="photo.unique_id" draggable="false"
                                    @contextmenu.prevent
                                    class="w-full h-full object-cover  transition-transform duration-700 select-none pointer-events-none" />

                                <div class="absolute inset-0 bg-gradient-to-t from-black/50 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none"></div>
                                
                                <div class="absolute bottom-3 left-3 bg-white/90 backdrop-blur-sm border border-white/50 text-black font-bold text-[10px] px-2 py-1 rounded shadow-sm opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none uppercase tracking-wider">
                                    ID: {{ photo.unique_id.substring(0,6) }}
                                </div>
                            </div>
                        </div>
                    </div>

                
                    <div v-else class="text-center py-24 bg-white rounded border border-gray-100 shadow-sm flex flex-col items-center">
                        <div class="w-20 h-20 bg-gray-50 rounded-full flex items-center justify-center mb-6">
                            <PhotoIcon class="w-10 h-10 text-gray-300" />
                        </div>
                        <h4 class="text-3xl font-flux text-black mb-2">Archivo vacío</h4>
                        <p class="text-sm text-gray-500">No hay fotografías disponibles para mostrar.</p>
                    </div>
                </div>

            </div>
        </div>
    </AppLayout>
</template>