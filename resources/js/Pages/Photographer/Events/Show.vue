<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    event: Object,
    photos: Object,
    stats: Object, // ← Agregar esto

});

const showUploadModal = ref(false);
const selectedFiles = ref([]);
const previewUrls = ref([]);

const uploadForm = useForm({
    photos: [],
    event_id: props.event.id,
});

const handleFileSelect = (e) => {
    const files = Array.from(e.target.files);
    selectedFiles.value = files;
    uploadForm.photos = files;

    // Generar previews
    previewUrls.value = [];
    files.forEach(file => {
        const reader = new FileReader();
        reader.onload = (e) => {
            previewUrls.value.push(e.target.result);
        };
        reader.readAsDataURL(file);
    });
};


const uploadPhotos = () => {
    // DEBUG: Ver qué se está enviando
    console.log('📤 Enviando formulario:', {
        event_id: uploadForm.event_id,
        photos_count: uploadForm.photos.length,
    });

    uploadForm.post(route('photographer.photos.store'), {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
            console.log('✅ Fotos subidas exitosamente');
            showUploadModal.value = false;
            selectedFiles.value = [];
            previewUrls.value = [];
            uploadForm.reset('photos');
            uploadForm.event_id = props.event.id; // Restaurar event_id
        },
        onError: (errors) => {
            console.error('❌ Error al subir fotos:', errors);
            alert('Error al subir las fotos. Revisa la consola para más detalles.');
        }
    });
};
const deletePhoto = (photoId) => {
    if (confirm('¿Estás seguro de eliminar esta foto?')) {
        router.delete(route('photographer.photos.destroy', photoId), {
            preserveScroll: true,
        });
    }
};

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString('es-ES', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
};

const updateCoverImage = (photoId) => {
    if (confirm('¿Usar esta foto como portada del evento?')) {
        router.post(route('photographer.events.cover-image', props.event.id), {
            photo_id: photoId,
        }, {
            preserveScroll: true,
        });
    }
};
</script>
<template>
    <Head :title="event.name" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <Link :href="route('photographer.events.index')" class="text-indigo-600 hover:text-indigo-800 text-sm mb-2 inline-block">
                        ← Volver a eventos
                    </Link>
                    <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ event.name }}</h2>
                </div>
                <div class="flex gap-3">
                    <button
                        @click="showUploadModal = true"
                        class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg font-semibold transition"
                    >
                        📤 Subir Fotos
                    </button>
                    <Link
                        :href="route('photographer.events.edit', event.id)"
                        class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg font-semibold transition"
                    >
                        ✏️ Editar Evento
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                
                <!-- Event Info Card -->
                <div class="bg-white rounded-lg shadow-lg overflow-hidden mb-8">
                    <!-- Cover Image -->
                    <div class="relative h-64 bg-gradient-to-br from-indigo-500 to-purple-600">
                        <img
                            v-if="event.cover_image"
                            :src="`/storage/${event.cover_image}`"
                            :alt="event.name"
                            class="w-full h-full object-cover"
                        />
                        <div class="absolute inset-0 bg-gradient-to-t from-black/70 to-transparent"></div>
                        <div class="absolute bottom-6 left-6 right-6">
                            <h1 class="text-4xl font-bold text-white mb-2">{{ event.name }}</h1>
                            <div class="flex items-center gap-6 text-white/90">
                                <div class="flex items-center">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                    {{ formatDate(event.event_date) }}
                                </div>
                                <div v-if="event.location" class="flex items-center">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                    </svg>
                                    {{ event.location }}
                                </div>
                                <div class="flex items-center">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                    {{ stats.total_photos }} fotos
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Description -->
                    <div v-if="event.description" class="p-6 border-b">
                        <p class="text-gray-700">{{ event.description }}</p>
                    </div>

                    <!-- Stats -->
                    <div class="p-6 bg-gray-50 grid grid-cols-3 gap-6">
                        <div class="text-center">
                            <div class="text-3xl font-bold text-indigo-600">{{ stats.total_photos }}</div>
                            <div class="text-sm text-gray-600">Total de Fotos</div>
                        </div>
                        <div class="text-center">
                            <div class="text-3xl font-bold text-green-600">{{ stats.active_photos }}</div>
                            <div class="text-sm text-gray-600">Fotos Activas</div>
                        </div>
                        <div class="text-center">
                            <div class="text-3xl font-bold text-purple-600">{{ stats.total_downloads }}</div>
                            <div class="text-sm text-gray-600">Descargas Totales</div>
                        </div>
                    </div>
                </div>

                <!-- Photos Grid -->
                <div class="bg-white rounded-lg shadow-lg p-6">
                    <h3 class="text-2xl font-bold text-gray-900 mb-6">Galería de Fotos</h3>

                    <!-- Empty State -->
                    <div v-if="!photos.data || photos.data.length === 0" class="text-center py-16">
                        <div class="text-6xl mb-4">📸</div>
                        <h4 class="text-xl font-semibold text-gray-900 mb-2">No hay fotos en este evento</h4>
                        <p class="text-gray-600 mb-6">Comienza subiendo tus primeras fotos</p>
                        <button
                            @click="showUploadModal = true"
                            class="inline-block bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-3 rounded-lg font-semibold transition"
                        >
                            📤 Subir Fotos
                        </button>
                    </div>

                    <!-- Photos Grid -->
                    <div v-else>
                        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 mb-6">
                            <div
                                v-for="photo in photos.data"
                                :key="photo.id"
                                class="group relative aspect-square bg-gray-200 rounded-lg overflow-hidden shadow hover:shadow-xl transition"
                            >
                                <img
                                    :src="photo.thumbnail_url"
                                    :alt="photo.unique_id"
                                    class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-300"
                                />
                                
                                <!-- Overlay -->
                                <div class="absolute inset-0 bg-black/0 group-hover:bg-black/60 transition-all duration-300 flex items-center justify-center">
                                    <div class="opacity-0 group-hover:opacity-100 flex flex-col gap-2 transition-all">
                                        <Link
                                            :href="route('photographer.photos.show', photo.id)"
                                            class="bg-white text-gray-900 px-4 py-2 rounded-lg font-semibold text-sm hover:bg-gray-100 transition"
                                        >
                                            Ver
                                        </Link>
                                        <button
                                            @click="updateCoverImage(photo.id)"
                                            class="bg-indigo-600 text-white px-4 py-2 rounded-lg font-semibold text-sm hover:bg-indigo-700 transition"
                                        >
                                            Portada
                                        </button>
                                        <button
                                            @click="deletePhoto(photo.id)"
                                            class="bg-red-600 text-white px-4 py-2 rounded-lg font-semibold text-sm hover:bg-red-700 transition"
                                        >
                                            Eliminar
                                        </button>
                                    </div>
                                </div>

                                <!-- Badge -->
                                <div class="absolute top-2 left-2 bg-white/90 backdrop-blur-sm px-2 py-1 rounded text-xs font-mono">
                                    {{ photo.unique_id }}
                                </div>
                                
                                <!-- Status Badge -->
                                <div v-if="!photo.is_active" class="absolute top-2 right-2 bg-red-500 text-white px-2 py-1 rounded text-xs font-bold">
                                    Inactiva
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

        <!-- Upload Modal -->
        <div v-if="showUploadModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50 p-4">
            <div class="bg-white rounded-2xl shadow-2xl max-w-4xl w-full max-h-[90vh] overflow-y-auto">
                <div class="p-6 border-b sticky top-0 bg-white z-10">
                    <div class="flex items-center justify-between">
                        <h3 class="text-2xl font-bold text-gray-900">Subir Fotos al Evento</h3>
                        <button @click="showUploadModal = false" class="text-gray-400 hover:text-gray-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <form @submit.prevent="uploadPhotos" class="p-6">
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Selecciona las fotos
                        </label>
                        <input
                            type="file"
                            multiple
                            accept="image/jpeg,image/png,image/jpg"
                            @change="handleFileSelect"
                            class="block w-full text-sm text-gray-500 file:mr-4 file:py-3 file:px-6 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 cursor-pointer"
                        />
                        <p class="text-xs text-gray-500 mt-2">Puedes seleccionar múltiples archivos (JPG, PNG). Máximo 10MB por foto.</p>
                    </div>

                    <!-- Previews -->
                    <div v-if="previewUrls.length > 0" class="mb-6">
                        <h4 class="font-semibold text-gray-900 mb-3">Vista previa ({{ previewUrls.length }} fotos)</h4>
                        <div class="grid grid-cols-3 md:grid-cols-4 gap-3 max-h-96 overflow-y-auto p-2">
                            <div v-for="(url, index) in previewUrls" :key="index" class="aspect-square rounded-lg overflow-hidden shadow-md">
                                <img :src="url" :alt="`Preview ${index + 1}`" class="w-full h-full object-cover">
                            </div>
                        </div>
                    </div>

                    <!-- Progress -->
                    <div v-if="uploadForm.processing" class="mb-6">
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="bg-indigo-600 h-2 rounded-full transition-all duration-300" style="width: 50%"></div>
                        </div>
                        <p class="text-sm text-gray-600 mt-2 text-center">Subiendo fotos...</p>
                    </div>

                    <div class="flex items-center justify-end gap-4 pt-6 border-t">
                        <button
                            type="button"
                            @click="showUploadModal = false"
                            :disabled="uploadForm.processing"
                            class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            Cancelar
                        </button>
                        <button
                            type="submit"
                            :disabled="uploadForm.processing || selectedFiles.length === 0"
                            class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2 rounded-lg font-semibold transition disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            {{ uploadForm.processing ? 'Subiendo...' : `Subir ${selectedFiles.length} foto(s)` }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>