<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { computed } from 'vue';
import { 
    CheckCircleIcon, 
    ArrowDownTrayIcon, 
    HomeIcon, 
    PhotoIcon,
    CalendarIcon,
    CurrencyDollarIcon,
    EnvelopeIcon
} from '@heroicons/vue/24/outline';

const props = defineProps({
    purchase: {
        type: Object,
        required: true
    }
});

// Obtener la primera foto (o la única en este caso)
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
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};
</script>

<template>
    <Head title="Compra Confirmada" />

    <AppLayout>
        <div class="min-h-screen bg-gray-50 flex flex-col justify-center py-12 sm:px-6 lg:px-8">
            
            <div class="sm:mx-auto sm:w-full sm:max-w-3xl">
                
                <div class="bg-white border border-emerald-100 rounded-sm shadow-xl overflow-hidden relative">
                    
                    <div class="bg-emerald-600 px-8 py-10 text-center relative overflow-hidden">
                        <div class="absolute inset-0 bg-black/10"></div>
                        <div class="relative z-10">
                            <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-emerald-500 mb-4 shadow-lg">
                                <CheckCircleIcon class="h-10 w-10 text-white" />
                            </div>
                            <h2 class="text-3xl font-serif font-bold text-white mb-2">¡Pago Confirmado!</h2>
                            <p class="text-emerald-100 text-sm font-light tracking-wide">Su transacción ha sido procesada correctamente.</p>
                        </div>
                    </div>

                    <div class="px-8 py-10">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-10">
                            
                            <div class="space-y-6">
                                <h3 class="text-xs font-bold uppercase tracking-widest text-slate-900 border-b border-gray-100 pb-2 mb-4">
                                    Resumen del Pedido
                                </h3>

                                <div class="flex gap-4">
                                    <div class="w-24 h-24 bg-gray-100 border border-gray-200 rounded-sm overflow-hidden flex-shrink-0">
                                        <img v-if="photo?.thumbnail_url" 
                                            :src="photo.thumbnail_url" 
                                            class="w-full h-full object-cover" 
                                        />
                                        <div v-else class="w-full h-full flex items-center justify-center text-slate-300">
                                            <PhotoIcon class="w-8 h-8" />
                                        </div>
                                    </div>

                                    <div class="flex-1">
                                        <p class="text-sm font-bold text-slate-900 mb-1">
                                            Foto Digital #{{ photo?.unique_id }}
                                        </p>
                                        <p class="text-xs text-slate-500 mb-2 font-light">Licencia de uso personal en alta resolución.</p>
                                        <div class="flex items-center text-xs text-slate-600 bg-slate-50 px-2 py-1 rounded-sm w-fit border border-gray-200">
                                            <CurrencyDollarIcon class="w-3 h-3 mr-1" />
                                            {{ formatPrice(purchase.total_amount) }}
                                        </div>
                                    </div>
                                </div>

                                <div class="bg-slate-50 border border-gray-100 p-4 rounded-sm space-y-2">
                                    <div class="flex justify-between text-xs">
                                        <span class="text-slate-500">ID de Transacción</span>
                                        <span class="font-mono text-slate-700">#{{ purchase.id }}</span>
                                    </div>
                                    <div class="flex justify-between text-xs">
                                        <span class="text-slate-500">Fecha</span>
                                        <span class="text-slate-700">{{ formatDate(purchase.created_at) }}</span>
                                    </div>
                                    <div class="flex justify-between text-xs">
                                        <span class="text-slate-500">Método</span>
                                        <span class="text-slate-700 uppercase">{{ purchase.payment_details?.payment_method_id || 'Tarjeta' }}</span>
                                    </div>
                                </div>
                            </div>

                            <div class="flex flex-col h-full">
                                <h3 class="text-xs font-bold uppercase tracking-widest text-slate-900 border-b border-gray-100 pb-2 mb-6">
                                    Entrega Digital
                                </h3>

                                <div class="bg-blue-50 border border-blue-100 p-5 rounded-sm mb-6">
                                    <div class="flex items-start gap-3">
                                        <EnvelopeIcon class="w-5 h-5 text-blue-600 mt-0.5" />
                                        <div>
                                            <p class="text-xs font-bold text-blue-800 uppercase tracking-wide mb-1">Confirmación Enviada</p>
                                            <p class="text-xs text-blue-700/80 leading-relaxed">
                                                Hemos enviado el recibo y el enlace de descarga a <strong>{{ purchase.buyer_email }}</strong>.
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <div class="mt-auto">
                                    <p class="text-xs text-slate-500 mb-3 text-center">Enlace válido por 7 días</p>
                                    
                                    <a v-if="purchase.order_token" 
                                       :href="route('payment.download', purchase.order_token)"
                                       class="block w-full bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold uppercase tracking-widest py-4 rounded-sm text-center transition-all shadow-md hover:shadow-lg flex items-center justify-center gap-2"
                                    >
                                        <ArrowDownTrayIcon class="w-4 h-4" />
                                        Descargar Archivo Original
                                    </a>
                                </div>
                            </div>

                        </div>
                    </div>

                    <div class="bg-gray-50 px-8 py-5 border-t border-gray-100 flex flex-col sm:flex-row justify-between items-center gap-4">
                        <Link :href="route('home')" class="text-xs font-bold uppercase tracking-widest text-slate-500 hover:text-slate-900 flex items-center gap-2 transition">
                            <HomeIcon class="w-4 h-4" /> Volver al Inicio
                        </Link>
                        
                        <a href="mailto:soporte@empresa.com" class="text-xs text-slate-400 hover:text-slate-600 transition">
                            ¿Problemas con la descarga? Contactar Soporte
                        </a>
                    </div>

                </div>

            </div>
        </div>
    </AppLayout>
</template>