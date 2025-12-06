<script setup>
import { Link } from '@inertiajs/vue3';
import { ArrowLongRightIcon, ArrowDownTrayIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
    photos: {
        type: Array,
        default: () => []
    },
    title: {
        type: String,
        default: 'Últimas Incorporaciones'
    },
    subtitle: {
        type: String,
        default: 'Nuevas imágenes añadidas al archivo oficial.'
    }
});

const handleImageError = (e) => {
    e.target.style.display = 'none';
    const parent = e.target.parentElement;
    if (!parent.querySelector('.placeholder-institutional')) {
        const placeholder = document.createElement('div');
        placeholder.className = 'placeholder-institutional w-full h-full flex items-center justify-center bg-gray-100';
        placeholder.innerHTML = `
            <svg class="w-12 h-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1"
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

const getImageUrl = (photo) => {
    return photo.watermarked_url || photo.thumbnail_url || photo.file_url || null;  
};
</script>

<template>
    <div class="bg-white py-24 border-t border-gray-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <div class="flex flex-col md:flex-row md:items-end justify-between mb-12 gap-6">
                <div class="max-w-2xl">
                    <h2 class="text-3xl md:text-4xl font-serif font-bold text-slate-900 mb-3">
                        {{ title }}
                    </h2>
                    <p class="text-sm text-slate-500 font-light leading-relaxed">
                        {{ subtitle }}
                    </p>
                </div>
                
                <Link :href="route('gallery.index')" 
                    class="group flex items-center text-xs font-bold uppercase tracking-widest text-slate-900 hover:text-slate-600 transition-colors pb-1 border-b border-slate-900 hover:border-slate-600 w-fit">
                    Ver Archivo Completo
                    <ArrowLongRightIcon class="w-4 h-4 ml-2 group-hover:translate-x-1 transition-transform" />
                </Link>
            </div>

            <div v-if="photos && photos.length > 0" class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-6">
                <Link 
                    v-for="photo in photos" 
                    :key="photo.id"
                    :href="route('gallery.show', photo.unique_id)"
                    class="group relative aspect-square overflow-hidden bg-gray-100 rounded-sm border border-gray-200 hover:border-slate-400 transition-all duration-300"
                >
                    <img 
                        v-if="getImageUrl(photo)"
                        :src="getImageUrl(photo)"
                        :alt="`Foto ${photo.unique_id}`"
                        class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105 filter grayscale-[0.3] group-hover:grayscale-0" 
                        loading="lazy"
                        @error="handleImageError"
                    />
                    
                    <div v-else class="w-full h-full flex items-center justify-center text-slate-300">
                        <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                    </div>

                    <div v-if="isRecent(photo.created_at)" 
                        class="absolute top-3 left-3 bg-slate-900 text-white text-[9px] font-bold uppercase tracking-widest px-2 py-1 shadow-sm">
                        Nuevo
                    </div>

                    <div class="absolute bottom-0 left-0 w-full p-4 bg-gradient-to-t from-slate-900/90 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                        <div class="text-white">
                            <p class="text-[10px] uppercase tracking-widest text-white/60 mb-1">ID: {{ photo.unique_id }}</p>
                            <div class="flex justify-between items-end">
                                <span class="text-xs font-bold truncate pr-2">{{ photo.event_name || 'Sin evento' }}</span>
                                <span v-if="photo.downloads > 0" class="flex items-center text-[10px] font-mono bg-white/10 px-1.5 py-0.5 rounded-sm">
                                    <ArrowDownTrayIcon class="w-3 h-3 mr-1" /> {{ photo.downloads }}
                                </span>
                            </div>
                        </div>
                    </div>
                </Link>
            </div>

            <div v-else class="text-center py-24 border border-dashed border-gray-300 bg-gray-50 rounded-sm">
                <p class="text-slate-400 text-sm font-light">No se han cargado fotografías recientemente.</p>
            </div>

        </div>
    </div>
</template>