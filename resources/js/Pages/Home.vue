<script setup>
import { Head, Link, usePage } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';
import RecentPhotosSection from '@/Components/RecentPhotosSection.vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import FutureEventsSection from '@/Components/FutureEventsSection.vue';
import FutureEventsMap from '@/Components/FutureEventsMap.vue';
import { Swiper, SwiperSlide } from 'swiper/vue';
import { Navigation, Autoplay, EffectFade } from 'swiper/modules';
import 'swiper/css';
import 'swiper/css/navigation';
import 'swiper/css/effect-fade';  

import { 
    ArrowLongRightIcon, 
    ArrowLeftIcon, 
    ArrowRightIcon, 
    UserIcon, 
    MapPinIcon, 
    CalendarIcon 
} from '@heroicons/vue/24/outline';


import { computed } from 'vue';

const prevButton = ref(null);
const nextButton = ref(null);



const props = defineProps({
    canLogin: Boolean,
    canRegister: Boolean,
    recentEvents: { type: Array, default: () => [] },
    futureEvents: {         
        type: Array,
        default: () => []
    },
    recentPhotos: { type: Array, default: () => [] },
    stats: { type: Object, default: () => ({ total_photos: 0, total_events: 0, total_photographers: 0 }) },
    videoList: { type: Array, default: () => [] }
});


const heroPrevBtn = ref(null);
const heroNextBtn = ref(null);
const currentHeroSlide = ref(1);

 
const heroEvents = computed(() => props.recentEvents ? props.recentEvents.slice(0, 4) : []);
const totalHeroSlides = computed(() => heroEvents.value.length + 1);

 
const onHeroSlideChange = (swiper) => {
    currentHeroSlide.value = swiper.realIndex + 1;
};


const formatSlideNum = (num) => String(num).padStart(2, '0');



const animatedPhotos = ref(0);
const animatedEvents = ref(0);
const animatedPhotographers = ref(0);

const animateCount = (target, refVar, duration = 2000) => {
    const increment = target / (duration / 16);
    const timer = setInterval(() => {
        refVar.value += increment;
        if (refVar.value >= target) {
            refVar.value = target;
            clearInterval(timer);
        }
    }, 16);
};

const videos = ref(props.videoList || []);


const currentVideo = ref(null);
const promoVideo = ref(null);

function randomVideo() {
    return videos.value[Math.floor(Math.random() * videos.value.length)];
}


onMounted(() => {
    
    animateCount(props.stats.total_events || 0, animatedEvents);
    animateCount(props.stats.total_photos || 0, animatedPhotos);
    animateCount(props.stats.total_photographers || 0, animatedPhotographers);

    
    if (videos.value.length === 0) return;
    currentVideo.value = randomVideo();
    const videoEl = promoVideo.value;
    if (!videoEl) return;
    videoEl.addEventListener("ended", () => {
        currentVideo.value = randomVideo();
        videoEl.load();
        videoEl.play();
    });
});


const formatDate = (dateString) => {
    if (!dateString) return '';
    return new Date(dateString).toLocaleDateString('es-ES', { year: 'numeric', month: 'short', day: 'numeric' });
};

const handleImageError = (e) => {
    e.target.style.display = 'none';
    const parent = e.target.parentElement;
    if (!parent.querySelector('.placeholder-institutional')) {
        const placeholder = document.createElement('div');
        placeholder.className = 'placeholder-institutional w-full h-full flex items-center justify-center bg-gray-100';
        placeholder.innerHTML = `<svg class="w-12 h-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>`;
        parent.appendChild(placeholder);
    }
};


const page = usePage();
const auth = page.props.auth;


</script>

<template>
    <Head title="Inicio - Institucional" />

    <AppLayout>

        <div class="relative h-screen min-h-[700px] w-full bg-[#1B2632] overflow-hidden font-['Syne']">
    
    <Swiper
        :modules="[Navigation, Autoplay, EffectFade]"
        effect="fade"
        :fadeEffect="{ crossFade: true }"
        :autoplay="{ delay: 6000, disableOnInteraction: false }"
        :navigation="{ prevEl: heroPrevBtn, nextEl: heroNextBtn }"
        @slideChange="onHeroSlideChange"
        :loop="true"
        class="h-full w-full"
    >
        <SwiperSlide>
            <div class="w-full h-full relative">
                <video autoplay muted loop playsinline class="absolute inset-0 w-full h-full object-cover z-0" ref="promoVideo">
                    <source :src="currentVideo" type="video/mp4">
                </video>
                
                <div class="absolute inset-0 bg-gradient-to-t from-[#111920] via-[#111920]/40 to-transparent z-10"></div>
                
                <div class="relative z-20 h-full flex flex-col justify-end pb-[12%] pl-24 pr-8 md:pl-32 max-w-7xl mx-auto hero-content-container">
                    <span class="text-[10px] font-bold tracking-[0.3em] text-[#FFB162] uppercase mb-4 slide-anim">
                        Fotografía Profesional
                    </span>
                    
                    <h1 class="font-['Cormorant_Garamond'] text-6xl md:text-8xl lg:text-[110px] font-light text-[#EEE9DF] mb-8 leading-[0.9] slide-anim delay-100">
                        Preservando <br /> <em class="italic text-[#C9C1B1]">momentos</em>
                    </h1>
                    
                    <div class="flex flex-wrap items-center gap-6 slide-anim delay-200">
                        <Link :href="route('events.index')" class="bg-white text-[#1B2632] px-8 py-3.5 text-[11px] font-bold uppercase tracking-widest hover:bg-[#FFB162] hover:text-[#1B2632] transition-colors rounded-full flex items-center gap-3 shadow-lg group">
                            Explorar Archivo <ArrowLongRightIcon class="w-4 h-4 group-hover:translate-x-1 transition-transform"/>
                        </Link>
                        
                        <div class="flex items-center gap-3 border border-[#C9C1B1]/30 rounded-full px-6 py-3.5 backdrop-blur-sm text-white">
                            <span class="text-[12px] font-bold text-[#FFB162]">f33</span>
                            <span class="text-[11px] text-[#C9C1B1] tracking-wide border-l border-[#C9C1B1]/30 pl-3">Agencia de fotografía oficial</span>
                        </div>
                    </div>
                </div>
            </div>
        </SwiperSlide>

        <SwiperSlide v-for="event in heroEvents" :key="event.id">
            <div class="w-full h-full relative">
                <img v-if="event.cover_image_url" :src="event.cover_image_url" :alt="event.name" class="absolute inset-0 w-full h-full object-cover z-0 hero-img-anim saturate-[0.8]" />
                <div class="absolute inset-0 bg-gradient-to-t from-[#1B2632] via-[#1B2632]/40 to-transparent z-10"></div>
                
                <div class="relative z-20 h-full flex flex-col justify-end pb-[12%] pl-24 pr-8 md:pl-32 max-w-7xl mx-auto hero-content-container">
                    
                    <span class="text-[10px] font-bold tracking-[0.3em] text-[#FFB162] uppercase mb-4 slide-anim flex items-center gap-2">
                        Última Cobertura
                    </span>
                    
                    <h1 class="font-['Cormorant_Garamond'] text-6xl md:text-8xl lg:text-[110px] font-light text-[#EEE9DF] mb-6 leading-[0.9] slide-anim delay-100 line-clamp-2">
                        {{ event.name }}
                    </h1>

                    <div v-if="event.photographer" class="flex items-center gap-4 mb-8 slide-anim delay-200">
                        <div class="w-12 h-12 md:w-14 md:h-14 rounded-full overflow-hidden bg-[#2C3B4D] border border-white/20 flex-shrink-0 flex items-center justify-center shadow-lg">
                            <img v-if="event.photographer.avatar" :src="event.photographer.avatar" class="w-full h-full object-cover" />
                            <UserIcon v-else class="w-6 h-6 text-white/50" />
                        </div>
                        
                        <div class="flex flex-col justify-center">
                            <span class="text-[#EEE9DF] text-[13px] md:text-[15px] font-bold tracking-wide">
                                {{ event.photographer.business_name || event.photographer.name }}
                            </span>
                            <span class="text-[#C9C1B1] text-[11px] md:text-[12px] tracking-wider flex items-center gap-1.5 mt-1">
                                <MapPinIcon class="w-3.5 h-3.5 text-[#FFB162]" />
                                {{ event.location || 'Ubicación no especificada' }}
                            </span>
                        </div>
                    </div>

                    <div class="flex flex-wrap items-center gap-4 slide-anim delay-[300ms]">
                        <Link :href="route('events.show', event.slug)" class="bg-white text-[#1B2632] px-8 py-3.5 text-[11px] font-bold uppercase tracking-widest hover:bg-[#FFB162] hover:text-[#1B2632] transition-colors rounded-full flex items-center gap-3 shadow-lg group">
                            Ver Galería <ArrowLongRightIcon class="w-4 h-4 group-hover:translate-x-1 transition-transform"/>
                        </Link>
                        
                        <div class="flex items-center gap-2 border border-[#C9C1B1]/30 rounded-full px-6 py-3.5 backdrop-blur-sm text-white">
                            <CalendarIcon class="w-4 h-4 text-[#FFB162]"/>
                            <span class="text-[11px] text-[#C9C1B1] tracking-widest">{{ formatDate(event.event_date) }}</span>
                            <span class="text-[11px] text-[#C9C1B1] tracking-widest border-l border-[#C9C1B1]/30 pl-3 ml-1">{{ event.photos_count || 0 }} Fotos</span>
                        </div>
                    </div>

                </div>
            </div>
        </SwiperSlide>
    </Swiper>

    <div class="absolute left-6 md:left-12 top-1/2 -translate-y-1/2 z-30 flex flex-col items-center gap-4">
        <span class="text-white text-[11px] font-bold tracking-[0.2em] transition-all duration-300">{{ formatSlideNum(currentHeroSlide) }}</span>
        <div class="w-[1px] h-12 bg-[#C9C1B1]/30"></div>
        <span class="text-[#C9C1B1]/60 text-[11px] tracking-[0.2em]">{{ formatSlideNum(totalHeroSlides) }}</span>
    </div>

    <div class="absolute right-6 md:right-12 top-1/2 -translate-y-1/2 z-30 flex flex-col gap-4">
        <button ref="heroPrevBtn" class="w-12 h-12 rounded-full border border-white/20 flex items-center justify-center text-white hover:border-[#FFB162] hover:bg-[#FFB162] hover:text-[#1B2632] transition-all duration-300 backdrop-blur-sm group">
            <ArrowLeftIcon class="w-4 h-4 group-hover:-translate-x-0.5 transition-transform" />
        </button>
        <button ref="heroNextBtn" class="w-12 h-12 rounded-full border border-white/20 flex items-center justify-center text-white hover:border-[#FFB162] hover:bg-[#FFB162] hover:text-[#1B2632] transition-all duration-300 backdrop-blur-sm group">
            <ArrowRightIcon class="w-4 h-4 group-hover:translate-x-0.5 transition-transform" />
        </button>
    </div>
</div>

        <section class="py-24 bg-[#1B2632] relative font-['Syne'] overflow-hidden">
            <div class="max-w-7xl mx-auto px-8 md:px-16">
                <div class="flex flex-col md:flex-row justify-between items-end gap-6 mb-16">
                    <div>
                        <div class="text-[9px] font-bold tracking-[0.35em] uppercase text-[#FFB162] mb-3 flex items-center gap-3 after:content-[''] after:h-px after:w-12 after:bg-[#A35139]">
                            Últimos Eventos
                        </div>
                        <h2 class="font-['Cormorant_Garamond'] text-4xl md:text-6xl font-light text-[#EEE9DF] leading-none">
                            Lo que <em class="italic text-[#C9C1B1]">captamos</em> <br />recientemente
                        </h2>
                    </div>
                    <Link :href="route('events.index')" class="text-[11px] font-bold tracking-[0.2em] uppercase text-[#FFB162] border-b border-[#A35139] pb-1 hover:text-[#A35139] transition-colors whitespace-nowrap">
                        Ver todos &rarr;
                    </Link>
                </div>

                <div class="relative group">
                    <Swiper v-if="recentEvents && recentEvents.length > 0" :modules="[Navigation, Autoplay]" :slides-per-view="1" :space-between="20"
                        :navigation="{ prevEl: prevButton, nextEl: nextButton }" :autoplay="{ delay: 5000 }" 
                        :breakpoints="{ '768': { slidesPerView: 2 }, '1024': { slidesPerView: 3 } }" class="!overflow-visible">
                        
                        <SwiperSlide v-for="event in recentEvents.slice(0, 15)" :key="event.id">
                            <Link :href="route('events.show', event.slug)" class="block relative overflow-hidden rounded-sm group/card cursor-none aspect-[4/5] bg-[#2C3B4D]">
                                <img v-if="event.cover_image_url" :src="event.cover_image_url" :alt="event.name"
                                    class="w-full h-full object-cover transition-transform duration-[800ms] group-hover/card:scale-105 saturate-[0.8] group-hover/card:saturate-110" @error="handleImageError" />
                                
                                <div class="absolute inset-0 bg-gradient-to-t from-[#1B2632]/90 via-transparent to-transparent opacity-80 group-hover/card:opacity-100 transition-opacity duration-400"></div>
                                
                                <div class="absolute top-5 right-5 w-9 h-9 border border-[#FFB162]/40 flex items-center justify-center text-[#FFB162] opacity-0 -translate-y-2 group-hover/card:opacity-100 group-hover/card:translate-y-0 transition-all duration-300">
                                    &rarr;
                                </div>

                                <div class="absolute bottom-0 left-0 right-0 p-6 translate-y-2 group-hover/card:translate-y-0 transition-transform duration-400">
                                    <div class="text-[9px] font-bold tracking-[0.3em] uppercase text-[#FFB162] mb-1">
                                        {{ event.location || 'Evento' }}
                                    </div>
                                    <h3 class="font-['Cormorant_Garamond'] text-2xl text-[#EEE9DF] leading-tight mb-2">
                                        {{ event.name }}
                                    </h3>
                                    <div class="text-[11px] text-[#C9C1B1] tracking-widest opacity-0 translate-y-2 group-hover/card:opacity-100 group-hover/card:translate-y-0 transition-all duration-400 delay-100">
                                        {{ formatDate(event.event_date) }} &mdash; {{ event.photos_count || 0 }} Fotos
                                    </div>
                                </div>
                            </Link>
                        </SwiperSlide>
                    </Swiper>
                    <div v-else class="text-center py-20 border border-dashed border-[#2C3B4D] text-[#C9C1B1]">
                        No hay eventos recientes.
                    </div>
                </div>
            </div>
        </section>





        <section class="bg-[#2C3B4D] py-20 px-8 relative overflow-hidden font-['Syne'] border-y border-[#FFB162]/10">
            <div class="absolute -top-16 -right-16 w-80 h-80 rounded-full border border-[#FFB162]/10"></div>
            <div class="absolute -bottom-10 left-10 w-40 h-40 rounded-full border border-[#FFB162]/20"></div>
            
            <div class="max-w-7xl mx-auto grid grid-cols-1 md:grid-cols-3 gap-2">
                <div class="bg-[#1B2632]/50 p-8 border border-[#C9C1B1]/10 text-center md:text-left">
                    <div class="font-['Cormorant_Garamond'] text-6xl text-[#FFB162] font-light leading-none mb-2">{{ Math.floor(animatedEvents) }}</div>
                    <div class="text-[10px] tracking-[0.2em] uppercase text-[#C9C1B1] opacity-70">Eventos Cubiertos</div>
                </div>
                <div class="bg-[#1B2632]/50 p-8 border border-[#C9C1B1]/10 text-center md:text-left">
                    <div class="font-['Cormorant_Garamond'] text-6xl text-[#FFB162] font-light leading-none mb-2">{{ Math.floor(animatedPhotos) }}</div>
                    <div class="text-[10px] tracking-[0.2em] uppercase text-[#C9C1B1] opacity-70">Fotografías</div>
                </div>
                <div class="bg-[#1B2632]/50 p-8 border border-[#C9C1B1]/10 text-center md:text-left">
                    <div class="font-['Cormorant_Garamond'] text-6xl text-[#FFB162] font-light leading-none mb-2">{{ Math.floor(animatedPhotographers) }}</div>
                    <div class="text-[10px] tracking-[0.2em] uppercase text-[#C9C1B1] opacity-70">Profesionales</div>
                </div>
            </div>
        </section>

        <FutureEventsSection :is-authenticated="!!auth?.user" :user-role="auth?.user?.role" />

        <div class="h-[500px] overflow-hidden relative border-t border-[#1B2632]">
            <FutureEventsMap :events="futureEvents" />
            <div class="absolute bottom-6 left-6 z-20">
                <Link :href="route('future-events.map')" class="bg-[#1B2632] text-[#EEE9DF] px-4 py-3 text-[10px] font-bold uppercase tracking-[0.2em] hover:bg-[#FFB162] hover:text-[#1B2632] transition-colors flex items-center gap-2">
                    <span>Ver Mapa Completo</span>
                </Link>
            </div>
        </div>

        <RecentPhotosSection :photos="recentPhotos" title="Últimas incorporaciones" subtitle="Explora las imágenes más recientes añadidas a nuestro archivo." />

    </AppLayout>
</template>

<style>
@keyframes fade-in { from { opacity: 0; transform: translateY(30px); } to { opacity: 1; transform: translateY(0); } }
.animate-fade-in { animation: fade-in 1s cubic-bezier(0.25, 0.46, 0.45, 0.94) forwards; }
.animate-fade-in-delayed { opacity: 0; animation: fade-in 1s cubic-bezier(0.25, 0.46, 0.45, 0.94) 0.3s forwards; }

 
@keyframes fade-in {
    from {
        opacity: 0;
        transform: translateY(20px);
    }

    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.animate-fade-in {
    animation: fade-in 1s ease-out forwards;
}

.animate-fade-in-delayed {
    opacity: 0;
    animation: fade-in 1s ease-out 0.3s forwards;
}

 
.custom-nav-btn {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    width: 60px;
    
    height: 60px;
    background-color: white;
    border: 1px solid #0f172a;
   
    color: #0f172a;
    display: flex;
    
    align-items: center;
    justify-content: center;
    cursor: pointer;
    z-index: 50;
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
}

 
.prev-btn {
    left: -80px;
     
}

.next-btn {
    right: -80px;
    
}




/* Animaciones exclusivas del nuevo Hero Slider */
.hero-content-container .slide-anim {
    opacity: 0;
    transform: translateY(30px);
    transition: opacity 0.8s ease, transform 0.8s cubic-bezier(0.25, 0.46, 0.45, 0.94);
}

.swiper-slide-active .hero-content-container .slide-anim {
    opacity: 1;
    transform: translateY(0);
}

.delay-100 { transition-delay: 150ms !important; }
.delay-200 { transition-delay: 300ms !important; }
.delay-\[300ms\] { transition-delay: 450ms !important; }

/* Efecto Ken Burns (zoom suave) para las imágenes de los eventos al estar activas */
.hero-img-anim {
    transform: scale(1.08);
    transition: transform 10s ease-out;
}
.swiper-slide-active .hero-img-anim {
    transform: scale(1);
}
 
.custom-nav-btn:hover {
    background-color: #0f172a;
    color: white;
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
    transform: translateY(-50%) scale(1.05);
    /* Mantiene el centrado vertical y escala */
}

 
.swiper-button-disabled {
    opacity: 0.3;
    cursor: not-allowed;
    background-color: #f1f5f9;
    /* Slate 100 */
    border-color: #cbd5e1;
    color: #94a3b8;
}

 
@media (max-width: 1024px) {
    .custom-nav-btn {
        display: none !important;
    }
}
</style>