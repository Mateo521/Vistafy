<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import { ArrowLeftIcon, FunnelIcon, UserIcon } from '@heroicons/vue/24/outline';

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
</script>

<template>

    <Head :title="event.name" />

    <AppLayout>
        <div class="relative h-[60vh] min-h-[400px] bg-slate-900 overflow-hidden">
            <img v-if="event.cover_image_url" :src="event.cover_image_url" :alt="event.name"
                class="absolute inset-0 w-full h-full object-cover opacity-60"
                @error="(e) => e.target.style.display = 'none'" />

            <div class="absolute inset-0 bg-gradient-to-t from-slate-900 via-transparent to-slate-900/30"></div>

            <div class="absolute top-24 left-0 w-full px-4 sm:px-6 lg:px-8 z-10">
                <Link :href="route('events.index')"
                    class="inline-flex items-center text-white/70 hover:text-white text-xs font-bold uppercase tracking-widest transition-colors">
                    <ArrowLeftIcon class="w-4 h-4 mr-2" />
                    Volver al Archivo
                </Link>
            </div>

            <div class="absolute bottom-0 left-0 w-full p-8 md:p-12">
                <div class="max-w-7xl mx-auto">
                    <span class="text-xs font-bold tracking-[0.2em] text-white/60 uppercase mb-2 block">
                        {{ formatDate(event.event_date) }}
                    </span>
                    <h1 class="text-4xl md:text-6xl font-serif font-bold text-white mb-4 max-w-7xl leading-tight">
                        {{ event.name }}
                    </h1>
                    <div class="flex flex-wrap items-center gap-6 text-white/80 font-light text-sm">
                        <span v-if="event.location">{{ event.location }}</span>
                        <span class="w-1 h-1 bg-white/50 rounded-full"></span>
                        <span>{{ event.photos_count }} Fotografías</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="min-h-screen bg-white border-t border-gray-100">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">

                <div class="flex flex-col lg:flex-row gap-12 mb-12 border-b border-gray-100 pb-12">

                    <div class="lg:w-2/3">
                        <h3 class="text-xs font-bold uppercase tracking-widest text-slate-900 mb-4">Sobre el Evento</h3>
                        <p class="text-slate-600 leading-relaxed font-light text-lg mb-4">
                            {{ event.description }}
                        </p>
                        <p v-if="event.long_description" class="text-slate-500 leading-relaxed text-sm">
                            {{ event.long_description }}
                        </p>
                    </div>

                    <div class="lg:w-1/3 flex flex-col gap-3">
                        <div class="bg-gray-50 p-6 rounded-sm border border-gray-200">
                            <h3
                                class="text-xs font-bold uppercase tracking-widest text-slate-900 mb-4 flex items-center gap-2">
                                <FunnelIcon class="w-4 h-4" /> Filtrar Contenido
                            </h3>

                            <div class="space-y-3">
                                <label class="flex items-center cursor-pointer group">
                                    <input type="radio" v-model="selectedPhotographer" value="all"
                                        @change="filterByPhotographer" class="peer sr-only">
                                    <div
                                        class="w-4 h-4 rounded-full border border-slate-300 mr-3 peer-checked:bg-slate-900 peer-checked:border-slate-900 flex items-center justify-center transition-colors">
                                        <div
                                            class="w-1.5 h-1.5 bg-white rounded-full opacity-0 peer-checked:opacity-100">
                                        </div>
                                    </div>
                                    <span
                                        class="text-sm text-slate-600 peer-checked:text-slate-900 peer-checked:font-medium transition-colors">Mostrar
                                        Todo</span>
                                </label>

                                <label v-for="photographer in photographers" :key="photographer.id"
                                    class="flex items-center cursor-pointer group">
                                    <input type="radio" v-model="selectedPhotographer" :value="photographer.id"
                                        @change="filterByPhotographer" class="peer sr-only">
                                    <div
                                        class="w-4 h-4 rounded-full border border-slate-300 mr-3 peer-checked:bg-slate-900 peer-checked:border-slate-900 flex items-center justify-center transition-colors">
                                        <div
                                            class="w-1.5 h-1.5 bg-white rounded-full opacity-0 peer-checked:opacity-100">
                                        </div>
                                    </div>
                                    <span
                                        class="text-sm text-slate-600 peer-checked:text-slate-900 peer-checked:font-medium transition-colors flex items-center gap-2">
                                        {{ photographer.business_name || photographer.user.name }}
                                        <span
                                            class="text-[10px] text-slate-400 bg-white border border-gray-200 px-1.5 rounded-sm">{{
                                            photographer.photos_count }}</span>
                                    </span>
                                </label>
                            </div>
                        </div>
                        <Link :href="route('events.face-search.show', event.slug)"
                            class="inline-flex items-center gap-2 bg-gray-600 text-center hover:bg-gray-700 text-white font-medium px-6 py-3 rounded transition-colors shadow-md hover:shadow-lg">
                            <MagnifyingGlassIcon class="w-5 h-5" />
                            <span>Buscar por Rostro</span>
                        </Link>
                    </div>
                </div>

                <div v-if="photos.data && photos.data.length > 0">
                    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 mb-12">
                        <Link v-for="photo in photos.data" :key="photo.id"
                            :href="route('gallery.show', photo.unique_id)"
                            class="group relative aspect-square overflow-hidden bg-gray-100 rounded-sm">
                            <img :src="photo.thumbnail_url" :alt="photo.unique_id"
                                class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105 filter grayscale-[0.3] group-hover:grayscale-0"
                                loading="lazy" />

                            <div
                                class="absolute inset-0 bg-slate-900/60 opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex flex-col justify-end p-4">
                                <span class="text-xs font-bold text-white uppercase tracking-widest mb-1">Ver
                                    Foto</span>
                                <span class="text-[10px] text-white/70 font-mono">#{{ photo.unique_id }}</span>
                            </div>

                            <div
                                class="absolute top-2 right-2 bg-white/90 backdrop-blur-sm px-2 py-1 rounded-sm text-[10px] font-bold uppercase tracking-wider text-slate-900 opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                                {{ photo.photographer_name }}
                            </div>
                        </Link>
                    </div>

                    <div v-if="photos.last_page > 1" class="flex justify-center gap-2 pt-8 border-t border-gray-100">
                        <Link v-for="(link, index) in photos.links" :key="index" :href="link.url || '#'"
                            v-html="link.label"
                            :class="['h-10 min-w-[2.5rem] px-3 flex items-center justify-center text-sm font-medium rounded-sm border transition-colors',
                                link.active ? 'bg-slate-900 text-white border-slate-900' : 'bg-white text-slate-600 border-gray-200 hover:border-slate-400']" />
                    </div>
                </div>

                <div v-else class="text-center py-32 border border-dashed border-gray-200 bg-gray-50 rounded-sm">
                    <p class="text-slate-400 font-serif text-lg">No hay fotografías disponibles con los filtros
                        seleccionados.</p>
                    <button v-if="selectedPhotographer !== 'all'"
                        @click="selectedPhotographer = 'all'; filterByPhotographer()"
                        class="mt-4 text-xs font-bold uppercase tracking-widest text-slate-900 border-b border-slate-900 pb-1 hover:text-slate-600 hover:border-slate-600 transition">
                        Ver todo el material
                    </button>
                </div>

            </div>
        </div>
    </AppLayout>
</template>