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
    if (!isAuthenticated.value) {
        window.location.href = route('login');
        return;
    }

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
        error('ERROR DE CONEXIÓN');
    } finally {
        addingToCart.value = false;
    }
};

const handleImageError = (e) => {
    e.target.style.display = 'none';
    const parent = e.target.parentElement;
    if (!parent.querySelector('.placeholder-img')) {
        const placeholder = document.createElement('div');
    
        placeholder.className = 'placeholder-img w-full h-full min-h-[300px] flex items-center justify-center bg-gray-100 border-4 border-black';
        placeholder.innerHTML = `<span class="font-mono text-xs text-[#E30613] font-bold uppercase tracking-widest">[ ERROR DE LECTURA ]</span>`;
        parent.appendChild(placeholder);
    }
};
</script>

<template>
    <Head :title="`ID_${photo.unique_id} — F33`" />

    <AppLayout>
        <div class="min-h-screen bg-[#F2F0EB] text-[#050505] font-sans selection:bg-[#E30613] selection:text-white">

        
            <div class="border-b-4 border-black bg-[#F2F0EB]/95 backdrop-blur-sm sticky top-0 z-30 pt-16 md:pt-0">
                <div class="max-w-[1500px] mx-auto px-4 md:px-8 h-14 flex items-center justify-between font-mono text-[10px] font-bold uppercase tracking-widest">
                    <Link :href="route('gallery.index')"
                        class="text-gray-500 hover:text-black flex items-center gap-3 transition-colors border-b-2 border-transparent hover:border-black px-2 py-1">
                        <ArrowLeftIcon class="w-3.5 h-3.5" /> [ CATÁLOGO ]
                    </Link>

                    <Link :href="route('cart.index')"
                        class="bg-black text-white hover:bg-[#E30613] transition-colors flex items-center gap-2 border-2 border-black px-4 py-1.5 shadow-[2px_2px_0px_rgba(0,0,0,1)] hover:shadow-none hover:translate-x-[2px] hover:translate-y-[2px]">
                        <ShoppingCartIcon class="w-4 h-4" />
                        <span>CARRITO DE COMPRAS</span>
                    </Link>
                </div>
            </div>

        
            <div class="max-w-[1500px] mx-auto px-4 md:px-8 py-12 md:py-20">
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-16">

            
                    <div class="lg:col-span-8">
                        <div class="bg-white border-4 border-black p-2 flex items-center justify-center relative group shadow-[8px_8px_0px_rgba(0,0,0,1)] hover:translate-x-[4px] hover:translate-y-[4px] hover:shadow-[4px_4px_0px_rgba(0,0,0,1)] transition-all cursor-zoom-in"
                            @click="showFullImage = true">
                            
                        
                            <div class="absolute top-4 left-4 z-20 bg-[#E30613] text-white border-2 border-black font-mono text-[9px] font-bold px-3 py-1 tracking-widest uppercase opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none">
                                CLICK PARA EXPANDIR
                            </div>

                        
                            <ProtectedImage :src="photo.watermarked_url || photo.thumbnail_url" :alt="photo.title"
                                class="w-full max-h-[80vh] object-contain"
                                @error="handleImageError" />
                            
                        
                            <div class="absolute bottom-4 right-4 bg-white border-2 border-black text-black text-[9px] font-mono font-bold uppercase tracking-widest px-3 py-1.5 opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none">
                                MUESTRA CON PROTECCIÓN
                            </div>
                        </div>
                    </div>

                
                    <div class="lg:col-span-4 flex flex-col font-mono">

                        <div class="mb-8">
                        
                            <div class="flex items-center justify-between border-b-2 border-black/10 pb-4 mb-6">
                                <span class="text-[10px] font-bold uppercase text-black bg-white px-3 py-1 border-2 border-black shadow-[2px_2px_0px_rgba(0,0,0,1)]">
                                    ID_REF: <span class="text-[#E30613]">{{ photo.unique_id }}</span>
                                </span>
                            </div>
                            
                        
                            <h1 class="font-black font-sans text-5xl md:text-6xl text-black mb-6 leading-[0.85] tracking-tighter uppercase">
                                {{ photo.title || 'Fotografía' }}
                            </h1>

                        
                            <div class="flex flex-col gap-2 mb-8 text-[10px] font-bold tracking-widest text-gray-500 uppercase">
                                <div class="flex justify-between border-b border-black/10 pb-2">
                                    <span>RESOLUCIÓN NATIVA:</span>
                                    <span class="text-black">{{ photo.width }} x {{ photo.height }} PX</span>
                                </div>
                                <div v-if="photo.event" class="flex justify-between border-b border-black/10 pb-2 mt-2">
                                    <span>EVENTO / LOCACIÓN:</span>
                                    <span class="text-[#E30613] truncate ml-4">{{ photo.event.name }}</span>
                                </div>
                            </div>

                        
                            <div class="border-t-[4px] border-black pt-6 mb-8 flex items-end justify-between">
                                <span class="text-[10px] font-bold uppercase text-gray-500">VALOR DE LICENCIA</span>
                                <span class="text-5xl font-black font-sans text-[#E30613] leading-none tracking-tighter">${{ photo.price }}</span>
                            </div>

                        
                            <button @click="addToCart" :disabled="addingToCart"
                                class="w-full bg-[#E30613] text-white text-[12px] font-black uppercase tracking-[0.25em] py-5 border-4 border-black shadow-[6px_6px_0px_rgba(0,0,0,1)] hover:shadow-none hover:translate-x-[6px] hover:translate-y-[6px] hover:bg-black transition-all flex items-center justify-center gap-3 disabled:opacity-50 disabled:cursor-not-allowed group">
                                <PlusIcon v-if="!addingToCart" class="w-5 h-5" stroke-width="3" />
                                <span v-if="addingToCart">Procesando...</span>
                                <span v-else>AGREGAR AL CARRITO</span>
                            </button>

                            <p class="text-[9px] text-gray-500 mt-4 leading-relaxed tracking-widest uppercase font-bold text-center">
                                DESCARGA SEGURA SIN MARCA DE AGUA
                            </p>
                        </div>

                    
                        <div class="border-4 border-black p-6 mt-auto bg-white shadow-[4px_4px_0px_rgba(0,0,0,1)]">
                            <h3 class="text-[10px] font-bold uppercase text-gray-500 mb-4 border-b-2 border-black/10 pb-2">
                                FOTÓGRAFO / CREADOR
                            </h3>
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 bg-[#F2F0EB] border-2 border-black flex-shrink-0 flex items-center justify-center overflow-hidden">
                                    <ProtectedImage v-if="photo.photographer?.profile_photo_url"
                                        :src="photo.photographer.profile_photo_url"
                                        class="w-full h-full object-cover grayscale" />
                                    <div v-else class="text-[#E30613] font-black text-xl font-sans">
                                        {{ photo.photographer?.business_name?.charAt(0) || 'F' }}
                                    </div>
                                </div>
                                <div>
                                    <p class="text-xs font-black text-black mb-1 uppercase tracking-widest">{{ photo.photographer?.business_name || 'FOTÓGRAFO F33' }}</p>
                                    <Link v-if="photo.photographer?.slug"
                                        :href="route('photographers.show', photo.photographer.slug)"
                                        class="text-[9px] uppercase font-bold text-[#E30613] hover:text-black border-b-2 border-[#E30613] hover:border-black transition-colors pb-0.5">
                                        [ INSPECCIONAR CATÁLOGO ]
                                    </Link>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

              
                <div v-if="relatedPhotos && relatedPhotos.length > 0" class="mt-24 md:mt-32 pt-16 border-t-8 border-black">
                    <h2 class="text-4xl md:text-6xl font-black font-sans text-black uppercase tracking-tighter mb-10">
                        ARCHIVOS <span class="text-[#E30613]">RELACIONADOS</span>
                    </h2>
                    
                    <div class="columns-2 md:columns-4 lg:columns-5 gap-4 space-y-4 masonry-grid">
                        <Link v-for="related in relatedPhotos" :key="related.id"
                            :href="route('gallery.show', related.unique_id)" 
                            class="break-inside-avoid block group relative bg-white overflow-hidden border-4 border-black shadow-[4px_4px_0px_rgba(0,0,0,1)] hover:translate-x-[4px] hover:translate-y-[4px] hover:shadow-none transition-all">
                            
                            <div class="relative w-full h-auto">
                                <ProtectedImage :src="related.thumbnail_url"
                                    class="w-full h-auto object-cover pointer-events-none"
                                    @error="handleImageError" />
                                
                                <div class="absolute top-2 left-2 bg-white border-2 border-black px-2 py-1 text-[9px] font-mono font-bold text-black tracking-widest pointer-events-none">
                                    ${{ related.price }}
                                </div>
                                
                                <div class="absolute bottom-2 right-2 bg-black text-white px-2 py-1 text-[8px] font-mono font-bold tracking-widest opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none">
                                    {{ related.unique_id }}
                                </div>
                            </div>
                        </Link>
                    </div>
                </div>
            </div>
        </div>

       
        <Transition enter-active-class="transition-opacity duration-200" enter-from-class="opacity-0"
            enter-to-class="opacity-100" leave-active-class="transition-opacity duration-200"
            leave-from-class="opacity-100" leave-to-class="opacity-0">
            
            <div v-if="showFullImage" class="fixed inset-0 z-[100] bg-[#F2F0EB]/95 backdrop-blur-md cursor-zoom-out flex flex-col"
                @click="showFullImage = false">
                
                <button @click.stop="showFullImage = false" 
                    class="absolute top-6 right-6 md:top-8 md:right-8 z-[150] bg-[#E30613] text-white border-4 border-black hover:bg-black font-mono font-bold text-[10px] md:text-xs uppercase tracking-widest px-4 py-2 transition-colors cursor-pointer shadow-[4px_4px_0px_rgba(0,0,0,1)]">
                    [ CERRAR VISOR ]
                </button>

                <div class="flex-1 flex items-center justify-center p-8 pt-24 pb-20 md:p-20 pointer-events-none z-[110]">
                   
                    <ProtectedImage :src="photo.watermarked_url || photo.thumbnail_url" :alt="photo.title"
                        class="w-auto h-auto object-contain border-[8px] border-black bg-white shadow-[12px_12px_0px_rgba(0,0,0,1)] pointer-events-auto cursor-default" 
                        style="max-width: 100%; max-height: 100%;"
                        @click.stop />
                </div>

                <div class="absolute bottom-6 left-0 w-full text-center font-mono font-bold text-[10px] text-gray-500 uppercase pointer-events-none z-[120]">
                    REVISIÓN F33 // ID: <span class="text-black">{{ photo.unique_id }}</span>
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
    width: 12px;
}
::-webkit-scrollbar-track {
    background: #F2F0EB;
    border-left: 2px solid #050505;
}
::-webkit-scrollbar-thumb {
    background: #050505;
    border-radius: 0;
}
::-webkit-scrollbar-thumb:hover {
    background: #E30613;
}
</style>