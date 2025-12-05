<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import {
    UserCircleIcon,
    PhotoIcon,
    TrashIcon,
    CheckCircleIcon
} from '@heroicons/vue/24/outline';

const props = defineProps({
    photographer: Object,
});

const form = useForm({
    business_name: props.photographer.business_name,
    bio: props.photographer.bio || '',
    phone: props.photographer.phone || '',
    region: props.photographer.region,
    profile_photo: null,
    banner_photo: null,
});

const profilePhotoPreview = ref(null);
const bannerPhotoPreview = ref(null);

const regions = [
    'Buenos Aires',
    'CABA',
    'Córdoba',
    'Santa Fe',
    'Mendoza',
    'Tucumán',
    'Rosario',
    'Salta',
    'Neuquén',
    'Entre Ríos',
];

const handleProfilePhotoChange = (event) => {
    const file = event.target.files[0];
    if (file) {
        form.profile_photo = file;
        const reader = new FileReader();
        reader.onload = (e) => {
            profilePhotoPreview.value = e.target.result;
        };
        reader.readAsDataURL(file);
    }
};

const handleBannerPhotoChange = (event) => {
    const file = event.target.files[0];
    if (file) {
        form.banner_photo = file;
        const reader = new FileReader();
        reader.onload = (e) => {
            bannerPhotoPreview.value = e.target.result;
        };
        reader.readAsDataURL(file);
    }
};

const deleteProfilePhoto = () => {
    if (confirm('¿Eliminar la foto de perfil?')) {
        router.delete(route('photographer.profile.photo.delete'), {
            preserveScroll: true,
        });
    }
};

const deleteBannerPhoto = () => {
    if (confirm('¿Eliminar el banner?')) {
        router.delete(route('photographer.profile.banner.delete'), {
            preserveScroll: true,
        });
    }
};

const submit = () => {
    form.post(route('photographer.profile.update'), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset('profile_photo', 'banner_photo');
            profilePhotoPreview.value = null;
            bannerPhotoPreview.value = null;
        },
    });
};
</script>

<template>

    <Head title="Editar Perfil" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                <UserCircleIcon class="inline h-6 w-6 mr-2" />
                Editar Perfil
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">

                <div v-if="$page.props.flash?.success"
                    class="mb-6 bg-green-50 border-l-4 border-green-500 p-4 rounded-lg">
                    <div class="flex items-center">
                        <CheckCircleIcon class="h-5 w-5 text-green-500 mr-3" />
                        <p class="text-green-700 font-medium">{{ $page.props.flash.success }}</p>
                    </div>
                </div>


                <form @submit.prevent="submit" class="space-y-6">

                    <!-- Banner Photo -->
                    <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                        <div class="p-6">
                            <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center gap-2">
                                <PhotoIcon class="h-5 w-5" />
                                Banner de Perfil (Opcional)
                            </h3>
                            <p class="text-sm text-gray-600 mb-4">
                                Imagen de fondo para tu perfil público. Tamaño recomendado: 1920x400px
                            </p>

                            <!-- Banner Preview -->
                            <div class="mb-4">
                                <div class="relative h-48 bg-gray-100 rounded-lg overflow-hidden">
                                    <img v-if="bannerPhotoPreview || photographer.banner_photo_url"
                                        :src="bannerPhotoPreview || photographer.banner_photo_url"
                                        class="w-full h-full object-cover" alt="Banner" />
                                    <div v-else class="flex items-center justify-center h-full text-gray-400">
                                        <div class="text-center">
                                            <PhotoIcon class="h-12 w-12 mx-auto mb-2" />
                                            <p class="text-sm">Sin banner</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="flex gap-3">
                                <label
                                    class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg font-semibold transition cursor-pointer text-center">
                                    <PhotoIcon class="inline h-4 w-4 mr-2" />
                                    Seleccionar Banner
                                    <input type="file" accept="image/jpeg,image/png,image/jpg,image/webp"
                                        @change="handleBannerPhotoChange" class="hidden" />
                                </label>

                                <button v-if="photographer.banner_photo_url" @click.prevent="deleteBannerPhoto"
                                    type="button"
                                    class="bg-red-100 hover:bg-red-200 text-red-700 px-4 py-2 rounded-lg font-semibold transition inline-flex items-center gap-2">
                                    <TrashIcon class="h-4 w-4" />
                                    Eliminar
                                </button>
                            </div>

                            <p v-if="form.errors.banner_photo" class="mt-2 text-sm text-red-600">
                                {{ form.errors.banner_photo }}
                            </p>
                        </div>
                    </div>

                    <!-- Profile Photo -->
                    <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                        <div class="p-6">
                            <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center gap-2">
                                <UserCircleIcon class="h-5 w-5" />
                                Foto de Perfil (Opcional)
                            </h3>
                            <p class="text-sm text-gray-600 mb-4">
                                Tu foto de perfil se mostrará en el directorio de fotógrafos. Tamaño recomendado:
                                400x400px
                            </p>

                            <!-- Profile Photo Preview -->
                            <div class="mb-4 flex justify-center">
                                <div class="relative">
                                    <div
                                        class="h-40 w-40 rounded-full overflow-hidden bg-gray-100 border-4 border-white shadow-lg">
                                        <img v-if="profilePhotoPreview || photographer.profile_photo_url"
                                            :src="profilePhotoPreview || photographer.profile_photo_url"
                                            class="w-full h-full object-cover" alt="Foto de perfil" />
                                        <div v-else class="flex items-center justify-center h-full text-gray-400">
                                            <UserCircleIcon class="h-20 w-20" />
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="flex gap-3">
                                <label
                                    class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg font-semibold transition cursor-pointer text-center">
                                    <PhotoIcon class="inline h-4 w-4 mr-2" />
                                    Seleccionar Foto
                                    <input type="file" accept="image/jpeg,image/png,image/jpg,image/webp"
                                        @change="handleProfilePhotoChange" class="hidden" />
                                </label>

                                <button v-if="photographer.profile_photo_url" @click.prevent="deleteProfilePhoto"
                                    type="button"
                                    class="bg-red-100 hover:bg-red-200 text-red-700 px-4 py-2 rounded-lg font-semibold transition inline-flex items-center gap-2">
                                    <TrashIcon class="h-4 w-4" />
                                    Eliminar
                                </button>
                            </div>

                            <p v-if="form.errors.profile_photo" class="mt-2 text-sm text-red-600">
                                {{ form.errors.profile_photo }}
                            </p>
                        </div>
                    </div>

                    <!-- Business Info -->
                    <div class="bg-white rounded-xl shadow-lg p-6">
                        <h3 class="text-lg font-bold text-gray-900 mb-4">Información del Negocio</h3>

                        <div class="space-y-4">
                            <!-- Business Name -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Nombre del Negocio <span class="text-red-500">*</span>
                                </label>
                                <input v-model="form.business_name" type="text"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                    placeholder="Ej: Estudio Fotográfico XYZ" required />
                                <p v-if="form.errors.business_name" class="mt-2 text-sm text-red-600">
                                    {{ form.errors.business_name }}
                                </p>
                            </div>

                            <!-- Region -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Región <span class="text-red-500">*</span>
                                </label>
                                <select v-model="form.region"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                    required>
                                    <option value="">Selecciona una región</option>
                                    <option v-for="region in regions" :key="region" :value="region">
                                        {{ region }}
                                    </option>
                                </select>
                                <p v-if="form.errors.region" class="mt-2 text-sm text-red-600">
                                    {{ form.errors.region }}
                                </p>
                            </div>

                            <!-- Phone -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Teléfono
                                </label>
                                <input v-model="form.phone" type="text"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                    placeholder="+54 9 11 1234-5678" />
                                <p v-if="form.errors.phone" class="mt-2 text-sm text-red-600">
                                    {{ form.errors.phone }}
                                </p>
                            </div>

                            <!-- Bio -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Biografía / Descripción
                                </label>
                                <textarea v-model="form.bio" rows="4"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                    placeholder="Contanos sobre tu experiencia, servicios y especialidades..."
                                    maxlength="1000"></textarea>
                                <p class="mt-1 text-sm text-gray-500">
                                    {{ form.bio?.length || 0 }} / 1000 caracteres
                                </p>
                                <p v-if="form.errors.bio" class="mt-2 text-sm text-red-600">
                                    {{ form.errors.bio }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="flex justify-end gap-4">
                        <button type="submit" :disabled="form.processing"
                            class="bg-indigo-600 hover:bg-indigo-700 text-white px-8 py-3 rounded-lg font-semibold transition disabled:opacity-50 inline-flex items-center gap-2">
                            <CheckCircleIcon class="h-5 w-5" />
                            {{ form.processing ? 'Guardando...' : 'Guardar Cambios' }}
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
