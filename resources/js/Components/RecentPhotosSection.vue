<script setup>
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    photos: {
        type: Array,
        default: () => []
    },
    title: {
        type: String,
        default: 'Últimas Fotos Subidas'
    },
    subtitle: {
        type: String,
        default: 'Descubrí las fotografías más recientes lorem ipsum'
    }
});

const handleImageError = (e) => {
    e.target.style.display = 'none';
    const parent = e.target.parentElement;
    if (!parent.querySelector('.placeholder-gradient')) {
        const placeholder = document.createElement('div');
        placeholder.className = 'placeholder-gradient w-full h-full flex items-center justify-center';
        placeholder.innerHTML = `
            <svg class="w-12 h-12 text-white/50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
        `;
        parent.appendChild(placeholder);
    }
};

const isRecent = (date) => {
    if (!date) return false;
    const photoDate = new Date(date);
    const now = new Date();
    const diffTime = Math.abs(now - photoDate);
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
    return diffDays <= 7;
};

// Función para obtener la URL de la imagen
const getImageUrl = (photo) => {
    // Prioridad: thumbnail > watermark > file_path ## photo.thumbnail_url
    return  photo.watermarked_url  || photo.thumbnail_url || photo.file_url || null;  
};
</script>

<template>
    <div class="bg-gray-50 py-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="text-center mb-16">
                <h2 class="text-4xl md:text-5xl font-bold text-gray-900 mb-4">
                    {{ title }}
                </h2>
                <p class="text-xl text-gray-600">
                    {{ subtitle }}
                </p>
            </div>

            <!-- Grid de Fotos -->
            <div v-if="photos && photos.length > 0" class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                <!-- Cada foto redirige a /galeria/{uniqueId} -->
                <Link 
                    v-for="photo in photos" 
                    :key="photo.id"
                    :href="route('gallery.show', photo.unique_id)"
                    class="group relative aspect-square overflow-hidden rounded-xl shadow-lg hover:shadow-2xl transition-all duration-300 transform hover:scale-105 cursor-pointer"
                >
                    <!-- Imagen -->
                    <div class="w-full h-full bg-gradient-to-br from-purple-400 via-indigo-500 to-pink-500">
                        <img 
                            v-if="getImageUrl(photo)"
                            :src="getImageUrl(photo)"
                            :alt="`Foto ${photo.unique_id}`"
                            class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110" 
                            loading="lazy"
                            @error="handleImageError"
                        />
                        <div v-else class="w-full h-full flex items-center justify-center">
                            <svg class="w-16 h-16 text-white/30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>
                    </div>

                    <!-- Overlay con información -->
                    <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/40 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                        <div class="absolute bottom-0 left-0 right-0 p-4">
                            <!-- Código único -->
                            <div class="flex items-center justify-between mb-2">
                                <span class="bg-white/90 backdrop-blur-sm px-3 py-1.5 rounded-full text-xs font-bold text-gray-900">
                                     {{ photo.unique_id }}
                                </span>
                                <span v-if="photo.downloads > 0" class="bg-green-500/90 text-white px-3 py-1.5 rounded-full text-xs font-bold">
                                    {{ photo.downloads }} ⬇
                                </span>
                            </div>

                            <!-- Evento (si tiene evento asociado) -->
                            <div v-if="photo.event_name" class="mb-1">
                                <p class="text-white text-sm font-semibold truncate">
                                     {{ photo.event_name }}
                                </p>
                            </div>

                            <!-- Fotógrafo -->
                            <p v-if="photo.photographer_name" class="text-white/80 text-xs truncate">
                                 {{ photo.photographer_name }}
                            </p>
                        </div>
                    </div>

                    <!-- Badge de "Nueva" si es reciente (menos de 7 días) -->
                    <div 
                        v-if="isRecent(photo.created_at)"
                        class="absolute top-3 right-3 bg-gradient-to-r from-yellow-400 to-orange-500 text-white text-xs font-bold px-3 py-1 rounded-full shadow-lg animate-pulse"
                    >
                         Nueva
                    </div>
                </Link>
            </div>

            <!-- Empty state -->
            <div v-else class="text-center py-20">
                <div class="text-8xl mb-6">-</div>
                <h3 class="text-2xl font-bold text-gray-900 mb-2">No hay fotos recientes</h3>
                <p class="text-gray-600">Las nuevas fotos aparecerán acá</p>
            </div>

            <!-- Botón Ver Todas -->
            <div v-if="photos && photos.length > 0" class="text-center mt-12">
                <Link 
                    :href="route('events.index')"
                    class="inline-flex items-center bg-gradient-to-r from-indigo-600 to-purple-600 text-white px-10 py-4 rounded-xl hover:from-indigo-700 hover:to-purple-700 transition font-bold text-lg shadow-lg hover:shadow-xl transform hover:scale-105"
                >
                    Ver Todas las Fotos
                    <svg class="ml-3 w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                    </svg>
                </Link>
            </div>
        </div>
    </div>
</template>

<style scoped>
.placeholder-gradient {
    background: linear-gradient(135deg, rgb(168, 85, 247) 0%, rgb(99, 102, 241) 50%, rgb(236, 72, 153) 100%);
}
</style>
