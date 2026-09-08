<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { computed, onMounted, ref } from 'vue';
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
    <Head title="Compra exitosa" />

    <AppLayout>
        <div class="min-h-screen bg-[#F8F9FA] flex flex-col justify-center py-24 sm:px-6 lg:px-8 font-sans text-slate-800 relative overflow-hidden">

            <div class="sm:mx-auto sm:w-full sm:max-w-4xl relative z-10 px-4 sm:px-0">

                <div class="bg-white border border-gray-100 shadow-xl rounded overflow-hidden relative">

                    
                    <div class="bg-green-50/50 border-b border-green-100 px-8 py-10 md:py-14 text-center relative overflow-hidden">
                        
                        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-48 h-48 bg-green-200 rounded-full blur-3xl opacity-50 pointer-events-none"></div>
                        
                        <div class="relative z-10">
                            <div class="mx-auto flex items-center justify-center h-24 w-24 bg-white rounded-full mb-6 shadow-sm border border-green-100">
                                <CheckBadgeIcon class="h-14 w-14 text-green-500" />
                            </div>
                            <h2 class="text-4xl md:text-5xl font-flux text-black leading-none mb-3">
                                Pago <span class="text-green-500">Exitoso</span>
                            </h2>
                            <p class="text-sm font-bold text-gray-500 uppercase tracking-wider">
                                Tu compra fue procesado correctamente
                            </p>
                        </div>
                    </div>

                    
                    <div class="px-6 py-10 md:p-12">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-10 md:gap-16">

                            
                            <div class="space-y-6">
                                <h3 class="text-xs font-bold uppercase tracking-widest text-gray-400 border-b border-gray-100 pb-3 flex items-center gap-2">
                                    <span class="w-4 h-px bg-gray-200"></span> Resumen de compra
                                </h3>

                                <div class="flex flex-col sm:flex-row gap-5">
                                    
                                    <div class="w-32 h-32 bg-gray-50 rounded border border-gray-200 flex-shrink-0 relative overflow-hidden shadow-inner group">
                                        <img v-if="photo?.thumbnail_url || photo?.watermarked_url"
                                            :src="photo.thumbnail_url || photo.watermarked_url"
                                            class="w-full h-full object-cover transition-transform duration-500" />
                                        <div v-else class="w-full h-full flex items-center justify-center text-gray-300">
                                            <PhotoIcon class="w-8 h-8" />
                                        </div>
                                    </div>

                                    
                                    <div class="flex-1 flex flex-col justify-center">
                                        <p class="font-bold text-slate-800 text-lg mb-1 line-clamp-1">
                                            {{ photo?.title || `Fotografía #${photo?.unique_id || 'N/A'}` }}
                                        </p>
                                        <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-4">
                                            Licencia de uso personal
                                        </p>
                                        <div class="flex items-center text-lg font-flux text-[#E30613]">
                                            {{ formatPrice(purchase.total_amount) }}
                                        </div>
                                    </div>
                                </div>

                                
                                <div class="bg-gray-50 rounded p-5 space-y-3 text-xs font-bold uppercase tracking-wider text-gray-500 border border-gray-100">
                                    <div class="flex justify-between items-center border-b border-gray-200 pb-2">
                                        <span>ID de transacción</span>
                                        <span class="text-black">#{{ purchase.id }}</span>
                                    </div>
                                    <div class="flex justify-between items-center border-b border-gray-200 pb-2">
                                        <span>Fecha</span>
                                        <span class="text-black">{{ formatDate(purchase.created_at) }}</span>
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <span>Método de pago</span>
                                        <span class="text-black">{{ purchase.payment_details?.payment_method_id || 'Digital' }}</span>
                                    </div>
                                </div>
                            </div>

                            
                            <div class="flex flex-col h-full">
                                <h3 class="text-xs font-bold uppercase tracking-widest text-gray-400 border-b border-gray-100 pb-3 flex items-center gap-2 mb-6">
                                    <span class="w-4 h-px bg-gray-200"></span> Descarga de archivos
                                </h3>

                                <div class="bg-blue-50/50 border border-blue-100 rounded p-6 mb-8 flex items-start gap-4">
                                    <EnvelopeIcon class="w-6 h-6 text-blue-500 shrink-0 mt-0.5" />
                                    <div>
                                        <p class="font-bold text-slate-800 mb-1">Recibo y enlace enviados</p>
                                        <p class="text-xs font-medium text-blue-800/80 leading-relaxed">
                                            Enviamos un comprobante y un enlace de recuperación seguro a la dirección 
                                            <strong class="text-blue-600 block mt-1 truncate">{{ purchase.buyer_email }}</strong>
                                        </p>
                                    </div>
                                </div>

                                <div class="mt-auto">
                                    <p v-if="autoDownloading" class="text-xs font-bold text-gray-500 mb-4 text-center uppercase tracking-wider flex items-center justify-center gap-2">
                                        <span class="w-4 h-4 border-2 border-gray-300 border-t-gray-600 rounded-full animate-spin"></span>
                                        Iniciando descarga automática...
                                    </p>
                                    <p v-else class="text-xs font-bold text-gray-400 mb-4 text-center uppercase tracking-wider">
                                        Enlace válido por 7 días
                                    </p>

                                    <a v-if="purchase.order_token"
                                        :href="route('payment.download', purchase.order_token)"
                                        class="w-full bg-black text-white font-bold text-sm uppercase tracking-wider py-4 rounded-full hover:bg-[#E30613] hover:shadow-lg hover:shadow-red-500/30 transition-all flex items-center justify-center gap-3 group">
                                        <ArrowDownTrayIcon class="w-5 h-5 group-hover:-translate-y-1 transition-transform" />
                                        Descargar original (Alta Res)
                                    </a>
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
                            ¿Tenés problemas con la descarga? Contactar soporte
                        </a>
                    </div>

                </div>

            </div>
        </div>
    </AppLayout>
</template>