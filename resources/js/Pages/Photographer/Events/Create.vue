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
    <Head title="Nuevo Evento" />

    <AuthenticatedLayout>
        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                
                <div class="mb-10 border-b border-gray-200 pb-6 flex flex-col md:flex-row md:items-end justify-between gap-4">
                    <div>
                        <Link :href="route('photographer.events.index')" class="text-[10px] font-bold uppercase tracking-widest text-slate-400 hover:text-slate-900 mb-2 block transition-colors">
                            ← Volver a Eventos
                        </Link>
                        <h1 class="text-3xl font-serif font-bold text-slate-900">
                            Crear Nuevo Evento
                        </h1>
                        <p class="text-sm text-slate-500 font-light mt-1">Configure los detalles y la visibilidad de su nueva galería.</p>
                    </div>
                </div>

                <form @submit.prevent="submit" class="space-y-8">
                    
                    <div class="bg-white border border-gray-200 p-8 rounded-sm shadow-sm">
                        <h2 class="text-xs font-bold uppercase tracking-widest text-slate-900 mb-6 border-b border-gray-100 pb-2">
                            Información General
                        </h2>

                        <div class="space-y-6">
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wide text-slate-500 mb-2">Nombre del Evento</label>
                                <input
                                    v-model="form.name"
                                    type="text"
                                    required
                                    class="w-full border-gray-300 rounded-sm focus:border-slate-900 focus:ring-0 text-slate-900 placeholder-gray-400"
                                    placeholder="Ej: Boda Smith & Jones"
                                />
                                <p v-if="form.errors.name" class="text-red-600 text-xs mt-1">{{ form.errors.name }}</p>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-xs font-bold uppercase tracking-wide text-slate-500 mb-2">Fecha</label>
                                    <input
                                        v-model="form.event_date"
                                        type="date"
                                        required
                                        class="w-full border-gray-300 rounded-sm focus:border-slate-900 focus:ring-0 text-slate-900"
                                    />
                                    <p v-if="form.errors.event_date" class="text-red-600 text-xs mt-1">{{ form.errors.event_date }}</p>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold uppercase tracking-wide text-slate-500 mb-2">Ubicación</label>
                                    <input
                                        v-model="form.location"
                                        type="text"
                                        class="w-full border-gray-300 rounded-sm focus:border-slate-900 focus:ring-0 text-slate-900 placeholder-gray-400"
                                        placeholder="Ciudad, Lugar..."
                                    />
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wide text-slate-500 mb-2">Resumen (SEO)</label>
                                <textarea
                                    v-model="form.description"
                                    rows="2"
                                    maxlength="500"
                                    class="w-full border-gray-300 rounded-sm focus:border-slate-900 focus:ring-0 text-slate-900 placeholder-gray-400 resize-none"
                                    placeholder="Breve descripción para listados..."
                                ></textarea>
                                <p class="text-[10px] text-slate-400 text-right mt-1">{{ form.description?.length || 0 }}/500</p>
                            </div>

                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wide text-slate-500 mb-2">Detalles Completos</label>
                                <textarea
                                    v-model="form.long_description"
                                    rows="5"
                                    maxlength="2000"
                                    class="w-full border-gray-300 rounded-sm focus:border-slate-900 focus:ring-0 text-slate-900 placeholder-gray-400"
                                    placeholder="Información detallada para los asistentes..."
                                ></textarea>
                                <p class="text-[10px] text-slate-400 text-right mt-1">{{ form.long_description?.length || 0 }}/2000</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white border border-gray-200 p-8 rounded-sm shadow-sm">
                        <h2 class="text-xs font-bold uppercase tracking-widest text-slate-900 mb-6 border-b border-gray-100 pb-2">
                            Configuración de Acceso
                        </h2>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <label class="cursor-pointer group">
                                <input type="radio" name="visibility" class="peer sr-only" 
                                    :checked="form.is_active && !form.is_private"
                                    @change="form.is_active = true; form.is_private = false"
                                >
                                <div class="p-4 border border-gray-200 rounded-sm hover:border-slate-400 peer-checked:border-emerald-500 peer-checked:bg-emerald-50/30 transition-all h-full">
                                    <div class="flex items-center gap-2 mb-2 text-emerald-700">
                                        <GlobeAltIcon class="w-5 h-5" />
                                        <span class="text-sm font-bold uppercase tracking-wide">Público</span>
                                    </div>
                                    <p class="text-xs text-slate-500 font-light">Visible para todos en el directorio general.</p>
                                </div>
                            </label>

                            <label class="cursor-pointer group">
                                <input type="radio" name="visibility" class="peer sr-only" 
                                    :checked="form.is_active && form.is_private"
                                    @change="form.is_active = true; form.is_private = true"
                                >
                                <div class="p-4 border border-gray-200 rounded-sm hover:border-slate-400 peer-checked:border-amber-500 peer-checked:bg-amber-50/30 transition-all h-full">
                                    <div class="flex items-center gap-2 mb-2 text-amber-700">
                                        <LockClosedIcon class="w-5 h-5" />
                                        <span class="text-sm font-bold uppercase tracking-wide">Privado</span>
                                    </div>
                                    <p class="text-xs text-slate-500 font-light">Accesible solo mediante enlace directo o contraseña.</p>
                                </div>
                            </label>

                            <label class="cursor-pointer group">
                                <input type="radio" name="visibility" class="peer sr-only" 
                                    :checked="!form.is_active"
                                    @change="form.is_active = false; form.is_private = false"
                                >
                                <div class="p-4 border border-gray-200 rounded-sm hover:border-slate-400 peer-checked:border-slate-500 peer-checked:bg-slate-50 transition-all h-full">
                                    <div class="flex items-center gap-2 mb-2 text-slate-700">
                                        <EyeSlashIcon class="w-5 h-5" />
                                        <span class="text-sm font-bold uppercase tracking-wide">Borrador</span>
                                    </div>
                                    <p class="text-xs text-slate-500 font-light">No visible. Guardar para publicar más tarde.</p>
                                </div>
                            </label>
                        </div>
                    </div>

                    <div class="bg-white border border-gray-200 p-8 rounded-sm shadow-sm">
                        <h2 class="text-xs font-bold uppercase tracking-widest text-slate-900 mb-6 border-b border-gray-100 pb-2">
                            Identidad Visual
                        </h2>

                        <div class="flex flex-col md:flex-row gap-8 items-start">
                            <div class="w-full md:w-1/3 aspect-video bg-gray-100 border border-gray-200 flex items-center justify-center overflow-hidden rounded-sm relative">
                                <img v-if="coverImagePreview" :src="coverImagePreview" class="w-full h-full object-cover">
                                <div v-else class="text-slate-300 flex flex-col items-center">
                                    <PhotoIcon class="w-12 h-12 mb-2" />
                                    <span class="text-[10px] uppercase tracking-widest">Vista Previa</span>
                                </div>
                            </div>

                            <div class="flex-1">
                                <label class="block text-xs font-bold uppercase tracking-wide text-slate-500 mb-2">Imagen de Portada</label>
                                <label class="flex flex-col items-center justify-center w-full h-32 border-2 border-gray-300 border-dashed rounded-sm cursor-pointer hover:bg-gray-50 hover:border-slate-400 transition-all">
                                    <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                        <p class="mb-1 text-sm text-slate-700 font-medium">Haga clic para subir</p>
                                        <p class="text-xs text-slate-500">JPG, PNG (Max. 5MB)</p>
                                    </div>
                                    <input type="file" class="hidden" accept="image/*" @change="handleCoverImageChange" />
                                </label>
                                <p v-if="form.errors.cover_image" class="text-red-600 text-xs mt-2">{{ form.errors.cover_image }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end gap-4 pt-6">
                        <Link :href="route('photographer.events.index')" class="px-6 py-3 border border-slate-300 text-slate-600 text-xs font-bold uppercase tracking-widest hover:border-slate-900 hover:text-slate-900 transition rounded-sm">
                            Cancelar
                        </Link>
                        <button type="submit" :disabled="form.processing" class="px-8 py-3 bg-slate-900 text-white text-xs font-bold uppercase tracking-widest hover:bg-slate-800 transition rounded-sm shadow-md disabled:opacity-50">
                            {{ form.processing ? 'Guardando...' : 'Crear Evento' }}
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>