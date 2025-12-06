<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import {
    UserCircleIcon,
    PhotoIcon,
    TrashIcon,
    CheckCircleIcon,
    ArrowUpTrayIcon,
    GlobeAltIcon,
    CameraIcon,
    HandThumbUpIcon
} from '@heroicons/vue/24/outline';

const props = defineProps({
    photographer: Object,
});

const form = useForm({
    _method: 'PATCH', // Truco para permitir archivos en modo Edición
    business_name: props.photographer.business_name,
    bio: props.photographer.bio || '',
    phone: props.photographer.phone || '',
    region: props.photographer.region,
    // --- NUEVOS CAMPOS ---
    website: props.photographer.website || '',
    instagram: props.photographer.instagram || '',
    facebook: props.photographer.facebook || '',
    // ---------------------
    profile_photo: null,
    banner_photo: null,
});

const profilePhotoPreview = ref(null);
const bannerPhotoPreview = ref(null);

const regions = [
    'Buenos Aires', 'CABA', 'Córdoba', 'Santa Fe', 'Mendoza', 
    'Tucumán', 'Rosario', 'Salta', 'Neuquén', 'Entre Ríos',
];

const handleProfilePhotoChange = (event) => {
    const file = event.target.files[0];
    if (file) {
        form.profile_photo = file;
        const reader = new FileReader();
        reader.onload = (e) => profilePhotoPreview.value = e.target.result;
        reader.readAsDataURL(file);
    }
};

const handleBannerPhotoChange = (event) => {
    const file = event.target.files[0];
    if (file) {
        form.banner_photo = file;
        const reader = new FileReader();
        reader.onload = (e) => bannerPhotoPreview.value = e.target.result;
        reader.readAsDataURL(file);
    }
};

const deleteProfilePhoto = () => {
    if (confirm('¿Eliminar la foto de perfil?')) {
        router.delete(route('photographer.profile.photo.delete'), { preserveScroll: true });
        profilePhotoPreview.value = null;
    }
};

const deleteBannerPhoto = () => {
    if (confirm('¿Eliminar el banner?')) {
        router.delete(route('photographer.profile.banner.delete'), { preserveScroll: true });
        bannerPhotoPreview.value = null;
    }
};

const submit = () => {
    // Usamos POST porque enviamos archivos, pero Laravel lo interpretará como PATCH gracias al campo _method
    form.post(route('photographer.profile.update'), {
        preserveScroll: true,
        onSuccess: () => {
            // Limpiamos los inputs de archivo para no re-enviarlos
            form.profile_photo = null;
            form.banner_photo = null;
            // No reseteamos los previews para que el usuario vea lo que acaba de subir
        },
    });
};
</script>

<template>
    <Head title="Perfil Profesional" />

    <AuthenticatedLayout>
        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">

                <div class="mb-10 border-b border-gray-200 pb-6">
                    <span class="text-xs font-bold tracking-[0.2em] text-slate-400 uppercase mb-2 block">
                        Configuración
                    </span>
                    <h1 class="text-3xl font-serif font-bold text-slate-900">
                        Perfil Público
                    </h1>
                    <p class="text-sm text-slate-500 font-light mt-1">Gestione su identidad visual y datos de contacto.</p>
                </div>

                <div v-if="$page.props.flash?.success" class="mb-8 bg-emerald-50 text-emerald-800 px-4 py-3 rounded-sm text-xs font-medium flex items-center gap-2 border border-emerald-100">
                    <CheckCircleIcon class="w-4 h-4" /> {{ $page.props.flash.success }}
                </div>

                <form @submit.prevent="submit" class="space-y-8">

                    <div class="bg-white border border-gray-200 p-8 rounded-sm shadow-sm">
                        <h2 class="text-xs font-bold uppercase tracking-widest text-slate-900 mb-6 border-b border-gray-100 pb-2">
                            Identidad Visual
                        </h2>

                        <div class="mb-8">
                            <label class="block text-xs font-bold uppercase tracking-wide text-slate-500 mb-3">Banner de Portada</label>
                            
                            <div class="relative h-48 bg-slate-100 border border-gray-200 rounded-sm overflow-hidden group">
                                <img v-if="bannerPhotoPreview" :src="bannerPhotoPreview" class="w-full h-full object-cover" />
                                <img v-else-if="photographer.banner_photo_url" :src="photographer.banner_photo_url" class="w-full h-full object-cover transition-opacity group-hover:opacity-75" />
                                
                                <div v-else class="flex flex-col items-center justify-center h-full text-slate-300">
                                    <PhotoIcon class="h-10 w-10 mb-2" />
                                    <span class="text-[10px] uppercase tracking-widest">1920x400 Recomendado</span>
                                </div>

                                <div class="absolute inset-0 flex items-center justify-center gap-3 opacity-0 group-hover:opacity-100 transition-opacity bg-black/20 backdrop-blur-[2px]">
                                    <label class="cursor-pointer bg-white text-slate-900 px-4 py-2 text-[10px] font-bold uppercase tracking-widest hover:bg-slate-50 transition rounded-sm flex items-center gap-2 shadow-sm">
                                        <ArrowUpTrayIcon class="w-3 h-3" /> Cambiar
                                        <input type="file" accept="image/*" @change="handleBannerPhotoChange" class="hidden" />
                                    </label>
                                    <button v-if="photographer.banner_photo_url" @click.prevent="deleteBannerPhoto" 
                                        class="bg-red-600 text-white px-4 py-2 text-[10px] font-bold uppercase tracking-widest hover:bg-red-700 transition rounded-sm flex items-center gap-2 shadow-sm">
                                        <TrashIcon class="w-3 h-3" />
                                    </button>
                                </div>
                            </div>
                            <p v-if="form.errors.banner_photo" class="text-red-600 text-xs mt-2">{{ form.errors.banner_photo }}</p>
                        </div>

                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wide text-slate-500 mb-3">Foto de Perfil</label>
                            <div class="flex items-center gap-6">
                                <div class="relative w-24 h-24 bg-slate-100 border border-gray-200 rounded-sm overflow-hidden group flex-shrink-0">
                                    <img v-if="profilePhotoPreview" :src="profilePhotoPreview" class="w-full h-full object-cover" />
                                    <img v-else-if="photographer.profile_photo_url" :src="photographer.profile_photo_url" class="w-full h-full object-cover" />
                                    
                                    <div v-else class="w-full h-full flex items-center justify-center text-slate-300">
                                        <UserCircleIcon class="h-10 w-10" />
                                    </div>
                                </div>

                                <div class="space-y-3">
                                    <div class="flex gap-3">
                                        <label class="cursor-pointer border border-slate-300 text-slate-600 px-4 py-2 text-[10px] font-bold uppercase tracking-widest hover:border-slate-900 hover:text-slate-900 transition rounded-sm bg-white">
                                            Seleccionar
                                            <input type="file" accept="image/*" @change="handleProfilePhotoChange" class="hidden" />
                                        </label>
                                        <button v-if="photographer.profile_photo_url" @click.prevent="deleteProfilePhoto" 
                                            class="text-red-600 hover:text-red-800 text-[10px] font-bold uppercase tracking-widest border border-transparent hover:border-red-200 px-3 py-2 transition rounded-sm">
                                            Eliminar
                                        </button>
                                    </div>
                                    <p class="text-[10px] text-slate-400 font-light">JPG, PNG o WEBP. Máximo 2MB.</p>
                                    <p v-if="form.errors.profile_photo" class="text-red-600 text-xs">{{ form.errors.profile_photo }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white border border-gray-200 p-8 rounded-sm shadow-sm">
                        <h2 class="text-xs font-bold uppercase tracking-widest text-slate-900 mb-6 border-b border-gray-100 pb-2">
                            Datos del Negocio
                        </h2>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                            <div class="md:col-span-2">
                                <label class="block text-xs font-bold uppercase tracking-wide text-slate-500 mb-2">Nombre Comercial</label>
                                <input v-model="form.business_name" type="text"
                                    class="w-full border-gray-300 rounded-sm focus:border-slate-900 focus:ring-0 text-slate-900 placeholder-slate-300 text-sm"
                                    placeholder="Ej: Estudio Fotográfico" required 
                                />
                                <p v-if="form.errors.business_name" class="text-red-600 text-xs mt-1">{{ form.errors.business_name }}</p>
                            </div>

                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wide text-slate-500 mb-2">Región</label>
                                <select v-model="form.region" required
                                    class="w-full border-gray-300 rounded-sm focus:border-slate-900 focus:ring-0 text-slate-900 bg-white text-sm">
                                    <option value="" disabled>Seleccione una opción</option>
                                    <option v-for="region in regions" :key="region" :value="region">{{ region }}</option>
                                </select>
                                <p v-if="form.errors.region" class="text-red-600 text-xs mt-1">{{ form.errors.region }}</p>
                            </div>

                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wide text-slate-500 mb-2">Teléfono</label>
                                <input v-model="form.phone" type="text"
                                    class="w-full border-gray-300 rounded-sm focus:border-slate-900 focus:ring-0 text-slate-900 placeholder-slate-300 text-sm"
                                    placeholder="+54 9 11 ..." 
                                />
                                <p v-if="form.errors.phone" class="text-red-600 text-xs mt-1">{{ form.errors.phone }}</p>
                            </div>

                            <div class="md:col-span-2">
                                <label class="block text-xs font-bold uppercase tracking-wide text-slate-500 mb-2">Biografía</label>
                                <textarea v-model="form.bio" rows="4" maxlength="1000"
                                    class="w-full border-gray-300 rounded-sm focus:border-slate-900 focus:ring-0 text-slate-900 placeholder-slate-300 resize-none text-sm"
                                    placeholder="Describa su experiencia y servicios..."
                                ></textarea>
                                <div class="flex justify-between mt-1">
                                    <p v-if="form.errors.bio" class="text-red-600 text-xs">{{ form.errors.bio }}</p>
                                    <span class="text-[10px] text-slate-400 ml-auto">{{ form.bio?.length || 0 }} / 1000</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white border border-gray-200 p-8 rounded-sm shadow-sm">
                        <h2 class="text-xs font-bold uppercase tracking-widest text-slate-900 mb-6 border-b border-gray-100 pb-2">
                            Presencia Digital
                        </h2>

                        <div class="space-y-5">
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wide text-slate-500 mb-2 flex items-center gap-2">
                                    <GlobeAltIcon class="w-4 h-4" /> Sitio Web
                                </label>
                                <input v-model="form.website" type="url"
                                    class="w-full border-gray-300 rounded-sm focus:border-slate-900 focus:ring-0 text-slate-900 placeholder-slate-300 text-sm"
                                    placeholder="https://www.miportfolio.com" 
                                />
                                <p v-if="form.errors.website" class="text-red-600 text-xs mt-1">{{ form.errors.website }}</p>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-xs font-bold uppercase tracking-wide text-slate-500 mb-2 flex items-center gap-2">
                                        <CameraIcon class="w-4 h-4" /> Instagram
                                    </label>
                                    <div class="relative">
                                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400 text-sm">@</span>
                                        <input v-model="form.instagram" type="text"
                                            class="w-full pl-8 border-gray-300 rounded-sm focus:border-slate-900 focus:ring-0 text-slate-900 placeholder-slate-300 text-sm"
                                            placeholder="usuario" 
                                        />
                                    </div>
                                    <p v-if="form.errors.instagram" class="text-red-600 text-xs mt-1">{{ form.errors.instagram }}</p>
                                </div>

                                <div>
                                    <label class="block text-xs font-bold uppercase tracking-wide text-slate-500 mb-2 flex items-center gap-2">
                                        <HandThumbUpIcon class="w-4 h-4" /> Facebook
                                    </label>
                                    <div class="relative">
                                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400 text-sm">/</span>
                                        <input v-model="form.facebook" type="text"
                                            class="w-full pl-6 border-gray-300 rounded-sm focus:border-slate-900 focus:ring-0 text-slate-900 placeholder-slate-300 text-sm"
                                            placeholder="pagina" 
                                        />
                                    </div>
                                    <p v-if="form.errors.facebook" class="text-red-600 text-xs mt-1">{{ form.errors.facebook }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end pt-4">
                        <button type="submit" :disabled="form.processing"
                            class="bg-slate-900 text-white px-8 py-3 rounded-sm text-xs font-bold uppercase tracking-widest hover:bg-slate-800 transition shadow-md disabled:opacity-50 flex items-center gap-2">
                            <span v-if="form.processing">Guardando...</span>
                            <span v-else>Guardar Cambios</span>
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>