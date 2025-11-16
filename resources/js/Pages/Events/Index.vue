<template>
    <PublicLayout>
        <!-- Hero -->
        <div class="bg-gradient-to-r from-purple-600 to-indigo-600 text-white py-16">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                <h1 class="text-4xl md:text-5xl font-bold mb-4">
                    Eventos Fotográficos
                </h1>
                <p class="text-xl">
                    Explora galerías de eventos especiales
                </p>
            </div>
        </div>

        <!-- Lista de eventos -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div v-if="events.data.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <div 
                    v-for="event in events.data" 
                    :key="event.id"
                    class="bg-white rounded-lg shadow-lg overflow-hidden hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-2"
                >
                    <!-- Imagen de portada (primera foto del evento) -->
                    <div class="h-48 bg-gradient-to-br from-purple-400 to-indigo-500 flex items-center justify-center text-white">
                        <div class="text-center">
                            <svg class="w-16 h-16 mx-auto mb-2 opacity-75" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            <p class="text-2xl font-bold">{{ event.photos_count }} fotos</p>
                        </div>
                    </div>

                    <!-- Info del evento -->
                    <div class="p-6">
                        <h3 class="text-xl font-bold text-gray-900 mb-2">
                            {{ event.name }}
                        </h3>

                        <p v-if="event.description" class="text-gray-600 mb-4 line-clamp-2">
                            {{ event.description }}
                        </p>

                        <div class="flex items-center text-sm text-gray-500 mb-4 space-x-4">
                            <div v-if="event.event_date" class="flex items-center">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                {{ formatDate(event.event_date) }}
                            </div>

                            <div v-if="event.location" class="flex items-center">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                {{ event.location }}
                            </div>
                        </div>

                        <Link 
                            :href="route('events.show', event.slug)"
                            class="block w-full bg-indigo-600 hover:bg-indigo-700 text-white text-center py-3 rounded-lg transition font-semibold"
                        >
                            Ver Galería
                        </Link>
                    </div>
                </div>
            </div>

            <!-- Sin eventos -->
            <div v-else class="text-center py-20">
                <svg class="mx-auto h-24 w-24 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                <h3 class="mt-4 text-xl font-semibold text-gray-900">No hay eventos disponibles</h3>
                <p class="mt-2 text-gray-600">Los eventos fotográficos aparecerán aquí</p>
            </div>

            <!-- Paginación -->
            <div v-if="events.data.length > 0" class="mt-12">
                <Pagination :links="events.links" />
            </div>
        </div>
    </PublicLayout>
</template>

<script setup>
import PublicLayout from '@/Layouts/PublicLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import { Link } from '@inertiajs/vue3';

defineProps({
    events: Object,
});

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('es-ES', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
};
</script>
