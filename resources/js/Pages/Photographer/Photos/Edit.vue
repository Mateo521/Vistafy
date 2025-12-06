<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ArrowLeftIcon, CheckIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
    photo: Object,
    events: Array,
});

const form = useForm({
    price: props.photo.price,
    is_active: props.photo.is_active,
    event_id: props.photo.event_id,
});

const submit = () => {
    form.put(route('photographer.photos.update', props.photo.id), {
        preserveScroll: true,
    });
};

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString('es-ES', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
};
</script>

<template>
    <Head :title="`Editar ${photo.unique_id}`" />

    <AuthenticatedLayout>
        <div class="py-12">
            <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
                
                <div class="mb-8 border-b border-gray-200 pb-6 flex justify-between items-end">
                    <div>
                        <Link :href="route('photographer.photos.show', photo.id)" class="text-[10px] font-bold uppercase tracking-widest text-slate-400 hover:text-slate-900 mb-2 block transition-colors flex items-center gap-1">
                            <ArrowLeftIcon class="w-3 h-3" /> Volver al Detalle
                        </Link>
                        <h1 class="text-3xl font-serif font-bold text-slate-900">
                            Editar Propiedades
                        </h1>
                        <p class="text-sm text-slate-500 font-light mt-1 font-mono">ID: {{ photo.unique_id }}</p>
                    </div>
                </div>

                <div class="bg-white border border-gray-200 rounded-sm shadow-sm overflow-hidden">
                    <div class="grid grid-cols-1 lg:grid-cols-2">
                        
                        <div class="bg-slate-50 p-8 flex flex-col items-center justify-center border-b lg:border-b-0 lg:border-r border-gray-200">
                            <div class="relative shadow-lg max-w-full">
                                <img
                                    :src="photo.watermarked_url || photo.thumbnail_url"
                                    :alt="photo.unique_id"
                                    class="max-h-[500px] object-contain rounded-sm"
                                />
                            </div>
                            
                            <div class="mt-8 w-full max-w-sm">
                                <div class="bg-white p-4 border border-gray-200 rounded-sm text-xs text-slate-500 space-y-2">
                                    <div class="flex justify-between border-b border-gray-100 pb-2">
                                        <span class="uppercase tracking-widest font-bold">Dimensiones</span>
                                        <span class="font-mono">{{ photo.width }} x {{ photo.height }} px</span>
                                    </div>
                                    <div class="flex justify-between border-b border-gray-100 pb-2">
                                        <span class="uppercase tracking-widest font-bold">Descargas</span>
                                        <span class="font-mono">{{ photo.downloads }}</span>
                                    </div>
                                    <div class="flex justify-between pt-1">
                                        <span class="uppercase tracking-widest font-bold">Subida</span>
                                        <span class="font-mono">{{ formatDate(photo.created_at) }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="p-8 lg:p-12 flex flex-col justify-center">
                            <h3 class="text-lg font-serif font-bold text-slate-900 mb-6">Configuración de Venta</h3>
                            
                            <form @submit.prevent="submit" class="space-y-6">
                                
                                <div>
                                    <label for="event_id" class="block text-xs font-bold uppercase tracking-wide text-slate-500 mb-2">
                                        Asignar a Evento
                                    </label>
                                    <div class="relative">
                                        <select
                                            id="event_id"
                                            v-model="form.event_id"
                                            class="w-full border-gray-300 rounded-sm focus:border-slate-900 focus:ring-0 text-sm text-slate-700 bg-white"
                                        >
                                            <option :value="null">-- Sin asignar --</option>
                                            <option v-for="event in events" :key="event.id" :value="event.id">
                                                {{ event.name }}
                                            </option>
                                        </select>
                                    </div>
                                    <p class="text-[10px] text-slate-400 mt-1">Vincula la foto a una galería específica.</p>
                                    <p v-if="form.errors.event_id" class="mt-1 text-xs text-red-600">{{ form.errors.event_id }}</p>
                                </div>

                                <div>
                                    <label for="price" class="block text-xs font-bold uppercase tracking-wide text-slate-500 mb-2">
                                        Precio Unitario (USD)
                                    </label>
                                    <input
                                        id="price"
                                        type="number"
                                        v-model="form.price"
                                        step="0.01"
                                        min="0"
                                        required
                                        class="w-full border-gray-300 rounded-sm focus:border-slate-900 focus:ring-0 text-slate-900 font-mono text-lg"
                                    />
                                    <p v-if="form.errors.price" class="mt-1 text-xs text-red-600">{{ form.errors.price }}</p>
                                </div>

                                <div class="pt-2">
                                    <label class="flex items-start cursor-pointer group">
                                        <div class="flex items-center h-5">
                                            <input
                                                type="checkbox"
                                                v-model="form.is_active"
                                                class="w-4 h-4 text-slate-900 border-gray-300 rounded-sm focus:ring-slate-900"
                                            />
                                        </div>
                                        <div class="ml-3 text-sm">
                                            <span class="font-bold text-slate-700 block mb-1">Visible en Galería</span>
                                            <span class="text-xs text-slate-500 font-light block">Si se desmarca, la foto quedará oculta para los clientes pero visible en tu archivo.</span>
                                        </div>
                                    </label>
                                </div>

                                <transition enter-active-class="transition ease-out duration-300" enter-from-class="opacity-0 translate-y-2" enter-to-class="opacity-100 translate-y-0" leave-active-class="transition ease-in duration-200" leave-from-class="opacity-100" leave-to-class="opacity-0">
                                    <div v-if="form.recentlySuccessful" class="bg-emerald-50 text-emerald-800 px-4 py-3 rounded-sm text-xs font-medium flex items-center gap-2 border border-emerald-100">
                                        <CheckIcon class="w-4 h-4" /> Cambios guardados correctamente.
                                    </div>
                                </transition>

                                <div class="pt-6 flex gap-4 border-t border-gray-100 mt-8">
                                    <Link
                                        :href="route('photographer.photos.show', photo.id)"
                                        class="flex-1 px-6 py-3 border border-slate-300 text-slate-600 text-center text-xs font-bold uppercase tracking-widest hover:border-slate-900 hover:text-slate-900 transition rounded-sm"
                                    >
                                        Cancelar
                                    </Link>
                                    <button
                                        type="submit"
                                        :disabled="form.processing"
                                        class="flex-1 px-6 py-3 bg-slate-900 text-white text-xs font-bold uppercase tracking-widest hover:bg-slate-800 transition rounded-sm shadow-md disabled:opacity-70 disabled:cursor-not-allowed"
                                    >
                                        {{ form.processing ? 'Guardando...' : 'Guardar Cambios' }}
                                    </button>
                                </div>

                            </form>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>