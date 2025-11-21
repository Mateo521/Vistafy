<template>
    <div class="min-h-screen bg-gray-50 flex items-center justify-center p-4">
        <div class="max-w-md w-full bg-white rounded-lg shadow-lg p-8 text-center">
            <!-- Loading spinner mientras verifica -->
            <div v-if="checking" class="mb-6">
                <div class="mx-auto w-16 h-16 flex items-center justify-center">
                    <svg class="animate-spin h-12 w-12 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </div>
            </div>

            <!-- Ícono de éxito -->
            <div v-else class="mb-6">
                <div class="mx-auto w-16 h-16 bg-green-100 rounded-full flex items-center justify-center">
                    <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                </div>
            </div>

            <!-- Título -->
            <h1 class="text-2xl font-bold text-gray-900 mb-2">
                {{ checking ? 'Verificando tu pago...' : '¡Pago Exitoso!' }}
            </h1>
            
            <p class="text-gray-600 mb-6">
                {{ checking 
                    ? 'Por favor espera mientras confirmamos tu pago. Esto puede tardar unos segundos.' 
                    : 'Tu compra se ha procesado correctamente.'
                }}
            </p>

            <!-- Progress indicator -->
            <div v-if="checking" class="mb-6">
                <div class="text-sm text-gray-500">
                    Verificando... (Intento {{ attempts }} de {{ maxAttempts }})
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2 mt-2">
                    <div 
                        class="bg-blue-600 h-2 rounded-full transition-all duration-300"
                        :style="{ width: `${(attempts / maxAttempts) * 100}%` }"
                    ></div>
                </div>
            </div>

            <!-- Alerta si tarda mucho -->
            <div v-if="!checking && timedOut" class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
                <p class="text-sm text-yellow-800">
                    ⚠️ El pago está tardando más de lo esperado en confirmarse.
                    Por favor, revisa tu email o contacta con soporte.
                </p>
            </div>

            <!-- Información de la compra -->
            <div v-if="purchase && !checking" class="bg-gray-50 rounded-lg p-4 mb-6 text-left">
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
                        <span :class="statusClass">{{ statusText }}</span>
                    </div>
                </div>
            </div>

            <!-- Botón de descarga (solo si está approved) -->
            <a 
                v-if="purchase && currentStatus === 'approved' && purchase.download_token"
                :href="`/downloads/${purchase.download_token}`"
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
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    purchase: Object,
});

const checking = ref(props.purchase?.status !== 'approved');
const currentStatus = ref(props.purchase?.status || 'pending');
const attempts = ref(0);
const maxAttempts = ref(20); // 20 intentos × 3 segundos = 1 minuto
const timedOut = ref(false);
let intervalId = null;

const statusText = computed(() => {
    const statuses = {
        'approved': 'Aprobado',
        'pending': 'Pendiente',
        'rejected': 'Rechazado',
        'cancelled': 'Cancelado',
    };
    return statuses[currentStatus.value] || 'Pendiente';
});

const statusClass = computed(() => {
    const classes = {
        'approved': 'text-green-600 font-medium',
        'pending': 'text-yellow-600 font-medium',
        'rejected': 'text-red-600 font-medium',
        'cancelled': 'text-gray-600 font-medium',
    };
    return classes[currentStatus.value] || 'text-gray-600 font-medium';
});

const checkPaymentStatus = async () => {
    if (!props.purchase?.id) return;

    try {
        console.log(`🔍 Verificando pago... (intento ${attempts.value + 1})`);
        
        const response = await fetch(`/purchases/${props.purchase.id}/check-status`);
        const data = await response.json();

        console.log('✅ Estado del pago:', data);

        currentStatus.value = data.status;

        if (data.status === 'approved') {
            checking.value = false;
            
            if (intervalId) {
                clearInterval(intervalId);
            }

            // Redirigir automáticamente a la página de descarga
            if (data.download_url) {
                console.log('🎉 Pago aprobado! Redirigiendo a descarga...');
                setTimeout(() => {
                    window.location.href = data.download_url;
                }, 1000);
            }
        } else if (attempts.value >= maxAttempts.value) {
            checking.value = false;
            timedOut.value = true;
            
            if (intervalId) {
                clearInterval(intervalId);
            }
            
            console.warn('⚠️ Tiempo de espera agotado');
        } else {
            attempts.value++;
        }
    } catch (error) {
        console.error('❌ Error verificando pago:', error);
        attempts.value++;
        
        if (attempts.value >= maxAttempts.value) {
            checking.value = false;
            timedOut.value = true;
            
            if (intervalId) {
                clearInterval(intervalId);
            }
        }
    }
};

onMounted(() => {
    if (checking.value) {
        // Primera verificación inmediata
        checkPaymentStatus();
        
        // Continuar verificando cada 3 segundos
        intervalId = setInterval(checkPaymentStatus, 3000);
    }
});

onUnmounted(() => {
    if (intervalId) {
        clearInterval(intervalId);
    }
});
</script>
