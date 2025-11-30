<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';

const props = defineProps({
    photo: Object,
});

const deletePhoto = () => {
    if (confirm('¿Estás seguro de eliminar esta foto?')) {
        router.delete(route('photographer.photos.destroy', props.photo.id), {
            onSuccess: () => {
                router.visit(route('photographer.photos.index'));
            }
        });
    }
};

const formatFileSize = (bytes) => {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
};

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString('es-ES', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};
</script>

<template>
    <Head :title="`Foto ${photo.unique_id}`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Detalles de Foto
                </h2>
                <Link
                    :href="route('photographer.photos.index')"
                    class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg font-semibold transition"
                >
                    ← Volver
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    
                    <!-- Image Preview -->
                    <div class="bg-white rounded-lg shadow-lg p-6">
                        <img
                            :src="photo.watermarked_url || photo.thumbnail_url"
                            :alt="photo.unique_id"
                            class="w-full h-auto rounded-lg"
                        />
                    </div>

                    <!-- Photo Details -->
                    <div class="bg-white rounded-lg shadow-lg p-6">
                        <h3 class="text-2xl font-bold text-gray-900 mb-6">Información de la Foto</h3>

                        <div class="space-y-4">
                            <div>
                                <label class="text-sm font-semibold text-gray-600">ID Único</label>
                                <p class="text-lg text-gray-900">{{ photo.unique_id }}</p>
                            </div>

                            <div v-if="photo.event">
                                <label class="text-sm font-semibold text-gray-600">Evento</label>
                                <Link
                                    :href="route('photographer.events.show', photo.event.id)"
                                    class="text-lg text-indigo-600 hover:text-indigo-800"
                                >
                                    {{ photo.event.name }}
                                </Link>
                            </div>

                            <div>
                                <label class="text-sm font-semibold text-gray-600">Precio</label>
                                <p class="text-lg text-gray-900">${{ photo.price }}</p>
                            </div>

                            <div>
                                <label class="text-sm font-semibold text-gray-600">Estado</label>
                                <span
                                    :class="[
                                        'inline-block px-3 py-1 rounded-full text-sm font-bold',
                                        photo.is_active
                                            ? 'bg-green-100 text-green-800'
                                            : 'bg-gray-100 text-gray-800'
                                    ]"
                                >
                                    {{ photo.is_active ? '✓ Activa' : '✗ Inactiva' }}
                                </span>
                            </div>

                            <div>
                                <label class="text-sm font-semibold text-gray-600">Dimensiones</label>
                                <p class="text-lg text-gray-900">{{ photo.width }} x {{ photo.height }} px</p>
                            </div>

                            <div>
                                <label class="text-sm font-semibold text-gray-600">Tamaño del archivo</label>
                                <p class="text-lg text-gray-900">{{ formatFileSize(photo.file_size) }}</p>
                            </div>

                            <div>
                                <label class="text-sm font-semibold text-gray-600">Descargas</label>
                                <p class="text-lg text-gray-900">{{ photo.downloads }}</p>
                            </div>

                            <div>
                                <label class="text-sm font-semibold text-gray-600">Fecha de subida</label>
                                <p class="text-lg text-gray-900">{{ formatDate(photo.created_at) }}</p>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="mt-8 flex gap-3">
                            <Link
                                :href="route('photographer.photos.edit', photo.id)"
                                class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white text-center px-4 py-3 rounded-lg font-semibold transition"
                            >
                                 Editar
                            </Link>
                            <button
                                @click="deletePhoto"
                                class="flex-1 bg-red-600 hover:bg-red-700 text-white px-4 py-3 rounded-lg font-semibold transition"
                            >
                                 Eliminar
                            </button>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
