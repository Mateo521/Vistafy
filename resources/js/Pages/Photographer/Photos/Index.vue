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

        <div class="py-12 bg-[#F2F0EB] min-h-screen text-black antialiased selection:bg-[#FF0000] selection:text-white">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">


                <div
                    class="flex flex-col md:flex-row md:items-end justify-between mb-12 border-b-8 border-black pb-8 gap-6">
                    <div>
                        <span
                            class="font-mono text-[#FF0000] text-sm font-bold uppercase mb-2 block tracking-widest flex items-center gap-2">
                            <div class="w-3 h-3 bg-[#FF0000] border-2 border-black animate-pulse"></div>
                            [ Gestión_de_Activos ]
                        </span>
                        <h1 class="text-5xl md:text-7xl font-black uppercase tracking-tighter text-black leading-none">
                            Archivo <br> Fotográfico
                        </h1>
                    </div>

                    <div class="flex gap-4 font-mono">
                        <Link :href="route('photographer.events.index')"
                            class="px-6 py-4 bg-white border-4 border-black text-black text-sm font-bold uppercase tracking-widest shadow-[6px_6px_0px_rgba(0,0,0,1)] hover:shadow-none hover:translate-x-[6px] hover:translate-y-[6px] transition-all rounded-none">
                            Ver Eventos
                        </Link>
                        <Link :href="route('photographer.photos.create')"
                            class="px-6 py-4 bg-[#FF0000] border-4 border-black text-white text-sm font-bold uppercase tracking-widest shadow-[6px_6px_0px_rgba(0,0,0,1)] hover:shadow-none hover:translate-x-[6px] hover:translate-y-[6px] transition-all rounded-none">
                            Subir Material
                        </Link>
                    </div>
                </div>


                <div v-if="stats" class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-12">

                    <div
                        class="bg-white p-6 border-4 border-black shadow-[6px_6px_0px_rgba(0,0,0,1)] flex flex-col justify-between group hover:bg-black hover:text-white transition-colors rounded-none">
                        <div class="flex justify-between items-start mb-4">
                            <span
                                class="font-mono text-xs font-bold uppercase tracking-widest text-gray-500 group-hover:text-[#FF0000]">Total_Archivos</span>
                            <PhotoIcon class="h-6 w-6 text-black group-hover:text-white" stroke-width="2" />
                        </div>
                        <span class="text-5xl font-black tracking-tighter">{{ stats.total }}</span>
                    </div>

                    <div
                        class="bg-white p-6 border-4 border-black shadow-[6px_6px_0px_rgba(0,0,0,1)] flex flex-col justify-between group hover:bg-[#FF0000] hover:text-white transition-colors rounded-none">
                        <div class="flex justify-between items-start mb-4">
                            <span
                                class="font-mono text-xs font-bold uppercase tracking-widest text-gray-500 group-hover:text-black">Visibles</span>
                            <div class="h-4 w-4 border-2 border-black bg-emerald-400 group-hover:border-white"></div>
                        </div>
                        <span class="text-5xl font-black tracking-tighter">{{ stats.active }}</span>
                    </div>

                    <div
                        class="bg-white p-6 border-4 border-black shadow-[6px_6px_0px_rgba(0,0,0,1)] flex flex-col justify-between group hover:bg-gray-300 transition-colors rounded-none">
                        <div class="flex justify-between items-start mb-4">
                            <span
                                class="font-mono text-xs font-bold uppercase tracking-widest text-gray-500">Ocultas</span>
                            <div class="h-4 w-4 border-2 border-black bg-gray-300 group-hover:bg-black"></div>
                        </div>
                        <span class="text-5xl font-black tracking-tighter">{{ stats.inactive }}</span>
                    </div>

                    <div
                        class="bg-[#FFC000] p-6 border-4 border-black shadow-[6px_6px_0px_rgba(0,0,0,1)] flex flex-col justify-between group hover:bg-black hover:text-[#FFC000] transition-colors rounded-none">
                        <div class="flex justify-between items-start mb-4">
                            <span
                                class="font-mono text-xs font-bold uppercase tracking-widest text-black group-hover:text-white">Descargas</span>
                            <ArrowDownTrayIcon class="h-6 w-6 text-black group-hover:text-[#FFC000]" stroke-width="2" />
                        </div>
                        <span class="text-5xl font-black tracking-tighter">{{ stats.total_downloads }}</span>
                    </div>
                </div>


                <div>

                    <div v-if="!photos.data || photos.data.length === 0"
                        class="text-center py-32 border-8 border-dashed border-black bg-white rounded-none shadow-[8px_8px_0px_rgba(0,0,0,1)]">
                        <PhotoIcon class="h-20 w-20 mx-auto text-black mb-6 stroke-1" />
                        <h4 class="text-3xl font-black uppercase tracking-tight text-black mb-2">Archivo Vacío</h4>
                        <p class="font-mono text-sm text-gray-500 mb-8 max-w-md mx-auto uppercase">Sistema inactivo. No
                            se detectaron fotografías en el almacenamiento.</p>
                        <Link :href="route('photographer.photos.create')"
                            class="inline-block border-b-4 border-black text-black font-mono text-lg font-bold uppercase tracking-widest pb-1 hover:text-[#FF0000] hover:border-[#FF0000] transition-colors">
                            Iniciar Carga //
                        </Link>
                    </div>


                    <div v-else>
                        <div
                            class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-8 mb-16">


                            <div v-for="photo in photos.data" :key="photo.id"
                                class="bg-white border-4 border-black rounded-none overflow-hidden group shadow-[6px_6px_0px_rgba(0,0,0,1)] hover:shadow-none hover:translate-x-[6px] hover:translate-y-[6px] transition-all duration-200 flex flex-col">


                                <div class="relative aspect-square bg-black overflow-hidden border-b-4 border-black">
                                    <img :src="photo.thumbnail_url" :alt="photo.unique_id"
                                        class="w-full h-full object-cover filter grayscale contrast-125 opacity-80 group-hover:grayscale-0 group-hover:opacity-100 group-hover:scale-105 transition-all duration-500"
                                        loading="lazy" />


                                    <div class="absolute top-3 right-3 z-10">
                                        <div :class="[
                                            'h-4 w-4 border-2 border-black rounded-none shadow-[2px_2px_0px_rgba(0,0,0,1)]',
                                            photo.is_active ? 'bg-emerald-400' : 'bg-gray-300'
                                        ]" :title="photo.is_active ? 'Visible' : 'Oculta'"></div>
                                    </div>


                                    <div
                                        class="absolute inset-0 bg-black/80 opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-center justify-center border-[8px] border-transparent group-hover:border-[#FF0000]">
                                        <div class="text-white text-center font-mono">
                                            <div
                                                class="flex items-center justify-center gap-2 mb-2 border-b-2 border-[#FF0000] pb-2 px-4">
                                                <ArrowDownTrayIcon class="h-5 w-5 text-[#FF0000]" stroke-width="2" />
                                                <span class="text-2xl font-black">{{ photo.downloads }}</span>
                                            </div>
                                            <span
                                                class="text-xs uppercase tracking-widest text-gray-300">Descargas</span>
                                        </div>
                                    </div>
                                </div>


                                <div class="p-4 flex flex-col flex-1 bg-[#F2F0EB]">
                                    <div class="mb-4">
                                        <div
                                            class="font-mono text-xs font-bold text-gray-500 mb-1 border-b-2 border-black w-fit pr-2 pb-1">
                                            ID_{{ photo.unique_id }}
                                        </div>
                                        <div v-if="photo.event"
                                            class="text-lg font-black uppercase text-black truncate tracking-tighter"
                                            :title="photo.event.name">
                                            {{ photo.event.name }}
                                        </div>
                                        <div v-else class="text-sm font-mono text-[#FF0000] uppercase font-bold">
                                            [ Sin Evento ]
                                        </div>
                                    </div>


                                    <div class="flex items-center justify-between border-t-4 border-black pt-4 mt-auto">
                                        <Link :href="route('photographer.photos.show', photo.id)"
                                            class="text-black hover:text-[#FF0000] transition-colors p-2 border-2 border-transparent hover:border-black"
                                            title="Ver Detalles">
                                            <EyeIcon class="h-6 w-6" stroke-width="2" />
                                        </Link>

                                        <div class="flex gap-2">
                                            <button @click.stop.prevent="toggleActive(photo)" :class="[
                                                'p-2 border-2 border-black transition-all rounded-none',
                                                photo.is_active ? 'bg-emerald-400 text-black hover:bg-emerald-500' : 'bg-white text-black hover:bg-gray-200'
                                            ]" :title="photo.is_active ? 'Ocultar' : 'Publicar'">
                                                <ArrowPathIcon class="h-5 w-5" stroke-width="2" />
                                            </button>

                                            <button @click.stop.prevent="deletePhoto(photo.id)"
                                                class="p-2 border-2 border-black bg-white text-black hover:bg-[#FF0000] hover:text-white transition-all rounded-none"
                                                title="Eliminar">
                                                <TrashIcon class="h-5 w-5" stroke-width="2" />
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>


                        <div v-if="photos.last_page > 1"
                            class="flex flex-wrap items-center justify-center gap-3 pt-12 border-t-8 border-black font-mono">


                            <Link v-if="photos.prev_page_url" :href="photos.prev_page_url"
                                class="h-12 px-6 flex items-center justify-center text-sm font-bold uppercase tracking-wider rounded-none transition-all bg-white text-black border-4 border-black shadow-[4px_4px_0px_rgba(0,0,0,1)] hover:shadow-none hover:translate-x-[4px] hover:translate-y-[4px]">
                                ← Prev
                            </Link>
                            <span v-else
                                class="h-12 px-6 flex items-center justify-center text-sm font-bold uppercase tracking-wider bg-gray-200 text-gray-400 border-4 border-gray-300 cursor-not-allowed">
                                ← Prev
                            </span>


                            <div class="flex items-center gap-2">
                                <template v-for="(page, index) in paginationPages" :key="index">

                                    <span v-if="page === photos.current_page"
                                        class="h-12 w-12 flex items-center justify-center text-lg font-black rounded-none bg-[#FF0000] text-white border-4 border-black shadow-[4px_4px_0px_rgba(0,0,0,1)]">
                                        {{ page }}
                                    </span>


                                    <span v-else-if="page === '...'"
                                        class="h-12 w-12 flex items-center justify-center text-black font-black text-xl">
                                        ...
                                    </span>


                                    <Link v-else :href="photos.path + '?page=' + page"
                                        class="h-12 w-12 flex items-center justify-center text-lg font-bold rounded-none transition-all bg-white text-black border-4 border-black shadow-[4px_4px_0px_rgba(0,0,0,1)] hover:shadow-none hover:translate-x-[4px] hover:translate-y-[4px]">
                                        {{ page }}
                                    </Link>
                                </template>
                            </div>


                            <Link v-if="photos.next_page_url" :href="photos.next_page_url"
                                class="h-12 px-6 flex items-center justify-center text-sm font-bold uppercase tracking-wider rounded-none transition-all bg-white text-black border-4 border-black shadow-[4px_4px_0px_rgba(0,0,0,1)] hover:shadow-none hover:translate-x-[4px] hover:translate-y-[4px]">
                                Sig →
                            </Link>
                            <span v-else
                                class="h-12 px-6 flex items-center justify-center text-sm font-bold uppercase tracking-wider bg-gray-200 text-gray-400 border-4 border-gray-300 cursor-not-allowed">
                                Sig →
                            </span>
                        </div>

                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>