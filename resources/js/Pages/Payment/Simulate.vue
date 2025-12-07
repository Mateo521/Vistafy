<script setup>
import { Head } from '@inertiajs/vue3';
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import {
  CheckCircleIcon,
  XCircleIcon,
  ClockIcon,
  CreditCardIcon
} from '@heroicons/vue/24/outline';

const props = defineProps({
  purchase: Object,
});

const selectedStatus = ref('approved');
const processing = ref(false);

const simulatePayment = () => {
  if (processing.value) return;
  processing.value = true;

  router.post(route('payment.simulate.process', props.purchase.id), {
    status: selectedStatus.value
  }, {
    onFinish: () => {
      processing.value = false;
    }
  });
};


const formatPrice = (amount) => {
  // 🔥 Validar que sea un número
  const numericAmount = parseFloat(amount);
  
  if (isNaN(numericAmount)) {
    console.error('❌ Precio inválido:', amount);
    return '$0.00 ARS';
  }
  
  return new Intl.NumberFormat('es-AR', {
    style: 'currency',
    currency: 'ARS'
  }).format(numericAmount);
};
</script>

<template>
  <Head title="Simulación de Pago" />

  <AppLayout>
    <div class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100 py-12">
      <div class="max-w-2xl mx-auto px-4">
      
        <!-- Info de Simulación -->
        <div class="mb-6 rounded-lg bg-yellow-50 border-2 border-yellow-200 p-4">
          <div class="flex items-center gap-3">
            <svg class="w-6 h-6 text-yellow-600 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 
                3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42
                c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0
                11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1
                1 0 00-1-1z" clip-rule="evenodd"/>
            </svg>
            <div>
              <h3 class="font-bold text-yellow-900">🧪 Modo Simulación</h3>
              <p class="text-sm text-yellow-800">
                Esto es un entorno de prueba. Ningún pago real será procesado.
              </p>
            </div>
          </div>
        </div>

        <!-- Tarjeta con Detalles -->
        <div class="bg-white rounded-2xl shadow-2xl overflow-hidden">
          
          <div class="bg-gradient-to-r from-indigo-600 to-purple-600 px-8 py-6 text-white">
            <div class="flex items-center justify-center mb-4">
              <CreditCardIcon class="w-12 h-12" />
            </div>
            <h1 class="text-3xl font-bold text-center">Simulador de Pago</h1>
            <p class="text-indigo-100 text-center mt-2">Mercado Pago — Modo Desarrollo</p>
          </div>

          <!-- Detalles de Compra -->
          <div class="px-8 py-6 border-b">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Detalles de la compra</h2>
            <div class="space-y-3">
              <div class="flex justify-between">
                <span class="text-gray-600">ID de compra:</span>
                <span class="font-mono text-sm font-semibold">
                  #{{ purchase.id }}
                </span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-600">Email:</span>
                <span class="font-medium">{{ purchase.buyer_email }}</span>
              </div>
              <div v-if="purchase.items && purchase.items.length > 0">
                <div v-for="item in purchase.items" :key="item.id" class="flex justify-between items-start">
                  <div>
                    <p class="font-medium text-gray-900">Foto #{{ item.photo.unique_id }}</p>
                    <p v-if="item.photo.event" class="text-sm text-gray-500">{{ item.photo.event.name }}</p>
                  </div>
                  <span class="font-semibold">{{ formatPrice(item.unit_price) }}</span>
                </div>
              </div>
              <div class="flex justify-between items-center pt-3 border-t-2 border-gray-200">
                <span class="text-lg font-bold text-gray-900">Total:</span>
                <span class="text-2xl font-bold text-indigo-600">
                  {{ formatPrice(purchase.total_amount) }}
                </span>
              </div>
            </div>
          </div>

          <!-- Selección de Estado -->
          <div class="px-8 py-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Selecciona el resultado del pago</h2>
            <div class="space-y-3">
              
              <label class="relative flex items-center p-4 border-2 rounded-lg cursor-pointer hover:bg-green-50"
                     :class="selectedStatus === 'approved' ? 'border-green-500 bg-green-50' : 'border-gray-200'">
                <input type="radio" v-model="selectedStatus" value="approved" class="sr-only" />
                <CheckCircleIcon class="w-6 h-6 text-green-600 mr-3 flex-shrink-0" />
                <div class="flex-1">
                  <div class="font-semibold text-gray-900">✅ Pago Aprobado</div>
                  <div class="text-sm text-gray-600">El pago fue procesado exitosamente</div>
                </div>
                <div v-if="selectedStatus === 'approved'" class="w-5 h-5 rounded-full bg-green-500 flex items-center justify-center">
                  <svg class="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0
                      01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1
                      0 011.414 0z" clip-rule="evenodd"/>
                  </svg>
                </div>
              </label>

              <label class="relative flex items-center p-4 border-2 rounded-lg cursor-pointer hover:bg-red-50"
                     :class="selectedStatus === 'rejected' ? 'border-red-500 bg-red-50' : 'border-gray-200'">
                <input type="radio" v-model="selectedStatus" value="rejected" class="sr-only" />
                <XCircleIcon class="w-6 h-6 text-red-600 mr-3 flex-shrink-0" />
                <div class="flex-1">
                  <div class="font-semibold text-gray-900">❌ Pago Rechazado</div>
                  <div class="text-sm text-gray-600">El pago fue rechazado por el procesador</div>
                </div>
                <div v-if="selectedStatus === 'rejected'" class="w-5 h-5 rounded-full bg-red-500 flex items-center justify-center">
                  <svg class="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0
                      01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1
                      0 011.414 0z" clip-rule="evenodd"/>
                  </svg>
                </div>
              </label>

              <label class="relative flex items-center p-4 border-2 rounded-lg cursor-pointer hover:bg-yellow-50"
                     :class="selectedStatus === 'pending' ? 'border-yellow-500 bg-yellow-50' : 'border-gray-200'">
                <input type="radio" v-model="selectedStatus" value="pending" class="sr-only" />
                <ClockIcon class="w-6 h-6 text-yellow-600 mr-3 flex-shrink-0" />
                <div class="flex-1">
                  <div class="font-semibold text-gray-900">⏳ Pago Pendiente</div>
                  <div class="text-sm text-gray-600">El pago está en proceso de validación</div>
                </div>
                <div v-if="selectedStatus === 'pending'" class="w-5 h-5 rounded-full bg-yellow-500 flex items-center justify-center">
                  <svg class="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0
                      01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1
                      0 011.414 0z" clip-rule="evenodd"/>
                  </svg>
                </div>
              </label>

            </div>
          </div>

          <!-- Botón de acción -->
          <div class="px-8 py-6 bg-gray-50">
            <button 
              @click="simulatePayment"
              :disabled="processing"
              :class="[
                'w-full rounded-lg px-6 py-4 font-bold text-white transition-all text-lg',
                processing 
                  ? 'bg-gray-400 cursor-not-allowed' 
                  : 'bg-indigo-600 hover:bg-indigo-700 shadow-lg hover:shadow-xl transform hover:scale-105'
              ]"
            >
              <span v-if="processing" class="flex items-center justify-center gap-2">
                <svg class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373
                    0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3
                    7.938l3-2.647z">
                  </path>
                </svg>
                Procesando...
              </span>
              <span v-else>🚀 Simular Pago</span>
            </button>
          </div>

        </div>
      </div>
    </div>
  </AppLayout>
</template>
