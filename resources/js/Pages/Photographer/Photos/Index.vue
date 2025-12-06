<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import {
    EyeIcon,
    TrashIcon,
    ArrowPathIcon,
    ArrowDownTrayIcon,
    PhotoIcon,
    ArchiveBoxIcon // Corregido: Primera letra mayúscula
} from '@heroicons/vue/24/outline';

const props = defineProps({
    photos: Object,
    events: Array,
    filters: Object,
    stats: Object,
});

const deletePhoto = (photoId) => {
    if (confirm('¿Confirmar eliminación definitiva de esta fotografía?')) {
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
</script>

<template>
    <Head title="Archivo Fotográfico" />

    <AuthenticatedLayout>
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

                <div class="flex flex-col md:flex-row md:items-end justify-between mb-10 border-b border-gray-200 pb-6 gap-4">
                    <div>
                        <span class="text-xs font-bold tracking-[0.2em] text-slate-400 uppercase mb-2 block">
                            Gestión de Activos
                        </span>
                        <h1 class="text-3xl font-serif font-bold text-slate-900">
                            Archivo Fotográfico
                        </h1>
                    </div>
                    <div class="flex gap-3">
                        <Link :href="route('photographer.events.index')" 
                            class="px-6 py-3 border border-slate-300 text-slate-600 text-xs font-bold uppercase tracking-widest hover:border-slate-900 hover:text-slate-900 transition rounded-sm">
                            Ver Eventos
                        </Link>
                        <Link :href="route('photographer.photos.create')" 
                            class="px-6 py-3 bg-slate-900 text-white text-xs font-bold uppercase tracking-widest hover:bg-slate-800 transition rounded-sm shadow-sm">
                            Subir Material
                        </Link>
                    </div>
                </div>

                <div v-if="stats" class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-12">
                    <div class="bg-white p-6 border border-gray-200 rounded-sm flex flex-col justify-between group hover:border-slate-300 transition-colors">
                        <div class="flex justify-between items-start mb-4">
                            <span class="text-[10px] font-bold uppercase tracking-widest text-slate-500">Total Archivos</span>
                            <PhotoIcon class="h-5 w-5 text-slate-300" />
                        </div>
                        <span class="text-3xl font-serif font-medium text-slate-900">{{ stats.total }}</span>
                    </div>

                    <div class="bg-white p-6 border border-gray-200 rounded-sm flex flex-col justify-between group hover:border-slate-300 transition-colors">
                        <div class="flex justify-between items-start mb-4">
                            <span class="text-[10px] font-bold uppercase tracking-widest text-slate-500">Visibles</span>
                            <div class="h-2 w-2 rounded-full bg-emerald-500"></div>
                        </div>
                        <span class="text-3xl font-serif font-medium text-slate-900">{{ stats.active }}</span>
                    </div>

                    <div class="bg-white p-6 border border-gray-200 rounded-sm flex flex-col justify-between group hover:border-slate-300 transition-colors">
                        <div class="flex justify-between items-start mb-4">
                            <span class="text-[10px] font-bold uppercase tracking-widest text-slate-500">Ocultas</span>
                            <div class="h-2 w-2 rounded-full bg-slate-300"></div>
                        </div>
                        <span class="text-3xl font-serif font-medium text-slate-900">{{ stats.inactive }}</span>
                    </div>

                    <div class="bg-white p-6 border border-gray-200 rounded-sm flex flex-col justify-between group hover:border-slate-300 transition-colors">
                        <div class="flex justify-between items-start mb-4">
                            <span class="text-[10px] font-bold uppercase tracking-widest text-slate-500">Descargas</span>
                            <ArrowDownTrayIcon class="h-5 w-5 text-slate-300" />
                        </div>
                        <span class="text-3xl font-serif font-medium text-slate-900">{{ stats.total_downloads }}</span>
                    </div>
                </div>

                <div>
                    <div v-if="!photos.data || photos.data.length === 0" class="text-center py-24 border border-dashed border-gray-300 rounded-sm bg-gray-50">
                        <PhotoIcon class="h-16 w-16 mx-auto text-gray-300 mb-4 stroke-1" />
                        <h4 class="text-lg font-serif font-medium text-slate-900 mb-2">Archivo vacío</h4>
                        <p class="text-sm text-slate-500 font-light mb-8 max-w-sm mx-auto">No hay fotografías cargadas en el sistema actualmente.</p>
                        <Link :href="route('photographer.photos.create')"
                            class="inline-block border-b border-slate-900 text-slate-900 text-xs font-bold uppercase tracking-widest pb-1 hover:text-slate-600 hover:border-slate-600 transition">
                            Iniciar Carga
                        </Link>
                    </div>

                    <div v-else>
                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-6 mb-12">
                            <div v-for="photo in photos.data" :key="photo.id"
                                class="bg-white border border-gray-200 rounded-sm overflow-hidden group hover:shadow-lg hover:border-slate-300 transition-all duration-300">
                                
                                <div class="relative aspect-square bg-gray-100 overflow-hidden border-b border-gray-100">
                                    <img :src="photo.thumbnail_url" :alt="photo.unique_id"
                                        class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500 filter grayscale-[0.3] group-hover:grayscale-0"
                                        loading="lazy" />

                                    <div class="absolute top-3 right-3">
                                        <div :class="[
                                            'h-2 w-2 rounded-full shadow-sm ring-2 ring-white',
                                            photo.is_active ? 'bg-emerald-500' : 'bg-slate-400'
                                        ]" :title="photo.is_active ? 'Visible' : 'Oculta'"></div>
                                    </div>

                                    <div class="absolute inset-0 bg-slate-900/60 opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-center justify-center">
                                        <div class="text-white text-center">
                                            <div class="flex items-center justify-center gap-1 mb-1">
                                                <ArrowDownTrayIcon class="h-4 w-4" />
                                                <span class="text-sm font-bold">{{ photo.downloads }}</span>
                                            </div>
                                            <span class="text-[9px] uppercase tracking-widest opacity-80">Descargas</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="p-4">
                                    <div class="mb-4">
                                        <div class="font-mono text-xs text-slate-400 mb-1">
                                            #{{ photo.unique_id }}
                                        </div>
                                        <div v-if="photo.event" class="text-sm font-serif font-bold text-slate-900 truncate" :title="photo.event.name">
                                            {{ photo.event.name }}
                                        </div>
                                        <div v-else class="text-xs text-slate-400 italic">Sin evento asignado</div>
                                    </div>

                                    <div class="flex items-center justify-between border-t border-gray-100 pt-3">
                                        <Link :href="route('photographer.photos.show', photo.id)"
                                            class="text-slate-400 hover:text-slate-900 transition" title="Ver Detalles">
                                            <EyeIcon class="h-4 w-4" />
                                        </Link>

                                        <div class="flex gap-3">
                                            <button @click.stop.prevent="toggleActive(photo)" 
                                                :class="[
                                                    'transition',
                                                    photo.is_active ? 'text-emerald-600 hover:text-emerald-800' : 'text-slate-400 hover:text-slate-600'
                                                ]"
                                                :title="photo.is_active ? 'Ocultar' : 'Publicar'">
                                                <ArrowPathIcon class="h-4 w-4" />
                                            </button>

                                            <button @click.stop.prevent="deletePhoto(photo.id)"
                                                class="text-red-400 hover:text-red-600 transition" title="Eliminar">
                                                <TrashIcon class="h-4 w-4" />
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div v-if="photos.last_page > 1" class="flex items-center justify-center gap-2 pt-8 border-t border-gray-200">
                            <template v-for="(link, index) in photos.links" :key="index">
                                <Link v-if="link.url" :href="link.url" 
                                    class="h-8 w-8 flex items-center justify-center text-xs font-medium rounded-sm transition-colors"
                                    :class="link.active 
                                        ? 'bg-slate-900 text-white' 
                                        : 'bg-white text-slate-600 hover:bg-gray-100 border border-gray-200'"
                                >
                                    <span v-html="link.label"></span>
                                </Link>
                                <span v-else v-html="link.label" class="h-8 w-8 flex items-center justify-center text-xs text-gray-300 cursor-not-allowed"></span>
                            </template>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>