<script setup>
import { ref, onMounted, onUnmounted, computed } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { 
    ClockIcon, 
    ArrowPathIcon, 
    HomeIcon, 
    PhotoIcon 
} from '@heroicons/vue/24/outline';

const props = defineProps({
    purchase: {
        type: Object,
        required: true,
    },
});

const isChecking = ref(false);
let pollingInterval = null;

// Helper para obtener la foto
const photo = computed(() => {
    if (props.purchase.items && props.purchase.items.length > 0) {
        return props.purchase.items[0].photo;
    }
    return props.purchase.photo || null;
});

const isApproved = computed(() => props.purchase.status === 'approved');

const checkStatus = () => {
    if (isChecking.value) return;
    isChecking.value = true;

    router.reload({
        only: ['purchase'],
        onSuccess: (page) => {
            if (page.props.purchase?.status === 'approved') {
                router.visit(route('payment.success', { purchase_id: props.purchase.id }), { replace: true });
            }
        },
        onFinish: () => {
            setTimeout(() => isChecking.value = false, 1000);
        }
    });
};

const startPolling = () => {
    pollingInterval = setInterval(checkStatus, 10000); // Cada 10 segundos
};

const stopPolling = () => {
    if (pollingInterval) clearInterval(pollingInterval);
};

const backToPhoto = () => {
    if (photo.value?.unique_id) {
        router.visit(route('gallery.show', photo.value.unique_id));
    }
};

onMounted(() => {
    if (isApproved.value) {
        router.visit(route('payment.success', { purchase_id: props.purchase.id }), { replace: true });
        return;
    }
    startPolling();
});

onUnmounted(() => {
    stopPolling();
});

const formatPrice = (amount) => {
    return new Intl.NumberFormat('es-AR', { style: 'currency', currency: 'ARS' }).format(parseFloat(amount));
};
</script>

<template>
    <Head title="Procesando Pago" />

    <AppLayout>
        <div class="min-h-screen bg-gray-50 flex flex-col justify-center py-12 sm:px-6 lg:px-8">
            
            <div class="sm:mx-auto sm:w-full sm:max-w-xl">
                
                <div class="bg-white border border-amber-100 rounded-sm shadow-xl overflow-hidden relative">
                    
                    <div class="bg-amber-400 px-8 py-10 text-center relative overflow-hidden">
                        <div class="absolute inset-0 bg-black/5"></div>
                        <div class="relative z-10">
                            <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-amber-500 border-2 border-white/30 mb-4 shadow-lg animate-pulse">
                                <ClockIcon class="h-8 w-8 text-white" />
                            </div>
                            <h2 class="text-3xl font-serif font-bold text-white mb-2">Pago en Proceso</h2>
                            <p class="text-amber-50 text-sm font-light tracking-wide">Estamos verificando su transacción.</p>
                        </div>
                    </div>

                    <div class="px-8 py-10">
                        
                        <div class="text-center mb-8">
                            <p class="text-slate-600 leading-relaxed text-sm">
                                Su pago ha sido recibido y está pendiente de confirmación. Esto suele demorar unos instantes, pero en algunos casos puede tardar hasta 48 horas (ej: Rapipago).
                            </p>
                        </div>

                        <div class="bg-amber-50 border border-amber-100 rounded-sm p-6 mb-8 text-left">
                            <h3 class="text-xs font-bold uppercase tracking-widest text-amber-800 mb-4 border-b border-amber-200 pb-2">
                                Detalles de la Orden
                            </h3>
                            
                            <div class="space-y-3 text-sm">
                                <div class="flex justify-between">
                                    <span class="text-amber-800/70">ID de Referencia</span>
                                    <span class="font-mono text-amber-900 font-medium">#{{ purchase.id }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-amber-800/70">Monto Total</span>
                                    <span class="text-amber-900 font-bold">{{ formatPrice(purchase.total_amount) }}</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-amber-800/70">Estado Actual</span>
                                    <span class="flex items-center gap-2 text-amber-700 bg-white px-2 py-1 rounded-sm border border-amber-100 text-xs font-bold uppercase tracking-wider">
                                        <ArrowPathIcon class="w-3 h-3 animate-spin" v-if="isChecking" />
                                        <span>Pendiente</span>
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="space-y-4">
                            <button v-if="photo" @click="backToPhoto" 
                                class="block w-full bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold uppercase tracking-widest py-4 rounded-sm text-center transition-all shadow-md hover:shadow-lg flex items-center justify-center gap-2">
                                <PhotoIcon class="w-4 h-4" />
                                Volver a la Foto
                            </button>

                            <Link :href="route('home')" 
                                class="block w-full border border-slate-300 text-slate-600 hover:text-slate-900 hover:border-slate-900 text-xs font-bold uppercase tracking-widest py-4 rounded-sm text-center transition-all flex items-center justify-center gap-2">
                                <HomeIcon class="w-4 h-4" />
                                Ir al Inicio
                            </Link>
                        </div>
                        
                        <div class="mt-8 text-center text-xs text-slate-400 font-light">
                            <p>Esta página se actualizará automáticamente cuando el pago se apruebe.</p>
                        </div>

                    </div>

                </div>

            </div>
        </div>
    </AppLayout>
</template>