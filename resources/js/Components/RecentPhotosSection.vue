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
 
    return photo.watermarked_url || photo.thumbnail_url || null;
};
</script>

<template>
    <div class="bg-[#111920] py-24 font-['Syne']">
        <div class="max-w-7xl mx-auto px-8 md:px-16">
            
            <div class="flex flex-col md:flex-row md:items-end justify-between mb-16 gap-6">
                <div>
                    <div class="text-[9px] font-bold tracking-[0.35em] uppercase text-[#FFB162] mb-3 flex items-center gap-3 after:content-[''] after:h-px after:w-12 after:bg-[#A35139]">
                        Galería
                    </div>
                    <h2 class="font-['Cormorant_Garamond'] text-4xl md:text-5xl font-light text-[#EEE9DF] leading-tight">
                        {{ title }}
                    </h2>
                    <p class="text-[13px] text-[#C9C1B1] mt-2 max-w-lg">
                        {{ subtitle }}
                    </p>
                </div>
                
                <Link :href="route('gallery.index')" 
                    class="group flex items-center text-[11px] font-bold uppercase tracking-[0.2em] text-[#FFB162] pb-1 border-b border-[#A35139] hover:text-[#A35139] transition-colors w-fit">
                    Ver Archivo Completo
                    <span class="ml-2 group-hover:translate-x-1 transition-transform">&rarr;</span>
                </Link>
            </div>

    

            <div v-if="photos && photos.length > 0" class="columns-2 md:columns-3 lg:columns-5 gap-4">
                <Link 
                    v-for="(photo, index) in photos" 
                    :key="photo.id"
                    :href="route('gallery.show', photo.unique_id)"
                    class="group relative overflow-hidden bg-[#1B2632] block break-inside-avoid mb-4"
                    :class="(index % 3 === 0) ? 'aspect-[4/5]' : ((index % 2 === 0) ? 'aspect-[2/3]' : 'aspect-[3/4]')"
                >
                    <img 
                        v-if="getImageUrl(photo)"
                        :src="getImageUrl(photo)"
                        :alt="`Foto ${photo.unique_id}`"
                        class="w-full h-full object-cover transition-all duration-[800ms] group-hover:scale-110 saturate-[0.4] group-hover:saturate-110" 
                        loading="lazy"
                        @error="handleImageError"
                    />
                    
                    <div v-else class="w-full h-full flex items-center justify-center text-[#2C3B4D]">
                        <span class="font-['Cormorant_Garamond'] text-4xl italic opacity-30">f33</span>
                    </div>

                    <div v-if="isRecent(photo.created_at)" 
                        class="absolute top-3 left-3 bg-[#FFB162] text-[#1B2632] text-[9px] font-bold uppercase tracking-widest px-2 py-1">
                        Nuevo
                    </div>

                    <div class="absolute inset-0 bg-gradient-to-t from-[#111920] via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-400 flex flex-col justify-end p-5">
                        <div class="translate-y-4 group-hover:translate-y-0 transition-transform duration-400">
                            <p class="text-[9px] uppercase tracking-widest text-[#FFB162] mb-1">ID: {{ photo.unique_id }}</p>
                            <div class="font-['Cormorant_Garamond'] text-xl text-[#EEE9DF] leading-tight truncate">
                                {{ photo.event_name || 'Sin evento' }}
                            </div>
                            <div v-if="photo.downloads > 0" class="mt-2 text-[10px] text-[#C9C1B1] font-mono tracking-widest">
                                &darr; {{ photo.downloads }} DL
                            </div>
                        </div>
                    </div>
                </Link>
            </div>

            <div v-else class="text-center py-32 border border-[#C9C1B1]/10 bg-[#1B2632]">
                <p class="font-['Cormorant_Garamond'] text-2xl italic text-[#C9C1B1]/50">No se han cargado fotografías recientemente.</p>
            </div>

        </div>
    </div>
</template>