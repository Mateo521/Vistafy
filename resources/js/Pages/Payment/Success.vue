<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { computed } from 'vue';
import {
    CheckBadgeIcon,
    ArrowDownTrayIcon,
    HomeIcon,
    PhotoIcon,
    EnvelopeIcon
} from '@heroicons/vue/24/outline';

const props = defineProps({
    purchase: {
        type: Object,
        required: true
    }
});

const purchasedItems = computed(() => {
    const items = props.purchase.items || [];
    
    return items.filter(item => {
        const isOrderApproved = props.purchase.status === 'approved';
        const isItemPaid = item.payment && item.payment.status === 'approved';
        
        return isOrderApproved || isItemPaid;
    });
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


const getDownloadUrl = (photoId) => {
    return route('payment.download', { token: props.purchase.order_token, photo_id: photoId });
};
</script>

<template>

    <Head title="Compra exitosa" />

    <AppLayout>
        <div
            class="min-h-screen bg-[#F8F9FA] flex flex-col justify-center py-24 sm:px-6 lg:px-8 font-sans text-slate-800 relative overflow-hidden">

            <div class="sm:mx-auto sm:w-full sm:max-w-4xl relative z-10 px-4 sm:px-0">

                <div class="bg-white border border-gray-100 shadow-xl rounded overflow-hidden relative">


                    <div
                        class="bg-green-50/50 border-b border-green-100 px-8 py-10 md:py-14 text-center relative overflow-hidden">
                        <div
                            class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-48 h-48 bg-green-200 rounded-full blur-3xl opacity-50 pointer-events-none">
                        </div>

                        <div class="relative z-10">
                            <div
                                class="mx-auto flex items-center justify-center h-24 w-24 bg-white rounded-full mb-6 shadow-sm border border-green-100">
                                <CheckBadgeIcon class="h-14 w-14 text-green-500" />
                            </div>
                            <h2 class="text-4xl md:text-5xl font-flux text-black leading-none mb-3">
                                Pago <span class="text-green-500">Exitoso</span>
                            </h2>
                            <p class="text-sm font-bold text-gray-500 uppercase tracking-wider">
                                Tu compra fue procesada correctamente
                            </p>
                        </div>
                    </div>


                    <div class="px-6 py-10 md:p-12">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-10 md:gap-16">


                            <div class="space-y-6">
                                <h3
                                    class="text-xs font-bold uppercase tracking-widest text-gray-400 border-b border-gray-100 pb-3 flex items-center gap-2">
                                    <span class="w-4 h-px bg-gray-200"></span> Tus fotografías
                                </h3>


                                <div class="max-h-[400px] overflow-y-auto pr-2 space-y-4">
                                    <div v-for="item in purchasedItems" :key="item.id"
                                        class="flex flex-col sm:flex-row gap-4 p-3 bg-gray-50 rounded border border-gray-100 items-center">


                                        <div
                                            class="w-24 h-24 bg-white rounded border border-gray-200 flex-shrink-0 relative overflow-hidden shadow-inner">
                                            <img v-if="item.photo?.thumbnail_url || item.photo?.watermarked_url"
                                                :src="item.photo.thumbnail_url || item.photo.watermarked_url"
                                                class="w-full h-full object-cover" />
                                            <div v-else
                                                class="w-full h-full flex items-center justify-center text-gray-300">
                                                <PhotoIcon class="w-6 h-6" />
                                            </div>
                                        </div>


                                        <div
                                            class="flex-1 flex flex-col justify-center text-center sm:text-left w-full">
                                            <p class="font-bold text-slate-800 text-sm mb-1 truncate">
                                                {{ item.photo?.title || `Fotografía #${item.photo?.unique_id || 'N/A'}`
                                                }}
                                            </p>

                                            <a :href="getDownloadUrl(item.photo_id)"
                                                class="mt-3 w-full inline-flex items-center justify-center gap-2 bg-black text-white font-bold text-[10px] uppercase tracking-wider py-2.5 rounded hover:bg-[#E30613] transition-colors group">
                                                <ArrowDownTrayIcon
                                                    class="w-4 h-4 group-hover:-translate-y-0.5 transition-transform" />
                                                Descargar foto
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>


                            <div class="flex flex-col h-full">
                                <h3
                                    class="text-xs font-bold uppercase tracking-widest text-gray-400 border-b border-gray-100 pb-3 flex items-center gap-2 mb-6">
                                    <span class="w-4 h-px bg-gray-200"></span> Información
                                </h3>

                                <div
                                    class="bg-blue-50/50 border border-blue-100 rounded p-6 mb-8 flex items-start gap-4">
                                    <EnvelopeIcon class="w-6 h-6 text-blue-500 shrink-0 mt-0.5" />
                                    <div>
                                        <p class="font-bold text-slate-800 mb-1">Recibo y enlaces enviados</p>
                                        <p class="text-xs font-medium text-blue-800/80 leading-relaxed">
                                            Enviamos un comprobante y un enlace seguro a:
                                            <strong class="text-blue-600 block mt-1 truncate">{{ purchase.buyer_email
                                                }}</strong>
                                            <span
                                                class="block mt-2 text-[10px] uppercase tracking-wider text-blue-400">Los
                                                enlaces son válidos por 7 días.</span>
                                        </p>
                                    </div>
                                </div>

                                <div
                                    class="bg-gray-50 rounded p-5 space-y-3 text-xs font-bold uppercase tracking-wider text-gray-500 border border-gray-100 mt-auto">
                                    <div class="flex justify-between items-center border-b border-gray-200 pb-2">
                                        <span>Total abonado</span>
                                        <span class="text-[#E30613] font-flux text-xl">{{
                                            formatPrice(purchase.total_amount) }}</span>
                                    </div>
                                    <div class="flex justify-between items-center border-b border-gray-200 pb-2">
                                        <span>ID Transacción</span>
                                        <span class="text-black">#{{ purchase.id }}</span>
                                    </div>
                                    <div class="flex justify-between items-center border-b border-gray-200 pb-2">
                                        <span>Fecha</span>
                                        <span class="text-black">{{ formatDate(purchase.created_at) }}</span>
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <span>Método</span>
                                        <span class="text-black">{{ purchase.payment_details?.payment_method_id ||
                                            'MercadoPago' }}</span>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>


                    <div
                        class="bg-gray-50 px-8 py-6 border-t border-gray-100 flex flex-col sm:flex-row justify-between items-center gap-4">
                        <Link :href="route('home')"
                            class="text-xs font-bold uppercase tracking-wider text-gray-500 hover:text-black flex items-center gap-2 transition-colors">
                            <HomeIcon class="w-4 h-4" /> Volver al inicio
                        </Link>

                        <a href="mailto:contacto@f33.click"
                            class="text-[10px] font-bold uppercase tracking-widest text-gray-400 hover:text-[#E30613] transition-colors">
                            ¿Problemas con la descarga? Contactar soporte
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>