<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { ref, computed } from 'vue';
import {
    ArrowLeftIcon,
    XMarkIcon,
    TrashIcon,
    ShoppingBagIcon,
    ShieldCheckIcon
} from '@heroicons/vue/24/outline';
import axios from 'axios';
import { useToast } from '@/Composables/useToast';  

const props = defineProps({
    items: Array,
    total: Number,
});

const { success, error } = useToast(); 

const processing = ref(false);
const removingItems = ref(new Set());

const itemCount = computed(() => props.items?.length || 0);

const formattedTotal = computed(() => {
    return parseFloat(props.total || 0).toFixed(2);
});

const formatPrice = (amount) => {
    return parseFloat(amount || 0).toFixed(2);
};

const removeItem = async (photoId) => {
    if (removingItems.value.has(photoId)) return;

    removingItems.value.add(photoId);

    try {
        await axios.delete(route('cart.remove', photoId));
        success('DATO ELIMINADO DE LA COLA');  
        router.reload({ only: ['items', 'total'] });
    } catch (err) {
        console.error('Error eliminando item:', err);
        error('ERROR AL PURGAR DATO');  
    } finally {
        removingItems.value.delete(photoId);
    }
};

const clearCart = async () => {
    if (!confirm('¿CONFIRMAR PURGA TOTAL DE MEMORIA?')) return;

    processing.value = true;

    try {
        await axios.delete(route('cart.clear'));
        success('MEMORIA TEMPORAL PURGADA'); 
        router.reload({ only: ['items', 'total'] });
    } catch (err) {
        console.error('Error vaciando carrito:', err);
        error('ERROR AL VACIAR BUFFER'); 
    } finally {
        processing.value = false;
    }
};

const checkout = async () => {
    if (processing.value || itemCount.value === 0) return;

    processing.value = true;

    try {
        const photoIds = props.items.map(item => item.photo_id);

        const response = await axios.post(route('payment.initiate.cart'), {
            photo_ids: photoIds
        });

        const paymentUrl = response.data.init_point || response.data.sandbox_init_point;

        if (response.data.success && paymentUrl) {
            window.location.href = paymentUrl; 
        } else {
            error('FALLO DE CONEXIÓN CON PASARELA DE PAGO');
        }
    } catch (err) {
        console.error('Error en checkout:', err);
        error(err.response?.data?.message || 'ERROR DE TRANSACCIÓN');  
    } finally {
        processing.value = false;
    }
};

// Placeholder Brutalista
const handleImageError = (e) => {
    e.target.style.display = 'none';
    const parent = e.target.parentElement;
    if (!parent.querySelector('.placeholder-img')) {
        const placeholder = document.createElement('div');
        placeholder.className = 'placeholder-img w-full h-full flex items-center justify-center bg-gray-950 border border-red-600/30';
        placeholder.innerHTML = `<span class="font-mono text-[9px] text-red-600 uppercase tracking-widest">[ ERROR DE LECTURA ]</span>`;
        parent.appendChild(placeholder);
    }
};
</script>

<template>
    <Head title="Terminal de Descargas — F33" />

    <AppLayout>
        <div class="min-h-screen bg-black text-white font-sans selection:bg-red-600 selection:text-white ">

            <div class="border-b border-white/20 bg-black/90 backdrop-blur-sm sticky top-0 z-30 pt-16 md:pt-0">
                <div class="max-w-[1500px] mx-auto px-4 md:px-8 h-14 flex items-center justify-between font-mono text-[10px] uppercase tracking-widest">
                    <Link :href="route('gallery.index')"
                        class="text-gray-400 hover:text-white flex items-center gap-3 transition-none border border-transparent hover:border-white px-3 py-1">
                        <ArrowLeftIcon class="w-3.5 h-3.5" /> [ RETORNAR AL CATÁLOGO ]
                    </Link>

                    <button v-if="itemCount > 0" @click="clearCart" :disabled="processing"
                        class="text-gray-400 hover:text-red-600 flex items-center gap-2 transition-none disabled:opacity-30 px-3 py-1 border border-transparent hover:border-red-600">
                        <TrashIcon class="w-3.5 h-3.5" /> [ PURGAR MEMORIA ]
                    </button>
                </div>
            </div>

            <div class="max-w-[1500px] mx-auto px-4 md:px-8 py-12 md:py-20">

                <div class="mb-12 border-b-[12px] border-white pb-8">
                    <span class="font-mono text-xs uppercase tracking-[0.45em] text-red-600 mb-4 block  border-red-600 pl-3">
                        Memoria temporal // {{ itemCount }} {{ itemCount === 1 ? 'Archivo' : 'Archivos' }}
                    </span>
                    <h1 class="font-black text-6xl md:text-8xl lg:text-[9rem] leading-[0.85] tracking-tighter uppercase text-white">
                        COLA DE<br><span class="text-red-600">DESCARGAS.</span>
                    </h1>
                </div>

                <div v-if="itemCount === 0" class="flex flex-col items-center justify-center py-32 border-4 border-dashed border-gray-800 bg-gray-950 text-center">
                    <div class="w-20 h-20 border-2 border-red-600 bg-red-600/10 flex items-center justify-center mb-6">
                        <ShoppingBagIcon class="w-10 h-10 text-red-600" />
                    </div>
                    <h2 class="text-4xl md:text-6xl font-black text-white tracking-tighter uppercase mb-4">
                        SISTEMA VACÍO.
                    </h2>
                    <p class="font-mono text-xs text-gray-500 tracking-widest uppercase mb-10">
                        NO HAY FRAGMENTOS ASIGNADOS AL BUFFER DE DESCARGA.
                    </p>
                    <Link :href="route('gallery.index')"
                        class="border-2 border-white bg-black text-white hover:bg-white hover:text-black font-black uppercase tracking-widest px-8 py-4 flex items-center gap-3 transition-none">
                        <ArrowLeftIcon class="w-4 h-4" /> [ INICIAR BÚSQUEDA ]
                    </Link>
                </div>

                <div v-else class="grid grid-cols-1 lg:grid-cols-12 gap-12 lg:gap-16">

                    <div class="lg:col-span-8 flex flex-col gap-4">
                        <div v-for="item in items" :key="item.id"
                            class="bg-gray-950 border-2 border-white/10 hover:border-white p-4 flex flex-col sm:flex-row gap-6 transition-none group">

                            <Link :href="route('gallery.show', item.photo.unique_id)"
                                class="flex-shrink-0 w-full sm:w-40 h-40 bg-black border-[4px] border-black group-hover:border-red-600 overflow-hidden relative transition-none">
                                <ProtectedImage :src="item.photo.thumbnail_url"
                                    class="w-full h-full object-cover grayscale opacity-70 group-hover:grayscale-0 group-hover:opacity-100 transition-none"
                                    @error="handleImageError" />
                                <div class="absolute inset-0 bg-red-600 mix-blend-overlay opacity-0 group-hover:opacity-30 transition-none pointer-events-none"></div>
                            </Link>

                            <div class="flex-1 flex flex-col font-mono relative">
                                <div class="flex justify-between items-start gap-4">
                                    <div>
                                        <p class="text-[9px] font-bold uppercase  text-red-600 mb-1">
                                            ID_REF: #{{ item.photo.unique_id }}
                                        </p>
                                        <Link :href="route('gallery.show', item.photo.unique_id)"
                                            class="font-sans font-black text-2xl md:text-3xl text-white uppercase tracking-tighter leading-none hover:text-red-600 transition-none line-clamp-2">
                                            {{ item.photo.title || 'Fotografía' }}
                                        </Link>
                                    </div>
                                    <button @click="removeItem(item.photo_id)" :disabled="removingItems.has(item.photo_id)"
                                        class="text-gray-600 hover:text-red-600 transition-none disabled:opacity-30 flex-shrink-0 border border-transparent hover:border-red-600 p-1">
                                        <XMarkIcon class="w-6 h-6" />
                                    </button>
                                </div>

                                <div class="flex flex-wrap gap-2 my-4">
                                    <span v-if="item.photo.event" class="bg-white/10 text-white text-[9px] font-bold uppercase tracking-widest px-2 py-1">
                                        {{ item.photo.event.name }}
                                    </span>
                                    <span class="border border-white/20 text-gray-400 text-[9px] font-bold uppercase tracking-widest px-2 py-1">
                                        {{ item.photo.width }} x {{ item.photo.height }} PX
                                    </span>
                                </div>

                                <div class="mt-auto border-t border-white/10 pt-4 flex items-end justify-between">
                                    <span class="text-[9px] font-bold uppercase tracking-[0.2em] text-gray-500">
                                        LICENCIA STANDARD
                                    </span>
                                    <span class="font-sans font-black text-3xl text-white leading-none">
                                        ${{ formatPrice(item.price) }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="lg:col-span-4">
                        <div class="bg-black border-[4px] border-white p-8 sticky top-24 shadow-[8px_8px_0_rgba(220,38,38,1)]">

                            <h2 class="font-mono text-xs font-bold uppercase  text-gray-400 mb-6 border-b border-white/20 pb-4">
                                // BALANCE DE TRANSACCIÓN
                            </h2>

                            <div class="font-mono space-y-4 mb-8">
                                <div class="flex justify-between items-center text-sm uppercase tracking-widest">
                                    <span class="text-gray-400">ARCHIVOS ({{ itemCount }})</span>
                                    <span class="text-white font-bold">${{ formattedTotal }}</span>
                                </div>
                            </div>

                            <div class="mb-8 border-y-[4px] border-white py-6">
                                <div class="flex justify-between items-end font-sans">
                                    <span class="text-xl font-black uppercase text-gray-400 tracking-tighter">TOTAL</span>
                                    <span class="text-5xl font-black text-red-600 leading-none tracking-tighter">
                                        ${{ formattedTotal }}
                                    </span>
                                </div>
                            </div>

                            <button @click="checkout" :disabled="processing || itemCount === 0"
                                class="w-full bg-white text-black font-black text-sm uppercase tracking-[0.25em] py-5 border-[4px] border-white hover:bg-black hover:text-white transition-none flex items-center justify-center gap-3 disabled:opacity-30 disabled:cursor-not-allowed group">
                                <span v-if="processing" class="animate-pulse">PROCESANDO...</span>
                                <span v-else>EJECUTAR TRANSACCIÓN</span>
                                <span v-if="!processing" class="text-lg leading-none group-hover:translate-x-2 transition-transform duration-300">→</span>
                            </button>

                            <div class="mt-6 font-mono text-[9px] uppercase tracking-widest text-gray-500 font-bold space-y-3">
                                <p class="flex items-start gap-2">
                                    <ShieldCheckIcon class="w-4 h-4 text-white flex-shrink-0" />
                                    PASARELA ENCRIPTADA MEDIANTE MERCADO PAGO.
                                </p>
                                <p class="flex items-start gap-2">
                                    <svg class="w-4 h-4 text-white flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>
                                    DESCARGA INMEDIATA AL ACREDITAR PAGO.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </AppLayout>
</template>

<style scoped>

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