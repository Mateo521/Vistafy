<script setup>
import { ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import PhotographerMap from '@/Components/PhotographerMap.vue';
import {
    MagnifyingGlassIcon,
    MapPinIcon,
    AdjustmentsHorizontalIcon,
    XMarkIcon
} from '@heroicons/vue/24/outline';

const props = defineProps({
    photographers: Object,
    regions: Array,
    filters: Object,
});

const search = ref(props.filters.search || '');
const selectedRegion = ref(props.filters.region || '');
const sortBy = ref(props.filters.sort || 'recent');
const showFilters = ref(false);

const handleSearch = () => {
    router.get('/fotografos', {
        search: search.value,
        region: selectedRegion.value,
        sort: sortBy.value,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
};

const clearFilters = () => {
    search.value = '';
    selectedRegion.value = '';
    sortBy.value = 'recent';
    router.get('/fotografos');
};

const hasActiveFilters = computed(() => {
    return search.value || selectedRegion.value || sortBy.value !== 'recent';
});

const getInitials = (name) => {
    if (!name) return 'F';
    return name
        .split(' ')
        .map(word => word[0])
        .join('')
        .toUpperCase()
        .substring(0, 2);
};

const handleImageError = (e) => {
    e.target.style.display = 'none';
    const parent = e.target.parentElement;
    if (!parent.querySelector('.placeholder-img')) {
        const placeholder = document.createElement('div');
        placeholder.className = 'placeholder-img w-full h-full flex items-center justify-center bg-gray-50 border border-gray-100';
        placeholder.innerHTML = `<span class="font-bold text-xl text-gray-300">F33</span>`;
        parent.appendChild(placeholder);
    }
};
</script>

<template>
    <Head title="Staff y Fotógrafos — F33.CLICK" />

    <AppLayout>
    
        <div class="pt-32 pb-12 px-4 md:px-8 max-w-[1500px] mx-auto">
            <div class="relative w-full h-[40vh] md:h-[45vh] rounded overflow-hidden shadow-2xl flex flex-col justify-end p-8 md:p-16">
        
                <div class="absolute inset-0 w-full h-full">
                    <!--img src="/0fcce5d4573ebd79df2e147d7f87af35.jpg" class="w-full h-full object-cover" alt="Staff fotógrafos" /-->
                    <div class="absolute inset-0 bg-gradient-to-t from-black/90 via-black/40 to-transparent"></div>
                </div>

                <div class="relative z-10">
                    <span class="text-[#E30613] font-bold tracking-widest uppercase text-sm mb-3 block flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-[#E30613]"></span> -
                    </span>
                    <h1 class="font-flux text-6xl md:text-8xl text-white leading-none tracking-wide">
                        Nuestro <span class="text-[#E30613]">staff.</span>
                    </h1>
                </div>
            </div>
        </div>

        <div class="min-h-screen bg-[#F2F0EB] text-slate-800 pb-20">
            <div class="max-w-[1500px] mx-auto px-4 md:px-8">

            
                <div class="mb-10 relative bg-white rounded overflow-hidden shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-gray-100 h-[400px]">
                    <PhotographerMap :photographers="photographers.data" class="w-full h-full" />
                    
                    <div class="absolute top-6 left-6 bg-white/90 backdrop-blur-md px-4 py-2 rounded-full shadow-md border border-gray-100 flex items-center gap-2">
                        <span class="relative flex h-3 w-3">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-3 w-3 bg-[#E30613]"></span>
                        </span>
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-700">Rastreo Global Activo</span>
                    </div>
                </div>

            
                <div class="bg-white rounded shadow-[0_8px_30px_rgb(0,0,0,0.04)] mb-8 border border-gray-100 relative z-10 p-2 md:p-3">
                    <form @submit.prevent="handleSearch">
                        <div class="flex flex-col md:flex-row gap-2">
                    
                            <div class="flex-1 relative">
                                <MagnifyingGlassIcon class="absolute left-6 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" />
                                <input v-model="search" type="text"
                                    placeholder="Buscar fotógrafo o especialidad..."
                                    class="w-full pl-14 pr-6 py-4 bg-gray-50 hover:bg-gray-100 focus:bg-white border border-transparent focus:border-gray-300 rounded text-base focus:ring-4 focus:ring-gray-100 transition-all outline-none font-medium text-slate-700 placeholder-slate-400" />
                            </div>
                            
                        
                            <div class="flex gap-2">
                                <button type="button" @click="showFilters = !showFilters" :class="[
                                    'px-6 py-4 flex items-center gap-2 rounded font-bold text-xs uppercase tracking-wider transition-colors',
                                    showFilters ? 'bg-black text-white' : 'bg-gray-50 text-gray-600 hover:bg-gray-100'
                                ]">
                                    <AdjustmentsHorizontalIcon class="w-5 h-5" />
                                    Filtros
                                    <span v-if="hasActiveFilters" class="w-2 h-2 bg-[#E30613] rounded-full ml-1"></span>
                                </button>
                                <button type="submit"
                                    class="px-8 py-4 bg-[#E30613] hover:bg-red-700 text-white rounded font-bold text-xs uppercase tracking-wider transition-colors shadow-lg shadow-red-500/30">
                                    Buscar
                                </button>
                            </div>
                        </div>

                        
                        <transition enter-active-class="transition duration-300 ease-out"
                            enter-from-class="opacity-0 -translate-y-4" enter-to-class="opacity-100 translate-y-0"
                            leave-active-class="transition duration-200 ease-in" leave-from-class="opacity-100 translate-y-0"
                            leave-to-class="opacity-0 -translate-y-4">
                            
                            <div v-show="showFilters" class="mt-4 p-6 md:p-8 bg-gray-50 rounded border border-gray-100">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    
                                    <div>
                                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2 ml-1">Zona de despliegue</label>
                                        <div class="relative">
                                            <select v-model="selectedRegion" @change="handleSearch"
                                                class="w-full bg-white border border-gray-200 text-slate-700 px-4 py-3.5 rounded focus:border-gray-300 focus:ring-4 focus:ring-gray-100 appearance-none font-medium outline-none">
                                                <option value="">Cobertura Global</option>
                                                <option v-for="region in regions" :key="region" :value="region">{{ region }}</option>
                                            </select>
                                            <MapPinIcon class="absolute right-4 top-3.5 w-5 h-5 text-gray-400 pointer-events-none" />
                                        </div>
                                    </div>

                                    <div>
                                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2 ml-1">Ordenar por</label>
                                        <select v-model="sortBy" @change="handleSearch"
                                            class="w-full bg-white border border-gray-200 text-slate-700 px-4 py-3.5 rounded focus:border-gray-300 focus:ring-4 focus:ring-gray-100 appearance-none font-medium outline-none">
                                            <option value="recent">Últimos registrados</option>
                                            <option value="name">Alfabético (A-Z)</option>
                                            <option value="popular">Más Populares</option>
                                            <option value="events">Volumen de Coberturas</option>
                                        </select>
                                    </div>

                                </div>

                                <div v-if="hasActiveFilters" class="mt-6 pt-6 border-t border-gray-200 flex justify-end">
                                    <button type="button" @click="clearFilters" class="text-xs font-bold text-gray-500 hover:text-[#E30613] uppercase tracking-wider transition-colors flex items-center gap-1">
                                        <XMarkIcon class="w-4 h-4" /> Limpiar Filtros
                                    </button>
                                </div>
                            </div>
                        </transition>
                    </form>
                </div>

                
                <div class="flex justify-between items-center mb-8 px-2">
                    <span class="text-sm font-bold text-slate-700">
                        Mostrando <strong class="text-black">{{ photographers.data.length }}</strong> profesionales
                    </span>
                </div>

                
                <div v-if="photographers.data.length === 0" class="flex flex-col items-center justify-center py-24 px-4 text-center bg-white rounded shadow-sm border border-gray-100">
                    <div class="w-20 h-20 bg-gray-50 rounded-full flex items-center justify-center mb-6">
                        <MagnifyingGlassIcon class="w-8 h-8 text-gray-300" />
                    </div>
                    <h3 class="font-flux text-4xl text-black mb-3">No hay resultados</h3>
                    <p class="text-gray-500 mb-8 max-w-md">Ningún fotógrafo coincide con tu búsqueda actual.</p>
                    <button @click="clearFilters"
                        class="bg-black text-white px-8 py-3.5 rounded-full font-bold text-xs uppercase tracking-wider hover:bg-gray-800 transition-colors">
                        Ver todos
                    </button>
                </div>

        
                <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    <Link v-for="photographer in photographers.data" :key="photographer.id"
                        :href="route('photographers.show', photographer.slug)"
                        class="group bg-white rounded overflow-hidden shadow-[0_8px_30px_rgb(0,0,0,0.04)] hover:shadow-xl hover:-translate-y-1 border border-gray-100 transition-all duration-300 flex flex-col">

                        
                        <div class="h-32 bg-gray-100 relative overflow-hidden">
                            <img v-if="photographer.banner_photo_url" :src="photographer.banner_photo_url"
                                :alt="photographer.business_name"
                                class="w-full h-full object-cover transition-transform duration-700 " />
                            
                            
                            <div class="absolute top-4 right-4 bg-white/90 backdrop-blur text-black text-[10px] font-bold uppercase tracking-wider px-3 py-1.5 rounded-full flex items-center gap-1 shadow-sm">
                                <svg class="w-3.5 h-3.5 text-[#E30613]" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" /></svg>
                                Validado
                            </div>
                        </div>

                        
                        <div class="p-6 pt-0 relative flex-1 flex flex-col">
                            
                        
                            <div class="w-20 h-20 -mt-10 mb-4 bg-white rounded-full border-4 border-white shadow-sm flex items-center justify-center overflow-hidden z-10">
                                <img v-if="photographer.profile_photo_url" :src="photographer.profile_photo_url"
                                    :alt="photographer.business_name"
                                    class="w-full h-full object-cover" />
                                <div v-else class="w-full h-full bg-gray-50 flex items-center justify-center">
                                    <span class="text-xl font-black text-gray-400">
                                        {{ getInitials(photographer.business_name) }}
                                    </span>
                                </div>
                            </div>

                            <div class="mb-4">
                                <h3 class="font-flux text-3xl text-black leading-none mb-2 group-hover:text-[#E30613] transition-colors">
                                    {{ photographer.business_name }}
                                </h3>
                                <div v-if="photographer.region" class="flex items-center text-xs font-bold text-gray-500 uppercase tracking-wider">
                                    <MapPinIcon class="h-4 w-4 mr-1 text-[#E30613]" />
                                    {{ photographer.region }}
                                </div>
                            </div>

                            <p v-if="photographer.bio" class="text-sm text-gray-500 line-clamp-3 mb-6 leading-relaxed flex-1">
                                {{ photographer.bio }}
                            </p>

                    
                            <div class="grid grid-cols-2 gap-3 mt-auto pt-6 border-t border-gray-100">
                                <div class="bg-gray-50 rounded p-3 text-center group-hover:bg-red-50 transition-colors">
                                    <span class="block font-flux text-2xl text-black leading-none mb-1 group-hover:text-[#E30613] transition-colors">
                                        {{ photographer.events_count }}
                                    </span>
                                    <span class="text-[10px] font-bold uppercase tracking-wider text-gray-500">Eventos</span>
                                </div>
                                <div class="bg-gray-50 rounded p-3 text-center group-hover:bg-red-50 transition-colors">
                                    <span class="block font-flux text-2xl text-black leading-none mb-1 group-hover:text-[#E30613] transition-colors">
                                        {{ photographer.photos_count }}
                                    </span>
                                    <span class="text-[10px] font-bold uppercase tracking-wider text-gray-500">Tomas</span>
                                </div>
                            </div>
                        </div>
                    </Link>
                </div>

        
                <div v-if="photographers.last_page > 1" class="mt-16 flex justify-center">
                    <div class="flex flex-wrap gap-2 bg-white p-2 rounded-full shadow-sm border border-gray-100">
                        <template v-for="(link, index) in photographers.links" :key="index">
                            <Link v-if="link.url" :href="link.url"
                                class="min-w-[40px] h-10 flex items-center justify-center px-4 rounded-full text-xs font-bold transition-colors"
                                :class="link.active
                                    ? 'bg-[#E30613] text-white shadow-md'
                                    : 'bg-transparent text-gray-600 hover:bg-gray-100 hover:text-black'">
                                <span v-html="link.label"></span>
                            </Link>
                            <span v-else v-html="link.label" class="min-w-[40px] h-10 flex items-center justify-center px-4 rounded-full text-xs font-bold text-gray-300"></span>
                        </template>
                    </div>
                </div>

            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
::-webkit-scrollbar {
    width: 8px;
}
::-webkit-scrollbar-track {
    background: #F2F0EB;
}
::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 10px;
}
::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
}
</style>