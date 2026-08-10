<script setup>
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import { 
    ArrowLeftIcon, 
    MagnifyingGlassIcon, 
    HashtagIcon, 
    MapPinIcon,
    CalendarIcon,
    CameraIcon
} from '@heroicons/vue/24/outline';
import ProtectedImage from '@/Components/ProtectedImage.vue';

const props = defineProps({
    event: Object,
    photos: Object,
    photographers: Array,
});

const selectedPhotographer = ref('all');

const filterForm = useForm({
    photographer_id: '',
});

const filterByPhotographer = () => {
    if (selectedPhotographer.value === 'all') {
        filterForm.photographer_id = '';
    } else {
        filterForm.photographer_id = selectedPhotographer.value;
    }

    filterForm.get(route('events.show', props.event.slug), {
        preserveState: true,
        preserveScroll: true,
    });
};

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
    if (!parent.querySelector('.placeholder-img')) {
        const placeholder = document.createElement('div');
        placeholder.className = 'placeholder-img w-full h-full min-h-[250px] flex flex-col items-center justify-center bg-gray-50 border border-gray-100 rounded';
        placeholder.innerHTML = `
            <svg class="w-8 h-8 text-gray-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
            <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">No disponible</span>
        `;
        parent.appendChild(placeholder);
    }
};
</script>

<template>
    <Head :title="event.name + ' — F33'" />

    <AppLayout>
        
        <div class="fixed top-24 md:top-28 left-4 md:left-8 z-40">
            <Link :href="route('events.index')"
                class="flex items-center gap-2 bg-white/80 backdrop-blur-md px-4 py-2 rounded-full shadow-sm border border-gray-200 text-xs font-bold uppercase tracking-wider text-gray-600 hover:text-black hover:shadow-md hover:-translate-x-1 transition-all">
                <ArrowLeftIcon class="w-4 h-4" /> Volver
            </Link>
        </div>

        <div class="min-h-screen bg-[#F8F9FA] text-slate-800 pb-24 pt-20">
            <div class="max-w-[1500px] mx-auto px-4 md:px-8">

                
                <div class="relative w-full h-[50vh] min-h-[400px] rounded overflow-hidden shadow-xl mb-12 flex flex-col justify-end">
                    
                    <div class="absolute inset-0 w-full h-full bg-slate-900">
                        <img v-if="event.cover_image_url" :src="event.cover_image_url" 
                             class="w-full h-full object-cover opacity-80 mix-blend-overlay" :alt="event.name" />
                        <div class="absolute inset-0 bg-gradient-to-t from-black/90 via-black/40 to-transparent"></div>
                    </div>

                    
                    <div class="relative z-10 p-8 md:p-16">
                        <div class="flex items-center gap-2 mb-4">
                            <span class="bg-[#E30613] text-white text-[10px] font-bold uppercase tracking-widest px-3 py-1 rounded-full flex items-center gap-1.5">
                                <CalendarIcon class="w-3.5 h-3.5" /> {{ formatDate(event.event_date) }}
                            </span>
                            <span v-if="event.location" class="bg-white/20 backdrop-blur-md text-white border border-white/20 text-[10px] font-bold uppercase tracking-widest px-3 py-1 rounded-full flex items-center gap-1.5">
                                <MapPinIcon class="w-3.5 h-3.5" /> {{ event.location }}
                            </span>
                        </div>
                        
                        <h1 class="font-flux text-5xl md:text-8xl text-white leading-none tracking-wide mb-6">
                            {{ event.name }}
                        </h1>

                        <div class="flex items-center gap-2 text-white/80 text-sm font-medium">
                            <CameraIcon class="w-5 h-5" />
                            <span><strong class="text-white">{{ event.photos_count }}</strong> fotografías capturadas</span>
                        </div>
                    </div>
                </div>

            
                <div class="flex flex-col lg:flex-row gap-8 lg:gap-12 mb-16">
                    
                
                    <div class="lg:w-7/12">
                        <div class="bg-white p-8 md:p-10 rounded shadow-sm border border-gray-100 h-full">
                            <h3 class="text-xs font-bold uppercase tracking-widest text-gray-400 mb-6 flex items-center gap-2">
                                <span class="w-8 h-px bg-gray-200"></span> Acerca del evento
                            </h3>
                            <p class="text-lg md:text-xl text-slate-700 leading-relaxed mb-6 font-medium">
                                {{ event.description || 'Sin descripción general asignada a este evento.' }}
                            </p>
                            <p v-if="event.long_description" class="text-slate-500 leading-relaxed">
                                {{ event.long_description }}
                            </p>
                        </div>
                    </div>

                
                    <div class="lg:w-5/12 flex flex-col gap-6">
                        
                        
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-1 xl:grid-cols-2 gap-4">
                            <Link :href="route('events.face-search', event.slug)"
                                class="group bg-gradient-to-br from-[#E30613] to-red-800 p-6 rounded shadow-md hover:shadow-xl hover:-translate-y-1 transition-all duration-300 relative overflow-hidden">
                                <div class="absolute -right-4 -bottom-4 opacity-20 transform  transition-transform duration-500">
                                    <MagnifyingGlassIcon class="w-24 h-24 text-white" />
                                </div>
                                <div class="relative z-10">
                                    <div class="w-10 h-10 bg-white/20 rounded-full flex items-center justify-center mb-4 backdrop-blur-sm">
                                        <MagnifyingGlassIcon class="w-5 h-5 text-white" />
                                    </div>
                                    <h4 class="font-bold text-white text-lg mb-1">Escáner Facial</h4>
                                    <p class="text-red-100 text-xs">Sube una selfie y encuéntrate.</p>
                                </div>
                            </Link>

                            <Link :href="route('events.bib-search', event.slug)"
                                class="group bg-black p-6 rounded shadow-md hover:shadow-xl hover:-translate-y-1 transition-all duration-300 relative overflow-hidden">
                                <div class="absolute -right-4 -bottom-4 opacity-10 transform  transition-transform duration-500">
                                    <HashtagIcon class="w-24 h-24 text-white" />
                                </div>
                                <div class="relative z-10">
                                    <div class="w-10 h-10 bg-white/10 rounded-full flex items-center justify-center mb-4 backdrop-blur-sm border border-white/10">
                                        <HashtagIcon class="w-5 h-5 text-white" />
                                    </div>
                                    <h4 class="font-bold text-white text-lg mb-1">Búsqueda OCR</h4>
                                    <p class="text-gray-400 text-xs">Busca por tu número de dorsal.</p>
                                </div>
                            </Link>
                        </div>

                    
                        <div class="bg-white p-6 rounded shadow-sm border border-gray-100 flex-1">
                            <h3 class="text-xs font-bold uppercase tracking-widest text-gray-400 mb-4 flex items-center gap-2">
                                <span class="w-4 h-px bg-gray-200"></span> Fotógrafos Oficiales
                            </h3>
                            
                            <div class="flex flex-wrap gap-2">
                                
                                <label class="cursor-pointer">
                                    <input type="radio" v-model="selectedPhotographer" value="all" @change="filterByPhotographer" class="sr-only">
                                    <div :class="[
                                        'px-4 py-2 rounded-full text-xs font-bold uppercase tracking-wider transition-all duration-200 border',
                                        selectedPhotographer === 'all' 
                                            ? 'bg-black text-white border-black shadow-md' 
                                            : 'bg-gray-50 text-gray-500 border-gray-200 hover:bg-gray-100 hover:text-black'
                                    ]">
                                        Ver Todos
                                    </div>
                                </label>

                                
                                <label v-for="photographer in photographers" :key="photographer.id" class="cursor-pointer">
                                    <input type="radio" v-model="selectedPhotographer" :value="photographer.id" @change="filterByPhotographer" class="sr-only">
                                    <div :class="[
                                        'px-4 py-2 rounded-full text-xs font-bold uppercase tracking-wider transition-all duration-200 border flex items-center gap-2',
                                        selectedPhotographer === photographer.id 
                                            ? 'bg-black text-white border-black shadow-md' 
                                            : 'bg-gray-50 text-gray-500 border-gray-200 hover:bg-gray-100 hover:text-black'
                                    ]">
                                        {{ photographer.business_name || photographer.user.name }}
                                        <span :class="[
                                            'px-1.5 py-0.5 rounded-full text-[9px] leading-none',
                                            selectedPhotographer === photographer.id ? 'bg-white/20 text-white' : 'bg-gray-200 text-gray-600'
                                        ]">
                                            {{ photographer.photos_count }}
                                        </span>
                                    </div>
                                </label>
                            </div>
                        </div>

                    </div>
                </div>

                
                <div v-if="photos.data && photos.data.length > 0">
                    <div class="flex justify-between items-center mb-6 px-2">
                        <h2 class="text-2xl font-flux text-black tracking-wide">Capturas Recientes</h2>
                        <span class="text-sm font-bold text-gray-500">{{ photos.total }} resultados</span>
                    </div>

                    <div class="columns-2 md:columns-3 lg:columns-4 xl:columns-5 gap-4 space-y-4 masonry-grid mb-16">
                        <div v-for="photo in photos.data" :key="photo.id"
                            @click="router.visit(route('gallery.show', photo.unique_id))" @contextmenu.prevent
                            class="break-inside-avoid block group relative bg-white rounded overflow-hidden shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300 cursor-pointer border border-transparent hover:border-gray-200">

                            <div class="relative w-full h-auto">
                                <ProtectedImage :src="photo.thumbnail_url" :alt="photo.unique_id"
                                    class="w-full h-auto object-cover transition-transform duration-700  pointer-events-none"
                                    loading="lazy" @error="handleImageError" />

                            
                                <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none"></div>

                            
                                <div class="absolute top-3 left-3 bg-white/90 backdrop-blur px-2 py-1 rounded-md text-[10px] font-bold text-slate-800 shadow-sm pointer-events-none opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                                    #{{ photo.unique_id }}
                                </div>

                            
                                <div class="absolute bottom-3 left-3 right-3 flex justify-between items-end opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none">
                                    <div class="bg-black/70 backdrop-blur-md text-white px-3 py-1.5 rounded-full text-[10px] font-bold uppercase tracking-wider">
                                        {{ photo.photographer_name }}
                                    </div>
                                    <div class="w-8 h-8 bg-white/30 backdrop-blur-md rounded-full flex items-center justify-center text-white">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                        </svg>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                
                    <div v-if="photos.last_page > 1" class="flex justify-center pt-8 border-t border-gray-200">
                        <div class="flex flex-wrap gap-2 bg-white p-2 rounded-full shadow-sm border border-gray-100">
                            <template v-for="(link, index) in photos.links" :key="index">
                                <Link v-if="link.url" :href="link.url"
                                    class="min-w-[40px] h-10 flex items-center justify-center px-4 rounded-full text-xs font-bold transition-colors"
                                    :class="link.active
                                        ? 'bg-[#E30613] text-white shadow-md'
                                        : 'bg-transparent text-gray-600 hover:bg-gray-100 hover:text-black'">
                                    <span v-html="link.label"></span>
                                </Link>
                                <span v-else v-html="link.label" class="min-w-[40px] h-10 flex items-center justify-center px-4 rounded-full text-xs font-bold text-gray-300"></span>
                            </template>
                        </div>
                    </div>
                </div>

            
                <div v-else class="flex flex-col items-center justify-center py-24 px-4 text-center bg-white rounded shadow-sm border border-gray-100">
                    <div class="w-20 h-20 bg-gray-50 rounded-full flex items-center justify-center mb-6">
                        <CameraIcon class="w-8 h-8 text-gray-300" />
                    </div>
                    <h3 class="font-flux text-4xl text-black mb-3">Sin Resultados</h3>
                    <p class="text-gray-500 mb-8 max-w-md">
                        {{ selectedPhotographer !== 'all' 
                            ? 'El fotógrafo seleccionado aún no ha publicado capturas para este evento.' 
                            : 'Aún no se han publicado fotografías para este evento.' }}
                    </p>
                    <button v-if="selectedPhotographer !== 'all'"
                        @click="selectedPhotographer = 'all'; filterByPhotographer()"
                        class="bg-black text-white px-8 py-3.5 rounded-full font-bold text-xs uppercase tracking-wider hover:bg-gray-800 transition-colors">
                        Ver galería completa
                    </button>
                </div>

            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
.masonry-grid {
    column-fill: balance;
}


::-webkit-scrollbar {
    width: 8px;
}
::-webkit-scrollbar-track {
    background: #F8F9FA;
}
::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 10px;
}
::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
}
</style>