<template>
    <div class="min-h-screen bg-gray-50 flex items-center justify-center p-4">
        <div class="max-w-md w-full bg-white rounded-lg shadow-lg p-8 text-center">
            <!-- Ícono de error -->
            <div class="mb-6">
                <div class="mx-auto w-16 h-16 bg-red-100 rounded-full flex items-center justify-center">
                    <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                        </path>
                    </svg>
                </div>
            </div>

            <!-- Título -->
            <h1 class="text-2xl font-bold text-gray-900 mb-2">
                Pago Rechazado
            </h1>

            <p class="text-gray-600 mb-6">
                Tu pago no pudo ser procesado. Por favor intenta nuevamente.
            </p>

            <!-- Información del error -->
            <div v-if="purchase" class="bg-red-50 rounded-lg p-4 mb-6 text-left">
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-600">ID de Compra:</span>
                        <span class="font-medium">#{{ purchase.id }}</span>
                    </div>
                    <div v-if="purchase.rejection_reason" class="flex justify-between">
                        <span class="text-gray-600">Motivo:</span>
                        <span class="font-medium text-red-600">{{ purchase.rejection_reason }}</span>
                    </div>
                </div>
            </div>

            <!-- Botones -->
            <button @click="retryPayment"
                class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-lg transition-colors mb-3">
                 Intentar Nuevamente
            </button>

            <Link href="/"
                class="inline-block w-full bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold py-3 px-6 rounded-lg transition-colors">
             Volver al Inicio
            </Link>
        </div>
    </div>
</template>

<script setup>
import { Link, router } from '@inertiajs/vue3';

const props = defineProps({
    purchase: Object,
});


const retryPayment = () => {
    if (props.purchase?.photo?.unique_id) {
        router.visit(`/galeria/${props.purchase.photo.unique_id}`);
    } else {
        router.visit('/');
    }
};
</script>
