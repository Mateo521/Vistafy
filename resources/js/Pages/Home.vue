<script setup>
import { Head, Link, usePage } from '@inertiajs/vue3';
import { ref, onMounted, nextTick } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';


import FutureEventsSection from '@/Components/FutureEventsSection.vue';
import FutureEventsMap from '@/Components/FutureEventsMap.vue';
import RecentPhotosSection from '@/Components/RecentPhotosSection.vue';


import { Swiper, SwiperSlide } from 'swiper/vue';
import { Navigation, Autoplay, EffectFade, Pagination, FreeMode } from 'swiper/modules';
import 'swiper/css';
import 'swiper/css/navigation';
import 'swiper/css/pagination';
import 'swiper/css/effect-fade';
import 'swiper/css/free-mode';

import AOS from 'aos';
import 'aos/dist/aos.css';

const props = defineProps({
    canLogin: Boolean,
    canRegister: Boolean,
    recentEvents: { type: Array, default: () => [] },
    futureEvents: { type: Array, default: () => [] },
    recentPhotos: { type: Array, default: () => [] },
    stats: { type: Object, default: () => ({ total_photos: 0, total_events: 0, total_photographers: 0 }) },
    videoList: { type: Array, default: () => [] }
});


const currentVideo = ref(props.videoList.length > 0 ? props.videoList[Math.floor(Math.random() * props.videoList.length)] : '/40c665d047c7afa27213c22c2c7b6308_720w.mp4');
const isGlitching = ref(false);

const onHeroSlideChange = () => {
    isGlitching.value = false;

    nextTick(() => {
        isGlitching.value = true;
        setTimeout(() => {
            isGlitching.value = false;
        }, 600);
    });
};

const formatDate = (dateString) => new Date(dateString).toLocaleDateString('es-ES', { year: 'numeric', month: '2-digit', day: '2-digit' }).replace(/\//g, '.');


const initGlitchEffect = () => {
    const glitchContainers = document.querySelectorAll('.glitch-image-container');
    const random = (min, max) => Math.random() * (max - min) + min;

    glitchContainers.forEach(container => {
        const imgUrl = container.getAttribute('data-img');
        if (!imgUrl) return;

        const height = container.clientHeight || 220;
        const width = container.clientWidth;
        let i = 0;
        let html = '';

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
                        animation-delay: ${delay};
                     ">
                </div>`;
            i += actualHeight;
        }
        container.innerHTML = html;
    });
};

onMounted(() => {
    AOS.init({
        once: true,
        offset: 50,
        duration: 800,
        easing: 'ease-out-cubic',
    });
    
    // Inicializar glitch después de que el DOM esté listo
    setTimeout(initGlitchEffect, 100);
});
</script>

<template>
    <Head title="f33.click - Captura Todo" />

    <AppLayout>
        <div class="min-h-screen overflow-x-hidden bg-black font-sans text-white selection:bg-red-600 selection:text-white">
            
            <div class="grain pointer-events-none fixed inset-0 z-50 opacity-[0.18] mix-blend-screen"></div>

            <section id="hero" class="relative min-h-screen w-full bg-black" :class="{ 'is-glitching': isGlitching }">
                
                <Swiper 
                    :modules="[Navigation, Autoplay, EffectFade, Pagination]" 
                    effect="fade" 
                    :fadeEffect="{ crossFade: true }"
                    :autoplay="{ delay: 7000, disableOnInteraction: false }"
                    :navigation="{ prevEl: '.swiper-hero-prev', nextEl: '.swiper-hero-next' }" 
                    :pagination="{ el: '.swiper-hero-pagination', clickable: true }"
                    :loop="true" 
                    @slideChangeTransitionStart="onHeroSlideChange"
                    class="swiper-hero h-screen w-full">

                    <SwiperSlide class="relative h-screen w-full overflow-hidden">
                        <div class="absolute inset-0 bg-[linear-gradient(rgba(255,255,255,.055)_1px,transparent_1px),linear-gradient(90deg,rgba(255,255,255,.055)_1px,transparent_1px)] bg-[size:42px_42px] opacity-70 z-10"></div>
                        
                        <video autoplay muted loop playsinline class="absolute inset-0 h-full w-full object-cover opacity-60 mix-blend-screen z-0">
                            <source :src="currentVideo" type="video/mp4">
                        </video>

                        <div class="absolute left-[6vw] top-[18vh] z-20 h-40 w-40 border-l-2 border-t-2 border-white"></div>
                        <div class="absolute right-[6vw] top-[18vh] z-20 h-40 w-40 border-r-2 border-t-2 border-red-600"></div>
                        <div class="absolute bottom-[10vh] left-[6vw] z-20 h-40 w-40 border-b-2 border-l-2 border-red-600"></div>
                        <div class="absolute bottom-[10vh] right-[6vw] z-20 h-40 w-40 border-b-2 border-r-2 border-white"></div>

                        <div class="absolute left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2 z-20 h-[100px] w-24 rounded-full border border-white/40">
                            <span class="absolute left-1/2 top-0 h-full w-px -translate-x-1/2 bg-white/40"></span>
                            <span class="absolute left-0 top-1/2 h-px w-full -translate-y-1/2 bg-white/40"></span>
                            <span class="absolute left-1/2 top-1/2 h-2 w-2 -translate-x-1/2 -translate-y-1/2 rounded-full bg-red-600 shadow-[0_0_24px_#ff0000]"></span>
                        </div>

                        <div class="absolute left-6 top-24 z-20 max-w-48 border border-white/20 bg-black/70 p-3 font-mono text-[10px] uppercase leading-relaxed tracking-widest text-gray-400 md:left-10">
                            <p class="text-red-600">GRABANDO 00:33:21</p>
                            <p>ISO 1600</p>
                            <p>OBTURADOR 1/500</p>
                            <p>WB 5600K</p>
                        </div>

                        <div class="absolute right-6 top-28 z-20 max-w-52 border border-white/20 bg-black/70 p-3 font-mono text-[10px] uppercase leading-relaxed tracking-widest text-gray-400 md:right-10">
                            <p>BATERÍA 91%</p>
                            <p>SEÑAL EN LÍNEA</p>
                            <p>ID REEL: f33-MAIN</p>
                            <p class="text-white">MERCADO: ACTIVO</p>
                        </div>

                        <div class="absolute inset-0 z-30 mx-auto flex max-w-[1500px] items-end px-4 pb-12 md:px-8 lg:px-12">
                            <div class="relative w-full">
                                <div class="absolute -left-4 bottom-[46%] hidden h-36 w-[54vw] rotate-[-4deg] border-y border-red-600/70 bg-red-600/10 md:block"></div>
                                <p class="mb-4 font-mono text-xs uppercase tracking-[0.45em] text-gray-500">PRODUCCIÓN AUDIOVISUAL / IDENTIDAD CORPORATIVA / CAPTURA CRUDA</p>
                                <h1 class="relative slide-title-reveal max-w-[1260px] font-black text-[20vw] leading-[0.76] tracking-tighter text-white md:text-[12rem]">
                                    CAPTURA.<br />TODO.
                                    <span class="font-brush absolute -right-2 top-[16%] rotate-[-8deg] text-[28vw] leading-none tracking-normal text-red-600 mix-blend-screen md:text-[12rem]">f33</span>
                                </h1>
                                <div class="relative -mt-2 grid gap-3 border-y border-white/15 bg-black/70 py-4 font-mono text-[10px] uppercase tracking-[0.24em] text-gray-500 md:grid-cols-4">
                                    <p class="border-l border-red-600 pl-3">Autenticidad</p>
                                    <p class="border-l border-white/20 pl-3">Velocidad</p>
                                    <p class="border-l border-white/20 pl-3">Calidad</p>
                                    <p class="border-l border-white/20 pl-3 text-white">[ {{ stats.total_photos }} Fotografías ]</p>
                                </div>
                            </div>
                        </div>
                    </SwiperSlide>

                    <SwiperSlide v-for="(event, index) in recentEvents.slice(0, 3)" :key="event.id" class="relative h-screen w-full overflow-hidden">
                        <div class="absolute inset-0 bg-black/40 z-10"></div>
                        <img :src="event.cover_image_url" :alt="event.name" class="absolute inset-0 h-full w-full object-cover grayscale contrast-125 z-0" />

                        <div class="absolute left-6 top-24 z-20 max-w-48 border border-white/20 bg-black/70 p-3 font-mono text-[10px] uppercase tracking-widest text-gray-400 md:left-10">
                            <p class="text-white">ARCHIVO // 0{{ index + 1 }}</p>
                            <p>{{ formatDate(event.event_date) }}</p>
                        </div>

                        <div class="absolute inset-0 z-30 mx-auto flex max-w-[1500px] items-end px-4 pb-12 md:px-8 lg:px-12">
                            <div class="relative w-full">
                                <h2 class="slide-title-reveal font-black text-[12vw] leading-none tracking-tighter text-white uppercase md:text-[7rem]">
                                    {{ event.name }}<br /><span class="text-red-600">{{ event.location || 'Cobertura' }}</span>
                                </h2>
                                <p class="slide-subtitle-reveal mt-4 max-w-xl font-mono text-xs uppercase tracking-widest text-gray-400">
                                    {{ event.description || 'Cobertura en pasarela con metadatos indexados listos para descarga e impresión comercial.' }}
                                </p>
                            </div>
                        </div>
                    </SwiperSlide>

                </Swiper>

                <div class="absolute bottom-12 right-6 md:right-12 z-40 flex space-x-2 font-mono text-[10px] tracking-widest">
                    <button class="swiper-hero-prev bg-black/80 border border-white/20 px-4 py-2 hover:bg-white hover:text-black transition duration-150 cursor-pointer uppercase">[ ANTERIOR ]</button>
                    <button class="swiper-hero-next bg-black/80 border border-white/20 px-4 py-2 hover:bg-white hover:text-black transition duration-150 cursor-pointer uppercase">[ SIGUIENTE ]</button>
                </div>
                <div class="swiper-hero-pagination absolute !bottom-6 !left-1/2 !-translate-x-1/2 z-40 flex justify-center gap-1 w-auto"></div>
            </section>


            <section id="flujo-de-trabajo" class="relative overflow-hidden bg-black py-28 md:py-36">
                <div class="absolute inset-0 bg-[linear-gradient(90deg,rgba(255,255,255,.04)_1px,transparent_1px),linear-gradient(rgba(255,255,255,.04)_1px,transparent_1px)] bg-[size:22px_22px]"></div>

                <div class="relative px-4 md:px-8 lg:px-12">
                    <div class="mb-12 flex mx-auto max-w-[1500px] flex-col justify-between gap-6 md:flex-row md:items-end">
                        <div>
                            <p class="font-mono text-sm uppercase text-red-600">Sistema Integral</p>
                            <h2 class="mt-2 font-black text-[12vw] leading-none tracking-tighter text-white md:text-[7rem]">
                                ENCONTRÁ. SUBÍ. COBRÁ.
                            </h2>
                        </div>
                        <p class="max-w-sm text-3xl leading-none text-white/80 md:text-5xl">Tres etapas entre la captura y la venta.</p>
                    </div>
                    
                    <div class="film-perf relative -mx-4 border-y-4 border-white py-12 text-black shadow-[0_45px_120px_rgba(255,255,255,.12)] md:-mx-8 lg:-mx-12">
                        <div class="grid gap-3 px-4 md:grid-cols-3 md:px-8 lg:px-12">
                            <article class="group relative min-h-[700px] overflow-hidden border-[12px] border-black bg-black text-white">
                                <img src="https://images.unsplash.com/photo-1501386761578-eac5c94b800a?auto=format&fit=crop&w=900&q=85" class="absolute inset-0 h-full w-full object-cover opacity-70 transition duration-500" />
                                <div class="absolute inset-0 bg-gradient-to-t from-black via-black/30 to-transparent"></div>
                                <div class="absolute left-4 top-4 flex h-14 w-14 items-center justify-center border-2 border-white bg-black font-mono text-xl font-bold">01</div>
                                <div class="absolute bottom-6 left-6 right-6">
                                    <p class="font-mono text-sm uppercase text-red-600">Ubica la demanda</p>
                                    <h3 class="mt-2 font-black text-6xl leading-none tracking-tighter">Encontrá</h3>
                                    <p class="mt-4 font-mono text-sm uppercase leading-relaxed tracking-widest text-gray-300">
                                        Eventos, activaciones de marca y archivos en vivo que los compradores no pueden buscar en ningún otro lugar.
                                    </p>
                                </div>
                            </article>
                            <article class="group relative min-h-[700px] overflow-hidden border-[12px] border-black bg-black text-white">
                                <img src="https://images.unsplash.com/photo-1519389950473-47ba0277781c?auto=format&fit=crop&w=900&q=85" class="absolute inset-0 h-full w-full object-cover opacity-65 transition duration-500" />
                                <div class="absolute inset-0 scanlines bg-black/20"></div>
                                <div class="absolute left-4 top-4 flex h-14 w-14 items-center justify-center border-2 border-red-600 bg-black font-mono text-xl font-bold text-red-600">02</div>
                                <div class="absolute right-5 top-6 h-28 w-28 rounded-full border-2 border-white/80"></div>
                                <div class="absolute bottom-6 left-6 right-6">
                                    <p class="font-mono text-sm uppercase text-red-600">Sincronización de metadatos</p>
                                    <h3 class="mt-2 font-black text-6xl leading-none tracking-tighter">Subí el material</h3>
                                    <p class="mt-4 font-mono text-sm uppercase leading-relaxed tracking-widest text-gray-300">
                                        Descarga volúmenes altos con códigos de evento, marcas de tiempo y controles de descubrimiento seguro.
                                    </p>
                                </div>
                            </article>
                            <article class="group relative min-h-[700px] overflow-hidden border-[12px] border-black bg-black text-white">
                                <img src="https://images.unsplash.com/photo-1556742049-0cfed4f6a45d?auto=format&fit=crop&w=900&q=85" class="absolute inset-0 h-full w-full object-cover opacity-55 transition duration-500" />
                                <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_center,rgba(255,0,0,.45),rgba(0,0,0,.88)_62%)]"></div>
                                <div class="absolute left-4 top-4 flex h-14 w-14 items-center justify-center border-2 border-white bg-black font-mono text-xl font-bold">03</div>
                                <div class="absolute left-1/2 top-1/2 flex h-44 w-72 -translate-x-1/2 -translate-y-1/2 rotate-[-6deg] items-center justify-center rounded-[50%] border-4 border-red-600 bg-black/50 shadow-[0_0_55px_rgba(255,0,0,.45)]">
                                    <span class="font-black text-5xl tracking-tighter text-white">$$$</span>
                                </div>
                                <div class="absolute bottom-6 left-6 right-6">
                                    <p class="font-mono text-sm uppercase text-red-600">Liquidación directa</p>
                                    <h3 class="mt-2 font-black text-6xl leading-none tracking-tighter">Recibí tu pago</h3>
                                </div>
                            </article>
                        </div>
                    </div>
                </div>
            </section>

            <section id="ultimos-eventos" class="relative border-b border-white/10 bg-black py-16 z-30">
                <div class="mx-auto max-w-[1500px] px-4 md:px-8 lg:px-12 mb-12" data-aos="fade-up">
                    <h2 class="mt-4 font-black text-4xl md:text-6xl tracking-tighter text-white">ÚLTIMOS EVENTOS</h2>
                </div>

                <div class="w-full overflow-hidden relative" data-aos="fade-in" data-aos-delay="200">
                    <div class="absolute top-0 bottom-0 left-0 w-32 bg-gradient-to-r from-black to-transparent z-10 pointer-events-none"></div>
                    <div class="absolute top-0 bottom-0 right-0 w-32 bg-gradient-to-l from-black to-transparent z-10 pointer-events-none"></div>

                    <Swiper 
                        :modules="[Autoplay, FreeMode]"
                        :slidesPerView="'auto'"
                        :spaceBetween="0"
                        :loop="true"
                        :freeMode="{ enabled: true, momentum: false }"
                        :speed="6000"
                        :autoplay="{ delay: 0, disableOnInteraction: false, pauseOnMouseEnter: true }"
                        class="swiper-marquee">
                        
                        <SwiperSlide v-for="event in recentEvents" :key="event.id" class="px-0 flex items-center justify-center">
                            <Link :href="route('events.show', event.slug)">
                                <img :src="event.cover_image_url" :alt="event.name"
                                    class="h-[700px] w-auto object-contain opacity-50 hover:opacity-100 transition duration-300" />
                            </Link>
                        </SwiperSlide>
                    </Swiper>
                </div>
            </section>

            <FutureEventsSection :events="futureEvents" />

            <section id="mapa-cobertura" class="relative overflow-hidden bg-black pt-28 border-b border-white/10">
                <div class="mx-auto max-w-[1500px] px-4 md:px-8 lg:px-12 mb-12" data-aos="fade-up">
                    <h2 class="mt-4 font-black text-4xl md:text-6xl tracking-tighter text-white">NUESTRA COBERTURA</h2>
                </div>
                <div data-aos="fade-in" data-aos-delay="200" class="relative w-full border border-white/15 p-2 bg-gray-950">
                    <FutureEventsMap :events="futureEvents" />
                </div>
            </section>

            <section id="ultimas-incorporaciones" class="relative overflow-hidden bg-black py-28">
                <div class="absolute inset-0 bg-[linear-gradient(rgba(255,255,255,.06)_1px,transparent_1px),linear-gradient(90deg,rgba(255,255,255,.06)_1px,transparent_1px)] bg-[size:18px_18px] opacity-80"></div>
                
                <div class="relative mx-auto max-w-[1500px] px-4 md:px-8 lg:px-12">
                    <div class="relative mb-12 py-16 md:py-24 group cursor-crosshair">
                        <div class="glitch-image-container rotate-180 absolute z-10 inset-0 w-full h-full overflow-hidden -z-10 opacity-60"
                            :data-img="recentPhotos[0]?.watermarked_url || '/0fcce5d4573ebd79df2e147d7f87af35.jpg'">
                        </div>
                        <h2 class="font-black text-[10vw] z-10 relative leading-none tracking-tighter text-white md:text-[8rem] xl:text-[9rem] pointer-events-none">
                            ÚLTIMAS<br>INCORPORACIONES.
                        </h2>
                        <p class="absolute -bottom-5 right-[5%] rotate-[-5deg] text-3xl text-red-600 md:text-5xl z-20 pointer-events-none shadow-[8px_8px_0_rgba(255,255,255,0.1)] bg-black border-2 border-red-600 px-4 py-2 uppercase tracking-widest font-bold">
                            Catálogo f33
                        </p>
                    </div>

                    <RecentPhotosSection :photos="recentPhotos" />
                </div>
            </section>

            <footer class="relative bg-black border-t-[12px] border-white text-white font-mono overflow-hidden">
                <div class="ticker-container w-full overflow-hidden border-b border-white bg-red-600 text-black py-3 flex whitespace-nowrap cursor-crosshair">
                    <div class="animate-ticker flex space-x-8 font-black text-2xl uppercase tracking-tighter">
                        <span>f33(•).CLICK <span class="font-sans font-light mx-2">&lt;-&gt;</span> IDENTIDAD DE EVENTOS</span>
                        <span>CRECIMIENTO ORGÁNICO</span>
                        <span>f33(•).CLICK <span class="font-sans font-light mx-2">&lt;-&gt;</span> IDENTIDAD DE EVENTOS</span>
                        <span>CRECIMIENTO ORGÁNICO</span>
                        <span>f33(•).CLICK <span class="font-sans font-light mx-2">&lt;-&gt;</span> IDENTIDAD DE EVENTOS</span>
                        <span>CRECIMIENTO ORGÁNICO</span>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 w-full border-b-[12px] border-white">
                    <div class="group border-b lg:border-b-0 lg:border-r border-white p-8 md:p-12 flex flex-col justify-between h-[320px] hover:bg-white hover:text-black transition-none cursor-pointer">
                        <div>
                            <div class="h-4 w-4 bg-red-600 rounded-full mb-4 group-hover:animate-ping"></div>
                            <p class="text-xs uppercase tracking-widest text-gray-500 group-hover:text-black">Estado de red</p>
                        </div>
                        <div>
                            <h3 class="font-black text-6xl leading-none tracking-tighter">EN<br>LÍNEA</h3>
                            <p class="mt-4 text-xs font-bold uppercase group-hover:text-red-600">NODO: SAN LUIS / BUE / BOG</p>
                        </div>
                    </div>

                    <div class="group border-b lg:border-b-0 lg:border-r border-white p-8 md:p-12 flex flex-col justify-center h-[320px] hover:bg-white hover:text-black transition-none cursor-pointer">
                        <nav class="flex flex-col space-y-4 font-black text-3xl uppercase tracking-tighter">
                            <a href="#hero" class="hover:text-red-600 hover:translate-x-2 transition-transform">PROTOCOLO f33</a>
                            <a href="#flujo-de-trabajo" class="hover:text-red-600 hover:translate-x-2 transition-transform">WORKFLOW</a>
                            <a href="#ultimas-incorporaciones" class="hover:text-red-600 hover:translate-x-2 transition-transform">ARCHIVO VIVO</a>
                        </nav>
                    </div>

                    <div class="group border-b md:border-b-0 md:border-r border-white flex items-center justify-center h-[320px] hover:bg-white transition-none overflow-hidden">
                        <svg viewBox="0 0 100 100" class="w-48 h-48 fill-white group-hover:fill-black group-hover:scale-150 group-hover:rotate-180 transition-all duration-300 ease-in-out">
                            <path fill-rule="evenodd" clip-rule="evenodd" d="M50 0C22.3858 0 0 22.3858 0 50C0 77.6142 22.3858 100 50 100C77.6142 100 100 77.6142 100 50C100 22.3858 77.6142 0 50 0ZM50 15C30.67 15 15 30.67 15 50C15 58.7495 18.2127 66.7483 23.5183 72.8837C28.2721 66.0718 35.8456 59.9324 45 57.1402L50 45L55 57.1402C64.1544 59.9324 71.7279 66.0718 76.4817 72.8837C81.7873 66.7483 85 58.7495 85 50C85 30.67 69.33 15 50 15Z" />
                        </svg>
                    </div>

                    <div class="group p-8 md:p-12 flex flex-col justify-between h-[320px] hover:bg-white hover:text-black transition-none cursor-pointer">
                        <div class="text-right">
                            <p class="text-[10px] uppercase tracking-[0.2em] text-red-600 font-bold mb-2">VERSIÓN: 0.2_BETA</p>
                            <p class="text-xs uppercase tracking-widest group-hover:text-black">DERECHOS GESTIONADOS</p>
                        </div>
                        <div class="text-right">
                            <h4 class="font-black text-2xl uppercase tracking-tighter">CONTACTO</h4>
                            <a href="mailto:info@f33.click" class="block mt-1 text-sm border-b border-white group-hover:border-black pb-1 inline-block">info@f33.click</a>
                        </div>
                    </div>
                </div>

                <div class="relative w-full h-[400px] flex items-center justify-center overflow-hidden group hover:bg-white transition-none cursor-crosshair">
                    <h2 class="font-black text-[25vw] leading-[0.7] tracking-tighter text-white group-hover:text-black transition-none z-10">
                        f33
                    </h2>
                    <h2 class="absolute font-sans font-black text-[12vw] leading-none tracking-tighter text-red-600 uppercase mix-blend-difference opacity-0 group-hover:opacity-100 transition-none z-20 pointer-events-none whitespace-nowrap">
                        CAPTURA TODO
                    </h2>
                </div>
            </footer>
        </div>
    </AppLayout>
</template>

<style scoped>
/* Fuentes */
@import url('https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;1,300;1,400&family=Syne:wght@400;500;700;800&family=Rubik+Dirt&family=Space+Mono:ital,wght@0,400;0,700;1,400;1,700&display=swap');

.font-serif { font-family: 'Cormorant Garamond', serif; }
.font-sans  { font-family: 'Inter', 'Syne', sans-serif; }
.font-mono  { font-family: 'Space Mono', monospace; }
.font-brush { font-family: 'Rubik Dirt', cursive; }


.film-perf {
    background-image: radial-gradient(circle, transparent 0 9px, #fff 10px 15px, transparent 16px), radial-gradient(circle, transparent 0 9px, #fff 10px 15px, transparent 16px);
    background-position: 16px 10px, 16px calc(100% - 10px);
    background-size: 64px 36px;
    background-repeat: repeat-x;
}

.scanlines {
    background-image: linear-gradient(to bottom, rgba(255, 255, 255, .08) 1px, transparent 1px);
    background-size: 100% 8px;
}


:deep(.swiper-marquee .swiper-wrapper) {
    transition-timing-function: linear !important;
}
:deep(.swiper-marquee .swiper-slide) {
    width: auto;
}


@keyframes ticker {
    0% { transform: translateX(0); }
    100% { transform: translateX(-50%); }
}
.animate-ticker {
    animation: ticker 15s linear infinite;
}
.ticker-container:hover .animate-ticker {
    animation-play-state: paused;
}


.slide-title-reveal, .slide-subtitle-reveal {
    opacity: 0;
    transform: translateY(40px);
    transition: all 3s cubic-bezier(0.16, 1, 0.3, 1);
}
.slide-subtitle-reveal { transform: translateY(20px); }
:deep(.swiper-slide-active .slide-title-reveal) {
    opacity: 1;
    transform: translateY(0);
    transition-delay: 0.3s;
}
:deep(.swiper-slide-active .slide-subtitle-reveal) {
    opacity: 1;
    transform: translateY(0);
    transition-delay: 0.5s;
}


:deep(.swiper-hero-pagination .swiper-pagination-bullet-active) {
    background-color: #dc2626 !important;
    width: 48px !important;
}
:deep(.swiper-hero-pagination .swiper-pagination-bullet) {
    width: 32px;
    height: 4px;
    border-radius: 0;
    background-color: rgba(255,255,255,0.3);
    opacity: 1;
    transition: all 0.3s ease;
}


.swiper-hero.is-glitching :deep(.swiper-slide-active) {
    animation: hero-slice-fade 0.2s cubic-bezier(0.25, 0.46, 0.45, 0.94) both;
}
@keyframes hero-slice-fade {
    0%   { clip-path: inset(20% 0 80% 0); opacity: 0; }
    15%  { clip-path: inset(60% 0 10% 0); opacity: 0.3; }
    30%  { clip-path: inset(10% 0 50% 0); opacity: 0.8; }
    45%  { clip-path: inset(80% 0 5% 0); opacity: 0.2; }
    60%  { clip-path: inset(30% 0 20% 0); opacity: 0.9; }
    75%  { clip-path: inset(5% 0 70% 0); opacity: 0.5; }
    90%  { clip-path: inset(50% 0 10% 0); opacity: 0.8; }
    100% { clip-path: inset(0 0 0 0); opacity: 1; }
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