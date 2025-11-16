<script setup>
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    canLogin: {
        type: Boolean,
        default: false
    },
    canRegister: {
        type: Boolean,
        default: false
    },
    recentEvents: {
        type: Array,
        default: () => []
    },
    recentPhotos: {
        type: Array,
        default: () => []
    },
    stats: {
        type: Object,
        default: () => ({})
    }
});

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString('es-ES', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
};

const handleImageError = (e) => {
    e.target.style.display = 'none';
    const parent = e.target.parentElement;
    parent.innerHTML = `
        <div class="w-full h-full bg-gradient-to-br from-purple-400 via-indigo-500 to-pink-500 flex items-center justify-center">
            <svg class="w-20 h-20 text-white/50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
        </div>
    `;
};
</script>

<template>
    <Head title="Inicio - PixelSpot" />

    <div class="min-h-screen bg-gradient-to-br from-purple-50 via-white to-indigo-50">
        
        <!-- Navigation -->
        <nav class="bg-white/80 backdrop-blur-md shadow-sm sticky top-0 z-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center h-16">
                    <!-- Logo -->
                    <Link href="/" class="flex items-center space-x-2">
                        <div class="w-10 h-10 bg-gradient-to-br from-purple-600 to-indigo-600 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                    d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <span class="text-2xl font-bold bg-gradient-to-r from-purple-600 to-indigo-600 bg-clip-text text-transparent">
                            PixelSpot
                        </span>
                    </Link>

                    <!-- Auth Links -->
                    <div class="flex items-center space-x-4">
                        <Link :href="route('events.index')" 
                            class="text-gray-700 hover:text-purple-600 font-medium transition">
                            Eventos
                        </Link>
                        
                        <template v-if="canLogin">
                            <Link :href="route('login')" 
                                class="text-gray-700 hover:text-purple-600 font-medium transition">
                                Iniciar Sesión
                            </Link>
                            
                            <Link v-if="canRegister" :href="route('register')" 
                                class="bg-gradient-to-r from-purple-600 to-indigo-600 text-white px-6 py-2 rounded-lg hover:from-purple-700 hover:to-indigo-700 transition font-semibold shadow-lg hover:shadow-xl">
                                Registrarse
                            </Link>
                        </template>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Hero Section -->
        <div class="relative overflow-hidden">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-24">
                <div class="text-center">
                    <h1 class="text-5xl md:text-6xl font-bold text-gray-900 mb-6">
                        Encuentra tus momentos
                        <span class="bg-gradient-to-r from-purple-600 to-indigo-600 bg-clip-text text-transparent block mt-2">
                            capturados en fotos
                        </span>
                    </h1>
                    <p class="text-xl text-gray-600 mb-8 max-w-2xl mx-auto">
                        Busca tu código único y descarga tus fotos profesionales de eventos especiales
                    </p>
                    <div class="flex flex-col sm:flex-row gap-4 justify-center">
                        <Link :href="route('events.index')" 
                            class="bg-gradient-to-r from-purple-600 to-indigo-600 text-white px-8 py-4 rounded-lg hover:from-purple-700 hover:to-indigo-700 transition font-bold text-lg shadow-lg hover:shadow-xl transform hover:-translate-y-1">
                            🔍 Buscar mis Fotos
                        </Link>
                        <a href="#eventos" 
                            class="bg-white text-gray-900 px-8 py-4 rounded-lg hover:bg-gray-50 transition font-bold text-lg shadow-lg hover:shadow-xl border-2 border-gray-200">
                            Ver Eventos
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stats Section -->
        <div class="bg-white py-12">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    <div class="text-center">
                        <div class="text-4xl font-bold text-purple-600 mb-2">{{ stats.total_photos || 0 }}</div>
                        <div class="text-gray-600">Fotos Disponibles</div>
                    </div>
                    <div class="text-center">
                        <div class="text-4xl font-bold text-indigo-600 mb-2">{{ stats.total_events || 0 }}</div>
                        <div class="text-gray-600">Eventos Activos</div>
                    </div>
                    <div class="text-center">
                        <div class="text-4xl font-bold text-pink-600 mb-2">{{ stats.total_photographers || 0 }}</div>
                        <div class="text-gray-600">Fotógrafos</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Features Section -->
        <div class="bg-gray-50 py-20">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-16">
                    <h2 class="text-4xl font-bold text-gray-900 mb-4">¿Cómo funciona?</h2>
                    <p class="text-xl text-gray-600">Simple, rápido y seguro</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-12">
                    <!-- Step 1 -->
                    <div class="text-center">
                        <div class="bg-gradient-to-br from-purple-100 to-indigo-100 w-20 h-20 rounded-full flex items-center justify-center mx-auto mb-6 shadow-lg">
                            <span class="text-3xl font-bold text-purple-600">1</span>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 mb-3">Busca tu evento</h3>
                        <p class="text-gray-600">Encuentra el evento en el que participaste</p>
                    </div>

                    <!-- Step 2 -->
                    <div class="text-center">
                        <div class="bg-gradient-to-br from-purple-100 to-indigo-100 w-20 h-20 rounded-full flex items-center justify-center mx-auto mb-6 shadow-lg">
                            <span class="text-3xl font-bold text-purple-600">2</span>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 mb-3">Ingresa tu código</h3>
                        <p class="text-gray-600">Usa el código único que te dieron</p>
                    </div>

                    <!-- Step 3 -->
                    <div class="text-center">
                        <div class="bg-gradient-to-br from-purple-100 to-indigo-100 w-20 h-20 rounded-full flex items-center justify-center mx-auto mb-6 shadow-lg">
                            <span class="text-3xl font-bold text-purple-600">3</span>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 mb-3">Descarga tus fotos</h3>
                        <p class="text-gray-600">Obtén tus fotos en alta calidad</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sección de Eventos -->
        <div id="eventos" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20">
            <div class="text-center mb-12">
                <h2 class="text-4xl font-bold text-gray-900 mb-4">Eventos Destacados</h2>
                <p class="text-xl text-gray-600">Revive los mejores momentos</p>
            </div>

            <div v-if="recentEvents && recentEvents.length > 0" class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-8">
                <Link 
                    v-for="event in recentEvents" 
                    :key="event.id" 
                    :href="route('events.show', event.slug)"
                    class="bg-white rounded-xl shadow-lg overflow-hidden hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-2"
                >
                    <!-- Thumbnail -->
                    <div class="relative h-48 overflow-hidden">
                        <template v-if="event.cover_image_url">
                            <img 
                                :src="event.cover_image_url"
                                :alt="event.name"
                                class="w-full h-full object-cover transition-transform duration-300 hover:scale-110" 
                                @error="handleImageError"
                            />
                        </template>
                        <template v-else>
                            <div class="w-full h-full bg-gradient-to-br from-purple-400 via-indigo-500 to-pink-500 flex items-center justify-center">
                                <svg class="w-20 h-20 text-white/50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            </div>
                        </template>

                        <!-- Overlay -->
                        <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-transparent to-transparent">
                            <div class="absolute bottom-3 left-3 right-3 flex items-center justify-between">
                                <div class="bg-white/95 backdrop-blur-sm px-4 py-2 rounded-full shadow-lg">
                                    <div class="flex items-center gap-2">
                                        <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                        <span class="text-sm font-bold text-gray-800">
                                            {{ event.photos_count || 0 }} fotos
                                        </span>
                                    </div>
                                </div>
                                <span v-if="!event.is_private" class="bg-green-500 text-white px-3 py-1 rounded-full text-xs font-bold shadow-lg">
                                    🌐 Público
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Info -->
                    <div class="p-6">
                        <h3 class="text-xl font-bold text-gray-900 mb-2 line-clamp-1">
                            {{ event.name }}
                        </h3>
                        <p v-if="event.description" class="text-gray-600 text-sm mb-3 line-clamp-2">
                            {{ event.description }}
                        </p>
                        <div class="flex items-center text-sm text-gray-500">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            {{ formatDate(event.event_date) }}
                        </div>
                        <div v-if="event.location" class="flex items-center text-sm text-gray-500 mt-2">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            {{ event.location }}
                        </div>
                    </div>

                    <!-- CTA -->
                    <div class="px-6 pb-6 border-t pt-4">
                        <div class="flex items-center justify-center text-indigo-600 font-semibold text-sm">
                            Ver galería del evento
                            <svg class="ml-2 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                            </svg>
                        </div>
                    </div>
                </Link>
            </div>

            <!-- Empty state -->
            <div v-else class="text-center py-12">
                <div class="text-6xl mb-4">📅</div>
                <p class="text-gray-500 text-lg">No hay eventos disponibles en este momento</p>
            </div>

            <div class="text-center">
                <Link :href="route('events.index')"
                    class="inline-flex items-center bg-purple-600 text-white px-8 py-3 rounded-lg hover:bg-purple-700 transition font-bold shadow-lg hover:shadow-xl">
                    Ver Todos los Eventos
                    <svg class="ml-2 w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                    </svg>
                </Link>
            </div>
        </div>

        <!-- Footer -->
        <footer class="bg-gray-900 text-white py-12">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                <p class="text-gray-400">
                    © {{ new Date().getFullYear() }} PixelSpot. Todos los derechos reservados.
                </p>
            </div>
        </footer>

    </div>
</template>
