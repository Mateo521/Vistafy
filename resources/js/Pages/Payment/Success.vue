<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { computed } from 'vue';
import { CheckCircleIcon, ArrowDownTrayIcon, HomeIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
    purchase: {
        type: Object,
        required: true
    }
});

// 🔥 Obtener la primera foto (o podrías iterar si hay varias)
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
    <Head title="Pago Exitoso" />

    <AppLayout>
        <div class="min-h-screen bg-gradient-to-br from-green-50 to-emerald-100 py-12">
            <div class="max-w-3xl mx-auto px-4">
                
                <!-- Success Card -->
                <div class="bg-white rounded-2xl shadow-2xl overflow-hidden">
                    
                    <!-- Header con icono de éxito -->
                    <div class="bg-gradient-to-r from-green-500 to-emerald-600 px-8 py-12 text-center">
                        <div class="inline-flex items-center justify-center w-20 h-20 bg-white rounded-full mb-4">
                            <CheckCircleIcon class="w-12 h-12 text-green-600" />
                        </div>
                        <h1 class="text-3xl md:text-4xl font-bold text-white mb-2">
                            ¡Pago Exitoso!
                        </h1>
                        <p class="text-green-100 text-lg">
                            Tu compra ha sido procesada correctamente
                        </p>
                    </div>

                    <!-- Detalles de la compra -->
                    <div class="p-8">
                        
                        <!-- ID de compra -->
                        <div class="mb-6 text-center">
                            <p class="text-sm text-gray-600 mb-1">ID de compra</p>
                            <p class="text-2xl font-bold text-gray-900">
                                #{{ purchase.id }}
                            </p>
                        </div>

                        <!-- Información de la compra -->
                        <div class="bg-gray-50 rounded-lg p-6 mb-6 space-y-3">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Email:</span>
                                <span class="font-medium text-gray-900">{{ purchase.buyer_email }}</span>
                            </div>
                            
                            <div class="flex justify-between">
                                <span class="text-gray-600">Fecha:</span>
                                <span class="font-medium text-gray-900">{{ formatDate(purchase.created_at) }}</span>
                            </div>

                            <div v-if="purchase.mp_payment_id" class="flex justify-between">
                                <span class="text-gray-600">ID de Pago MP:</span>
                                <span class="font-mono text-sm font-medium text-gray-900">{{ purchase.mp_payment_id }}</span>
                            </div>

                            <div class="pt-3 border-t border-gray-200 flex justify-between items-center">
                                <span class="text-lg font-semibold text-gray-900">Total Pagado:</span>
                                <span class="text-2xl font-bold text-green-600">
                                    {{ formatPrice(purchase.total_amount) }}
                                </span>
                            </div>
                        </div>

                        <!-- Items comprados -->
                        <div class="mb-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Fotos Compradas</h3>
                            
                            <div v-for="item in purchase.items" :key="item.id" 
                                 class="flex items-center gap-4 p-4 bg-gray-50 rounded-lg mb-3">
                                
                                <!-- Thumbnail de la foto -->
                                <div class="w-20 h-20 flex-shrink-0 rounded-lg overflow-hidden bg-gray-200">
                                    <img v-if="item.photo.thumbnail_url" 
                                         :src="item.photo.thumbnail_url"
                                         :alt="`Foto #${item.photo.unique_id}`"
                                         class="w-full h-full object-cover" />
                                    <div v-else class="w-full h-full flex items-center justify-center text-gray-400">
                                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                                d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                    </div>
                                </div>

                                <!-- Info de la foto -->
                                <div class="flex-1">
                                    <p class="font-semibold text-gray-900">
                                        Foto #{{ item.photo.unique_id }}
                                    </p>
                                    <p v-if="item.photo.event" class="text-sm text-gray-600">
                                        {{ item.photo.event.name }}
                                    </p>
                                </div>

                                <!-- Precio -->
                                <div class="text-right">
                                    <p class="font-semibold text-gray-900">
                                        {{ formatPrice(item.unit_price) }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Info de descarga -->
                        <div class="bg-blue-50 border-2 border-blue-200 rounded-lg p-6 mb-6">
                            <div class="flex items-start gap-3">
                                <svg class="w-6 h-6 text-blue-600 flex-shrink-0 mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                        d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <div>
                                    <h4 class="font-semibold text-blue-900 mb-1">
                                        📧 Revisa tu correo electrónico
                                    </h4>
                                    <p class="text-sm text-blue-800">
                                        Hemos enviado un enlace de descarga seguro a <strong>{{ purchase.buyer_email }}</strong>. 
                                        El enlace estará disponible por 7 días.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Botón de descarga (si tienes el token) -->
                        <div v-if="purchase.order_token" class="mb-6">
                            <Link :href="route('payment.download', purchase.order_token)"
                                class="flex items-center justify-center gap-3 w-full bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-4 rounded-lg font-semibold transition-all shadow-lg hover:shadow-xl">
                                <ArrowDownTrayIcon class="w-5 h-5" />
                                Descargar Ahora
                            </Link>
                        </div>

                        <!-- Acciones -->
                        <div class="flex flex-col sm:flex-row gap-3">
                            <Link :href="route('events.index')"
                                class="flex-1 flex items-center justify-center gap-2 bg-gray-100 hover:bg-gray-200 text-gray-700 px-6 py-3 rounded-lg font-semibold transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                        d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                Ver más eventos
                            </Link>

                            <Link :href="route('home')"
                                class="flex-1 flex items-center justify-center gap-2 bg-white hover:bg-gray-50 border-2 border-gray-300 text-gray-700 px-6 py-3 rounded-lg font-semibold transition-colors">
                                <HomeIcon class="w-5 h-5" />
                                Volver al inicio
                            </Link>
                        </div>
                    </div>
                </div>

                <!-- Soporte -->
                <div class="mt-8 text-center text-gray-600">
                    <p class="mb-2">¿Necesitas ayuda?</p>
                    <a href="mailto:soporte@vistafy.com" class="text-indigo-600 hover:text-indigo-700 font-medium">
                        Contacta a soporte
                    </a>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
