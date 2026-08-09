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
        <div class="f33-theme relative w-full min-h-screen selection:bg-[#E30613] selection:text-white">
            
           
            <div class="fixed inset-0 z-[0] opacity-5 pointer-events-none bg-[url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAiIGhlaWdodD0iMjAiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PGNpcmNsZSBjeD0iMSIgY3k9IjEiIHI9IjEiIGZpbGw9InJnYmEoMCwwLDAsMSkiLz48L3N2Zz4=')]"></div>

            <main class="relative z-10 pt-0">

           
                <Swiper :modules="[Navigation, Pagination, Autoplay, EffectFade]" effect="fade"
                    :fadeEffect="{ crossFade: true }" :autoplay="{ delay: 5000, disableOnInteraction: false }"
                    :pagination="{ clickable: true }" :navigation="true" :loop="true"
                    class="swiper-main h-[70vh] md:h-screen w-full bg-[#F2F0EB] border-b-8 border-black">
                    
                    <SwiperSlide v-for="(bannerUrl, index) in banners" :key="index"
                        class="relative overflow-hidden bg-[#F2F0EB]">

                      
                        <div class="absolute inset-0 w-full h-full z-0">
                            <img :src="bannerUrl"
                                class="absolute inset-0 w-full h-full object-cover grayscale-[0.2] contrast-125 opacity-90"
                                alt="F33 Banner" />
                        </div>

                      
                        <div class="absolute inset-0 bg-gradient-to-t from-[#F2F0EB] via-[#F2F0EB]/30 to-transparent z-10 pointer-events-none"></div>

                   
                        <div class="absolute inset-0 w-full h-full z-20 pointer-events-none flex flex-col justify-end pb-24 md:pb-32 px-6 md:px-12">
                            <div class="max-w-7xl mx-auto w-full relative">
                              
                                <div class="inline-block bg-white border-4 border-black p-4 shadow-[8px_8px_0px_0px_rgba(227,6,19,1)]">
                                    <h1 class="text-6xl md:text-[8rem] lg:text-[10rem] font-flux text-black uppercase tracking-tighter leading-[0.85]">
                                        F33 <span class="text-[#E30613]">.</span>
                                    </h1>
                                </div>
                            </div>
                        </div>

                    </SwiperSlide>

                  
                    <SwiperSlide v-if="!banners || banners.length === 0"
                        class="relative overflow-hidden bg-[#F2F0EB] flex items-center justify-center">
                        <div class="text-center font-mono text-black border-4 border-black bg-white p-12 shadow-[8px_8px_0px_0px_rgba(0,0,0,1)]">
                            <span class="text-[#E30613] animate-pulse text-3xl font-black">>_</span><br>
                            <span class="font-bold tracking-widest uppercase">DIR_VACÍO: /public/banners/</span>
                        </div>
                    </SwiperSlide>
                </Swiper>


              
                <section id="eventos" class="w-full pt-20 pb-0">
                    <div class="px-6 md:px-12 flex flex-col md:flex-row justify-between items-end mb-12">
                        <div>
                            <h2 class="text-5xl md:text-7xl font-black font-flux uppercase tracking-tighter text-black mb-2 flex items-center gap-4">
                                <span class="w-8 h-8 md:w-12 md:h-12 bg-[#E30613] border-4 border-black inline-block"></span>
                                Eventos
                            </h2>
                            <p class="text-gray-600 font-mono text-sm font-bold tracking-widest uppercase border-l-4 border-[#E30613] pl-3">
                                Seleccioná un evento para ver su colección de fotos
                            </p>
                        </div>
                    </div>

                    <div class="masonry-container px-4 md:px-12" v-if="recentEvents.length > 0">
                        <Link v-for="event in recentEvents" :key="event.id"
                            :href="route('events.show', event.slug || event.id)"
                            class="masonry-item relative group overflow-hidden bg-white block border-4 border-black shadow-[6px_6px_0_0_rgba(0,0,0,1)] hover:shadow-none hover:translate-x-[6px] hover:translate-y-[6px] transition-all duration-200">

                        
                            <img :src="event.cover_image_url"
                                class="w-full h-auto block filter grayscale-[0.2] contrast-125 group-hover:grayscale-0 transition-all duration-500">

                            
                            <div class="absolute inset-0 bg-white/90 backdrop-blur-sm flex flex-col justify-center items-center opacity-0 group-hover:opacity-100 transition-opacity duration-300 p-6 text-center border-[12px] border-transparent group-hover:border-black">
                                <span class="text-black bg-[#E30613] font-mono text-[10px] font-black tracking-widest uppercase mb-4 border-2 border-black px-3 py-1">
                                    {{ event.is_private ? 'Privado' : 'Público' }}
                                </span>
                                <h3 class="text-black text-4xl font-black font-flux uppercase tracking-tight mb-3">
                                    {{ event.name }}
                                </h3>
                                <p v-if="event.description"
                                    class="text-gray-800 text-sm font-medium line-clamp-2 max-w-[80%] mb-6 font-mono">
                                    {{ event.description }}
                                </p>
                                <span class="text-white bg-black font-mono font-bold text-xs uppercase tracking-widest px-6 py-3 border-2 border-black group-hover:bg-[#E30613] transition-colors">
                                    Ver Galería →
                                </span>
                            </div>
                        </Link>
                    </div>

                    
                    <div v-else class="text-center py-32 border-4 border-dashed border-black mx-6 md:mx-12 bg-white shadow-[8px_8px_0_0_rgba(0,0,0,1)]">
                        <p class="font-mono font-bold text-black uppercase tracking-widest text-xl">Aún no hay eventos registrados.</p>
                    </div>
                </section>


               
                <section v-if="recentPhotos.length > 0" class="w-full pt-32 pb-24 overflow-hidden border-t-8 border-black mt-20 bg-white">
                    <div class="px-6 md:px-12 flex flex-col md:flex-row justify-between items-end mb-12">
                        <div>
                            <h2 class="text-4xl md:text-7xl font-black font-flux uppercase tracking-tighter text-black mb-2">
                                Últimas <span class="text-[#E30613]">fotos</span>
                            </h2>
                            <p class="text-gray-600 font-mono text-sm font-bold tracking-widest uppercase">
                                Colecciones recientes de nuestros fotógrafos
                            </p>
                        </div>
                        <Link :href="route('gallery.index')"
                            class="hidden md:block text-black bg-white border-4 border-black px-6 py-3 font-mono font-bold uppercase tracking-widest shadow-[4px_4px_0_0_rgba(227,6,19,1)] hover:shadow-none hover:translate-x-[4px] hover:translate-y-[4px] transition-all">
                            Ver Archivo Completo
                        </Link>
                    </div>

                    <div class="pl-6 md:pl-12">
                        <Swiper :modules="[FreeMode, Autoplay]" :slidesPerView="1.2" :spaceBetween="24" :freeMode="true"
                            :grabCursor="true"
                            :breakpoints="{ '640': { slidesPerView: 2.2 }, '1024': { slidesPerView: 3.5 }, '1536': { slidesPerView: 4.5 } }"
                            class="w-full !overflow-visible pb-8">

                            <SwiperSlide v-for="photo in recentPhotos.slice(0, 8)" :key="photo.id">
                                <div @click="router.visit(route('gallery.show', photo.unique_id))"
                                    class="relative aspect-[3/4] bg-[#F2F0EB] group overflow-hidden border-4 border-black shadow-[6px_6px_0_0_rgba(0,0,0,1)] hover:shadow-none hover:translate-x-[6px] hover:translate-y-[6px] transition-all duration-200 block w-full h-full cursor-pointer flex flex-col">

                                    <!-- Foto -->
                                    <div class="relative flex-1 overflow-hidden bg-black border-b-4 border-black">
                                        <img :src="photo.watermarked_url || photo.thumbnail_url"
                                            class="w-full h-full object-cover filter contrast-125 group-hover:scale-105 transition-transform duration-500 pointer-events-none select-none" />
                                    </div>

                                    <!-- Etiqueta inferior estilo Polaroid/Brutalista -->
                                    <div class="bg-white p-4 flex items-center gap-3">
                                        <div class="w-10 h-10 flex-shrink-0 bg-[#F2F0EB] border-2 border-black overflow-hidden">
                                            <img v-if="getEventCoverForPhoto(photo)" :src="getEventCoverForPhoto(photo)"
                                                class="w-full h-full object-cover grayscale">
                                            <span v-else class="flex items-center justify-center w-full h-full text-[10px] text-black font-black">F33</span>
                                        </div>

                                        <div class="flex-1 min-w-0">
                                            <p class="text-black font-black text-sm uppercase tracking-tighter truncate">
                                                {{ photo.event_name || 'Operación X' }}
                                            </p>
                                            <p class="text-[#E30613] font-mono text-[10px] font-bold tracking-widest mt-0.5">
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
    font-family: 'Outfit', sans-serif;
    background-color: #F2F0EB;
    color: #050505;
}

 
.font-flux { font-family: 'Outfit', sans-serif; }
.font-mono { font-family: 'JetBrains Mono', monospace; }

 
.swiper-main {
    width: 100%;
}

.swiper-main>.swiper-pagination {
    bottom: 2rem !important;
}

.swiper-main>.swiper-pagination>.swiper-pagination-bullet {
    width: 16px;
    height: 16px;
    background-color: white;
    border: 3px solid black;
    opacity: 1;
    border-radius: 0;  
    transition: all 0.2s ease;
    box-shadow: 2px 2px 0 0 rgba(0,0,0,1);
}

.swiper-main>.swiper-pagination>.swiper-pagination-bullet-active {
    background-color: #E30613;
    border-color: black;
    width: 32px;
}

.swiper-main>.swiper-button-next,
.swiper-main>.swiper-button-prev {
    color: black !important;
    background-color: white;
    border: 4px solid black;
    width: 60px;
    height: 60px;
    opacity: 0;
    transition: all 0.2s ease;
    box-shadow: 4px 4px 0 0 rgba(0,0,0,1);
}

.swiper-main>.swiper-button-next:after,
.swiper-main>.swiper-button-prev:after {
    font-size: 24px;
    font-weight: 900;
}

.swiper-main:hover>.swiper-button-next,
.swiper-main:hover>.swiper-button-prev {
    opacity: 1;
}

.swiper-main>.swiper-button-next:hover,
.swiper-main>.swiper-button-prev:hover {
    background-color: #E30613;
    color: white !important;
    transform: translate(2px, 2px);
    box-shadow: 2px 2px 0 0 rgba(0,0,0,1);
}
</style>