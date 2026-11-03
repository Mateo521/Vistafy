<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { computed } from 'vue';
import {
    CheckBadgeIcon,
    ArrowDownTrayIcon,
    HomeIcon,
    PhotoIcon,
    EnvelopeIcon,
    ArrowRightIcon
} from '@heroicons/vue/24/outline';

const props = defineProps({
    purchase: {
        type: Object,
        required: true
    },

    has_pending_payments: { 
        type: Boolean,
        default: false
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
        <div class="min-h-screen bg-[#F8F9FA] flex flex-col justify-center py-24 sm:px-6 lg:px-8 font-sans text-slate-800 relative">

            <div class="sm:mx-auto sm:w-full sm:max-w-4xl relative z-10 px-4 sm:px-0">
                <div class="bg-white border border-gray-100 shadow-sm hover:shadow-md transition-shadow duration-300 rounded overflow-hidden">

                    
                    <div class="bg-white border-b border-gray-100 px-8 py-10 text-center">
                        <div class="mx-auto flex items-center justify-center h-16 w-16 bg-green-50 rounded-full mb-4">
                            <CheckBadgeIcon class="h-8 w-8 text-green-500" />
                        </div>
                        <h2 class="text-2xl md:text-3xl font-flux text-black leading-none mb-2">
                            Pago <span class="text-green-500">exitoso</span>
                        </h2>
                        <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">
                            Tu compra fue procesada correctamente
                        </p>
                    </div>

                    
                    <div class="px-6 py-8 md:p-10">
                        
                        
                        <div v-if="has_pending_payments" class="mb-10 bg-yellow-50 border border-yellow-100 p-5 rounded">
                            <p class="text-xs font-bold uppercase tracking-widest text-yellow-700 mb-2">
                                Atención
                            </p>
                            <p class="text-sm text-yellow-800 font-medium leading-relaxed mb-4">
                                El pago a este fotógrafo fue procesado exitosamente, pero <strong>aún tienes fotos de otros profesionales pendientes de pago</strong> en esta misma orden.
                            </p>
                            <Link :href="route('payment.split.show', purchase.id)"
                                class="inline-flex items-center justify-center gap-2 bg-black text-white font-bold text-[10px] uppercase tracking-wider py-3 px-6 rounded-full hover:bg-[#E30613] transition-colors shadow-md w-full sm:w-auto">
                                Continuar pagando el resto <ArrowRightIcon class="w-4 h-4" />
                            </Link>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-10">
                            
                            
                            <div class="space-y-6">
                                <h3 class="text-xs font-bold uppercase tracking-widest text-gray-400 border-b border-gray-100 pb-3 flex items-center gap-2">
                                    <span class="w-4 h-px bg-gray-200"></span> Fotografías compradas
                                </h3>

                                
                                <div class="space-y-4">
                                    <div v-for="item in purchasedItems" :key="item.id"
                                        class="flex flex-col sm:flex-row gap-4 p-3 bg-gray-50 rounded border border-gray-100 items-center">
                                        
                                        <div class="w-20 h-20 bg-white rounded border border-gray-200 flex-shrink-0 relative overflow-hidden shadow-inner">
                                            <img v-if="item.photo?.thumbnail_url || item.photo?.watermarked_url"
                                                :src="item.photo.thumbnail_url || item.photo.watermarked_url"
                                                class="w-full h-full object-cover" />
                                            <div v-else class="w-full h-full flex items-center justify-center text-gray-300">
                                                <PhotoIcon class="w-6 h-6" />
                                            </div>
                                        </div>

                                        <div class="flex-1 flex flex-col justify-center text-center sm:text-left w-full">
                                            <p class="font-bold text-slate-800 text-sm mb-1 truncate">
                                                {{ item.photo?.title || `Ref #${item.photo?.unique_id?.substring(0,6) || 'N/A'}` }}
                                            </p>
                                            <a :href="getDownloadUrl(item.photo_id)"
                                                class="mt-2 w-full inline-flex items-center justify-center gap-2 bg-white border border-gray-200 text-black font-bold text-[10px] uppercase tracking-wider py-2 rounded hover:border-black transition-colors group">
                                                <ArrowDownTrayIcon class="w-4 h-4 group-hover:-translate-y-0.5 transition-transform" />
                                                Descargar foto
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Información de la Orden -->
                            <div class="flex flex-col h-full">
                                <h3 class="text-xs font-bold uppercase tracking-widest text-gray-400 border-b border-gray-100 pb-3 flex items-center gap-2 mb-6">
                                    <span class="w-4 h-px bg-gray-200"></span> Información
                                </h3>

                                <div class="bg-blue-50/50 border border-blue-100 rounded p-6 mb-8 flex items-start gap-4">
                                    <EnvelopeIcon class="w-6 h-6 text-blue-500 shrink-0 mt-0.5" />
                                    <div>
                                        <p class="font-bold text-slate-800 mb-1">Recibo y enlaces enviados</p>
                                        <p class="text-xs font-medium text-blue-800/80 leading-relaxed">
                                            Enviamos un comprobante y enlaces seguros a:
                                            <strong class="text-blue-600 block mt-1 truncate">{{ purchase.buyer_email }}</strong>
                                            <span class="block mt-2 text-[10px] uppercase tracking-wider text-blue-400">Los enlaces son válidos por 7 días.</span>
                                        </p>
                                    </div>
                                </div>

                                <div class="bg-gray-50 rounded p-5 space-y-3 text-xs font-bold uppercase tracking-wider text-gray-500 border border-gray-100 mt-auto">
                                    <div class="flex justify-between items-center border-b border-gray-200 pb-2">
                                        <span>Total abonado</span>
                                        <span class="text-[#E30613] font-flux text-xl leading-none">{{ formatPrice(purchase.total_amount) }}</span>
                                    </div>
                                    <div class="flex justify-between items-center border-b border-gray-200 pb-2">
                                        <span>Orden N°</span>
                                        <span class="text-black font-mono">#{{ purchase.id }}</span>
                                    </div>
                                    <div class="flex justify-between items-center border-b border-gray-200 pb-2">
                                        <span>Fecha</span>
                                        <span class="text-black">{{ formatDate(purchase.created_at) }}</span>
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <span>Método</span>
                                        <span class="text-black">{{ purchase.payment_details?.payment_method_id || 'MercadoPago' }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>


                    <div class="bg-gray-50 px-8 py-6 border-t border-gray-100 flex flex-col sm:flex-row justify-between items-center gap-4">
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