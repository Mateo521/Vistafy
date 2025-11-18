<template>
    <div class="min-h-screen bg-gray-50 flex items-center justify-center p-4">
        <div class="max-w-md w-full bg-white rounded-lg shadow-lg p-8 text-center">
            <!-- Ícono de éxito -->
            <div class="mb-6">
                <div class="mx-auto w-16 h-16 bg-green-100 rounded-full flex items-center justify-center">
                    <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                </div>
            </div>

            <!-- Título -->
            <h1 class="text-2xl font-bold text-gray-900 mb-2">
                ¡Pago Exitoso!
            </h1>
            
            <p class="text-gray-600 mb-6">
                Tu compra se ha procesado correctamente.
            </p>

            <!-- Información de la compra -->
            <div v-if="purchase" class="bg-gray-50 rounded-lg p-4 mb-6 text-left">
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
                        <span class="text-green-600 font-medium">Aprobado</span>
                    </div>
                </div>
            </div>

            <!-- Botón de descarga -->
            <a 
                v-if="purchase && purchase.download_token"
                :href="`/pago/descargar/${purchase.download_token}`"
                class="inline-block w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-lg transition-colors mb-3"
            >
                📥 Descargar Foto
            </a>

            <!-- Botón volver -->
            <Link 
                href="/"
                class="inline-block w-full bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold py-3 px-6 rounded-lg transition-colors"
            >
                🏠 Volver al Inicio
            </Link>
        </div>
    </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    purchase: Object,
});
</script>
