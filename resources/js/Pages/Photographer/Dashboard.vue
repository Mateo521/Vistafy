<template>
    <PhotographerLayout>
        <!-- Banner de Video Promocional -->
        <div class="relative bg-gradient-to-r from-indigo-600 to-purple-600 overflow-hidden">
            <div class="absolute inset-0 bg-black opacity-40"></div>
            
            <!-- Video de fondo (opcional) -->
            <div class="absolute inset-0">
                <video 
                    v-if="hasPromoVideo"
                    autoplay 
                    muted 
                    loop 
                    playsinline
                    class="w-full h-full object-cover opacity-30"
                >
                    <source src="/videos/promo.mp4" type="video/mp4">
                </video>
            </div>

            <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20">
                <div class="text-center text-white">
                    <h1 class="text-4xl md:text-6xl font-bold mb-4">
                        ¡Bienvenido, {{ photographer.business_name }}!
                    </h1>
                    <p class="text-xl md:text-2xl mb-8 opacity-90">
                        Gestiona tus fotos, eventos y ventas en un solo lugar
                    </p>
                    <div class="flex flex-wrap justify-center gap-4">
                        <Link 
                            :href="route('photographer.photos.create')"
                            class="bg-white text-indigo-600 px-8 py-3 rounded-lg font-bold hover:bg-gray-100 transition transform hover:scale-105"
                        >
                            <svg class="inline w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            Subir Fotos
                        </Link>
                        <Link 
                            :href="route('photographer.events.create')"
                            class="bg-purple-700 text-white px-8 py-3 rounded-lg font-bold hover:bg-purple-800 transition"
                        >
                            <svg class="inline w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            Crear Evento
                        </Link>
                    </div>
                </div>
            </div>
        </div>

        <!-- Estadísticas -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 -mt-10 relative z-10">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-12">
                <!-- Total Fotos -->
                <div class="bg-white rounded-lg shadow-lg p-6 transform hover:scale-105 transition">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-indigo-100 rounded-lg p-3">
                            <svg class="w-8 h-8 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm text-gray-500 font-medium">Total Fotos</p>
                            <p class="text-3xl font-bold text-gray-900">{{ stats.total_photos }}</p>
                        </div>
                    </div>
                </div>

                <!-- Fotos Activas -->
                <div class="bg-white rounded-lg shadow-lg p-6 transform hover:scale-105 transition">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-green-100 rounded-lg p-3">
                            <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm text-gray-500 font-medium">Fotos Activas</p>
                            <p class="text-3xl font-bold text-gray-900">{{ stats.active_photos }}</p>
                        </div>
                    </div>
                </div>

                <!-- Total Descargas -->
                <div class="bg-white rounded-lg shadow-lg p-6 transform hover:scale-105 transition">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-purple-100 rounded-lg p-3">
                            <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10" />
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm text-gray-500 font-medium">Descargas</p>
                            <p class="text-3xl font-bold text-gray-900">{{ stats.total_downloads }}</p>
                        </div>
                    </div>
                </div>

                <!-- Total Eventos -->
                <div class="bg-white rounded-lg shadow-lg p-6 transform hover:scale-105 transition">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-yellow-100 rounded-lg p-3">
                            <svg class="w-8 h-8 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm text-gray-500 font-medium">Eventos</p>
                            <p class="text-3xl font-bold text-gray-900">{{ stats.total_events }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Contenido Principal -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-12">
            <!-- Fotos Recientes -->
            <div class="mb-12">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-2xl font-bold text-gray-900">Fotos Recientes</h2>
                    <Link 
                        :href="route('photographer.photos.index')"
                        class="text-indigo-600 hover:text-indigo-700 font-semibold"
                    >
                        Ver todas →
                    </Link>
                </div>

                <div v-if="recentPhotos.length > 0" class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
                    <div 
                        v-for="photo in recentPhotos" 
                        :key="photo.id"
                        class="group relative aspect-square rounded-lg overflow-hidden shadow-md hover:shadow-xl transition"
                    >
                        <img 
                            :src="photo.thumbnail_url" 
                            :alt="photo.unique_id"
                            class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-300"
                        >
                        <div class="absolute inset-0 bg-gradient-to-t from-black via-transparent to-transparent opacity-0 group-hover:opacity-100 transition">
                            <div class="absolute bottom-0 left-0 right-0 p-3">
                                <p class="text-white text-xs font-mono">{{ photo.unique_id }}</p>
                                <p class="text-white text-sm font-bold">${{ photo.price }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-else class="text-center py-12 bg-gray-50 rounded-lg">
                    <svg class="mx-auto h-16 w-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    <h3 class="mt-4 text-lg font-semibold text-gray-900">No tienes fotos aún</h3>
                    <p class="mt-2 text-gray-600 mb-4">Comienza subiendo tu primera fotografía</p>
                    <Link 
                        :href="route('photographer.photos.create')"
                        class="inline-flex items-center px-6 py-3 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition"
                    >
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Subir Primera Foto
                    </Link>
                </div>
            </div>

            <!-- Eventos Recientes -->
            <div>
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-2xl font-bold text-gray-900">Mis Eventos</h2>
                    <Link 
                        :href="route('photographer.events.index')"
                        class="text-indigo-600 hover:text-indigo-700 font-semibold"
                    >
                        Ver todos →
                    </Link>
                </div>

                <div v-if="recentEvents.length > 0" class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div 
                        v-for="event in recentEvents" 
                        :key="event.id"
                        class="bg-white rounded-lg shadow-md hover:shadow-xl transition overflow-hidden"
                    >
                        <div class="h-40 bg-gradient-to-br from-purple-400 to-indigo-500 flex items-center justify-center text-white">
                            <div class="text-center">
                                <svg class="w-12 h-12 mx-auto mb-2 opacity-75" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                <p class="text-lg font-bold">{{ event.photos_count }} fotos</p>
                            </div>
                        </div>
                        <div class="p-4">
                            <h3 class="font-bold text-gray-900 mb-2 truncate">{{ event.name }}</h3>
                            <div class="flex items-center text-sm text-gray-500 mb-3">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                {{ event.event_date ? formatDate(event.event_date) : 'Sin fecha' }}
                            </div>
                            <div class="flex gap-2">
                                <Link 
                                    :href="route('photographer.events.show', event.id)"
                                    class="flex-1 text-center bg-indigo-600 text-white py-2 rounded hover:bg-indigo-700 transition text-sm font-medium"
                                >
                                    Gestionar
                                </Link>
                                <Link 
                                    v-if="!event.is_private"
                                    :href="route('events.show', event.slug)"
                                    target="_blank"
                                    class="flex-1 text-center border border-gray-300 text-gray-700 py-2 rounded hover:bg-gray-50 transition text-sm font-medium"
                                >
                                    Ver Público
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-else class="text-center py-12 bg-gray-50 rounded-lg">
                    <svg class="mx-auto h-16 w-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    <h3 class="mt-4 text-lg font-semibold text-gray-900">No tienes eventos aún</h3>
                    <p class="mt-2 text-gray-600 mb-4">Crea tu primer evento para organizar tus fotos</p>
                    <Link 
                        :href="route('photographer.events.create')"
                        class="inline-flex items-center px-6 py-3 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition"
                    >
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Crear Primer Evento
                    </Link>
                </div>
            </div>
        </div>
    </PhotographerLayout>
</template>

<script setup>
import PhotographerLayout from '@/Layouts/PhotographerLayout.vue';
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    photographer: Object,
    stats: Object,
    recentPhotos: Array,
    recentEvents: Array,
});

const hasPromoVideo = computed(() => {
    // Verificar si existe el video promocional
    return true; // Cambiar a true cuando tengas el video
});

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('es-ES', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
};
</script>
