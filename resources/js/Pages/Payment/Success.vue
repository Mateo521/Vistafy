<template>
  <div class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100 flex items-center justify-center p-6">
    <div class="max-w-2xl w-full">
      <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
        <!-- Header -->
        <div class="bg-gradient-to-r from-green-500 to-emerald-600 p-8 text-white text-center">
          <div class="w-20 h-20 bg-white rounded-full flex items-center justify-center mx-auto mb-4">
            <svg class="w-10 h-10 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
            </svg>
          </div>
          <h1 class="text-3xl font-bold mb-2">¡Pago Exitoso!</h1>
          <p class="text-green-100">Tu foto está lista para descargar</p>
        </div>

        <!-- Contenido -->
        <div class="p-8">
          <!-- Preview -->
          <div v-if="photo" class="mb-6">
            <div class="relative rounded-lg overflow-hidden shadow-lg">
              <img :src="photoUrl" :alt="`Foto #${photo.id}`" class="w-full h-auto">
              <div class="absolute top-4 right-4 bg-black/50 backdrop-blur-sm text-white px-3 py-1 rounded-full text-sm">
                Foto #{{ photo.id }}
              </div>
            </div>
          </div>

          <!-- Botón descarga DIRECTO -->
          <a :href="`/pago/descargar/${purchase.download_token}`"
             download
             class="block w-full bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-semibold py-4 px-6 rounded-lg transition duration-200 text-center shadow-lg hover:shadow-xl mb-6">
            <div class="flex items-center justify-center space-x-3">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
              </svg>
              <span>Descargar Foto en Alta Resolución</span>
            </div>
          </a>

          <!-- Detalles (colapsable/opcional) -->
          <details class="bg-gray-50 rounded-lg p-4 mb-6">
            <summary class="font-semibold text-gray-800 cursor-pointer">Ver detalles de la compra</summary>
            <div class="mt-4 space-y-2 text-sm">
              <div class="flex justify-between">
                <span class="text-gray-600">Compra ID:</span>
                <span class="font-mono text-gray-800">#{{ purchase.id }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-600">Email:</span>
                <span class="text-gray-800 text-xs">{{ purchase.buyer_email }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-600">Monto:</span>
                <span class="font-semibold text-gray-800">${{ formatPrice(purchase.amount) }} ARS</span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-600">Fecha:</span>
                <span class="text-gray-800 text-xs">{{ formatDate(purchase.created_at) }}</span>
              </div>
            </div>
          </details>

          <!-- Volver -->
          <div class="text-center">
            <Link href="/" class="text-blue-600 hover:text-blue-800 font-medium">
              ← Volver al inicio
            </Link>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
  purchase: {
    type: Object,
    required: true,
  },
  photo: {
    type: Object,
    required: true,
  },
});

const photoUrl = computed(() => {
  return `/storage/${props.photo.path}`;
});

const formatPrice = (price) => {
  return new Intl.NumberFormat('es-AR', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  }).format(price);
};

const formatDate = (date) => {
  return new Date(date).toLocaleString('es-AR', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  });
};
</script>
