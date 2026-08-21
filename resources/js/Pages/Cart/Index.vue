<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import ProtectedImage from '@/Components/ProtectedImage.vue';
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
const unavailablePaymentPhotographers = computed(() => {
    const photographers = new Map();

    (props.items || []).forEach((item) => {
        const photographer = item.photo?.photographer;

        if (photographer && !photographer.has_mercadopago_account) {
            photographers.set(photographer.id, photographer.business_name || 'Fotógrafo');
        }
    });

    return Array.from(photographers, ([id, name]) => ({ id, name }));
});

const hasUnavailablePaymentPhotographers = computed(() => unavailablePaymentPhotographers.value.length > 0);

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
    if (hasUnavailablePaymentPhotographers.value) {
        const names = unavailablePaymentPhotographers.value.map(photographer => photographer.name).join(', ');
        error(`NO SE PUEDE COMPRAR: ${names} DEBE VINCULAR MERCADO PAGO.`);
        return;
    }

    processing.value = true;

    try {
        const photoIds = props.items.map(item => item.photo_id);

        const response = await axios.post(route('payment.initiate.cart'), {
            photo_ids: photoIds
        });

        if (response.data.success && response.data.requires_multiple_payments && response.data.redirect_url) {
            window.location.href = response.data.redirect_url;
            return;
        }

        const paymentUrl = response.data.init_point || response.data.sandbox_init_point;

        if (response.data.success && paymentUrl) {
            window.location.href = paymentUrl;
        } else {
            error('FALLO DE CONEXIÓN CON PASARELA DE PAGO');
            processing.value = false;
        }
    } catch (err) {
        console.error('Error en checkout:', err);
        processing.value = false;
        error(err.response?.data?.message || 'ERROR AL PROCESAR TRANSACCIÓN');
    }
};

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
    <Head title="Carrito de Compras | F33" />

    <AppLayout>
        <div class="min-h-screen bg-[#F8F9FA] text-slate-800 font-sans selection:bg-[#E30613] selection:text-white pb-24 pt-24 md:pt-28">

            
            <div class="max-w-[1500px] mx-auto px-4 sm:px-6 lg:px-8 mb-10">
                <div class="flex flex-col md:flex-row md:items-end justify-between border-b border-gray-200 pb-8 gap-6">
                    <div>
                        <Link :href="route('gallery.index')"
                            class="inline-flex items-center gap-2 bg-white px-4 py-2 rounded-full text-xs font-bold uppercase tracking-wider text-gray-500 hover:text-black hover:shadow-sm border border-gray-200 transition-all mb-6">
                            <ArrowLeftIcon class="w-4 h-4" /> Seguir explorando
                        </Link>
                        <h1 class="font-flux text-5xl md:text-7xl text-black uppercase tracking-wide leading-none">
                            Mi <span class="text-[#E30613]">Carrito</span>
                        </h1>
                        <p class="text-sm font-bold text-gray-500 uppercase tracking-wider mt-4">
                            {{ itemCount }} {{ itemCount === 1 ? 'Archivo seleccionado' : 'Archivos seleccionados' }}
                        </p>
                    </div>

                    <button v-if="itemCount > 0" @click="clearCart" :disabled="processing"
                        class="px-5 py-2.5 bg-red-50 text-[#E30613] text-xs font-bold uppercase tracking-wider hover:bg-red-100 transition-colors rounded-full flex items-center gap-2 disabled:opacity-50 w-max">
                        <TrashIcon class="w-4 h-4" /> Vaciar Carrito
                    </button>
                </div>
            </div>

            <div class="max-w-[1500px] mx-auto px-4 sm:px-6 lg:px-8">

                
                <div v-if="itemCount === 0"
                    class="flex flex-col items-center justify-center py-24 bg-white rounded border border-gray-100 shadow-sm text-center">
                    <div class="w-24 h-24 bg-gray-50 rounded-full flex items-center justify-center mb-6">
                        <ShoppingBagIcon class="w-12 h-12 text-gray-300" />
                    </div>
                    <h2 class="text-4xl font-flux text-black mb-3">
                        Carrito vacío
                    </h2>
                    <p class="text-sm font-medium text-gray-500 mb-10 max-w-md mx-auto">
                        Todavía no agregaste ninguna fotografía. Explorá nuestras galerías para encontrar y descargar las mejores fotos.
                    </p>
                    <Link :href="route('gallery.index')"
                        class="bg-black text-white hover:bg-[#E30613] hover:shadow-lg hover:shadow-red-500/30 hover:-translate-y-1 font-bold text-xs uppercase tracking-wider px-8 py-4 rounded-full flex items-center gap-2 transition-all">
                        <ArrowLeftIcon class="w-4 h-4" /> Ir al catálogo
                    </Link>
                </div>

                
                <div v-else class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12">

                
                    <div class="lg:col-span-8 flex flex-col gap-4">
                        <div v-for="item in items" :key="item.id"
                            class="bg-white border border-gray-100 hover:shadow-md hover:border-gray-200 rounded p-4 md:p-6 flex flex-col sm:flex-row gap-6 transition-all group">

                            <Link :href="route('gallery.show', item.photo.unique_id)"
                                class="block flex-shrink-0 w-full sm:w-48 h-48 bg-gray-100 rounded overflow-hidden relative">
                                <ProtectedImage :src="item.photo.thumbnail_url" :alt="item.photo.title"
                                    class="absolute inset-0 w-full h-full object-cover transition-transform duration-700 "
                                    @error="handleImageError" />
                                <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none"></div>
                            </Link>

                            <div class="flex-1 flex flex-col relative">
                                <div class="flex justify-between items-start gap-4">
                                    <div>
                                        <span class="inline-block bg-gray-50 text-gray-500 text-[10px] font-bold uppercase tracking-wider px-2.5 py-1 rounded-md mb-3 border border-gray-100">
                                            ID: #{{ item.photo.unique_id }}
                                        </span>
                                        <Link :href="route('gallery.show', item.photo.unique_id)"
                                            class="block font-flux text-3xl text-black leading-none hover:text-[#E30613] transition-colors line-clamp-2">
                                            {{ item.photo.title || 'Captura Fotográfica' }}
                                        </Link>
                                    </div>
                                    <button @click="removeItem(item.photo_id)"
                                        :disabled="removingItems.has(item.photo_id)"
                                        class="w-10 h-10 flex items-center justify-center rounded-full bg-gray-50 text-gray-400 hover:bg-red-50 hover:text-[#E30613] transition-colors disabled:opacity-50 shrink-0">
                                        <XMarkIcon class="w-5 h-5" />
                                    </button>
                                </div>

                                <div class="flex flex-wrap gap-2 my-4">
                                    <span v-if="item.photo.event"
                                        class="bg-gray-50 text-slate-600 text-[10px] font-bold uppercase tracking-wider px-2.5 py-1.5 rounded-md">
                                        {{ item.photo.event.name }}
                                    </span>
                                    <span class="bg-gray-50 text-slate-600 text-[10px] font-bold uppercase tracking-wider px-2.5 py-1.5 rounded-md">
                                        {{ item.photo.width }} x {{ item.photo.height }} PX
                                    </span>
                                </div>

                                <div class="mt-auto border-t border-gray-100 pt-4 flex items-end justify-between">
                                    <span class="text-[10px] font-bold uppercase text-gray-400">
                                        Licencia Estándar
                                    </span>
                                    <span class="font-flux text-4xl text-black leading-none">
                                        ${{ formatPrice(item.price) }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    
                    <div class="lg:col-span-4">
                        <div class="bg-white border border-gray-100 rounded p-6 md:p-8 shadow-sm sticky top-28">

                            <h2 class="text-xs font-bold uppercase tracking-widest text-gray-400 mb-6 flex items-center gap-2 border-b border-gray-100 pb-4">
                                <span class="w-4 h-px bg-gray-200"></span> Resumen de compra
                            </h2>

                            <div class="space-y-4 mb-6">
                                <div class="flex justify-between items-center text-sm font-bold text-slate-600">
                                    <span>Subtotal ({{ itemCount }} items)</span>
                                    <span>${{ formattedTotal }}</span>
                                </div>
                            </div>

                            <div class="mb-8 border-y border-gray-100 py-6">
                                <div class="flex justify-between items-end">
                                    <span class="text-sm font-bold uppercase text-gray-400">Total</span>
                                    <span class="text-5xl font-flux text-black leading-none tracking-wide">
                                        ${{ formattedTotal }}
                                    </span>
                                </div>
                            </div>

                            <div v-if="hasUnavailablePaymentPhotographers"
                                class="mb-6 bg-red-50 border border-red-100 p-4 rounded text-xs font-medium text-red-600 leading-relaxed">
                                <p class="font-bold text-[#E30613] mb-1">Pago no disponible</p>
                                <p>
                                    {{ unavailablePaymentPhotographers.map(photographer => photographer.name).join(', ') }}
                                    debe vincular Mercado Pago para recibir pagos. Quita esas fotos del carrito o intenta más tarde.
                                </p>
                            </div>

                            <button @click="checkout" :disabled="processing || itemCount === 0 || hasUnavailablePaymentPhotographers"
                                class="w-full bg-black text-white font-bold text-xs uppercase tracking-wider py-4 rounded-full hover:bg-[#E30613] hover:shadow-lg hover:shadow-red-500/30 transition-all flex items-center justify-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:bg-black disabled:hover:shadow-none group">
                                <span v-if="processing" class="flex items-center gap-2">
                                    <div class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin"></div> Procesando...
                                </span>
                                <span v-else-if="hasUnavailablePaymentPhotographers">Pago No Disponible</span>
                                <span v-else>Proceder al Pago</span>
                                <ArrowRightIcon v-if="!processing && !hasUnavailablePaymentPhotographers" class="w-4 h-4 group-hover:translate-x-1 transition-transform" />
                            </button>

                            <div class="mt-6 space-y-3 text-[10px] font-bold uppercase tracking-widest text-gray-400">
                                <p class="flex items-center gap-2">
                                    <ShieldCheckIcon class="w-4 h-4 text-green-500" /> Transacción segura con Mercado Pago.
                                </p>
                                <p class="flex items-center gap-2">
                                    <CloudArrowDownIcon class="w-4 h-4 text-blue-500" /> Descarga inmediata al acreditar.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        
        <transition enter-active-class="transition duration-300 ease-out"
            enter-from-class="opacity-0" enter-to-class="opacity-100"
            leave-active-class="transition duration-200 ease-in" leave-from-class="opacity-100"
            leave-to-class="opacity-0">
            <div v-if="showEmailModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
                <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" @click="showEmailModal = false"></div>

                <div class="relative bg-white rounded w-full max-w-md shadow-2xl overflow-hidden z-10 flex flex-col">
                    
                    <div class="px-6 py-5 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
                        <span class="font-bold text-sm text-black">Finalizar compra</span>
                        <button @click="showEmailModal = false" class="w-8 h-8 rounded-full bg-white border border-gray-200 flex items-center justify-center text-gray-500 hover:bg-red-50 hover:text-[#E30613] hover:border-red-100 transition-colors">
                            <XMarkIcon class="w-4 h-4" />
                        </button>
                    </div>

                    <div class="p-6 md:p-8">
                        <form @submit.prevent="submitGuestCheckout" class="space-y-6">
                            
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 mb-2 ml-1">
                                    Correo de entrega
                                </label>
                                <input v-model="guestEmail" type="email" required
                                    class="w-full bg-gray-50 border border-transparent focus:bg-white focus:border-gray-300 focus:ring-4 focus:ring-gray-100 text-slate-800 font-medium text-sm py-3.5 px-4 rounded-xl transition-all outline-none"
                                    placeholder="tu@correo.com">
                                <p v-if="emailError" class="text-[#E30613] text-xs font-bold mt-2 ml-1">
                                    {{ emailError }}
                                </p>
                            </div>

                            <label class="relative flex justify-between items-start cursor-pointer group p-4 border border-gray-100 rounded hover:border-gray-200 transition-colors">
                                <div class="pr-4">
                                    <span class="block text-sm font-bold text-slate-700 group-hover:text-black transition-colors">
                                        Crear cuenta F33
                                    </span>
                                    <span class="block text-xs text-gray-400 mt-1 font-medium">
                                        Guardá tu historial de compras. Vas a recibir tu contraseña por email.
                                    </span>
                                </div>
                                <div class="relative inline-flex items-center mt-1 shrink-0">
                                    <input id="createAccount" v-model="createAccount" type="checkbox" class="sr-only peer">
                                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#E30613]"></div>
                                </div>
                            </label>

                            <div class="pt-4 flex flex-col gap-3">
                                <button type="submit" :disabled="processing"
                                    class="w-full py-3.5 rounded-full bg-black text-white text-xs font-bold uppercase tracking-wider hover:bg-[#E30613] transition-colors disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2">
                                    <span v-if="processing" class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin"></span>
                                    {{ processing ? 'Procesando...' : 'Ir a pagar' }}
                                </button>
                                <button type="button" @click="showEmailModal = false"
                                    class="w-full py-3.5 text-gray-500 bg-white border border-gray-200 rounded-full text-xs font-bold uppercase tracking-wider hover:bg-gray-50 hover:text-black transition-colors">
                                    Cancelar
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </transition>

    </AppLayout>
</template>

<style scoped>
::-webkit-scrollbar {
    width: 6px;
}
::-webkit-scrollbar-track {
    background: transparent;
}
::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 10px;
}
::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
}
</style>