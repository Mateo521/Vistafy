<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    photos: Object,
    events: Array,
    filters: Object,
    stats: Object,
});

const searchForm = useForm({
    search: props.filters.search || '',
    event_id: props.filters.event_id || '',
    is_active: props.filters.is_active || '',
});

const search = () => {
    searchForm.get(route('photographer.photos.index'), {
        preserveState: true,
        preserveScroll: true,
    });
};

const clearFilters = () => {
    searchForm.reset();
    search();
};

const deletePhoto = (photoId) => {
    if (confirm('¿Estás seguro de eliminar esta foto?')) {
        router.delete(route('photographer.photos.destroy', photoId), {
            preserveScroll: true,
        });
    }
};

const toggleActive = (photoId, currentStatus) => {
    router.patch(route('photographer.photos.update', photoId), {
        is_active: !currentStatus,
    }, {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Mis Fotos" />

    <AuthenticatedLayout>
        <div class="py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="flex justify-between items-center mb-6">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">Mis Fotos</h1>
                        <p class="text-gray-600 mt-1">Gestiona tu galería de fotos</p>
                    </div>
                    <Link
                        :href="route('photographer.photos.create')"
                        class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-3 rounded-lg font-semibold inline-flex items-center gap-2 transition"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Subir Fotos
                    </Link>
                </div>

                <!-- Stats -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="text-sm text-gray-600">Total de Fotos</div>
                        <div class="text-3xl font-bold text-gray-900">{{ stats.total }}</div>
                    </div>
                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="text-sm text-gray-600">Activas</div>
                        <div class="text-3xl font-bold text-green-600">{{ stats.active }}</div>
                    </div>
                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="text-sm text-gray-600">Inactivas</div>
                        <div class="text-3xl font-bold text-orange-600">{{ stats.inactive }}</div>
                    </div>
                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="text-sm text-gray-600">Descargas Totales</div>
                        <div class="text-3xl font-bold text-indigo-600">{{ stats.total_downloads }}</div>
                    </div>
                </div>

                <!-- Filters -->
                <div class="bg-white rounded-lg shadow p-6 mb-6">
                    <form @submit.prevent="search" class="grid grid-cols-1 md:grid-cols-5 gap-4">
                        <!-- Search -->
                        <div class="md:col-span-2">
                            <input
                                v-model="searchForm.search"
                                type="text"
                                placeholder="Buscar por título, ID o descripción..."
                                class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                            />
                        </div>

                        <!-- Event Filter -->
                        <div>
                            <select
                                v-model="searchForm.event_id"
                                class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                            >
                                <option value="">Todos los eventos</option>
                                <option v-for="event in events" :key="event.id" :value="event.id">
                                    {{ event.name }}
                                </option>
                            </select>
                        </div>

                        <!-- Status Filter -->
                        <div>
                            <select
                                v-model="searchForm.is_active"
                                class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                            >
                                <option value="">Todos los estados</option>
                                <option value="1">Activas</option>
                                <option value="0">Inactivas</option>
                            </select>
                        </div>

                        <!-- Buttons -->
                        <div class="flex gap-2">
                            <button
                                type="submit"
                                class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg font-semibold transition"
                            >
                                Buscar
                            </button>
                            <button
                                type="button"
                                @click="clearFilters"
                                class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg font-semibold transition"
                            >
                                Limpiar
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Photos Grid -->
                <div v-if="photos.data && photos.data.length > 0" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4">
                    <div
                        v-for="photo in photos.data"
                        :key="photo.id"
                        class="group relative bg-white rounded-lg shadow overflow-hidden hover:shadow-xl transition"
                    >
                        <!-- Image -->
                        <div class="aspect-square overflow-hidden bg-gray-100">
                            <img
                                :src="photo.thumbnail_url || 'https://via.placeholder.com/400?text=Sin+Imagen'"
                                :alt="photo.title"
                                class="w-full h-full object-cover group-hover:scale-110 transition duration-300"
                                @error="(e) => e.target.src = 'https://via.placeholder.com/400?text=Error'"
                            />
                        </div>

                        <!-- Overlay on hover -->
                        <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-60 transition flex items-center justify-center opacity-0 group-hover:opacity-100">
                            <div class="flex gap-2">
                                <!-- Ver -->
                                <a
                                    :href="photo.preview_url || photo.thumbnail_url"
                                    target="_blank"
                                    class="bg-white text-gray-900 p-2 rounded-lg hover:bg-gray-100 transition"
                                    title="Ver imagen"
                                >
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                </a>

                                <!-- Eliminar -->
                                <button
                                    @click="deletePhoto(photo.id)"
                                    class="bg-red-600 text-white p-2 rounded-lg hover:bg-red-700 transition"
                                    title="Eliminar"
                                >
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <!-- Info -->
                        <div class="p-3">
                            <div class="font-semibold text-sm text-gray-900 truncate">{{ photo.title }}</div>
                            <div class="text-xs text-gray-500 flex items-center justify-between mt-1">
                                <span>ID: {{ photo.unique_id }}</span>
                                <span class="font-semibold text-indigo-600">${{ photo.price }}</span>
                            </div>
                            <div class="flex items-center justify-between mt-2">
                                <span
                                    :class="photo.is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'"
                                    class="text-xs px-2 py-1 rounded-full font-medium"
                                >
                                    {{ photo.is_active ? 'Activa' : 'Inactiva' }}
                                </span>
                                <button
                                    @click="toggleActive(photo.id, photo.is_active)"
                                    class="text-xs text-indigo-600 hover:text-indigo-800 font-medium"
                                >
                                    {{ photo.is_active ? 'Desactivar' : 'Activar' }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-else class="bg-white rounded-lg shadow p-12 text-center">
                    <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    <h3 class="text-xl font-semibold text-gray-900 mb-2">No hay fotos</h3>
                    <p class="text-gray-600 mb-6">Comienza subiendo tu primera foto</p>
                    <Link
                        :href="route('photographer.photos.create')"
                        class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-3 rounded-lg font-semibold inline-block transition"
                    >
                        Subir Fotos
                    </Link>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
