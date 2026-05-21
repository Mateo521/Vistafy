<script setup>
import { router } from '@inertiajs/vue3';

const props = defineProps({
    photos: {
        type: Array,
        default: () => []
    },
    title: {
        type: String,
        default: 'Últimas incorporaciones'
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
        placeholder.className = 'placeholder-institutional w-full h-full flex items-center justify-center bg-gray-900 border border-white/10';
        placeholder.innerHTML = `
            <svg class="w-12 h-12 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
    return photo.thumbnail_url || photo.watermarked_url || null;
};
</script>

<template>
    <div class="relative z-20" data-aos="fade-up">
        
        <div class="mb-12 flex justify-end">
            <button @click="router.visit(route('gallery.index'))" 
                class="group flex items-center gap-4 bg-black border border-white px-6 py-3 font-mono text-[10px] uppercase tracking-[0.2em] text-white hover:bg-white hover:text-black transition-colors cursor-pointer">
                <span>[ ACCEDER AL ARCHIVO COMPLETO ]</span>
            </button>
        </div>

        <div v-if="photos && photos.length > 0" class="columns-2 md:columns-3 lg:columns-4 gap-0 space-y-0">
            <div 
                v-for="(photo, index) in photos" 
                :key="photo.id"
                @click="router.visit(route('gallery.show', photo.unique_id))"
                @contextmenu.prevent
                class="group relative overflow-hidden bg-black block break-inside-avoid border border-black cursor-pointer"
                :class="(index % 5 === 0) ? 'aspect-[3/4]' : ((index % 3 === 0) ? 'aspect-square' : 'aspect-[4/5]')"
            >
                <img 
                    v-if="getImageUrl(photo)"
                    :src="getImageUrl(photo)"
                    :alt="`Archivo ${photo.unique_id}`"
                    draggable="false"
                    @contextmenu.prevent
                    class="w-full h-full object-cover transition-all duration-700 group-hover:scale-105 grayscale contrast-125 group-hover:grayscale-0 select-none pointer-events-none" 
                    loading="lazy"
                    @error="handleImageError"
                />
                
                <div v-else class="w-full h-full flex items-center justify-center text-gray-800">
                    <span class="font-brush text-5xl opacity-20">f33</span>
                </div>

                <div v-if="isRecent(photo.created_at)" 
                    class="absolute top-4 left-4 bg-red-600 text-black text-[9px] font-bold uppercase tracking-widest px-2 py-1 pointer-events-none">
                    NUEVO
                </div>

                <div class="absolute inset-0 bg-gradient-to-t from-black via-black/20 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex flex-col justify-end p-6 pointer-events-none">
                    <div class="translate-y-4 group-hover:translate-y-0 transition-transform duration-300">
                        <div class="flex items-center justify-between mb-2 border-b border-white/20 pb-2">
                            <p class="font-mono text-[10px] uppercase tracking-[0.2em] text-red-600 font-bold">
                                #{{ photo.unique_id }}
                            </p>
                            <p v-if="photo.price" class="font-mono text-[11px] font-bold text-white">
                                ${{ photo.price }}
                            </p>
                        </div>
                        
                        <div class="font-sans font-black text-xl text-white uppercase tracking-tighter leading-tight truncate">
                            {{ photo.event_name || 'COBERTURA GENERAL' }}
                        </div>
                        
                        <div class="mt-4 flex items-center justify-between">
                            <span class="font-mono text-[9px] text-gray-400 uppercase tracking-widest">
                                {{ photo.photographer || 'Staff f33' }}
                            </span>
                            <div class="h-6 w-6 border border-white flex items-center justify-center bg-white text-black">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M7 17L17 7M17 7H7M17 7v10" />
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div v-else class="text-center py-32 border border-white/10 bg-gray-950">
            <p class="font-mono text-sm uppercase tracking-widest text-gray-500">Bóveda vacía. A la espera de transmisiones.</p>
        </div>
    </div>
</template>