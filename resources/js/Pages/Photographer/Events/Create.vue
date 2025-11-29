<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { ref } from 'vue';

const form = useForm({
    name: '',
    description: '',
    long_description: '',
    event_date: '',
    location: '',
    is_private: false,
    is_active: true,  //  AGREGAR (por defecto público)
    cover_image: null,
});

const coverImagePreview = ref(null);

const handleCoverImageChange = (e) => {
    const file = e.target.files[0];
    if (file) {
        form.cover_image = file;
        
        const reader = new FileReader();
        reader.onload = (e) => {
            coverImagePreview.value = e.target.result;
        };
        reader.readAsDataURL(file);
    }
};

const submit = () => {
    form.post(route('photographer.events.store'), {
        forceFormData: true,
    });
};
</script>

<template>
    <Head title="Crear Evento" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Crear Nuevo Evento</h2>
                <Link :href="route('photographer.events.index')" class="text-indigo-600 hover:text-indigo-800">
                    ← Volver a eventos
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <form @submit.prevent="submit" class="p-8">
                        
                        <!-- Name -->
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Nombre del Evento *
                            </label>
                            <input
                                v-model="form.name"
                                type="text"
                                required
                                class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                                placeholder="Ej: Fiesta de Graduación 2025"
                            />
                            <p v-if="form.errors.name" class="text-red-600 text-sm mt-1">{{ form.errors.name }}</p>
                        </div>

                        <!-- Description -->
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Descripción Corta
                            </label>
                            <textarea
                                v-model="form.description"
                                rows="3"
                                maxlength="500"
                                class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                                placeholder="Descripción breve del evento (máx. 500 caracteres)"
                            ></textarea>
                            <p class="text-sm text-gray-500 mt-1">{{ form.description?.length || 0 }} / 500 caracteres</p>
                            <p v-if="form.errors.description" class="text-red-600 text-sm mt-1">{{ form.errors.description }}</p>
                        </div>

                        <!-- Long Description -->
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Descripción Detallada
                            </label>
                            <textarea
                                v-model="form.long_description"
                                rows="5"
                                maxlength="2000"
                                class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                                placeholder="Descripción completa del evento (máx. 2000 caracteres)"
                            ></textarea>
                            <p class="text-sm text-gray-500 mt-1">{{ form.long_description?.length || 0 }} / 2000 caracteres</p>
                            <p v-if="form.errors.long_description" class="text-red-600 text-sm mt-1">{{ form.errors.long_description }}</p>
                        </div>

                        <!-- Event Date & Location -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                            <!-- Event Date -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Fecha del Evento *
                                </label>
                                <input
                                    v-model="form.event_date"
                                    type="date"
                                    required
                                    class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                                />
                                <p v-if="form.errors.event_date" class="text-red-600 text-sm mt-1">{{ form.errors.event_date }}</p>
                            </div>

                            <!-- Location -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Ubicación
                                </label>
                                <input
                                    v-model="form.location"
                                    type="text"
                                    class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                                    placeholder="Ej: Salón de Eventos Central"
                                />
                                <p v-if="form.errors.location" class="text-red-600 text-sm mt-1">{{ form.errors.location }}</p>
                            </div>
                        </div>

                        <!-- Cover Image -->
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Imagen de Portada
                            </label>
                            <div class="mt-2">
                                <div v-if="coverImagePreview" class="mb-4">
                                    <img :src="coverImagePreview" alt="Preview" class="h-48 w-auto rounded-lg shadow" />
                                </div>
                                <input
                                    type="file"
                                    accept="image/jpeg,image/png,image/jpg"
                                    @change="handleCoverImageChange"
                                    class="block w-full text-sm text-gray-500
                                        file:mr-4 file:py-2 file:px-4
                                        file:rounded-lg file:border-0
                                        file:text-sm file:font-semibold
                                        file:bg-indigo-50 file:text-indigo-700
                                        hover:file:bg-indigo-100"
                                />
                                <p class="text-xs text-gray-500 mt-1">JPG, PNG (Máx. 5MB)</p>
                                <p v-if="form.errors.cover_image" class="text-red-600 text-sm mt-1">{{ form.errors.cover_image }}</p>
                            </div>
                        </div>

                        <!--  NUEVA SECCIÓN: Visibilidad del Evento -->
                        <div class="mb-6 bg-gray-50 p-6 rounded-lg">
                            <label class="block text-sm font-semibold text-gray-700 mb-4">
                                Visibilidad del Evento *
                            </label>

                            <div class="space-y-3">
                                <!-- Público y Activo -->
                                <label
                                    class="flex items-center cursor-pointer p-4 bg-white rounded-lg border-2 transition"
                                    :class="form.is_active && !form.is_private ? 'border-green-500 bg-green-50' : 'border-gray-200 hover:border-gray-300'"
                                >
                                    <input
                                        type="radio"
                                        :checked="form.is_active && !form.is_private"
                                        @change="form.is_active = true; form.is_private = false"
                                        class="w-5 h-5 text-green-600"
                                    />
                                    <span class="ml-3">
                                        <span class="font-semibold text-gray-900">🌍 Público</span>
                                        <p class="text-sm text-gray-600">Cualquiera puede ver las fotos en la galería pública</p>
                                    </span>
                                </label>

                                <!-- Privado y Activo -->
                                <label
                                    class="flex items-center cursor-pointer p-4 bg-white rounded-lg border-2 transition"
                                    :class="form.is_active && form.is_private ? 'border-yellow-500 bg-yellow-50' : 'border-gray-200 hover:border-gray-300'"
                                >
                                    <input
                                        type="radio"
                                        :checked="form.is_active && form.is_private"
                                        @change="form.is_active = true; form.is_private = true"
                                        class="w-5 h-5 text-yellow-600"
                                    />
                                    <span class="ml-3">
                                        <span class="font-semibold text-gray-900">🔒 Privado</span>
                                        <p class="text-sm text-gray-600">Solo accesible con enlace privado (se generará automáticamente)</p>
                                    </span>
                                </label>

                                <!-- Inactivo (oculto) -->
                                <label
                                    class="flex items-center cursor-pointer p-4 bg-white rounded-lg border-2 transition"
                                    :class="!form.is_active ? 'border-red-500 bg-red-50' : 'border-gray-200 hover:border-gray-300'"
                                >
                                    <input
                                        type="radio"
                                        :checked="!form.is_active"
                                        @change="form.is_active = false; form.is_private = false"
                                        class="w-5 h-5 text-red-600"
                                    />
                                    <span class="ml-3">
                                        <span class="font-semibold text-gray-900">👁️ Oculto</span>
                                        <p class="text-sm text-gray-600">No aparece en ningún listado público (solo tú lo verás)</p>
                                    </span>
                                </label>
                            </div>
                        </div>

                        <!-- Buttons -->
                        <div class="flex items-center justify-end gap-4 pt-6 border-t">
                            <Link
                                :href="route('photographer.events.index')"
                                class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition"
                            >
                                Cancelar
                            </Link>
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2 rounded-lg font-semibold transition disabled:opacity-50"
                            >
                                <span v-if="form.processing">Creando...</span>
                                <span v-else>Crear Evento</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
