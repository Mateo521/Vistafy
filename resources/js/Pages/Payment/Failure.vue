<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { computed } from 'vue';
import { 
    ExclamationTriangleIcon, 
    ArrowPathIcon, 
    HomeIcon, 
    XCircleIcon 
} from '@heroicons/vue/24/outline';

const props = defineProps({
    purchase: {
        type: Object,
        default: null
    }
});

// Helper para obtener la foto (soporta estructura antigua y nueva de items)
const photo = computed(() => {
    if (!props.purchase) return null;
    // Si tiene items (nueva estructura carrito), toma la primera foto
    if (props.purchase.items && props.purchase.items.length > 0) {
        return props.purchase.items[0].photo;
    }
    // Fallback a relación directa
    return props.purchase.photo || null;
});

const retryPayment = () => {
    if (photo.value?.unique_id) {
        router.visit(route('gallery.show', photo.value.unique_id));
    } else {
        router.visit(route('gallery.index'));
    }
};

const formatDate = (date) => {
    if (!date) return 'N/A';
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
    <Head title="Pago Rechazado" />

    <AppLayout>
        <div class="min-h-screen bg-gray-50 flex flex-col justify-center py-12 sm:px-6 lg:px-8">
            
            <div class="sm:mx-auto sm:w-full sm:max-w-xl">
                
                <div class="bg-white border border-red-100 rounded-sm shadow-xl overflow-hidden relative">
                    
                    <div class="bg-red-600 px-8 py-10 text-center relative overflow-hidden">
                        <div class="absolute inset-0 bg-black/10"></div>
                        
                        <div class="absolute inset-0 opacity-10" style="background-image: radial-gradient(#fff 1px, transparent 1px); background-size: 20px 20px;"></div>

                        <div class="relative z-10">
                            <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-red-700/50 border border-red-400/30 mb-4 shadow-lg backdrop-blur-sm">
                                <ExclamationTriangleIcon class="h-8 w-8 text-white" />
                            </div>
                            <h2 class="text-3xl font-serif font-bold text-white mb-2">Transacción Fallida</h2>
                            <p class="text-red-100 text-sm font-light tracking-wide">El proceso de pago no pudo completarse.</p>
                        </div>
                    </div>

                    <div class="px-8 py-10">
                        
                        <div class="text-center mb-8">
                            <p class="text-slate-600 leading-relaxed text-sm">
                                Lo sentimos, hubo un problema al procesar su tarjeta o medio de pago. No se ha realizado ningún cargo a su cuenta.
                            </p>
                        </div>

                        <div v-if="purchase" class="bg-red-50 border border-red-100 rounded-sm p-6 mb-8 text-left">
                            <h3 class="text-xs font-bold uppercase tracking-widest text-red-800 mb-4 flex items-center gap-2 border-b border-red-200 pb-2">
                                <XCircleIcon class="w-4 h-4" /> Reporte del Error
                            </h3>
                            
                            <div class="space-y-3 text-sm">
                                <div class="flex justify-between">
                                    <span class="text-red-700/70">ID de Referencia</span>
                                    <span class="font-mono text-red-900 font-medium">#{{ purchase.id }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-red-700/70">Fecha</span>
                                    <span class="text-red-900">{{ formatDate(purchase.created_at) }}</span>
                                </div>
                                <div v-if="purchase.payment_details?.status_detail" class="flex flex-col mt-2 pt-2 border-t border-red-200/50">
                                    <span class="text-red-700/70 text-xs uppercase tracking-wide mb-1">Motivo Técnico</span>
                                    <span class="font-mono text-red-900 bg-white/50 p-2 rounded-sm border border-red-100 text-xs">
                                        {{ purchase.payment_details.status_detail }}
                                    </span>
                                </div>
                                <div v-else-if="purchase.rejection_reason" class="flex flex-col mt-2 pt-2 border-t border-red-200/50">
                                    <span class="text-red-700/70 text-xs uppercase tracking-wide mb-1">Motivo</span>
                                    <span class="text-red-900 font-medium">{{ purchase.rejection_reason }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="space-y-4">
                            <button @click="retryPayment" 
                                class="block w-full bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold uppercase tracking-widest py-4 rounded-sm text-center transition-all shadow-md hover:shadow-lg flex items-center justify-center gap-2">
                                <ArrowPathIcon class="w-4 h-4" />
                                Intentar Nuevamente con otro medio
                            </button>

                            <Link :href="route('home')" 
                                class="block w-full border border-slate-300 text-slate-600 hover:text-slate-900 hover:border-slate-900 text-xs font-bold uppercase tracking-widest py-4 rounded-sm text-center transition-all flex items-center justify-center gap-2">
                                <HomeIcon class="w-4 h-4" />
                                Volver al Inicio
                            </Link>
                        </div>

                        <div class="mt-8 text-center border-t border-gray-100 pt-6">
                            <p class="text-xs text-slate-400">
                                Si el problema persiste, contacte a su banco o 
                                <a href="mailto:soporte@empresa.com" class="text-slate-600 underline hover:text-slate-900">escríbanos a soporte</a>.
                            </p>
                        </div>

                    </div>

                </div>

            </div>
        </div>
    </AppLayout>
</template>