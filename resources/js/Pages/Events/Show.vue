<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import { ArrowLeftIcon, FunnelIcon, MagnifyingGlassIcon, HashtagIcon } from '@heroicons/vue/24/outline';

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

// Actualizado para encajar con el Dark Theme
const handleImageError = (e) => {
    e.target.style.display = 'none';
    const parent = e.target.parentElement;
    if (!parent.querySelector('.placeholder-institutional')) {
        const placeholder = document.createElement('div');
        placeholder.className = 'placeholder-institutional w-full h-full flex items-center justify-center bg-[#111920]';
        placeholder.innerHTML = `
            <span class="font-['Cormorant_Garamond'] text-4xl italic opacity-20 text-[#EEE9DF]">f33</span>
        `;
        parent.appendChild(placeholder);
    }
};
</script>

<template>

    <Head :title="event.name" />

    <AppLayout>
        <div class="relative h-[60vh] min-h-[500px] bg-[#111920] overflow-hidden font-['Syne']">
            <img v-if="event.cover_image_url" :src="event.cover_image_url" :alt="event.name"
                class="absolute inset-0 w-full h-full object-cover opacity-50 saturate-[0.6]"
                @error="handleImageError" />

            <div class="absolute inset-0 bg-gradient-to-t from-[#111920] via-[#111920]/40 to-transparent"></div>

            <div class="absolute top-32 left-0 w-full px-8 md:px-16 max-w-7xl mx-auto z-10 left-0 right-0">
                <Link :href="route('events.index')"
                    class="inline-flex items-center text-[#C9C1B1] hover:text-[#FFB162] text-[10px] font-bold uppercase tracking-[0.2em] transition-colors group">
                    <ArrowLeftIcon class="w-4 h-4 mr-2 group-hover:-translate-x-1 transition-transform" />
                    Volver al Archivo
                </Link>
            </div>

            <div class="absolute bottom-0 left-0 w-full px-8 md:px-16 pb-16">
                <div class="max-w-7xl mx-auto">
                    <span class="text-[10px] font-bold tracking-[0.3em] text-[#FFB162] uppercase mb-4 block flex items-center gap-3 after:content-[''] after:h-px after:w-12 after:bg-[#A35139]">
                        {{ formatDate(event.event_date) }}
                    </span>
                    <h1 class="text-5xl md:text-7xl font-['Cormorant_Garamond'] font-light text-[#EEE9DF] mb-6 max-w-5xl leading-[0.95]">
                        {{ event.name }}
                    </h1>
                    <div class="flex flex-wrap items-center gap-6 text-[#C9C1B1] text-[11px] font-bold uppercase tracking-[0.2em]">
                        <span v-if="event.location">{{ event.location }}</span>
                        <span class="w-1 h-1 bg-[#C9C1B1]/30 rounded-full"></span>
                        <span class="text-[#FFB162]">{{ event.photos_count }} Fotografías</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="min-h-screen bg-[#111920] font-['Syne'] pb-24">
            <div class="max-w-7xl mx-auto px-8 md:px-16 pt-16">

                <div class="flex flex-col lg:flex-row gap-16 mb-16 border-b border-[#C9C1B1]/10 pb-16">

                    <div class="lg:w-7/12">
                        <h3 class="text-[9px] font-bold uppercase tracking-[0.35em] text-[#FFB162] mb-6">Sobre el Evento</h3>
                        <p class="text-[#C9C1B1] leading-relaxed font-light text-base md:text-lg mb-6">
                            {{ event.description }}
                        </p>
                        <p v-if="event.long_description" class="text-[#C9C1B1]/70 leading-relaxed text-[13px] font-light">
                            {{ event.long_description }}
                        </p>
                    </div>

                    <div class="lg:w-5/12 flex flex-col gap-8">
                        
                        <div class="bg-[#1B2632] p-8 border border-[#C9C1B1]/10 shadow-2xl relative overflow-hidden">
                            <div class="absolute -right-4 -bottom-4 text-[#FFB162] opacity-[0.03] pointer-events-none">
                                <FunnelIcon class="w-32 h-32" />
                            </div>

                            <h3 class="text-[10px] font-bold uppercase tracking-[0.2em] text-[#EEE9DF] mb-6 flex items-center gap-2">
                                <FunnelIcon class="w-4 h-4 text-[#FFB162]" /> 
                                Curaduría Visual
                            </h3>

                            <div class="space-y-4 relative z-10">
                                <label class="flex items-center cursor-pointer group">
                                    <input type="radio" v-model="selectedPhotographer" value="all" @change="filterByPhotographer" class="peer sr-only">
                                    <div class="w-4 h-4 rounded-full border border-[#C9C1B1]/30 mr-4 peer-checked:border-[#FFB162] flex items-center justify-center transition-colors">
                                        <div class="w-2 h-2 bg-[#FFB162] rounded-full opacity-0 peer-checked:opacity-100 transition-opacity"></div>
                                    </div>
                                    <span class="text-[11px] font-bold uppercase tracking-widest text-[#C9C1B1] peer-checked:text-[#EEE9DF] transition-colors">
                                        Mostrar Todo el Material
                                    </span>
                                </label>

                                <label v-for="photographer in photographers" :key="photographer.id" class="flex items-center cursor-pointer group">
                                    <input type="radio" v-model="selectedPhotographer" :value="photographer.id" @change="filterByPhotographer" class="peer sr-only">
                                    <div class="w-4 h-4 rounded-full border border-[#C9C1B1]/30 mr-4 peer-checked:border-[#FFB162] flex items-center justify-center transition-colors">
                                        <div class="w-2 h-2 bg-[#FFB162] rounded-full opacity-0 peer-checked:opacity-100 transition-opacity"></div>
                                    </div>
                                    <div class="flex items-center gap-3">
                                        <span class="text-[11px] font-bold uppercase tracking-widest text-[#C9C1B1] peer-checked:text-[#EEE9DF] transition-colors">
                                            {{ photographer.business_name || photographer.user.name }}
                                        </span>
                                        <span class="text-[9px] text-[#C9C1B1]/50 bg-[#111920] border border-[#C9C1B1]/10 px-1.5 py-0.5 rounded-sm font-mono">
                                            {{ photographer.photos_count }}
                                        </span>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <div class="flex flex-col gap-4">
                            <Link :href="route('events.face-search', event.slug)"
                                class="inline-flex items-center justify-center gap-3 bg-[#FFB162] text-[#1B2632] hover:bg-[#EEE9DF] font-bold text-[10px] uppercase tracking-[0.2em] px-6 py-4 transition-colors shadow-lg group">
                                <MagnifyingGlassIcon class="w-5 h-5 group-hover:scale-110 transition-transform" />
                                <span>Búsqueda Biométrica</span>
                            </Link>

                            <Link :href="route('events.bib-search', event.slug)"
                                class="inline-flex items-center justify-center gap-3 bg-transparent border border-[#FFB162] text-[#FFB162] hover:bg-[#FFB162] hover:text-[#1B2632] font-bold text-[10px] uppercase tracking-[0.2em] px-6 py-4 transition-colors shadow-lg group">
                                <HashtagIcon class="w-5 h-5 group-hover:scale-110 transition-transform" />
                                <span>Búsqueda por Dorsal</span>
                            </Link>
                        </div>

                    </div>
                </div>

                <div v-if="photos.data && photos.data.length > 0">
                    <div class="columns-2 md:columns-3 lg:columns-4 gap-4 mb-16">
                        <Link v-for="(photo, index) in photos.data" :key="photo.id"
                            :href="route('gallery.show', photo.unique_id)"
                            class="group relative overflow-hidden bg-[#1B2632] block break-inside-avoid mb-4"
                            :class="(index % 4 === 0 || index % 7 === 0) ? 'aspect-[4/5]' : ((index % 3 === 0) ? 'aspect-[3/4]' : 'aspect-[2/3]')">
                            
                            <img :src="photo.thumbnail_url" :alt="photo.unique_id"
                                class="w-full h-full object-cover transition-transform duration-[800ms] group-hover:scale-105 saturate-[0.5] group-hover:saturate-100"
                                loading="lazy" 
                                @error="handleImageError" />

                            <div class="absolute inset-0 bg-gradient-to-t from-[#111920]/90 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-400 flex flex-col justify-end p-5">
                                <div class="translate-y-4 group-hover:translate-y-0 transition-transform duration-400">
                                    <span class="text-[9px] font-bold text-[#FFB162] uppercase tracking-[0.2em] mb-1 block">
                                        Ver Detalles
                                    </span>
                                    <span class="text-[11px] text-[#EEE9DF] font-mono tracking-widest">
                                        ID: {{ photo.unique_id }}
                                    </span>
                                </div>
                            </div>

                            <div class="absolute top-3 right-3 bg-[#111920]/80 backdrop-blur-md border border-[#C9C1B1]/10 px-3 py-1.5 text-[9px] font-bold uppercase tracking-widest text-[#EEE9DF] opacity-0 group-hover:opacity-100 transition-opacity duration-400 transform translate-y-2 group-hover:translate-y-0">
                                {{ photo.photographer_name }}
                            </div>
                        </Link>
                    </div>

                    <div v-if="photos.last_page > 1" class="flex justify-center gap-2 pt-8 border-t border-[#C9C1B1]/10">
                        <div class="flex items-center gap-2 bg-[#1B2632] p-2 border border-[#C9C1B1]/10 shadow-xl">
                            <template v-for="(link, index) in photos.links" :key="index">
                                <Link v-if="link.url" :href="link.url"
                                    v-html="link.label"
                                    class="min-w-[40px] h-10 flex items-center justify-center px-4 text-[11px] font-bold transition-colors"
                                    :class="link.active ? 'bg-[#FFB162] text-[#1B2632]' : 'text-[#C9C1B1] hover:bg-[#2C3B4D] hover:text-[#EEE9DF]'" />
                                <span v-else v-html="link.label" class="min-w-[40px] h-10 flex items-center justify-center px-4 text-[11px] font-bold text-[#C9C1B1]/30"></span>
                            </template>
                        </div>
                    </div>
                </div>

                <div v-else class="text-center py-32 border border-dashed border-[#C9C1B1]/20 bg-[#1B2632]">
                    <div class="mx-auto h-16 w-16 text-[#FFB162] opacity-50 mb-6">
                        <FunnelIcon class="w-full h-full" />
                    </div>
                    <h3 class="text-3xl font-['Cormorant_Garamond'] text-[#EEE9DF] mb-3">Material no encontrado</h3>
                    <p class="text-[#C9C1B1] text-[13px] font-light max-w-md mx-auto mb-8">
                        No hay fotografías disponibles con el fotógrafo seleccionado para este evento.
                    </p>
                    <button v-if="selectedPhotographer !== 'all'"
                        @click="selectedPhotographer = 'all'; filterByPhotographer()"
                        class="text-[10px] font-bold uppercase tracking-[0.2em] text-[#FFB162] border-b border-[#FFB162] pb-1 hover:text-[#EEE9DF] hover:border-[#EEE9DF] transition-colors">
                        Restablecer Filtros y Ver Todo
                    </button>
                </div>

            </div>
        </div>
    </AppLayout>
</template>