<script setup>
import { Link, router } from '@inertiajs/vue3';

const props = defineProps({
    purchase: Object,
});

// 🔧 CORREGIDO - Usar unique_id de la foto
const backToPhoto = () => {
    if (props.purchase?.photo?.unique_id) {
        router.visit(`/galeria/${props.purchase.photo.unique_id}`);
    } else {
        router.visit('/');
    }
};
</script>

<template>
    <div class="min-h-screen bg-gray-50 flex items-center justify-center p-4">
        <div class="max-w-md w-full bg-white rounded-lg shadow-lg p-8 text-center">
            <!-- Ícono de pendiente -->
            <div class="mb-6">
                <div class="mx-auto w-16 h-16 bg-yellow-100 rounded-full flex items-center justify-center">
                    <svg class="w-8 h-8 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>

            <!-- Título -->
            <h1 class="text-2xl font-bold text-gray-900 mb-2">
                Pago Pendiente
            </h1>
            
            <p class="text-gray-600 mb-6">
                Tu pago está siendo procesado. Te notificaremos cuando se complete.
            </p>

            <!-- Información -->
            <div v-if="purchase" class="bg-yellow-50 rounded-lg p-4 mb-6 text-left">
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-600">ID de Compra:</span>
                        <span class="font-medium">#{{ purchase.id }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Monto:</span>
                        <span class="font-medium">${{ purchase.amount }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Estado:</span>
                        <span class="text-yellow-600 font-medium">En proceso</span>
                    </div>
                </div>
            </div>

            <!-- 🔧 Botón para volver a la foto -->
            <button
                v-if="purchase?.photo?.unique_id"
                @click="backToPhoto"
                class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-lg transition-colors mb-3"
            >
                🔄 Ver la Foto Nuevamente
            </button>

            <!-- Botón volver al inicio -->
            <Link 
                href="/"
                class="inline-block w-full bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold py-3 px-6 rounded-lg transition-colors"
            >
                🏠 Volver al Inicio
            </Link>
        </div>
    </div>
</template>
