<script setup>
import { ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import {
    MapPinIcon,
    PhoneIcon,
    EnvelopeIcon,
    GlobeAltIcon,
    CalendarIcon,
    PhotoIcon,
    MagnifyingGlassIcon,
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
const eventSearch = ref(props.filters.event_search || '');
const selectedEventId = ref(props.filters.event_id || '');

const switchTab = (tab) => {
    currentTab.value = tab;
    router.get(route('photographers.show', props.photographer.slug), {
        tab: tab,
        event_search: eventSearch.value,
        event_id: selectedEventId.value,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
};

const filterByEvent = (eventId) => {
    selectedEventId.value = eventId;
    router.get(route('photographers.show', props.photographer.slug), {
        tab: 'photos',
        event_id: eventId,
    }, {
        preserveState: true,
    });
};

const clearPhotoFilters = () => {
    selectedEventId.value = '';
    router.get(route('photographers.show', props.photographer.slug), {
        tab: 'photos',
    });
};

const getInitials = (name) => {
    return name
        .split(' ')
        .map(word => word[0])
        .join('')
        .toUpperCase()
        .substring(0, 2);
};
</script>

<template>
    <AppLayout>
        <Head :title="photographer.business_name" />

        <div class="min-h-screen bg-gray-50">
            
            <!-- Cover Photo -->
            <div class="relative h-64 md:h-80 bg-gradient-to-br from-purple-500 via-pink-500 to-orange-400">
                <img
                    v-if="photographer.cover_photo_url"
                    :src="photographer.cover_photo_url"
                    :alt="photographer.business_name"
                    class="w-full h-full object-cover"
                />
                <div class="absolute inset-0 bg-black/20"></div>
            </div>

            <!-- Profile Section -->
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="bg-white rounded-2xl shadow-xl -mt-20 relative z-10 p-6 md:p-8">
                    
                    <div class="flex flex-col md:flex-row items-center md:items-start gap-6">
                        
                        <!-- Avatar -->
                        <div class="flex-shrink-0">
                            <div class="w-32 h-32 md:w-40 md:h-40 rounded-full bg-white p-2 shadow-2xl">
                                <div v-if="photographer.profile_photo_url" class="w-full h-full rounded-full overflow-hidden">
                                    <img
                                        :src="photographer.profile_photo_url"
                                        :alt="photographer.business_name"
                                        class="w-full h-full object-cover"
                                    />
                                </div>
                                <div v-else class="w-full h-full rounded-full bg-gradient-to-br from-purple-400 to-pink-400 flex items-center justify-center">
                                    <span class="text-4xl font-bold text-white">
                                        {{ getInitials(photographer.business_name) }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Info -->
                        <div class="flex-1 text-center md:text-left">
                            <h1 class="text-3xl md:text-4xl font-bold text-gray-900 mb-2">
                                {{ photographer.business_name }}
                            </h1>
                            
                            <!-- Stats Row -->
                            <div class="flex items-center justify-center md:justify-start gap-6 mb-4">
                                <div>
                                    <span class="text-2xl font-bold text-gray-900">{{ stats.total_events }}</span>
                                    <span class="text-sm text-gray-600 ml-1">Eventos</span>
                                </div>
                                <div>
                                    <span class="text-2xl font-bold text-gray-900">{{ stats.total_photos }}</span>
                                    <span class="text-sm text-gray-600 ml-1">Fotos</span>
                                </div>
                            </div>

                            <!-- Bio -->
                            <p v-if="photographer.bio" class="text-gray-700 mb-4 max-w-2xl">
                                {{ photographer.bio }}
                            </p>

                            <!-- Contact Info -->
                            <div class="flex flex-wrap items-center justify-center md:justify-start gap-4 text-sm text-gray-600">
                                <div v-if="photographer.region" class="flex items-center gap-1">
                                    <MapPinIcon class="h-4 w-4" />
                                    <span>{{ photographer.region }}</span>
                                </div>
                                <div v-if="photographer.phone" class="flex items-center gap-1">
                                    <PhoneIcon class="h-4 w-4" />
                                    <a :href="`tel:${photographer.phone}`" class="hover:text-purple-600">
                                        {{ photographer.phone }}
                                    </a>
                                </div>
                                <div v-if="photographer.user.email" class="flex items-center gap-1">
                                    <EnvelopeIcon class="h-4 w-4" />
                                    <a :href="`mailto:${photographer.user.email}`" class="hover:text-purple-600">
                                        Contactar
                                    </a>
                                </div>
                            </div>

                            <!-- Social Links -->
                            <div v-if="photographer.website || photographer.instagram || photographer.facebook" class="flex items-center justify-center md:justify-start gap-3 mt-4">
                                <a
                                    v-if="photographer.website"
                                    :href="photographer.website"
                                    target="_blank"
                                    class="p-2 bg-gray-100 hover:bg-purple-100 rounded-full transition"
                                >
                                    <GlobeAltIcon class="h-5 w-5 text-gray-700" />
                                </a>
                                <a
                                    v-if="photographer.instagram"
                                    :href="`https://instagram.com/${photographer.instagram}`"
                                    target="_blank"
                                    class="p-2 bg-gray-100 hover:bg-pink-100 rounded-full transition"
                                >
                                    <svg class="h-5 w-5 text-pink-600" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/>
                                    </svg>
                                </a>
                                <a
                                    v-if="photographer.facebook"
                                    :href="`https://facebook.com/${photographer.facebook}`"
                                    target="_blank"
                                    class="p-2 bg-gray-100 hover:bg-blue-100 rounded-full transition"
                                >
                                    <svg class="h-5 w-5 text-blue-600" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                                    </svg>
                                </a>
                            </div>
                        </div>

                    </div>

                    <!-- Tabs Navigation -->
                    <div class="mt-8 border-t border-gray-200 pt-6">
                        <nav class="flex gap-8 justify-center md:justify-start">
                            <button
                                @click="switchTab('events')"
                                :class="[
                                    'flex items-center gap-2 pb-2 border-b-2 font-semibold transition',
                                    currentTab === 'events'
                                        ? 'border-purple-600 text-purple-600'
                                        : 'border-transparent text-gray-600 hover:text-gray-900 hover:border-gray-300'
                                ]"
                            >
                                <CalendarIcon class="h-5 w-5" />
                                <span>Eventos</span>
                                <span class="px-2 py-0.5 bg-gray-100 rounded-full text-xs">
                                    {{ stats.public_events }}
                                </span>
                            </button>
                            <button
                                @click="switchTab('photos')"
                                :class="[
                                    'flex items-center gap-2 pb-2 border-b-2 font-semibold transition',
                                    currentTab === 'photos'
                                        ? 'border-purple-600 text-purple-600'
                                        : 'border-transparent text-gray-600 hover:text-gray-900 hover:border-gray-300'
                                ]"
                            >
                                <PhotoIcon class="h-5 w-5" />
                                <span>Fotos</span>
                                <span class="px-2 py-0.5 bg-gray-100 rounded-full text-xs">
                                    {{ stats.total_photos }}
                                </span>
                            </button>
                        </nav>
                    </div>
                </div>

                <!-- Content Tabs -->
                <div class="mt-8 pb-12">
                    
                    <!-- Tab: Eventos -->
                    <div v-show="currentTab === 'events'">
                        <div v-if="events.data.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            <Link
                                v-for="event in events.data"
                                :key="event.id"
                                :href="route('events.show', event.slug)"
                                class="group bg-white rounded-xl shadow-md hover:shadow-2xl transition-all duration-300 overflow-hidden"
                            >
                                <div class="aspect-video bg-gradient-to-br from-purple-400 to-pink-400 relative overflow-hidden">
                                    <img
                                        v-if="event.cover_image_url"
                                        :src="event.cover_image_url"
                                        :alt="event.name"
                                        class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-300"
                                    />
                                    <div class="absolute top-3 right-3 bg-white/90 backdrop-blur-sm px-3 py-1 rounded-full text-sm font-semibold text-gray-700">
                                        {{ event.photos_count }} fotos
                                    </div>
                                </div>
                                <div class="p-4">
                                    <h3 class="font-bold text-lg text-gray-900 group-hover:text-purple-600 transition mb-2">
                                        {{ event.name }}
                                    </h3>
                                    <div class="flex items-center gap-4 text-sm text-gray-600">
                                        <div class="flex items-center gap-1">
                                            <CalendarIcon class="h-4 w-4" />
                                            <span>{{ new Date(event.event_date).toLocaleDateString('es-ES') }}</span>
                                        </div>
                                        <div v-if="event.location" class="flex items-center gap-1">
                                            <MapPinIcon class="h-4 w-4" />
                                            <span class="truncate">{{ event.location }}</span>
                                        </div>
                                    </div>
                                </div>
                            </Link>
                        </div>
                        <div v-else class="text-center py-16 bg-white rounded-xl">
                            <CalendarIcon class="mx-auto h-16 w-16 text-gray-300 mb-4" />
                            <p class="text-gray-600">No hay eventos disponibles</p>
                        </div>

                        <!-- Paginación Eventos -->
                        <div v-if="events.last_page > 1" class="flex justify-center gap-2 mt-8">
                            <Link
                                v-for="link in events.links"
                                :key="link.label"
                                :href="link.url"
                                :class="[
                                    'px-4 py-2 rounded-lg font-medium transition',
                                    link.active
                                        ? 'bg-purple-600 text-white'
                                        : 'bg-white text-gray-700 hover:bg-gray-100',
                                    !link.url && 'opacity-50 cursor-not-allowed'
                                ]"
                                v-html="link.label"
                            />
                        </div>
                    </div>

                    <!-- Tab: Fotos (Grid estilo Instagram) -->
                    <div v-show="currentTab === 'photos'">
                        
                        <!-- Filtro -->
                        <div v-if="selectedEventId" class="bg-white rounded-xl p-4 mb-6 flex items-center justify-between">
                            <span class="text-sm text-gray-600">
                                Filtrando por evento específico
                            </span>
                            <button
                                @click="clearPhotoFilters"
                                class="text-sm text-purple-600 hover:text-purple-700 font-semibold"
                            >
                                Ver todas las fotos
                            </button>
                        </div>

                        <div v-if="photos.data.length > 0" class="grid grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-1 md:gap-2">
                            <Link
                                v-for="photo in photos.data"
                                :key="photo.id"
                                :href="route('gallery.show', photo.unique_id)"
                                class="group aspect-square bg-gray-200 overflow-hidden relative"
                            >
                                <img
                                    :src="photo.thumbnail_url"
                                    :alt="photo.unique_id"
                                    class="w-full h-full object-cover group-hover:opacity-75 transition"
                                />
                                <div class="absolute inset-0 bg-black/0 group-hover:bg-black/30 transition flex items-center justify-center opacity-0 group-hover:opacity-100">
                                    <PhotoIcon class="h-8 w-8 text-white" />
                                </div>
                            </Link>
                        </div>
                        <div v-else class="text-center py-16 bg-white rounded-xl">
                            <PhotoIcon class="mx-auto h-16 w-16 text-gray-300 mb-4" />
                            <p class="text-gray-600">No hay fotos disponibles</p>
                        </div>

                        <!-- Paginación Fotos -->
                        <div v-if="photos.last_page > 1" class="flex justify-center gap-2 mt-8">
                            <Link
                                v-for="link in photos.links"
                                :key="link.label"
                                :href="link.url"
                                :class="[
                                    'px-4 py-2 rounded-lg font-medium transition',
                                    link.active
                                        ? 'bg-purple-600 text-white'
                                        : 'bg-white text-gray-700 hover:bg-gray-100',
                                    !link.url && 'opacity-50 cursor-not-allowed'
                                ]"
                                v-html="link.label"
                            />
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </AppLayout>
</template>
