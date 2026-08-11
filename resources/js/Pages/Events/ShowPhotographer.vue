<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import ProtectedImage from '@/Components/ProtectedImage.vue';
import { CameraIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
    event: Object,
    photographer: Object,
    photos: Object
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
    <Head :title="`${photographer.business_name} en ${event.name} — f33.click`" />

    <AppLayout>
        <div class="min-h-screen bg-[#F8F9FA] text-slate-800 font-sans selection:bg-red-600 selection:text-white pb-20 pt-24 md:pt-32">
            
            <div class="max-w-[1400px] mx-auto px-4 md:px-8">
                
                
                <div class="flex flex-wrap items-center gap-2 text-xs font-bold uppercase tracking-widest text-slate-400 mb-8">
                    <Link :href="route('events.index')" class="hover:text-red-600 transition-colors">Calendario</Link>
                    <span class="text-slate-300">/</span>
                    <Link :href="route('events.show', event.slug)" class="hover:text-red-600 transition-colors truncate max-w-[150px] sm:max-w-[300px]">{{ event.name }}</Link>
                    <span class="text-slate-300">/</span>
                    <span class="text-black">{{ photographer.business_name }}</span>
                </div>

                
                <div class="bg-white rounded p-6 md:p-8 shadow-sm border border-slate-100 mb-12 flex flex-col md:flex-row md:items-center justify-between gap-6">
                    <div class="flex items-center gap-6">
                        <div class="w-20 h-20 rounded-full bg-slate-200 border-4 border-white shadow-md overflow-hidden flex-shrink-0 flex items-center justify-center">
                            <img v-if="photographer.profile_photo_url" :src="photographer.profile_photo_url" class="w-full h-full object-cover">
                            <span v-else class="font-flux text-3xl text-slate-400 mt-1">{{ photographer.business_name.charAt(0) }}</span>
                        </div>
                        <div>
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1 block">Galería del Profesional</span>
                            <h1 class="font-flux text-4xl md:text-5xl text-black uppercase leading-none mb-2">
                                {{ photographer.business_name }}
                            </h1>
                            <div v-if="photographer.roles && photographer.roles.length > 0" class="flex flex-wrap gap-2">
                                <span v-for="role in photographer.roles" :key="role" class="text-[10px] font-bold text-red-600 bg-red-50 px-2 py-1 rounded uppercase tracking-widest border border-red-100">
                                 {{ role }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                
                <div v-if="!photos.data || photos.data.length === 0" class="text-center py-24 bg-white rounded shadow-sm border border-slate-100">
                    <div class="w-20 h-20 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-6">
                        <CameraIcon class="w-10 h-10 text-slate-300" />
                    </div>
                    <h3 class="font-flux text-4xl text-black mb-3">Sin material disponible</h3>
                    <p class="font-lato text-slate-500">Este profesional no tiene fotos públicas activas para este evento.</p>
                </div>

                
                <div v-else>
                    <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-5 gap-4 mb-16">
                        <Link v-for="photo in photos.data" :key="photo.id"
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

                    
                    <div v-if="photos.last_page > 1" class="flex justify-center">
                        <div class="flex flex-wrap gap-2 bg-white p-2 rounded shadow-sm border border-slate-100">
                            <template v-for="(link, index) in photos.links" :key="index">
                                <Link v-if="link.url" :href="link.url" 
                                    class="min-w-[40px] h-10 flex items-center justify-center px-4 text-sm font-bold rounded transition-colors"
                                    :class="link.active ? 'bg-red-600 text-white shadow-md' : 'bg-transparent text-slate-500 hover:bg-slate-50 hover:text-black'"
                                >
                                    <span v-html="link.label"></span>
                                </Link>
                                <span v-else v-html="link.label" class="min-w-[40px] h-10 flex items-center justify-center px-4 text-sm font-bold text-slate-300"></span>
                            </template>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </AppLayout>
</template>
