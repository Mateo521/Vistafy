<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { ArrowLeftIcon, HashtagIcon, MagnifyingGlassIcon, ExclamationTriangleIcon } from '@heroicons/vue/24/outline';

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
    <Head :title="`Buscar Dorsal - ${event.name} | F33`" />

    <AppLayout>
        <div class="min-h-screen bg-[#F8F9FA] text-slate-800 font-sans antialiased py-12 pt-24 md:pt-28">
            <div class="max-w-[1500px] mx-auto px-4 sm:px-6 lg:px-8">

                <div class="mb-6 relative z-20">
                    <Link :href="route('events.show', event.slug)"
                        class="inline-flex items-center gap-2 bg-white px-4 py-2 rounded-full text-xs font-bold uppercase tracking-wider text-gray-500 hover:text-black hover:shadow-sm border border-gray-200 transition-all">
                        <ArrowLeftIcon class="w-4 h-4" /> Volver al evento
                    </Link>
                </div>

                <div class="relative w-full h-[35vh] min-h-[300px] rounded overflow-hidden shadow-sm mb-12 flex flex-col justify-end group">
                    <div class="absolute inset-0 w-full h-full bg-slate-900">
                        <img v-if="event.cover_image_url" :src="event.cover_image_url" :alt="event.name"
                            class="w-full h-full object-cover opacity-70 mix-blend-overlay transition-transform duration-1000 " />
                        <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent"></div>
                    </div>

                    <div class="relative z-10 p-8 md:p-12 text-center md:text-left flex flex-col md:flex-row md:items-end justify-between gap-6 pb-24 md:pb-16">
                        <div>
                            <div class="inline-flex items-center gap-2 bg-[#E30613] text-white px-3 py-1.5 rounded-md text-[10px] font-bold uppercase tracking-widest shadow-sm mb-4">
                                <HashtagIcon class="w-4 h-4" /> Búsqueda por dorsal
                            </div>
                            <h1 class="font-flux text-4xl md:text-6xl text-white leading-none tracking-wide">
                                {{ event.name }}
                            </h1>
                        </div>
                    </div>
                </div>

                <div class="max-w-2xl mx-auto -mt-24 md:-mt-20 relative z-20 mb-16 px-4 sm:px-0">
                    <form @submit.prevent="searchBib" class="bg-white rounded p-6 md:p-8 shadow-xl border border-gray-100">
                        <label for="bib_number" class="block text-xs font-bold uppercase tracking-wider text-gray-500 mb-3 text-center">
                            Ingresá tu número de corredor
                        </label>
                        
                        <div class="relative mb-4">
                            <HashtagIcon class="absolute left-6 top-1/2 -translate-y-1/2 w-6 h-6 text-gray-300" />
                            <input
                                id="bib_number"
                                v-model="searchForm.bib_number"
                                type="text"
                                placeholder="Ej: 120, 529..."
                                class="w-full bg-gray-50 border border-transparent focus:bg-white focus:border-gray-300 focus:ring-4 focus:ring-gray-100 text-slate-800 font-flux text-3xl md:text-4xl text-center py-5 rounded transition-all outline-none placeholder-gray-300 uppercase tracking-widest"
                                required
                            />
                        </div>

                        <button
                            type="submit"
                            :disabled="searchForm.processing"
                            class="w-full bg-black text-white font-bold text-xs uppercase tracking-wider py-4 rounded-full hover:bg-[#E30613] hover:shadow-lg hover:shadow-red-500/30 transition-all flex items-center justify-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed group"
                        >
                            <span v-if="searchForm.processing" class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin"></span>
                            <MagnifyingGlassIcon v-else class="w-5 h-5 group-hover:-rotate-12 transition-transform" />
                            {{ searchForm.processing ? 'Buscando...' : 'Iniciar búsqueda' }}
                        </button>
                    </form>
                </div>


                <div class="max-w-[1500px] mx-auto">
                    

                    <div v-if="searchedBib && photos && photos.data.length > 0">
                        

                        <div class="flex flex-col sm:flex-row sm:items-end justify-between border-b border-gray-200 pb-4 mb-8 gap-4">
                            <div>
                                <h2 class="font-flux text-3xl md:text-4xl text-black">
                                    Dorsal <span class="text-[#E30613]">#{{ searchedBib }}</span>
                                </h2>
                            </div>
                            <div class="bg-gray-100 text-slate-700 px-4 py-2 rounded-full text-xs font-bold uppercase tracking-wider shadow-inner w-max">
                                {{ photos.total }} {{ photos.total === 1 ? 'Captura Encontrada' : 'Capturas Encontradas' }}
                            </div>
                        </div>


                        <div class="columns-2 md:columns-3 lg:columns-4 gap-4 space-y-4">
                            <Link v-for="photo in photos.data" :key="photo.id"
                                :href="route('gallery.show', photo.unique_id)"
                                class="break-inside-avoid block group relative bg-gray-100 rounded overflow-hidden border border-gray-200 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300 w-full h-auto">

                                <img :src="photo.thumbnail_url" :alt="photo.unique_id"
                                    class="w-full h-auto object-cover transition-transform duration-700  pointer-events-none select-none"
                                    loading="lazy" />

                                <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none"></div>


                                <div class="absolute top-3 left-3 pointer-events-none flex flex-wrap gap-1 max-w-[80%]">
                                    <span class="bg-white/90 backdrop-blur-md text-[#E30613] font-bold text-[10px] uppercase tracking-wider px-2 py-1 rounded-md shadow-sm">
                                        #{{ photo.bib_numbers.join(', #') }}
                                    </span>
                                </div>


                                <div class="absolute bottom-3 right-3 bg-white text-black px-3 py-1.5 rounded-full text-[10px] font-bold uppercase tracking-wider opacity-0 group-hover:opacity-100 transition-opacity shadow-sm flex items-center gap-1">
                                    Ver foto
                                </div>
                            </Link>
                        </div>

                        <div v-if="photos.last_page > 1" class="mt-16 flex justify-center">
                            <div class="flex flex-wrap items-center gap-2 bg-white p-2 rounded-full shadow-sm border border-gray-100">
                                <template v-for="(link, index) in photos.links" :key="index">
                                    <Link v-if="link.url" :href="link.url"
                                        class="min-w-[40px] h-10 flex items-center justify-center px-4 rounded-full text-xs font-bold transition-colors"
                                        :class="link.active 
                                            ? 'bg-[#E30613] text-white shadow-md' 
                                            : 'bg-transparent text-gray-600 hover:bg-gray-100 hover:text-black'"
                                        v-html="link.label" />
                                    <span v-else v-html="link.label" class="min-w-[40px] h-10 flex items-center justify-center px-4 rounded-full text-xs font-bold text-gray-300 cursor-not-allowed"></span>
                                </template>
                            </div>
                        </div>
                    </div>

                    <div v-else-if="searchedBib" class="flex flex-col items-center justify-center py-24 bg-white border border-red-100 rounded shadow-sm h-full">
                        <div class="w-20 h-20 bg-red-50 rounded-full flex items-center justify-center mb-6">
                            <ExclamationTriangleIcon class="w-10 h-10 text-[#E30613]" />
                        </div>
                        <h3 class="font-flux text-3xl text-black mb-3">Sin Coincidencias</h3>
                        <p class="text-sm font-medium text-gray-500 max-w-md text-center leading-relaxed">
                            No pudimos encontrar fotos vinculadas al dorsal <strong class="text-[#E30613]">#{{ searchedBib }}</strong> en este evento. Verificá el número e intenta nuevamente.
                        </p>
                    </div>

                    <div v-else class="flex flex-col items-center justify-center py-24 bg-white border border-gray-100 rounded shadow-sm h-full">
                        <div class="w-20 h-20 bg-gray-50 rounded-full flex items-center justify-center mb-6">
                            <HashtagIcon class="w-10 h-10 text-gray-300" />
                        </div>
                        <h3 class="font-flux text-3xl text-black mb-3">Sistema OCR</h3>
                        <p class="text-sm font-medium text-gray-500 max-w-sm text-center leading-relaxed">
                            Ingresá tu número de corredor en el buscador superior para escanear.
                        </p>
                    </div>

                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>

::-webkit-scrollbar {
    width: 6px;
}
::-webkit-scrollbar-track {
    background: transparent;
}
::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 10px;
}
::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
}
</style>