<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import axios from 'axios';
import { computed, onMounted, onUnmounted, ref } from 'vue';
import {
    ArrowDownTrayIcon,
    ArrowPathIcon,
    CheckCircleIcon,
    ClockIcon,
    CreditCardIcon,
    ExclamationTriangleIcon,
    HomeIcon
} from '@heroicons/vue/24/outline';

const props = defineProps({
    purchase: {
        type: Object,
        required: true,
    },
});

const purchaseState = ref({ ...props.purchase });
const payments = ref([...(props.purchase.payments || [])]);
const refreshing = ref(false);
let interval = null;

const formatPrice = (amount) => {
    return new Intl.NumberFormat('es-AR', {
        style: 'currency',
        currency: 'ARS',
    }).format(parseFloat(amount) || 0);
};

const approvedCount = computed(() => payments.value.filter(payment => payment.status === 'approved').length);
const allApproved = computed(() => purchaseState.value.status === 'approved' || approvedCount.value === payments.value.length);
const hasFailed = computed(() => payments.value.some(payment => ['rejected', 'cancelled', 'failed'].includes(payment.status)));

const paymentUrl = (payment) => payment.init_point || payment.sandbox_init_point;

const statusLabel = (status) => {
    switch (status) {
        case 'approved':
            return 'Aprobado';
        case 'rejected':
        case 'cancelled':
        case 'failed':
            return 'Revisar';
        default:
            return 'Pendiente';
    }
};

const refreshStatus = async () => {
    if (refreshing.value) return;
    refreshing.value = true;

    try {
        const response = await axios.get(route('payment.split.status', props.purchase.id));
        purchaseState.value = {
            ...purchaseState.value,
            ...response.data.purchase,
        };
        payments.value = response.data.payments;
    } finally {
        refreshing.value = false;
    }
};

const goToPayment = async (payment) => {
    let url = paymentUrl(payment);

    if (['rejected', 'cancelled', 'failed'].includes(payment.status)) {
        const response = await axios.post(route('payment.split.retry', {
            purchase: props.purchase.id,
            payment: payment.id,
        }));

        if (response.data.success) {
            const index = payments.value.findIndex(item => item.id === payment.id);
            if (index !== -1) {
                payments.value[index] = response.data.payment;
            }
            url = response.data.init_point || response.data.sandbox_init_point;
        }
    }
    if (url) {
        window.location.href = url;
    }
};

onMounted(() => {
    interval = setInterval(refreshStatus, 10000);
});

onUnmounted(() => {
    if (interval) clearInterval(interval);
});
</script>

<template>
    <Head title="Pagos por fotógrafo" />

    <AppLayout>
        <div class="min-h-screen bg-black text-white px-4 py-24 font-sans">
            <div class="max-w-7xl mx-auto">
                <div class="border-b-[10px] border-white pb-8 mb-10">
                    <p class="font-mono text-xs uppercase tracking-widest text-red-600 mb-3">
                        // Orden #{{ purchaseState.id }}
                    </p>
                    <h1 class="font-black text-5xl md:text-7xl uppercase tracking-tighter leading-none">
                        Pagos por<br><span class="text-red-600">fotógrafo.</span>
                    </h1>
                    <p class="font-mono text-xs uppercase tracking-widest text-gray-400 mt-5 max-w-3xl">
                        Mercado Pago liquida cada pago a un fotógrafo. Para completar esta compra, aboná cada bloque pendiente.
                        La descarga se habilita cuando todos estén aprobados.
                    </p>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <div class="lg:col-span-2 space-y-4">
                        <div
                            v-for="payment in payments"
                            :key="payment.id"
                            class="bg-zinc-950 border-2 border-white/10 p-5 flex flex-col md:flex-row md:items-center gap-5 justify-between"
                        >
                            <div>
                                <p class="font-mono text-[10px] uppercase tracking-widest text-gray-500 mb-2">
                                    Fotógrafo
                                </p>
                                <h2 class="text-2xl font-black uppercase tracking-tight">
                                    {{ payment.photographer?.business_name || payment.photographer_name || 'Fotógrafo' }}
                                </h2>
                                <p class="font-mono text-xs text-gray-400 mt-2">
                                    Subtotal: <span class="text-white font-bold">{{ formatPrice(payment.amount) }}</span>
                                </p>
                            </div>

                            <div class="flex flex-col sm:flex-row sm:items-center gap-3">
                                <span
                                    class="font-mono text-[10px] uppercase tracking-widest border px-3 py-2"
                                    :class="{
                                        'border-green-500 text-green-400': payment.status === 'approved',
                                        'border-red-600 text-red-500': ['rejected', 'cancelled', 'failed'].includes(payment.status),
                                        'border-yellow-500 text-yellow-400': !['approved', 'rejected', 'cancelled', 'failed'].includes(payment.status),
                                    }"
                                >
                                    {{ statusLabel(payment.status) }}
                                </span>

                                <button
                                    v-if="payment.status !== 'approved'"
                                    @click="goToPayment(payment)"
                                    :disabled="!paymentUrl(payment)"
                                    class="bg-white text-black font-black uppercase tracking-widest px-5 py-3 text-xs border-2 border-white hover:bg-black hover:text-white disabled:opacity-40 disabled:cursor-not-allowed flex items-center justify-center gap-2"
                                >
                                    <CreditCardIcon class="w-4 h-4" />
                                    Pagar
                                </button>

                                <CheckCircleIcon v-else class="w-8 h-8 text-green-400" />
                            </div>
                        </div>
                    </div>

                    <aside class="bg-zinc-950 border-[4px] border-white p-6 h-fit sticky top-24">
                        <p class="font-mono text-[10px] uppercase tracking-widest text-gray-500 mb-4">
                            Resumen
                        </p>

                        <div class="space-y-3 font-mono text-xs uppercase tracking-widest mb-6">
                            <div class="flex justify-between">
                                <span class="text-gray-500">Total</span>
                                <span class="font-bold">{{ formatPrice(purchaseState.total_amount) }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-500">Aprobados</span>
                                <span class="font-bold">{{ approvedCount }} / {{ payments.length }}</span>
                            </div>
                        </div>

                        <div v-if="allApproved" class="border border-green-500 bg-green-500/10 p-4 mb-5">
                            <div class="flex items-start gap-3">
                                <CheckCircleIcon class="w-6 h-6 text-green-400 flex-shrink-0" />
                                <p class="font-mono text-[10px] uppercase tracking-widest text-green-300">
                                    Todos los pagos fueron aprobados. Ya podés descargar la orden completa.
                                </p>
                            </div>
                        </div>

                        <div v-else-if="hasFailed" class="border border-red-600 bg-red-600/10 p-4 mb-5">
                            <div class="flex items-start gap-3">
                                <ExclamationTriangleIcon class="w-6 h-6 text-red-500 flex-shrink-0" />
                                <p class="font-mono text-[10px] uppercase tracking-widest text-red-300">
                                    Uno de los pagos falló o fue cancelado. Podés volver a intentar el bloque pendiente.
                                </p>
                            </div>
                        </div>

                        <div v-else class="border border-yellow-500 bg-yellow-500/10 p-4 mb-5">
                            <div class="flex items-start gap-3">
                                <ClockIcon class="w-6 h-6 text-yellow-400 flex-shrink-0" />
                                <p class="font-mono text-[10px] uppercase tracking-widest text-yellow-200">
                                    Todavía hay pagos pendientes de aprobación.
                                </p>
                            </div>
                        </div>

                        <a
                            v-if="allApproved && purchaseState.order_token"
                            :href="route('payment.download', purchaseState.order_token)"
                            class="w-full bg-white text-black font-black uppercase tracking-widest py-4 text-xs border-2 border-white hover:bg-black hover:text-white flex items-center justify-center gap-2 mb-3"
                        >
                            <ArrowDownTrayIcon class="w-4 h-4" />
                            Descargar
                        </a>

                        <button
                            @click="refreshStatus"
                            :disabled="refreshing"
                            class="w-full border border-white/30 text-white font-bold uppercase tracking-widest py-3 text-[10px] hover:border-white disabled:opacity-50 flex items-center justify-center gap-2 mb-3"
                        >
                            <ArrowPathIcon class="w-4 h-4" :class="{ 'animate-spin': refreshing }" />
                            Actualizar estado
                        </button>

                        <Link
                            :href="route('home')"
                            class="w-full text-gray-500 hover:text-white font-mono uppercase tracking-widest text-[10px] flex items-center justify-center gap-2"
                        >
                            <HomeIcon class="w-4 h-4" />
                            Volver al inicio
                        </Link>
                    </aside>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
