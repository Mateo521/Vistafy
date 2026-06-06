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
    if (words.length === 1) return { first: words[0], second: 'RAW' };

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
            <div class="fixed inset-0 z-[0] opacity-10 pointer-events-none fondo2"></div>

            <main class="relative z-10 pt-0">

                <Swiper :modules="[Navigation, Pagination, Autoplay, EffectFade]" effect="fade"
                    :fadeEffect="{ crossFade: true }" :autoplay="{ delay: 8000, disableOnInteraction: false }"
                    :pagination="{ clickable: true }" :navigation="true" :loop="true" class="swiper-main">

                    <SwiperSlide v-for="(event, index) in recentEvents.slice(0, 3)" :key="event.id"
                        class="relative bg-[#050505] overflow-hidden">

                        <div class="absolute inset-0 w-full h-full z-0">
                            <Swiper v-if="getEventPhotos(event).length > 0" :modules="[Pagination, Autoplay]"
                                :nested="true" :autoplay="{ delay: 2500, disableOnInteraction: false }"
                                :pagination="{ clickable: true, el: '.inner-pagination-' + event.id }"
                                class="w-full h-full inner-swiper">
                                <SwiperSlide v-for="photo in getEventPhotos(event)" :key="photo.id">
                                    <img :src="photo.watermarked_url || photo.thumbnail_url"
                                        class="w-full h-full object-cover object-center opacity-50">
                                </SwiperSlide>

                                <div :class="'swiper-pagination inner-pagination-' + event.id"></div>
                            </Swiper>

                            <img v-else-if="event.cover_image_url" :src="event.cover_image_url"
                                class="w-full h-full object-cover object-center opacity-50">

                            <video v-else autoplay muted loop playsinline class="w-full h-full object-cover opacity-50">
                                <source :src="currentVideo" type="video/mp4">
                            </video>
                        </div>

                        <div
                            class="absolute inset-0 bg-gradient-to-t from-[#050505]/90 via-[#050505]/40 to-transparent z-10 pointer-events-none">
                        </div>

                        <div
                            class="absolute inset-0 w-full h-full px-6 md:px-12 flex flex-col justify-end pb-32 z-20 pointer-events-none">

                            <div class="flex items-end justify-between mb-4">
                                <span
                                    class="text-[#E30613] font-flux font-bold tracking-widest flex items-center gap-4">
                                    <span class="w-12 h-[2px] bg-[#E30613]"></span> 0{{ index + 1 }}
                                </span>

                                <div v-if="event.cover_image_url"
                                    class="hidden sm:block w-20 h-20 md:w-28 md:h-28 bg-[#09090b] border border-white/20 p-1.5 shadow-2xl relative">
                                    <div
                                        class="absolute -top-2 -right-2 bg-[#E30613] text-white text-[8px] font-flux font-bold px-1 uppercase tracking-widest z-10">
                                        ID: {{ event.id }}
                                    </div>
                                    <img :src="event.cover_image_url"
                                        class="w-full h-full object-cover grayscale contrast-125" alt="Logo Evento">
                                </div>
                            </div>

                            <h1
                                class="text-4xl md:text-6xl lg:text-[8rem] font-flux tracking-tighter leading-[0.85] uppercase">
                                {{ formatEventTitle(event.name).first }} <br>
                                <span :class="index % 2 === 0 ? 'text-transparent' : 'text-[#E30613]'"
                                    :style="index % 2 === 0 ? '-webkit-text-stroke: 2px white;' : ''">
                                    {{ formatEventTitle(event.name).second }}
                                </span>
                            </h1>

                            <p class="mt-8 max-w-xl text-lg text-gray-300 font-lato pl-6 line-clamp-2">
                                {{ event.description || 'Cobertura inmersiva. Captura cruda para revivir la energía del momento.' }}
                            </p>

                            <div class="mt-8 pl-6 pointer-events-auto w-max">
                                <Link :href="route('events.show', event.slug || event.id)"
                                    class="inline-block bg-[#E30613] border border-[#E30613] text-white font-flux text-[10px] font-bold uppercase tracking-widest px-8 py-4 hover:bg-white hover:text-black hover:border-white transition-colors duration-300">
                                    [ Ver Archivo ]
                                </Link>
                            </div>
                        </div>

                    </SwiperSlide>



                </Swiper>


                <section id="eventos" class="w-full pt-20 pb-0 bg-[#050505]">
                    <div class="px-6 md:px-12 flex flex-col md:flex-row justify-between items-end mb-12">
                        <div>
                            <h2 class="text-4xl md:text-7xl font-black uppercase tracking-tighter mb-2">Eventos <span
                                    class="text-[#E30613]">.</span></h2>
                            <p class="text-gray-400 font-flux text-sm tracking-widest uppercase">Seleccioná un evento
                                para ver su colección de fotos</p>
                        </div>
                    </div>

                    <div class="masonry-container px-1" v-if="recentEvents.length > 0">
                        <Link v-for="event in recentEvents" :key="event.id"
                            :href="route('events.show', event.slug || event.id)"
                            class="masonry-item relative group overflow-hidden bg-[#09090b] block ">

                            <img :src="event.cover_image_url"
                                class="w-full h-auto block grayscale group-hover:grayscale-0 transition-all duration-700 group-hover:scale-105 opacity-80 group-hover:opacity-100">

                            <div
                                class="absolute inset-0 bg-[#050505]/80 flex flex-col justify-center items-center opacity-0 group-hover:opacity-100 transition-opacity duration-300 p-6 text-center">
                                <span
                                    class="text-[#E30613] font-flux text-xs font-bold tracking-widest uppercase mb-3 border border-[#E30613] px-3 py-1">
                                    {{ event.is_private ? 'Privado' : 'Público' }}
                                </span>
                                <h3 class="text-white text-3xl font-black uppercase tracking-tight mb-2">{{ event.name
                                    }}</h3>
                                <p v-if="event.description"
                                    class="text-gray-300 text-sm font-light line-clamp-2 max-w-[80%] mb-6">
                                    {{ event.description }}
                                </p>
                                <span
                                    class="text-white font-flux text-lg border-b-2 border-[#E30613] hover:text-[#E30613] transition-colors">
                                    Ver más
                                </span>
                            </div>
                        </Link>
                    </div>

                    <div v-else class="text-center py-32 border border-white/10 mx-6 bg-[#09090b]">
                        <p class="font-flux text-gray-500 uppercase tracking-widest">Aún no hay eventos registrados.</p>
                    </div>
                </section>


                <section v-if="recentPhotos.length > 0" class="w-full pt-32 pb-16 overflow-hidden bg-[#050505]">
                    <div class="px-6 md:px-12 flex flex-col md:flex-row justify-between items-end mb-12">
                        <div>
                            <h2 class="text-4xl md:text-7xl font-black uppercase tracking-tighter mb-2">
                                Últimas <span class="text-[#E30613]">fotos</span>
                            </h2>
                            <p class="text-gray-400 font-flux text-sm tracking-widest uppercase">
                                Colecciones de fotos de nuestros fotógrafos
                            </p>
                        </div>
                        <Link :href="route('gallery.index')"
                            class="hidden md:block text-[#E30613] font-flux font-bold uppercase tracking-widest border-b border-[#E30613] pb-1 hover:text-white hover:border-white transition-colors">
                            [ Ver más ]
                        </Link>
                    </div>

                    <div class="pl-6 md:pl-12">
                        <Swiper :modules="[FreeMode, Autoplay]" :slidesPerView="1.2" :spaceBetween="16" :freeMode="true"
                            :grabCursor="true"
                            :breakpoints="{ '640': { slidesPerView: 2.2 }, '1024': { slidesPerView: 3.5 }, '1536': { slidesPerView: 4.5 } }"
                            class="w-full !overflow-visible">

                            <SwiperSlide v-for="photo in recentPhotos.slice(0, 5)" :key="photo.id">
                                <div @click="router.visit(route('gallery.show', photo.unique_id))"
                                    class="relative aspect-[3/4] bg-[#09090b] group overflow-hidden border border-white/10  block w-full h-full">

                                    <img :src="photo.watermarked_url || photo.thumbnail_url"
                                        class="w-full h-full object-cover filter grayscale contrast-125 group-hover:grayscale-0 transition-all duration-700 group-hover:scale-105 pointer-events-none select-none" />

                                    <div
                                        class="absolute bottom-4 left-4 right-4 flex items-center gap-3 bg-[#050505]/90 p-3 border border-white/10 backdrop-blur-md translate-y-4 opacity-0 group-hover:translate-y-0 group-hover:opacity-100 transition-all duration-300">
                                        <div
                                            class="w-12 h-12 flex-shrink-0 bg-black border border-white/20 overflow-hidden">
                                            <img v-if="getEventCoverForPhoto(photo)" :src="getEventCoverForPhoto(photo)"
                                                class="w-full h-full object-cover">
                                            <span v-else
                                                class="flex items-center justify-center w-full h-full text-[8px] text-red-600 font-black">f33</span>
                                        </div>

                                        <div class="flex-1 min-w-0">
                                            <p
                                                class="text-white font-black text-sm uppercase tracking-tighter truncate">
                                                {{ photo.event_name || 'Operación X' }}
                                            </p>
                                            <p
                                                class="text-[#E30613] font-flux text-[9px] font-bold tracking-widest mt-0.5">
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
.f33-theme {
    font-family: 'Lato', sans-serif;
    background-color: #050505;
    color: #ffffff;
}



.swiper-main {
    width: 100%;
    height: 100vh;
}


.swiper-main>.swiper-pagination {
    bottom: 2rem !important;
}

.swiper-main>.swiper-pagination>.swiper-pagination-bullet {
    width: 12px;
    height: 12px;
    background-color: transparent;
    border: 2px solid #ffffff;
    opacity: 0.5;
    transition: all 0.3s ease;
}

.swiper-main>.swiper-pagination>.swiper-pagination-bullet-active {
    background-color: #E30613;
    border-color: #E30613;
    opacity: 1;
    width: 30px;
    border-radius: 10px;
}

.swiper-main>.swiper-button-next,
.swiper-main>.swiper-button-prev {
    color: white !important;
    opacity: 0;
    transition: opacity 0.3s ease;
    z-index: 50;
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


.inner-swiper .swiper-pagination {
    top: 100px !important;
    bottom: auto !important;
    text-align: center;
    width: 100%;
    z-index: 30;
}

.inner-swiper .swiper-pagination-bullet {
    width: 6px;
    height: 6px;
    background-color: #ffffff;
    opacity: 0.3;
    transition: all 0.3s ease;
    margin: 0 4px !important;
}

.inner-swiper .swiper-pagination-bullet-active {
    background-color: #E30613;
    opacity: 1;
    width: 18px;
    border-radius: 4px;
}
</style>