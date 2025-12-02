<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import {
    EyeIcon,
    TrashIcon,
    CheckCircleIcon,
    XCircleIcon,
    ArrowDownTrayIcon,
    PhotoIcon
} from '@heroicons/vue/24/outline';

const props = defineProps({
    photos: Object,
    events: Array,
    filters: Object,
    stats: Object,
});

const deletePhoto = (photoId) => {  //  SIN el parámetro event
    if (confirm('¿Estás seguro de eliminar esta foto?')) {
        router.delete(route('photographer.photos.destroy', photoId), {
            preserveScroll: true,
        });
    }
};

const toggleActive = (photo) => {  //  SIN el parámetro event
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
                    <PhotoIcon class="inline h-6 w-6 mr-2" />
                    Mis Fotos
                </h2>
                <Link :href="route('photographer.events.index')"
                    class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg font-semibold transition inline-flex items-center gap-2">
                Ver Eventos
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

                <!-- Stats Cards -->
                <div v-if="stats" class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                    <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl shadow-lg p-6 text-white">
                        <div class="flex items-center justify-between mb-2">
                            <div class="text-3xl font-bold">{{ stats.total }}</div>
                            <PhotoIcon class="h-10 w-10 opacity-50" />
                        </div>
                        <div class="text-blue-100 font-medium">Total de Fotos</div>
                    </div>
                    <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-xl shadow-lg p-6 text-white">
                        <div class="flex items-center justify-between mb-2">
                            <div class="text-3xl font-bold">{{ stats.active }}</div>
                            <CheckCircleIcon class="h-10 w-10 opacity-50" />
                        </div>
                        <div class="text-green-100 font-medium">Fotos Activas</div>
                    </div>
                    <div class="bg-gradient-to-br from-gray-500 to-gray-600 rounded-xl shadow-lg p-6 text-white">
                        <div class="flex items-center justify-between mb-2">
                            <div class="text-3xl font-bold">{{ stats.inactive }}</div>
                            <XCircleIcon class="h-10 w-10 opacity-50" />
                        </div>
                        <div class="text-gray-100 font-medium">Fotos Inactivas</div>
                    </div>
                    <div class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl shadow-lg p-6 text-white">
                        <div class="flex items-center justify-between mb-2">
                            <div class="text-3xl font-bold">{{ stats.total_downloads }}</div>
                            <ArrowDownTrayIcon class="h-10 w-10 opacity-50" />
                        </div>
                        <div class="text-purple-100 font-medium">Total Descargas</div>
                    </div>
                </div>

                <!-- Photos Grid -->
                <div class="bg-white rounded-xl shadow-lg p-6">
                    <h3 class="text-2xl font-bold text-gray-900 mb-6">Galería de Fotos</h3>

                    <!-- Empty State -->
                    <div v-if="!photos.data || photos.data.length === 0" class="text-center py-16">
                        <PhotoIcon class="h-24 w-24 mx-auto text-gray-300 mb-4" />
                        <h4 class="text-xl font-semibold text-gray-900 mb-2">No tienes fotos subidas</h4>
                        <p class="text-gray-600 mb-6">Ve a un evento para subir tus primeras fotos</p>
                        <Link :href="route('photographer.events.index')"
                            class="inline-block bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-3 rounded-lg font-semibold transition">
                        Ver Mis Eventos
                        </Link>
                    </div>

                    <!-- Photos Grid -->
                    <div v-else>
                        <div
                            class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-6 mb-6">
                            <div v-for="photo in photos.data" :key="photo.id"
                                class="bg-white border-2 border-gray-200 rounded-xl overflow-hidden hover:shadow-xl transition-all duration-300 group">
                                <!-- Image Container -->
                                <div class="relative aspect-square bg-gray-100 overflow-hidden">
                                    <img :src="photo.thumbnail_url" :alt="photo.unique_id"
                                        class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                                        loading="lazy" />

                                    <!-- Status Badge (Always Visible) -->
                                    <div class="absolute top-3 right-3">
                                        <span :class="[
                                            'inline-flex items-center gap-1 px-3 py-1.5 rounded-full text-xs font-bold shadow-lg backdrop-blur-sm',
                                            photo.is_active
                                                ? 'bg-green-500/90 text-white'
                                                : 'bg-gray-500/90 text-white'
                                        ]">
                                            <component :is="photo.is_active ? CheckCircleIcon : XCircleIcon"
                                                class="h-3 w-3" />
                                            {{ photo.is_active ? 'Activa' : 'Inactiva' }}
                                        </span>
                                    </div>

                                    <!-- Hover Overlay -->
                                    <div
                                        class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/40 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                                        <div class="absolute bottom-3 left-3 right-3 text-white">
                                            <div class="flex items-center gap-2 text-xs mb-1">
                                                <ArrowDownTrayIcon class="h-3 w-3" />
                                                <span>{{ photo.downloads }} descargas</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Photo Info -->
                                <div class="p-4">
                                    <div class="mb-3">
                                        <div class="font-bold text-gray-900 truncate text-sm">
                                            {{ photo.unique_id }}
                                        </div>
                                        <div v-if="photo.event" class="text-xs text-gray-500 truncate mt-1">
                                            {{ photo.event.name }}
                                        </div>
                                    </div>

                                    <!-- Action Buttons -->
                                    <div class="space-y-2">
                                        <!-- Ver Detalles -->
                                        <Link :href="route('photographer.photos.show', photo.id)"
                                            class="w-full bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-semibold transition inline-flex items-center justify-center gap-2">
                                        <EyeIcon class="h-4 w-4" />
                                        Ver Detalles
                                        </Link>

                                        <!-- Toggle Active / Delete -->
                                        <div class="grid grid-cols-2 gap-2">
                                            <button @click.stop.prevent="toggleActive(photo)" :class="[
                                                'px-3 py-2 rounded-lg text-xs font-semibold transition inline-flex items-center justify-center gap-1.5',
                                                photo.is_active
                                                    ? 'bg-orange-100 hover:bg-orange-200 text-orange-700'
                                                    : 'bg-green-100 hover:bg-green-200 text-green-700'
                                            ]">
                                                <component :is="photo.is_active ? XCircleIcon : CheckCircleIcon"
                                                    class="h-4 w-4" />
                                                {{ photo.is_active ? 'Desactivar' : 'Activar' }}
                                            </button>

                                            <button @click.stop.prevent="deletePhoto(photo.id)"
                                                class="bg-red-100 hover:bg-red-200 text-red-700 px-3 py-2 rounded-lg text-xs font-semibold transition inline-flex items-center justify-center gap-1.5">
                                                <TrashIcon class="h-4 w-4" />
                                                Eliminar
                                            </button>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>

                        <!-- Pagination -->
                        <div v-if="photos.last_page > 1"
                            class="flex items-center justify-center gap-2 flex-wrap pt-6 border-t border-gray-200">
                            <template v-for="(link, index) in photos.links" :key="index">
                                <Link v-if="link.url" :href="link.url" v-html="link.label" :class="[
                                    'px-4 py-2 rounded-lg font-medium transition',
                                    link.active
                                        ? 'bg-indigo-600 text-white shadow-lg'
                                        : 'bg-gray-100 text-gray-700 hover:bg-gray-200 border border-gray-300'
                                ]" />
                                <span v-else v-html="link.label"
                                    class="px-4 py-2 rounded-lg font-medium bg-gray-50 text-gray-400 cursor-not-allowed" />
                            </template>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
