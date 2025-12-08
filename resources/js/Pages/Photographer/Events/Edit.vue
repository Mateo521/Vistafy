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

const handleImageChange = (e) => {
    const file = e.target.files[0];
    if (file) {
        form.cover_image = file;
        previewImage.value = URL.createObjectURL(file);
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
            // Notificación opcional
        },
    });
};

const deleteEvent = () => {
    if (confirm('ATENCIÓN: ¿Estás seguro de eliminar este evento? Esta acción no se puede deshacer y se borrarán todas las fotos asociadas.')) {
        router.delete(route('photographer.events.destroy', props.event.id));
    }
};
</script>

<template>
    <Head title="Editar Evento" />

    <AuthenticatedLayout>
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                
                <div class="mb-10 border-b border-gray-200 pb-6 flex flex-col md:flex-row md:items-end justify-between gap-4">
                    <div>
                        <Link :href="route('photographer.events.index')" class="text-[10px] font-bold uppercase tracking-widest text-slate-400 hover:text-slate-900 mb-2 block transition-colors flex items-center gap-1">
                            <ArrowLeftIcon class="w-3 h-3" /> Volver
                        </Link>
                        <h1 class="text-3xl font-serif font-bold text-slate-900">
                            Editar Evento
                        </h1>
                        <p class="text-sm text-slate-500 font-light mt-1">Modifique los detalles, visibilidad o elimine la galería.</p>
                    </div>
                    
                    <Link :href="route('photographer.events.show', event.id)" 
                        class="px-5 py-2 border border-slate-300 text-slate-600 text-xs font-bold uppercase tracking-widest hover:border-slate-900 hover:text-slate-900 transition rounded-sm">
                        Ver Galería
                    </Link>
                </div>

                <form @submit.prevent="submit" class="space-y-8">

                    <div class="bg-white border border-gray-200 p-8 rounded-sm shadow-sm">
                        <h2 class="text-xs font-bold uppercase tracking-widest text-slate-900 mb-6 border-b border-gray-100 pb-2">
                            Detalles Principales
                        </h2>

                        <div class="space-y-6">
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wide text-slate-500 mb-2">Nombre del Evento</label>
                                <input v-model="form.name" type="text" required
                                    class="w-full border-gray-300 rounded-sm focus:border-slate-900 focus:ring-0 text-slate-900"
                                    placeholder="Nombre del evento" />
                                <div v-if="form.errors.name" class="text-red-600 text-xs mt-1">{{ form.errors.name }}</div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-xs font-bold uppercase tracking-wide text-slate-500 mb-2">Fecha</label>
                                    <input v-model="form.event_date" type="date" required
                                        class="w-full border-gray-300 rounded-sm focus:border-slate-900 focus:ring-0 text-slate-900" />
                                    <div v-if="form.errors.event_date" class="text-red-600 text-xs mt-1">{{ form.errors.event_date }}</div>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold uppercase tracking-wide text-slate-500 mb-2">Ubicación</label>
                                    <input v-model="form.location" type="text"
                                        class="w-full border-gray-300 rounded-sm focus:border-slate-900 focus:ring-0 text-slate-900"
                                        placeholder="Lugar del evento" />
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wide text-slate-500 mb-2">Descripción Corta</label>
                                <textarea v-model="form.description" rows="2" maxlength="500"
                                    class="w-full border-gray-300 rounded-sm focus:border-slate-900 focus:ring-0 text-slate-900 resize-none"></textarea>
                                <p class="text-[10px] text-right text-slate-400">{{ form.description?.length || 0 }}/500</p>
                            </div>

                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wide text-slate-500 mb-2">Descripción Detallada</label>
                                <textarea v-model="form.long_description" rows="5" maxlength="2000"
                                    class="w-full border-gray-300 rounded-sm focus:border-slate-900 focus:ring-0 text-slate-900"></textarea>
                                <p class="text-[10px] text-right text-slate-400">{{ form.long_description?.length || 0 }}/2000</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white border border-gray-200 p-8 rounded-sm shadow-sm">
                        <h2 class="text-xs font-bold uppercase tracking-widest text-slate-900 mb-6 border-b border-gray-100 pb-2">
                            Imagen de Portada
                        </h2>

                        <div class="flex flex-col md:flex-row gap-8">
                            <div class="w-full md:w-1/2">
                                <div class="aspect-video bg-gray-100 border border-gray-200 rounded-sm overflow-hidden relative group">
                                    <img v-if="previewImage" :src="previewImage" class="w-full h-full object-cover" />
                                    <div v-else class="w-full h-full flex items-center justify-center text-slate-300">
                                        <PhotoIcon class="w-12 h-12" />
                                    </div>
                                    <div class="absolute inset-0 bg-black/50 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                                        <span class="text-white text-xs font-bold uppercase tracking-widest">Vista Previa</span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="flex-1 flex flex-col justify-center">
                                <label class="block text-xs font-bold uppercase tracking-wide text-slate-500 mb-3">Cambiar Imagen</label>
                                <input type="file" accept="image/jpeg,image/png,image/jpg" @change="handleImageChange"
                                    class="block w-full text-sm text-slate-500
                                    file:mr-4 file:py-2 file:px-4
                                    file:rounded-sm file:border-0
                                    file:text-xs file:font-bold file:uppercase file:tracking-widest
                                    file:bg-slate-100 file:text-slate-700
                                    hover:file:bg-slate-200 cursor-pointer" 
                                />
                                <p class="text-xs text-slate-400 mt-2 font-light">Se recomienda una imagen horizontal de alta resolución (JPG, PNG).</p>
                                <div v-if="form.errors.cover_image" class="text-red-600 text-xs mt-2">{{ form.errors.cover_image }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white border border-gray-200 p-8 rounded-sm shadow-sm">
                        <h2 class="text-xs font-bold uppercase tracking-widest text-slate-900 mb-6 border-b border-gray-100 pb-2">
                            Configuración de Acceso
                        </h2>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <label class="cursor-pointer group">
                                <input type="radio" class="peer sr-only" :checked="form.is_active && !form.is_private" @change="form.is_active = true; form.is_private = false">
                                <div class="p-4 border border-gray-200 rounded-sm hover:border-slate-400 peer-checked:border-emerald-500 peer-checked:bg-emerald-50/30 transition-all h-full">
                                    <div class="flex items-center gap-2 mb-2 text-emerald-700">
                                        <GlobeAltIcon class="w-5 h-5" />
                                        <span class="text-sm font-bold uppercase tracking-wide">Público</span>
                                    </div>
                                    <p class="text-xs text-slate-500 font-light">Visible para todos en el directorio.</p>
                                </div>
                            </label>

                            <label class="cursor-pointer group">
                                <input type="radio" class="peer sr-only" :checked="form.is_active && form.is_private" @change="form.is_active = true; form.is_private = true">
                                <div class="p-4 border border-gray-200 rounded-sm hover:border-slate-400 peer-checked:border-amber-500 peer-checked:bg-amber-50/30 transition-all h-full">
                                    <div class="flex items-center gap-2 mb-2 text-amber-700">
                                        <LockClosedIcon class="w-5 h-5" />
                                        <span class="text-sm font-bold uppercase tracking-wide">Privado</span>
                                    </div>
                                    <p class="text-xs text-slate-500 font-light">Solo accesible mediante enlace/token.</p>
                                </div>
                            </label>

                            <label class="cursor-pointer group">
                                <input type="radio" class="peer sr-only" :checked="!form.is_active" @change="form.is_active = false; form.is_private = false">
                                <div class="p-4 border border-gray-200 rounded-sm hover:border-slate-400 peer-checked:border-slate-500 peer-checked:bg-slate-50 transition-all h-full">
                                    <div class="flex items-center gap-2 mb-2 text-slate-700">
                                        <EyeSlashIcon class="w-5 h-5" />
                                        <span class="text-sm font-bold uppercase tracking-wide">Borrador</span>
                                    </div>
                                    <p class="text-xs text-slate-500 font-light">Oculto. Solo visible para usted.</p>
                                </div>
                            </label>
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-4 pt-4">
                        <Link :href="route('photographer.events.index')" 
                            class="px-6 py-3 border border-slate-300 text-slate-600 text-xs font-bold uppercase tracking-widest hover:border-slate-900 hover:text-slate-900 transition rounded-sm">
                            Cancelar
                        </Link>
                        <button type="submit" :disabled="form.processing" 
                            class="px-8 py-3 bg-slate-900 text-white text-xs font-bold uppercase tracking-widest hover:bg-slate-800 transition rounded-sm shadow-md disabled:opacity-50">
                            {{ form.processing ? 'Guardando...' : 'Guardar Cambios' }}
                        </button>
                    </div>

                </form>

                <div class="mt-16 pt-10 border-t border-red-100">
                    <div class="bg-red-50/50 border border-red-100 rounded-sm p-6 flex flex-col md:flex-row items-center justify-between gap-6">
                        <div>
                            <h3 class="text-sm font-bold text-red-700 uppercase tracking-wider mb-1 flex items-center gap-2">
                                <TrashIcon class="w-4 h-4" /> Zona de Peligro
                            </h3>
                            <p class="text-xs text-red-600/70 font-light max-w-lg">
                                Esta acción eliminará permanentemente el evento y todas las fotografías asociadas. Esta acción no se puede deshacer.
                            </p>
                        </div>
                        <button @click="deleteEvent" 
                            class="px-6 py-2 bg-white border border-red-200 text-red-600 text-xs font-bold uppercase tracking-widest hover:bg-red-600 hover:text-white hover:border-red-600 transition rounded-sm whitespace-nowrap">
                            Eliminar Evento
                        </button>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>