<template>
    <PhotographerLayout>
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-900">Editar Perfil</h1>
                <p class="text-gray-600 mt-2">Actualiza tu información profesional</p>
            </div>

            <form @submit.prevent="submitForm" class="space-y-8">
                <!-- Fotos de Perfil y Portada -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-xl font-bold text-gray-900 mb-6">Imágenes de Perfil</h2>
                    
                    <!-- Foto de Perfil -->
                    <div class="mb-6">
                        <label class="block text-sm font-semibold text-gray-700 mb-3">Foto de Perfil</label>
                        <div class="flex items-center space-x-6">
                            <div class="relative">
                                <img 
                                    v-if="profilePhotoPreview"
                                    :src="profilePhotoPreview" 
                                    class="w-24 h-24 rounded-full object-cover border-4 border-indigo-100"
                                >
                                <div 
                                    v-else-if="photographer.profile_photo_url"
                                    class="w-24 h-24 rounded-full overflow-hidden border-4 border-indigo-100"
                                >
                                    <img :src="photographer.profile_photo_url" class="w-full h-full object-cover">
                                </div>
                                <div 
                                    v-else
                                    class="w-24 h-24 rounded-full bg-indigo-600 flex items-center justify-center text-white text-3xl font-bold border-4 border-indigo-100"
                                >
                                    {{ photographer.business_name.charAt(0).toUpperCase() }}
                                </div>
                            </div>
                            <div>
                                <input 
                                    type="file" 
                                    @change="handleProfilePhoto" 
                                    accept="image/*"
                                    ref="profilePhotoInput"
                                    class="hidden"
                                >
                                <button 
                                    type="button"
                                    @click="$refs.profilePhotoInput.click()"
                                    class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 transition"
                                >
                                    Cambiar Foto
                                </button>
                                <p class="text-xs text-gray-500 mt-2">JPG, PNG. Max 2MB</p>
                            </div>
                        </div>
                    </div>

                    <!-- Foto de Portada -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-3">Foto de Portada</label>
                        <div class="space-y-3">
                            <div 
                                v-if="coverPhotoPreview || photographer.cover_photo_url"
                                class="relative h-48 rounded-lg overflow-hidden"
                            >
                                <img 
                                    :src="coverPhotoPreview || photographer.cover_photo_url" 
                                    class="w-full h-full object-cover"
                                >
                            </div>
                            <div 
                                v-else
                                class="h-48 rounded-lg bg-gradient-to-r from-indigo-500 to-purple-600"
                            ></div>
                            
                            <input 
                                type="file" 
                                @change="handleCoverPhoto" 
                                accept="image/*"
                                ref="coverPhotoInput"
                                class="hidden"
                            >
                            <button 
                                type="button"
                                @click="$refs.coverPhotoInput.click()"
                                class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 transition"
                            >
                                Cambiar Portada
                            </button>
                            <p class="text-xs text-gray-500">JPG, PNG. Max 5MB. Recomendado: 1920x400px</p>
                        </div>
                    </div>
                </div>

                <!-- Información Básica -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-xl font-bold text-gray-900 mb-6">Información Básica</h2>
                    
                    <div class="space-y-6">
                        <!-- Nombre del Negocio -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                Nombre del Negocio *
                            </label>
                            <input 
                                v-model="form.business_name"
                                type="text"
                                required
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                placeholder="Ej: Fotografía Profesional JP"
                            >
                            <p v-if="errors.business_name" class="text-red-500 text-sm mt-1">{{ errors.business_name }}</p>
                        </div>

                        <!-- Región -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                Región *
                            </label>
                            <select 
                                v-model="form.region"
                                required
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                            >
                                <option value="norte">Norte</option>
                                <option value="centro">Centro</option>
                                <option value="sur">Sur</option>
                            </select>
                            <p v-if="errors.region" class="text-red-500 text-sm mt-1">{{ errors.region }}</p>
                        </div>

                        <!-- Teléfono -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                Teléfono
                            </label>
                            <input 
                                v-model="form.phone"
                                type="tel"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                placeholder="+1234567890"
                            >
                            <p v-if="errors.phone" class="text-red-500 text-sm mt-1">{{ errors.phone }}</p>
                        </div>

                        <!-- Biografía -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                Biografía
                            </label>
                            <textarea 
                                v-model="form.bio"
                                rows="5"
                                maxlength="1000"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                placeholder="Cuéntanos sobre tu experiencia como fotógrafo..."
                            ></textarea>
                            <p class="text-xs text-gray-500 mt-1">{{ form.bio?.length || 0 }}/1000 caracteres</p>
                            <p v-if="errors.bio" class="text-red-500 text-sm mt-1">{{ errors.bio }}</p>
                        </div>
                    </div>
                </div>

                <!-- Botones -->
                <div class="flex items-center justify-between">
                    <Link 
                        :href="route('photographer.profile')"
                        class="text-gray-600 hover:text-gray-900 font-semibold"
                    >
                        Cancelar
                    </Link>
                    <button 
                        type="submit"
                        :disabled="form.processing"
                        class="bg-indigo-600 text-white px-8 py-3 rounded-lg hover:bg-indigo-700 transition font-semibold disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        <span v-if="!form.processing">Guardar Cambios</span>
                        <span v-else>Guardando...</span>
                    </button>
                </div>
            </form>
        </div>
    </PhotographerLayout>
</template>

<script setup>
import PhotographerLayout from '@/Layouts/PhotographerLayout.vue';
import { Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    photographer: Object,
    errors: Object,
});

const form = useForm({
    business_name: props.photographer.business_name,
    region: props.photographer.region,
    phone: props.photographer.phone,
    bio: props.photographer.bio,
    profile_photo: null,
    cover_photo: null,
});

const profilePhotoPreview = ref(null);
const coverPhotoPreview = ref(null);

const handleProfilePhoto = (e) => {
    const file = e.target.files[0];
    if (file) {
        form.profile_photo = file;
        profilePhotoPreview.value = URL.createObjectURL(file);
    }
};

const handleCoverPhoto = (e) => {
    const file = e.target.files[0];
    if (file) {
        form.cover_photo = file;
        coverPhotoPreview.value = URL.createObjectURL(file);
    }
};

const submitForm = () => {
    form.post(route('photographer.profile.update'), {
        preserveScroll: true,
        onSuccess: () => {
            profilePhotoPreview.value = null;
            coverPhotoPreview.value = null;
        },
    });
};
</script>
