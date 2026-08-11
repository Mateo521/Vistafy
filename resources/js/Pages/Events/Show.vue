<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import ProtectedImage from '@/Components/ProtectedImage.vue';

import { 
    ArrowLeftIcon, 
    MapPinIcon, 
    CalendarIcon,
    CameraIcon
} from '@heroicons/vue/24/outline';


const props = defineProps({
    event: Object,
    galleries: {
        type: Array,
        default: () => []
    },
    filters: Object
});

const handleImageError = (e) => {
    e.target.style.display = 'none';
    const parent = e.target.parentElement;
    if (!parent.querySelector('.placeholder-img')) {
        const placeholder = document.createElement('div');
        placeholder.className = 'placeholder-img w-full h-full min-h-[250px] flex items-center justify-center bg-slate-100 border border-slate-200';
        placeholder.innerHTML = `<span class="font-bold text-xs text-slate-400 uppercase tracking-widest">Sin Imagen</span>`;
        parent.appendChild(placeholder);
    }
};
</script>

<template>
    <Head :title="`${event.name} — f33.click`" />

    <AppLayout>
        <div class="min-h-screen bg-[#F8F9FA] text-slate-800 font-sans selection:bg-red-600 selection:text-white pb-20 pt-24 md:pt-32">
            
            <div class="max-w-[1400px] mx-auto px-4 md:px-8">
                
                
                <div class="mb-12">
                    <Link :href="route('events.index')"
                        class="inline-flex items-center gap-2 text-xs font-bold uppercase tracking-widest text-slate-400 hover:text-red-600 transition-colors mb-6">
                        <ArrowLeftIcon class="w-4 h-4" /> Volver a eventos subidos
                    </Link>

                    <div class="bg-white rounded-3xl overflow-hidden shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-slate-100 relative">
                        
                        <div class="absolute inset-0 h-48 md:h-full md:w-1/3 right-0 opacity-10 md:opacity-20 pointer-events-none overflow-hidden">
                            <img v-if="event.cover_image_url" :src="event.cover_image_url" class="w-full h-full object-cover grayscale mask-image-gradient" />
                        </div>

                        <div class="p-8 md:p-12 relative z-10">
                            <div class="flex flex-wrap gap-3 mb-4">
                                <span v-if="event.is_private" class="bg-red-600 text-white px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-widest">
                                    Evento Privado
                                </span>
                                <span class="bg-black text-white px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-widest">
                                    {{ event.photos_count }} fotos hechas
                                </span>
                            </div>

                            <h1 class="font-flux text-5xl md:text-7xl text-black leading-none mb-4 uppercase tracking-tight">
                                {{ event.name }}
                            </h1>
                            
                            <p v-if="event.description" class="text-slate-500 max-w-2xl text-sm md:text-base leading-relaxed mb-8 font-lato">
                                {{ event.description }}
                            </p>

                            <div class="flex flex-wrap gap-6 text-xs font-bold uppercase tracking-wider text-slate-400">
                                <div class="flex items-center gap-2">
                                    <CalendarIcon class="w-5 h-5 text-red-600" />
                                    <span>{{ event.event_date }}</span>
                                </div>
                                <div v-if="event.location" class="flex items-center gap-2">
                                    <MapPinIcon class="w-5 h-5 text-red-600" />
                                    <span>{{ event.location }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                
                <div v-if="!galleries || galleries.length === 0" class="text-center py-24 bg-white rounded-3xl shadow-sm border border-slate-100 mt-8">
                    <div class="w-20 h-20 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-6">
                        <CameraIcon class="w-10 h-10 text-slate-300" />
                    </div>
                    <h3 class="font-flux text-4xl text-black mb-3">Sin material disponible</h3>
                    <p class="font-lato text-slate-500 mb-8">Aún no se han cargado fotografías para este evento o están en revisión.</p>
                </div>

                
                <div v-else class="space-y-16">
                    
                    <div v-for="gallery in galleries" :key="gallery.photographer.id" class="relative">
                        
                        
                        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6 border-b border-slate-200 pb-4 sticky top-20 bg-[#F8F9FA]/90 backdrop-blur-md z-20 py-2">
                            
                            <div class="flex items-center gap-4">
                                
                                <div class="w-14 h-14 rounded-full bg-slate-200 border-2 border-white shadow-sm overflow-hidden flex-shrink-0 flex items-center justify-center">
                                    <img v-if="gallery.photographer.profile_photo_url" :src="gallery.photographer.profile_photo_url" class="w-full h-full object-cover">
                                    <span v-else class="font-flux text-2xl text-slate-400 mt-1">{{ gallery.photographer.business_name.charAt(0) }}</span>
                                </div>
                                
                                
                                <div>
                                    <h3 class="font-flux text-3xl text-black uppercase leading-none mb-1">
                                        {{ gallery.photographer.business_name }}
                                    </h3>
                                    
                                    <div v-if="gallery.roles && gallery.roles.length > 0" class="flex flex-wrap gap-2 mt-1">
                                        <span v-for="role in gallery.roles" :key="role" class="text-[10px] font-bold text-red-600 bg-red-50 px-2 py-0.5 rounded uppercase tracking-widest border border-red-100">
                                            {{ role }}
                                        </span>
                                    </div>
                                    <span v-else class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">
                                        Fotógrafo
                                    </span>
                                </div>
                            </div>

                            <div class="flex items-center gap-4">
                                <span class="text-xs font-bold text-slate-400 uppercase tracking-widest bg-white px-4 py-2 rounded-full shadow-sm border border-slate-100 hidden md:block">
                                    {{ gallery.photos_count }} Capturas
                                </span>
                                <Link :href="route('events.show-photographer', [event.slug, gallery.photographer.slug])" 
                                    class="bg-black text-white px-4 py-2 rounded-full text-xs font-bold uppercase tracking-widest hover:bg-red-600 transition-colors">
                                    Ver todo
                                </Link>
                            </div>



                        </div>

                        
                        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-5 gap-4">
                            <Link v-for="photo in gallery.photos" :key="photo.id"
                                :href="route('gallery.show', photo.unique_id)" 
                                class="group relative rounded overflow-hidden aspect-[4/5] cursor-pointer shadow-sm hover:shadow-xl transition-all duration-300 bg-slate-100">
                                
                                <ProtectedImage :src="photo.thumbnail_url"
                                    class="w-full h-full object-cover transition-transform duration-700  pointer-events-none"
                                    @error="handleImageError" />
                                
                                <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex flex-col justify-between p-3 md:p-4">
                                    
                                    
                                    <div class="flex justify-end">
                                        <span v-if="photo.location_role" class="bg-white/90 backdrop-blur-sm text-black font-bold text-[9px] px-2 py-1 rounded shadow-sm uppercase tracking-widest truncate max-w-[100px]">
                                            {{ photo.location_role }}
                                        </span>
                                    </div>

                                    <div class="w-full flex justify-between items-end">
                                        <span class="text-white font-mono text-[9px] md:text-[10px] tracking-widest">{{ photo.unique_id }}</span>
                                        <span class="bg-red-600 text-white font-bold text-xs px-2 py-1 rounded-lg">${{ photo.price }}</span>
                                    </div>
                                </div>
                            </Link>
                        </div>

                    </div>
                </div>

            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
.font-flux {
    font-family: 'Bebas Neue', sans-serif;
}
.font-lato {
    font-family: 'Lato', sans-serif;
}

.mask-image-gradient {
    mask-image: linear-gradient(to right, transparent, black);
    -webkit-mask-image: linear-gradient(to right, transparent, black);
}
</style>