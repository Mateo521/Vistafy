<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    event: Object,
    photos: Object,
    photographers: Array,
});

const selectedPhotographer = ref('all');

const filterForm = useForm({
    photographer_id: '',
});

const filterByPhotographer = () => {
    if (selectedPhotographer.value === 'all') {
        filterForm.photographer_id = '';
    } else {
        filterForm.photographer_id = selectedPhotographer.value;
    }

    filterForm.get(route('events.show', props.event.slug), {
        preserveState: true,
        preserveScroll: true,
    });
};
</script>

<template>
    <Head :title="event.name" />

    <AppLayout>
        <div class="min-h-screen bg-gray-50">
            <!-- Hero Banner -->
            <div class="relative h-96 bg-gradient-to-br from-purple-900 via-indigo-900 to-pink-900 overflow-hidden">
                <img 
                    v-if="event.cover_image_url"
                    :src="event.cover_image_url" 
                    :alt="event.name" 
                    class="absolute inset-0 w-full h-full object-cover opacity-40"
                    @error="(e) => e.target.style.display = 'none'"
                />
                <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/40 to-transparent"></div>

                <!-- Event Info Overlay -->
                <div class="absolute bottom-0 left-0 right-0 p-8">
                    <div class="max-w-7xl mx-auto">
                        <Link 
                            :href="route('events.index')"
                            class="inline-flex items-center text-white/80 hover:text-white mb-4 transition"
                        >
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                            </svg>
                            Volver a eventos
                        </Link>
                        <h1 class="text-5xl font-bold text-white mb-4 drop-shadow-lg">{{ event.name }}</h1>
                        <div class="flex flex-wrap items-center gap-6 text-white/90">
                            <div class="flex items-center">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                {{ new Date(event.event_date).toLocaleDateString('es-ES', { day: 'numeric', month: 'long', year: 'numeric' }) }}
                            </div>
                            <div v-if="event.location" class="flex items-center">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                {{ event.location }}
                            </div>
                            <div class="flex items-center">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                {{ event.photos_count }} fotos
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
                <!-- Description -->
                <div v-if="event.description" class="bg-white rounded-xl shadow-sm p-8 mb-8 border border-gray-100">
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">Sobre el Evento</h2>
                    <p class="text-gray-700 leading-relaxed">{{ event.description }}</p>
                    <p v-if="event.long_description" class="text-gray-700 leading-relaxed mt-4">{{ event.long_description }}</p>
                </div>

                <!-- Stats -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                    <div class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl shadow-lg p-6 text-white">
                        <div class="text-4xl font-bold mb-2">{{ event.photos_count }}</div>
                        <div class="text-purple-100">Fotos Disponibles</div>
                    </div>
                    <div class="bg-gradient-to-br from-indigo-500 to-indigo-600 rounded-xl shadow-lg p-6 text-white">
                        <div class="text-4xl font-bold mb-2">{{ photographers.length }}</div>
                        <div class="text-indigo-100">Fotógrafos</div>
                    </div>
                    <div class="bg-gradient-to-br from-pink-500 to-pink-600 rounded-xl shadow-lg p-6 text-white">
                        <div class="text-4xl font-bold mb-2">{{ event.downloads || 0 }}</div>
                        <div class="text-pink-100">Descargas</div>
                    </div>
                </div>

                <!-- Photographers Filter -->
                <div v-if="photographers && photographers.length > 1" class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-8">
                    <h3 class="text-lg font-bold text-gray-900 mb-4">Filtrar por Fotógrafo</h3>
                    <div class="flex flex-wrap gap-3">
                        <button 
                            @click="selectedPhotographer = 'all'; filterByPhotographer()"
                            :class="selectedPhotographer === 'all' ? 'bg-purple-600 text-white shadow-lg' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'"
                            class="px-4 py-2 rounded-lg font-medium transition"
                        >
                            Todos ({{ event.photos_count }})
                        </button>
                        <button 
                            v-for="photographer in photographers" 
                            :key="photographer.id"
                            @click="selectedPhotographer = photographer.id; filterByPhotographer()"
                            :class="selectedPhotographer === photographer.id ? 'bg-purple-600 text-white shadow-lg' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'"
                            class="px-4 py-2 rounded-lg font-medium transition"
                        >
                            {{ photographer.user.name }} ({{ photographer.photos_count }})
                        </button>
                    </div>
                </div>

                <!-- Contact Organizer -->
                <!--div v-if="event.photographer" class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-8">
                    <h3 class="text-lg font-bold text-gray-900 mb-4">Organizador del Evento</h3>
                    <div class="flex items-start gap-4">
                        <div class="w-16 h-16 bg-gradient-to-br from-purple-100 to-indigo-100 rounded-full flex items-center justify-center flex-shrink-0">
                            <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h4 class="font-bold text-gray-900 text-lg">{{ event.photographer.business_name }}</h4>
                            <p v-if="event.photographer.region" class="text-sm text-gray-600">{{ event.photographer.region }}</p>
                            <div class="mt-3 flex flex-wrap gap-4">
                                <a 
                                    v-if="event.photographer.phone" 
                                    :href="`tel:${event.photographer.phone}`"
                                    class="flex items-center text-sm text-purple-600 hover:text-purple-800 transition"
                                >
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                    </svg>
                                    {{ event.photographer.phone }}
                                </a>
                                <a 
                                    v-if="event.photographer.email" 
                                    :href="`mailto:${event.photographer.email}`"
                                    class="flex items-center text-sm text-purple-600 hover:text-purple-800 transition"
                                >
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                    </svg>
                                    {{ event.photographer.email }}
                                </a>
                            </div>
                        </div>
                    </div>
                </div-->

                <!-- Photos Grid -->
                <div>
                    <h2 class="text-2xl font-bold text-gray-900 mb-6">
                        Fotos del Evento
                        <span class="text-gray-500 text-lg font-normal ml-2">({{ photos.total }})</span>
                    </h2>

                    <div 
                        v-if="photos.data && photos.data.length > 0"
                        class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4 mb-8"
                    >
                        <Link 
                            v-for="photo in photos.data" 
                            :key="photo.id"
                            :href="route('gallery.show', photo.unique_id)"
                            class="group bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1"
                        >
                            <!-- Image -->
                            <div class="aspect-square overflow-hidden bg-gray-100 relative">
                                <img 
                                    :src="photo.thumbnail_url" 
                                    :alt="photo.title || photo.unique_id"
                                    class="w-full h-full object-cover group-hover:scale-110 transition duration-500"
                                    @error="(e) => e.target.src = 'https://via.placeholder.com/400?text=Sin+Imagen'"
                                />
                                <!-- Price Badge -->
                                <div class="absolute top-2 right-2 bg-white/95 backdrop-blur px-2 py-1 rounded-full text-xs font-bold text-purple-600 shadow-lg">
                                    ${{ photo.price }}
                                </div>
                                <!-- Photographer Badge -->
                                <div 
                                    v-if="photographers.length > 1 && photo.photographer"
                                    class="absolute bottom-2 left-2 bg-black/70 backdrop-blur px-2 py-1 rounded text-xs text-white"
                                >
                                    {{ photo.photographer.business_name }}
                                </div>
                            </div>

                            <!-- Info -->
                            <div class="p-3">
                                <div class="text-sm font-bold text-gray-900 text-center truncate">
                                    {{ photo.title || photo.unique_id }}
                                </div>
                                <div class="text-xs text-gray-500 text-center mt-1">
                                    ID: {{ photo.unique_id }}
                                </div>
                            </div>
                        </Link>
                    </div>

                    <!-- Empty State -->
                    <div v-else class="bg-white rounded-xl shadow-sm border border-gray-100 p-16 text-center">
                        <svg class="w-20 h-20 text-gray-400 mx-auto mb-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        <h3 class="text-2xl font-semibold text-gray-900 mb-3">No hay fotos disponibles</h3>
                        <p class="text-gray-600 mb-6">
                            <template v-if="selectedPhotographer !== 'all'">
                                Este fotógrafo no tiene fotos en este evento
                            </template>
                            <template v-else>
                                Este evento aún no tiene fotos publicadas
                            </template>
                        </p>
                        <button 
                            v-if="selectedPhotographer !== 'all'"
                            @click="selectedPhotographer = 'all'; filterByPhotographer()"
                            class="bg-purple-600 hover:bg-purple-700 text-white px-6 py-3 rounded-lg font-semibold inline-block transition shadow-lg hover:shadow-xl"
                        >
                            Ver todas las fotos
                        </button>
                    </div>

                    <!-- Pagination -->
                    <div v-if="photos.data && photos.data.length > 0 && photos.last_page > 1" class="mt-8">
                        <div class="flex items-center justify-center gap-2 flex-wrap">
                            <template v-for="(link, index) in photos.links" :key="index">
                                <Link 
                                    v-if="link.url" 
                                    :href="link.url" 
                                    v-html="link.label"
                                    :class="[
                                        'px-4 py-2 rounded-lg font-medium transition',
                                        link.active
                                            ? 'bg-purple-600 text-white shadow-lg'
                                            : 'bg-white text-gray-700 hover:bg-gray-100 shadow border border-gray-200'
                                    ]"
                                />
                                <span 
                                    v-else 
                                    v-html="link.label"
                                    class="px-4 py-2 rounded-lg font-medium bg-gray-100 text-gray-400 cursor-not-allowed"
                                />
                            </template>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
