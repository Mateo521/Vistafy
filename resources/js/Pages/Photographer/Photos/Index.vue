<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    photos: Object,
    events: Array,
    filters: Object,
    stats: Object,
});

const deletePhoto = (photoId) => {
    if (confirm('¿Estás seguro de eliminar esta foto?')) {
        router.delete(route('photographer.photos.destroy', photoId), {
            preserveScroll: true,
        });
    }
};

const toggleActive = (photo) => {
    router.put(route('photographer.photos.update', photo.id), {
        is_active: !photo.is_active,
        price: photo.price,
    }, {
        preserveScroll: true,
    });
};

const formatFileSize = (bytes) => {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
};
</script>

<template>
    <Head title="Mis Fotos" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                     Mis Fotos
                </h2>
                <Link
                    :href="route('photographer.events.index')"
                    class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg font-semibold transition"
                >
                    Ver Eventos
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                
                <!-- Stats Cards -->
                <div v-if="stats" class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                    <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg shadow-lg p-6 text-white">
                        <div class="text-3xl font-bold mb-2">{{ stats.total }}</div>
                        <div class="text-blue-100">Total de Fotos</div>
                    </div>
                    <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-lg shadow-lg p-6 text-white">
                        <div class="text-3xl font-bold mb-2">{{ stats.active }}</div>
                        <div class="text-green-100">Fotos Activas</div>
                    </div>
                    <div class="bg-gradient-to-br from-gray-500 to-gray-600 rounded-lg shadow-lg p-6 text-white">
                        <div class="text-3xl font-bold mb-2">{{ stats.inactive }}</div>
                        <div class="text-gray-100">Fotos Inactivas</div>
                    </div>
                    <div class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-lg shadow-lg p-6 text-white">
                        <div class="text-3xl font-bold mb-2">{{ stats.total_downloads }}</div>
                        <div class="text-purple-100">Total Descargas</div>
                    </div>
                </div>

                <!-- Photos Grid -->
                <div class="bg-white rounded-lg shadow-lg p-6">
                    <h3 class="text-2xl font-bold text-gray-900 mb-6">Todas mis Fotos</h3>

                    <!-- Empty State -->
                    <div v-if="!photos.data || photos.data.length === 0" class="text-center py-16">
                        <div class="text-6xl mb-4">-</div>
                        <h4 class="text-xl font-semibold text-gray-900 mb-2">No tienes fotos subidas</h4>
                        <p class="text-gray-600 mb-6">Ve a un evento para subir tus primeras fotos</p>
                        <Link
                            :href="route('photographer.events.index')"
                            class="inline-block bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-3 rounded-lg font-semibold transition"
                        >
                            Ver Mis Eventos
                        </Link>
                    </div>

                    <!-- Photos List -->
                    <div v-else>
                        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-6 gap-4 mb-6">
                            <div
                                v-for="photo in photos.data"
                                :key="photo.id"
                                class="relative group bg-gray-100 rounded-lg overflow-hidden aspect-square"
                            >
                                <!-- Image -->
                                <img
                                    :src="photo.thumbnail_url"
                                    :alt="photo.unique_id"
                                    class="w-full h-full object-cover"
                                    loading="lazy"
                                />

                                <!-- Overlay with actions -->
                                <div class="absolute inset-0 bg-black/70 opacity-0 group-hover:opacity-100 transition-opacity flex flex-col items-center justify-center gap-2 p-2">
                                    
                                    <!-- Photo Info -->
                                    <div class="text-white text-center text-xs mb-1">
                                        <div class="font-bold truncate">{{ photo.unique_id }}</div>
                                        <div v-if="photo.event" class="text-gray-300 truncate text-[10px]">{{ photo.event.name }}</div>
                                        <div class="text-gray-300 text-[10px]">{{ photo.downloads }} descargas</div>
                                    </div>

                                    <!-- Actions -->
                                    <div class="flex gap-1 w-full px-1">
                                        <Link
                                            :href="route('photographer.photos.show', photo.id)"
                                            class="flex-1 bg-blue-600 hover:bg-blue-700 text-white text-center px-2 py-1.5 rounded text-[10px] font-semibold transition"
                                        >
                                            Ver
                                        </Link>
                                        <button
                                            @click="toggleActive(photo)"
                                            :class="[
                                                'flex-1 text-white text-center px-2 py-1.5 rounded text-[10px] font-semibold transition',
                                                photo.is_active
                                                    ? 'bg-green-600 hover:bg-green-700'
                                                    : 'bg-gray-600 hover:bg-gray-700'
                                            ]"
                                            :title="photo.is_active ? 'Desactivar' : 'Activar'"
                                        >
                                            {{ photo.is_active ? '✓' : '✗' }}
                                        </button>
                                        <button
                                            @click="deletePhoto(photo.id)"
                                            class="bg-red-600 hover:bg-red-700 text-white px-2 py-1.5 rounded text-[10px] font-semibold transition"
                                            title="Eliminar"
                                        >
                                            🗑️
                                        </button>
                                    </div>
                                </div>

                                <!-- Status Badge -->
                                <div class="absolute top-2 right-2">
                                    <span
                                        :class="[
                                            'px-2 py-1 rounded-full text-[10px] font-bold shadow-lg',
                                            photo.is_active
                                                ? 'bg-green-500 text-white'
                                                : 'bg-gray-500 text-white'
                                        ]"
                                    >
                                        {{ photo.is_active ? '✓' : '✗' }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Pagination -->
                        <div v-if="photos.last_page > 1" class="flex items-center justify-center gap-2 flex-wrap">
                            <template v-for="(link, index) in photos.links" :key="index">
                                <Link
                                    v-if="link.url"
                                    :href="link.url"
                                    v-html="link.label"
                                    :class="[
                                        'px-4 py-2 rounded-lg font-medium transition',
                                        link.active
                                            ? 'bg-indigo-600 text-white shadow-lg'
                                            : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
                                    ]"
                                />
                                <span
                                    v-else
                                    v-html="link.label"
                                    class="px-4 py-2 rounded-lg font-medium bg-gray-100 text-gray-400 cursor-not-allowed"
                                />
                            </template>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
