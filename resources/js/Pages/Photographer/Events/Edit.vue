<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    event: Object,
    availablePhotos: Array,
});

const form = useForm({
    name: props.event.name,
    description: props.event.description || '',
    long_description: props.event.long_description || '',
    event_date: props.event.event_date,
    location: props.event.location || '',
    is_private: props.event.is_private,
    is_active: props.event.is_active ?? true,
    cover_image: null,
});

const previewImage = ref(props.event.cover_image_url);

const handleImageChange = (e) => {
    const file = e.target.files[0];
    if (file) {
        form.cover_image = file;
        previewImage.value = URL.createObjectURL(file);
    }
};

const submit = () => {
    //  Agregar _method al formulario
    form.transform((data) => ({
        ...data,
        _method: 'PUT'
    })).post(route('photographer.events.update', props.event.id), {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
            console.log('Evento actualizado');
        },
    });
};


const deleteEvent = () => {
    if (confirm('¿Estás seguro de eliminar este evento? Se eliminarán todas las fotos asociadas.')) {
        router.delete(route('photographer.events.destroy', props.event.id));
    }
};
</script>

<template>

    <Head title="Editar Evento" />
  

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Editar Evento
                </h2>
                <button @click="deleteEvent"
                    class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg font-semibold transition">
                    Eliminar Evento
                </button>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white rounded-lg shadow-lg p-8">

                    <form @submit.prevent="submit" class="space-y-6">

                        <!-- Nombre -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                Nombre del Evento *
                            </label>
                            <input v-model="form.name" type="text" required
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                placeholder="Ej: Boda Juan y María" />
                            <div v-if="form.errors.name" class="text-red-600 text-sm mt-1">
                                {{ form.errors.name }}
                            </div>
                        </div>

                        <!-- Descripción Corta -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                Descripción Corta
                            </label>
                            <input v-model="form.description" type="text" maxlength="500"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                placeholder="Breve descripción del evento" />
                            <p class="text-xs text-gray-500 mt-1">
                                {{ form.description?.length || 0 }}/500 caracteres
                            </p>
                        </div>

                        <!-- Descripción Larga -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                Descripción Detallada
                            </label>
                            <textarea v-model="form.long_description" rows="4" maxlength="2000"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                placeholder="Descripción completa del evento..."></textarea>
                            <p class="text-xs text-gray-500 mt-1">
                                {{ form.long_description?.length || 0 }}/2000 caracteres
                            </p>
                        </div>

                        <!-- Fecha y Ubicación -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Fecha -->
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">
                                    Fecha del Evento *
                                </label>
                                <input v-model="form.event_date" type="date" required
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent" />
                                <div v-if="form.errors.event_date" class="text-red-600 text-sm mt-1">
                                    {{ form.errors.event_date }}
                                </div>
                            </div>

                            <!-- Ubicación -->
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">
                                    Ubicación
                                </label>
                                <input v-model="form.location" type="text"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                    placeholder="Ej: Salón Los Pinos, CABA" />
                            </div>
                        </div>

                        <!-- Imagen de Portada -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                Imagen de Portada
                            </label>

                            <!-- Preview -->
                            <div v-if="previewImage" class="mb-4">
                                <img :src="previewImage" alt="Preview" class="w-full h-64 object-cover rounded-lg" />
                            </div>

                            <input type="file" accept="image/jpeg,image/png,image/jpg" @change="handleImageChange"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent" />
                            <p class="text-xs text-gray-500 mt-1">
                                JPG, PNG. Máximo 5MB.
                            </p>
                            <div v-if="form.errors.cover_image" class="text-red-600 text-sm mt-1">
                                {{ form.errors.cover_image }}
                            </div>
                        </div>


                        <!-- Opciones - MODO ALTERNANTE -->
                        <div class="space-y-4 bg-gray-50 p-6 rounded-lg">
                            <label class="block text-sm font-semibold text-gray-700 mb-4">
                                Visibilidad del Evento
                            </label>

                            <div class="space-y-3">
                                <!-- Público y Activo -->
                                <label
                                    class="flex items-center cursor-pointer p-4 bg-white rounded-lg border-2 transition"
                                    :class="form.is_active && !form.is_private ? 'border-green-500 bg-green-50' : 'border-gray-200 hover:border-gray-300'">
                                    <input type="radio" :checked="form.is_active && !form.is_private"
                                        @change="form.is_active = true; form.is_private = false"
                                        class="w-5 h-5 text-green-600" />
                                    <span class="ml-3">
                                        <span class="font-semibold text-gray-900">🌍 Público</span>
                                        <p class="text-sm text-gray-600">Cualquiera puede ver las fotos</p>
                                    </span>
                                </label>

                                <!-- Privado y Activo -->
                                <label
                                    class="flex items-center cursor-pointer p-4 bg-white rounded-lg border-2 transition"
                                    :class="form.is_active && form.is_private ? 'border-yellow-500 bg-yellow-50' : 'border-gray-200 hover:border-gray-300'">
                                    <input type="radio" :checked="form.is_active && form.is_private"
                                        @change="form.is_active = true; form.is_private = true"
                                        class="w-5 h-5 text-yellow-600" />
                                    <span class="ml-3">
                                        <span class="font-semibold text-gray-900">🔒 Privado</span>
                                        <p class="text-sm text-gray-600">Solo con enlace privado</p>
                                    </span>
                                </label>

                                <!-- Inactivo (oculto) -->
                                <label
                                    class="flex items-center cursor-pointer p-4 bg-white rounded-lg border-2 transition"
                                    :class="!form.is_active ? 'border-red-500 bg-red-50' : 'border-gray-200 hover:border-gray-300'">
                                    <input type="radio" :checked="!form.is_active"
                                        @change="form.is_active = false; form.is_private = false"
                                        class="w-5 h-5 text-red-600" />
                                    <span class="ml-3">
                                        <span class="font-semibold text-gray-900"> Oculto</span>
                                        <p class="text-sm text-gray-600">Solo tú puedes verlo</p>
                                    </span>
                                </label>
                            </div>
                        </div>



                        <!-- Botones -->
                        <div class="flex items-center justify-between pt-6 border-t">
                            <a :href="route('photographer.events.index')"
                                class="px-6 py-3 bg-gray-200 hover:bg-gray-300 text-gray-800 rounded-lg font-semibold transition">
                                ← Cancelar
                            </a>

                            <button type="submit" :disabled="form.processing"
                                class="px-8 py-3 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-semibold transition disabled:opacity-50 disabled:cursor-not-allowed">
                                <span v-if="form.processing">Guardando...</span>
                                <span v-else>Guardar Cambios</span>
                            </button>
                        </div>

                    </form>

                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
