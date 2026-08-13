<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';

import { Swiper, SwiperSlide } from 'swiper/vue';
import { Autoplay, EffectFade } from 'swiper/modules';
import 'swiper/css';
import 'swiper/css/effect-fade';

const props = defineProps({
    recentEvents: { type: Array, default: () => [] },
    recentPhotos: { type: Array, default: () => [] },
    videoList: { type: Array, default: () => [] },
    banners: { type: Array, default: () => [] },
});
</script>

<template>

    <Head>
        <title>f33.click | Fotografía deportiva</title>

        <link rel="preconnect" href="https://fonts.googleapis.com">
            <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
                <link
                    href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Lato:wght@300;400;700;900&display=swap"
                    rel="stylesheet">
    </Head>

    <AppLayout>
        <div class="f33-sport-theme w-full min-h-screen selection:bg-red-600 selection:text-white">

            <main class="relative z-10 pt-4 md:pt-8">


                <section class="pb-16 px-4 md:px-8 max-w-[90rem] mx-auto">
                    <div
                        class="relative w-full h-[75vh] rounded-3xl overflow-hidden shadow-2xl shadow-red-900/10 flex items-end bg-white">


                        <div class="absolute inset-0 w-full h-full z-0">
                            <Swiper :modules="[Autoplay, EffectFade]" effect="fade" :fadeEffect="{ crossFade: true }"
                                :autoplay="{ delay: 5000, disableOnInteraction: false }" :loop="true"
                                class="w-full h-full">
                                <SwiperSlide v-for="(bannerUrl, index) in banners" :key="index">
                                    <img :src="bannerUrl" class="w-full h-full object-cover" alt="Banner f33" />
                                </SwiperSlide>
                                <SwiperSlide v-if="!banners || banners.length === 0">
                                    <img src="https://images.unsplash.com/photo-1541534741688-6078c6bfb5c5?auto=format&fit=crop&w=2000&q=80"
                                        class="w-full h-full object-cover" alt="Fallback Banner" />
                                </SwiperSlide>
                            </Swiper>
                        </div>


                        <!--div
                            class="absolute inset-0 bg-gradient-to-b from-black via-black/90 to-transparent md:w-3/4 z-10 pointer-events-none">
                        </div-->


                        <div class="relative z-20 p-8 md:p-16 max-w-2xl pointer-events-auto">
                            <!--span class="font-bold tracking-widest uppercase text-red-600 mb-4 block text-sm">
                                Fotografía deportiva
                            </span-->
                            <!--h1 class="font-flux text-2xl md:text-7xl lg:text-[6rem]  text-black mb-6">
                                La fotografía cambió <br>
                                <span class="text-gradient">la forma de venderla no.</span>
                            </h1-->
                            <!--p class="font-lato text-lg md:text-xl text-slate-700 leading-relaxed mb-10">
                                Inmortalizamos el movimiento, la pasión y la adrenalina. Explorá nuestros eventos y
                                reviví la acción con una calidad visual incomparable.
                            </p-->
                            <div class="flex py-3 gap-4">
                                <a href="/nosotros"
                                    class="bg-gradient-to-r from-red-600 to-black text-white font-bold uppercase tracking-wider px-8 py-4 rounded hover:shadow-lg hover:shadow-red-500/30 transition-all duration-300">
                                    Sobre nosotros
                                </a>
                            </div>
                        </div>
                    </div>
                </section>


            
                <section id="eventos" class="py-20 px-4 md:px-8 max-w-[90rem] mx-auto">
                    
                    <div class="text-center mb-16">
                        <span class="font-bold tracking-widest text-red-600 uppercase text-sm">Todos los eventos y coberturas</span>
                        <h2 class="font-flux text-5xl md:text-7xl text-black mt-2">
                            Eventos <span class="text-slate-300 font-sans font-light">/</span> Destacados
                        </h2>
                    </div>

                
                    <div v-if="recentEvents.length > 0" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                        <Link v-for="event in recentEvents.slice(0, 8)" :key="event.id"
                            :href="route('events.show', event.slug || event.id)"
                            class="group relative block w-full aspect-[4/5] overflow-hidden rounded shadow-[0_8px_30px_rgb(0,0,0,0.04)] hover:shadow-[0_8px_40px_rgb(230,0,0,0.15)] transition-all duration-500 bg-black">

                        
                            <img :src="event.cover_image_url" :alt="event.name"
                                class="absolute inset-0 w-full h-full object-cover transition-transform duration-700 ease-out  opacity-100 group-hover:opacity-40">

                        
                            <div class="absolute inset-0 flex flex-col justify-center items-center p-6 text-center opacity-0 group-hover:opacity-100 transition-opacity duration-500">
                                
                            
                                <span class="bg-red-600 text-white font-bold px-4 py-1.5 rounded-full text-[10px] uppercase tracking-widest mb-4 transform translate-y-4 group-hover:translate-y-0 transition-transform duration-500 delay-75">
                                    {{ event.is_private ? 'Privado' : 'Público' }}
                                </span>

                            
                                <h3 class="font-flux text-4xl lg:text-5xl text-white mb-4 transform translate-y-4 group-hover:translate-y-0 transition-transform duration-500 delay-100 drop-shadow-lg">
                                    {{ event.name }}
                                </h3>

                            
                                <span class="inline-flex items-center gap-2 text-white font-bold uppercase text-xs tracking-widest border-b-2 border-red-600 pb-1 mt-2 transform translate-y-4 group-hover:translate-y-0 transition-transform duration-500 delay-150 hover:text-red-500">
                                    Ir a la galería
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path>
                                    </svg>
                                </span>
                            </div>
                        </Link>
                    </div>

                    
                    <div v-else class="text-center py-20 bg-white rounded-3xl shadow-sm border border-slate-100">
                        <p class="font-lato font-bold text-slate-400 uppercase tracking-widest">Aún no hay eventos registrados.</p>
                    </div>

                    
                    <div v-if="recentEvents.length > 8" class="mt-16 text-center">
                        <Link :href="route('events.index')" class="inline-flex items-center gap-2 text-black font-bold uppercase text-sm hover:text-red-600 transition-colors bg-white px-8 py-4 rounded-full shadow-sm border border-slate-200 hover:shadow-md">
                            Explorar eventos
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path></svg>
                        </Link>
                    </div>
                </section>




                <section v-if="recentPhotos.length > 0" id="galeria"
                    class="py-20 px-4 md:px-8 bg-white border-t border-slate-100">
                    <div class="max-w-7xl mx-auto">
                        <div class="text-center mb-16">
                            <span class="font-bold tracking-widest text-red-600 uppercase text-sm">Portafolio</span>
                            <h2 class="font-flux text-5xl md:text-7xl text-black mt-2">Últimas fotos</h2>
                            <p class="text-slate-500 mt-4 max-w-2xl mx-auto font-lato">Nuestra selección más reciente.</p>
                        </div>

                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                            <Link v-for="photo in recentPhotos.slice(0, 8)" :key="photo.id"
                                :href="route('gallery.show', photo.unique_id)"
                                class="group relative rounded overflow-hidden aspect-[4/5] cursor-pointer shadow-sm hover:shadow-xl transition-all duration-300">

                                <img :src="photo.watermarked_url || photo.thumbnail_url" :alt="photo.unique_id"
                                    class="w-full h-full object-cover transition-transform duration-700  pointer-events-none select-none">

                                <div
                                    class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-end p-6">
                                    <span class="text-white font-bold tracking-wider uppercase text-sm line-clamp-1">
                                        {{ photo.event_name || photo.unique_id }}
                                    </span>
                                </div>
                            </Link>
                        </div>

                        <div class="mt-16 text-center">
                            <Link :href="route('gallery.index')"
                                class="inline-block px-10 py-4 rounded-full border-2 border-black text-black font-bold uppercase tracking-widest hover:bg-black hover:text-white transition-colors duration-300">
                                Ver catálogo completo
                            </Link>
                        </div>
                    </div>
                </section>

            </main>
        </div>
    </AppLayout>
</template>

<style>


.marquee-swiper .swiper-wrapper {
    transition-timing-function: linear !important;
}


.marquee-swiper .swiper-slide {
    transition: filter 0.3s ease;
}

.f33-sport-theme {
    background-color: #F8F9FA;
    color: #1e293b;
}


.text-gradient {
    background: linear-gradient(135deg, #E60000 0%, #000000 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}
</style>