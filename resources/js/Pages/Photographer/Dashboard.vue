<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { 
    CalendarIcon, 
    PhotoIcon, 
    ArrowDownTrayIcon, 
    MapPinIcon 
} from '@heroicons/vue/24/outline';

defineProps({
    photographer: Object,
    stats: Object,
    recentPhotos: Array,
    recentEvents: Array,
});

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString('es-ES', {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit'
    }).replace(/\//g, '.');
};

const getImageUrl = (photo) => {
    return photo.watermarked_url || photo.thumbnail_url || null;
};

const handleImageError = (e) => {
    e.target.style.display = 'none';
    const parent = e.target.parentElement;
    if (parent) {
        parent.classList.add('bg-zinc-900', 'border', 'border-white/10'); 
    }
};
</script>

<template>
    <Head title="Terminal Comando f33" />

    <AuthenticatedLayout>
        
        <div class="py-12 bg-black min-h-screen text-white selection:bg-red-600 selection:text-white">
            
            <div class="fixed inset-0 pointer-events-none bg-[linear-gradient(rgba(255,255,255,.03)_1px,transparent_1px),linear-gradient(90deg,rgba(255,255,255,.03)_1px,transparent_1px)] bg-[size:32px_32px] z-0"></div>

            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 relative z-10">

                <div class="flex flex-col md:flex-row md:items-end justify-between mb-10 border-b border-red-600 pb-6 gap-4">
                    <div>
                        <div class="flex items-center gap-3 mb-2">
                            <div class="w-3 h-3 bg-red-600 animate-pulse"></div>
                            <span class="text-[10px] font-mono font-bold tracking-[0.2em] text-red-600 uppercase">
                                Enlace Activo // Nivel de Acceso: PROFESIONAL
                            </span>
                        </div>
                        <h1 class="text-4xl md:text-5xl font-sans font-black text-white uppercase tracking-tighter">
                            {{ photographer.business_name || photographer.user.name }}
                        </h1>
                    </div>
                    <div class="flex gap-3">
                        <Link :href="route('photographer.photos.create')" 
                            class="px-6 py-3 bg-black border border-white text-white text-[10px] font-mono font-bold uppercase tracking-widest hover:bg-white hover:text-black transition-colors rounded-none flex items-center gap-2">
                            <PhotoIcon class="w-4 h-4" /> [ Cargar fotos ]
                        </Link>
                        <Link :href="route('photographer.events.create')" 
                            class="px-6 py-3 bg-red-600 border border-red-600 text-black text-[10px] font-mono font-bold uppercase tracking-widest hover:bg-black hover:text-red-600 transition-colors rounded-none flex items-center gap-2">
                            <CalendarIcon class="w-4 h-4" /> [ Nuevo evento ]
                        </Link>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-12">
                    
                    <div class="bg-zinc-950 p-6 border border-white/10 flex flex-col justify-between h-32 group hover:border-red-600 transition-colors relative overflow-hidden">
                        <div class="absolute top-0 right-0 w-8 h-8 border-t-2 border-r-2 border-white/10 group-hover:border-red-600 transition-colors"></div>
                        <div class="absolute bottom-0 left-0 w-8 h-8 border-b-2  border-white/10 group-hover:border-red-600 transition-colors"></div>
                        
                        <div class="flex justify-between items-start relative z-10">
                            <span class="text-[10px] font-mono uppercase tracking-widest text-gray-500 group-hover:text-red-600 transition-colors">Total Archivo</span>
                            <PhotoIcon class="w-4 h-4 text-gray-700 group-hover:text-red-600" />
                        </div>
                        <span class="text-5xl font-sans font-black text-white tracking-tighter relative z-10">{{ stats.total_photos }}</span>
                    </div>

                    <div class="bg-zinc-950 p-6 border border-white/10 flex flex-col justify-between h-32 group hover:border-red-600 transition-colors relative overflow-hidden">
                        <div class="absolute top-0 right-0 w-8 h-8 border-t-2 border-r-2 border-white/10 group-hover:border-red-600 transition-colors"></div>
                        <div class="absolute bottom-0 left-0 w-8 h-8 border-b-2  border-white/10 group-hover:border-red-600 transition-colors"></div>

                        <div class="flex justify-between items-start relative z-10">
                            <span class="text-[10px] font-mono uppercase tracking-widest text-gray-500 group-hover:text-red-600 transition-colors">Públicas</span>
                            <div class="w-2 h-2 rounded-full bg-white group-hover:bg-red-600 animate-pulse"></div>
                        </div>
                        <span class="text-5xl font-sans font-black text-white tracking-tighter relative z-10">{{ stats.active_photos }}</span>
                    </div>

                    <div class="bg-zinc-950 p-6 border border-white/10 flex flex-col justify-between h-32 group hover:border-red-600 transition-colors relative overflow-hidden">
                        <div class="absolute top-0 right-0 w-8 h-8 border-t-2 border-r-2 border-white/10 group-hover:border-red-600 transition-colors"></div>
                        <div class="absolute bottom-0 left-0 w-8 h-8 border-b-2  border-white/10 group-hover:border-red-600 transition-colors"></div>

                        <div class="flex justify-between items-start relative z-10">
                            <span class="text-[10px] font-mono uppercase tracking-widest text-gray-500 group-hover:text-red-600 transition-colors">Descargas</span>
                            <ArrowDownTrayIcon class="w-4 h-4 text-gray-700 group-hover:text-red-600" />
                        </div>
                        <span class="text-5xl font-sans font-black text-white tracking-tighter relative z-10">{{ stats.total_downloads }}</span>
                    </div>

                    <div class="bg-zinc-950 p-6 border border-white/10 flex flex-col justify-between h-32 group hover:border-red-600 transition-colors relative overflow-hidden">
                        <div class="absolute top-0 right-0 w-8 h-8 border-t-2 border-r-2 border-white/10 group-hover:border-red-600 transition-colors"></div>
                        <div class="absolute bottom-0 left-0 w-8 h-8 border-b-2  border-white/10 group-hover:border-red-600 transition-colors"></div>

                        <div class="flex justify-between items-start relative z-10">
                            <span class="text-[10px] font-mono uppercase tracking-widest text-gray-500 group-hover:text-red-600 transition-colors">Misiones / Eventos</span>
                            <CalendarIcon class="w-4 h-4 text-gray-700 group-hover:text-red-600" />
                        </div>
                        <span class="text-5xl font-sans font-black text-white tracking-tighter relative z-10">{{ stats.total_events }}</span>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-12">
                    
                    <div class="lg:col-span-2">
                        <div class="flex items-center justify-between mb-6 border-b border-white/20 pb-2">
                            <h3 class="text-xl font-sans font-black uppercase tracking-tighter text-white">Eventos Recientes</h3>
                            <Link :href="route('photographer.events.index')" 
                                class="text-[10px] font-mono font-bold uppercase tracking-widest text-red-600 hover:text-white transition flex items-center gap-1">
                                [ Ver Registro ]
                            </Link>
                        </div>

                        <div v-if="recentEvents && recentEvents.length > 0" class="space-y-6">
                            <Link v-for="(event, index) in recentEvents" :key="event.id" 
                                :href="route('photographer.events.show', event.id)"
                                class="group flex flex-col md:flex-row bg-zinc-950 border border-white/10 hover:border-red-600 transition-all duration-300 rounded-none overflow-hidden relative">
                                
                                <div class="w-full md:w-48 h-48 md:h-auto relative overflow-hidden bg-black flex-shrink-0">
                                    <img v-if="event.cover_image_url" :src="event.cover_image_url" :alt="event.name"
                                        class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105 filter grayscale contrast-125 group-hover:grayscale-0"
                                        @error="handleImageError" />
                                    <div v-else class="w-full h-full flex items-center justify-center text-gray-800">
                                        <span class="font-brush text-5xl opacity-30">f33</span>
                                    </div>
                                    
                                    <div class="absolute inset-0 bg-black/40 pointer-events-none group-hover:bg-transparent transition-colors"></div>
                                    <div class="absolute top-2 left-2 px-2 py-0.5 bg-black border border-white/20">
                                        <span :class="[
                                            'text-[8px] font-mono font-bold uppercase tracking-widest',
                                            event.is_private ? 'text-red-600' : 'text-white'
                                        ]">
                                            {{ event.is_private ? 'CLASIFICADO' : 'PÚBLICO' }}
                                        </span>
                                    </div>
                                    <div class="absolute bottom-2 right-2 text-[8px] font-mono text-white/50">
                                        REC // 0{{ index + 1 }}
                                    </div>
                                </div>

                                <div class="p-6 flex flex-col justify-between flex-1 bg-gradient-to-r from-transparent to-black/20">
                                    <div>
                                        <div class="flex justify-between items-start mb-2">
                                            <h4 class="text-2xl font-sans font-black text-white uppercase tracking-tighter group-hover:text-red-600 transition-colors line-clamp-1">
                                                {{ event.name }}
                                            </h4>
                                            <span class="text-[10px] font-mono text-gray-500 whitespace-nowrap ml-2 border-b border-gray-700">
                                                {{ formatDate(event.event_date) }}
                                            </span>
                                        </div>
                                        <p v-if="event.description" class="text-xs text-gray-400 font-mono tracking-wide line-clamp-2 mb-4">
                                            {{ event.description }}
                                        </p>
                                    </div>

                                    <div class="flex items-end justify-between pt-4 border-t border-white/10">
                                        <div class="flex items-center gap-4 text-[10px] font-mono text-gray-500 uppercase tracking-widest">
                                            <span class="flex items-center">
                                                <PhotoIcon class="w-3.5 h-3.5 mr-1 text-red-600" />
                                                {{ event.photos_count || 0 }} DATA
                                            </span>
                                            <span v-if="event.location" class="flex items-center truncate max-w-[150px]">
                                                <MapPinIcon class="w-3.5 h-3.5 mr-1 text-red-600" />
                                                {{ event.location }}
                                            </span>
                                        </div>
                                        <span class="text-[10px] font-mono font-bold uppercase tracking-widest text-white group-hover:text-red-600">> Ingresar</span>
                                    </div>
                                </div>
                            </Link>
                        </div>

                        <div v-else class="text-center py-16 border border-white/10 bg-zinc-950">
                            <p class="text-gray-500 font-mono text-sm uppercase tracking-widest mb-4">Sin operaciones activas.</p>
                            <Link :href="route('photographer.events.create')" 
                                class="text-[10px] font-mono font-bold uppercase tracking-widest text-white border-b border-white pb-1 hover:text-red-600 hover:border-red-600 transition">
                                Iniciar primera misión
                            </Link>
                        </div>
                    </div>

                    <div>
                        <div class="flex items-center justify-between mb-6 border-b border-white/20 pb-2">
                            <h3 class="text-xl font-sans font-black uppercase tracking-tighter text-white">Últimos Datos</h3>
                            <Link :href="route('photographer.photos.index')" 
                                class="text-[10px] font-mono font-bold uppercase tracking-widest text-red-600 hover:text-white transition flex items-center gap-1">
                                [ Ver Todo ]
                            </Link>
                        </div>

                        <div v-if="recentPhotos && recentPhotos.length > 0" class="grid grid-cols-2 gap-2">
                            <div v-for="photo in recentPhotos" :key="photo.id"
                                class="aspect-square bg-black overflow-hidden relative group border border-white/10 hover:border-red-600 ">
                                
                                <img 
                                    v-if="getImageUrl(photo)"
                                    :src="getImageUrl(photo)" 
                                    :alt="photo.unique_id"
                                    class="w-full h-full object-cover transition-all duration-500 group-hover:scale-105 filter grayscale contrast-125 group-hover:grayscale-0 opacity-70 group-hover:opacity-100"
                                    @error="handleImageError" 
                                />
                                
                                <div v-else class="w-full h-full flex items-center justify-center text-gray-800 bg-zinc-950">
                                    <span class="font-brush text-3xl opacity-20">f33</span>
                                </div>
                                
                                <div class="absolute inset-0 bg-black/60 opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex flex-col items-center justify-center gap-2 pointer-events-none">
                                    <span class="text-red-600 font-mono text-[9px] uppercase tracking-widest border border-red-600 px-2 py-1 bg-black/80">
                                        ID:{{ photo.unique_id.substring(0,6) }}
                                    </span>
                                </div>

                                <Link :href="route('photographer.photos.show', photo.id)" class="absolute inset-0 z-10"></Link>
                            </div>
                        </div>

                        <div v-else class="text-center py-16 border border-white/10 bg-zinc-950">
                            <p class="text-gray-500 font-mono text-xs uppercase tracking-widest mb-4">No hay datos<br>transmitidos.</p>
                            <Link :href="route('photographer.photos.create')" 
                                class="text-[10px] font-mono font-bold uppercase tracking-widest text-white border-b border-white pb-0.5 hover:text-red-600 hover:border-red-600 transition">
                                Iniciar carga
                            </Link>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>