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
    <Head :title="`[BÚSQUEDA DORSAL] ${event.name}`" />

    <AppLayout>
        <div class="relative h-[40vh] min-h-[300px] bg-black overflow-hidden border-b-2 border-[#E30613]">
            <img v-if="event.cover_image_url" :src="event.cover_image_url" :alt="event.name"
                class="absolute inset-0 w-full h-full object-cover filter grayscale opacity-40 mix-blend-screen" />

            <div class="absolute inset-0 bg-[url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAiIGhlaWdodD0iMjAiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PGNpcmNsZSBjeD0iMSIgY3k9IjEiIHI9IjEiIGZpbGw9InJnYmEoMjU1LDI1NSwyNTUsMC4wNSkiLz48L3N2Zz4=')]"></div>
            <div class="absolute inset-0 bg-gradient-to-t from-[#050505] via-transparent to-transparent"></div>

            <div class="absolute top-32 left-0 w-full px-4 sm:px-6 lg:px-8 z-10 max-w-7xl mx-auto">
                <Link :href="route('events.show', event.slug)"
                    class="inline-flex items-center text-zinc-500 hover:text-white font-mono text-[10px] font-bold uppercase tracking-widest transition-colors group bg-black border border-zinc-800 px-4 py-2 hover:border-white w-max">
                    <ArrowLeftIcon class="w-3 h-3 mr-2 group-hover:-translate-x-1 transition-transform" />
                    CANCELAR / VOLVER AL EVENTO
                </Link>
            </div>

            <div class="absolute bottom-0 left-0 w-full p-8 z-10">
                <div class="max-w-4xl mx-auto text-center">
                    <HashtagIcon class="w-12 h-12 text-[#E30613] mx-auto mb-4" />
                    <h1 class="text-5xl md:text-7xl font-flux text-white mb-2 uppercase tracking-tighter mix-blend-difference">
                        Extracción por Dorsal
                    </h1>
                    <p class="font-mono text-xs font-bold text-zinc-400 uppercase tracking-widest">> {{ event.name }}</p>
                </div>
            </div>
        </div>

        <div class="bg-[#09090b] border-b border-zinc-800">
            <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
                <form @submit.prevent="searchBib" class="space-y-4">
                    <div class="bg-black border border-zinc-800 p-6 shadow-[4px_4px_0px_0px_rgba(255,255,255,0.05)]">
                        <label for="bib_number" class="block font-mono text-[10px] font-bold uppercase tracking-widest text-[#E30613] mb-4 flex items-center gap-2">
                            <span class="w-2 h-2 bg-[#E30613] animate-pulse"></span>
                            >_REQUERIDO: NÚMERO DE DORSAL
                        </label>
                        <input
                            id="bib_number"
                            v-model="searchForm.bib_number"
                            type="text"
                            placeholder="EJ: 120, 529, 1234..."
                            class="w-full px-4 py-4 bg-[#050505] border border-zinc-700 text-white focus:border-[#E30613] focus:ring-0 text-xl font-mono uppercase tracking-widest rounded-none transition-colors text-center"
                            required
                        />
                        <p class="mt-3 text-[10px] font-mono uppercase text-zinc-600 tracking-widest text-center">
                            INGRESÁ ÚNICAMENTE CARACTERES NUMÉRICOS
                        </p>
                    </div>

                    <button
                        type="submit"
                        :disabled="searchForm.processing"
                        class="w-full bg-[#E30613] border border-[#E30613] hover:bg-white hover:border-white text-black font-mono text-[10px] font-bold uppercase tracking-widest px-6 py-4 transition-colors disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-3 rounded-none"
                    >
                        <MagnifyingGlassIcon class="w-5 h-5" />
                        <span v-if="!searchForm.processing">Iniciar Búsqueda de Activos</span>
                        <span v-else>Procesando Consulta...</span>
                    </button>
                </form>
            </div>
        </div>

        <div class="min-h-screen bg-[#050505] selection:bg-[#E30613] selection:text-black">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
                
                <div v-if="searchedBib && photos && photos.data.length > 0">
                    <div class="mb-10 text-center border-y border-zinc-800 py-4 bg-[#09090b]">
                        <p class="font-mono text-xs uppercase tracking-widest text-zinc-400">
                            > STATUS_OK: <span class="text-white">{{ photos.total }}</span> ACTIVOS ENCONTRADOS PARA DORSAL 
                            <strong class="text-[#E30613] ml-1">#{{ searchedBib }}</strong>
                        </p>
                    </div>

                    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-2 lg:gap-4">
                        <Link v-for="photo in photos.data" :key="photo.id"
                            :href="route('gallery.show', photo.unique_id)"
                            class="group relative aspect-square overflow-hidden bg-zinc-950 border border-zinc-800 hover:border-[#E30613] rounded-none transition-colors block">
                            
                            <img :src="photo.thumbnail_url" :alt="photo.unique_id"
                                class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110 filter grayscale opacity-60 group-hover:grayscale-0 group-hover:opacity-100"
                                loading="lazy" />

                            <div class="absolute inset-0 bg-[#E30613]/0 group-hover:bg-[#E30613]/20 transition-colors duration-300 mix-blend-multiply pointer-events-none"></div>

                            <div class="absolute inset-0 opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex flex-col justify-end p-4 bg-gradient-to-t from-black via-black/50 to-transparent pointer-events-none">
                                <span class="text-[10px] font-mono font-bold text-[#E30613] uppercase tracking-widest mb-1">>_ INSPECCIONAR</span>
                                <span class="text-[9px] text-zinc-400 font-mono uppercase">ASSET_#{{ photo.unique_id }}</span>
                            </div>

                            <div class="absolute top-2 left-2 bg-black/80 backdrop-blur-sm border border-zinc-700 text-white px-2 py-1 rounded-none font-mono text-[9px] font-bold uppercase tracking-widest group-hover:border-[#E30613] transition-colors">
                                #{{ photo.bib_numbers.join(', #') }}
                            </div>
                        </Link>
                    </div>

                    <div v-if="photos.last_page > 1" class="flex flex-wrap justify-center gap-2 pt-12 mt-12 border-t border-zinc-800">
                        <template v-for="(link, index) in photos.links" :key="index">
                            <Link v-if="link.url" :href="link.url"
                                class="h-10 min-w-[2.5rem] px-3 flex items-center justify-center font-mono text-[10px] font-bold uppercase tracking-widest rounded-none transition-colors border"
                                :class="link.active 
                                    ? 'bg-[#E30613] text-black border-[#E30613]' 
                                    : 'bg-black text-zinc-500 border-zinc-800 hover:border-white hover:text-white'"
                                v-html="link.label" />
                            <span v-else v-html="link.label" class="h-10 min-w-[2.5rem] px-3 flex items-center justify-center font-mono text-[10px] font-bold uppercase text-zinc-700 border border-transparent cursor-not-allowed"></span>
                        </template>
                    </div>
                </div>

                <div v-else-if="searchedBib" class="text-center py-24 border-2 border-dashed border-zinc-800 bg-[#09090b] mt-8">
                    <HashtagIcon class="w-16 h-16 text-zinc-700 mx-auto mb-4 stroke-1" />
                    <h3 class="font-flux text-2xl uppercase tracking-widest text-zinc-500 mb-2">
                        ERR_NOT_FOUND // 0 RESULTADOS
                    </h3>
                    <p class="font-mono text-xs text-zinc-400 mb-2 uppercase tracking-widest">
                        NO EXISTEN REGISTROS VINCULADOS AL DORSAL <strong class="text-[#E30613] ml-1">#{{ searchedBib }}</strong>
                    </p>
                    <p class="font-mono text-[10px] text-zinc-600 uppercase tracking-widest">
                        VERIFICÁ LOS DATOS INGRESADOS O INTENTÁ CON OTRO NÚMERO
                    </p>
                </div>

                <div v-else class="text-center py-24 border border-zinc-800 bg-[#09090b] mt-8">
                    <HashtagIcon class="w-16 h-16 text-zinc-800 mx-auto mb-4" />
                    <h3 class="font-flux text-3xl uppercase tracking-widest text-zinc-600 mb-4">
                     LISTO
                    </h3>
                    <p class="font-mono text-xs text-zinc-500 uppercase tracking-widest">
                        INGRESÁ EL NÚMERO DE DORSAL
                    </p>
                </div>

            </div>
        </div>
    </AppLayout>
</template>