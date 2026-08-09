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
    videoList: { type: Array, default: () => [] },
    banners: Array,
});

const getEventPhotos = (event) => {
    if (event.photos && event.photos.length > 0) {
        return event.photos.slice(0, 6);
    }
    const photos = props.recentPhotos.filter(p => p.event_id == event.id);
    return photos.slice(0, 6);
};

const currentVideo = ref(props.videoList?.length > 0 ? props.videoList[Math.floor(Math.random() * props.videoList.length)] : '/40c665d047c7afa27213c22c2c7b6308_720w.mp4');

const getEventCoverForPhoto = (photo) => {
    if (photo.event && photo.event.cover_image_url) {
        return photo.event.cover_image_url;
    }
    const event = props.recentEvents.find(e => e.id === photo.event_id);
    return event ? event.cover_image_url : null;
};

const formatEventTitle = (name) => {
    if (!name) return { first: 'F33', second: 'EVENT' };
    const words = name.trim().split(' ');
    if (words.length === 1) return { first: words[0], second: '.' };

    const mid = Math.ceil(words.length / 2);
    return {
        first: words.slice(0, mid).join(' '),
        second: words.slice(mid).join(' ')
    };
};
</script>

<template>
    <Head title="F33.click" />

    <AppLayout>
        <div class="f33-theme relative w-full min-h-screen">
            <!-- Fondo oscuro sólido y limpio -->
            <div class="fixed inset-0 z-[0] bg-[#050505] pointer-events-none"></div>

            <main class="relative z-10 pt-0">

                <!-- HERO SWIPER -->
                <Swiper :modules="[Navigation, Pagination, Autoplay, EffectFade]" effect="fade"
                    :fadeEffect="{ crossFade: true }" :autoplay="{ delay: 5000, disableOnInteraction: false }"
                    :pagination="{ clickable: true }" :navigation="true" :loop="true"
                    class="swiper-main h-[70vh] md:h-screen w-full border-b border-white/10">
                    
                    <SwiperSlide v-for="(bannerUrl, index) in banners" :key="index"
                        class="relative overflow-hidden bg-black">

                        <div class="absolute inset-0 w-full h-full z-0">
                            <!-- Imagen estática, sin animaciones de escala -->
                            <img :src="bannerUrl"
                                class="absolute inset-0 w-full h-full object-cover opacity-50"
                                alt="F33 Banner" />
                        </div>

                        <!-- Degradado inferior simple -->
                        <div class="absolute inset-0 bg-gradient-to-t from-[#050505] via-[#050505]/40 to-transparent z-10 pointer-events-none"></div>

                        <div class="absolute inset-0 w-full h-full z-20 pointer-events-none flex flex-col justify-end pb-20 md:pb-32 px-6 md:px-12">
                            <div class="max-w-7xl mx-auto w-full relative">
                                <h1 class="text-6xl md:text-[8rem] lg:text-[10rem] font-black text-white uppercase tracking-tighter leading-[0.85]">
                                    F33
                                </h1>
                            </div>
                        </div>
                    </SwiperSlide>

                    <!-- FALLBACK VACÍO -->
                    <SwiperSlide v-if="!banners || banners.length === 0"
                        class="relative overflow-hidden bg-black flex items-center justify-center">
                        <div class="text-center font-mono text-zinc-500 border border-zinc-800 p-12">
                            <span class="text-[#E30613] text-2xl">>_</span><br>
                            DIR_VACÍO: /public/banners/
                        </div>
                    </SwiperSlide>
                </Swiper>


                <!-- SECCIÓN EVENTOS -->
                <section id="eventos" class="w-full pt-20 pb-0">
                    <div class="px-6 md:px-12 flex flex-col md:flex-row justify-between items-end mb-12">
                        <div>
                            <h2 class="text-4xl md:text-7xl font-black uppercase tracking-tighter text-white mb-2">
                                Eventos <span class="text-[#E30613]">.</span>
                            </h2>
                            <p class="text-gray-400 font-mono text-sm tracking-widest uppercase">
                                Seleccioná un evento para ver su colección de fotos
                            </p>
                        </div>
                    </div>

                    <div class="masonry-container px-6 md:px-12" v-if="recentEvents.length > 0">
                        <Link v-for="event in recentEvents" :key="event.id"
                            :href="route('events.show', event.slug || event.id)"
                            class="masonry-item relative group overflow-hidden bg-black block border border-white/10">

                            <!-- Imagen con transición suave de opacidad (sin zoom) -->
                            <img :src="event.cover_image_url"
                                class="w-full h-auto block opacity-80 group-hover:opacity-30 transition-opacity duration-500">

                            <!-- Overlay de información centrado (fade in) -->
                            <div class="absolute inset-0 flex flex-col justify-center items-center opacity-0 group-hover:opacity-100 transition-opacity duration-300 p-6 text-center">
                                <span class="text-[#E30613] font-mono text-xs font-bold tracking-widest uppercase mb-3 border border-[#E30613] px-3 py-1">
                                    {{ event.is_private ? 'Privado' : 'Público' }}
                                </span>
                                <h3 class="text-white text-3xl md:text-4xl font-black uppercase tracking-tight mb-2">
                                    {{ event.name }}
                                </h3>
                                <p v-if="event.description" class="text-gray-300 text-sm font-light line-clamp-2 max-w-[80%] mb-6">
                                    {{ event.description }}
                                </p>
                                <span class="text-white font-mono text-sm border-b border-white pb-1 hover:text-[#E30613] hover:border-[#E30613] transition-colors">
                                    Ver Galería
                                </span>
                            </div>
                        </Link>
                    </div>

                    <div v-else class="text-center py-32 border border-white/10 mx-6 md:mx-12 bg-[#09090b]">
                        <p class="font-mono text-gray-500 uppercase tracking-widest">Aún no hay eventos registrados.</p>
                    </div>
                </section>


                <!-- SECCIÓN FOTOS RECIENTES -->
                <section v-if="recentPhotos.length > 0" class="w-full pt-32 pb-16 overflow-hidden">
                    <div class="px-6 md:px-12 flex flex-col md:flex-row justify-between items-end mb-12">
                        <div>
                            <h2 class="text-4xl md:text-7xl font-black uppercase tracking-tighter text-white mb-2">
                                Últimas <span class="text-[#E30613]">fotos</span>
                            </h2>
                            <p class="text-gray-400 font-mono text-sm tracking-widest uppercase">
                                Colecciones recientes de nuestros fotógrafos
                            </p>
                        </div>
                        <Link :href="route('gallery.index')"
                            class="hidden md:block text-[#E30613] font-mono font-bold uppercase tracking-widest border-b border-[#E30613] pb-1 hover:text-white hover:border-white transition-colors">
                            [ Ver Archivo Completo ]
                        </Link>
                    </div>

                    <div class="pl-6 md:pl-12">
                        <Swiper :modules="[FreeMode, Autoplay]" :slidesPerView="1.2" :spaceBetween="16" :freeMode="true"
                            :grabCursor="true"
                            :breakpoints="{ '640': { slidesPerView: 2.2 }, '1024': { slidesPerView: 3.5 }, '1536': { slidesPerView: 4.5 } }"
                            class="w-full !overflow-visible">

                            <SwiperSlide v-for="photo in recentPhotos.slice(0, 8)" :key="photo.id">
                                <div @click="router.visit(route('gallery.show', photo.unique_id))"
                                    class="relative aspect-[3/4] bg-black group overflow-hidden border border-white/10 block w-full h-full cursor-pointer">

                                    <!-- Foto limpia sin transformaciones de escala -->
                                    <img :src="photo.watermarked_url || photo.thumbnail_url"
                                        class="w-full h-full object-cover filter contrast-125 opacity-90 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none select-none" />

                                    <!-- Barra de información inferior siempre visible -->
                                    <div class="absolute bottom-0 left-0 right-0 flex items-center gap-3 bg-gradient-to-t from-black via-black/80 to-transparent p-4 pt-12 border-t border-transparent group-hover:border-[#E30613] transition-colors duration-300">
                                        
                                        <div class="flex-1 min-w-0">
                                            <p class="text-white font-black text-sm uppercase tracking-tighter truncate">
                                                {{ photo.event_name || 'Operación X' }}
                                            </p>
                                            <p class="text-gray-400 font-mono text-[10px] tracking-widest mt-1">
                                                ID: <span class="text-[#E30613]">{{ photo.unique_id }}</span>
                                            </p>
                                        </div>

                                        <div class="w-8 h-8 flex-shrink-0 bg-[#09090b] border border-white/20 overflow-hidden">
                                            <img v-if="getEventCoverForPhoto(photo)" :src="getEventCoverForPhoto(photo)"
                                                class="w-full h-full object-cover grayscale opacity-80">
                                            <span v-else class="flex items-center justify-center w-full h-full text-[8px] text-zinc-500 font-black">F33</span>
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
.f33-theme {
    font-family: 'Outfit', sans-serif;
    background-color: #050505;
    color: #ffffff;
}

.font-mono { 
    font-family: 'JetBrains Mono', monospace; 
}

 
.swiper-main {
    width: 100%;
}

.swiper-main>.swiper-pagination {
    bottom: 2rem !important;
}

.swiper-main>.swiper-pagination>.swiper-pagination-bullet {
    width: 8px;
    height: 8px;
    background-color: #ffffff;
    opacity: 0.3;
    border-radius: 0;
    transition: all 0.3s ease;
}

.swiper-main>.swiper-pagination>.swiper-pagination-bullet-active {
    background-color: #E30613;
    opacity: 1;
    width: 24px;
}

.swiper-main>.swiper-button-next,
.swiper-main>.swiper-button-prev {
    color: white !important;
    opacity: 0;
    transition: opacity 0.3s ease;
}

.swiper-main:hover>.swiper-button-next,
.swiper-main:hover>.swiper-button-prev {
    opacity: 0.8;
}

.swiper-main>.swiper-button-next:hover,
.swiper-main>.swiper-button-prev:hover {
    color: #E30613 !important;
    opacity: 1 !important;
}
</style>