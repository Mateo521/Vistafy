<script setup>
import { Head, Link, usePage, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { ref, computed, watch, onUnmounted } from 'vue';
import { useToast } from '@/Composables/useToast';
import ProtectedImage from '@/Components/ProtectedImage.vue';
import {
    ArrowLeftIcon,
    ShoppingCartIcon,
    PlusIcon
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
    if (addingToCart.value) return;
    addingToCart.value = true;

    try {
        const response = await axios.post(route('cart.add', props.photo.id));

        if (response.data.success) {
            success('FOTOGRAFÍA AGREGADA AL CARRITO');
            window.dispatchEvent(new Event('cart-updated'));
        } else {
            error('FOTOGRAFÍA YA EXISTENTE EN EL CARRITO');
        }
    } catch (err) {
        console.error('Error agregando al carrito:', err);
        if (err.response) {
            error('ERROR AL AGREGAR AL CARRITO');
        } else {
            error('ERROR DE CONEXIÓN DEL NODO');
        }
    } finally {
        addingToCart.value = false;
    }
};

const handleImageError = (e) => {
    e.target.style.display = 'none';
    const parent = e.target.parentElement;
    if (!parent.querySelector('.placeholder-img')) {
        const placeholder = document.createElement('div');
        placeholder.className = 'placeholder-img w-full h-full min-h-[300px] flex items-center justify-center bg-gray-900 border border-red-600/30';
        placeholder.innerHTML = `<span class="font-mono text-xs text-red-600 uppercase tracking-widest">[ ERROR DE LECTURA ]</span>`;
        parent.appendChild(placeholder);
    }
};
</script>

<template>
    <Head :title="`ID_${photo.unique_id} — F33`" />

    <AppLayout>
        <div class="min-h-screen bg-black text-white font-sans selection:bg-red-600 selection:text-white">

            <div class="border-b border-white/20 bg-black/90 backdrop-blur-sm sticky top-0 z-30 pt-16 md:pt-0">
                <div class="max-w-[1500px] mx-auto px-4 md:px-8 h-14 flex items-center justify-between font-mono text-[10px] uppercase tracking-widest">
                    <Link :href="route('gallery.index')"
                        class="text-gray-400 hover:text-white flex items-center gap-3 transition-none border border-transparent hover:border-white px-3 py-1">
                        <ArrowLeftIcon class="w-3.5 h-3.5" /> [ CATÁLOGO ]
                    </Link>

                    <Link :href="route('cart.index')"
                        class="text-white hover:text-black hover:bg-white transition-none flex items-center gap-2 border border-white px-3 py-1">
                        <ShoppingCartIcon class="w-4 h-4" />
                        <span>CARRITO DE COMPRAS</span>
                    </Link>
                </div>
            </div>

            <div class="max-w-[1500px] mx-auto px-4 md:px-8 py-12 md:py-20">
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-16">

                    <div class="lg:col-span-8">
                        <div class="bg-gray-950 border-[6px] border-black hover:border-red-600 p-2 flex items-center justify-center relative group  transition-none"
                            @click="showFullImage = true">
                            
                            <div class="absolute top-4 left-4 z-20 bg-red-600 text-black font-mono text-[9px] font-bold px-2 py-1 tracking-widest uppercase opacity-0 group-hover:opacity-100 transition-none pointer-events-none">
                                CLICK PARA EXPANDIR
                            </div>

                            <ProtectedImage :src="photo.watermarked_url || photo.thumbnail_url" :alt="photo.title"
                                class="w-full max-h-[80vh] object-contain grayscale-[0.3] group-hover:grayscale-0 transition-none cursor-zoom-in"
                                @error="handleImageError" />
                            
                            <div class="absolute bottom-4 right-4 bg-black border border-white text-white text-[9px] font-mono font-bold uppercase tracking-widest px-3 py-1.5 opacity-0 group-hover:opacity-100 transition-none pointer-events-none">
                                MUESTRA CON PROTECCIÓN
                            </div>
                        </div>
                    </div>

                    <div class="lg:col-span-4 flex flex-col font-mono">

                        <div class="mb-8">
                            <div class="flex items-center justify-between border-b border-white/20 pb-4 mb-6">
                                <span class="text-[10px] font-bold uppercase  text-red-600 bg-red-600/10 px-2 py-1 border border-red-600">ID_REF: {{ photo.unique_id }}</span>
                            </div>
                            
                            <h1 class="font-black font-sans text-5xl md:text-6xl text-white mb-6 leading-[0.85] tracking-tighter uppercase">
                                {{ photo.title || 'Fotografía' }}
                            </h1>

                            <div class="flex flex-col gap-2 mb-8 text-[10px] tracking-widest text-gray-400 uppercase">
                                <div class="flex justify-between border-b border-white/10 pb-2">
                                    <span>RESOLUCIÓN NATIVA:</span>
                                    <span class="text-white">{{ photo.width }} x {{ photo.height }} PX</span>
                                </div>
                                <div v-if="photo.event" class="flex justify-between border-b border-white/10 pb-2">
                                    <span>EVENTO / LOCACIÓN:</span>
                                    <span class="text-red-600 truncate ml-4">{{ photo.event.name }}</span>
                                </div>
                            </div>

                            <div class="border-t-[4px] border-white pt-6 mb-8 flex items-end justify-between">
                                <span class="text-[10px] font-bold uppercase  text-gray-500">VALOR DE LICENCIA</span>
                                <span class="text-5xl font-black font-sans text-white leading-none tracking-tighter">${{ photo.price }}</span>
                            </div>

                            <button @click="addToCart" :disabled="addingToCart"
                                class="w-full bg-red-600 hover:bg-white text-black text-[12px] font-black uppercase tracking-[0.25em] py-5 border-[4px] border-red-600 hover:border-white transition-none flex items-center justify-center gap-3 disabled:opacity-30 disabled:cursor-not-allowed group">
                                <PlusIcon v-if="!addingToCart" class="w-5 h-5" />
                                <span v-if="addingToCart">Procesando...</span>
                                <span v-else>AGREGAR AL CARRITO</span>
                            </button>

                            <p class="text-[9px] text-gray-600 mt-4 leading-relaxed tracking-widest uppercase font-bold text-center">
                                TRAYECTO DE DESCARGA SEGURO
                            </p>
                        </div>

                        <div class="border border-white/20 p-6 mt-auto bg-gray-950 hover:border-white transition-none">
                            <h3 class="text-[9px] font-bold uppercase  text-gray-500 mb-4 border-b border-white/10 pb-2">
                                FOTÓGRAFO / CREADOR
                            </h3>
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 bg-black border border-red-600 flex-shrink-0 flex items-center justify-center">
                                    <ProtectedImage v-if="photo.photographer?.profile_photo_url"
                                        :src="photo.photographer.profile_photo_url"
                                        class="w-full h-full object-cover grayscale" />
                                    <div v-else class="text-red-600 font-black text-xl font-sans">
                                        {{ photo.photographer?.business_name?.charAt(0) || 'F' }}
                                    </div>
                                </div>
                                <div>
                                    <p class="text-xs font-bold text-white mb-1 uppercase tracking-widest">{{ photo.photographer?.business_name || 'FOTÓGRAFO F33' }}</p>
                                    <Link v-if="photo.photographer?.slug"
                                        :href="route('photographers.show', photo.photographer.slug)"
                                        class="text-[9px] uppercase  font-bold text-red-600 hover:text-white border-b border-red-600 hover:border-white transition-none">
                                        [ INSPECCIONAR CATÁLOGO ]
                                    </Link>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                <div v-if="relatedPhotos && relatedPhotos.length > 0" class="mt-24 md:mt-32 pt-16 border-t border-white/20">
                    <h2 class="text-4xl md:text-6xl font-black font-sans text-white uppercase tracking-tighter mb-10">
                        ARCHIVOS <span class="text-red-600">RELACIONADOS</span>
                    </h2>
                    
                    <div class="columns-2 md:columns-4 lg:columns-5 gap-2 space-y-2 masonry-grid">
                        <Link v-for="related in relatedPhotos" :key="related.id"
                            :href="route('gallery.show', related.unique_id)" 
                            class="break-inside-avoid block group relative bg-gray-950 overflow-hidden border-[4px] border-black hover:border-red-600 transition-none ">
                            
                            <div class="relative w-full h-auto">
                                <ProtectedImage :src="related.thumbnail_url"
                                    class="w-full h-auto object-cover grayscale opacity-70 group-hover:grayscale-0 group-hover:opacity-100 transition-none pointer-events-none"
                                    @error="handleImageError" />
                                
                                <div class="absolute top-2 left-2 bg-black border border-white px-2 py-1 text-[9px] font-mono text-white tracking-widest pointer-events-none">
                                    ${{ related.price }}
                                </div>
                                
                                <div class="absolute bottom-2 right-2 bg-red-600 text-black px-1.5 py-0.5 text-[8px] font-mono font-bold tracking-widest opacity-0 group-hover:opacity-100 transition-none pointer-events-none">
                                    {{ related.unique_id }}
                                </div>
                            </div>
                        </Link>
                    </div>
                </div>
            </div>
        </div>

        <Transition enter-active-class="transition-none" enter-from-class="opacity-0"
            enter-to-class="opacity-100" leave-active-class="transition-none"
            leave-from-class="opacity-100" leave-to-class="opacity-0">
            
            <div v-if="showFullImage" class="fixed inset-0 z-[100] bg-black/95 backdrop-blur-md cursor-zoom-out"
                @click="showFullImage = false">
                
                <button @click.stop="showFullImage = false" 
                    class="absolute top-6 right-6 md:top-8 md:right-8 z-[150] bg-black text-white border-2 border-white hover:bg-white hover:text-black font-mono font-bold text-[10px] md:text-xs uppercase tracking-widest px-4 py-2 transition-none cursor-pointer shadow-2xl">
                    [ CERRAR VISOR ]
                </button>

                <div class="absolute inset-0 flex items-center justify-center p-8 pt-24 pb-20 md:p-20 pointer-events-none z-[110]">
                    <ProtectedImage :src="photo.watermarked_url || photo.thumbnail_url" :alt="photo.title"
                        class="w-auto h-auto object-contain border-[4px] border-white shadow-[0_0_50px_rgba(0,0,0,0.8)] pointer-events-auto cursor-default" 
                        style="max-width: 100%; max-height: 100%;"
                        @click.stop />
                </div>

                <div class="absolute bottom-6 left-0 w-full text-center font-mono text-[10px] text-gray-500 uppercase pointer-events-none z-[120]">
                    REVISIÓN F33 // ID: {{ photo.unique_id }}
                </div>
                
            </div>
        </Transition>

    </AppLayout>
</template>

<style scoped>
.masonry-grid {
    column-fill: balance;
}

::-webkit-scrollbar {
    width: 8px;
}
::-webkit-scrollbar-track {
    background: #000000;
    border-left: 1px solid #333;
}
::-webkit-scrollbar-thumb {
    background: #ffffff;
    border-radius: 0;
}
::-webkit-scrollbar-thumb:hover {
    background: #dc2626;
}
</style>