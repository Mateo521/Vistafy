<script setup>
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
const props = defineProps({
    photos: Object,
    events: Array,
    regions: Array,
    filters: Object,
});

const filterForm = useForm({
    search: props.filters.search || '',
    region: props.filters.region || 'all',
    event: props.filters.event || '',
    sort: props.filters.sort || 'recent',
});

const showFilters = ref(false);

const applyFilters = () => {
    filterForm.get(route('gallery.index'), {
        preserveState: true,
        preserveScroll: true,
    });
};

const clearFilters = () => {
    filterForm.reset();
    filterForm.region = 'all';
    applyFilters();
};

const changeSort = (sortValue) => {
    filterForm.sort = sortValue;
    applyFilters();
};
</script>

<template>

    <Head title="Galería de Fotos" />

    <AppLayout>

        <div class="min-h-screen bg-gray-50">


            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                <!-- Hero Section -->
                <div class="text-center mb-12">
                    <h1 class="text-4xl font-bold text-gray-900 mb-4">Galería de Fotos</h1>
                    <p class="text-xl text-gray-600 max-w-2xl mx-auto">
                        Encuentra y compra tus mejores momentos capturados profesionalmente
                    </p>
                </div>

                <!-- Search & Filters -->
                <div class="bg-white rounded-lg shadow-lg p-6 mb-8">
                    <form @submit.prevent="applyFilters">
                        <!-- Search Bar -->
                        <div class="flex gap-4 mb-4">
                            <input v-model="filterForm.search" type="text"
                                placeholder="Buscar por ID de foto, título o fotógrafo..."
                                class="flex-1 rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500" />
                            <button type="button" @click="showFilters = !showFilters"
                                :class="showFilters ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-700'"
                                class="hover:bg-indigo-700 hover:text-white px-6 py-2 rounded-lg font-semibold transition flex items-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                                </svg>
                                Filtros
                            </button>
                            <button type="submit"
                                class="bg-indigo-600 hover:bg-indigo-700 text-white px-8 py-2 rounded-lg font-semibold transition">
                                Buscar
                            </button>
                        </div>

                        <!-- Advanced Filters -->
                        <div v-show="showFilters" class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-4 border-t">
                            <!-- Region -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Región</label>
                                <select v-model="filterForm.region"
                                    class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                                    <option v-for="region in regions" :key="region.value" :value="region.value">
                                        {{ region.label }}
                                    </option>
                                </select>
                            </div>

                            <!-- Event -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Evento</label>
                                <select v-model="filterForm.event"
                                    class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="">Todos los eventos</option>
                                    <option v-for="event in events" :key="event.id" :value="event.id">
                                        {{ event.name }}
                                    </option>
                                </select>
                            </div>

                            <!-- Clear Filters -->
                            <div class="md:col-span-2 flex justify-end">
                                <button type="button" @click="clearFilters"
                                    class="text-indigo-600 hover:text-indigo-800 font-medium transition">
                                    Limpiar filtros
                                </button>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Sort Options -->
                <div class="flex justify-between items-center mb-6">
                    <div class="text-gray-600 font-medium">
                        {{ photos.total }} fotos encontradas
                    </div>
                    <div class="flex gap-2">
                        <button @click="changeSort('recent')"
                            :class="filterForm.sort === 'recent' ? 'bg-indigo-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-100'"
                            class="px-4 py-2 rounded-lg font-medium shadow transition">
                            Más Recientes
                        </button>
                        <button @click="changeSort('popular')"
                            :class="filterForm.sort === 'popular' ? 'bg-indigo-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-100'"
                            class="px-4 py-2 rounded-lg font-medium shadow transition">
                            Populares
                        </button>
                        <button @click="changeSort('price_low')"
                            :class="filterForm.sort === 'price_low' ? 'bg-indigo-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-100'"
                            class="px-4 py-2 rounded-lg font-medium shadow transition">
                            Precio: Menor
                        </button>
                        <button @click="changeSort('price_high')"
                            :class="filterForm.sort === 'price_high' ? 'bg-indigo-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-100'"
                            class="px-4 py-2 rounded-lg font-medium shadow transition">
                            Precio: Mayor
                        </button>
                    </div>
                </div>

                <!-- Photos Grid -->
                <div v-if="photos.data && photos.data.length > 0"
                    class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4 mb-8">
                    <Link v-for="photo in photos.data" :key="photo.id" :href="route('gallery.show', photo.unique_id)"
                        class="group bg-white rounded-lg shadow-md overflow-hidden hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-1">
                    <!-- Image -->
                    <div class="aspect-square overflow-hidden bg-gray-100 relative">
                        <img :src="photo.thumbnail_url" :alt="photo.unique_id"
                            class="w-full h-full object-cover group-hover:scale-110 transition duration-500"
                            @error="(e) => e.target.src = 'https://via.placeholder.com/400?text=Sin+Imagen'" />
                        <div
                            class="absolute top-2 right-2 bg-white px-2 py-1 rounded-full text-xs font-bold text-indigo-600 shadow">
                            ${{ photo.price }}
                        </div>
                    </div>

                    <!-- Info -->
                    <div class="p-3">
                        <div class="text-sm font-bold text-gray-900 text-center">{{ photo.unique_id }}</div>
                        <div class="text-xs text-gray-500 text-center mt-1">{{ photo.photographer }}</div>
                    </div>
                    </Link>
                </div>

                <!-- Empty State -->
                <div v-else class="bg-white rounded-lg shadow p-16 text-center">
                    <svg class="w-20 h-20 text-gray-400 mx-auto mb-6" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    <h3 class="text-2xl font-semibold text-gray-900 mb-3">No se encontraron fotos</h3>
                    <p class="text-gray-600 mb-6">Intenta ajustar tus filtros de búsqueda o explora otras categorías</p>
                    <button @click="clearFilters"
                        class="bg-indigo-600 hover:bg-indigo-700 text-white px-8 py-3 rounded-lg font-semibold inline-block transition">
                        Limpiar Filtros
                    </button>
                </div>

                <!-- Pagination -->
                <div v-if="photos.data && photos.data.length > 0 && photos.last_page > 1" class="mt-8">
                    <div class="flex items-center justify-center gap-2">
                        <template v-for="(link, index) in photos.links" :key="index">
                            <Link v-if="link.url" :href="link.url" v-html="link.label" :class="[
                                'px-4 py-2 rounded-lg font-medium transition',
                                link.active
                                    ? 'bg-indigo-600 text-white shadow-lg'
                                    : 'bg-white text-gray-700 hover:bg-gray-100 shadow'
                            ]" />
                            <span v-else v-html="link.label"
                                class="px-4 py-2 rounded-lg font-medium bg-gray-100 text-gray-400 cursor-not-allowed" />
                        </template>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <footer class="bg-gray-900 text-white mt-20">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                        <div>
                            <h3 class="text-xl font-bold mb-4">Vistafy</h3>
                            <p class="text-gray-400">La mejor plataforma para comprar fotos de eventos profesionales.
                            </p>
                        </div>
                        <div>
                            <h4 class="font-semibold mb-3">Enlaces</h4>
                            <div class="space-y-2">
                                <Link href="/" class="block text-gray-400 hover:text-white transition">Inicio</Link>
                                <Link href="/galeria" class="block text-gray-400 hover:text-white transition">Galería
                                </Link>
                                <Link href="/eventos" class="block text-gray-400 hover:text-white transition">Eventos
                                </Link>
                                <Link href="/fotografos" class="block text-gray-400 hover:text-white transition">
                                Fotógrafos</Link>
                            </div>
                        </div>
                        <div>
                            <h4 class="font-semibold mb-3">Contacto</h4>
                            <p class="text-gray-400">¿Eres fotógrafo? Únete a nuestra plataforma</p>
                            <Link :href="route('register')"
                                class="inline-block mt-3 bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2 rounded-lg transition">
                            Registrarse
                            </Link>
                        </div>
                    </div>
                    <div class="mt-8 pt-8 border-t border-gray-800 text-center text-gray-500">
                        © 2025 Vistafy. Todos los derechos reservados.
                    </div>
                </div>
            </footer>
        </div>

    </AppLayout>
</template>
