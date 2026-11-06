<script setup>
import { ref, computed } from 'vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { Swiper, SwiperSlide } from 'swiper/vue';
import { Navigation, Thumbs, Keyboard, FreeMode, Virtual } from 'swiper/modules';
import 'swiper/css';
import 'swiper/css/navigation';
import 'swiper/css/thumbs';
import 'swiper/css/free-mode';

import AppLayout from '@/Layouts/AppLayout.vue';
import ProtectedImage from '@/Components/ProtectedImage.vue';
import { useToast } from '@/Composables/useToast';
import axios from 'axios';

import {
    ArrowLeftIcon, MapPinIcon, CalendarIcon, XMarkIcon, CameraIcon, 
    HashtagIcon, ArrowRightIcon, MagnifyingGlassIcon, ShoppingCartIcon
} from '@heroicons/vue/24/outline';

const props = defineProps({
    event: Object,
    photos: Object,
    permissions: Object,
    stats: Object,
    galleries: { type: Array, default: () => [] },
    filters: Object
});

const { success, error } = useToast();
const page = usePage();
const isAuthenticated = computed(() => page.props.auth.user !== null);
const addingToCartIds = ref([]);

const swiperModules = [Navigation, Thumbs, Keyboard, FreeMode, Virtual];
const lightboxPhotos = ref([]);
const isLightboxOpen = ref(false);
const activeIndex = ref(0);
const thumbsSwiper = ref(null);


const activePhotographer = ref(null);
const activePhotographerTotal = ref(0);


const mixedPhotos = computed(() => {
    const interleaved = [];

    const galleriesCopy = props.galleries.map(g => ({
        photographer: g.photographer,
        photos: [...g.photos]
    }));
    
    let hasMore = true;
    while (hasMore) {
        hasMore = false;
        for (const gallery of galleriesCopy) {
            if (gallery.photos.length > 0) {
                const photo = gallery.photos.shift();
                interleaved.push({
                    ...photo,
                    gallery_photographer: gallery.photographer 
                });
                hasMore = true;
            }
        }
    }
    return interleaved;
});

const setThumbsSwiper = (swiper) => { thumbsSwiper.value = swiper; };

const openLightbox = (clickedPhoto) => {

    const gallery = props.galleries.find(g => g.photographer.id === clickedPhoto.gallery_photographer.id);
    
    if (gallery) {
        activePhotographer.value = gallery.photographer;
        activePhotographerTotal.value = gallery.photos_count;
        lightboxPhotos.value = gallery.photos; 
        
        const newIndex = gallery.photos.findIndex(p => p.id === clickedPhoto.id);
        activeIndex.value = newIndex !== -1 ? newIndex : 0;
        isLightboxOpen.value = true;
    }
};

const closeLightbox = () => {
    isLightboxOpen.value = false;
    thumbsSwiper.value = null;
};

const addToCart = async (photo) => {
    if (!isAuthenticated.value) {
        window.location.href = route('login');
        return;
    }
    if (addingToCartIds.value.includes(photo.id)) return;
    addingToCartIds.value.push(photo.id);

    try {
        const response = await axios.post(route('cart.add', photo.id));
        if (response.data.success) {
            success('Fotografía agregada al carrito');
            window.dispatchEvent(new Event('cart-updated'));
        } else {
            error('La fotografía ya está en el carrito');
        }
    } catch (err) {
        error('Error de conexión');
    } finally {
        addingToCartIds.value = addingToCartIds.value.filter(id => id !== photo.id);
    }
};

const handleImageError = (e) => {
    e.target.style.display = 'none';
    const parent = e.target.parentElement;
    if (!parent.querySelector('.placeholder-img')) {
        const placeholder = document.createElement('div');
        placeholder.className = 'placeholder-img w-full h-full min-h-[250px] flex items-center justify-center bg-slate-100 border border-slate-200';
        placeholder.innerHTML = `<span class="font-bold text-xs text-slate-400 uppercase tracking-widest">Sin Imagen</span>`;
        parent.appendChild(placeholder);
    }
};
</script>

<template>
    <Head>
        <title>{{ event.name }} | F33 Fotografía</title>
        <meta name="description" :content="event.description ? event.description.substring(0, 155) + '...' : `Galería de fotos del evento ${event.name}.`" />
        <meta property="og:image" :content="event.cover_image_url" />
    </Head>

    <AppLayout>
        <div class="min-h-screen bg-[#F8F9FA] text-slate-800 font-sans antialiased py-12 pt-24 md:pt-28">
            <div class="max-w-[1400px] mx-auto px-4 sm:px-6 lg:px-8">

                <div class="mb-6">
                    <Link :href="route('events.index')" class="inline-flex items-center gap-2 bg-white px-4 py-2 rounded-full text-xs font-bold uppercase tracking-wider text-gray-500 hover:text-black hover:shadow-sm border border-gray-200 transition-all">
                        <ArrowLeftIcon class="w-4 h-4" /> Volver a eventos
                    </Link>
                </div>

                <div class="bg-white rounded overflow-hidden shadow-sm border border-gray-100 relative mb-12 flex flex-col lg:flex-row">
                    <div class="lg:absolute lg:inset-y-0 lg:right-0 lg:w-1/2 h-64 lg:h-full order-1 lg:order-2">
                        <img v-if="event.cover_image_url" :src="event.cover_image_url" class="w-full h-full object-cover">
                        <div class="absolute inset-0 bg-gradient-to-t lg:bg-gradient-to-r from-white via-white/80 to-transparent"></div>
                    </div>

                    <div class="p-8 md:p-12 relative z-10 lg:w-3/4 order-2 lg:order-1 flex flex-col justify-center">
                        <div class="flex flex-wrap gap-3 mb-6">
                            <span v-if="event.is_private" class="bg-red-50 text-[#E30613] border border-red-100 px-3 py-1.5 rounded-md text-[10px] font-bold uppercase tracking-widest flex items-center gap-1">
                                <span class="w-2 h-2 rounded-full bg-[#E30613] animate-pulse"></span> Evento Privado
                            </span>
                            <span class="bg-gray-100 text-gray-600 border border-gray-200 px-3 py-1.5 rounded-md text-[10px] font-bold uppercase tracking-widest">
                                {{ event.photos_count || 0 }} Fotografías
                            </span>
                        </div>
                        <h1 class="font-flux text-4xl md:text-5xl lg:text-6xl text-black leading-none mb-4 tracking-wide">
                            {{ event.name }}
                        </h1>
                        <p v-if="event.description" class="text-gray-500 max-w-xl text-sm md:text-base leading-relaxed mb-8 font-medium">
                            {{ event.description }}
                        </p>
                        <div class="flex flex-wrap gap-6 text-xs font-bold uppercase tracking-wider text-gray-400">
                            <div class="flex items-center gap-2 bg-gray-50 px-4 py-2 rounded-full border border-gray-100">
                                <CalendarIcon class="w-4 h-4 text-[#E30613]" /> <span class="text-slate-700">{{ event.event_date }}</span>
                            </div>
                            <div v-if="event.location" class="flex items-center gap-2 bg-gray-50 px-4 py-2 rounded-full border border-gray-100">
                                <MapPinIcon class="w-4 h-4 text-[#E30613]" /> <span class="text-slate-700 truncate max-w-[200px]">{{ event.location }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 md:gap-6 mb-12">
                    <Link :href="route('events.face-search', event.slug)" class="group bg-white border border-gray-100 p-6 md:p-8 rounded shadow-sm hover:shadow-md hover:border-red-100 transition-all flex items-center justify-between">
                        <div class="flex items-center gap-5">
                            <div class="w-14 h-14 bg-red-50 rounded-full flex items-center justify-center shrink-0 group-hover:scale-110 transition-transform">
                                <MagnifyingGlassIcon class="w-6 h-6 text-[#E30613]" />
                            </div>
                            <div>
                                <h4 class="font-flux text-2xl text-black group-hover:text-[#E30613] transition-colors leading-none mb-1">Escáner facial</h4>
                                <p class="text-xs font-bold uppercase tracking-wider text-gray-400">Encontrá tus fotos con una selfie</p>
                            </div>
                        </div>
                        <div class="w-10 h-10 rounded-full bg-gray-50 flex items-center justify-center group-hover:bg-[#E30613] transition-colors shrink-0">
                            <ArrowRightIcon class="w-4 h-4 text-gray-400 group-hover:text-white transition-colors" />
                        </div>
                    </Link>
                    <Link :href="route('events.bib-search', event.slug)" class="group bg-white border border-gray-100 p-6 md:p-8 rounded shadow-sm hover:shadow-md hover:border-gray-300 transition-all flex items-center justify-between">
                        <div class="flex items-center gap-5">
                            <div class="w-14 h-14 bg-gray-100 rounded-full flex items-center justify-center shrink-0 group-hover:scale-110 transition-transform">
                                <HashtagIcon class="w-6 h-6 text-black" />
                            </div>
                            <div>
                                <h4 class="font-flux text-2xl text-black leading-none mb-1">Búsqueda OCR</h4>
                                <p class="text-xs font-bold uppercase tracking-wider text-gray-400">Buscar por número de dorsal</p>
                            </div>
                        </div>
                        <div class="w-10 h-10 rounded-full bg-gray-50 flex items-center justify-center group-hover:bg-black transition-colors shrink-0">
                            <ArrowRightIcon class="w-4 h-4 text-gray-400 group-hover:text-white transition-colors" />
                        </div>
                    </Link>
                </div>

                <div v-if="mixedPhotos.length === 0" class="text-center py-24 bg-white rounded shadow-sm border border-gray-100 mt-8 flex flex-col items-center">
                    <div class="w-20 h-20 bg-gray-50 rounded-full flex items-center justify-center mb-6">
                        <CameraIcon class="w-10 h-10 text-gray-300" />
                    </div>
                    <h3 class="font-flux text-4xl text-black mb-3">Sin material disponible</h3>
                    <p class="text-sm font-medium text-gray-500">Aún no se cargaron fotografías para este evento.</p>
                </div>

                <div v-else class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-3 md:gap-4">
                    <div v-for="photo in mixedPhotos" :key="photo.id"
                        @click="openLightbox(photo)"
                        class="group relative rounded overflow-hidden aspect-[4/5] cursor-pointer shadow-sm border border-gray-200 hover:shadow-lg hover:border-gray-300 transition-all duration-300 bg-gray-100">

                        <ProtectedImage :src="photo.watermarked_url || photo.thumbnail_url" loading="lazy" class="w-full h-full object-cover rounded shadow-2xl relative z-10" @error="handleImageError" />

                        <div class="absolute inset-0 z-20 bg-gradient-to-t from-black/80 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex flex-col justify-end p-3 pointer-events-none">
                            <div class="flex justify-between items-end w-full">
                                
                                <div class="flex flex-col gap-1.5">
                                    <div class="flex flex-col gap-0.5">
                                        <span v-if="photo.location_role" class="block bg-white/90 backdrop-blur-sm text-black font-bold text-[9px] px-2 py-0.5 rounded uppercase tracking-wider w-max">
                                            {{ photo.location_role }}
                                        </span>
                                        <span class="text-white/80 font-bold text-[9px] uppercase tracking-wider">
                                            ID: {{ photo.unique_id.substring(0, 6) }}
                                        </span>
                                    </div>
                                    <div class="flex items-center gap-2 mt-1">
                                        <img :src="photo.gallery_photographer?.profile_photo_url || 'https://via.placeholder.com/50'" class="w-5 h-5 rounded-full object-cover border border-white/30">
                                        <span class="text-white font-bold text-[9px] uppercase tracking-wider truncate max-w-[100px]">
                                            {{ photo.gallery_photographer?.business_name || 'Fotógrafo' }}
                                        </span>
                                    </div>
                                </div>

                                <div class="flex flex-col items-end gap-2">
                                    <span class="bg-[#E30613] text-white font-bold text-[10px] px-2 py-1 rounded-md shadow-sm">${{ photo.price }}</span>
                                    
                                    <button @click.prevent.stop="addToCart(photo)" title="Añadir al carrito" class="bg-white/90 backdrop-blur p-2 rounded-full shadow-sm flex items-center justify-center text-black hover:bg-black hover:text-white transition-colors duration-300 pointer-events-auto z-20">

                                        <svg v-if="addingToCartIds?.includes(photo.id)"
                                            class="animate-spin w-4 h-4" xmlns="http://www.w3.org/2000/svg"
                                            fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        <ShoppingCartIcon v-else class="w-4 h-4" />
                                    </button>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <!-- LIGHTBOX SWIPER -->
        <Teleport to="body">
            <Transition name="fade">
                <div v-if="isLightboxOpen" class="fixed inset-0 z-[99999] bg-slate-900/95 backdrop-blur-sm flex flex-col">

                    <div class="absolute top-0 right-0 left-0 p-4 flex justify-between items-center z-50 pointer-events-none">
                        <div class="text-white/50 text-xs font-bold uppercase px-4 pointer-events-auto">Vista Previa Fotógrafo</div>
                        <button @click="closeLightbox" class="w-10 h-10 bg-white/10 hover:bg-[#E30613] text-white rounded-full flex items-center justify-center transition-colors pointer-events-auto backdrop-blur-md">
                            <XMarkIcon class="w-5 h-5" />
                        </button>
                    </div>

                    <div class="relative z-10 h-24 md:h-28 w-full bg-black/50 shrink-0 px-4 py-2 border-t border-white/10 order-last">
                        <swiper @swiper="setThumbsSwiper" :modules="swiperModules" :spaceBetween="10" :slidesPerView="'auto'" :freeMode="true" :watchSlidesProgress="true" :initialSlide="activeIndex" class="h-full thumbs-gallery">
                            <swiper-slide v-for="photo in lightboxPhotos" :key="'thumb-' + photo.id" class="!w-16 md:!w-20 h-full rounded cursor-pointer overflow-hidden opacity-40 transition-opacity hover:opacity-100">
                                <img :src="photo.thumbnail_url" class="w-full h-full object-cover" />
                            </swiper-slide>
                        </swiper>
                    </div>

                    <div class="relative z-10 flex-1 w-full min-h-0 mt-12 order-1" v-if="thumbsSwiper">
                        <swiper :modules="swiperModules" :virtual="{ enabled: true }" :initialSlide="activeIndex" :navigation="true" :keyboard="{ enabled: true }" :thumbs="{ swiper: thumbsSwiper }" :spaceBetween="30" class="h-full w-full">
                            <swiper-slide v-for="(photo, index) in lightboxPhotos" :key="'main-' + photo.id" :virtualIndex="index" class="flex items-center justify-center p-4">
                                <div class="relative h-full w-full max-w-full flex items-center justify-center">

                                    <div class="absolute inset-0 flex flex-col items-center justify-center z-0">
                                        <svg class="animate-spin w-10 h-10 text-white/20 mb-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                        <span class="text-white/40 text-xs font-bold uppercase tracking-widest">Cargando...</span>
                                    </div>

                                    <ProtectedImage :src="photo.watermarked_url || photo.thumbnail_url" loading="lazy" class="max-h-full max-w-full object-contain rounded shadow-2xl relative z-10" @error="handleImageError" />

                                    <!-- Panel Flotante Inferior en Lightbox -->
                                    <div class="absolute bottom-4 left-4 right-4 p-3 md:p-5 bg-white/90 rounded flex flex-col md:flex-row justify-between gap-4 items-center border border-white z-20">
                                        
                                        <!-- Perfil Fotógrafo + Ver Todas -->
                                        <div class="flex items-center gap-4 w-full md:w-auto">
                                            <img :src="activePhotographer?.profile_photo_url || 'https://via.placeholder.com/50'" class="w-12 h-12 rounded-full border border-gray-200 object-cover">
                                            <div>
                                                <p class="font-bold text-sm text-black">{{ activePhotographer?.business_name }}</p>
                                                
                                                <Link v-if="activePhotographerTotal > lightboxPhotos.length" :href="route('events.show-photographer', [event.slug, activePhotographer?.slug])" 
                                                    class="text-[#E30613] text-[10px] font-bold uppercase tracking-wider hover:underline flex items-center gap-1 mt-1">
                                                    Ver las {{ activePhotographerTotal }} fotos <ArrowRightIcon class="w-3 h-3" />
                                                </Link>
                                                <p v-else class="text-gray-400 font-bold text-[10px] uppercase tracking-wider mt-1">
                                                    Ref: {{ photo.unique_id }}
                                                </p>
                                            </div>
                                        </div>

                                        <div class="flex items-center gap-4 w-full md:w-auto justify-end">
                                            <span class="text-[#E30613] font-flux text-4xl leading-none mr-2">${{ photo.price }}</span>
                                            <button @click.prevent.stop="addToCart(photo)" class="bg-black hover:bg-[#E30613] text-white px-5 py-3 rounded-full text-xs font-bold uppercase tracking-wider flex items-center gap-2 transition-colors shadow-md">
                                                <span v-if="addingToCartIds?.includes(photo.id)">Añadiendo...</span>
                                                <span v-else class="flex items-center gap-2"><ShoppingCartIcon class="w-4 h-4" /> Añadir</span>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </swiper-slide>
                        </swiper>
                    </div>
                </div>
            </Transition>
        </Teleport>
    </AppLayout>
</template>

<style scoped>
.thumbs-gallery .swiper-slide-thumb-active {
    opacity: 1 !important;
    border: 2px solid white;
}
:deep(.swiper-button-next), :deep(.swiper-button-prev) {
    color: white;
    background-color: rgba(0, 0, 0, 0.5);
    width: 3rem;
    padding: 10px;
    height: 3rem;
    margin: 0 10px;
    border-radius: 50%;
}
.fade-enter-active, .fade-leave-active { transition: opacity 0.3s ease, transform 0.3s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; transform: scale(0.98); }
:deep(.swiper-button-next:after), :deep(.swiper-button-prev:after) { font-size: 1.2rem; font-weight: bold; }
</style>