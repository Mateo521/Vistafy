<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import { 
    PhotoIcon, 
    GlobeAltIcon, 
    LockClosedIcon, 
    EyeSlashIcon,
    ArrowLeftIcon,
    CloudArrowUpIcon
} from '@heroicons/vue/24/outline';

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
        <div class="bg-[#F8F9FA] min-h-screen text-slate-800 font-sans antialiased py-12 md:py-24 pt-28">
            <div class="max-w-[1000px] mx-auto px-4 sm:px-6 lg:px-8">

            
                <div class="mb-10 flex flex-col md:flex-row md:items-end justify-between gap-6 border-b border-gray-200 pb-8">
                    <div>
                        <Link :href="route('photographer.events.index')"
                            class="inline-flex items-center gap-2 bg-white px-4 py-2 rounded-full text-xs font-bold uppercase tracking-wider text-gray-500 hover:text-black hover:shadow-sm border border-gray-200 transition-all mb-6">
                            <ArrowLeftIcon class="w-4 h-4" /> Volver a eventos
                        </Link>
                        <h1 class="font-flux text-5xl md:text-7xl text-black leading-none tracking-wide">
                            Crear <span class="text-[#E30613]">Evento</span>
                        </h1>
                        <p class="text-sm font-medium text-gray-500 mt-3">
                            Configurá los detalles, la portada y la visibilidad de su nueva galería.
                        </p>
                    </div>
                </div>

                <form @submit.prevent="submit" class="space-y-8">

                    
                    <div class="bg-white border border-gray-100 rounded-3xl shadow-[0_8px_30px_rgb(0,0,0,0.04)] p-6 md:p-10 transition-all duration-300 hover:shadow-md">
                        <h2 class="text-xs font-bold uppercase tracking-widest text-gray-400 mb-8 flex items-center gap-2">
                            <span class="w-4 h-px bg-gray-200"></span> Información general
                        </h2>

                        <div class="space-y-8">
                        
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 mb-2 ml-1">
                                    Nombre del evento
                                </label>
                                <input v-model="form.name" type="text" required
                                    class="w-full bg-gray-50 border border-transparent focus:bg-white focus:border-gray-300 focus:ring-4 focus:ring-gray-100 text-slate-800 font-bold text-lg py-3.5 px-4 rounded-xl transition-all outline-none placeholder-gray-400"
                                    placeholder="Ej: Boda Smith & Jones, Maratón 42K..." />
                                <p v-if="form.errors.name" class="text-[#E30613] text-xs font-bold mt-2 ml-1">{{ form.errors.name }}</p>
                            </div>

                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 md:gap-8">
                                <div>
                                    <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 mb-2 ml-1">
                                        Fecha
                                    </label>
                                    <input v-model="form.event_date" type="date" required
                                        class="w-full bg-gray-50 border border-transparent focus:bg-white focus:border-gray-300 focus:ring-4 focus:ring-gray-100 text-slate-700 font-bold text-sm py-3.5 px-4 rounded-xl transition-all outline-none" />
                                    <p v-if="form.errors.event_date" class="text-[#E30613] text-xs font-bold mt-2 ml-1">{{ form.errors.event_date }}</p>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 mb-2 ml-1">
                                        Ubicación
                                    </label>
                                    <input v-model="form.location" type="text"
                                        class="w-full bg-gray-50 border border-transparent focus:bg-white focus:border-gray-300 focus:ring-4 focus:ring-gray-100 text-slate-700 font-bold text-sm py-3.5 px-4 rounded-xl transition-all outline-none placeholder-gray-400"
                                        placeholder="Ciudad, Lugar, Estadio..." />
                                </div>
                            </div>

                            
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 mb-2 ml-1">
                                    Resumen
                                </label>
                                <textarea v-model="form.description" rows="2" maxlength="500"
                                    class="w-full bg-gray-50 border border-transparent focus:bg-white focus:border-gray-300 focus:ring-4 focus:ring-gray-100 text-slate-700 font-medium text-sm py-3.5 px-4 rounded-xl transition-all outline-none resize-none placeholder-gray-400"
                                    placeholder="Breve descripción para mostrar en los listados..."></textarea>
                                <p class="text-xs text-gray-400 text-right mt-2 font-medium">
                                    {{ form.description?.length || 0 }}/500
                                </p>
                            </div>

                           
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 mb-2 ml-1">
                                    Detalles completos
                                </label>
                                <textarea v-model="form.long_description" rows="5" maxlength="2000"
                                    class="w-full bg-gray-50 border border-transparent focus:bg-white focus:border-gray-300 focus:ring-4 focus:ring-gray-100 text-slate-700 font-medium text-sm py-3.5 px-4 rounded-xl transition-all outline-none placeholder-gray-400"
                                    placeholder="Información extendida, horarios, indicaciones para los participantes..."></textarea>
                                <p class="text-xs text-gray-400 text-right mt-2 font-medium">
                                    {{ form.long_description?.length || 0 }}/2000
                                </p>
                            </div>
                        </div>
                    </div>

                
                    <div class="bg-white border border-gray-100 rounded-3xl shadow-[0_8px_30px_rgb(0,0,0,0.04)] p-6 md:p-10 transition-all duration-300 hover:shadow-md">
                        <h2 class="text-xs font-bold uppercase tracking-widest text-gray-400 mb-8 flex items-center gap-2">
                            <span class="w-4 h-px bg-gray-200"></span> Configuración de acceso
                        </h2>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            
                            <label class="cursor-pointer group relative flex-1">
                                <input type="radio" name="visibility" class="peer sr-only"
                                    :checked="form.is_active && !form.is_private"
                                    @change="form.is_active = true; form.is_private = false">
                                <div class="p-6 bg-white border-2 border-gray-100 rounded-2xl hover:border-gray-200 peer-checked:border-black peer-checked:shadow-md transition-all h-full">
                                    <div class="flex items-center gap-3 mb-4 text-gray-400 peer-checked:text-black">
                                        <GlobeAltIcon class="w-6 h-6" />
                                        <span class="font-bold text-sm uppercase tracking-wider">Público</span>
                                    </div>
                                    <p class="text-xs text-gray-500 leading-relaxed font-medium">Visible para todos.</p>
                                </div>
                            </label>

                            
                            <label class="cursor-pointer group relative flex-1">
                                <input type="radio" name="visibility" class="peer sr-only"
                                    :checked="form.is_active && form.is_private"
                                    @change="form.is_active = true; form.is_private = true">
                                <div class="p-6 bg-white border-2 border-gray-100 rounded-2xl hover:border-gray-200 peer-checked:border-[#E30613] peer-checked:bg-red-50/30 peer-checked:shadow-md transition-all h-full">
                                    <div class="flex items-center gap-3 mb-4 text-gray-400 peer-checked:text-[#E30613]">
                                        <LockClosedIcon class="w-6 h-6" />
                                        <span class="font-bold text-sm uppercase tracking-wider">Privado</span>
                                    </div>
                                    <p class="text-xs text-gray-500 leading-relaxed font-medium">Accesible solo mediante enlace directo. Oculto del directorio.</p>
                                </div>
                            </label>

                            
                            <label class="cursor-pointer group relative flex-1">
                                <input type="radio" name="visibility" class="peer sr-only" :checked="!form.is_active"
                                    @change="form.is_active = false; form.is_private = false">
                                <div class="p-6 bg-white border-2 border-gray-100 rounded-2xl hover:border-gray-200 peer-checked:border-gray-400 peer-checked:bg-gray-50 peer-checked:shadow-md transition-all h-full">
                                    <div class="flex items-center gap-3 mb-4 text-gray-300 peer-checked:text-gray-600">
                                        <EyeSlashIcon class="w-6 h-6" />
                                        <span class="font-bold text-sm uppercase tracking-wider">Borrador</span>
                                    </div>
                                    <p class="text-xs text-gray-500 leading-relaxed font-medium">Evento desactivado. Podés guardarlo para publicar más tarde.</p>
                                </div>
                            </label>
                        </div>
                    </div>

                   
                    <div class="bg-white border border-gray-100 rounded-3xl shadow-[0_8px_30px_rgb(0,0,0,0.04)] p-6 md:p-10 transition-all duration-300 hover:shadow-md">
                        <h2 class="text-xs font-bold uppercase tracking-widest text-gray-400 mb-8 flex items-center gap-2">
                            <span class="w-4 h-px bg-gray-200"></span> Añadir imagen de portada
                        </h2>

                        <div class="flex flex-col md:flex-row gap-8 items-start">
                            
                             
                            <div class="w-full md:w-5/12 aspect-video bg-gray-50 rounded-2xl border border-gray-200 flex items-center justify-center overflow-hidden relative shadow-inner">
                                <img v-if="coverImagePreview" :src="coverImagePreview" class="w-full h-full object-cover">
                                <div v-else class="text-gray-300 flex flex-col items-center">
                                    <PhotoIcon class="w-12 h-12 mb-2" />
                                    <span class="text-[10px] font-bold uppercase tracking-widest text-gray-400">Sin Portada</span>
                                </div>
                            </div>

                           
                            <div class="flex-1 w-full">
                                <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 mb-3 ml-1">
                                    Imagen principal
                                </label>
                                <label class="flex flex-col items-center justify-center w-full h-32 border-2 border-dashed border-gray-300 bg-white rounded-2xl cursor-pointer hover:bg-gray-50 hover:border-black transition-all group">
                                    <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                        <CloudArrowUpIcon class="w-8 h-8 text-gray-400 group-hover:text-black mb-2 transition-colors" />
                                        <p class="mb-1 text-sm font-bold text-gray-600 group-hover:text-black transition-colors">Selecciona una imagen</p>
                                        <p class="text-xs text-gray-400 font-medium">JPG, PNG (Max. 5MB)</p>
                                    </div>
                                    <input type="file" class="hidden" accept="image/*" @change="handleCoverImageChange" />
                                </label>
                                <p v-if="form.errors.cover_image" class="text-[#E30613] text-xs font-bold mt-2 ml-1">{{ form.errors.cover_image }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="flex flex-col-reverse sm:flex-row justify-end gap-3 pt-6">
                        <Link :href="route('photographer.events.index')"
                            class="px-8 py-3.5 rounded-full border border-gray-200 bg-white text-gray-600 font-bold text-xs uppercase tracking-wider hover:bg-gray-50 hover:text-black transition-colors text-center shadow-sm">
                            Cancelar
                        </Link>
                        <button type="submit" :disabled="form.processing"
                            class="px-10 py-3.5 rounded-full bg-black text-white font-bold text-xs uppercase tracking-wider hover:bg-[#E30613] hover:shadow-lg hover:shadow-red-500/30 hover:-translate-y-1 transition-all disabled:opacity-50 disabled:hover:translate-y-0 disabled:hover:shadow-none text-center flex items-center justify-center gap-2">
                            <span v-if="form.processing" class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin"></span>
                            {{ form.processing ? 'Procesando...' : 'Crear evento' }}
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>