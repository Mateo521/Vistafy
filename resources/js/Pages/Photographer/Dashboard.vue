<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    photographer: Object,
    stats: Object,
    recentPhotos: Array,
    recentEvents: Array,
});

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString('es-ES', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
};
</script>

<template>
    <Head title="Dashboard Fotógrafo" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Dashboard - {{ photographer.business_name || photographer.user.name }}
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                
                <!-- Stats Cards -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                    <!-- Total Photos -->
                    <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl shadow-lg p-6 text-white">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-blue-100 text-sm font-medium mb-1">Total Fotos</p>
                                <p class="text-3xl font-bold">{{ stats.total_photos }}</p>
                            </div>
                            <div class="bg-white/20 rounded-full p-3">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- Active Photos -->
                    <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-xl shadow-lg p-6 text-white">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-green-100 text-sm font-medium mb-1">Fotos Activas</p>
                                <p class="text-3xl font-bold">{{ stats.active_photos }}</p>
                            </div>
                            <div class="bg-white/20 rounded-full p-3">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- Total Downloads -->
                    <div class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl shadow-lg p-6 text-white">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-purple-100 text-sm font-medium mb-1">Descargas</p>
                                <p class="text-3xl font-bold">{{ stats.total_downloads }}</p>
                            </div>
                            <div class="bg-white/20 rounded-full p-3">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- Total Events -->
                    <div class="bg-gradient-to-br from-orange-500 to-orange-600 rounded-xl shadow-lg p-6 text-white">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-orange-100 text-sm font-medium mb-1">Eventos</p>
                                <p class="text-3xl font-bold">{{ stats.total_events }}</p>
                            </div>
                            <div class="bg-white/20 rounded-full p-3">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Eventos Destacados - CON THUMBNAILS -->
                <div class="mb-8">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-2xl font-bold text-gray-900"> Eventos Destacados</h3>
                        <Link 
                            :href="route('photographer.events.index')"
                            class="text-indigo-600 hover:text-indigo-800 font-semibold flex items-center gap-2"
                        >
                            Ver todos
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </Link>
                    </div>

                    <div v-if="recentEvents && recentEvents.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <Link
                            v-for="event in recentEvents"
                            :key="event.id"
                            :href="route('photographer.events.show', event.id)"
                            class="bg-white rounded-xl shadow-lg overflow-hidden hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-1"
                        >
                            <!-- Thumbnail Image -->
                            <div class="relative h-48 overflow-hidden">
                                <!-- Cover Image si existe -->
                                <img
                                    v-if="event.cover_image"
                                    :src="`/storage/${event.cover_image}`"
                                    :alt="event.name"
                                    class="w-full h-full object-cover transition-transform duration-300 hover:scale-110"
                                    @error="(e) => e.target.style.display = 'none'"
                                />
                                
                                <!-- Gradient overlay si no hay imagen -->
                                <div 
                                    v-else
                                    class="w-full h-full bg-gradient-to-br from-indigo-500 via-purple-500 to-pink-500"
                                >
                                    <div class="flex items-center justify-center h-full">
                                        <svg class="w-20 h-20 text-white/50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                        </svg>
                                    </div>
                                </div>

                                <!-- Badge de estado -->
                                <div class="absolute top-3 right-3">
                                    <span
                                        :class="event.is_private ? 'bg-red-500' : 'bg-green-500'"
                                        class="px-3 py-1 rounded-full text-white text-xs font-bold shadow-lg"
                                    >
                                        {{ event.is_private ? ' Privado' : ' Público' }}
                                    </span>
                                </div>

                                <!-- Cantidad de fotos -->
                                <div class="absolute bottom-3 left-3 bg-white/95 backdrop-blur-sm px-4 py-2 rounded-full shadow-lg">
                                    <div class="flex items-center gap-2">
                                        <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                        </svg>
                                        <span class="text-sm font-bold text-gray-800">
                                            {{ event.photos_count || 0 }} fotos
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <!-- Event Info -->
                            <div class="p-6">
                                <h4 class="text-xl font-bold text-gray-900 mb-2 line-clamp-1">
                                    {{ event.name }}
                                </h4>
                                <p v-if="event.description" class="text-gray-600 text-sm mb-3 line-clamp-2">
                                    {{ event.description }}
                                </p>
                                <div class="flex items-center text-sm text-gray-500">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                    {{ formatDate(event.event_date) }}
                                </div>
                                <div v-if="event.location" class="flex items-center text-sm text-gray-500 mt-2">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                    {{ event.location }}
                                </div>
                            </div>

                            <!-- Hover indicator -->
                            <div class="px-6 pb-6">
                                <div class="flex items-center justify-center text-indigo-600 font-semibold text-sm">
                                    Ver detalles →
                                </div>
                            </div>
                        </Link>
                    </div>

                    <!-- Empty State -->
                    <div v-else class="bg-white rounded-xl shadow-lg p-12 text-center">
                        <div class="text-6xl mb-4"></div>
                        <h4 class="text-xl font-semibold text-gray-900 mb-2">No hay eventos aún</h4>
                        <p class="text-gray-600 mb-6">Crea tu primer evento para organizar tus fotos</p>
                        <Link
                            :href="route('photographer.events.create')"
                            class="inline-block bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-3 rounded-lg font-semibold transition"
                        >
                            + Crear Evento
                        </Link>
                    </div>
                </div>

                <!-- Fotos Recientes -->
                <div>
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-2xl font-bold text-gray-900"> Fotos Recientes</h3>
                        <Link 
                            :href="route('photographer.photos.index')"
                            class="text-indigo-600 hover:text-indigo-800 font-semibold flex items-center gap-2"
                        >
                            Ver todas
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </Link>
                    </div>

                    <div v-if="recentPhotos && recentPhotos.length > 0" class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                        <div
                            v-for="photo in recentPhotos"
                            :key="photo.id"
                            class="aspect-square bg-gray-200 rounded-lg overflow-hidden shadow hover:shadow-xl transition group relative"
                        >
                            <img
                                :src="`/storage/${photo.thumbnail_path || photo.file_path}`"
                                :alt="photo.unique_id"
                                class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-300"
                                @error="(e) => e.target.src = 'https://via.placeholder.com/400x400?text=Foto'"
                            />
                            <div class="absolute inset-0 bg-black/0 group-hover:bg-black/40 transition-all duration-300 flex items-center justify-center">
                                <Link
                                    :href="route('photographer.photos.show', photo.id)"
                                    class="opacity-0 group-hover:opacity-100 bg-white text-gray-900 px-4 py-2 rounded-lg font-semibold text-sm transition-all transform translate-y-2 group-hover:translate-y-0"
                                >
                                    Ver detalles
                                </Link>
                            </div>
                        </div>
                    </div>

                    <!-- Empty State -->
                    <div v-else class="bg-white rounded-xl shadow-lg p-12 text-center">
                        <div class="text-6xl mb-4"></div>
                        <h4 class="text-xl font-semibold text-gray-900 mb-2">No hay fotos aún</h4>
                        <p class="text-gray-600 mb-6">Sube tus primeras fotos para comenzar</p>
                        <Link
                            :href="route('photographer.photos.create')"
                            class="inline-block bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-3 rounded-lg font-semibold transition"
                        >
                            + Subir Fotos
                        </Link>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
