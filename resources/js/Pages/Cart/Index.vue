<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { ref, computed } from 'vue';
import {
    ArrowLeftIcon,
    XMarkIcon,
    TrashIcon,
    ShoppingBagIcon,
    BeakerIcon
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
        success('Fotografía eliminada del carrito');  
        router.reload({ only: ['items', 'total'] });
    } catch (err) {
        console.error('Error eliminando item:', err);
        error('Error al eliminar la fotografía');  
    } finally {
        removingItems.value.delete(photoId);
    }
};

const clearCart = async () => {
    if (!confirm('¿Estás seguro de vaciar el carrito?')) return;

    processing.value = true;

    try {
        await axios.delete(route('cart.clear'));
        success('Carrito vaciado correctamente'); //  Toast
        router.reload({ only: ['items', 'total'] });
    } catch (err) {
        console.error('Error vaciando carrito:', err);
        error('Error al vaciar el carrito'); //  Toast
    } finally {
        processing.value = false;
    }
};

const checkout = async (simulate = false) => {
    if (processing.value || itemCount.value === 0) return;

    processing.value = true;

    try {
        const photoIds = props.items.map(item => item.photo_id);

        const response = await axios.post(route('payment.initiate.cart'), {
            photo_ids: photoIds,
            simulate_payment: simulate
        });

        if (response.data.success) {
            if (response.data.simulated) {
                window.location.href = response.data.redirect_url;
            } else {
                window.location.href = response.data.sandbox_init_point;
            }
        }
    } catch (err) {
        console.error('Error en checkout:', err);
        error(err.response?.data?.message || 'Error al procesar el pago');  
    } finally {
        processing.value = false;
    }
};

const handleImageError = (e) => {
    e.target.style.display = 'none';
    const parent = e.target.parentElement;
    if (!parent.querySelector('.placeholder-institutional')) {
        const placeholder = document.createElement('div');
        placeholder.className = 'placeholder-institutional w-full h-full flex items-center justify-center bg-gray-100 text-slate-300';
        placeholder.innerHTML = `<svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>`;
        parent.appendChild(placeholder);
    }
};
</script>

<template>

    <Head title="Carrito de Compras" />

    <AppLayout>
        <div class="min-h-screen bg-white">

            <!-- Header -->
            <div class="border-b border-gray-100 bg-white sticky top-0 z-30">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
                    <Link :href="route('gallery.index')"
                        class="text-[10px] font-bold uppercase tracking-widest text-slate-500 hover:text-slate-900 flex items-center gap-2 transition-colors">
                        <ArrowLeftIcon class="w-3 h-3" /> Seguir Comprando
                    </Link>

                    <button v-if="itemCount > 0" @click="clearCart" :disabled="processing"
                        class="text-[10px] font-bold uppercase tracking-widest text-slate-400 hover:text-red-600 flex items-center gap-2 transition-colors disabled:opacity-50">
                        <TrashIcon class="w-3 h-3" /> Vaciar Carrito
                    </button>
                </div>
            </div>

            <!-- Content -->
            <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-12">

                <!-- Title -->
                <div class="mb-12">
                    <span class="text-[10px] uppercase tracking-widest text-slate-400 block mb-2">
                        {{ itemCount }} {{ itemCount === 1 ? 'Item' : 'Items' }}
                    </span>
                    <h1 class="text-4xl font-serif font-bold text-slate-900">Carrito de Compras</h1>
                </div>

                <!-- Empty State -->
                <div v-if="itemCount === 0" class="text-center py-20">
                    <div class="inline-flex items-center justify-center w-20 h-20 bg-gray-100 rounded-full mb-6">
                        <ShoppingBagIcon class="w-10 h-10 text-slate-300" />
                    </div>
                    <h2 class="text-2xl font-serif font-bold text-slate-900 mb-3">
                        Tu carrito está vacío
                    </h2>
                    <p class="text-slate-500 mb-8 max-w-md mx-auto">
                        Explora nuestra galería y agrega las fotografías que más te gusten
                    </p>
                    <Link :href="route('gallery.index')"
                        class="inline-flex items-center gap-2 bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold uppercase tracking-widest px-8 py-4 rounded-sm transition-all shadow-md hover:shadow-lg">
                        <ArrowLeftIcon class="w-4 h-4" /> Explorar Galería
                    </Link>
                </div>

                <!-- Cart Items -->
                <div v-else class="grid grid-cols-1 lg:grid-cols-3 gap-12">

                    <!-- Items List -->
                    <div class="lg:col-span-2 space-y-4">
                        <div v-for="item in items" :key="item.id"
                            class="bg-white border border-gray-100 rounded-sm p-4 hover:border-gray-200 transition-colors group">

                            <div class="flex gap-6">

                                <!-- Thumbnail -->
                                <Link :href="route('gallery.show', item.photo.unique_id)"
                                    class="flex-shrink-0 w-24 h-24 sm:w-32 sm:h-32 bg-gray-100 rounded-sm overflow-hidden">
                                    <img v-if="item.photo.thumbnail_url" :src="item.photo.thumbnail_url"
                                        :alt="item.photo.title"
                                        class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105 filter grayscale-[0.3] group-hover:grayscale-0"
                                        @error="handleImageError" />
                                </Link>

                                <!-- Info -->
                                <div class="flex-1 min-w-0">
                                    <div class="flex justify-between items-start gap-4 mb-2">
                                        <div class="flex-1">
                                            <Link :href="route('gallery.show', item.photo.unique_id)"
                                                class="text-sm font-bold text-slate-900 hover:text-slate-600 transition-colors line-clamp-1">
                                                {{ item.photo.title || `Foto #${item.photo.unique_id}` }}
                                            </Link>
                                            <p class="text-[10px] uppercase tracking-widest text-slate-400 mt-1">
                                                ID: #{{ item.photo.unique_id }}
                                            </p>
                                        </div>

                                        <button @click="removeItem(item.photo_id)"
                                            :disabled="removingItems.has(item.photo_id)"
                                            class="text-slate-300 hover:text-red-600 transition-colors disabled:opacity-50">
                                            <XMarkIcon class="w-5 h-5" />
                                        </button>
                                    </div>

                                    <div class="flex flex-wrap gap-2 mb-3">
                                        <span v-if="item.photo.event"
                                            class="text-[10px] font-bold uppercase tracking-wider bg-slate-100 text-slate-600 px-2 py-1 rounded-sm">
                                            {{ item.photo.event.name }}
                                        </span>
                                        <span
                                            class="text-[10px] font-bold uppercase tracking-wider bg-slate-100 text-slate-600 px-2 py-1 rounded-sm">
                                            {{ item.photo.width }} x {{ item.photo.height }} px
                                        </span>
                                    </div>

                                    <div class="flex items-baseline justify-between">
                                        <span class="text-xs text-slate-500">Licencia Digital</span>
                                        <span class="text-xl font-serif font-bold text-slate-900">
                                            ${{ formatPrice(item.price) }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Summary Sidebar -->
                    <div class="lg:col-span-1">
                        <div class="bg-white border border-gray-200 rounded-sm p-8 sticky top-24">

                            <h2
                                class="text-xs font-bold uppercase tracking-widest text-slate-900 mb-6 border-b border-gray-100 pb-3">
                                Resumen de Compra
                            </h2>

                            <div class="space-y-3 mb-6 pb-6 border-b border-gray-100">
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-slate-600">
                                        {{ itemCount }} {{ itemCount === 1 ? 'fotografía' : 'fotografías' }}
                                    </span>
                                    <span class="text-sm font-medium text-slate-900">
                                        ${{ formattedTotal }}
                                    </span>
                                </div>
                            </div>

                            <!-- Total -->
                            <div class="mb-8 pb-8 border-b border-gray-200">
                                <div class="flex justify-between items-baseline">
                                    <span class="text-sm font-semibold text-slate-900">Total</span>
                                    <span class="text-3xl font-serif font-bold text-slate-900">
                                        ${{ formattedTotal }}
                                    </span>
                                </div>
                            </div>

                            <!--  Botón Normal de Checkout -->
                            <button @click="checkout(false)" :disabled="processing || itemCount === 0"
                                class="w-full bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold uppercase tracking-widest py-4 rounded-sm transition-all shadow-md hover:shadow-lg flex items-center justify-center gap-2 disabled:opacity-70 disabled:cursor-not-allowed mb-3">
                                <span v-if="processing">Procesando...</span>
                                <span v-else>Finalizar Compra</span>
                                <span v-if="!processing" class="text-lg leading-none">→</span>
                            </button>

                            <!-- 🧪 Botón de Simulación (Solo en desarrollo) -->
                            <button @click="checkout(true)" :disabled="processing || itemCount === 0"
                                class="w-full bg-green-600 hover:bg-green-700 text-white text-xs font-bold uppercase tracking-widest py-3 rounded-sm transition-all shadow-md hover:shadow-lg flex items-center justify-center gap-2 disabled:opacity-70 disabled:cursor-not-allowed mb-4">
                                <BeakerIcon class="w-4 h-4" />
                                <span v-if="processing">Procesando...</span>
                                <span v-else>Simular Pago (Dev)</span>
                            </button>

                            <!-- Info -->
                            <div class="space-y-3">
                                <p class="text-[10px] text-slate-400 text-center leading-relaxed">
                                    Pago seguro a través de Mercado Pago
                                </p>
                                <div class="flex items-center justify-center gap-2 pt-3 border-t border-gray-100">
                                    <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                    </svg>
                                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-600">
                                        Entrega Inmediata vía Email
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </AppLayout>
</template>
