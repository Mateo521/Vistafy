<script setup>
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import {
    CheckCircleIcon,
    XCircleIcon,
    ClockIcon,
    CreditCardIcon,
    BeakerIcon
} from '@heroicons/vue/24/outline';

const props = defineProps({
    purchase: Object,
});

const selectedStatus = ref('approved');
const processing = ref(false);

const simulatePayment = () => {
    if (processing.value) return;
    processing.value = true;

    router.post(route('payment.simulate.process', props.purchase.id), {
        status: selectedStatus.value
    }, {
        onFinish: () => {
            processing.value = false;
        }
    });
};

const formatPrice = (amount) => {
    const numericAmount = parseFloat(amount);
    if (isNaN(numericAmount)) return '$0.00';
    return new Intl.NumberFormat('es-AR', {
        style: 'currency',
        currency: 'ARS'
    }).format(numericAmount);
};
</script>

<template>
    <Head title="Simulación de Pasarela" />

    <AppLayout>
        <div class="min-h-screen bg-gray-50 py-12 flex items-center justify-center">
            <div class="max-w-xl w-full px-4 sm:px-6 lg:px-8">
                
                <div class="text-center mb-10">
                    <div class="inline-flex items-center justify-center w-16 h-16 bg-slate-900 rounded-sm mb-6 shadow-lg">
                        <BeakerIcon class="w-8 h-8 text-white" />
                    </div>
                    <span class="text-xs font-bold tracking-[0.2em] text-slate-400 uppercase mb-2 block">
                        Entorno de Desarrollo
                    </span>
                    <h1 class="text-3xl font-serif font-bold text-slate-900 mb-2">
                        Simulador de Pagos
                    </h1>
                    <p class="text-sm text-slate-500 font-light max-w-sm mx-auto">
                        Esta herramienta permite probar el flujo de compra sin realizar transacciones reales.
                    </p>
                </div>

                <div class="bg-white border border-gray-200 rounded-sm shadow-xl overflow-hidden">
                    
                    <div class="p-8 border-b border-gray-100 bg-gray-50/50">
                        <h3 class="text-xs font-bold uppercase tracking-widest text-slate-900 mb-6 flex items-center gap-2">
                            <CreditCardIcon class="w-4 h-4" /> Resumen de la Orden
                        </h3>
                        
                        <div class="space-y-4 text-sm">
                            <div class="flex justify-between items-center">
                                <span class="text-slate-500">ID de Referencia</span>
                                <span class="font-mono text-slate-900">#{{ purchase.id }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-slate-500">Cliente</span>
                                <span class="font-medium text-slate-900">{{ purchase.buyer_email }}</span>
                            </div>
                            
                            <div class="pt-4 border-t border-gray-200 mt-4 space-y-2">
                                <div v-for="item in purchase.items" :key="item.id" class="flex justify-between items-start text-xs">
                                    <span class="text-slate-600">Foto #{{ item.photo.unique_id }}</span>
                                    <span class="font-mono text-slate-900">{{ formatPrice(item.unit_price) }}</span>
                                </div>
                            </div>

                            <div class="pt-4 border-t border-gray-200 flex justify-between items-center">
                                <span class="text-sm font-bold text-slate-900">Total a Pagar</span>
                                <span class="text-xl font-serif font-bold text-slate-900">
                                    {{ formatPrice(purchase.total_amount) }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="p-8">
                        <h3 class="text-xs font-bold uppercase tracking-widest text-slate-900 mb-6">
                            Seleccione Resultado Simulado
                        </h3>

                        <div class="space-y-3">
                            <label class="group relative flex items-center p-4 border rounded-sm cursor-pointer transition-all duration-300"
                                :class="selectedStatus === 'approved' ? 'border-emerald-500 bg-emerald-50/30' : 'border-gray-200 hover:border-slate-400'">
                                <input type="radio" v-model="selectedStatus" value="approved" class="sr-only" />
                                <div class="flex items-center gap-4 w-full">
                                    <div :class="['w-10 h-10 rounded-full flex items-center justify-center border transition-colors', selectedStatus === 'approved' ? 'bg-emerald-500 border-emerald-500 text-white' : 'bg-white border-gray-200 text-gray-300']">
                                        <CheckCircleIcon class="w-6 h-6" />
                                    </div>
                                    <div>
                                        <div class="font-bold text-slate-900 text-sm">Pago Aprobado</div>
                                        <div class="text-xs text-slate-500 font-light">Simula una transacción exitosa inmediata.</div>
                                    </div>
                                </div>
                            </label>

                            <label class="group relative flex items-center p-4 border rounded-sm cursor-pointer transition-all duration-300"
                                :class="selectedStatus === 'rejected' ? 'border-red-500 bg-red-50/30' : 'border-gray-200 hover:border-slate-400'">
                                <input type="radio" v-model="selectedStatus" value="rejected" class="sr-only" />
                                <div class="flex items-center gap-4 w-full">
                                    <div :class="['w-10 h-10 rounded-full flex items-center justify-center border transition-colors', selectedStatus === 'rejected' ? 'bg-red-500 border-red-500 text-white' : 'bg-white border-gray-200 text-gray-300']">
                                        <XCircleIcon class="w-6 h-6" />
                                    </div>
                                    <div>
                                        <div class="font-bold text-slate-900 text-sm">Pago Rechazado</div>
                                        <div class="text-xs text-slate-500 font-light">Simula un error de tarjeta o fondos insuficientes.</div>
                                    </div>
                                </div>
                            </label>

                            <label class="group relative flex items-center p-4 border rounded-sm cursor-pointer transition-all duration-300"
                                :class="selectedStatus === 'pending' ? 'border-amber-500 bg-amber-50/30' : 'border-gray-200 hover:border-slate-400'">
                                <input type="radio" v-model="selectedStatus" value="pending" class="sr-only" />
                                <div class="flex items-center gap-4 w-full">
                                    <div :class="['w-10 h-10 rounded-full flex items-center justify-center border transition-colors', selectedStatus === 'pending' ? 'bg-amber-500 border-amber-500 text-white' : 'bg-white border-gray-200 text-gray-300']">
                                        <ClockIcon class="w-6 h-6" />
                                    </div>
                                    <div>
                                        <div class="font-bold text-slate-900 text-sm">Pago Pendiente</div>
                                        <div class="text-xs text-slate-500 font-light">Simula una revisión de seguridad o pago en efectivo.</div>
                                    </div>
                                </div>
                            </label>
                        </div>

                        <div class="mt-8">
                            <button 
                                @click="simulatePayment"
                                :disabled="processing"
                                class="w-full bg-slate-900 text-white py-4 rounded-sm text-xs font-bold uppercase tracking-widest hover:bg-slate-800 transition-all shadow-md hover:shadow-lg disabled:opacity-70 disabled:cursor-not-allowed flex items-center justify-center gap-2"
                            >
                                <svg v-if="processing" class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <span v-if="processing">Procesando...</span>
                                <span v-else>Ejecutar Simulación</span>
                            </button>
                        </div>

                    </div>
                </div>

            </div>
        </div>
    </AppLayout>
</template>