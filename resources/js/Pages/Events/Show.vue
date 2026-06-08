<script setup>
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import { ArrowLeftIcon, FunnelIcon, MagnifyingGlassIcon, HashtagIcon, MapPinIcon } from '@heroicons/vue/24/outline';
import ProtectedImage from '@/Components/ProtectedImage.vue';
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
        month: 'short',
        day: 'numeric'
    });
};

// Placeholder Brutalista
const handleImageError = (e) => {
    e.target.style.display = 'none';
    const parent = e.target.parentElement;
    if (!parent.querySelector('.placeholder-img')) {
        const placeholder = document.createElement('div');
        placeholder.className = 'placeholder-img w-full h-full min-h-[250px] flex items-center justify-center bg-gray-950 border-[4px] border-red-600/30';
        placeholder.innerHTML = `<span class="font-mono text-[10px] text-red-600 uppercase tracking-widest">[ SEÑAL PERDIDA ]</span>`;
        parent.appendChild(placeholder);
    }
};

// Inicializador del Glitch
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
            const gx1 = random(-25, 25).toFixed(1) + 'px';
            const gx2 = random(-25, 25).toFixed(1) + 'px';
            const gh1 = random(-30, 30).toFixed(1) + 'deg';
            const gh2 = random(-30, 30).toFixed(1) + 'deg';
            const duration = random(3, 8).toFixed(1) + 's';
            const delay = random(0, 3).toFixed(1) + 's';

            html += `
                <div class="glitch-strip" 
                     style="
                        height: ${actualHeight}px; 
                        background-image: url('${imgUrl}');
                        background-size: ${width}px ${height}px; 
                        background-position: 0px -${i}px;
                        --glitch-x-1: ${gx1};
                        --glitch-x-2: ${gx2};
                        --glitch-hue-1: ${gh1};
                        --glitch-hue-2: ${gh2};
                        animation-duration: ${duration};
                        animation-delay: -${delay};
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

    <Head :title="event.name + ' — F33'" />

    <AppLayout>

        <div class="border-b border-white/20 bg-black/90 backdrop-blur-sm sticky top-0 z-30 pt-16 md:pt-0">
            <div
                class="max-w-[1500px] mx-auto px-4 md:px-8 h-14 flex items-center font-mono text-[10px] uppercase tracking-widest">
                <Link :href="route('events.index')"
                    class="text-gray-400 hover:text-white flex items-center gap-3 transition-none border border-transparent hover:border-white px-3 py-1">
                    <ArrowLeftIcon class="w-3.5 h-3.5" /> [ Archivos ]
                </Link>
            </div>
        </div>

        <div
            class="relative bg-black pt-20 pb-16 md:pt-32 md:pb-24 border-b-[12px] border-white group  overflow-hidden">
            <div v-if="event.cover_image_url"
                class="glitch-image-container absolute inset-0 w-full h-full overflow-hidden -z-10 opacity-30 grayscale contrast-125"
                :data-img="event.cover_image_url"></div>
            <div v-else class="absolute inset-0 w-full h-full bg-gray-950 -z-10"></div>

            <div class="max-w-[1500px] mx-auto px-4 md:px-8">
                <div class="pointer-events-none">
                    <p class="font-mono text-xs uppercase text-red-600 mb-4  border-red-600 pl-3">
                        // {{ formatDate(event.event_date) }}
                    </p>
                    <h1
                        class="font-black text-[10vw] md:text-[7rem] leading-[0.8] text-white tracking-tighter mix-blend-difference mb-6">
                        {{ event.name }}
                    </h1>

                    <div
                        class="flex flex-wrap items-center gap-6 font-mono text-[10px] font-bold uppercase tracking-widest text-gray-400 mt-8">
                        <span v-if="event.location"
                            class="flex items-center gap-2 border border-white/20 px-3 py-1.5 bg-black/50">
                            <MapPinIcon class="w-3.5 h-3.5 text-red-600" /> {{ event.location }}
                        </span>
                        <span class="border border-white/20 px-3 py-1.5 bg-black/50 text-white">
                            VOLUMEN: <span class="text-red-600">{{ event.photos_count }}</span> FRAMES
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <div class="min-h-screen bg-black text-white font-sans pb-24">
            <div class="max-w-[1500px] mx-auto px-4 md:px-8 pt-16">

                <div class="flex flex-col lg:flex-row gap-12 lg:gap-16 mb-16 border-b border-white/20 pb-16">

                    <div class="lg:w-7/12">
                        <h3
                            class="font-mono text-[10px] font-bold uppercase tracking-[0.35em] text-gray-500 mb-6 border-b border-white/10 pb-2">
                            / REPORTE DESCRIPTIVO
                        </h3>
                        <p class="font-mono text-sm text-gray-400 uppercase tracking-widest leading-relaxed mb-6">
                            {{ event.description || 'SIN DATOS DESCRIPTIVOS ASIGNADOS AL EVENTO.' }}
                        </p>
                        <p v-if="event.long_description"
                            class="font-mono text-[11px] text-gray-600 uppercase tracking-widest leading-relaxed">
                            {{ event.long_description }}
                        </p>
                    </div>

                    <div class="lg:w-5/12 flex flex-col gap-6">

                        <div
                            class="bg-gray-950 border-[4px] border-white/20 p-6 relative overflow-hidden transition-none group hover:border-white">
                            <div
                                class="absolute -right-8 -bottom-8 text-white/5 pointer-events-none group-hover:text-red-600/5 transition-none">
                                <FunnelIcon class="w-48 h-48" />
                            </div>

                            <h3
                                class="font-mono text-[10px] font-bold uppercase  text-white mb-6 border-b border-white/10 pb-2">
                                [ FILTRAR POR FOTÓGRAFO ]
                            </h3>

                            <div class="space-y-3 relative z-10 font-mono text-[10px] tracking-widest uppercase">
                                <label class="flex items-center cursor-pointer group/label">
                                    <input type="radio" v-model="selectedPhotographer" value="all"
                                        @change="filterByPhotographer" class="sr-only">
                                    <div class="w-4 h-4 border-2 mr-3 flex items-center justify-center transition-none"
                                        :class="selectedPhotographer === 'all' ? 'border-red-600' : 'border-gray-600 group-hover/label:border-white'">
                                        <div v-if="selectedPhotographer === 'all'" class="w-2 h-2 bg-red-600"></div>
                                    </div>
                                    <span class="font-bold transition-none"
                                        :class="selectedPhotographer === 'all' ? 'text-white' : 'text-gray-500 group-hover/label:text-white'">
                                        MOSTRAR TODO EL VOLUMEN
                                    </span>
                                </label>

                                <label v-for="photographer in photographers" :key="photographer.id"
                                    class="flex items-center cursor-pointer group/label">
                                    <input type="radio" v-model="selectedPhotographer" :value="photographer.id"
                                        @change="filterByPhotographer" class="sr-only">
                                    <div class="w-4 h-4 border-2 mr-3 flex items-center justify-center transition-none"
                                        :class="selectedPhotographer === photographer.id ? 'border-red-600' : 'border-gray-600 group-hover/label:border-white'">
                                        <div v-if="selectedPhotographer === photographer.id" class="w-2 h-2 bg-red-600">
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-3 w-full">
                                        <span class="font-bold truncate transition-none"
                                            :class="selectedPhotographer === photographer.id ? 'text-white' : 'text-gray-500 group-hover/label:text-white'">
                                            {{ photographer.business_name || photographer.user.name }}
                                        </span>
                                        <span
                                            class="ml-auto text-[9px] text-gray-500 bg-black border border-white/20 px-1.5 py-0.5">
                                            {{ photographer.photos_count }}
                                        </span>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <div class="flex flex-col gap-3">
                            <Link :href="route('events.face-search', event.slug)"
                                class="w-full flex items-center justify-between bg-red-600 text-black hover:bg-white transition-none px-6 py-4 font-black font-sans text-xl uppercase tracking-tighter group">
                                <span class="flex items-center gap-3">
                                    <MagnifyingGlassIcon class="w-6 h-6" /> ESCÁNER BIOMÉTRICO
                                </span>
                                <span
                                    class="font-mono text-xs opacity-50 group-hover:translate-x-2 transition-transform">→</span>
                            </Link>

                            <Link :href="route('events.bib-search', event.slug)"
                                class="w-full flex items-center justify-between bg-black border-2 border-white text-white hover:bg-white hover:text-black transition-none px-6 py-4 font-black font-sans text-xl uppercase tracking-tighter group">
                                <span class="flex items-center gap-3">
                                    <HashtagIcon class="w-6 h-6" /> ANÁLISIS OCR (DORSAL)
                                </span>
                                <span
                                    class="font-mono text-xs opacity-50 group-hover:translate-x-2 transition-transform">→</span>
                            </Link>
                        </div>

                    </div>
                </div>

                <div v-if="photos.data && photos.data.length > 0">
                    <div class="columns-2 md:columns-3 lg:columns-4 xl:columns-5 gap-2 space-y-2 masonry-grid mb-16">

                        <div v-for="photo in photos.data" :key="photo.id"
                            @click="router.visit(route('gallery.show', photo.unique_id))" @contextmenu.prevent
                            class="break-inside-avoid block group relative bg-gray-950 overflow-hidden border-[4px] border-black hover:border-red-600 transition-none  w-full h-auto">



                            <ProtectedImage :src="photo.thumbnail_url" :alt="photo.unique_id"
                                class="w-full h-auto object-cover grayscale opacity-70 group-hover:grayscale-0 group-hover:opacity-100 transition-none pointer-events-none"
                                loading="lazy" @error="handleImageError" />


                            <div
                                class="absolute inset-0 bg-red-600 mix-blend-overlay opacity-0 group-hover:opacity-20 transition-none pointer-events-none">
                            </div>

                            <div class="absolute top-2 left-2 pointer-events-none">
                                <span
                                    class="bg-black border border-white text-white font-mono text-[9px] font-bold uppercase tracking-widest px-2 py-1 shadow-lg block mb-1 w-max opacity-0 group-hover:opacity-100 transition-none">
                                    [ INSPECCIONAR ]
                                </span>
                                <span
                                    class="bg-black text-red-600 font-mono text-[9px] font-bold uppercase tracking-widest px-2 py-1 w-max">
                                    #{{ photo.unique_id }}
                                </span>
                            </div>

                            <div
                                class="absolute bottom-2 right-2 bg-black text-white px-2 py-1 text-[9px] font-mono font-bold uppercase tracking-widest border border-white/20 opacity-0 group-hover:opacity-100 transition-none pointer-events-none">
                                {{ photo.photographer_name }}
                            </div>
                        </div>

                    </div>

                    <div v-if="photos.last_page > 1" class="flex justify-center pt-8 border-t border-white/20">
                        <div class="flex flex-wrap gap-2">
                            <template v-for="(link, index) in photos.links" :key="index">
                                <Link v-if="link.url" :href="link.url" v-html="link.label"
                                    class="min-w-[40px] h-10 flex items-center justify-center px-4 font-mono text-[11px] font-bold transition-none border-2 border-white/20 hover:border-white"
                                    :class="link.active ? 'bg-red-600 text-black border-red-600 hover:border-red-600' : 'bg-black text-gray-400 hover:text-white'" />
                                <span v-else v-html="link.label"
                                    class="min-w-[40px] h-10 flex items-center justify-center px-4 font-mono text-[11px] font-bold text-gray-700 border-2 border-transparent"></span>
                            </template>
                        </div>
                    </div>
                </div>

                <div v-else
                    class="flex flex-col items-center justify-center py-32 border-4 border-dashed border-gray-800 bg-gray-950 text-center">
                    <div class="w-16 h-16 text-gray-600 mb-6">
                        <FunnelIcon class="w-full h-full" />
                    </div>
                    <h3 class="font-black text-4xl md:text-6xl text-gray-700 tracking-tighter mb-4 uppercase">NULO.</h3>
                    <p class="font-mono text-xs text-gray-500 tracking-widest uppercase mb-8">
                        EL FOTÓGRAFO SELECCIONADO NO REGISTRA ARCHIVOS EN ESTE EVENTO.
                    </p>
                    <button v-if="selectedPhotographer !== 'all'"
                        @click="selectedPhotographer = 'all'; filterByPhotographer()"
                        class="border-2 border-red-600 bg-black text-red-600 hover:bg-red-600 hover:text-black px-8 py-3 font-mono text-[10px] font-bold uppercase tracking-widest transition-none">
                        [ PURGAR FILTRO Y VER TODO ]
                    </button>
                </div>

            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
.masonry-grid {
    column-fill: balance;
}


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

    0%,
    33.33%,
    43.33%,
    66.67%,
    76.67%,
    100% {
        transform: none;
        filter: hue-rotate(0) drop-shadow(0 0 0 transparent);
    }

    33.43%,
    43.23% {
        transform: translateX(var(--glitch-x-1));
        filter: hue-rotate(var(--glitch-hue-1)) drop-shadow(3px 0 0 rgba(220, 38, 38, 0.6));
    }

    66.77%,
    76.57% {
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