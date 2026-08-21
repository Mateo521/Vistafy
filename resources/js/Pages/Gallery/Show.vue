<script setup>
import { Head, Link, usePage, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { ref, computed, watch, onUnmounted } from 'vue';
import { useToast } from '@/Composables/useToast';
import ProtectedImage from '@/Components/ProtectedImage.vue';
import {
    ArrowLeftIcon,
    ShoppingCartIcon,
    PlusIcon,
    ArrowsPointingOutIcon,
    XMarkIcon
} from '@heroicons/vue/24/outline';
import axios from 'axios';

const { success, error } = useToast();

const props = defineProps({
    photo: Object,
    relatedPhotos: Array,
});

const showFullImage = ref(false);
const addingToCart = ref(false);

const page = usePage();
const isAuthenticated = computed(() => page.props.auth?.user !== null);

const handleKeydown = (e) => {
    if (e.key === 'Escape') {
        showFullImage.value = false;
    }
};

watch(showFullImage, (fullImage) => {
    if (fullImage) {
        document.addEventListener('keydown', handleKeydown);
        document.body.style.overflow = 'hidden';
    } else {
        document.removeEventListener('keydown', handleKeydown);
        document.body.style.overflow = '';
    }
});

onUnmounted(() => {
    document.removeEventListener('keydown', handleKeydown);
    document.body.style.overflow = '';
});

const addToCart = async () => {
    if (!isAuthenticated.value) {
        window.location.href = route('login');
        return;
    }

    if (addingToCart.value) return;
    addingToCart.value = true;

    try {
        const response = await axios.post(route('cart.add', props.photo.id));

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
        addingToCart.value = false;
    }
};

const handleImageError = (e) => {
    e.target.style.display = 'none';
    const parent = e.target.parentElement;
    if (!parent.querySelector('.placeholder-img')) {
        const placeholder = document.createElement('div');
        placeholder.className = 'placeholder-img w-full h-full min-h-[300px] flex items-center justify-center bg-slate-50 rounded border border-slate-100';
        placeholder.innerHTML = `<span class="font-bold text-xs text-slate-400 uppercase tracking-widest">Sin Imagen</span>`;
        parent.appendChild(placeholder);
    }
};
</script>

<template>
    <Head :title="`Foto ${photo.unique_id} | f33.click`" />

    <AppLayout>
        <div class="min-h-screen bg-[#F8F9FA] text-slate-800 font-lato selection:bg-red-600 selection:text-white pb-20">

        
            <div class="max-w-[90rem] mx-auto px-4 md:px-8 pt-32 md:pt-40">
                
            
                <div class="mb-8">
                    <Link :href="route('gallery.index')"
                        class="inline-flex items-center gap-2 text-xs font-bold uppercase tracking-widest text-slate-400 hover:text-red-600 transition-colors">
                        <ArrowLeftIcon class="w-4 h-4" /> Volver a la galería
                    </Link>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12">

                
                    <div class="lg:col-span-8">
                        <div class="bg-white p-3 md:p-4 rounded shadow-[0_8px_30px_rgb(0,0,0,0.04)] relative group flex items-center justify-center h-full min-h-[50vh]">
                            
                        
                            <button @click="showFullImage = true" 
                                class="absolute top-8 right-8 z-20 bg-white/80 backdrop-blur-md border border-white text-slate-800 hover:text-red-600  transition-all p-3 rounded-full shadow-sm"
                                aria-label="Expandir imagen">
                                <ArrowsPointingOutIcon class="w-5 h-5" />
                            </button>

                        
                            <ProtectedImage :src="photo.watermarked_url || photo.thumbnail_url" :alt="photo.title"
                                class="w-full max-h-[75vh] object-contain rounded cursor-zoom-in transition-transform duration-500 "
                                @click="showFullImage = true"
                                @error="handleImageError" />
                            
                        
                            
                        </div>
                    </div>

                
                    <div class="lg:col-span-4 flex flex-col">

                        <div class="bg-white rounded p-8 md:p-10 shadow-[0_8px_30px_rgb(0,0,0,0.04)] h-full flex flex-col">
                            
                        
                            <div class="mb-6">
                                <span class="text-[10px] font-bold uppercase text-red-600 bg-red-50 px-4 py-2 rounded-full tracking-widest">
                                    Ref: {{ photo.unique_id }}
                                </span>
                            </div>
                            
                    
                            <h1 class="font-flux text-5xl md:text-6xl text-black mb-8 leading-none">
                                {{ photo.title || 'Foto deportiva' }}
                            </h1>

                        
                            <div class="flex flex-col gap-4 mb-10">
                                <div class="flex justify-between items-center pb-4 border-b border-slate-100">
                                    <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Resolución Original</span>
                                    <span class="text-sm font-bold text-slate-800">{{ photo.width }} x {{ photo.height }} px</span>
                                </div>
                                <div v-if="photo.event" class="flex justify-between items-center pb-4 border-b border-slate-100">
                                    <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Evento</span>
                                    <span class="text-sm font-bold text-red-600 truncate max-w-[150px] text-right">
                                        <Link :href="route('events.show', photo.event.slug || photo.event.id)" class="hover:underline">
                                            {{ photo.event.name }}
                                        </Link>
                                    </span>
                                </div>
                            </div>

                        
                            <div class="mb-8 flex items-end justify-between">
                                <span class="text-xs font-bold uppercase text-slate-400 tracking-wider mb-1">Licencia Digital</span>
                                <span class="font-flux text-6xl text-black leading-none">${{ photo.price }}</span>
                            </div>

                        
                            <button @click="addToCart" :disabled="addingToCart"
                                class="w-full bg-gradient-to-r from-red-600 to-red-500 text-white hover:shadow-[0_8px_25px_rgb(230,0,0,0.3)] hover:-translate-y-1 text-sm font-bold uppercase tracking-widest py-4 rounded-full transition-all duration-300 flex items-center justify-center gap-3 disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:translate-y-0 disabled:hover:shadow-none mt-auto">
                                <PlusIcon v-if="!addingToCart" class="w-5 h-5" stroke-width="2.5" />
                                <span v-if="addingToCart">Procesando...</span>
                                <span v-else>Agregar al carrito</span>
                            </button>

                        
                        </div>
                    </div>
                </div>


                <div class="mt-8 bg-white rounded p-6 md:p-8 shadow-[0_8px_30px_rgb(0,0,0,0.04)] flex flex-col sm:flex-row items-center justify-between gap-6">
                    <div class="flex items-center gap-6">
                        <div class="w-16 h-16 rounded-full bg-slate-100 flex-shrink-0 flex items-center justify-center overflow-hidden border-2 border-white shadow-md">
                            <ProtectedImage v-if="photo.photographer?.profile_photo_url"
                                :src="photo.photographer.profile_photo_url"
                                class="w-full h-full object-cover" />
                            <div v-else class="text-slate-400 font-flux text-3xl mt-1">
                                {{ photo.photographer?.business_name?.charAt(0) || 'F' }}
                            </div>
                        </div>
                        <div>
                            <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-1">Fotografía de </p>
                            <p class="text-xl font-flux text-black leading-none">{{ photo.photographer?.business_name || 'Fotógrafo F33' }}</p>
                        </div>
                    </div>
                    
                    <Link v-if="photo.photographer?.slug"
                        :href="route('photographers.show', photo.photographer.slug)"
                        class="px-8 py-3 rounded-full border border-slate-200 text-sm font-bold text-slate-600 hover:text-black hover:border-black transition-colors w-full sm:w-auto text-center">
                        Ver portafolio
                    </Link>
                </div>

                <div v-if="relatedPhotos && relatedPhotos.length > 0" class="mt-24 pt-12 border-t border-slate-200">
                    <div class="text-center md:text-left mb-12">
                        <h2 class="font-flux text-5xl md:text-6xl text-black">
                            fotos <span class="text-slate-300 font-sans font-light">/</span> relacionadas
                        </h2>
                    </div>
                    

                    <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-5 gap-4">
                        <Link v-for="related in relatedPhotos" :key="related.id"
                            :href="route('gallery.show', related.unique_id)" 
                            class="group relative rounded overflow-hidden aspect-[4/5] cursor-pointer shadow-sm hover:shadow-xl transition-all duration-300 bg-slate-100">
                            

                            <ProtectedImage :src="related.thumbnail_url"
                                class="w-full h-full object-cover transition-transform duration-700  pointer-events-none"
                                @error="handleImageError" />
                            

                            <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-end p-4">
                                <div class="w-full flex justify-between items-end">
                                    <span class="text-white font-mono text-[10px] tracking-widest">{{ related.unique_id }}</span>
                                    <span class="bg-red-600 text-white font-bold text-xs px-2 py-1 rounded-lg">${{ related.price }}</span>
                                </div>
                            </div>
                        </Link>
                    </div>
                </div>
            </div>
        </div>


        <Transition enter-active-class="transition-opacity duration-300 ease-out" enter-from-class="opacity-0"
            enter-to-class="opacity-100" leave-active-class="transition-opacity duration-200 ease-in"
            leave-from-class="opacity-100" leave-to-class="opacity-0">
            
            <div v-if="showFullImage" class="fixed inset-0 z-[100] bg-black/95 backdrop-blur-sm cursor-zoom-out flex flex-col"
                @click="showFullImage = false">
                
                <button @click.stop="showFullImage = false" 
                    class="absolute top-6 right-6 md:top-8 md:right-8 z-[150] bg-white/10 hover:bg-red-600 text-white rounded-full p-4 transition-colors cursor-pointer border border-white/20 hover:border-transparent">
                    <XMarkIcon class="w-6 h-6" />
                </button>

                <div class="flex-1 flex items-center justify-center p-4 pt-20 pb-16 md:p-16 pointer-events-none z-[110]">

                    <ProtectedImage :src="photo.watermarked_url || photo.thumbnail_url" :alt="photo.title"
                        class="w-auto h-auto object-contain rounded shadow-2xl pointer-events-auto cursor-default" 
                        style="max-width: 100%; max-height: 100%;"
                        @click.stop />
                </div>
                
            </div>
        </Transition>

    </AppLayout>
</template>

<style scoped>


::-webkit-scrollbar {
    width: 8px;
}
::-webkit-scrollbar-track {
    background: #f8f9fa;
}
::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 10px;
}
::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
}
</style>