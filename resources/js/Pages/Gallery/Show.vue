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
    ArrowsPointingOutIcon
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


            <div class="border-b-4 border-black bg-white sticky top-0 z-30 pt-16 md:pt-0">
                <div
                    class="max-w-[1500px] mx-auto px-4 md:px-8 h-14 flex items-center justify-between font-mono text-[10px] font-bold uppercase tracking-widest">
                    <Link :href="route('gallery.index')"
                        class="text-gray-600 hover:text-[#E30613] flex items-center gap-2 transition-colors py-1">
                        <ArrowLeftIcon class="w-4 h-4" /> VOLVER AL CATÁLOGO
                    </Link>

                    <Link :href="route('cart.index')"
                        class="bg-black text-white hover:bg-[#E30613] transition-colors flex items-center gap-2 border-2 border-black px-4 py-1.5">
                        <ShoppingCartIcon class="w-4 h-4" />
                        <span>CARRITO</span>
                    </Link>
                </div>
            </div>


            <div class="max-w-[1500px] mx-auto px-4 md:px-8 py-10 md:py-16">
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-10">


                    <div class="lg:col-span-8">
                        <div class="bg-white border-4 border-black p-3 relative group">


                            <button @click="showFullImage = true"
                                class="absolute top-6 right-6 z-20 bg-white border-2 border-black text-black hover:bg-[#E30613] hover:text-white transition-colors p-2 flex items-center gap-2 font-mono text-[10px] font-bold uppercase"
                                aria-label="Expandir imagen">
                                <ArrowsPointingOutIcon class="w-5 h-5" />
                                <span class="hidden sm:inline">Expandir</span>
                            </button>


                            <ProtectedImage :src="photo.watermarked_url || photo.thumbnail_url" :alt="photo.title"
                                class="w-full max-h-[75vh] object-contain cursor-zoom-in" @click="showFullImage = true"
                                @error="handleImageError" />


                            
                        </div>
                    </div>


                    <div class="lg:col-span-4 flex flex-col font-mono">

                        <div class="mb-8">

                            <div class="mb-4">
                                <span
                                    class="text-[11px] font-bold uppercase text-black bg-white px-3 py-1 border-2 border-black">
                                    ID: <span class="text-[#E30613]">{{ photo.unique_id }}</span>
                                </span>
                            </div>


                            <h1
                                class="font-black font-sans text-4xl md:text-5xl text-black mb-6 leading-none tracking-tight uppercase">
                                {{ photo.title || 'Fotografía' }}
                            </h1>


                            <div
                                class="flex flex-col gap-3 mb-8 text-[11px] font-bold tracking-widest text-gray-700 uppercase">
                                <div class="flex justify-between border-b-2 border-black/10 pb-2">
                                    <span>Resolución Original:</span>
                                    <span class="text-black">{{ photo.width }} x {{ photo.height }} PX</span>
                                </div>
                                <div v-if="photo.event" class="flex justify-between border-b-2 border-black/10 pb-2">
                                    <span>Evento:</span>
                                    <span class="text-[#E30613] truncate ml-4">{{ photo.event.name }}</span>
                                </div>
                            </div>


                            <div class="border-t-4 border-black pt-6 mb-8 flex items-end justify-between">
                                <span class="text-[12px] font-bold uppercase text-gray-600">Valor de Licencia</span>
                                <span class="text-5xl font-black font-sans text-black leading-none tracking-tighter">${{
                                    photo.price }}</span>
                            </div>


                            <button @click="addToCart" :disabled="addingToCart"
                                class="w-full bg-[#E30613] text-white hover:bg-black hover:text-white text-[13px] font-black uppercase tracking-widest py-5 border-4 border-black transition-colors flex items-center justify-center gap-3 disabled:opacity-50 disabled:cursor-not-allowed">
                                <PlusIcon v-if="!addingToCart" class="w-6 h-6" stroke-width="3" />
                                <span v-if="addingToCart">Procesando...</span>
                                <span v-else>Agregar al carrito</span>
                            </button>

                        
                        </div>


                        <div class="border-4 border-black p-5 mt-auto bg-white">
                            <h3
                                class="text-[11px] font-bold uppercase text-gray-600 mb-4 border-b-2 border-black/10 pb-2">
                                Fotógrafo
                            </h3>
                            <div class="flex items-center gap-4">
                                <div
                                    class="w-12 h-12 bg-[#F2F0EB] border-2 border-black flex-shrink-0 flex items-center justify-center overflow-hidden">
                                    <ProtectedImage v-if="photo.photographer?.profile_photo_url"
                                        :src="photo.photographer.profile_photo_url"
                                        class="w-full h-full object-cover" />
                                    <div v-else class="text-[#E30613] font-black text-xl font-sans">
                                        {{ photo.photographer?.business_name?.charAt(0) || 'F' }}
                                    </div>
                                </div>
                                <div class="flex-1">
                                    <p class="text-xs font-black text-black mb-1 uppercase tracking-widest truncate">{{
                                        photo.photographer?.business_name || 'Fotógrafo F33' }}</p>
                                    <Link v-if="photo.photographer?.slug"
                                        :href="route('photographers.show', photo.photographer.slug)"
                                        class="text-[10px] uppercase font-bold text-[#E30613] hover:text-black underline transition-colors">
                                        Ver Catálogo
                                    </Link>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>


                <div v-if="relatedPhotos && relatedPhotos.length > 0" class="mt-20 border-t-8 border-black pt-16">
                    <h2 class="text-3xl md:text-5xl font-black font-sans text-black uppercase tracking-tighter mb-10">
                        Archivos <span class="text-[#E30613]">Relacionados</span>
                    </h2>

                    <div class="columns-2 md:columns-4 lg:columns-5 gap-4 space-y-4 masonry-grid">
                        <Link v-for="related in relatedPhotos" :key="related.id"
                            :href="route('gallery.show', related.unique_id)"
                            class="break-inside-avoid flex flex-col bg-white border-4 border-black hover:border-[#E30613] transition-colors group">


                            <div class="relative w-full overflow-hidden border-b-4 border-black">
                                <ProtectedImage :src="related.thumbnail_url"
                                    class="w-full h-auto object-cover opacity-90 group-hover:opacity-100 transition-opacity"
                                    @error="handleImageError" />
                            </div>


                            <div class="p-3 bg-white flex justify-between items-center font-mono font-bold">
                                <span class="text-[10px] text-gray-500 uppercase tracking-widest">{{ related.unique_id
                                    }}</span>
                                <span class="text-xs text-black">${{ related.price }}</span>
                            </div>
                        </Link>
                    </div>
                </div>
            </div>
        </div>


        <Transition enter-active-class="transition-opacity duration-200" enter-from-class="opacity-0"
            enter-to-class="opacity-100" leave-active-class="transition-opacity duration-200"
            leave-from-class="opacity-100" leave-to-class="opacity-0">

            <div v-if="showFullImage" class="fixed inset-0 z-[100] bg-white/95 cursor-zoom-out flex flex-col"
                @click="showFullImage = false">

                <button @click.stop="showFullImage = false"
                    class="absolute top-4 right-4 md:top-8 md:right-8 z-[150] bg-black text-white hover:bg-[#E30613] font-mono font-bold text-[11px] uppercase tracking-widest px-6 py-3 transition-colors cursor-pointer border-4 border-black">
                    Cerrar
                </button>

                <div
                    class="flex-1 flex items-center justify-center p-4 pt-20 pb-16 md:p-16 pointer-events-none z-[110]">

                    <ProtectedImage :src="photo.watermarked_url || photo.thumbnail_url" :alt="photo.title"
                        class="w-auto h-auto object-contain border-[8px] border-black bg-white pointer-events-auto cursor-default"
                        style="max-width: 100%; max-height: 100%;" @click.stop />
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
}

::-webkit-scrollbar-thumb:hover {
    background: #E30613;
}
</style>