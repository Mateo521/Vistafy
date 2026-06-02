<script setup>
import { ref, onMounted } from 'vue';
import { Link } from '@inertiajs/vue3';
import axios from 'axios';
import { Swiper, SwiperSlide } from 'swiper/vue';
import { Autoplay, FreeMode } from 'swiper/modules';
import 'swiper/css';
import 'swiper/css/free-mode';

const props = defineProps({
    isAuthenticated: Boolean,
    userRole: String,
});

const futureEvents = ref([]);
const loading = ref(true);

onMounted(async () => {
    try {

        const response = await axios.get('/eventos-futuros/api?page=1');
        if (response.data && response.data.future_events) {
            futureEvents.value = response.data.future_events;
        }
    } catch (error) {
        console.error('Error cargando eventos futuros:', error);
    } finally {
        loading.value = false;
    }
});

const handleImageError = (e) => {
    e.target.src = 'https://images.unsplash.com/photo-1511795409834-ef04bbd61622?auto=format&fit=crop&w=1100&q=85';  
};
</script>

<template>
    <section id="eventos-futuros" class="relative border-b border-white/10 bg-black py-28 z-30">
        <div class="mx-auto max-w-[1500px] px-4 md:px-8 lg:px-12 mb-12" data-aos="fade-up">
            <p class="font-mono text-sm uppercase text-gray-500 pl-3  border-red-600">
                Próximas coberturas
            </p>
            <h2 class="mt-4 font-black text-4xl md:text-6xl tracking-tighter text-white">EVENTOS FUTUROS</h2>
        </div>

        <div v-if="loading" class="w-full flex justify-center py-20">
            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-red-600"></div>
        </div>

        <div v-else-if="futureEvents.length > 0" class="w-full overflow-hidden relative" data-aos="fade-in" data-aos-delay="200">
            <div class="absolute top-0 bottom-0 left-0 w-32 bg-gradient-to-r from-black to-transparent z-10 pointer-events-none"></div>
            <div class="absolute top-0 bottom-0 right-0 w-32 bg-gradient-to-l from-black to-transparent z-10 pointer-events-none"></div>

            <Swiper 
                :modules="[Autoplay, FreeMode]"
                :slidesPerView="'auto'"
                :spaceBetween="0"
                :loop="true"
                :freeMode="{ enabled: true, momentum: false }"
                :speed="6000"
                :autoplay="{ delay: 0, disableOnInteraction: false, pauseOnMouseEnter: true, reverseDirection: true }"
                class="swiper-marquee swiper-futuros">
                
                <SwiperSlide v-for="(event, index) in futureEvents" :key="`future-${event.id}-${index}`" class="px-0 flex items-center justify-center group relative cursor-pointer">
                    <Link :href="route('events.show', event.slug || event.id)" class="block relative w-full h-[700px]">
                        
                        <img :src="event.cover_image || event.cover_image_url" :alt="event.title || event.name"
                            class="h-full w-auto object-contain opacity-50 hover:opacity-100 transition duration-300 filter grayscale group-hover:grayscale-0"
                            @error="handleImageError" />
                        
                        <div class="absolute inset-0 bg-black/60 opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex flex-col items-center justify-center p-6 text-center z-20">
                            <span class="inline-flex items-center px-3 py-1.5 text-[10px] uppercase tracking-widest font-bold text-red-600 bg-black/80 border border-red-600/30 mb-4">
                                {{ event.days_until ? `En ${event.days_until} días` : 'Próximamente' }}
                            </span>
                            <h3 class="font-black text-4xl text-white uppercase tracking-tighter mb-2">
                                {{ event.title || event.name }}
                            </h3>
                            <p class="font-mono text-[10px] uppercase tracking-widest text-gray-400 border-t border-white/20 pt-4 mt-2">
                                {{ event.location || 'Ubicación a confirmar' }}
                            </p>
                        </div>
                    </Link>
                </SwiperSlide>
            </Swiper>
        </div>

        <div v-else class="text-center py-20 px-4">
            <div class="inline-flex h-20 w-20 items-center justify-center border border-white/20 bg-black/50 mb-6">
                <svg class="h-8 w-8 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
            </div>
            <p class="font-mono text-sm uppercase tracking-widest text-gray-500">No hay coberturas programadas</p>
        </div>
    </section>
</template>

<style scoped>

:deep(.swiper-marquee .swiper-wrapper) {
    transition-timing-function: linear !important;
}
:deep(.swiper-marquee .swiper-slide) {
    width: auto;
    flex-shrink: 0;
}
</style>