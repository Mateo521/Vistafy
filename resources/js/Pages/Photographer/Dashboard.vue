<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    photographer: Object,
    stats: Object,
    recentPhotos: Array,
    recentEvents: Array,
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
</script>

<template>
    <Head title="Panel Profesional" />

    <AuthenticatedLayout>
        
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

                <div class="flex flex-col md:flex-row md:items-end justify-between mb-10 border-b border-gray-200 pb-6 gap-4">
                    <div>
                        <span class="text-xs font-bold tracking-[0.2em] text-slate-400 uppercase mb-2 block">
                            Área Profesional
                        </span>
                        <h1 class="text-3xl font-serif font-bold text-slate-900">
                            {{ photographer.business_name || photographer.user.name }}
                        </h1>
                    </div>
                    <div class="flex gap-3">
                        <Link :href="route('photographer.photos.create')" 
                            class="px-6 py-3 bg-white border border-slate-300 text-slate-700 text-xs font-bold uppercase tracking-widest hover:bg-slate-50 hover:border-slate-900 hover:text-slate-900 transition rounded-sm">
                            Subir Fotos
                        </Link>
                        <Link :href="route('photographer.events.create')" 
                            class="px-6 py-3 bg-slate-900 text-white text-xs font-bold uppercase tracking-widest hover:bg-slate-800 transition rounded-sm shadow-sm">
                            Nuevo Evento
                        </Link>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-12">
                    
                    <div class="bg-white p-6 border border-gray-200 rounded-sm shadow-sm flex flex-col justify-between h-32 group hover:border-slate-300 transition-colors">
                        <div class="flex justify-between items-start">
                            <span class="text-[10px] font-bold uppercase tracking-widest text-slate-500 group-hover:text-slate-900 transition-colors">Total Archivo</span>
                            <svg class="w-5 h-5 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                        </div>
                        <span class="text-4xl font-serif font-medium text-slate-900">{{ stats.total_photos }}</span>
                    </div>

                    <div class="bg-white p-6 border border-gray-200 rounded-sm shadow-sm flex flex-col justify-between h-32 group hover:border-slate-300 transition-colors">
                        <div class="flex justify-between items-start">
                            <span class="text-[10px] font-bold uppercase tracking-widest text-slate-500 group-hover:text-slate-900 transition-colors">Públicas</span>
                            <svg class="w-5 h-5 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        </div>
                        <span class="text-4xl font-serif font-medium text-slate-900">{{ stats.active_photos }}</span>
                    </div>

                    <div class="bg-white p-6 border border-gray-200 rounded-sm shadow-sm flex flex-col justify-between h-32 group hover:border-slate-300 transition-colors">
                        <div class="flex justify-between items-start">
                            <span class="text-[10px] font-bold uppercase tracking-widest text-slate-500 group-hover:text-slate-900 transition-colors">Descargas</span>
                            <svg class="w-5 h-5 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>
                        </div>
                        <span class="text-4xl font-serif font-medium text-slate-900">{{ stats.total_downloads }}</span>
                    </div>

                    <div class="bg-white p-6 border border-gray-200 rounded-sm shadow-sm flex flex-col justify-between h-32 group hover:border-slate-300 transition-colors">
                        <div class="flex justify-between items-start">
                            <span class="text-[10px] font-bold uppercase tracking-widest text-slate-500 group-hover:text-slate-900 transition-colors">Eventos</span>
                            <svg class="w-5 h-5 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                        </div>
                        <span class="text-4xl font-serif font-medium text-slate-900">{{ stats.total_events }}</span>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-12">
                    
                    <div class="lg:col-span-2">
                        <div class="flex items-center justify-between mb-6 border-b border-gray-100 pb-2">
                            <h3 class="text-lg font-serif font-bold text-slate-900">Eventos Recientes</h3>
                            <Link :href="route('photographer.events.index')" 
                                class="text-[10px] font-bold uppercase tracking-widest text-slate-400 hover:text-slate-900 transition flex items-center gap-1">
                                Ver Todo
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                            </Link>
                        </div>

                        <div v-if="recentEvents && recentEvents.length > 0" class="space-y-6">
                            <Link v-for="event in recentEvents" :key="event.id" 
                                :href="route('photographer.events.show', event.id)"
                                class="group flex flex-col md:flex-row bg-white border border-gray-200 hover:border-slate-400 transition-all duration-300 rounded-sm overflow-hidden">
                                
                                <div class="w-full md:w-48 h-48 md:h-auto relative overflow-hidden bg-gray-100">
                                    <img v-if="event.cover_image_url" :src="event.cover_image_url" :alt="event.name"
                                        class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105 filter grayscale-[0.5] group-hover:grayscale-0"
                                        @error="handleImageError" />
                                    <div v-else class="w-full h-full bg-slate-50 flex items-center justify-center text-slate-300">
                                        <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                    </div>
                                    
                                    <div class="absolute top-0 left-0 p-3">
                                        <span :class="[
                                            'px-2 py-1 text-[9px] font-bold uppercase tracking-widest border',
                                            event.is_private 
                                                ? 'bg-white text-slate-900 border-slate-900' 
                                                : 'bg-slate-900 text-white border-transparent'
                                        ]">
                                            {{ event.is_private ? 'Privado' : 'Público' }}
                                        </span>
                                    </div>
                                </div>

                                <div class="p-6 flex flex-col justify-between flex-1">
                                    <div>
                                        <div class="flex justify-between items-start mb-2">
                                            <h4 class="text-xl font-serif font-bold text-slate-900 group-hover:text-slate-600 transition-colors line-clamp-1">
                                                {{ event.name }}
                                            </h4>
                                            <span class="text-xs font-mono text-slate-400 whitespace-nowrap ml-2">
                                                {{ formatDate(event.event_date) }}
                                            </span>
                                        </div>
                                        <p v-if="event.description" class="text-sm text-slate-500 font-light line-clamp-2 mb-4">
                                            {{ event.description }}
                                        </p>
                                    </div>

                                    <div class="flex items-end justify-between pt-4 border-t border-gray-100">
                                        <div class="flex items-center gap-4 text-xs text-slate-500 uppercase tracking-wide">
                                            <span class="flex items-center">
                                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                                {{ event.photos_count || 0 }} Fotos
                                            </span>
                                            <span v-if="event.location" class="flex items-center truncate max-w-[150px]">
                                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /></svg>
                                                {{ event.location }}
                                            </span>
                                        </div>
                                        <span class="text-xs font-bold uppercase tracking-widest text-slate-900 group-hover:underline">Gestionar</span>
                                    </div>
                                </div>
                            </Link>
                        </div>

                        <div v-else class="text-center py-16 border border-dashed border-gray-300 rounded-sm bg-white">
                            <p class="text-slate-400 font-serif mb-4">No hay eventos activos.</p>
                            <Link :href="route('photographer.events.create')" 
                                class="text-xs font-bold uppercase tracking-widest text-slate-900 border-b border-slate-900 pb-1 hover:text-slate-600 hover:border-slate-600 transition">
                                Crear primer evento
                            </Link>
                        </div>
                    </div>

                    <div>
                        <div class="flex items-center justify-between mb-6 border-b border-gray-100 pb-2">
                            <h3 class="text-lg font-serif font-bold text-slate-900">Últimas Cargas</h3>
                            <Link :href="route('photographer.photos.index')" 
                                class="text-[10px] font-bold uppercase tracking-widest text-slate-400 hover:text-slate-900 transition flex items-center gap-1">
                                Ver Todo
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                            </Link>
                        </div>

                        <div v-if="recentPhotos && recentPhotos.length > 0" class="grid grid-cols-2 gap-4">
                            <div v-for="photo in recentPhotos" :key="photo.id"
                                class="aspect-square bg-gray-100 rounded-sm overflow-hidden relative group border border-gray-200">
                                <img :src="`/storage/${photo.thumbnail_path || photo.file_path}`" :alt="photo.unique_id"
                                    class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110 filter grayscale-[0.3] group-hover:grayscale-0"
                                    @error="(e) => e.target.src = 'https://via.placeholder.com/400?text=Error'" />
                                
                                <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-center justify-center">
                                    <Link :href="route('photographer.photos.show', photo.id)"
                                        class="text-white border border-white px-3 py-1 text-[10px] font-bold uppercase tracking-widest hover:bg-white hover:text-black transition">
                                        Ver
                                    </Link>
                                </div>
                            </div>
                        </div>

                        <div v-else class="text-center py-12 border border-dashed border-gray-300 rounded-sm bg-white">
                            <p class="text-slate-400 text-xs mb-4">Sin fotografías recientes.</p>
                            <Link :href="route('photographer.photos.create')" 
                                class="text-[10px] font-bold uppercase tracking-widest text-slate-900 border-b border-slate-900 pb-0.5 hover:text-slate-600 hover:border-slate-600 transition">
                                Subir material
                            </Link>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>