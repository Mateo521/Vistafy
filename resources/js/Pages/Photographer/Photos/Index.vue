<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { computed } from 'vue';
import {
    EyeIcon,
    TrashIcon,
    EyeSlashIcon,
    ArrowDownTrayIcon,
    PhotoIcon,
    ArchiveBoxIcon,
    CheckBadgeIcon,
    PlusIcon
} from '@heroicons/vue/24/outline';

const props = defineProps({
    photos: Object,
    events: Array,
    filters: Object,
    stats: Object,
});

const deletePhoto = (photoId) => {
    if (confirm('ATENCIÓN: ¿Confirmar eliminación definitiva de este activo digital? La acción es irreversible.')) {
        router.delete(route('photographer.photos.destroy', photoId), {
            preserveScroll: true,
        });
    }
};

const toggleActive = (photo) => {
    router.put(route('photographer.photos.update', photo.id), {
        is_active: !photo.is_active,
        price: photo.price,
    }, {
        preserveScroll: true,
    });
};

const formatFileSize = (bytes) => {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['B', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
};

const paginationPages = computed(() => {
    const current = props.photos.current_page;
    const last = props.photos.last_page;
    const delta = 2;
    const pages = [];

    pages.push(1);

    const rangeStart = Math.max(2, current - delta);
    const rangeEnd = Math.min(last - 1, current + delta);

    if (rangeStart > 2) {
        pages.push('...');
    }

    for (let i = rangeStart; i <= rangeEnd; i++) {
        pages.push(i);
    }

    if (rangeEnd < last - 1) {
        pages.push('...');
    }

    if (last > 1) {
        pages.push(last);
    }

    return pages;
});

const handleImageError = (e) => {
    e.target.style.display = 'none';
    const parent = e.target.parentElement;
    if (!parent.querySelector('.placeholder-institutional')) {
        const placeholder = document.createElement('div');
        placeholder.className = 'placeholder-institutional w-full h-full flex flex-col items-center justify-center bg-gray-50 border-b border-gray-100 text-gray-300';
        placeholder.innerHTML = `
            <svg class="w-10 h-10 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
            <span class="text-xs font-bold uppercase tracking-wider text-gray-400">Sin Imagen</span>
        `;
        parent.appendChild(placeholder);
    }
};
</script>

<template>
    <Head title="Archivo fotográfico" />

    <AuthenticatedLayout>
        <div class="min-h-screen bg-[#F8F9FA] text-slate-800 font-sans antialiased py-12 pt-28">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

                
                <div class="flex flex-col md:flex-row md:items-end justify-between mb-10 border-b border-gray-200 pb-8 gap-6">
                    <div>
                        <span class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-2 flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-[#E30613] animate-pulse"></span>
                            Gestión de activos
                        </span>
                        <h1 class="text-5xl md:text-7xl font-flux text-black tracking-wide leading-none">
                            Archivo <span class="text-[#E30613]">Fotográfico</span>
                        </h1>
                    </div>
                    
                    <div class="flex flex-wrap gap-3">
                        <Link :href="route('photographer.events.index')"
                            class="px-6 py-3.5 bg-white border border-gray-200 text-gray-600 rounded-full text-xs font-bold uppercase tracking-wider hover:bg-gray-50 hover:text-black transition-colors flex items-center justify-center">
                            Ver eventos
                        </Link>
                        <Link :href="route('photographer.photos.create')"
                            class="px-6 py-3.5 bg-black text-white rounded-full text-xs font-bold uppercase tracking-wider hover:bg-[#E30613] hover:shadow-lg hover:shadow-red-500/30 transition-all flex items-center justify-center gap-2">
                            <PlusIcon class="w-4 h-4" /> Subir Material
                        </Link>
                    </div>
                </div>

            
                <div v-if="stats" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4 md:gap-6 mb-12">
                    
                
                    <div class="bg-white p-6 md:p-8 rounded border border-gray-100 flex flex-col justify-between group hover:shadow-md hover:-translate-y-1 transition-all duration-300">
                        <div class="flex justify-between items-start mb-4">
                            <div class="w-10 h-10 rounded-full bg-gray-50 flex items-center justify-center group-hover:bg-red-50 transition-colors">
                                <ArchiveBoxIcon class="h-5 w-5 text-gray-400 group-hover:text-black" />
                            </div>
                        </div>
                        <div>
                            <span class="text-4xl md:text-5xl font-flux text-black block mb-1">{{ stats.total }}</span>
                            <span class="text-xs font-bold uppercase tracking-wider text-gray-500">Archivos Totales</span>
                        </div>
                    </div>

                    
                    <div class="bg-white p-6 md:p-8 rounded border border-gray-100 flex flex-col justify-between group hover:shadow-md hover:-translate-y-1 transition-all duration-300">
                        <div class="flex justify-between items-start mb-4">
                            <div class="w-10 h-10 rounded-full bg-gray-50 flex items-center justify-center group-hover:bg-green-50 transition-colors">
                                <CheckBadgeIcon class="h-5 w-5 text-gray-400 group-hover:text-green-600" />
                            </div>
                        </div>
                        <div>
                            <span class="text-4xl md:text-5xl font-flux text-black block mb-1">{{ stats.active }}</span>
                            <span class="text-xs font-bold uppercase tracking-wider text-gray-500">Activos visibles</span>
                        </div>
                    </div>

                    
                    <div class="bg-white p-6 md:p-8 rounded border border-gray-100 flex flex-col justify-between group hover:shadow-md hover:-translate-y-1 transition-all duration-300">
                        <div class="flex justify-between items-start mb-4">
                            <div class="w-10 h-10 rounded-full bg-gray-50 flex items-center justify-center group-hover:bg-gray-100 transition-colors">
                                <EyeSlashIcon class="h-5 w-5 text-gray-400 group-hover:text-gray-600" />
                            </div>
                        </div>
                        <div>
                            <span class="text-4xl md:text-5xl font-flux text-gray-400 block mb-1">{{ stats.inactive }}</span>
                            <span class="text-xs font-bold uppercase tracking-wider text-gray-400">Ocultas / borrador</span>
                        </div>
                    </div>

                    
                    <div class="bg-white p-6 md:p-8 rounded border border-gray-100 flex flex-col justify-between group hover:shadow-md hover:-translate-y-1 transition-all duration-300">
                        <div class="flex justify-between items-start mb-4">
                            <div class="w-10 h-10 rounded-full bg-gray-50 flex items-center justify-center group-hover:bg-red-50 transition-colors">
                                <ArrowDownTrayIcon class="h-5 w-5 text-gray-400 group-hover:text-[#E30613]" />
                            </div>
                        </div>
                        <div>
                            <span class="text-4xl md:text-5xl font-flux text-[#E30613] block mb-1">{{ stats.total_downloads }}</span>
                            <span class="text-xs font-bold uppercase tracking-wider text-gray-500">Descargas totales</span>
                        </div>
                    </div>
                </div>

            
                <div v-if="!photos.data || photos.data.length === 0"
                    class="text-center py-24 bg-white rounded border border-gray-100 shadow-sm flex flex-col items-center">
                    <div class="w-20 h-20 bg-gray-50 rounded-full flex items-center justify-center mb-6">
                        <PhotoIcon class="h-10 w-10 text-gray-300" />
                    </div>
                    <h4 class="text-4xl font-flux text-black mb-3">Bóveda vacía</h4>
                    <p class="text-sm font-medium text-gray-500 mb-8 max-w-md mx-auto">
                        No se detectaron fotografías en el almacenamiento. Iniciá la carga de archivos para empezar.
                    </p>
                    <Link :href="route('photographer.photos.create')"
                        class="inline-block bg-black text-white px-8 py-3.5 rounded-full text-xs font-bold uppercase tracking-wider hover:bg-[#E30613] hover:shadow-lg hover:shadow-red-500/30 hover:-translate-y-1 transition-all">
                        Subir Fotografías
                    </Link>
                </div>

            
                <div v-else>
                    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4 lg:gap-6 mb-16">

                        <div v-for="photo in photos.data" :key="photo.id"
                            class="bg-white rounded border border-gray-100 shadow-sm overflow-hidden group hover:shadow-lg hover:-translate-y-1 transition-all duration-300 flex flex-col">

                        
                            <div class="relative aspect-square bg-gray-100 overflow-hidden shrink-0">
                                <img :src="photo.thumbnail_url" :alt="photo.unique_id"
                                    class="w-full h-full object-cover transition-transform duration-700 "
                                    loading="lazy" @error="handleImageError" />

                            
                                <div class="absolute top-3 left-3 z-10">
                                    <span :class="[
                                        'px-2.5 py-1 rounded-md text-[10px] font-bold uppercase tracking-wider shadow-sm backdrop-blur-md border border-white/20',
                                        photo.is_active ? 'bg-green-500/90 text-white' : 'bg-gray-800/90 text-white'
                                    ]" :title="photo.is_active ? 'Visible en galería' : 'Oculto al público'">
                                        {{ photo.is_active ? 'Visible' : 'Oculta' }}
                                    </span>
                                </div>

                            
                                <div class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-center justify-center gap-3">
                                    <Link :href="route('photographer.photos.show', photo.id)" title="Inspeccionar"
                                        class="w-10 h-10 bg-white/20 backdrop-blur-md rounded-full flex items-center justify-center text-white hover:bg-white hover:text-black transition-colors">
                                        <EyeIcon class="h-5 w-5" />
                                    </Link>
                                    <button @click.stop.prevent="toggleActive(photo)" :title="photo.is_active ? 'Ocultar foto' : 'Publicar foto'"
                                        class="w-10 h-10 bg-white/20 backdrop-blur-md rounded-full flex items-center justify-center text-white hover:bg-white hover:text-black transition-colors">
                                        <EyeSlashIcon v-if="photo.is_active" class="h-5 w-5" />
                                        <CheckBadgeIcon v-else class="h-5 w-5" />
                                    </button>
                                    <button @click.stop.prevent="deletePhoto(photo.id)" title="Borrar foto"
                                        class="w-10 h-10 bg-[#E30613]/80 backdrop-blur-md rounded-full flex items-center justify-center text-white hover:bg-[#E30613] transition-colors">
                                        <TrashIcon class="h-5 w-5" />
                                    </button>
                                </div>
                            </div>

                        
                            <div class="p-4 flex flex-col flex-1 bg-white">
                                <div class="flex justify-between items-start mb-3">
                                    <div class="font-mono text-[10px] font-bold text-gray-400 uppercase tracking-widest bg-gray-50 px-2 py-1 rounded">
                                        #{{ photo.unique_id.substring(0,6) }}
                                    </div>
                                    <div v-if="photo.downloads > 0" class="flex items-center gap-1 text-[10px] font-bold text-[#E30613]">
                                        <ArrowDownTrayIcon class="h-3 w-3" /> {{ photo.downloads }}
                                    </div>
                                </div>
                                
                                <div v-if="photo.event" class="text-sm font-bold text-slate-700 truncate group-hover:text-[#E30613] transition-colors" :title="photo.event.name">
                                    {{ photo.event.name }}
                                </div>
                                <div v-else class="text-xs font-bold text-gray-400 italic">
                                    Sin evento asignado
                                </div>
                            </div>
                        </div>

                    </div>

                
                    <div v-if="photos.last_page > 1" class="mt-16 flex justify-center">
                        <div class="flex flex-wrap items-center gap-2 bg-white p-2 rounded-full shadow-sm border border-gray-100">
                            
                            
                            <Link v-if="photos.prev_page_url" :href="photos.prev_page_url"
                                class="h-10 px-4 flex items-center justify-center text-xs font-bold uppercase tracking-wider text-gray-600 hover:bg-gray-100 rounded-full transition-colors">
                                Ant
                            </Link>
                            <span v-else class="h-10 px-4 flex items-center justify-center text-xs font-bold uppercase tracking-wider text-gray-300 cursor-not-allowed">Ant</span>

                            
                            <template v-for="(page, index) in paginationPages" :key="index">
                                <span v-if="page === photos.current_page"
                                    class="h-10 w-10 flex items-center justify-center text-xs font-bold bg-black text-white rounded-full shadow-md">
                                    {{ page }}
                                </span>
                                <span v-else-if="page === '...'" class="h-10 w-10 flex items-center justify-center text-xs text-gray-400 font-bold">
                                    ...
                                </span>
                                <Link v-else :href="photos.path + '?page=' + page"
                                    class="h-10 w-10 flex items-center justify-center text-xs font-bold text-gray-600 hover:bg-gray-100 rounded-full transition-colors">
                                    {{ page }}
                                </Link>
                            </template>

                        
                            <Link v-if="photos.next_page_url" :href="photos.next_page_url"
                                class="h-10 px-4 flex items-center justify-center text-xs font-bold uppercase tracking-wider text-gray-600 hover:bg-gray-100 rounded-full transition-colors">
                                Sig
                            </Link>
                            <span v-else class="h-10 px-4 flex items-center justify-center text-xs font-bold uppercase tracking-wider text-gray-300 cursor-not-allowed">Sig</span>
                            
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>