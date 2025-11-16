<template>
    <PublicLayout>
        <div class="bg-gray-100 py-12">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="mb-8">
                    <h1 class="text-4xl font-bold text-gray-900 mb-2">Galería Completa</h1>
                    <p class="text-gray-600">Explora todas nuestras fotografías disponibles</p>
                </div>

                <!-- Filtros -->
                <div class="bg-white rounded-lg shadow-md p-6 mb-8">
                    <form @submit.prevent="applyFilters" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <!-- Búsqueda -->
                        <div>
                            <input 
                                v-model="filterForm.search"
                                type="text"
                                placeholder="Buscar por ID o palabra clave..."
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                            >
                        </div>

                        <!-- Región -->
                        <div>
                            <select 
                                v-model="filterForm.region"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                            >
                                <option v-for="region in regions" :key="region.value" :value="region.value">
                                    {{ region.label }}
                                </option>
                            </select>
                        </div>

                        <!-- Ordenar -->
                        <div>
                            <select 
                                v-model="filterForm.sort"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                            >
                                <option value="recent">Más Recientes</option>
                                <option value="popular">Más Populares</option>
                                <option value="price_low">Precio: Menor a Mayor</option>
                                <option value="price_high">Precio: Mayor a Menor</option>
                            </select>
                        </div>

                        <!-- Botón -->
                        <div class="flex gap-2">
                            <button 
                                type="submit"
                                class="flex-1 bg-indigo-600 text-white px-6 py-2 rounded-lg hover:bg-indigo-700 transition font-semibold"
                            >
                                Filtrar
                            </button>
                            <button 
                                type="button"
                                @click="resetFilters"
                                class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition"
                            >
                                Limpiar
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Grid de fotos -->
                <div v-if="photos.data.length > 0" class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4 mb-8">
                    <Link 
                        v-for="photo in photos.data" 
                        :key="photo.id"
                        :href="route('gallery.show', photo.unique_id)"
                        class="group relative aspect-square rounded-lg overflow-hidden shadow-md hover:shadow-2xl transition-all duration-300"
                    >
                        <img 
                            :src="photo.thumbnail_url" 
                            :alt="photo.unique_id"
                            class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500"
                        >
                        <div class="absolute inset-0 bg-gradient-to-t from-black via-transparent to-transparent opacity-0 group-hover:opacity-100 transition">
                            <div class="absolute bottom-0 left-0 right-0 p-3">
                                <p class="text-white text-xs font-mono mb-1">{{ photo.unique_id }}</p>
                                <p class="text-white text-sm font-bold">${{ photo.price }}</p>
                            </div>
                        </div>
                    </Link>
                </div>

                <!-- Sin resultados -->
                <div v-else class="text-center py-20">
                    <svg class="mx-auto h-24 w-24 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    <h3 class="text-2xl font-bold text-gray-900 mb-2">No se encontraron fotos</h3>
                    <p class="text-gray-600 mb-6">Intenta ajustar los filtros de búsqueda</p>
                    <button 
                        @click="resetFilters"
                        class="bg-indigo-600 text-white px-6 py-3 rounded-lg hover:bg-indigo-700 transition font-semibold"
                    >
                        Ver Todas las Fotos
                    </button>
                </div>

                <!-- Paginación -->
                <div v-if="photos.data.length > 0" class="flex justify-center">
                    <nav class="flex items-center space-x-2">
                        <Link 
                            v-for="link in photos.links" 
                            :key="link.label"
                            :href="link.url"
                            :class="[
                                'px-4 py-2 rounded-lg font-semibold transition',
                                link.active 
                                    ? 'bg-indigo-600 text-white' 
                                    : link.url 
                                        ? 'bg-white text-gray-700 hover:bg-gray-100' 
                                        : 'bg-gray-100 text-gray-400 cursor-not-allowed'
                            ]"
                            v-html="link.label"
                        />
                    </nav>
                </div>
            </div>
        </div>
    </PublicLayout>
</template>

<script setup>
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    photos: Object,
    events: Array,
    regions: Array,
    filters: Object,
});

const filterForm = ref({
    search: props.filters.search || '',
    region: props.filters.region || 'all',
    event: props.filters.event || '',
    sort: props.filters.sort || 'recent',
});

const applyFilters = () => {
    router.get(route('gallery.index'), filterForm.value, {
        preserveState: true,
        preserveScroll: true,
    });
};

const resetFilters = () => {
    filterForm.value = {
        search: '',
        region: 'all',
        event: '',
        sort: 'recent',
    };
    router.get(route('gallery.index'));
};
</script>
