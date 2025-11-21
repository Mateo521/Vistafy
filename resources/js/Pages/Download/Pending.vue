<template>
    <div class="min-h-screen bg-gradient-to-br from-yellow-50 to-orange-100 flex items-center justify-center p-6">
        <div class="max-w-md w-full">
            <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
                <!-- Header -->
                <div class="bg-gradient-to-r from-yellow-500 to-orange-500 p-8 text-white text-center">
                    <div class="w-20 h-20 bg-white rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-10 h-10 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <h1 class="text-3xl font-bold mb-2">Pago Pendiente</h1>
                    <p class="text-yellow-100">Tu pago está siendo procesado</p>
                </div>

                <!-- Contenido -->
                <div class="p-8">
                    <div class="text-center mb-6">
                        <p class="text-gray-600 mb-4">
                            Tu compra está en proceso de confirmación. Recibirás un email cuando esté lista para
                            descargar.
                        </p>

                        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 text-left">
                            <div class="space-y-2 text-sm">
                                <div class="flex justify-between">
                                    <span class="text-yellow-800 font-medium">Estado:</span>
                                    <span
                                        class="px-2 py-1 bg-yellow-200 text-yellow-900 rounded text-xs font-semibold uppercase">
                                        {{ purchase.status }}
                                    </span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-yellow-800 font-medium">Compra ID:</span>
                                    <span class="font-mono text-yellow-900">#{{ purchase.id }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-yellow-800 font-medium">Email:</span>
                                    <span class="text-yellow-900 text-xs">{{ purchase.buyer_email }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Información -->
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                        <div class="flex items-start space-x-3">
                            <svg class="w-5 h-5 text-blue-600 mt-0.5 flex-shrink-0" fill="currentColor"
                                viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                                    clip-rule="evenodd"></path>
                            </svg>
                            <div class="text-sm text-blue-800">
                                <p class="font-semibold mb-1">¿Qué sigue?</p>
                                <ul class="list-disc list-inside space-y-1 text-blue-700">
                                    <li>El pago puede tardar unos minutos en confirmarse</li>
                                    <li>Esta página se actualizará automáticamente</li>
                                    <li>Recibirás un email cuando esté listo</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- Contador de actualización -->
                    <div class="text-center mb-4">
                        <div class="inline-flex items-center space-x-2 text-sm text-gray-600">
                            <svg class="w-4 h-4 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15">
                                </path>
                            </svg>
                            <span>Verificando estado... Próxima actualización en {{ countdown }}s</span>
                        </div>
                    </div>

                    <!-- Botón refrescar manual -->
                    <button @click="checkStatus" :disabled="isChecking"
                        class="block w-full bg-blue-600 hover:bg-blue-700 disabled:bg-gray-400 text-white font-semibold py-3 px-6 rounded-lg transition text-center mb-4">
                        <div class="flex items-center justify-center space-x-2">
                            <svg v-if="isChecking" class="w-5 h-5 animate-spin" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15">
                                </path>
                            </svg>
                            <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15">
                                </path>
                            </svg>
                            <span>{{ isChecking ? 'Verificando...' : 'Verificar Ahora' }}</span>
                        </div>
                    </button>

                    <!-- Volver -->
                    <Link href="/" class="block text-center text-blue-600 hover:text-blue-800 font-medium">
                    ← Volver al inicio
                    </Link>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { Link, router } from '@inertiajs/vue3';

const props = defineProps({
    purchase: {
        type: Object,
        required: true,
    },
});

const isChecking = ref(false);
const countdown = ref(10);
let pollingInterval = null;
let countdownInterval = null;

const checkStatus = async () => {
    if (isChecking.value) return;

    isChecking.value = true;

    try {
        // Recargar la página actual (Inertia recargará los datos)
        router.reload({
            only: ['purchase'],
            onSuccess: (page) => {
                // Si el estado cambió a completed, Inertia automáticamente
                // renderizará Download/Show.vue en lugar de Pending.vue
                console.log('Estado verificado:', page.props.purchase?.status);
            },
        });
    } catch (error) {
        console.error('Error verificando estado:', error);
    } finally {
        setTimeout(() => {
            isChecking.value = false;
        }, 1000);
    }
};

const startPolling = () => {
    // Verificar cada 10 segundos
    pollingInterval = setInterval(() => {
        checkStatus();
        countdown.value = 10; // Reiniciar contador
    }, 10000);

    // Actualizar countdown cada segundo
    countdownInterval = setInterval(() => {
        if (countdown.value > 0) {
            countdown.value--;
        } else {
            countdown.value = 10;
        }
    }, 1000);
};

const stopPolling = () => {
    if (pollingInterval) {
        clearInterval(pollingInterval);
        pollingInterval = null;
    }
    if (countdownInterval) {
        clearInterval(countdownInterval);
        countdownInterval = null;
    }
};

onMounted(() => {
    console.log('Iniciando polling automático');
    startPolling();
});

onUnmounted(() => {
    console.log('Deteniendo polling');
    stopPolling();
});
</script>
