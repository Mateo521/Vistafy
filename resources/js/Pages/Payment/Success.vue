<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { computed, onMounted, ref } from 'vue';
import {
    CheckCircleIcon,
    ArrowDownTrayIcon,
    HomeIcon,
    PhotoIcon,
    CurrencyDollarIcon,
    EnvelopeIcon
} from '@heroicons/vue/24/outline';

const props = defineProps({
    purchase: {
        type: Object,
        required: true
    }
});


const autoDownloading = ref(true);


const photo = computed(() => {
    return props.purchase.items?.[0]?.photo || null;
});

const formatPrice = (amount) => {
    return new Intl.NumberFormat('es-AR', {
        style: 'currency',
        currency: 'ARS'
    }).format(parseFloat(amount) || 0);
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('es-ES', {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
        hour: '2-digit',
        minute: '2-digit'
    }).replace(/\//g, '.');
};


onMounted(() => {
    if (props.purchase?.order_token) {
        setTimeout(() => {

            const downloadUrl = route('payment.download', props.purchase.order_token);
            window.location.href = downloadUrl;

            setTimeout(() => {
                autoDownloading.value = false;
            }, 1000);
        }, 2000);
    } else {
        autoDownloading.value = false;
    }
});
</script>

<template>

    <Head title="Extracción Confirmada - f33" />

    <AppLayout>
        <div
            class="min-h-screen bg-black flex flex-col justify-center py-24 sm:px-6 lg:px-8 font-sans text-white relative overflow-hidden">

            <div
                class="absolute inset-0 bg-[linear-gradient(rgba(255,255,255,.03)_1px,transparent_1px),linear-gradient(90deg,rgba(255,255,255,.03)_1px,transparent_1px)] bg-[size:32px_32px] pointer-events-none">
            </div>

            <div class="sm:mx-auto sm:w-full sm:max-w-4xl relative z-10">

                <div class="bg-zinc-950 border border-white/15 shadow-2xl overflow-hidden relative">

                    <div class="bg-zinc-900 border-b border-red-600 px-8 py-10 text-center relative overflow-hidden">
                        <div
                            class="absolute inset-0 bg-[linear-gradient(to_bottom,rgba(255,255,255,0.02)_1px,transparent_1px)] bg-[size:100%_4px] pointer-events-none">
                        </div>
                        <div class="relative z-10">
                            <div
                                class="mx-auto flex items-center justify-center h-20 w-20 border-2 border-red-600 bg-black mb-6 shadow-[0_0_20px_rgba(220,38,38,0.4)]">
                                <CheckCircleIcon class="h-10 w-10 text-red-600" />
                            </div>
                            <h2 class="text-3xl md:text-5xl font-black text-white uppercase tracking-tighter mb-2">
                                Transacción Exitosa</h2>
                            <p class="text-gray-400 font-mono text-[10px] uppercase tracking-widest">Enlace de datos
                                establecido correctamente.</p>
                        </div>
                    </div>

                    <div class="px-8 py-12">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-12">

                            <div class="space-y-6">
                                <h3
                                    class="font-mono text-[10px] font-bold uppercase tracking-widest text-red-600 border-b border-white/10 pb-2 mb-6">
                                    [ Resumen del Objetivo ]
                                </h3>

                                <div class="flex gap-5">
                                    <div class="w-28 h-28 bg-black border border-white/10 flex-shrink-0 relative group">
                                        <img v-if="photo?.thumbnail_url || photo?.watermarked_url"
                                            :src="photo.thumbnail_url || photo.watermarked_url"
                                            class="w-full h-full object-cover filter grayscale contrast-125 group-hover:grayscale-0 transition-all duration-500" />
                                        <div v-else
                                            class="w-full h-full flex items-center justify-center text-gray-700">
                                            <PhotoIcon class="w-8 h-8" />
                                        </div>
                                    </div>

                                    <div class="flex-1 flex flex-col justify-center">
                                        <p class="font-black text-white uppercase tracking-tighter text-xl mb-1">
                                            Archivo #{{ photo?.unique_id || 'Desconocido' }}
                                        </p>
                                        <p class="font-mono text-[9px] text-gray-500 uppercase tracking-widest mb-3">
                                            Licencia de Uso Personal (Alta Res)
                                        </p>
                                        <div
                                            class="flex items-center font-mono text-xs font-bold text-white bg-zinc-900 border border-white/10 px-3 py-1.5 w-fit">
                                            <CurrencyDollarIcon class="w-3.5 h-3.5 mr-1.5 text-red-600" />
                                            {{ formatPrice(purchase.total_amount) }}
                                        </div>
                                    </div>
                                </div>

                                <div
                                    class="bg-black border border-white/10 p-5 space-y-3 font-mono text-[10px] uppercase tracking-widest">
                                    <div class="flex justify-between items-center border-b border-white/5 pb-2">
                                        <span class="text-gray-500">ID de Transacción</span>
                                        <span class="text-white font-bold">#{{ purchase.id }}</span>
                                    </div>
                                    <div class="flex justify-between items-center border-b border-white/5 pb-2">
                                        <span class="text-gray-500">Timestamp</span>
                                        <span class="text-white">{{ formatDate(purchase.created_at) }}</span>
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <span class="text-gray-500">Vía de Pago</span>
                                        <span class="text-white">{{ purchase.payment_details?.payment_method_id ||
                                            'Digital' }}</span>
                                    </div>
                                </div>
                            </div>

                            <div class="flex flex-col h-full">
                                <h3
                                    class="font-mono text-[10px] font-bold uppercase tracking-widest text-red-600 border-b border-white/10 pb-2 mb-6">
                                    [ Extracción de Archivos ]
                                </h3>

                                <div class="bg-zinc-900 border border-white/10 p-6 mb-6">
                                    <div class="flex items-start gap-4">
                                        <EnvelopeIcon class="w-6 h-6 text-red-600 mt-1" />
                                        <div>
                                            <p
                                                class="font-sans text-sm font-black text-white uppercase tracking-wide mb-2">
                                                Respaldo Enviado</p>
                                            <p
                                                class="font-mono text-[10px] text-gray-400 leading-relaxed uppercase tracking-widest">
                                                Hemos enviado el recibo y un enlace seguro de recuperación a <br>
                                                <span class="text-white font-bold">{{ purchase.buyer_email }}</span>.
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <div class="mt-auto">
                                    <p v-if="autoDownloading"
                                        class="font-mono text-[10px] text-red-600 mb-3 text-center uppercase tracking-widest animate-pulse">
                                        Iniciando descarga automática...
                                    </p>
                                    <p v-else
                                        class="font-mono text-[10px] text-gray-500 mb-3 text-center uppercase tracking-widest">
                                        Enlace encriptado válido por 7 días
                                    </p>

                                    <a v-if="purchase.order_token"
                                        :href="route('payment.download', purchase.order_token)"
                                        class="group block w-full bg-white hover:bg-red-600 text-black hover:text-white font-sans text-xs font-black uppercase  py-5 text-center transition-all duration-300 border border-white flex items-center justify-center gap-3">
                                        <ArrowDownTrayIcon
                                            class="w-5 h-5 group-hover:-translate-y-1 transition-transform" />
                                        Descargar original
                                    </a>
                                </div>
                            </div>

                        </div>
                    </div>

                    <div
                        class="bg-zinc-900 px-8 py-6 border-t border-white/10 flex flex-col sm:flex-row justify-between items-center gap-4">
                        <Link :href="route('home')"
                            class="font-mono text-[10px] font-bold uppercase tracking-widest text-gray-500 hover:text-white flex items-center gap-2 transition-colors">
                            <HomeIcon class="w-4 h-4" /> CANCELAR al inicio
                        </Link>

                        <a href="mailto:contacto@f33.click"
                            class="font-mono text-[10px] uppercase tracking-widest text-gray-600 hover:text-red-600 transition-colors">
                            ¿Falla en extracción? Contactar
                        </a>
                    </div>

                </div>

            </div>
        </div>
    </AppLayout>
</template>