<script setup>
import { Head, Link, usePage } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';
import RecentPhotosSection from '@/Components/RecentPhotosSection.vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import { ArrowLongRightIcon, ArrowLeftIcon, ArrowRightIcon } from '@heroicons/vue/24/outline';
import FutureEventsSection from '@/Components/FutureEventsSection.vue';
import FutureEventsMap from '@/Components/FutureEventsMap.vue';

const prevButton = ref(null);
const nextButton = ref(null);
// --- SWIPER ---
import { Swiper, SwiperSlide } from 'swiper/vue';
import { Navigation, Autoplay } from 'swiper/modules';
import 'swiper/css';
import 'swiper/css/navigation';

const props = defineProps({
    canLogin: Boolean,
    canRegister: Boolean,
    recentEvents: { type: Array, default: () => [] },
    futureEvents: {        //  AGREGAR ESTO
        type: Array,
        default: () => []
    },
    recentPhotos: { type: Array, default: () => [] },
    stats: { type: Object, default: () => ({ total_photos: 0, total_events: 0, total_photographers: 0 }) },
    videoList: { type: Array, default: () => [] }
});


// --- Stats Animation ---
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
    //  Iniciar animación de estadísticas
    animateCount(props.stats.total_events || 0, animatedEvents);
    animateCount(props.stats.total_photos || 0, animatedPhotos);
    animateCount(props.stats.total_photographers || 0, animatedPhotographers);

    // Video logic
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

        <div class="relative h-[85vh] min-h-[600px] overflow-hidden bg-slate-900">
            <video autoplay muted playsinline class="absolute inset-0 w-full h-full object-cover opacity-60"
                ref="promoVideo">
                <source :src="currentVideo" type="video/mp4">
            </video>
            <div class="absolute inset-0 bg-gradient-to-t from-slate-900 via-slate-900/40 to-transparent"></div>
            <div class="relative h-full flex flex-col items-center pt-32 text-center px-4">
                <span
                    class="text-xs md:text-sm font-bold tracking-[0.3em] text-white/80 uppercase mb-4 animate-fade-in">
                    Fotografía Profesional
                </span>
                <h1
                    class="text-4xl md:text-6xl lg:text-7xl font-serif font-medium text-white mb-6 drop-shadow-lg max-w-7xl leading-tight animate-fade-in">
                    Preservando momentos <br /> <span class="italic text-gray-300">irrepetibles.</span>
                </h1>
                <p class="text-lg text-gray-300 mb-10 max-w-2xl font-light leading-relaxed animate-fade-in-delayed">
                    Acceda a su galería privada o explore nuestra cobertura de eventos recientes con la calidad que nos
                    distingue.
                </p>
                <!--Link :href="route('gallery.index')"
                    class="group relative px-8 py-3 overflow-hidden bg-white text-slate-900 text-sm font-bold uppercase tracking-widest hover:bg-gray-100 transition duration-300">
                <span class="relative z-10">Buscar mis Fotos</span>
                </Link-->
            </div>
        </div>

        <div class="relative z-20 -mt-64 pb-20 px-4 sm:px-6 lg:px-8">
            <div class="max-w-7xl mx-auto">

                <div class="bg-white shadow-2xl rounded-sm border-t-4 border-slate-900">

                    <div
                        class="p-8 md:p-10 border-b border-gray-100 flex flex-col md:flex-row justify-between items-end gap-4">
                        <div>
                            <h2 class="text-3xl font-serif font-bold text-slate-900">Eventos Recientes</h2>
                            <p class="text-slate-500 mt-2 font-light">Deslice para explorar las últimas 15 coberturas.
                            </p>
                        </div>
                        <Link :href="route('events.index')"
                            class="group flex items-center text-sm font-bold uppercase tracking-widest text-slate-900 hover:text-slate-600 transition">
                            Ver todo el archivo
                            <ArrowLongRightIcon class="ml-2 w-5 h-5 transform group-hover:translate-x-1 transition" />
                        </Link>
                    </div>


                    <div class="p-8 md:p-10 bg-gray-50/50 relative">
                        <div v-if="recentEvents && recentEvents.length > 0">

                            <div class="relative"> <!-- relative group -->

                                <Swiper :modules="[Navigation, Autoplay]" :slides-per-view="1" :space-between="30"
                                    :navigation="{
                                        prevEl: prevButton,
                                        nextEl: nextButton,
                                    }" :autoplay="{ delay: 5000, disableOnInteraction: false }" :breakpoints="{
                                        '640': { slidesPerView: 1, spaceBetween: 20 },
                                        '768': { slidesPerView: 2, spaceBetween: 30 },
                                        '1024': { slidesPerView: 3, spaceBetween: 30 },
                                    }" class="!static">
                                    <SwiperSlide v-for="event in recentEvents.slice(0, 15)" :key="event.id"
                                        class="h-auto">
                                        <Link :href="route('events.show', event.slug)"
                                            class="group block bg-white rounded-sm shadow-sm hover:shadow-xl transition-all duration-500 border border-gray-100 h-full flex flex-col">

                                            <div class="relative h-60 overflow-hidden bg-gray-200 flex-shrink-0">
                                                <img v-if="event.cover_image_url" :src="event.cover_image_url"
                                                    :alt="event.name"
                                                    class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105 filter saturate-[0.8] group-hover:saturate-100"
                                                    @error="handleImageError" />
                                                <div v-else
                                                    class="w-full h-full flex items-center justify-center bg-gray-100">
                                                    <svg class="w-10 h-10 text-gray-300" fill="none"
                                                        stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="1"
                                                            d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                    </svg>
                                                </div>

                                                <div
                                                    class="absolute top-0 right-0 bg-white/95 backdrop-blur px-4 py-2 shadow-sm border-b border-l border-gray-100">
                                                    <div
                                                        class="text-[10px] font-bold uppercase tracking-wider text-slate-900">
                                                        {{ formatDate(event.event_date) }}
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="p-6 flex flex-col flex-1">
                                                <h3
                                                    class="text-xl font-serif font-bold text-slate-900 mb-2 line-clamp-1 group-hover:text-blue-900 transition-colors">
                                                    {{ event.name }}
                                                </h3>

                                                <div class="flex flex-col space-y-2 mb-6 flex-1">
                                                    <div v-if="event.location"
                                                        class="flex items-center text-xs text-slate-500 uppercase tracking-wide">
                                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor"
                                                            viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2"
                                                                d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                                        </svg>
                                                        <span class="truncate">{{ event.location }}</span>
                                                    </div>
                                                    <div
                                                        class="flex items-center text-xs text-slate-500 uppercase tracking-wide">
                                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor"
                                                            viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2"
                                                                d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                        </svg>
                                                        {{ event.photos_count || 0 }} Fotos
                                                    </div>
                                                </div>

                                                <div
                                                    class="w-full text-center py-3 border border-slate-900 text-slate-900 text-xs font-bold uppercase tracking-widest group-hover:bg-slate-900 group-hover:text-white transition-colors duration-300">
                                                    Ver Galería
                                                </div>
                                            </div>
                                        </Link>
                                    </SwiperSlide>
                                </Swiper>

                                <div ref="prevButton" class="custom-nav-btn prev-btn hidden lg:flex">
                                    <ArrowLeftIcon class="w-5 h-5" />
                                </div>

                                <div ref="nextButton" class="custom-nav-btn next-btn hidden lg:flex">
                                    <ArrowRightIcon class="w-5 h-5" />
                                </div>

                            </div>
                        </div>

                        <div v-else class="text-center py-20 bg-white border border-dashed border-gray-300 rounded-sm">
                            <p class="text-slate-400 font-serif text-lg">No hay eventos recientes disponibles.</p>
                        </div>
                    </div>

                    <div class="bg-slate-50 border-t border-gray-200 px-8 py-10">
                        <div
                            class="grid grid-cols-1 md:grid-cols-3 gap-8 text-center divide-y md:divide-y-0 md:divide-x divide-gray-200">
                            <div class="pt-4 md:pt-0">
                                <span class="block text-4xl font-serif font-bold text-slate-900">{{
                                    Math.floor(animatedEvents) }}</span>
                                <span
                                    class="text-xs font-bold uppercase tracking-widest text-slate-500 mt-2 block">Eventos
                                    Cubiertos</span>
                            </div>
                            <div class="pt-4 md:pt-0">
                                <span class="block text-4xl font-serif font-bold text-slate-900">{{
                                    Math.floor(animatedPhotos) }}</span>
                                <span
                                    class="text-xs font-bold uppercase tracking-widest text-slate-500 mt-2 block">Fotografías</span>
                            </div>
                            <div class="pt-4 md:pt-0">
                                <span class="block text-4xl font-serif font-bold text-slate-900">{{
                                    Math.floor(animatedPhotographers) }}</span>
                                <span
                                    class="text-xs font-bold uppercase tracking-widest text-slate-500 mt-2 block">Profesionales</span>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <FutureEventsSection :is-authenticated="!!auth?.user" :user-role="auth?.user?.role" />

        <div class="h-[500px] overflow-hidden shadow-lg relative">
            <FutureEventsMap :events="futureEvents" />

            <div class="absolute bottom-6 left-6 z-20">
                <Link :href="route('future-events.map')"
                    class="inline-flex items-center gap-2 px-3 py-2 rounded-lg bg-black/70 text-white text-[10px] font-bold uppercase tracking-[0.2em] shadow-lg hover:bg-black/85 transition">
                    <svg class="w-6 h-6 text-gray-800 dark:text-white" aria-hidden="true"
                        xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8 4H4m0 0v4m0-4 5 5m7-5h4m0 0v4m0-4-5 5M8 20H4m0 0v-4m0 4 5-5m7 5h4m0 0v-4m0 4-5-5" />
                    </svg>

                    <span>Ver en pantalla completa</span>
                </Link>
            </div>
        </div>


        <div class="py-16">
            <RecentPhotosSection :photos="recentPhotos" title="Últimas Incorporaciones"
                subtitle="Explora las imágenes más recientes añadidas a nuestro archivo." />
        </div>

    </AppLayout>
</template>

<style>
/* Animaciones generales */
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

/* --- BOTONES DE NAVEGACIÓN PERSONALIZADOS --- */
.custom-nav-btn {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    width: 60px;
    /* Tamaño grande para impacto editorial */
    height: 60px;
    background-color: white;
    border: 1px solid #0f172a;
    /* Slate 900 */
    color: #0f172a;
    display: flex;
    /* Flex para centrar el icono */
    align-items: center;
    justify-content: center;
    cursor: pointer;
    z-index: 50;
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
}

/* Posicionamiento FUERA del Swiper */
/* Ajusta los valores de px según el margen de tu diseño */
.prev-btn {
    left: -80px;
    /* Lo saca hacia la izquierda */
}

.next-btn {
    right: -80px;
    /* Lo saca hacia la derecha */
}

/* Efecto Hover */
.custom-nav-btn:hover {
    background-color: #0f172a;
    color: white;
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
    transform: translateY(-50%) scale(1.05);
    /* Mantiene el centrado vertical y escala */
}

/* Estado Desactivado (Swiper le agrega esta clase automáticamente si se configura bien) */
.swiper-button-disabled {
    opacity: 0.3;
    cursor: not-allowed;
    background-color: #f1f5f9;
    /* Slate 100 */
    border-color: #cbd5e1;
    color: #94a3b8;
}

/* Ocultar botones en móviles para usar swipe */
@media (max-width: 1024px) {
    .custom-nav-btn {
        display: none !important;
    }
}
</style>