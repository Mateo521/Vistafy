<template>
    <PublicLayout>
        <!-- Banner con Video Promocional -->
        <div class="relative overflow-hidden" style="height: 600px;">
            <!-- Video de fondo -->
            <video 
                autoplay 
                muted 
                loop 
                playsinline
                class="absolute inset-0 w-full h-full object-cover"
            >
                <source src="/videos/promo.mp4" type="video/mp4">
            </video>

            <!-- Overlay oscuro -->
            <div class="absolute inset-0 bg-black opacity-50"></div>

            <!-- Contenido del Hero -->
            <div class="relative h-full flex items-center justify-center">
                <div class="max-w-4xl mx-auto text-center px-4 sm:px-6 lg:px-8">
                    <h1 class="text-5xl md:text-7xl font-bold text-white mb-6 drop-shadow-2xl animate-fade-in">
                        Encuentra tus mejores momentos
                    </h1>
                    <p class="text-xl md:text-2xl text-white mb-10 opacity-90 drop-shadow-lg animate-fade-in-delayed">
                        Busca tus fotos por ID único y descarga recuerdos en alta calidad
                    </p>

                    <!-- Buscador de fotos -->
                    <div class="max-w-2xl mx-auto animate-fade-in-more-delayed">
                        <form @submit.prevent="searchPhoto" class="flex gap-3">
                            <input 
                                v-model="searchQuery"
                                type="text"
                                placeholder="Ingresa tu ID de foto (ej: ABC123)"
                                class="flex-1 px-6 py-4 rounded-lg text-lg focus:ring-4 focus:ring-indigo-500 focus:outline-none"
                            >
                            <button 
                                type="submit"
                                class="bg-indigo-600 text-white px-8 py-4 rounded-lg font-bold hover:bg-indigo-700 transition transform hover:scale-105 shadow-xl"
                            >
                                <svg class="inline w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </button>
                        </form>
                        <p class="text-white text-sm mt-4 opacity-75">
                            💡 Tip: Encuentra tu ID único en la foto que te dieron
                        </p>
                    </div>
                </div>
            </div>

            <!-- Indicador de scroll -->
            <div class="absolute bottom-8 left-1/2 transform -translate-x-1/2 animate-bounce">
                <svg class="w-8 h-8 text-white opacity-75" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" />
                </svg>
            </div>
        </div>

        <!-- Sección de Características -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20">
            <div class="text-center mb-16">
                <h2 class="text-4xl font-bold text-gray-900 mb-4">¿Cómo funciona?</h2>
                <p class="text-xl text-gray-600">Es muy simple encontrar y comprar tus fotos</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-12">
                <!-- Paso 1 -->
                <div class="text-center">
                    <div class="bg-indigo-100 w-20 h-20 rounded-full flex items-center justify-center mx-auto mb-6">
                        <svg class="w-10 h-10 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-3">1. Busca tu ID</h3>
                    <p class="text-gray-600">Ingresa el código único que te proporcionó tu fotógrafo</p>
                </div>

                <!-- Paso 2 -->
                <div class="text-center">
                    <div class="bg-purple-100 w-20 h-20 rounded-full flex items-center justify-center mx-auto mb-6">
                        <svg class="w-10 h-10 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-3">2. Previsualiza</h3>
                    <p class="text-gray-600">Ve tu foto con marca de agua antes de comprar</p>
                </div>

                <!-- Paso 3 -->
                <div class="text-center">
                    <div class="bg-green-100 w-20 h-20 rounded-full flex items-center justify-center mx-auto mb-6">
                        <svg class="w-10 h-10 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10" />
                        </svg>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-3">3. Descarga</h3>
                    <p class="text-gray-600">Paga y descarga tu foto en alta resolución</p>
                </div>
            </div>
        </div>

        <!-- Galería de Muestra -->
        <div class="bg-gray-100 py-20">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-12">
                    <h2 class="text-4xl font-bold text-gray-900 mb-4">Fotos Recientes</h2>
                    <p class="text-xl text-gray-600">Explora nuestra galería de eventos</p>
                </div>

                <div v-if="recentPhotos.length > 0" class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4 mb-8">
                    <Link 
                        v-for="photo in recentPhotos" 
                        :key="photo.id"
                        :href="route('gallery.show', photo.unique_id)"
                        class="group relative aspect-square rounded-lg overflow-hidden shadow-md hover:shadow-2xl transition-all duration-300"
                    >
                        <img 
                            :src="photo.thumbnail_url" 
                            :alt="photo.unique_id"
                            class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500"
                        >
                        <div class="absolute inset-0 bg-gradient-to-t from-black via-transparent to-transparent opacity-0 group-hover:opacity-100 transition">
                            <div class="absolute bottom-0 left-0 right-0 p-3">
                                <p class="text-white text-xs font-mono">{{ photo.unique_id }}</p>
                            </div>
                        </div>
                    </Link>
                </div>

                <div class="text-center">
                    <Link 
                        :href="route('gallery.index')"
                        class="inline-flex items-center bg-indigo-600 text-white px-8 py-3 rounded-lg hover:bg-indigo-700 transition font-bold"
                    >
                        Ver Galería Completa
                        <svg class="ml-2 w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                        </svg>
                    </Link>
                </div>
            </div>
        </div>

        <!-- Sección de Eventos -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20">
            <div class="text-center mb-12">
                <h2 class="text-4xl font-bold text-gray-900 mb-4">Eventos Destacados</h2>
                <p class="text-xl text-gray-600">Revive los mejores momentos</p>
            </div>

            <div v-if="recentEvents.length > 0" class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-8">
                <Link 
                    v-for="event in recentEvents" 
                    :key="event.id"
                    :href="route('events.show', event.slug)"
                    class="bg-white rounded-lg shadow-lg overflow-hidden hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-2"
                >
                    <div class="h-48 bg-gradient-to-br from-purple-400 to-indigo-500 flex items-center justify-center text-white">
                        <div class="text-center">
                            <svg class="w-16 h-16 mx-auto mb-2 opacity-75" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            <p class="text-2xl font-bold">{{ event.photos_count }} fotos</p>
                        </div>
                    </div>
                    <div class="p-6">
                        <h3 class="text-xl font-bold text-gray-900 mb-2">{{ event.name }}</h3>
                        <p v-if="event.event_date" class="text-gray-500 text-sm">
                            {{ formatDate(event.event_date) }}
                        </p>
                    </div>
                </Link>
            </div>

            <div class="text-center">
                <Link 
                    :href="route('events.index')"
                    class="inline-flex items-center bg-purple-600 text-white px-8 py-3 rounded-lg hover:bg-purple-700 transition font-bold"
                >
                    Ver Todos los Eventos
                    <svg class="ml-2 w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                    </svg>
                </Link>
            </div>
        </div>
    </PublicLayout>
</template>

<script setup>
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

defineProps({
    recentPhotos: Array,
    recentEvents: Array,
});

const searchQuery = ref('');

const searchPhoto = () => {
    if (searchQuery.value.trim()) {
        router.visit(route('gallery.show', searchQuery.value.trim().toUpperCase()));
    }
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('es-ES', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
};
</script>

<style scoped>
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(30px); }
    to { opacity: 1; transform: translateY(0); }
}

.animate-fade-in {
    animation: fadeIn 1s ease-out;
}

.animate-fade-in-delayed {
    animation: fadeIn 1s ease-out 0.3s both;
}

.animate-fade-in-more-delayed {
    animation: fadeIn 1s ease-out 0.6s both;
}
</style>
