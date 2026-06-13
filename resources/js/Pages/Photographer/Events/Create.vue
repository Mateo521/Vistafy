<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import { PhotoIcon, GlobeAltIcon, LockClosedIcon, EyeSlashIcon } from '@heroicons/vue/24/outline';

const form = useForm({
    name: '',
    description: '',
    long_description: '',
    event_date: '',
    location: '',
    is_private: false,
    is_active: true,
    cover_image: null,
});

const coverImagePreview = ref(null);

const compressCoverImage = async (file) => {
    return new Promise((resolve) => {
        const reader = new FileReader();
        reader.onload = (e) => {
            const img = new Image();
            img.onload = () => {
                const canvas = document.createElement('canvas');
                let width = img.width;
                let height = img.height;

                const maxSize = 1920;

                if (width > height && width > maxSize) {
                    height = Math.round((height * maxSize) / width);
                    width = maxSize;
                } else if (height > maxSize) {
                    width = Math.round((width * maxSize) / height);
                    height = maxSize;
                }

                canvas.width = width;
                canvas.height = height;
                const ctx = canvas.getContext('2d');
                ctx.drawImage(img, 0, 0, width, height);

                canvas.toBlob((blob) => {
                    const compressedFile = new File([blob], file.name.replace(/\.[^/.]+$/, "") + ".jpg", {
                        type: 'image/jpeg',
                        lastModified: Date.now()
                    });
                    resolve(compressedFile);
                }, 'image/jpeg', 0.8);
            };
            img.src = e.target.result;
        };
        reader.readAsDataURL(file);
    });
};

const handleCoverImageChange = async (e) => {
    const file = e.target.files[0];
    if (file) {
        coverImagePreview.value = URL.createObjectURL(file);

        try {
            const compressedFile = await compressCoverImage(file);
            form.cover_image = compressedFile;
        } catch (error) {
            console.error("Error al comprimir la imagen de portada:", error);
            form.cover_image = file;
        }
    }
};

const submit = () => {
    form.post(route('photographer.events.store'), {
        forceFormData: true,
    });
};
</script>

<template>
    <Head title="Nuevo Evento" />

    <AuthenticatedLayout>
        <div class="bg-[#050505] min-h-screen text-white font-sans selection:bg-[#E30613] selection:text-white py-12 md:py-20">
            <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">

                <div class="mb-12 border-b-[4px] border-white/20 pb-8 flex flex-col md:flex-row md:items-end justify-between gap-4">
                    <div>
                        <Link :href="route('photographer.events.index')"
                            class="text-[10px] font-mono font-bold uppercase tracking-widest text-gray-500 hover:text-white mb-4 block transition-none">
                            [ Volver a Eventos ]
                        </Link>
                        <h1 class="text-5xl md:text-7xl font-flux tracking-tighter leading-none uppercase text-white">
                            Crear Nuevo <span class="text-[#E30613]">Evento</span>
                        </h1>
                        <p class="font-mono text-[10px] text-gray-400 uppercase tracking-widest mt-4">
                            Configure los detalles y la visibilidad de su nueva galería.
                        </p>
                    </div>
                </div>

                <form @submit.prevent="submit" class="space-y-8">

                    <div class="bg-[#09090b] border-[4px] border-white/10 p-6 md:p-10 transition-none focus-within:border-white/30">
                        <h2 class="font-mono text-[10px] font-bold uppercase  text-[#E30613] mb-8 border-b border-white/10 pb-4">
                            Información general
                        </h2>

                        <div class="space-y-8">
                            <div>
                                <label class="block font-mono text-[10px] font-bold uppercase tracking-widest text-gray-400 mb-3">
                                    Nombre del Evento
                                </label>
                                <input v-model="form.name" type="text" required
                                    class="w-full bg-black border-2 border-white/20 rounded-none focus:border-[#E30613] focus:ring-0 text-white font-sans text-lg py-3 px-4 placeholder-gray-700 transition-none"
                                    placeholder="Ej: Boda Smith & Jones" />
                                <p v-if="form.errors.name" class="font-mono text-[#E30613] text-[10px] uppercase tracking-widest mt-2 font-bold">{{ form.errors.name }}</p>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                                <div>
                                    <label class="block font-mono text-[10px] font-bold uppercase tracking-widest text-gray-400 mb-3">
                                        Fecha
                                    </label>
                                    <input v-model="form.event_date" type="date" required
                                        class="w-full bg-black border-2 border-white/20 rounded-none focus:border-[#E30613] focus:ring-0 text-white font-mono text-sm py-3 px-4 [color-scheme:dark] transition-none" />
                                    <p v-if="form.errors.event_date" class="font-mono text-[#E30613] text-[10px] uppercase tracking-widest mt-2 font-bold">{{ form.errors.event_date }}</p>
                                </div>
                                <div>
                                    <label class="block font-mono text-[10px] font-bold uppercase tracking-widest text-gray-400 mb-3">
                                        Ubicación
                                    </label>
                                    <input v-model="form.location" type="text"
                                        class="w-full bg-black border-2 border-white/20 rounded-none focus:border-[#E30613] focus:ring-0 text-white font-sans text-sm py-3 px-4 placeholder-gray-700 transition-none"
                                        placeholder="Ciudad, Lugar..." />
                                </div>
                            </div>

                            <div>
                                <label class="block font-mono text-[10px] font-bold uppercase tracking-widest text-gray-400 mb-3">
                                    Resumen (SEO)
                                </label>
                                <textarea v-model="form.description" rows="2" maxlength="500"
                                    class="w-full bg-black border-2 border-white/20 rounded-none focus:border-[#E30613] focus:ring-0 text-white font-sans text-sm py-3 px-4 placeholder-gray-700 resize-none transition-none"
                                    placeholder="Breve descripción para listados..."></textarea>
                                <p class="font-mono text-[9px] text-gray-500 text-right mt-2 tracking-widest">
                                    {{ form.description?.length || 0 }}/500
                                </p>
                            </div>

                            <div>
                                <label class="block font-mono text-[10px] font-bold uppercase tracking-widest text-gray-400 mb-3">
                                    Detalles Completos
                                </label>
                                <textarea v-model="form.long_description" rows="5" maxlength="2000"
                                    class="w-full bg-black border-2 border-white/20 rounded-none focus:border-[#E30613] focus:ring-0 text-white font-sans text-sm py-3 px-4 placeholder-gray-700 transition-none"
                                    placeholder="Información detallada para los asistentes..."></textarea>
                                <p class="font-mono text-[9px] text-gray-500 text-right mt-2 tracking-widest">
                                    {{ form.long_description?.length || 0 }}/2000
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-[#09090b] border-[4px] border-white/10 p-6 md:p-10 transition-none focus-within:border-white/30">
                        <h2 class="font-mono text-[10px] font-bold uppercase  text-[#E30613] mb-8 border-b border-white/10 pb-4">
                            Configuración de Acceso
                        </h2>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <label class="cursor-pointer group relative">
                                <input type="radio" name="visibility" class="peer sr-only"
                                    :checked="form.is_active && !form.is_private"
                                    @change="form.is_active = true; form.is_private = false">
                                <div class="p-6 bg-black border-2 border-white/20 hover:border-white/50 peer-checked:border-[#E30613] peer-checked:bg-[#E30613]/10 transition-none h-full">
                                    <div class="flex items-center gap-3 mb-4 text-white peer-checked:text-[#E30613]">
                                        <GlobeAltIcon class="w-6 h-6" />
                                        <span class="font-mono text-sm font-bold uppercase tracking-widest">Público</span>
                                    </div>
                                    <p class="font-sans text-xs text-gray-400">Visible para todos en el directorio general.</p>
                                </div>
                            </label>

                            <label class="cursor-pointer group relative">
                                <input type="radio" name="visibility" class="peer sr-only"
                                    :checked="form.is_active && form.is_private"
                                    @change="form.is_active = true; form.is_private = true">
                                <div class="p-6 bg-black border-2 border-white/20 hover:border-white/50 peer-checked:border-amber-500 peer-checked:bg-amber-500/10 transition-none h-full">
                                    <div class="flex items-center gap-3 mb-4 text-white peer-checked:text-amber-500">
                                        <LockClosedIcon class="w-6 h-6" />
                                        <span class="font-mono text-sm font-bold uppercase tracking-widest">Privado</span>
                                    </div>
                                    <p class="font-sans text-xs text-gray-400">Accesible solo mediante enlace directo o contraseña.</p>
                                </div>
                            </label>

                            <label class="cursor-pointer group relative">
                                <input type="radio" name="visibility" class="peer sr-only" :checked="!form.is_active"
                                    @change="form.is_active = false; form.is_private = false">
                                <div class="p-6 bg-black border-2 border-white/20 hover:border-white/50 peer-checked:border-white peer-checked:bg-white/5 transition-none h-full">
                                    <div class="flex items-center gap-3 mb-4 text-gray-400 peer-checked:text-white">
                                        <EyeSlashIcon class="w-6 h-6" />
                                        <span class="font-mono text-sm font-bold uppercase tracking-widest">Borrador</span>
                                    </div>
                                    <p class="font-sans text-xs text-gray-500">No visible. Guardar para publicar más tarde.</p>
                                </div>
                            </label>
                        </div>
                    </div>

                    <div class="bg-[#09090b] border-[4px] border-white/10 p-6 md:p-10 transition-none focus-within:border-white/30">
                        <h2 class="font-mono text-[10px] font-bold uppercase  text-[#E30613] mb-8 border-b border-white/10 pb-4">
                            Identidad Visual
                        </h2>

                        <div class="flex flex-col md:flex-row gap-8 items-start">
                            <div class="w-full md:w-1/3 aspect-video bg-black border-2 border-white/20 flex items-center justify-center overflow-hidden relative">
                                <img v-if="coverImagePreview" :src="coverImagePreview" class="w-full h-full object-cover grayscale contrast-125">
                                <div v-else class="text-gray-600 flex flex-col items-center">
                                    <PhotoIcon class="w-12 h-12 mb-3" />
                                    <span class="font-mono text-[9px] uppercase tracking-widest">Vista Previa</span>
                                </div>
                            </div>

                            <div class="flex-1 w-full">
                                <label class="block font-mono text-[10px] font-bold uppercase tracking-widest text-gray-400 mb-3">
                                    Imagen de Portada
                                </label>
                                <label class="flex flex-col items-center justify-center w-full h-32 border-2 border-dashed border-white/30 bg-black cursor-pointer hover:border-[#E30613] transition-none group">
                                    <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                        <p class="mb-2 font-mono text-xs text-white font-bold uppercase tracking-widest group-hover:text-[#E30613]">Haga clic para subir</p>
                                        <p class="font-mono text-[10px] text-gray-500 uppercase tracking-widest">JPG, PNG (Max. 5MB)</p>
                                    </div>
                                    <input type="file" class="hidden" accept="image/*" @change="handleCoverImageChange" />
                                </label>
                                <p v-if="form.errors.cover_image" class="font-mono text-[#E30613] text-[10px] uppercase tracking-widest mt-3 font-bold">{{ form.errors.cover_image }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="flex flex-col sm:flex-row justify-end gap-4 pt-8 border-t-[4px] border-white/10 mt-8">
                        <Link :href="route('photographer.events.index')"
                            class="px-8 py-4 border-2 border-white/30 text-white font-mono text-[10px] font-bold uppercase tracking-widest hover:border-white transition-none text-center">
                            Cancelar
                        </Link>
                        <button type="submit" :disabled="form.processing"
                            class="px-10 py-4 bg-[#E30613] border-2 border-[#E30613] text-black font-black font-sans text-sm uppercase  hover:bg-white hover:border-white transition-none disabled:opacity-50 text-center">
                            {{ form.processing ? 'PROCESANDO...' : 'CREAR EVENTO' }}
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>