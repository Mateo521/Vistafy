<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import { 
    MagnifyingGlassIcon, 
    AdjustmentsHorizontalIcon, 
    XMarkIcon, 
    CalendarIcon, 
    MapPinIcon 
} from '@heroicons/vue/24/outline';

const props = defineProps({
    events: Object,
    photographers: {
        type: Array,
        default: () => []
    },
    filters: {
        type: Object,
        default: () => ({ search: '', date: '', photographer_id: '' })
    }
});

const showFilters = ref(false);

const form = useForm({
    search: props.filters.search || '',
    date: props.filters.date || '',
    photographer_id: props.filters.photographer_id || '',
});

const submitFilters = () => {
    form.get(route('events.index'), {
        preserveState: true,
        preserveScroll: true,
    });
};

const clearFilters = () => {
    form.search = '';
    form.date = '';
    form.photographer_id = '';
    submitFilters();
};

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString('es-ES', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
};


const handleImageError = (e) => {
    e.target.style.display = 'none';
    const parent = e.target.parentElement;
    if (!parent.querySelector('.placeholder-img')) {
        const placeholder = document.createElement('div');
        placeholder.className = 'placeholder-img w-full h-full min-h-[250px] flex items-center justify-center bg-gray-950 border-2 border-red-600/30';
        placeholder.innerHTML = `<span class="font-mono text-[10px] text-red-600 uppercase tracking-widest">[ FALLO VISUAL ]</span>`;
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
    <Head title="Archivo de Eventos — F33.CLICK" />

    <AppLayout>
        
        <div class="relative bg-black pt-32 pb-16 md:pt-40 md:pb-24 border-b-[12px] border-white group  overflow-hidden">
            <div class="glitch-image-container absolute inset-0 w-full h-full overflow-hidden -z-10 opacity-40" data-img="/31b1d6803505497c6bbc636a9134d68d.jpg"></div>
            
            <div class="max-w-[1500px] mx-auto px-4 md:px-8">
                <div class="pointer-events-none">
                    <p class="font-mono text-xs uppercase tracking-[0.45em] text-red-600 mb-4  border-red-600 pl-3">
                        Línea de Tiempo
                    </p>
                    <h1 class="font-black text-[14vw] md:text-[8rem] leading-[0.8] text-white tracking-tighter mix-blend-difference">
                        ARCHIVO DE<br><span class="text-red-600 mix-blend-screen">EVENTOS.</span>
                    </h1>
                </div>
            </div>
        </div>

        <div class="min-h-screen bg-black text-white font-sans">
            <div class="max-w-[1500px] mx-auto px-4 md:px-8 py-10">
                
                <div class="bg-black border border-white/20 mb-12">
                    <form @submit.prevent="submitFilters">
                        <div class="flex flex-col md:flex-row gap-0 border-b border-white/20 font-mono text-xs uppercase tracking-widest">
                            <div class="flex-1 relative">
                                <MagnifyingGlassIcon class="absolute left-5 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-600" />
                                <input 
                                    v-model="form.search" 
                                    type="text"
                                    placeholder="BUSCAR IDENTIFICADOR, LOCACIÓN O MARCA..."
                                    class="w-full pl-12 pr-5 py-5 bg-transparent text-white placeholder-gray-600 border-0 focus:ring-0 focus:outline-none transition-none" 
                                />
                            </div>

                            <div class="flex border-t md:border-t-0 border-white/20">
                                <button type="button" @click="showFilters = !showFilters" 
                                    :class="[
                                        'px-6 py-5 flex items-center gap-2 border-r border-white/20 transition-none whitespace-nowrap hover:bg-white hover:text-black',
                                        showFilters ? 'bg-white text-black' : 'text-gray-400'
                                    ]">
                                    <AdjustmentsHorizontalIcon class="w-4 h-4" />
                                    [ PARÁMETROS ]
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
                                    <label class="block text-red-600 mb-2">/ MARCA TEMPORAL (FECHA)</label>
                                    <input 
                                        v-model="form.date"
                                        type="date" 
                                        class="w-full bg-black border border-white/20 text-white px-4 py-3 focus:border-red-600 focus:ring-0 appearance-none rounded-none outline-none transition-none [color-scheme:dark]"
                                    >
                                </div>

                                <div>
                                    <label class="block text-red-600 mb-2">/ OPERADOR (FOTÓGRAFO)</label>
                                    <select 
                                        v-model="form.photographer_id"
                                        class="w-full bg-black border border-white/20 text-white px-4 py-3 focus:border-red-600 focus:ring-0 appearance-none rounded-none outline-none transition-none"
                                    >
                                        <option value="">TODOS LOS OPERADORES</option>
                                        <option v-for="photographer in photographers" :key="photographer.id" :value="photographer.id">
                                            {{ photographer.business_name || photographer.user?.name }}
                                        </option>
                                    </select>
                                </div>

                            </div>

                            <div class="mt-8 flex justify-end">
                                <button type="button" @click="clearFilters" 
                                    class="font-mono text-[10px] text-red-600 hover:text-white uppercase tracking-[0.2em] transition-none border-b border-red-600 hover:border-white pb-1 flex items-center gap-2">
                                    <XMarkIcon class="w-3.5 h-3.5" /> PURGAR FILTROS
                                </button>
                            </div>
                        </div>
                    </form>

                    <div class="bg-gray-900 border-t border-white/10 px-6 py-3 flex justify-between items-center text-[10px] font-mono text-gray-500 uppercase tracking-widest">
                        <span>
                            MOSTRANDO <strong class="text-white text-xs">{{ events.data.length }}</strong> REGISTROS
                        </span>
                        <span v-if="form.search || form.date || form.photographer_id" class="text-red-600 font-bold animate-pulse">
                            FILTROS ACTIVOS
                        </span>
                    </div>
                </div>

                <div v-if="!events.data || events.data.length === 0" class="flex flex-col items-center justify-center py-32 border-4 border-dashed border-gray-800 bg-gray-950 text-center">
                    <h3 class="font-black text-6xl text-gray-700 tracking-tighter mb-4 uppercase">NULO.</h3>
                    <p class="font-mono text-xs text-gray-500 tracking-widest mb-8 uppercase">SIN COINCIDENCIAS EN LA LÍNEA DE TIEMPO.</p>
                    <button @click="clearFilters" class="border-2 border-red-600 bg-black text-red-600 hover:bg-red-600 hover:text-black px-8 py-3 font-mono text-[10px] font-bold uppercase tracking-widest transition-none">
                        [ REINICIAR BÚSQUEDA ]
                    </button>
                </div>

                <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    <Link v-for="event in events.data" :key="event.id" :href="route('events.show', event.slug)"
                        class="group flex flex-col bg-black border-[4px] border-white/10 hover:border-white transition-none overflow-hidden relative ">
                        
                        <div class="relative h-72 overflow-hidden bg-gray-950">
                            <img v-if="event.cover_image_url" :src="event.cover_image_url" :alt="event.name"
                                class="w-full h-full object-cover grayscale opacity-70 group-hover:grayscale-0 group-hover:opacity-100 group-hover:scale-105 transition-all duration-500 ease-out" 
                                @error="handleImageError" 
                            />
                            <div class="absolute inset-0 bg-gradient-to-t from-black via-transparent to-transparent opacity-90 pointer-events-none"></div>

                            <div class="absolute top-4 left-4 z-10 pointer-events-none">
                                <span class="bg-black border border-white px-3 py-1.5 text-[9px] font-mono font-bold uppercase tracking-widest text-white group-hover:bg-red-600 group-hover:text-black group-hover:border-red-600 transition-none shadow-[4px_4px_0_rgba(255,255,255,0.2)] group-hover:shadow-none">
                                    {{ formatDate(event.event_date) }}
                                </span>
                            </div>
                        </div>

                        <div class="p-6 flex-1 flex flex-col relative z-10 border-t-[4px] border-white/10 group-hover:border-white transition-none">
                            <h3 class="text-3xl font-black font-sans text-white mb-4 line-clamp-2 uppercase tracking-tighter leading-none group-hover:text-red-600 transition-none">
                                {{ event.name }}
                            </h3>

                            <p v-if="event.description" class="font-mono text-gray-500 text-[10px] uppercase tracking-widest mb-8 line-clamp-3 leading-relaxed">
                                {{ event.description }}
                            </p>

                            <div class="mt-auto pt-4 border-t border-white/10 flex items-center justify-between text-[9px] font-mono font-bold uppercase tracking-[0.2em]">
                                <div v-if="event.location" class="flex items-center text-gray-400">
                                    <MapPinIcon class="w-3.5 h-3.5 mr-1.5 text-white" />
                                    <span class="truncate max-w-[150px]">{{ event.location }}</span>
                                </div>
                                <div v-else></div>

                                <span class="text-white bg-white/10 px-2 py-1 group-hover:bg-white group-hover:text-black transition-none flex items-center gap-2">
                                    [ ACCEDER ]
                                </span>
                            </div>
                        </div>
                    </Link>
                </div>

                <div v-if="events.data && events.data.length > 0 && events.last_page > 1" class="mt-20 flex justify-center">
                    <div class="flex flex-wrap gap-2">
                        <template v-for="(link, index) in events.links" :key="index">
                            <Link v-if="link.url" :href="link.url" 
                                class="min-w-[40px] h-10 flex items-center justify-center px-4 font-mono text-[11px] font-bold transition-none border-2 border-white/20 hover:border-white"
                                :class="link.active 
                                    ? 'bg-red-600 text-black border-red-600 hover:border-red-600' 
                                    : 'bg-black text-gray-400 hover:text-white'"
                            >
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

/* Efectos Glitch Generales */
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


input[type="date"]::-webkit-calendar-picker-indicator {
    filter: invert(1);
    cursor: pointer;
}
input[type="date"]::-webkit-calendar-picker-indicator:hover {
    filter: invert(0.5) sepia(1) saturate(5) hue-rotate(345deg);
}
</style>