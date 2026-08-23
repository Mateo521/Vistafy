<script setup>
import { ref, computed } from 'vue';

import { Head, Link, usePage } from '@inertiajs/vue3';
import { Swiper, SwiperSlide } from 'swiper/vue';
import { Navigation, Thumbs, Keyboard, FreeMode } from 'swiper/modules';
import 'swiper/css';
import 'swiper/css/navigation';
import 'swiper/css/thumbs';
import 'swiper/css/free-mode';

import AppLayout from '@/Layouts/AppLayout.vue';
import ProtectedImage from '@/Components/ProtectedImage.vue';

import { useToast } from '@/Composables/useToast';
import axios from 'axios';



import {
    ArrowLeftIcon,
    MapPinIcon,
    CalendarIcon,
    XMarkIcon,
    CameraIcon,
    ShoppingCartIcon
} from '@heroicons/vue/24/outline';

const props = defineProps({
    event: Object,
    photos: Object,
    permissions: Object,
    stats: Object,
    galleries: {
        type: Array,
        default: () => []
    },
    filters: Object
});

const { success, error } = useToast();
const page = usePage();

const swiperModules = [Navigation, Thumbs, Keyboard, FreeMode];
const lightboxPhotos = ref([]);

const isLightboxOpen = ref(false);
const activeIndex = ref(0);
const thumbsSwiper = ref(null);


const setThumbsSwiper = (swiper) => {
    thumbsSwiper.value = swiper;
};


const openLightbox = (index, photosArray) => {
    lightboxPhotos.value = photosArray;
    activeIndex.value = index;
    isLightboxOpen.value = true;
};

const closeLightbox = () => {
    isLightboxOpen.value = false;
    thumbsSwiper.value = null;
};

const isAuthenticated = computed(() => page.props.auth.user !== null);
const addingToCartIds = ref([]);

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
        console.error('Error agregando al carrito:', err);
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

    <Head :title="`${event.name} — f33.click`" />

    <AppLayout>
        <div
            class="min-h-screen bg-[#F8F9FA] text-slate-800 font-sans selection:bg-red-600 selection:text-white pb-20 pt-24 md:pt-32">

            <div class="max-w-[1400px] mx-auto px-4 md:px-8">


                <div class="mb-12">
                    <Link :href="route('events.index')"
                        class="inline-flex items-center gap-2 text-xs font-bold uppercase tracking-widest text-slate-400 hover:text-red-600 transition-colors mb-6">
                        <ArrowLeftIcon class="w-4 h-4" /> Volver a eventos subidos
                    </Link>

                    <div
                        class="bg-white rounded overflow-hidden shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-slate-100 relative">

                        <div
                            class="absolute w-full inset-0 h-48 md:h-full  right-0 opacity-10 md:opacity-20 pointer-events-none overflow-hidden">
                            <img v-if="event.cover_image_url" :src="event.cover_image_url"
                                class="w-full h-full object-cover grayscale mask-image-gradient" />
                        </div>

                        <div class="p-8 md:p-12 relative z-10">
                            <div class="flex flex-wrap gap-3 mb-4">
                                <span v-if="event.is_private"
                                    class="bg-red-600 text-white px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-widest">
                                    Evento Privado
                                </span>
                                <span
                                    class="bg-black text-white px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-widest">
                                    {{ event.photos_count }} fotos hechas
                                </span>
                            </div>

                            <h1
                                class="font-flux text-xl md:text-3xl text-black leading-none mb-4 uppercase tracking-tight">
                                {{ event.name }}
                            </h1>

                            <p v-if="event.description"
                                class="text-slate-500 max-w-2xl text-sm md:text-base leading-relaxed mb-8 font-lato">
                                {{ event.description }}
                            </p>

                            <div class="flex flex-wrap gap-6 text-xs font-bold uppercase tracking-wider text-slate-400">
                                <div class="flex items-center gap-2">
                                    <CalendarIcon class="w-5 h-5 text-red-600" />
                                    <span>{{ event.event_date }}</span>
                                </div>
                                <div v-if="event.location" class="flex items-center gap-2">
                                    <MapPinIcon class="w-5 h-5 text-red-600" />
                                    <span>{{ event.location }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>


                <div v-if="!galleries || galleries.length === 0"
                    class="text-center py-24 bg-white rounded shadow-sm border border-slate-100 mt-8">
                    <div class="w-20 h-20 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-6">
                        <CameraIcon class="w-10 h-10 text-slate-300" />
                    </div>
                    <h3 class="font-flux text-4xl text-black mb-3">Sin material disponible</h3>
                    <p class="font-lato text-slate-500 mb-8">Aún no se han cargado fotografías para este evento o están
                        en revisión.</p>
                </div>


                <div v-else class="space-y-16">

                    <div v-for="gallery in galleries" :key="gallery.photographer.id" class="relative">


                        <div
                            class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6 border-b border-slate-200 py-4 sticky top-22 bg-[#F8F9FA]/90 backdrop-blur-md z-20 px-3">

                            <div class="flex items-center gap-4">

                                <div
                                    class="w-14 h-14 rounded-full bg-slate-200 border-2 border-white shadow-sm overflow-hidden flex-shrink-0 flex items-center justify-center">
                                    <img v-if="gallery.photographer.profile_photo_url"
                                        :src="gallery.photographer.profile_photo_url"
                                        class="w-full h-full object-cover">
                                    <span v-else class="font-flux text-2xl text-slate-400 mt-1">{{
                                        gallery.photographer.business_name.charAt(0) }}</span>
                                </div>


                                <div>
                                    <h3 class="font-flux text-3xl text-black uppercase leading-none mb-1">
                                        {{ gallery.photographer.business_name }}
                                    </h3>

                                    <div v-if="gallery.roles && gallery.roles.length > 0"
                                        class="flex flex-wrap gap-2 mt-1">
                                        <span v-for="role in gallery.roles" :key="role"
                                            class="text-[10px] font-bold text-red-600 bg-red-50 px-2 py-0.5 rounded uppercase tracking-widest border border-red-100">
                                            {{ role }}
                                        </span>
                                    </div>
                                    <span v-else class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">
                                        Fotógrafo
                                    </span>
                                </div>
                            </div>

                            <div class="flex items-center gap-4">
                                <span
                                    class="text-xs font-bold text-slate-400 uppercase tracking-widest bg-white px-4 py-2 rounded-full shadow-sm border border-slate-100 hidden md:block">
                                    {{ gallery.photos_count }} fotos
                                </span>
                                <Link :href="route('events.show-photographer', [event.slug, gallery.photographer.slug])"
                                    class="bg-black text-white px-4 py-2 rounded-full text-xs font-bold uppercase tracking-widest hover:bg-red-600 transition-colors">
                                    Ver todo
                                </Link>
                            </div>



                        </div>


                        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-5 gap-4">

                            <div v-for="(photo, index) in gallery.photos" :key="photo.id"
                                @click="openLightbox(index, gallery.photos)"
                                class="group relative rounded overflow-hidden aspect-[4/5] cursor-pointer shadow-sm hover:shadow-xl transition-all duration-300 bg-slate-100">

                                <ProtectedImage :src="photo.thumbnail_url"
                                    class="w-full h-full object-cover transition-transform duration-700 pointer-events-none"
                                    @error="handleImageError" />

                                <div
                                    class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex flex-col justify-between p-3 md:p-4 pointer-events-none">
                                    <div class="flex justify-between items-start w-full">
                                        <button @click.prevent.stop="addToCart(photo)" title="Añadir al carrito"
                                            class="bg-white/90 backdrop-blur p-1.5 rounded-full shadow-sm flex items-center justify-center gap-0 group/cart hover:bg-black hover:text-white transition-all duration-300 pointer-events-auto z-20">

                                            <svg v-if="addingToCartIds?.includes(photo.id)"
                                                class="animate-spin w-4 h-4 text-black group-hover/cart:text-white shrink-0 m-0.5"
                                                xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                                    stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor"
                                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                                </path>
                                            </svg>
                                            <ShoppingCartIcon v-else
                                                class="w-4 h-4 text-black group-hover/cart:text-white transition-colors shrink-0 m-0.5" />

                                            <span
                                                class="max-w-0 overflow-hidden whitespace-nowrap group-hover/cart:max-w-[100px] text-[10px] font-bold uppercase tracking-wider transition-all duration-300 ease-in-out group-hover/cart:px-1.5 group-hover/cart:mr-1">
                                                Añadir
                                            </span>
                                        </button>

                                        <span v-if="photo.location_role"
                                            class="bg-white/90 backdrop-blur-sm text-black font-bold text-[9px] px-2 py-1 rounded shadow-sm uppercase tracking-widest truncate max-w-[100px]">
                                            {{ photo.location_role }}
                                        </span>
                                    </div>

                                    <div class="w-full flex justify-between items-end">
                                        <span class="text-white font-mono text-[9px] md:text-[10px] tracking-widest">{{
                                            photo.unique_id }}</span>
                                        <span class="bg-red-600 text-white font-bold text-xs px-2 py-1 rounded-lg">${{
                                            photo.price }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

            </div>
        </div>

        <Teleport to="body">
            <div v-if="isLightboxOpen" class="fixed inset-0 z-[99999] bg-black/95 flex flex-col backdrop-blur-sm">


                <div class="absolute inset-0 flex items-center justify-center pointer-events-none overflow-hidden z-0">
                    <img src="/images/logo.png" class="w-[160%] md:w-[80%] opacity-50  select-none"
                        alt="F33 Background" />
                </div>


                <div
                    class="absolute top-0 right-0 left-0 p-4 flex justify-between items-center z-50 pointer-events-none">
                    <div class="text-white/50 text-xs font-mono px-4 pointer-events-auto"></div>
                    <button @click="closeLightbox"
                        class="w-12 h-12 bg-black/50 hover:bg-[#E30613] text-white rounded-full flex items-center justify-center transition-colors pointer-events-auto backdrop-blur-md">
                        <XMarkIcon class="w-6 h-6" />
                    </button>
                </div>


                <div
                    class="relative z-10 h-24 md:h-32 w-full bg-black shrink-0 px-4 py-2 border-t border-white/10 order-last">
                    <swiper @swiper="setThumbsSwiper" :modules="swiperModules" :spaceBetween="10"
                        :slidesPerView="'auto'" :freeMode="true" :watchSlidesProgress="true" :initialSlide="activeIndex"
                        class="h-full thumbs-gallery">
                        <swiper-slide v-for="photo in lightboxPhotos" :key="'thumb-' + photo.id"
                            class="!w-16 md:!w-20 h-full rounded cursor-pointer overflow-hidden opacity-40 transition-opacity hover:opacity-100">
                            <img :src="photo.thumbnail_url" class="w-full h-full object-cover" />
                        </swiper-slide>
                    </swiper>
                </div>


                <div class="relative z-10 flex-1 w-full min-h-0 mt-12 order-1" v-if="thumbsSwiper">
                    <swiper :modules="swiperModules" :initialSlide="activeIndex" :navigation="true"
                        :keyboard="{ enabled: true }" :thumbs="{ swiper: thumbsSwiper }" :spaceBetween="30"
                        class="h-full w-full">

                        <swiper-slide v-for="photo in lightboxPhotos" :key="'main-' + photo.id"
                            class="flex items-center justify-center">
                            <div class="relative h-full max-w-full flex items-center justify-center">
                                <ProtectedImage :src="photo.watermarked_url || photo.thumbnail_url"
                                    class="max-h-full max-w-full object-contain rounded shadow-2xl"
                                    @error="handleImageError" />
                                <div
                                    class="absolute bottom-0 left-0 right-0 p-4 md:p-6 bg-gradient-to-t from-black/90 to-transparent rounded-b flex justify-between items-end">
                                    <div>
                                        <p class="text-white font-mono text-sm tracking-widest opacity-80">{{
                                            photo.unique_id }}</p>
                                        <p class="text-[#E30613] font-bold text-xl">${{ photo.price }}</p>
                                    </div>
                                    <button @click.prevent.stop="addToCart(photo)"
                                        class="bg-white hover:bg-black text-black hover:text-white px-6 py-3 rounded-full text-xs font-bold uppercase tracking-wider flex items-center gap-2 transition-colors shadow-lg">
                                        <span v-if="addingToCartIds?.includes(photo.id)">Añadiendo...</span>
                                        <span v-else>Añadir al carrito</span>
                                    </button>
                                </div>
                            </div>
                        </swiper-slide>
                    </swiper>
                </div>

            </div>
        </Teleport>




    </AppLayout>
</template>

<style scoped>
.mask-image-gradient {
    mask-image: linear-gradient(to right, transparent, black);
    -webkit-mask-image: linear-gradient(to right, transparent, black);
}


.thumbs-gallery .swiper-slide-thumb-active {
    opacity: 1 !important;
    border: 2px solid white;
}

:deep(.swiper-button-next),
:deep(.swiper-button-prev) {
    color: white;
    background-color: rgba(0, 0, 0, 0.5);
    width: 3rem;
    height: 3rem;
    border-radius: 50%;
}

:deep(.swiper-button-next:after),
:deep(.swiper-button-prev:after) {
    font-size: 1.2rem;
    font-weight: bold;
}
</style>