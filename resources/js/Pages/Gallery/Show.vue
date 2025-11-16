<template>
    <PublicLayout>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12">
                <!-- Imagen -->
                <div>
                    <div class="bg-white rounded-lg shadow-xl overflow-hidden sticky top-8">
                        <img 
                            :src="photo.watermarked_url" 
                            :alt="photo.title || photo.unique_id"
                            class="w-full h-auto"
                        >
                        
                        <!-- Marca de agua visible -->
                        <div class="bg-gray-100 px-6 py-3 border-t">
                            <p class="text-xs text-gray-500 text-center">
                                <svg class="inline w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                </svg>
                                La versión sin marca de agua se entrega después del pago
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Información -->
                <div>
                    <!-- ID y Precio -->
                    <div class="flex items-center justify-between mb-6">
                        <div>
                            <span class="text-sm text-gray-500">ID de Foto</span>
                            <p class="text-2xl font-mono font-bold text-gray-900">{{ photo.unique_id }}</p>
                        </div>
                        <div class="text-right">
                            <span class="text-sm text-gray-500">Precio</span>
                            <p class="text-4xl font-bold text-indigo-600">${{ photo.price }}</p>
                        </div>
                    </div>

                    <!-- Título y Descripción -->
                    <div class="mb-6">
                        <h1 v-if="photo.title" class="text-3xl font-bold text-gray-900 mb-4">
                            {{ photo.title }}
                        </h1>
                        <p v-if="photo.description" class="text-gray-600 leading-relaxed">
                            {{ photo.description }}
                        </p>
                    </div>

                    <!-- Fotógrafo -->
                    <div class="bg-gray-50 rounded-lg p-6 mb-6">
                        <h3 class="text-sm font-semibold text-gray-500 uppercase mb-2">Fotógrafo</h3>
                        <div class="flex items-center space-x-3">
                            <div class="w-12 h-12 bg-indigo-600 rounded-full flex items-center justify-center text-white font-bold text-xl">
                                {{ photo.photographer.business_name.charAt(0) }}
                            </div>
                            <div>
                                <p class="font-semibold text-gray-900">{{ photo.photographer.business_name }}</p>
                                <p class="text-sm text-gray-500">Región: {{ photo.photographer.region }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Eventos relacionados -->
                    <div v-if="photo.events && photo.events.length > 0" class="mb-6">
                        <h3 class="text-sm font-semibold text-gray-500 uppercase mb-3">Eventos</h3>
                        <div class="flex flex-wrap gap-2">
                            <Link 
                                v-for="event in photo.events" 
                                :key="event.id"
                                :href="route('events.show', event.slug)"
                                class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 transition"
                            >
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                {{ event.name }}
                            </Link>
                        </div>
                    </div>

                    <!-- Botones de acción -->
                    <div class="space-y-3">
                        <button 
                            @click="addToCart"
                            class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-4 rounded-lg transition transform hover:scale-105 flex items-center justify-center"
                        >
                            <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                            Agregar al Carrito
                        </button>

                        <button 
                            @click="buyNow"
                            class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-4 rounded-lg transition flex items-center justify-center"
                        >
                            <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                            Comprar Ahora
                        </button>

                        <Link 
                            :href="route('gallery.index')"
                            class="block w-full text-center border border-gray-300 text-gray-700 font-semibold py-4 rounded-lg hover:bg-gray-50 transition"
                        >
                            Volver a la Galería
                        </Link>
                    </div>

                    <!-- Info adicional -->
                    <div class="mt-8 text-sm text-gray-500 space-y-2">
                        <p>✓ Foto en alta resolución sin marca de agua</p>
                        <p>✓ Descarga inmediata después del pago</p>
                        <p>✓ Licencia para uso personal</p>
                        <p>✓ Soporte al cliente 24/7</p>
                    </div>
                </div>
            </div>
        </div>
    </PublicLayout>
</template>

<script setup>
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { Link, router } from '@inertiajs/vue3';

const props = defineProps({
    photo: Object,
});

const addToCart = () => {
    // Por ahora solo mostrar alerta, implementaremos carrito después
    alert('Foto agregada al carrito (funcionalidad en desarrollo)');
    // TODO: Implementar carrito con localStorage o backend
};

const buyNow = () => {
    alert('Redirigiendo a checkout (funcionalidad en desarrollo)');
    // TODO: Implementar checkout con Mercado Pago
};
</script>
