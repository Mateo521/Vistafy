<template>
    <PublicLayout>
        <!-- Hero Section -->
        <div class="bg-indigo-600 text-white py-16">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                <h1 class="text-4xl md:text-5xl font-bold mb-4">
                    Encuentra la Fotografía Perfecta
                </h1>
                <p class="text-xl mb-8">
                    Miles de fotos profesionales listas para descargar
                </p>

                <!-- Barra de búsqueda -->
                <form @submit.prevent="search" class="max-w-2xl mx-auto">
                    <div class="flex gap-2">
                        <input 
                            v-model="searchQuery"
                            type="text" 
                            placeholder="Buscar por ID, título o descripción..."
                            class="flex-1 px-4 py-3 rounded-lg text-gray-900 focus:ring-2 focus:ring-indigo-300"
                        >
                        <button 
                            type="submit"
                            class="bg-indigo-800 hover:bg-indigo-900 px-6 py-3 rounded-lg font-semibold transition"
                        >
                            Buscar
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Gallery Grid -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div v-if="photos.data.length > 0" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                <div 
                    v-for="photo in photos.data" 
                    :key="photo.id"
                    class="group relative bg-white rounded-lg shadow-md overflow-hidden hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1"
                >
                    <!-- Imagen -->
                    <Link :href="route('gallery.show', photo.unique_id)">
                        <div class="aspect-square overflow-hidden bg-gray-200">
                            <img 
                                :src="photo.thumbnail_url" 
                                :alt="photo.title || photo.unique_id"
                                class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-300"
                                loading="lazy"
                            >
                        </div>
                    </Link>

                    <!-- Info -->
                    <div class="p-4">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xs font-mono text-gray-500">{{ photo.unique_id }}</span>
                            <span class="text-lg font-bold text-indigo-600">${{ photo.price }}</span>
                        </div>
                        
                        <h3 v-if="photo.title" class="font-semibold text-gray-800 truncate mb-1">
                            {{ photo.title }}
                        </h3>
                        
                        <p class="text-sm text-gray-600 truncate">
                            por {{ photo.photographer.business_name }}
                        </p>

                        <!-- Botón de acción -->
                        <Link 
                            :href="route('gallery.show', photo.unique_id)"
                            class="mt-3 block w-full bg-indigo-600 hover:bg-indigo-700 text-white text-center py-2 rounded-lg transition font-medium"
                        >
                            Ver Detalles
                        </Link>
                    </div>
                </div>
            </div>

            <!-- Sin resultados -->
            <div v-else class="text-center py-20">
                <svg class="mx-auto h-24 w-24 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                <h3 class="mt-4 text-xl font-semibold text-gray-900">No hay fotos disponibles</h3>
                <p class="mt-2 text-gray-600">Vuelve pronto para ver nuevas fotografías</p>
            </div>

            <!-- Paginación -->
            <div v-if="photos.data.length > 0" class="mt-12">
                <Pagination :links="photos.links" />
            </div>
        </div>
    </PublicLayout>
</template>

<script setup>
import PublicLayout from '@/Layouts/PublicLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import { Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

defineProps({
    photos: Object,
});

const searchQuery = ref('');

const search = () => {
    if (searchQuery.value.trim()) {
        router.visit(route('gallery.search', { q: searchQuery.value }));
    }
};
</script>
