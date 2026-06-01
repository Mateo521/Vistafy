<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';


import { Swiper, SwiperSlide } from 'swiper/vue';
import { Navigation, Pagination, Autoplay, EffectFade, FreeMode } from 'swiper/modules';
import 'swiper/css';
import 'swiper/css/navigation';
import 'swiper/css/pagination';
import 'swiper/css/effect-fade';
import 'swiper/css/free-mode';

const props = defineProps({
    recentEvents: { type: Array, default: () => [] },
    recentPhotos: { type: Array, default: () => [] },
    videoList: { type: Array, default: () => [] }
});


const currentVideo = ref(props.videoList?.length > 0 ? props.videoList[Math.floor(Math.random() * props.videoList.length)] : '/40c665d047c7afa27213c22c2c7b6308_720w.mp4');


const getEventCoverForPhoto = (photo) => {
    if (photo.event && photo.event.cover_image_url) {
        return photo.event.cover_image_url;
    }
    const event = props.recentEvents.find(e => e.id === photo.event_id);
    return event ? event.cover_image_url : null;
};
</script>

<template>
    <Head title="F33 | Premium Event Photography" />

    <AppLayout>
        <div class="f33-theme relative w-full min-h-screen">
            
            <div class="fixed inset-0 z-[0] opacity-10 pointer-events-none"
                style="background-image: url('data:image/svg+xml,%3Csvg width=%2260%22 height=%2260%22 viewBox=%220 0 60 60%22 xmlns=%22http://www.w3.org/2000/svg%22%3E%3Cg fill=%22none%22 fill-rule=%22evenodd%22%3E%3Cg fill=%22%23ffffff%22 fill-opacity=%221%22%3E%3Cpath d=%22M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z%22/%3E%3C/g%3E%3C/g%3E%3C/svg%3E'); background-attachment: fixed;">
            </div>

            <main class="relative z-10 pt-0">
                
                <Swiper 
                    :modules="[Navigation, Pagination, Autoplay, EffectFade]" 
                    effect="fade" 
                    :fadeEffect="{ crossFade: true }"
                    :autoplay="{ delay: 6000, disableOnInteraction: false }"
                    :pagination="{ clickable: true }"
                    :navigation="true"
                    :loop="true" 
                    class="swiper-main">

                    <SwiperSlide class="relative flex items-center justify-center">
                        <video autoplay muted loop playsinline class="absolute inset-0 w-full h-full object-cover grayscale opacity-60 z-[-2]">
                            <source :src="currentVideo" type="video/mp4">
                        </video>
                        <div class="absolute inset-0 bg-gradient-to-t from-[#050505] via-[#050505]/40 to-transparent z-[-1]"></div>
                        <div class="w-full px-6 md:px-12 flex flex-col justify-end h-full pb-32">
                            <span class="text-[#FF0000] font-mono font-bold tracking-widest mb-4 flex items-center gap-4">
                                <span class="w-12 h-[2px] bg-[#FF0000]"></span> 1
                            </span>
                            <h1 class="text-6xl md:text-8xl lg:text-[10rem] font-black tracking-tighter leading-[0.85] uppercase">
                                Visiones <br><span class="text-transparent" style="-webkit-text-stroke: 2px white;">Nocturnas</span>
                            </h1>
                            <p class="mt-8 max-w-xl text-lg text-gray-300 font-light pl-6">
                                Cobertura documental e inmersiva para la escena underground, festivales y eventos corporativos de alto impacto.
                            </p>
                        </div>
                    </SwiperSlide>

                    <SwiperSlide v-if="recentEvents.length > 0" class="relative flex items-center justify-center">
                        <img :src="recentEvents[0].cover_image_url" class="absolute inset-0 w-full h-full object-cover grayscale opacity-60 z-[-2]">
                        <div class="absolute inset-0 bg-gradient-to-t from-[#050505] via-[#050505]/40 to-transparent z-[-1]"></div>
                        <div class="w-full px-6 md:px-12 flex flex-col justify-end h-full pb-32">
                            <span class="text-[#FF0000] font-mono font-bold tracking-widest mb-4 flex items-center gap-4">
                                <span class="w-12 h-[2px] bg-[#FF0000]"></span> 2
                            </span>
                            <h1 class="text-6xl md:text-8xl lg:text-[10rem] font-black tracking-tighter leading-[0.85] uppercase">
                                Captura <br><span class="text-[#FF0000]">Raw</span>
                            </h1>
                            <p class="mt-8 max-w-xl text-lg text-gray-300 font-light pl-6">
                                Sin filtros artificiales. Inmortalizamos la energía cruda y real de cada momento en {{ recentEvents[0].name }}.
                            </p>
                        </div>
                    </SwiperSlide>
                </Swiper>




                <section id="eventos" class="w-full pt-20 pb-0 bg-[#050505]">
                    <div class="px-6 md:px-12 flex flex-col md:flex-row justify-between items-end mb-12">
                        <div>
                            <h2 class="text-4xl md:text-7xl font-black uppercase tracking-tighter mb-2">Eventos <span class="text-[#FF0000]">.</span></h2>
                            <p class="text-gray-400 font-mono text-sm tracking-widest uppercase">Seleccioná un evento para ver su colección de fotos</p>
                        </div>
                    </div>

                    <div class="masonry-container px-1" v-if="recentEvents.length > 0">
                        <Link v-for="event in recentEvents" :key="event.id" :href="route('events.show', event.slug || event.id)" class="masonry-item relative group overflow-hidden bg-[#09090b] block cursor-crosshair">
                            
                            <img :src="event.cover_image_url" class="w-full h-auto block grayscale group-hover:grayscale-0 transition-all duration-700 group-hover:scale-105 opacity-80 group-hover:opacity-100">

                            <div class="absolute inset-0 bg-[#050505]/80 flex flex-col justify-center items-center opacity-0 group-hover:opacity-100 transition-opacity duration-300 p-6 text-center">
                                <span class="text-[#FF0000] font-mono text-xs font-bold tracking-widest uppercase mb-3 border border-[#FF0000] px-3 py-1">
                                    {{ event.is_private ? 'Privado' : 'Público' }}
                                </span>
                                <h3 class="text-white text-3xl font-black uppercase tracking-tight mb-2">{{ event.name }}</h3>
                                <p v-if="event.description" class="text-gray-300 text-sm font-light line-clamp-2 max-w-[80%] mb-6">
                                    {{ event.description }}
                                </p>
                                <span class="text-white font-mono text-lg border-b-2 border-[#FF0000] hover:text-[#FF0000] transition-colors">
                                    Ver Archivos
                                </span>
                            </div>
                        </Link>
                    </div>

                    <div v-else class="text-center py-32 border border-white/10 mx-6 bg-[#09090b]">
                        <p class="font-mono text-gray-500 uppercase tracking-widest">Aún no hay eventos registrados.</p>
                    </div>
                </section>


                
                <section v-if="recentPhotos.length > 0" class="w-full pt-32 pb-16 overflow-hidden bg-[#050505]">
                    <div class="px-6 md:px-12 flex flex-col md:flex-row justify-between items-end mb-12">
                        <div>
                            <h2 class="text-4xl md:text-7xl font-black uppercase tracking-tighter mb-2">
                                Últimas <span class="text-[#FF0000]">fotos</span>
                            </h2>
                            <p class="text-gray-400 font-mono text-sm tracking-widest uppercase">
                                Colecciones de fotos de nuestros fotógrafos
                            </p>
                        </div>
                        <Link :href="route('gallery.index')" class="hidden md:block text-[#FF0000] font-mono font-bold uppercase tracking-widest border-b border-[#FF0000] pb-1 hover:text-white hover:border-white transition-colors">
                            [ Ver más ]
                        </Link>
                    </div>

                    <div class="pl-6 md:pl-12">
                        <Swiper 
                            :modules="[FreeMode, Autoplay]"
                            :slidesPerView="1.2"
                            :spaceBetween="16"
                            :freeMode="true"
                            :grabCursor="true"
                            :breakpoints="{ '640': { slidesPerView: 2.2 }, '1024': { slidesPerView: 3.5 }, '1536': { slidesPerView: 4.5 } }"
                            class="w-full !overflow-visible">
                            
                            <SwiperSlide v-for="photo in recentPhotos.slice(0, 5)" :key="photo.id">
                                <div @click="router.visit(route('gallery.show', photo.unique_id))" class="relative aspect-[3/4] bg-[#09090b] group overflow-hidden border border-white/10 cursor-crosshair block w-full h-full">
                                    
                                    <img :src="photo.watermarked_url || photo.thumbnail_url" 
                                        class="w-full h-full object-cover filter grayscale contrast-125 group-hover:grayscale-0 transition-all duration-700 group-hover:scale-105 pointer-events-none select-none" />
                                    
                                    <div class="absolute bottom-4 left-4 right-4 flex items-center gap-3 bg-[#050505]/90 p-3 border border-white/10 backdrop-blur-md translate-y-4 opacity-0 group-hover:translate-y-0 group-hover:opacity-100 transition-all duration-300">
                                        
                                        <div class="w-12 h-12 flex-shrink-0 bg-black border border-white/20 overflow-hidden">
                                            <img v-if="getEventCoverForPhoto(photo)" :src="getEventCoverForPhoto(photo)" class="w-full h-full object-cover">
                                            <span v-else class="flex items-center justify-center w-full h-full text-[8px] text-red-600 font-black">f33</span>
                                        </div>
                                        
                                        <div class="flex-1 min-w-0">
                                            <p class="text-white font-black text-sm uppercase tracking-tighter truncate">
                                                {{ photo.event_name || 'Operación X' }}
                                            </p>
                                            <p class="text-[#FF0000] font-mono text-[9px] font-bold tracking-widest mt-0.5">
                                                ID: {{ photo.unique_id }}
                                            </p>
                                        </div>
                                    </div>

                                </div>
                            </SwiperSlide>
                        </Swiper>
                    </div>
                </section>


            </main>
        </div>
    </AppLayout>
</template>

<style>

@import url('https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800;900&family=JetBrains+Mono:wght@400;700&display=swap');

.f33-theme {
    font-family: 'Outfit', sans-serif;
    background-color: #050505;
    color: #ffffff;
}

.f33-theme .font-mono {
    font-family: 'JetBrains Mono', monospace;
}


.swiper-main {
    width: 100%;
    height: 100vh;
}

.f33-theme .swiper-pagination-bullet {
    width: 12px;
    height: 12px;
    background-color: transparent;
    border: 2px solid #ffffff;
    opacity: 0.5;
    transition: all 0.3s ease;
}

.f33-theme .swiper-pagination-bullet-active {
    background-color: #FF0000;
    border-color: #FF0000;
    opacity: 1;
    width: 30px;
    border-radius: 10px;
}

.f33-theme .swiper-button-next,
.f33-theme .swiper-button-prev {
    color: white !important;
    opacity: 0;
    transition: opacity 0.3s ease;
}

.swiper-main:hover .swiper-button-next,
.swiper-main:hover .swiper-button-prev {
    opacity: 0.8;
}

.f33-theme .swiper-button-next:hover,
.f33-theme .swiper-button-prev:hover {
    color: #FF0000 !important;
    opacity: 1 !important;
}


.masonry-container {
    column-count: 1;
    column-gap: 4px;
}

@media (min-width: 640px) { .masonry-container { column-count: 2; } }
@media (min-width: 768px) { .masonry-container { column-count: 3; } }
@media (min-width: 1024px) { .masonry-container { column-count: 4; } }
@media (min-width: 1536px) { .masonry-container { column-count: 5; } }

.masonry-item {
    break-inside: avoid;
    margin-bottom: 4px;
}


.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>