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
import PhotoPrice from '@/Components/PhotoPrice.vue';
import { useToast } from '@/Composables/useToast';
import axios from 'axios';
import {
    MagnifyingGlassIcon,
    XMarkIcon,
    ExclamationTriangleIcon,
    ArrowLeftIcon,
    CloudArrowUpIcon,
    FaceSmileIcon,
    InformationCircleIcon,
    ShoppingCartIcon,
    CheckCircleIcon,
    CheckBadgeIcon
} from '@heroicons/vue/24/outline';

const props = defineProps({
    event: Object,
    cartPhotoIds: { type: Array, default: () => [] },
    purchasedPhotoIds: { type: Array, default: () => [] },
});

const { success, error } = useToast();
const page = usePage();
const isAuthenticated = computed(() => page.props.auth.user !== null);

const cartIds = ref([...props.cartPhotoIds]);
const purchasedIds = ref([...props.purchasedPhotoIds]);
const addingToCartIds = ref([]);

const uploadedImage = ref(null);
const uploadedImageUrl = ref(null);
const searching = ref(false);
const results = ref([]);
const faceError = ref(null);
const processingStage = ref('');

// Lightbox & Swiper
const swiperModules = [Navigation, Thumbs, Keyboard, FreeMode, Virtual];
const isLightboxOpen = ref(false);
const activeIndex = ref(0);
const thumbsSwiper = ref(null);

const setThumbsSwiper = (swiper) => {
    thumbsSwiper.value = swiper;
};

const openLightbox = (index) => {
    activeIndex.value = index;
    isLightboxOpen.value = true;
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
    if (addingToCartIds.value.includes(photo.id) || cartIds.value.includes(photo.id)) return;
    addingToCartIds.value.push(photo.id);

    try {
        const response = await axios.post(route('cart.add', photo.id));
        if (response.data.success) {
            success('Fotografía agregada al carrito');
            cartIds.value.push(photo.id);
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

function handleImageSelect(event) {
    const file = event.target.files[0];
    if (!file) return;

    if (!file.type.startsWith('image/')) {
        alert('Por favor selecciona una imagen');
        return;
    }
    if (file.size > 5 * 1024 * 1024) {
        alert('La imagen es muy grande. Máximo 5MB');
        return;
    }

    uploadedImage.value = file;
    results.value = [];
    faceError.value = null;
    processingStage.value = '';

    const reader = new FileReader();
    reader.onload = (e) => uploadedImageUrl.value = e.target.result;
    reader.readAsDataURL(file);
}

async function searchByFace() {
    if (!uploadedImage.value) return;

    searching.value = true;
    processingStage.value = 'searching';
    faceError.value = null;

    const formData = new FormData();
    formData.append('selfie', uploadedImage.value);

    try {
        const response = await axios.post(
            route('events.face-search.submit', props.event.slug),
            formData,
            { headers: { 'Content-Type': 'multipart/form-data' } }
        );

        results.value = response.data.results || [];
        processingStage.value = 'done';

        if (results.value.length === 0) {
            faceError.value = 'No se encontraron coincidencias exactas en este evento.';
        }
    } catch (err) {
        console.error('Error:', err);
        faceError.value = err.response?.data?.error || 'Error al procesar la búsqueda en el servidor.';
        processingStage.value = 'done';
    } finally {
        searching.value = false;
    }
}

function resetSearch() {
    uploadedImage.value = null;
    uploadedImageUrl.value = null;
    results.value = [];
    processingStage.value = '';
    faceError.value = null;
    const input = document.getElementById('face-upload-input');
    if (input) input.value = '';
}
</script>

<template>
    <Head :title="`Escáner Facial - ${event.name}`" />

    <AppLayout>
        <div class="min-h-screen bg-[#F8F9FA] text-slate-800 font-sans selection:bg-[#E30613] selection:text-white pb-24 pt-24 md:pt-28">
            <div class="max-w-[1500px] mx-auto px-4 sm:px-6 lg:px-8">

                <div class="mb-10">
                    <Link :href="route('events.show', event.slug)" class="inline-flex items-center gap-2 bg-white px-4 py-2 rounded-full text-xs font-bold uppercase tracking-wider text-gray-500 hover:text-black hover:shadow-sm border border-gray-200 transition-all mb-6">
                        <ArrowLeftIcon class="w-4 h-4" /> Volver a la galería
                    </Link>
                    <div class="flex items-center gap-3 mb-2">
                        <span class="text-xs font-bold text-gray-500 uppercase tracking-wider flex items-center gap-2">
                            <FaceSmileIcon class="w-4 h-4 text-[#E30613]" /> Escáner facial
                        </span>
                    </div>
                    <h1 class="font-flux text-5xl md:text-7xl text-black tracking-wide leading-none">
                        Búsqueda <span class="text-[#E30613]">biométrica</span>
                    </h1>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12">

                    <div class="lg:col-span-4 flex flex-col gap-6">
                        <div class="bg-white border border-gray-100 rounded p-6 md:p-8 shadow-sm sticky top-28">
                            <h3 class="text-xs font-bold uppercase tracking-widest text-gray-400 mb-6 flex items-center gap-2 border-b border-gray-100 pb-4">
                                <span class="w-4 h-px bg-gray-200"></span> Imagen de Referencia
                            </h3>

                            <div v-if="uploadedImageUrl" class="mb-6 relative rounded overflow-hidden border border-gray-200 shadow-inner group bg-gray-50 aspect-square">
                                <img :src="uploadedImageUrl" id="uploaded-preview" class="w-full h-full object-cover" />
                                <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                                    <button @click="resetSearch" class="bg-white text-black hover:bg-[#E30613] hover:text-white px-5 py-2.5 rounded-full font-bold text-xs uppercase tracking-wider flex items-center gap-2 shadow-sm transition-colors">
                                        <XMarkIcon class="w-4 h-4" /> Cambiar Foto
                                    </button>
                                </div>
                            </div>

                            <div v-else class="border-2 border-dashed border-gray-300 hover:border-black bg-gray-50 hover:bg-gray-100 rounded p-8 text-center transition-all duration-300 mb-6 group cursor-pointer relative aspect-square flex flex-col items-center justify-center">
                                <input type="file" @change="handleImageSelect" accept="image/*" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10" id="face-upload-input" />
                                <CloudArrowUpIcon class="w-12 h-12 text-gray-400 group-hover:text-black transition-colors mb-4" />
                                <h4 class="font-bold text-sm text-slate-800 mb-2 group-hover:text-black">Subí una selfie</h4>
                                <p class="text-[10px] font-bold uppercase tracking-wider text-gray-400">JPG o PNG (Máx 5MB)</p>
                            </div>

                            <button v-if="uploadedImageUrl" @click="searchByFace" :disabled="searching" class="w-full bg-black text-white font-bold text-xs uppercase tracking-wider py-4 rounded-full hover:bg-[#E30613] hover:shadow-lg hover:shadow-red-500/30 transition-all flex items-center justify-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed group">
                                <MagnifyingGlassIcon v-if="!searching" class="w-4 h-4 group-hover:-rotate-12 transition-transform" />
                                <div v-if="searching" class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin"></div>
                                <span>{{ searching ? 'Buscando rostros...' : 'Iniciar Escáner' }}</span>
                            </button>

                            <div class="mt-8 bg-blue-50 rounded p-5">
                                <p class="text-xs font-bold text-blue-800 uppercase tracking-wider mb-3 flex items-center gap-1.5"><InformationCircleIcon class="w-4 h-4" /> Consejos</p>
                                <ul class="space-y-2 text-xs font-medium text-blue-700/80">
                                    <li class="flex items-start gap-2"><span class="text-blue-500">•</span> Subí una foto de frente.</li>
                                    <li class="flex items-start gap-2"><span class="text-blue-500">•</span> Buena iluminación natural.</li>
                                    <li class="flex items-start gap-2"><span class="text-[#E30613] font-bold">×</span> Evitá lentes de sol.</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="lg:col-span-8">
                        <div v-if="faceError" class="mb-6 bg-red-50 border border-red-100 text-[#E30613] px-5 py-4 rounded text-sm font-bold flex items-center gap-3 shadow-sm">
                            <ExclamationTriangleIcon class="w-6 h-6 shrink-0" /> {{ faceError }}
                        </div>

                        <div v-if="results.length > 0" class="flex flex-col sm:flex-row sm:items-end justify-between border-b border-gray-200 pb-4 mb-8 gap-4">
                            <div>
                                <h2 class="font-flux text-3xl md:text-4xl text-black">Coincidencias <span class="text-[#E30613]">detectadas</span></h2>
                            </div>
                            <div class="bg-gray-100 text-slate-700 px-4 py-2 rounded-full text-xs font-bold uppercase tracking-wider shadow-inner w-max">
                                {{ results.length }} {{ results.length === 1 ? 'Fotografía' : 'Fotografías' }}
                            </div>
                        </div>

                        <div v-if="results.length > 0" class="columns-2 md:columns-3 xl:columns-4 gap-4 space-y-4">
                            <div v-for="(result, index) in results" :key="result.id" @click="openLightbox(index)" :class="[
                                'break-inside-avoid block group relative bg-gray-100 rounded overflow-hidden cursor-pointer shadow-sm transition-all duration-300 w-full h-auto',
                                purchasedIds.includes(result.id) ? 'ring-2 ring-blue-500 shadow-blue-200 opacity-90' :
                                    cartIds.includes(result.id) ? 'ring-2 ring-green-500 shadow-green-200' :
                                        'border border-gray-200 hover:shadow-xl hover:-translate-y-1'
                            ]">
                                <ProtectedImage :src="result.thumbnail_url" :alt="result.unique_id" class="w-full h-auto object-cover pointer-events-none" loading="lazy" />

                                <div class="absolute top-2.5 left-2.5 z-20 pointer-events-none flex flex-col gap-1.5">
                                    <span class="bg-white/90 backdrop-blur-md text-[#E30613] font-bold text-[10px] uppercase tracking-wider px-2.5 py-1 rounded-full shadow-sm w-max">
                                        Match {{ Math.round(result.similarity * 100) }}%
                                    </span>
                                    <span v-if="purchasedIds.includes(result.id)"
                                        class="bg-blue-600 text-white px-2.5 py-1 rounded-full text-[9px] font-bold uppercase tracking-wider shadow-md flex items-center gap-1 w-max">
                                        <CheckBadgeIcon class="w-3.5 h-3.5" /> Adquirida
                                    </span>
                                    <span v-else-if="cartIds.includes(result.id)"
                                        class="bg-green-500 text-white px-2.5 py-1 rounded-full text-[9px] font-bold uppercase tracking-wider shadow-md flex items-center gap-1 w-max">
                                        <CheckCircleIcon class="w-3.5 h-3.5" /> En carrito
                                    </span>
                                </div>

                                <div class="absolute inset-0 z-20 bg-gradient-to-t from-black/80 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex flex-col justify-end p-3 pointer-events-none">
                                    <div class="flex justify-between items-end w-full">
                                        <span class="text-white/80 font-bold text-[9px] uppercase tracking-wider">
                                            ID: {{ result.unique_id?.substring(0, 6) }}
                                        </span>

                                        <div class="flex flex-col items-end gap-2">
                                            <PhotoPrice v-if="result.price" :price="result.price" :event-id="event.id" variant="badge" />

                                            <button v-if="purchasedIds.includes(result.id)" title="Ya compraste esta foto"
                                                class="bg-blue-600 p-1.5 rounded-full shadow-sm flex items-center justify-center text-white cursor-default pointer-events-auto">
                                                <CheckBadgeIcon class="w-4 h-4 m-0.5" />
                                            </button>
                                            <button v-else-if="cartIds.includes(result.id)" title="Ya está en tu carrito"
                                                class="bg-green-500 p-1.5 rounded-full shadow-sm flex items-center justify-center text-white cursor-default pointer-events-auto">
                                                <CheckCircleIcon class="w-4 h-4 m-0.5" />
                                            </button>
                                            <button v-else @click.prevent.stop="addToCart(result)" title="Añadir al carrito"
                                                class="bg-white/90 backdrop-blur p-1.5 rounded-full shadow-sm flex items-center justify-center text-black hover:bg-black hover:text-white transition-colors duration-300 pointer-events-auto">
                                                <svg v-if="addingToCartIds.includes(result.id)" class="animate-spin w-4 h-4 m-0.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                                </svg>
                                                <ShoppingCartIcon v-else class="w-4 h-4 m-0.5" />
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div v-else-if="!uploadedImageUrl" class="flex flex-col items-center justify-center py-24 bg-white border border-gray-100 rounded shadow-sm h-full min-h-[400px]">
                            <div class="w-20 h-20 bg-gray-50 rounded-full flex items-center justify-center mb-6"><FaceSmileIcon class="w-10 h-10 text-gray-300" /></div>
                            <h3 class="font-flux text-3xl text-black mb-3">Esperando datos</h3>
                            <p class="text-sm font-medium text-gray-500 max-w-md text-center leading-relaxed">Subí una foto de referencia en el panel izquierdo para que nuestra IA encuentre todas las fotos en las que aparecés.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    
        <Teleport to="body">
            <Transition name="fade">
                <div v-if="isLightboxOpen" class="fixed inset-0 z-[99999] bg-slate-900/95 backdrop-blur-sm flex flex-col">

                    <div class="absolute top-0 right-0 left-0 p-4 flex justify-between items-center z-50 pointer-events-none">
                        <div class="text-white/60 text-xs font-bold uppercase px-4 pointer-events-auto">
                            Coincidencias ({{ results.length }})
                        </div>
                        <button @click="closeLightbox"
                            class="w-10 h-10 bg-white/10 hover:bg-[#E30613] text-white rounded-full flex items-center justify-center transition-colors pointer-events-auto backdrop-blur-md">
                            <XMarkIcon class="w-5 h-5" />
                        </button>
                    </div>

            
                    <div class="relative z-10 h-20 md:h-28 w-full bg-black/50 shrink-0 px-4 py-2 border-t border-white/10 order-last">
                        <swiper @swiper="setThumbsSwiper" :modules="swiperModules" :spaceBetween="10"
                            :slidesPerView="'auto'" :freeMode="true" :watchSlidesProgress="true"
                            :initialSlide="activeIndex" class="h-full thumbs-gallery">
                            <swiper-slide v-for="photo in results" :key="'thumb-' + photo.id"
                                class="!w-14 md:!w-20 h-full rounded cursor-pointer overflow-hidden opacity-40 transition-opacity hover:opacity-100">
                                <img :src="photo.thumbnail_url" loading="lazy" decoding="async" class="w-full h-full object-cover" />
                            </swiper-slide>
                        </swiper>
                    </div>

            
                    <div class="relative z-10 flex-1 w-full min-h-0 mt-12 order-1">
                        <swiper :modules="swiperModules" :virtual="{ enabled: true }" :initialSlide="activeIndex"
                            :navigation="true" :keyboard="{ enabled: true }"
                            :thumbs="{ swiper: thumbsSwiper && !thumbsSwiper.destroyed ? thumbsSwiper : null }"
                            :spaceBetween="30" class="h-full w-full">
                            <swiper-slide v-for="(photo, index) in results" :key="'main-' + photo.id"
                                :virtualIndex="index" class="flex items-center justify-center p-4">
                                <div class="relative h-full w-full max-w-full flex items-center justify-center">

                                    <ProtectedImage :src="photo.watermarked_url || photo.thumbnail_url" loading="lazy"
                                        class="max-h-full max-w-full object-contain rounded shadow-2xl relative z-10" />

                                    <div class="absolute bottom-4 left-4 right-4 p-3 md:p-5 bg-white/95 rounded flex flex-col sm:flex-row justify-between gap-3 items-center border border-white z-20">
                                        <div class="flex items-center gap-3 w-full sm:w-auto">
                                            <span class="bg-red-50 text-[#E30613] border border-red-100 px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider">
                                                Match {{ Math.round(photo.similarity * 100) }}%
                                            </span>
                                            <span class="text-gray-500 font-bold text-[10px] uppercase tracking-wider">
                                                Ref: {{ photo.unique_id }}
                                            </span>
                                        </div>

                                        <div class="flex items-center gap-4 w-full sm:w-auto justify-end">
                                            <PhotoPrice v-if="photo.price" :price="photo.price" :event-id="event.id" variant="large" />

                                            <div v-if="purchasedIds.includes(photo.id)"
                                                class="bg-blue-600 text-white px-5 py-2.5 rounded-full text-xs font-bold uppercase tracking-wider flex items-center gap-2 shadow-md cursor-default">
                                                <CheckBadgeIcon class="w-4 h-4" /> Tuya
                                            </div>
                                            <div v-else-if="cartIds.includes(photo.id)"
                                                class="bg-green-500 text-white px-5 py-2.5 rounded-full text-xs font-bold uppercase tracking-wider flex items-center gap-2 shadow-md cursor-default">
                                                <CheckCircleIcon class="w-4 h-4" /> En carrito
                                            </div>
                                            <button v-else @click.prevent.stop="addToCart(photo)"
                                                class="bg-black hover:bg-[#E30613] text-white px-5 py-2.5 rounded-full text-xs font-bold uppercase tracking-wider flex items-center gap-2 transition-colors shadow-md">
                                                <span v-if="addingToCartIds.includes(photo.id)">Añadiendo...</span>
                                                <span v-else class="flex items-center gap-2">
                                                    <ShoppingCartIcon class="w-4 h-4" /> Añadir
                                                </span>
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

:deep(.swiper-button-next),
:deep(.swiper-button-prev) {
    color: white;
    background-color: rgba(0, 0, 0, 0.5);
    width: 3rem;
    padding: 10px;
    height: 3rem;
    margin: 0 10px;
    border-radius: 50%;
}

.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.3s ease, transform 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
    transform: scale(0.98);
}

:deep(.swiper-button-next:after),
:deep(.swiper-button-prev:after) {
    font-size: 1.2rem;
    font-weight: bold;
}
</style>