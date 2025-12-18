<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { ArrowLeftIcon, HashtagIcon, MagnifyingGlassIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
    event: Object,
    photos: Object,
    searchedBib: String,
});

const searchForm = useForm({
    bib_number: props.searchedBib || '',
});

const searchBib = () => {
    searchForm.post(route('events.search-bib', props.event.slug), {
        preserveState: true,
        preserveScroll: false,
    });
};
</script>

<template>
    <Head :title="`Buscar por Dorsal - ${event.name}`" />

    <AppLayout>
        <!-- Header del evento -->
        <div class="relative h-[40vh] min-h-[300px] bg-slate-900 overflow-hidden">
            <img v-if="event.cover_image_url" :src="event.cover_image_url" :alt="event.name"
                class="absolute inset-0 w-full h-full object-cover opacity-40" />

            <div class="absolute inset-0 bg-gradient-to-t from-slate-900 via-transparent to-slate-900/30"></div>

            <div class="absolute top-24 left-0 w-full px-4 sm:px-6 lg:px-8 z-10">
                <Link :href="route('events.show', event.slug)"
                    class="inline-flex items-center text-white/70 hover:text-white text-xs font-bold uppercase tracking-widest transition-colors">
                    <ArrowLeftIcon class="w-4 h-4 mr-2" />
                    Volver al Evento
                </Link>
            </div>

            <div class="absolute bottom-0 left-0 w-full p-8">
                <div class="max-w-4xl mx-auto text-center">
                    <HashtagIcon class="w-12 h-12 text-white/60 mx-auto mb-4" />
                    <h1 class="text-3xl md:text-5xl font-sans font-bold text-white mb-2">
                        Buscar por Dorsal
                    </h1>
                    <p class="text-white/70 text-sm">{{ event.name }}</p>
                </div>
            </div>
        </div>

        <!-- Formulario de búsqueda -->
        <div class="bg-white border-b border-gray-100">
            <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
                <form @submit.prevent="searchBib" class="space-y-4">
                    <div>
                        <label for="bib_number" class="block text-xs font-bold uppercase tracking-widest text-slate-900 mb-3">
                            Ingresá tu número de dorsal
                        </label>
                        <input
                            id="bib_number"
                            v-model="searchForm.bib_number"
                            type="text"
                            placeholder="Ej: 120, 529, 1234..."
                            class="w-full px-4 py-3 border-2 border-gray-200 rounded-sm focus:border-slate-900 focus:ring-0 text-lg font-mono"
                            required
                        />
                        <p class="mt-2 text-xs text-slate-500">
                            Ingresá solo el número (sin letras ni símbolos)
                        </p>
                    </div>

                    <button
                        type="submit"
                        :disabled="searchForm.processing"
                        class="w-full bg-slate-900 hover:bg-slate-800 text-white font-medium px-6 py-4 transition-colors disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2"
                    >
                        <MagnifyingGlassIcon class="w-5 h-5" />
                        <span v-if="!searchForm.processing">Buscar mis fotos</span>
                        <span v-else>Buscando...</span>
                    </button>
                </form>
            </div>
        </div>

        <!-- Resultados -->
        <div class="min-h-screen bg-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
                
                <!-- Con resultados -->
                <div v-if="searchedBib && photos && photos.data.length > 0">
                    <div class="mb-8 text-center">
                        <p class="text-slate-600 text-sm">
                            Se encontraron <strong class="text-slate-900">{{ photos.total }}</strong> foto(s) con el dorsal 
                            <strong class="text-blue-600 font-mono">#{{ searchedBib }}</strong>
                        </p>
                    </div>

                    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                        <Link v-for="photo in photos.data" :key="photo.id"
                            :href="route('gallery.show', photo.unique_id)"
                            class="group relative aspect-square overflow-hidden bg-gray-100 rounded-sm">
                            <img :src="photo.thumbnail_url" :alt="photo.unique_id"
                                class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105"
                                loading="lazy" />

                            <div class="absolute inset-0 bg-slate-900/60 opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex flex-col justify-end p-4">
                                <span class="text-xs font-bold text-white uppercase tracking-widest mb-1">Ver Foto</span>
                                <span class="text-[10px] text-white/70 font-mono">#{{ photo.unique_id }}</span>
                            </div>

                            <!-- Badge con dorsales detectados -->
                            <div class="absolute top-2 left-2 bg-blue-600 text-white px-2 py-1 rounded-sm text-xs font-bold font-mono">
                                #{{ photo.bib_numbers.join(', #') }}
                            </div>
                        </Link>
                    </div>

                    <!-- Paginación -->
                    <div v-if="photos.last_page > 1" class="flex justify-center gap-2 pt-8 mt-8 border-t border-gray-100">
                        <Link v-for="(link, index) in photos.links" :key="index"
                            :href="link.url || '#'"
                            v-html="link.label"
                            :class="['h-10 min-w-[2.5rem] px-3 flex items-center justify-center text-sm font-medium rounded-sm border transition-colors',
                                link.active ? 'bg-slate-900 text-white border-slate-900' : 'bg-white text-slate-600 border-gray-200 hover:border-slate-400']" />
                    </div>
                </div>

                <!-- Sin resultados -->
                <div v-else-if="searchedBib" class="text-center py-32 border border-dashed border-gray-200 bg-gray-50 rounded-sm">
                    <HashtagIcon class="w-16 h-16 text-slate-300 mx-auto mb-4" />
                    <p class="text-slate-400 font-sans text-lg mb-2">
                        No se encontraron fotos con el dorsal <strong class="text-slate-600 font-mono">#{{ searchedBib }}</strong>
                    </p>
                    <p class="text-slate-400 text-sm">
                        Intentá con otro número o verificá que esté correctamente escrito
                    </p>
                </div>

                <!-- Estado inicial -->
                <div v-else class="text-center py-32">
                    <HashtagIcon class="w-16 h-16 text-slate-300 mx-auto mb-4" />
                    <p class="text-slate-400 font-sans text-lg">
                        Ingresá tu número de dorsal para buscar tus fotos
                    </p>
                </div>

            </div>
        </div>
    </AppLayout>
</template>
