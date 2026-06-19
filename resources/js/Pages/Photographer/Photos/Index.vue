<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { computed } from 'vue';
import {
    EyeIcon,
    TrashIcon,
    ArrowPathIcon,
    ArrowDownTrayIcon,
    PhotoIcon,
    ArchiveBoxIcon
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
</script>

<template>

    <Head title="Archivo Fotográfico" />

    <AuthenticatedLayout>

        <div class="py-12 bg-[#050505] min-h-screen text-white antialiased selection:bg-[#E30613] selection:text-black">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

                <div class="flex flex-col md:flex-row md:items-end justify-between mb-12 border-b border-zinc-800 pb-8 gap-6">
                    <div>
                        <span class="font-mono text-[#E30613] text-[10px] font-bold uppercase mb-2 block tracking-widest flex items-center gap-2">
                            <span class="w-2 h-2 bg-[#E30613] animate-pulse"></span>
                            >_ GESTIÓN_DE_ACTIVOS
                        </span>
                        <h1 class="text-5xl md:text-7xl font-flux uppercase tracking-tighter text-white leading-none">
                            Archivo <br> Fotográfico
                        </h1>
                    </div>

                    <div class="flex flex-wrap gap-4 font-mono">
                        <Link :href="route('photographer.events.index')"
                            class="px-6 py-4 bg-transparent border border-zinc-700 text-zinc-400 text-[10px] font-bold uppercase tracking-widest hover:border-white hover:text-white transition-colors rounded-none text-center">
                            VER_EVENTOS
                        </Link>
                        <Link :href="route('photographer.photos.create')"
                            class="px-6 py-4 bg-[#E30613] border border-[#E30613] text-black text-[10px] font-bold uppercase tracking-widest hover:bg-white hover:border-white transition-colors rounded-none text-center">
                            INICIALIZAR_CARGA
                        </Link>
                    </div>
                </div>

                <div v-if="stats" class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-12">
                    <div class="bg-[#09090b] p-6 border border-zinc-800 flex flex-col justify-between group hover:border-white transition-colors rounded-none shadow-[4px_4px_0px_0px_rgba(255,255,255,0.05)]">
                        <div class="flex justify-between items-start mb-4">
                            <span class="font-mono text-[10px] font-bold uppercase tracking-widest text-zinc-500 group-hover:text-white transition-colors">Total_Archivos</span>
                            <PhotoIcon class="h-5 w-5 text-zinc-700 group-hover:text-white transition-colors" />
                        </div>
                        <span class="text-5xl font-flux tracking-tighter">{{ stats.total }}</span>
                    </div>

                    <div class="bg-[#09090b] p-6 border border-zinc-800 flex flex-col justify-between group hover:border-emerald-500 transition-colors rounded-none shadow-[4px_4px_0px_0px_rgba(255,255,255,0.05)]">
                        <div class="flex justify-between items-start mb-4">
                            <span class="font-mono text-[10px] font-bold uppercase tracking-widest text-zinc-500 group-hover:text-emerald-500 transition-colors">Visibles</span>
                            <div class="h-2 w-2 rounded-none bg-emerald-500 mt-1.5 animate-pulse"></div>
                        </div>
                        <span class="text-5xl font-flux tracking-tighter">{{ stats.active }}</span>
                    </div>

                    <div class="bg-[#09090b] p-6 border border-zinc-800 flex flex-col justify-between group hover:border-zinc-500 transition-colors rounded-none shadow-[4px_4px_0px_0px_rgba(255,255,255,0.05)]">
                        <div class="flex justify-between items-start mb-4">
                            <span class="font-mono text-[10px] font-bold uppercase tracking-widest text-zinc-500 group-hover:text-zinc-400 transition-colors">Ocultas</span>
                            <div class="h-2 w-2 rounded-none bg-zinc-600 mt-1.5 group-hover:bg-zinc-400 transition-colors"></div>
                        </div>
                        <span class="text-5xl font-flux tracking-tighter text-zinc-500">{{ stats.inactive }}</span>
                    </div>

                    <div class="bg-[#09090b] p-6 border border-[#E30613] flex flex-col justify-between group hover:bg-[#E30613] transition-colors rounded-none shadow-[4px_4px_0px_0px_rgba(227,6,19,1)]">
                        <div class="flex justify-between items-start mb-4">
                            <span class="font-mono text-[10px] font-bold uppercase tracking-widest text-[#E30613] group-hover:text-black transition-colors">Descargas</span>
                            <ArrowDownTrayIcon class="h-5 w-5 text-[#E30613] group-hover:text-black transition-colors" />
                        </div>
                        <span class="text-5xl font-flux tracking-tighter text-white group-hover:text-black transition-colors">{{ stats.total_downloads }}</span>
                    </div>
                </div>

                <div>
                    <div v-if="!photos.data || photos.data.length === 0"
                        class="text-center py-32 border-2 border-dashed border-zinc-800 bg-[#09090b] rounded-none">
                        <PhotoIcon class="h-20 w-20 mx-auto text-zinc-800 mb-6 stroke-1" />
                        <h4 class="text-3xl font-flux uppercase tracking-widest text-zinc-500 mb-2">SISTEMA VACÍO // 0 ACTIVOS</h4>
                        <p class="font-mono text-xs text-zinc-600 mb-8 max-w-md mx-auto uppercase tracking-widest">
                            No se detectaron fotografías en el almacenamiento. Inicie la carga de archivos.
                        </p>
                        <Link :href="route('photographer.photos.create')"
                            class="inline-block border border-zinc-700 bg-black text-white px-8 py-3 font-mono text-[10px] font-bold uppercase tracking-widest hover:border-[#E30613] hover:text-[#E30613] transition-colors">
                            INICIAR CARGA
                        </Link>
                    </div>

                    <div v-else>
                        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4 lg:gap-8 mb-16">

                            <div v-for="photo in photos.data" :key="photo.id"
                                class="bg-[#09090b] border border-zinc-800 rounded-none overflow-hidden group hover:border-[#E30613] transition-all duration-300 flex flex-col">

                                <div class="relative aspect-square bg-zinc-950 overflow-hidden border-b border-zinc-800">
                                    <img :src="photo.thumbnail_url" :alt="photo.unique_id"
                                        class="w-full h-full object-cover filter grayscale opacity-60 group-hover:grayscale-0 group-hover:opacity-100 group-hover:scale-105 transition-all duration-500"
                                        loading="lazy" />

                                    <div class="absolute top-3 right-3 z-10 flex gap-2">
                                        <div :class="[
                                            'h-2 w-2 rounded-none shadow-[2px_2px_0px_rgba(0,0,0,0.5)]',
                                            photo.is_active ? 'bg-emerald-500' : 'bg-zinc-600'
                                        ]" :title="photo.is_active ? 'Visible' : 'Oculta'"></div>
                                    </div>

                                    <div class="absolute inset-0 bg-[#E30613]/0 group-hover:bg-[#E30613]/20 transition-colors duration-300 pointer-events-none mix-blend-multiply"></div>
                                    
                                    <div class="absolute inset-0 bg-gradient-to-t from-black via-black/40 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-center justify-center pointer-events-none">
                                        <div class="text-white text-center font-mono transform translate-y-4 group-hover:translate-y-0 transition-transform duration-300">
                                            <div class="flex items-center justify-center gap-2 mb-2 border-b border-[#E30613] pb-2 px-4">
                                                <ArrowDownTrayIcon class="h-4 w-4 text-[#E30613]" />
                                                <span class="text-3xl font-flux">{{ photo.downloads }}</span>
                                            </div>
                                            <span class="text-[9px] uppercase tracking-widest text-zinc-400">DESCARGAS</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="p-4 flex flex-col flex-1 bg-[#09090b]">
                                    <div class="mb-4">
                                        <div class="font-mono text-[9px] font-bold text-zinc-500 mb-1 border-b border-zinc-800 w-fit pr-2 pb-1 uppercase tracking-widest">
                                            ID_{{ photo.unique_id }}
                                        </div>
                                        <div v-if="photo.event"
                                            class="text-sm font-mono uppercase text-white truncate tracking-widest mt-2 group-hover:text-[#E30613] transition-colors"
                                            :title="photo.event.name">
                                            > {{ photo.event.name }}
                                        </div>
                                        <div v-else class="text-[10px] font-mono text-[#E30613] uppercase font-bold tracking-widest mt-2">
                                            [ SIN VÍNCULO ]
                                        </div>
                                    </div>

                                    <div class="flex items-center justify-between border-t border-zinc-800 pt-4 mt-auto">
                                        <Link :href="route('photographer.photos.show', photo.id)"
                                            class="w-8 h-8 flex items-center justify-center bg-black border border-zinc-700 text-zinc-400 hover:text-white hover:border-white transition-colors"
                                            title="Inspeccionar">
                                            <EyeIcon class="h-4 w-4" />
                                        </Link>

                                        <div class="flex gap-2">
                                            <button @click.stop.prevent="toggleActive(photo)" :class="[
                                                'w-8 h-8 flex items-center justify-center border transition-colors',
                                                photo.is_active ? 'bg-emerald-500/10 border-emerald-500 text-emerald-500 hover:bg-emerald-500 hover:text-black' : 'bg-black border-zinc-700 text-zinc-400 hover:border-white hover:text-white'
                                            ]" :title="photo.is_active ? 'Ocultar Activo' : 'Publicar Activo'">
                                                <ArrowPathIcon class="h-4 w-4" />
                                            </button>

                                            <button @click.stop.prevent="deletePhoto(photo.id)"
                                                class="w-8 h-8 flex items-center justify-center bg-black border border-zinc-700 text-zinc-400 hover:bg-[#E30613] hover:border-[#E30613] hover:text-black transition-colors"
                                                title="Purgar">
                                                <TrashIcon class="h-4 w-4" />
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div v-if="photos.last_page > 1"
                            class="flex flex-wrap items-center justify-center gap-2 pt-12 border-t border-zinc-800 font-mono">

                            <Link v-if="photos.prev_page_url" :href="photos.prev_page_url"
                                class="h-10 px-4 flex items-center justify-center text-[10px] font-bold uppercase tracking-widest rounded-none transition-colors bg-black text-zinc-400 border border-zinc-800 hover:border-white hover:text-white">
                                ← PREV
                            </Link>
                            <span v-else
                                class="h-10 px-4 flex items-center justify-center text-[10px] font-bold uppercase tracking-widest bg-black text-zinc-700 border border-transparent cursor-not-allowed">
                                ← PREV
                            </span>

                            <div class="flex items-center gap-2">
                                <template v-for="(page, index) in paginationPages" :key="index">
                                    <span v-if="page === photos.current_page"
                                        class="h-10 min-w-[2.5rem] px-2 flex items-center justify-center text-[10px] font-bold rounded-none bg-[#E30613] text-black border border-[#E30613]">
                                        {{ page }}
                                    </span>

                                    <span v-else-if="page === '...'"
                                        class="h-10 min-w-[2.5rem] flex items-center justify-center text-zinc-600 font-bold text-xs">
                                        ...
                                    </span>

                                    <Link v-else :href="photos.path + '?page=' + page"
                                        class="h-10 min-w-[2.5rem] px-2 flex items-center justify-center text-[10px] font-bold rounded-none transition-colors bg-black text-zinc-400 border border-zinc-800 hover:border-white hover:text-white">
                                        {{ page }}
                                    </Link>
                                </template>
                            </div>

                            <Link v-if="photos.next_page_url" :href="photos.next_page_url"
                                class="h-10 px-4 flex items-center justify-center text-[10px] font-bold uppercase tracking-widest rounded-none transition-colors bg-black text-zinc-400 border border-zinc-800 hover:border-white hover:text-white">
                                SIG →
                            </Link>
                            <span v-else
                                class="h-10 px-4 flex items-center justify-center text-[10px] font-bold uppercase tracking-widest bg-black text-zinc-700 border border-transparent cursor-not-allowed">
                                SIG →
                            </span>
                        </div>

                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>