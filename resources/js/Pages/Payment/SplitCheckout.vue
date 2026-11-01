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
    HomeIcon,
    CameraIcon
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
        case 'approved': return 'Aprobado';
        case 'rejected':
        case 'cancelled':
        case 'failed': return 'Revisar';
        default: return 'Pendiente';
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
        <div class="min-h-screen bg-[#F8F9FA] text-slate-800 px-4 py-24 font-sans relative overflow-hidden">

            <div
                class="absolute top-0 left-0 w-96 h-96 bg-red-100 rounded-full mix-blend-multiply filter blur-3xl opacity-50 animate-blob pointer-events-none">
            </div>
            <div
                class="absolute top-0 right-0 w-96 h-96 bg-yellow-100 rounded-full mix-blend-multiply filter blur-3xl opacity-50 animate-blob animation-delay-2000 pointer-events-none">
            </div>

            <div class="max-w-7xl mx-auto relative z-10">
                <div class="pb-8 mb-10 border-b border-gray-200">
                    <p class="font-bold text-xs uppercase tracking-widest text-[#E30613] mb-3">
                        // Orden #{{ purchaseState.id }}
                    </p>
                    <h1 class="font-flux text-5xl md:text-7xl uppercase tracking-tighter leading-none text-black">
                        Pagos por<br><span class="text-[#E30613]">fotógrafo.</span>
                    </h1>
                    <p class="text-sm font-medium text-gray-500 mt-5 max-w-3xl leading-relaxed">
                        Tu carrito contiene fotos de distintos profesionales. Mercado Pago procesa cada pago por
                        separado.
                        <strong>A medida que abones cada bloque, podrás descargar sus fotos inmediatamente</strong>, sin
                        esperar al resto.
                    </p>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <!-- Lista de Pagos -->
                    <div class="lg:col-span-2 space-y-5">
                        <div v-for="payment in payments" :key="payment.id"
                            class="bg-white/80 backdrop-blur-xl border border-gray-100 shadow-[0_8px_30px_rgb(0,0,0,0.04)] rounded p-6 flex flex-col md:flex-row md:items-center gap-6 justify-between hover:shadow-lg transition-shadow duration-300">

                            <div class="flex items-start gap-4">
                                <div
                                    class="w-12 h-12 rounded-full bg-gray-50 flex items-center justify-center border border-gray-100 shrink-0">
                                    <CameraIcon class="w-6 h-6 text-gray-400" />
                                </div>
                                <div>
                                    <p class="font-bold text-[10px] uppercase tracking-widest text-gray-400 mb-1">
                                        Fotógrafo
                                    </p>
                                    <h2 class="text-xl font-bold uppercase tracking-tight text-black line-clamp-1">
                                        {{ payment.photographer?.business_name || payment.photographer_name ||
                                        'Fotógrafo' }}
                                    </h2>
                                    <p class="text-sm font-medium text-gray-500 mt-1">
                                        Subtotal: <span class="text-[#E30613] font-flux text-xl ml-1">{{
                                            formatPrice(payment.amount) }}</span>
                                    </p>
                                </div>
                            </div>

                            <div class="flex flex-col sm:flex-row sm:items-center gap-3 md:min-w-[200px] justify-end">
                                <span
                                    class="font-bold text-[10px] uppercase tracking-widest px-4 py-2 rounded-full border text-center"
                                    :class="{
                                        'bg-green-50 border-green-200 text-green-600': payment.status === 'approved',
                                        'bg-red-50 border-red-200 text-red-600': ['rejected', 'cancelled', 'failed'].includes(payment.status),
                                        'bg-yellow-50 border-yellow-200 text-yellow-600': !['approved', 'rejected', 'cancelled', 'failed'].includes(payment.status),
                                    }">
                                    {{ statusLabel(payment.status) }}
                                </span>

                                <button v-if="payment.status !== 'approved'" @click="goToPayment(payment)"
                                    :disabled="!paymentUrl(payment)"
                                    class="bg-black text-white font-bold uppercase tracking-widest px-6 py-2.5 text-xs rounded-full hover:bg-[#E30613] disabled:opacity-40 disabled:cursor-not-allowed flex items-center justify-center gap-2 transition-colors shadow-md">
                                    <CreditCardIcon class="w-4 h-4" /> Pagar
                                </button>


                                <Link v-else :href="route('payment.success', { purchase_id: purchaseState.id })"
                                    class="bg-green-600 text-white font-bold uppercase tracking-widest px-6 py-2.5 text-xs rounded-full hover:bg-green-700 flex items-center justify-center gap-2 transition-colors shadow-md shadow-green-500/30">
                                    <ArrowDownTrayIcon class="w-4 h-4" /> Ver mis fotos
                                </Link>



                            </div>
                        </div>
                    </div>


                    <aside
                        class="bg-white/90 backdrop-blur-xl border border-gray-100 shadow-[0_8px_30px_rgb(0,0,0,0.04)] rounded p-8 h-fit sticky top-24">
                        <p
                            class="font-bold text-[10px] uppercase tracking-widest text-gray-400 mb-6 border-b border-gray-100 pb-3 flex items-center gap-2">
                            <span class="w-4 h-px bg-gray-200"></span> Resumen Global
                        </p>

                        <div class="space-y-4 font-bold text-xs uppercase tracking-widest mb-8 text-gray-500">
                            <div class="flex justify-between items-end border-b border-gray-50 pb-3">
                                <span>Total Orden</span>
                                <span class="text-black font-flux text-2xl leading-none">{{
                                    formatPrice(purchaseState.total_amount) }}</span>
                            </div>
                            <div class="flex justify-between items-center pb-2">
                                <span>Bloques Aprobados</span>
                                <span class="bg-gray-100 text-black px-3 py-1 rounded-full">{{ approvedCount }} / {{
                                    payments.length }}</span>
                            </div>
                        </div>

                        <div v-if="allApproved"
                            class="border border-green-100 bg-green-50 rounded p-5 mb-6 flex items-start gap-4">
                            <CheckCircleIcon class="w-6 h-6 text-green-500 flex-shrink-0" />
                            <p class="font-bold text-[10px] uppercase tracking-widest text-green-700 leading-relaxed">
                                Todos los pagos fueron completados. Revisa tu correo o descarga desde cada bloque.
                            </p>
                        </div>
                        <div v-else-if="hasFailed"
                            class="border border-red-100 bg-red-50 rounded p-5 mb-6 flex items-start gap-4">
                            <ExclamationTriangleIcon class="w-6 h-6 text-red-500 flex-shrink-0" />
                            <p class="font-bold text-[10px] uppercase tracking-widest text-red-700 leading-relaxed">
                                Uno de los pagos falló. Intenta nuevamente el bloque pendiente.
                            </p>
                        </div>
                        <div v-else
                            class="border border-yellow-100 bg-yellow-50 rounded p-5 mb-6 flex items-start gap-4">
                            <ClockIcon class="w-6 h-6 text-yellow-600 flex-shrink-0" />
                            <p class="font-bold text-[10px] uppercase tracking-widest text-yellow-700 leading-relaxed">
                                Procesando pagos... La pantalla se actualizará automáticamente.
                            </p>
                        </div>

                        <button @click="refreshStatus" :disabled="refreshing"
                            class="w-full bg-gray-50 border border-gray-200 text-gray-700 font-bold uppercase tracking-widest py-3.5 text-[10px] rounded-full hover:bg-gray-100 disabled:opacity-50 flex items-center justify-center gap-2 mb-4 transition-colors">
                            <ArrowPathIcon class="w-4 h-4" :class="{ 'animate-spin': refreshing }" />
                            Actualizar estado
                        </button>

                        <Link :href="route('home')"
                            class="w-full text-gray-400 hover:text-black font-bold uppercase tracking-widest text-[10px] flex items-center justify-center gap-2 transition-colors mt-2">
                            <HomeIcon class="w-4 h-4" /> Volver al inicio
                        </Link>
                    </aside>
                </div>
            </div>
        </div>
    </AppLayout>
</template>