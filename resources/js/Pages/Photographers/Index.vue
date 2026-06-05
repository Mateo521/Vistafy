<script setup>
import { ref, computed, onMounted } from 'vue';
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
        placeholder.className = 'placeholder-img w-full h-full flex items-center justify-center bg-black border-2 border-red-600/30';
        placeholder.innerHTML = `<span class="font-black text-2xl text-red-600 opacity-50">F33</span>`;
        parent.appendChild(placeholder);
    }
};


const initGlitch = () => {
    const glitchContainers = document.querySelectorAll('.glitch-image-container');
    glitchContainers.forEach(container => {
        const imgUrl = container.getAttribute('data-img');
        if (!imgUrl) return;

        const height = container.clientHeight || 220;
        const width = container.clientWidth;
        let i = 0;
        let html = '';
        const random = (min, max) => Math.random() * (max - min) + min;

        while (i < height) {
            const stripHeight = Math.floor(Math.random() * 6) + 2;
            const actualHeight = (i + stripHeight < height) ? stripHeight : (height - i);
            html += `
                <div class="glitch-strip" 
                     style="
                        height: ${actualHeight}px; 
                        background-image: url('${imgUrl}');
                        background-size: ${width}px ${height}px; 
                        background-position: 0px -${i}px;
                        --glitch-x-1: ${random(-25, 25).toFixed(1)}px;
                        --glitch-x-2: ${random(-25, 25).toFixed(1)}px;
                        --glitch-hue-1: ${random(-30, 30).toFixed(1)}deg;
                        --glitch-hue-2: ${random(-30, 30).toFixed(1)}deg;
                        animation-duration: ${random(3, 8).toFixed(1)}s;
                        animation-delay: -${random(0, 3).toFixed(1)}s;
                     ">
                </div>`;
            i += actualHeight;
        }
        container.innerHTML = html;
    });
};

onMounted(() => {
    initGlitch();
});
</script>

<template>
    <Head title="Staff y Operadores — F33.CLICK" />

    <AppLayout>

        <div class="relative bg-black pt-32 pb-16 md:pt-40 md:pb-24 border-b-[12px] border-white group  overflow-hidden">
            <div class="glitch-image-container absolute inset-0 w-full h-full overflow-hidden -z-10 opacity-30" data-img="/0fcce5d4573ebd79df2e147d7f87af35.jpg"></div>
            
            <div class="max-w-[1500px] mx-auto px-4 md:px-8">
                <div class="pointer-events-none">
                    <p class="font-mono text-xs uppercase tracking-[0.45em] text-red-600 mb-4  border-red-600 pl-3">
                        Directorio Base
                    </p>
                    <h1 class="font-black text-[14vw] md:text-[8rem] leading-[0.8] text-white tracking-tighter mix-blend-difference">
                        STAFF DE<br><span class="text-red-600 mix-blend-screen">OPERADORES.</span>
                    </h1>
                </div>
            </div>
        </div>

        <div class="w-full h-[450px] border-b border-white/20 relative z-0 bg-gray-950 grayscale contrast-125">
            <PhotographerMap :photographers="photographers.data" />
            
            <div class="absolute top-6 left-6 md:left-12 z-[400] bg-black border border-red-600 px-4 py-2 text-[10px] font-mono font-bold uppercase tracking-widest text-red-600 pointer-events-none shadow-[4px_4px_0_rgba(220,38,38,0.3)]">
                [ SISTEMA DE RASTREO ACTIVO ]
            </div>
        </div>

        <div class="min-h-screen bg-black text-white font-sans">
            <div class="max-w-[1500px] mx-auto px-4 md:px-8 py-10">

                <div class="bg-black border border-white/20 mb-12">
                    <form @submit.prevent="handleSearch">
                        <div class="flex flex-col md:flex-row gap-0 border-b border-white/20 font-mono text-xs uppercase tracking-widest">
                            <div class="flex-1 relative">
                                <MagnifyingGlassIcon class="absolute left-5 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-600" />
                                <input v-model="search" @keyup.enter="handleSearch" type="text"
                                    placeholder="BUSCAR OPERADOR O ESPECIALIDAD..."
                                    class="w-full pl-12 pr-5 py-5 bg-transparent text-white placeholder-gray-600 border-0 focus:ring-0 focus:outline-none transition-none" />
                            </div>

                            <div class="flex border-t md:border-t-0 border-white/20">
                                <button type="button" @click="showFilters = !showFilters" 
                                    :class="[
                                        'px-6 py-5 flex items-center gap-2 border-r border-white/20 transition-none whitespace-nowrap hover:bg-white hover:text-black',
                                        showFilters ? 'bg-white text-black' : 'text-gray-400'
                                    ]">
                                    <AdjustmentsHorizontalIcon class="w-4 h-4" />
                                    [ FILTROS ]
                                </button>
                                
                                <button type="submit" 
                                    class="px-8 py-5 bg-red-600 hover:bg-white text-black font-black transition-none whitespace-nowrap">
                                    EJECUTAR
                                </button>
                            </div>
                        </div>

                        <div v-show="showFilters" class="p-8 border-b border-white/20 bg-gray-950">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 font-mono text-[10px] tracking-widest uppercase">
                                
                                <div>
                                    <label class="block text-red-600 mb-2">/ ZONA DE DESPLIEGUE</label>
                                    <div class="relative">
                                        <select v-model="selectedRegion" @change="handleSearch"
                                            class="w-full bg-black border border-white/20 text-white px-4 py-3 focus:border-red-600 focus:ring-0 appearance-none rounded-none outline-none transition-none">
                                            <option value="">COBERTURA GLOBAL</option>
                                            <option v-for="region in regions" :key="region" :value="region">{{ region }}</option>
                                        </select>
                                        <MapPinIcon class="absolute right-4 top-3.5 w-4 h-4 text-gray-500 pointer-events-none" />
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-red-600 mb-2">/ PARÁMETRO DE ORDENAMIENTO</label>
                                    <select v-model="sortBy" @change="handleSearch"
                                        class="w-full bg-black border border-white/20 text-white px-4 py-3 focus:border-red-600 focus:ring-0 appearance-none rounded-none outline-none transition-none">
                                        <option value="recent">ÚLTIMAS ALTAS</option>
                                        <option value="name">ALFABÉTICO (A-Z)</option>
                                        <option value="popular">ÍNDICE DE POPULARIDAD</option>
                                        <option value="events">VOLUMEN DE COBERTURAS</option>
                                    </select>
                                </div>

                            </div>

                            <div class="mt-8 flex justify-end">
                                <button v-if="hasActiveFilters" type="button" @click="clearFilters" 
                                    class="font-mono text-[10px] text-red-600 hover:text-white uppercase tracking-[0.2em] transition-none border-b border-red-600 hover:border-white pb-1 flex items-center gap-2">
                                    <XMarkIcon class="w-3.5 h-3.5" /> PURGAR FILTROS
                                </button>
                            </div>
                        </div>
                    </form>

                    <div class="bg-gray-900 border-t border-white/10 px-6 py-3 flex justify-between items-center text-[10px] font-mono text-gray-500 uppercase tracking-widest">
                        <span>
                            REGISTRADOS <strong class="text-white text-xs">{{ photographers.data.length }}</strong> PROFESIONALES
                        </span>
                        <span v-if="hasActiveFilters" class="text-red-600 font-bold animate-pulse">
                            RESTRICCIONES ACTIVAS
                        </span>
                    </div>
                </div>

                <div v-if="photographers.data.length === 0" class="flex flex-col items-center justify-center py-32 border-4 border-dashed border-gray-800 bg-gray-950 text-center">
                    <h3 class="font-black text-6xl text-gray-700 tracking-tighter mb-4 uppercase">VACÍO.</h3>
                    <p class="font-mono text-xs text-gray-500 tracking-widest mb-8 uppercase">NINGÚN OPERADOR COINCIDE CON LA BÚSQUEDA.</p>
                    <button @click="clearFilters" class="border-2 border-red-600 bg-black text-red-600 hover:bg-red-600 hover:text-black px-8 py-3 font-mono text-[10px] font-bold uppercase tracking-widest transition-none">
                        [ REINICIAR DIRECTORIO ]
                    </button>
                </div>

                <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    <Link v-for="photographer in photographers.data" :key="photographer.id"
                        :href="route('photographers.show', photographer.slug)"
                        class="group flex flex-col bg-black border-[4px] border-white/10 hover:border-white transition-none overflow-hidden relative ">

                        <div class="h-48 overflow-hidden bg-gray-950 relative border-b-[4px] border-white/10 group-hover:border-white transition-none">
                            <img v-if="photographer.banner_photo_url" :src="photographer.banner_photo_url"
                                :alt="photographer.business_name"
                                class="w-full h-full object-cover grayscale opacity-60 group-hover:grayscale-0 group-hover:opacity-100 transition-none pointer-events-none" />
                            <div v-else class="w-full h-full flex items-center justify-center">
                                <span class="font-black text-5xl text-gray-800">F33</span>
                            </div>
                            
                            <div class="absolute top-4 right-4 bg-red-600 text-black text-[9px] font-mono font-bold uppercase tracking-widest px-2 py-1 flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-none pointer-events-none">
                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" /></svg>
                                VALIDADO
                            </div>
                        </div>

                        <div class="p-6 pt-12 relative flex-1 flex flex-col z-10">
                            
                            <div class="absolute -top-12 left-6 w-20 h-20 bg-black border-[4px] border-white/10 group-hover:border-red-600 transition-none flex items-center justify-center overflow-hidden">
                                <img v-if="photographer.profile_photo_url" :src="photographer.profile_photo_url"
                                    :alt="photographer.business_name"
                                    class="w-full h-full object-cover grayscale group-hover:grayscale-0 transition-none" />
                                <div v-else class="w-full h-full bg-gray-950 flex items-center justify-center">
                                    <span class="text-2xl font-black text-white">
                                        {{ getInitials(photographer.business_name) }}
                                    </span>
                                </div>
                            </div>

                            <div class="mb-4">
                                <h3 class="text-3xl font-black font-sans text-white uppercase tracking-tighter leading-none mb-2 group-hover:text-red-600 transition-none">
                                    {{ photographer.business_name }}
                                </h3>
                                <div v-if="photographer.region" class="flex items-center text-[9px] font-mono text-gray-500 uppercase tracking-widest font-bold">
                                    <MapPinIcon class="h-3 w-3 mr-1 text-red-600" />
                                    {{ photographer.region }}
                                </div>
                            </div>

                            <p v-if="photographer.bio" class="text-[10px] font-mono text-gray-400 uppercase tracking-widest line-clamp-3 mb-6 leading-relaxed">
                                {{ photographer.bio }}
                            </p>

                            <div class="mt-auto pt-6 border-t border-white/10 grid grid-cols-2 gap-4">
                                <div class="bg-gray-950 border border-white/10 p-3 group-hover:border-white transition-none">
                                    <span class="block text-3xl font-black font-sans text-white leading-none mb-1 group-hover:text-red-600 transition-none">
                                        {{ photographer.events_count }}
                                    </span>
                                    <span class="text-[9px] font-mono uppercase tracking-widest text-gray-500">Eventos</span>
                                </div>
                                <div class="bg-gray-950 border border-white/10 p-3 group-hover:border-white transition-none">
                                    <span class="block text-3xl font-black font-sans text-white leading-none mb-1 group-hover:text-red-600 transition-none">
                                        {{ photographer.photos_count }}
                                    </span>
                                    <span class="text-[9px] font-mono uppercase tracking-widest text-gray-500">Tomas</span>
                                </div>
                            </div>

                            <div class="mt-6 pt-4 border-t border-white/10 flex items-center justify-between">
                                <span class="text-[10px] font-mono font-bold uppercase tracking-widest text-gray-500 group-hover:text-white transition-none">
                                    [ INSPECCIONAR CATÁLOGO ]
                                </span>
                            </div>
                        </div>
                    </Link>
                </div>

                <div v-if="photographers.last_page > 1" class="mt-20 flex justify-center">
                    <div class="flex flex-wrap gap-2">
                        <template v-for="(link, index) in photographers.links" :key="index">
                            <Link v-if="link.url" :href="link.url"
                                class="min-w-[40px] h-10 flex items-center justify-center px-4 font-mono text-[11px] font-bold transition-none border-2 border-white/20 hover:border-white"
                                :class="link.active
                                    ? 'bg-red-600 text-black border-red-600 hover:border-red-600'
                                    : 'bg-black text-gray-400 hover:text-white'">
                                <span v-html="link.label"></span>
                            </Link>
                            <span v-else v-html="link.label" class="min-w-[40px] h-10 flex items-center justify-center px-4 font-mono text-[11px] font-bold text-gray-700 border-2 border-transparent"></span>
                        </template>
                    </div>
                </div>

            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
/* Scrollbar Brutalista */
::-webkit-scrollbar {
    width: 8px;
}
::-webkit-scrollbar-track {
    background: #000000;
    border-left: 1px solid #333;
}
::-webkit-scrollbar-thumb {
    background: #ffffff;
    border-radius: 0;
}
::-webkit-scrollbar-thumb:hover {
    background: #dc2626;
}


@keyframes precise-glitch {
    0%, 33.33%, 43.33%, 66.67%, 76.67%, 100% {
        transform: none;
        filter: hue-rotate(0) drop-shadow(0 0 0 transparent);
    }
    33.43%, 43.23% {
        transform: translateX(var(--glitch-x-1));
        filter: hue-rotate(var(--glitch-hue-1)) drop-shadow(3px 0 0 rgba(220, 38, 38, 0.6));
    }
    66.77%, 76.57% {
        transform: translateX(var(--glitch-x-2));
        filter: hue-rotate(var(--glitch-hue-2)) drop-shadow(-3px 0 0 rgba(255, 255, 255, 0.4));
    }
}

:deep(.glitch-strip) {
    width: 100%;
    background-repeat: no-repeat;
    animation-name: precise-glitch;
    animation-timing-function: linear;
    animation-iteration-count: infinite;
    animation-play-state: paused; 
}

.group:hover :deep(.glitch-strip) {
    animation-play-state: running;
}
</style>