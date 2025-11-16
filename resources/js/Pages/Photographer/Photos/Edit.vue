<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    photo: Object,
    events: Array,
});

const form = useForm({
    price: props.photo.price,
    is_active: props.photo.is_active,
    event_id: props.photo.event_id,
});

const submit = () => {
    form.put(route('photographer.photos.update', props.photo.id), {
        preserveScroll: true,
    });
};

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString('es-ES', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
};
</script>

<template>
    <Head :title="`Editar Foto ${photo.unique_id}`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    ✏️ Editar Foto: {{ photo.unique_id }}
                </h2>
                <Link
                    :href="route('photographer.photos.show', photo.id)"
                    class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg font-semibold transition"
                >
                    ← Volver
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 p-6">
                        
                        <!-- Image Preview -->
                        <div>
                            <h3 class="text-lg font-bold text-gray-900 mb-4">Vista Previa</h3>
                            <img
                                :src="photo.watermarked_url || photo.thumbnail_url"
                                :alt="photo.unique_id"
                                class="w-full h-auto rounded-lg border-2 border-gray-200"
                            />
                            
                            <div class="mt-4 p-4 bg-gray-50 rounded-lg">
                                <h4 class="text-sm font-bold text-gray-700 mb-2">Información</h4>
                                <div class="space-y-2 text-sm text-gray-600">
                                    <div><strong>ID:</strong> {{ photo.unique_id }}</div>
                                    <div><strong>Dimensiones:</strong> {{ photo.width }} x {{ photo.height }} px</div>
                                    <div><strong>Descargas:</strong> {{ photo.downloads }}</div>
                                    <div v-if="photo.event">
                                        <strong>Evento actual:</strong> {{ photo.event.name }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Edit Form -->
                        <div>
                            <h3 class="text-lg font-bold text-gray-900 mb-4">Editar Información</h3>
                            
                            <form @submit.prevent="submit" class="space-y-6">
                                
                                <!-- Evento -->
                                <div>
                                    <label for="event_id" class="block text-sm font-bold text-gray-700 mb-2">
                                        Evento
                                    </label>
                                    <select
                                        id="event_id"
                                        v-model="form.event_id"
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                    >
                                        <option :value="null">Sin evento</option>
                                        <option
                                            v-for="event in events"
                                            :key="event.id"
                                            :value="event.id"
                                        >
                                            {{ event.name }} - {{ formatDate(event.event_date) }}
                                        </option>
                                    </select>
                                    <p v-if="form.errors.event_id" class="mt-1 text-sm text-red-600">
                                        {{ form.errors.event_id }}
                                    </p>
                                </div>

                                <!-- Precio -->
                                <div>
                                    <label for="price" class="block text-sm font-bold text-gray-700 mb-2">
                                        Precio ($)
                                    </label>
                                    <input
                                        id="price"
                                        type="number"
                                        v-model="form.price"
                                        step="0.01"
                                        min="0"
                                        required
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                    />
                                    <p v-if="form.errors.price" class="mt-1 text-sm text-red-600">
                                        {{ form.errors.price }}
                                    </p>
                                </div>

                                <!-- Estado -->
                                <div>
                                    <label class="flex items-center space-x-3 cursor-pointer">
                                        <input
                                            type="checkbox"
                                            v-model="form.is_active"
                                            class="w-5 h-5 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500"
                                        />
                                        <span class="text-sm font-bold text-gray-700">
                                            Foto activa (visible para clientes)
                                        </span>
                                    </label>
                                </div>

                                <!-- Botones -->
                                <div class="flex gap-3 pt-4">
                                    <button
                                        type="submit"
                                        :disabled="form.processing"
                                        class="flex-1 bg-indigo-600 hover:bg-indigo-700 disabled:bg-gray-400 text-white px-6 py-3 rounded-lg font-semibold transition"
                                    >
                                        <span v-if="form.processing">Guardando...</span>
                                        <span v-else>💾 Guardar Cambios</span>
                                    </button>
                                    
                                    <Link
                                        :href="route('photographer.photos.show', photo.id)"
                                        class="flex-1 bg-gray-600 hover:bg-gray-700 text-white text-center px-6 py-3 rounded-lg font-semibold transition"
                                    >
                                        Cancelar
                                    </Link>
                                </div>

                                <!-- Mensaje de éxito -->
                                <div
                                    v-if="form.recentlySuccessful"
                                    class="p-4 bg-green-50 border border-green-200 rounded-lg text-green-800 text-sm"
                                >
                                    ✅ Cambios guardados exitosamente
                                </div>

                            </form>
                        </div>

                    </div>

                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
