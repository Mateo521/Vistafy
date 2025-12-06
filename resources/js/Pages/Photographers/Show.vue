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
    CameraIcon, // Para Instagram
    HandThumbUpIcon // Para Facebook
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

            <div class="relative h-64 md:h-80 bg-slate-100 overflow-hidden">
                <img v-if="photographer.banner_photo_url" :src="photographer.banner_photo_url"
                    :alt="photographer.business_name" class="w-full h-full object-cover opacity-90" />
                <div v-else class="w-full h-full flex items-center justify-center bg-slate-50 text-slate-300">
                    <PhotoIcon class="w-24 h-24" />
                </div>
                <div class="absolute inset-0 bg-gradient-to-t from-white via-transparent to-transparent"></div>
            </div>

            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative -mt-16 pb-12 border-b border-gray-100">
                <div class="flex flex-col md:flex-row gap-8 items-start">
                    
                    <div class="w-32 h-32 md:w-40 md:h-40 bg-white p-1 shadow-lg border border-gray-100 flex-shrink-0 rounded-sm">
                        <img v-if="photographer.profile_photo_url" 
                            :src="photographer.profile_photo_url" 
                            :alt="photographer.business_name"
                            class="w-full h-full object-cover rounded-sm" />
                        <div v-else class="w-full h-full bg-slate-900 flex items-center justify-center rounded-sm">
                            <span class="text-3xl font-serif font-bold text-white tracking-widest">
                                {{ getInitials(photographer.business_name) }}
                            </span>
                        </div>
                    </div>

                    <div class="flex-1 pt-4 md:pt-16">
                        <div class="flex flex-col lg:flex-row justify-between items-start gap-8">
                            
                            <div class="flex-1">
                                <h1 class="text-4xl font-serif font-bold text-slate-900 mb-2">
                                    {{ photographer.business_name }}
                                </h1>
                                
                                <div class="flex flex-wrap items-center gap-4 text-xs font-bold uppercase tracking-widest text-slate-500 mb-6">
                                    <span v-if="photographer.region" class="flex items-center">
                                        <MapPinIcon class="w-4 h-4 mr-1" />
                                        {{ photographer.region }}
                                    </span>
                                    <span class="flex items-center text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-sm border border-emerald-100">
                                        Verificado
                                    </span>
                                </div>

                                <p v-if="photographer.bio" class="text-slate-600 font-light max-w-2xl text-sm leading-relaxed mb-8">
                                    {{ photographer.bio }}
                                </p>

                                <div class="flex flex-wrap gap-3 border-t border-gray-100 pt-6">
                                    
                                    <a v-if="photographer.email" :href="`mailto:${photographer.email}`" 
                                        class="flex items-center gap-2 px-4 py-2 border border-slate-200 rounded-sm text-xs font-bold uppercase tracking-wider text-slate-700 hover:bg-slate-900 hover:text-white hover:border-slate-900 transition-colors">
                                        <EnvelopeIcon class="w-4 h-4" />
                                        Email
                                    </a>

                                    <a v-if="photographer.phone" :href="`https://wa.me/${photographer.phone.replace(/[^0-9]/g, '')}`" target="_blank"
                                        class="flex items-center gap-2 px-4 py-2 border border-slate-200 rounded-sm text-xs font-bold uppercase tracking-wider text-slate-700 hover:bg-green-600 hover:text-white hover:border-green-600 transition-colors">
                                        <PhoneIcon class="w-4 h-4" />
                                        WhatsApp
                                    </a>

                                    <div class="flex gap-2 ml-2 pl-2 border-l border-gray-200">
                                        <a v-if="photographer.website" :href="photographer.website" target="_blank" 
                                            class="p-2 text-slate-400 hover:text-slate-900 transition" title="Sitio Web">
                                            <GlobeAltIcon class="w-5 h-5" />
                                        </a>

                                        <a v-if="photographer.instagram" :href="`https://instagram.com/${photographer.instagram}`" target="_blank" 
                                            class="p-2 text-slate-400 hover:text-pink-600 transition" title="Instagram">
                                            <CameraIcon class="w-5 h-5" />
                                        </a>

                                        <a v-if="photographer.facebook" :href="`https://facebook.com/${photographer.facebook}`" target="_blank" 
                                            class="p-2 text-slate-400 hover:text-blue-600 transition" title="Facebook">
                                            <HandThumbUpIcon class="w-5 h-5" />
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <div class="flex lg:flex-col gap-8 lg:border-l lg:border-gray-200 lg:pl-12 lg:min-w-[150px]">
                                <div>
                                    <span class="block text-3xl font-serif font-bold text-slate-900">{{ stats.total_events }}</span>
                                    <span class="text-[10px] uppercase tracking-widest text-slate-400">Eventos</span>
                                </div>
                                <div>
                                    <span class="block text-3xl font-serif font-bold text-slate-900">{{ stats.total_photos }}</span>
                                    <span class="text-[10px] uppercase tracking-widest text-slate-400">Fotos</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-8">
                <div class="border-b border-gray-200 flex gap-8">
                    <button @click="switchTab('events')" 
                        :class="['pb-4 text-xs font-bold uppercase tracking-widest transition-colors border-b-2', 
                        currentTab === 'events' ? 'border-slate-900 text-slate-900' : 'border-transparent text-slate-400 hover:text-slate-600']">
                        Portafolio de Eventos
                    </button>
                    <button @click="switchTab('photos')" 
                        :class="['pb-4 text-xs font-bold uppercase tracking-widest transition-colors border-b-2', 
                        currentTab === 'photos' ? 'border-slate-900 text-slate-900' : 'border-transparent text-slate-400 hover:text-slate-600']">
                        Archivo Fotográfico
                    </button>
                </div>
            </div>

            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
                
                <div v-show="currentTab === 'events'">
                    <div v-if="events.data.length > 0" class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <Link v-for="event in events.data" :key="event.id" :href="route('events.show', event.slug)"
                            class="group flex bg-white border border-gray-100 hover:border-slate-300 transition-all duration-300 rounded-sm overflow-hidden">
                            
                            <div class="w-1/3 relative overflow-hidden bg-gray-100">
                                <img v-if="event.cover_image_url" :src="event.cover_image_url" :alt="event.name"
                                    class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105 filter grayscale-[0.3] group-hover:grayscale-0" />
                                <div v-else class="w-full h-full flex items-center justify-center text-slate-300">
                                    <PhotoIcon class="w-8 h-8" />
                                </div>
                            </div>

                            <div class="w-2/3 p-6 flex flex-col justify-between">
                                <div>
                                    <span class="text-[10px] font-bold uppercase tracking-widest text-slate-400 mb-1 block">
                                        {{ formatDate(event.event_date) }}
                                    </span>
                                    <h3 class="text-xl font-serif font-bold text-slate-900 group-hover:text-slate-600 transition mb-2">
                                        {{ event.name }}
                                    </h3>
                                    <p v-if="event.location" class="text-xs text-slate-500 flex items-center gap-1 mb-3">
                                        <MapPinIcon class="w-3 h-3" /> {{ event.location }}
                                    </p>
                                </div>
                                <div class="flex items-center text-xs font-bold uppercase tracking-wider text-slate-900 group-hover:translate-x-2 transition-transform duration-300">
                                    Ver Galería <ArrowLongRightIcon class="w-4 h-4 ml-2" />
                                </div>
                            </div>
                        </Link>
                    </div>
                    
                    <div v-else class="text-center py-20 border border-dashed border-gray-200 rounded-sm">
                        <CalendarIcon class="w-12 h-12 text-slate-200 mx-auto mb-4" />
                        <p class="text-slate-400 font-light">No hay eventos públicos disponibles.</p>
                    </div>
                </div>

                <div v-show="currentTab === 'photos'">
                    
                    <div v-if="selectedEventId" class="flex justify-between items-center mb-8 p-4 bg-slate-50 border border-gray-200 rounded-sm">
                        <span class="text-xs font-bold uppercase tracking-wide text-slate-600">
                            Filtrando por evento seleccionado
                        </span>
                        <button @click="clearPhotoFilters" class="text-xs font-bold text-slate-900 hover:text-red-600 flex items-center gap-1 transition">
                            <XMarkIcon class="w-4 h-4" /> Limpiar filtro
                        </button>
                    </div>

                    <div v-if="photos.data.length > 0">
                        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-5 gap-4">
                            <Link v-for="photo in photos.data" :key="photo.id" :href="route('gallery.show', photo.unique_id)"
                                class="group aspect-square bg-gray-100 overflow-hidden relative border border-gray-100 rounded-sm">
                                <img :src="photo.thumbnail_url" :alt="photo.unique_id"
                                    class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105 filter grayscale-[0.2] group-hover:grayscale-0" />
                                
                                <div class="absolute inset-0 bg-slate-900/0 group-hover:bg-slate-900/20 transition-colors duration-300"></div>
                            </Link>
                        </div>
                    </div>

                    <div v-else class="text-center py-20 border border-dashed border-gray-200 rounded-sm">
                        <PhotoIcon class="w-12 h-12 text-slate-200 mx-auto mb-4" />
                        <p class="text-slate-400 font-light">No hay fotografías disponibles.</p>
                    </div>
                </div>

            </div>
        </div>
    </AppLayout>
</template>