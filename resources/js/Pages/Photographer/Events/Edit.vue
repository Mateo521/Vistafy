<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, router, Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import { 
    PhotoIcon, 
    GlobeAltIcon, 
    LockClosedIcon, 
    EyeSlashIcon, 
    TrashIcon,
    ExclamationTriangleIcon,
    ArrowLeftIcon
} from '@heroicons/vue/24/outline';

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

const handleImageChange = async (e) => {
    const file = e.target.files[0];
    if (file) {
        previewImage.value = URL.createObjectURL(file);
        const compressedFile = await compressCoverImage(file);
        form.cover_image = compressedFile;
    }
};

const submit = () => {
    form.transform((data) => ({
        ...data,
        _method: 'PUT'
    })).post(route('photographer.events.update', props.event.id), {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
            
        },
    });
};

const deleteEvent = () => {
    if (confirm('ATENCIÓN_CRÍTICA: ¿Confirmar purga del evento? Se borrarán todas las fotos asociadas. Esta acción es IRREVERSIBLE.')) {
        router.delete(route('photographer.events.destroy', props.event.id));
    }
};
</script>

<template>
    <Head :title="`Editar: ${event.name}`" />

    <AuthenticatedLayout>
        <div class="min-h-screen bg-[#F8F9FA] text-slate-800 font-sans antialiased py-12 pt-28">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                

                <div class="mb-10 flex flex-col md:flex-row md:items-end justify-between gap-6 border-b border-gray-200 pb-8">
                    <div>
                        <Link :href="route('photographer.events.index')" 
                            class="inline-flex items-center gap-2 bg-white px-4 py-2 rounded-full text-xs font-bold uppercase tracking-wider text-gray-500 hover:text-black hover:shadow-sm border border-gray-200 transition-all mb-6">
                            <ArrowLeftIcon class="w-4 h-4" /> Volver a eventos
                        </Link>
                        
                        <div class="flex items-center gap-2 mb-2">
                            <span class="w-2 h-2 rounded-full bg-[#E30613] animate-pulse"></span>
                            <span class="text-xs font-bold text-gray-500 uppercase tracking-wider">Modo edición</span>
                        </div>
                        
                        <h1 class="text-5xl md:text-7xl font-flux text-black tracking-wide leading-none mb-3">
                            Modificar <span class="text-[#E30613]">evento</span>
                        </h1>
                        <p class="text-sm font-medium text-gray-500">
                            Modificá los detalles, la portada o la visibilidad de tu galería.
                        </p>
                    </div>
                    
                    <Link :href="route('photographer.events.show', event.id)" 
                        class="bg-white border border-gray-200 text-black hover:bg-gray-50 px-6 py-3.5 rounded-full text-xs font-bold uppercase tracking-wider transition-all shadow-sm flex items-center justify-center gap-2 w-max">
                        <EyeIcon class="w-4 h-4" /> Inspeccionar galería
                    </Link>
                </div>

                <form @submit.prevent="submit" class="space-y-8">


                    <div class="bg-white border border-gray-100 rounded shadow-[0_8px_30px_rgb(0,0,0,0.04)] p-6 md:p-10 transition-all duration-300 hover:shadow-md">
                        <h2 class="text-xs font-bold uppercase tracking-widest text-gray-400 mb-8 flex items-center gap-2">
                            <span class="w-4 h-px bg-gray-200"></span> Parámetros principales
                        </h2>

                        <div class="space-y-6">
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 mb-2 ml-1">
                                    Nombre del evento
                                </label>
                                <input v-model="form.name" type="text" required
                                    class="w-full bg-gray-50 border border-transparent focus:bg-white focus:border-gray-300 focus:ring-4 focus:ring-gray-100 text-slate-800 font-bold text-lg py-3.5 px-4 rounded transition-all outline-none placeholder-gray-400"
                                    placeholder="Ej: Boda Smith & Jones" />
                                <div v-if="form.errors.name" class="text-[#E30613] text-xs font-bold mt-2 ml-1">{{ form.errors.name }}</div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 mb-2 ml-1">
                                        Fecha del evento
                                    </label>
                                    <input v-model="form.event_date" type="date" required
                                        class="w-full bg-gray-50 border border-transparent focus:bg-white focus:border-gray-300 focus:ring-4 focus:ring-gray-100 text-slate-700 font-bold text-sm py-3.5 px-4 rounded transition-all outline-none" />
                                    <div v-if="form.errors.event_date" class="text-[#E30613] text-xs font-bold mt-2 ml-1">{{ form.errors.event_date }}</div>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 mb-2 ml-1">
                                        Ubicación
                                    </label>
                                    <input v-model="form.location" type="text"
                                        class="w-full bg-gray-50 border border-transparent focus:bg-white focus:border-gray-300 focus:ring-4 focus:ring-gray-100 text-slate-700 font-bold text-sm py-3.5 px-4 rounded transition-all outline-none placeholder-gray-400"
                                        placeholder="Ciudad o establecimiento" />
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 mb-2 ml-1">
                                    Resumen corto
                                </label>
                                <textarea v-model="form.description" rows="2" maxlength="500"
                                    class="w-full bg-gray-50 border border-transparent focus:bg-white focus:border-gray-300 focus:ring-4 focus:ring-gray-100 text-slate-700 font-medium text-sm py-3.5 px-4 rounded transition-all outline-none resize-none placeholder-gray-400"></textarea>
                                <p class="text-xs text-gray-400 text-right mt-2 font-medium">{{ form.description?.length || 0 }}/500 caracteres</p>
                            </div>

                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 mb-2 ml-1">
                                    Detalles completos
                                </label>
                                <textarea v-model="form.long_description" rows="5" maxlength="2000"
                                    class="w-full bg-gray-50 border border-transparent focus:bg-white focus:border-gray-300 focus:ring-4 focus:ring-gray-100 text-slate-700 font-medium text-sm py-3.5 px-4 rounded transition-all outline-none placeholder-gray-400"></textarea>
                                <p class="text-xs text-gray-400 text-right mt-2 font-medium">{{ form.long_description?.length || 0 }}/2000 caracteres</p>
                            </div>
                        </div>
                    </div>


                    <div class="bg-white border border-gray-100 rounded shadow-[0_8px_30px_rgb(0,0,0,0.04)] p-6 md:p-10 transition-all duration-300 hover:shadow-md">
                        <h2 class="text-xs font-bold uppercase tracking-widest text-gray-400 mb-8 flex items-center gap-2">
                            <span class="w-4 h-px bg-gray-200"></span> Imagen de portada
                        </h2>

                        <div class="flex flex-col md:flex-row gap-8 items-center">
                            

                            <div class="w-full md:w-5/12 aspect-video bg-gray-50 rounded border border-gray-200 flex items-center justify-center overflow-hidden relative shadow-inner">
                                <img v-if="previewImage" :src="previewImage" class="w-full h-full object-cover transition-transform duration-500 hover:scale-105" />
                                <div v-else class="text-gray-300 flex flex-col items-center">
                                    <PhotoIcon class="w-12 h-12 mb-2" />
                                    <span class="text-[10px] font-bold uppercase tracking-widest text-gray-400">Sin portada</span>
                                </div>
                            </div>
                            

                            <div class="flex-1 w-full">
                                <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 mb-3 ml-1">
                                    Modificar imagen
                                </label>
                                <label class="flex flex-col items-center justify-center w-full h-32 border-2 border-dashed border-gray-300 bg-white rounded cursor-pointer hover:bg-gray-50 hover:border-black transition-all group">
                                    <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                        <CloudArrowUpIcon class="w-8 h-8 text-gray-400 group-hover:text-black mb-2 transition-colors" />
                                        <p class="mb-1 text-sm font-bold text-gray-600 group-hover:text-black transition-colors">Seleccioná una nueva imagen</p>
                                        <p class="text-xs text-gray-400 font-medium">JPG, PNG. Auto-compresión habilitada.</p>
                                    </div>
                                    <input type="file" accept="image/jpeg,image/png,image/jpg" @change="handleImageChange" class="hidden" />
                                </label>
                                <div v-if="form.errors.cover_image" class="text-[#E30613] text-xs font-bold mt-2 ml-1">{{ form.errors.cover_image }}</div>
                            </div>
                        </div>
                    </div>


                    <div class="bg-white border border-gray-100 rounded shadow-[0_8px_30px_rgb(0,0,0,0.04)] p-6 md:p-10 transition-all duration-300 hover:shadow-md">
                        <h2 class="text-xs font-bold uppercase tracking-widest text-gray-400 mb-8 flex items-center gap-2">
                            <span class="w-4 h-px bg-gray-200"></span> Visibilidad y acceso
                        </h2>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

                            <label class="cursor-pointer group relative flex-1">
                                <input type="radio" class="peer sr-only" :checked="form.is_active && !form.is_private" @change="form.is_active = true; form.is_private = false">
                                <div class="p-6 bg-white border-2 border-gray-100 rounded hover:border-gray-200 peer-checked:border-black peer-checked:shadow-md transition-all h-full">
                                    <div class="flex items-center gap-3 mb-4 text-gray-400 peer-checked:text-black">
                                        <GlobeAltIcon class="w-6 h-6" />
                                        <span class="font-bold text-sm uppercase tracking-wider">Público</span>
                                    </div>
                                    <p class="text-xs text-gray-500 leading-relaxed font-medium">Visible para todos los usuarios.</p>
                                </div>
                            </label>


                            <label class="cursor-pointer group relative flex-1">
                                <input type="radio" class="peer sr-only" :checked="form.is_active && form.is_private" @change="form.is_active = true; form.is_private = true">
                                <div class="p-6 bg-white border-2 border-gray-100 rounded hover:border-gray-200 peer-checked:border-[#E30613] peer-checked:bg-red-50/30 peer-checked:shadow-md transition-all h-full">
                                    <div class="flex items-center gap-3 mb-4 text-gray-400 peer-checked:text-[#E30613]">
                                        <LockClosedIcon class="w-6 h-6" />
                                        <span class="font-bold text-sm uppercase tracking-wider">Privado</span>
                                    </div>
                                    <p class="text-xs text-gray-500 leading-relaxed font-medium">Acceso restringido. Requiere enlace directo para verlo.</p>
                                </div>
                            </label>


                            <label class="cursor-pointer group relative flex-1">
                                <input type="radio" class="peer sr-only" :checked="!form.is_active" @change="form.is_active = false; form.is_private = false">
                                <div class="p-6 bg-white border-2 border-gray-100 rounded hover:border-gray-200 peer-checked:border-gray-400 peer-checked:bg-gray-50 peer-checked:shadow-md transition-all h-full">
                                    <div class="flex items-center gap-3 mb-4 text-gray-300 peer-checked:text-gray-600">
                                        <EyeSlashIcon class="w-6 h-6" />
                                        <span class="font-bold text-sm uppercase tracking-wider">Borrador</span>
                                    </div>
                                    <p class="text-xs text-gray-500 leading-relaxed font-medium">Evento oculto. Visibilidad exclusiva para vos como administrador.</p>
                                </div>
                            </label>
                        </div>
                    </div>


                    <div class="flex flex-col-reverse sm:flex-row justify-end gap-3 pt-4">
                        <Link :href="route('photographer.events.index')" 
                            class="px-8 py-3.5 rounded-full border border-gray-200 bg-white text-gray-600 font-bold text-xs uppercase tracking-wider hover:bg-gray-50 hover:text-black transition-colors text-center shadow-sm">
                            Cancelar
                        </Link>
                        <button type="submit" :disabled="form.processing" 
                            class="px-10 py-3.5 rounded-full bg-black text-white font-bold text-xs uppercase tracking-wider hover:bg-[#E30613] hover:shadow-lg hover:shadow-red-500/30 hover:-translate-y-1 transition-all disabled:opacity-50 disabled:hover:translate-y-0 disabled:hover:shadow-none text-center flex items-center justify-center gap-2">
                            <span v-if="form.processing" class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin"></span>
                            {{ form.processing ? 'Guardando...' : 'Guardar cambios' }}
                        </button>
                    </div>

                </form>


                <div class="mt-16 pt-8 border-t border-gray-200">
                    <div class="bg-red-50 border border-red-100 rounded p-6 md:p-8 flex flex-col md:flex-row items-center justify-between gap-6 relative overflow-hidden">
                        
                        <div class="absolute -right-6 -bottom-6 w-32 h-32 bg-red-100 rounded-full blur-2xl pointer-events-none"></div>
                        
                        <div class="relative z-10 flex items-start gap-4">
                            <div class="w-12 h-12 bg-white rounded-full flex items-center justify-center text-[#E30613] shadow-sm shrink-0">
                                <ExclamationTriangleIcon class="w-6 h-6" />
                            </div>
                            <div>
                                <h3 class="font-bold text-lg text-[#E30613] mb-1">
                                    Peligro: Eliminar evento
                                </h3>
                                <p class="text-sm text-red-800/80 leading-relaxed max-w-2xl font-medium">
                                    Esta acción eliminará permanentemente el evento y <strong>todas las fotos asociadas</strong>. Los datos no podrán ser recuperados.
                                </p>
                            </div>
                        </div>
                        
                        <button @click="deleteEvent" 
                            class="relative z-10 px-6 py-3.5 bg-white border border-red-200 text-[#E30613] text-xs font-bold uppercase tracking-wider hover:bg-[#E30613] hover:text-white transition-colors rounded-full shadow-sm w-full md:w-auto flex items-center justify-center gap-2 shrink-0">
                            <TrashIcon class="w-4 h-4" /> Eliminar definitivamente
                        </button>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>