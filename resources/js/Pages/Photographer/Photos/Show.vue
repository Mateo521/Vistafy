<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import {
    PencilSquareIcon,
    TrashIcon,
    ArrowLeftIcon,
    InformationCircleIcon,
    CurrencyDollarIcon,
    TagIcon,
    ArrowDownTrayIcon
} from '@heroicons/vue/24/outline';

const props = defineProps({
    photo: Object,
});

const formatBibNumbers = (bibNumbers) => {
    if (!bibNumbers) return [];
    try {
        return typeof bibNumbers === 'string' ? JSON.parse(bibNumbers) : bibNumbers;
    } catch {
        return [];
    }
};


const deletePhoto = () => {
    if (confirm('ATENCIÓN: ¿Estás seguro de eliminar esta foto permanentemente? Esta acción no se puede deshacer.')) {
        router.delete(route('photographer.photos.destroy', props.photo.id), {
            onSuccess: () => {
                router.visit(route('photographer.photos.index'));
            }
        });
    }
};

const formatFileSize = (bytes) => {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
};

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString('es-ES', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};
</script>

<template>

    <Head :title="`Foto ${photo.unique_id}`" />

    <AuthenticatedLayout>
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

                <div
                    class="mb-8 border-b border-gray-200 pb-6 flex flex-col md:flex-row md:items-end justify-between gap-4">
                    <div>
                        <Link :href="route('photographer.photos.index')"
                            class="text-[10px] font-bold uppercase tracking-widest text-slate-400 hover:text-slate-900 mb-2 block transition-colors flex items-center gap-1">
                            <ArrowLeftIcon class="w-3 h-3" /> Volver al Archivo
                        </Link>
                        <h1 class="text-3xl font-sans font-bold text-slate-900">
                            Detalle de Activo
                        </h1>
                        <p class="text-sm text-slate-500 font-light mt-1 font-mono">ID: {{ photo.unique_id }}</p>
                    </div>

                    <div class="flex gap-3">
                        <Link :href="route('photographer.photos.edit', photo.id)"
                            class="px-5 py-2 bg-slate-900 text-white text-xs font-bold uppercase tracking-widest hover:bg-slate-800 transition rounded-sm flex items-center gap-2">
                            <PencilSquareIcon class="w-4 h-4" /> Editar
                        </Link>
                        <button @click="deletePhoto"
                            class="px-5 py-2 border border-red-200 text-red-600 text-xs font-bold uppercase tracking-widest hover:bg-red-50 hover:border-red-400 transition rounded-sm flex items-center gap-2">
                            <TrashIcon class="w-4 h-4" /> Eliminar
                        </button>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

                    <div class="lg:col-span-2">
                        <div
                            class="bg-slate-100 border border-gray-200 rounded-sm p-8 flex items-center justify-center min-h-[500px] shadow-inner">
                            <img :src="photo.watermarked_url || photo.thumbnail_url" :alt="photo.unique_id"
                                class="max-w-full max-h-[70vh] object-contain shadow-lg rounded-sm bg-white" />
                        </div>
                        <div class="mt-4 flex justify-between items-center text-xs text-slate-400">
                            <span>Vista previa con marca de agua</span>
                            <a :src="photo.watermarked_url" target="_blank"
                                class="hover:text-slate-900 underline decoration-1 underline-offset-2">Abrir original en
                                nueva pestaña</a>
                        </div>
                    </div>

                    <div class="lg:col-span-1 space-y-6">

                        <div class="bg-white border border-gray-200 rounded-sm shadow-sm overflow-hidden">
                            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50">
                                <h3
                                    class="text-xs font-bold uppercase tracking-widest text-slate-900 flex items-center gap-2">
                                    <CurrencyDollarIcon class="w-4 h-4" /> Información Comercial
                                </h3>
                            </div>
                            <div class="p-6 space-y-4">
                                <div>
                                    <label
                                        class="block text-[10px] font-bold uppercase tracking-wide text-slate-400 mb-1">Precio
                                        Unitario</label>
                                    <p class="text-2xl font-sans font-bold text-slate-900">${{ photo.price }}</p>
                                </div>

                                <div>
                                    <label
                                        class="block text-[10px] font-bold uppercase tracking-wide text-slate-400 mb-1">Estado</label>
                                    <div class="flex items-center gap-2">
                                        <div
                                            :class="['w-2 h-2 rounded-full', photo.is_active ? 'bg-emerald-500' : 'bg-slate-300']">
                                        </div>
                                        <span
                                            :class="['text-sm font-medium', photo.is_active ? 'text-emerald-700' : 'text-slate-500']">
                                            {{ photo.is_active ? 'Publicada' : 'Oculta' }}
                                        </span>
                                    </div>
                                </div>

                                <div v-if="photo.event">
                                    <label
                                        class="block text-[10px] font-bold uppercase tracking-wide text-slate-400 mb-1">Evento
                                        Asociado</label>
                                    <Link :href="route('photographer.events.show', photo.event.id)"
                                        class="text-sm font-medium text-indigo-600 hover:text-indigo-800 hover:underline">
                                        {{ photo.event.name }}
                                    </Link>
                                </div>
                            </div>





                        </div>

                        <div class="bg-white border border-gray-200 rounded-sm shadow-sm overflow-hidden">
                            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50">
                                <h3
                                    class="text-xs font-bold uppercase tracking-widest text-slate-900 flex items-center gap-2">
                                    <TagIcon class="w-4 h-4" /> Ficha Técnica
                                </h3>
                            </div>
                            <div class="divide-y divide-gray-100">
                                <div class="px-6 py-3 flex justify-between items-center">
                                    <span class="text-xs text-slate-500">Dimensiones</span>
                                    <span class="text-sm font-mono text-slate-700">{{ photo.width }} x {{ photo.height
                                        }} px</span>
                                </div>
                                <div class="px-6 py-3 flex justify-between items-center">
                                    <span class="text-xs text-slate-500">Peso</span>
                                    <span class="text-sm font-mono text-slate-700">{{ formatFileSize(photo.file_size)
                                        }}</span>
                                </div>
                                <div class="px-6 py-3 flex justify-between items-center">
                                    <span class="text-xs text-slate-500">Formato</span>
                                    <span class="text-sm font-mono text-slate-700 uppercase">
                                        {{ photo.file_path ? photo.file_path.split('.').pop() : 'N/A' }}
                                    </span>
                                </div>

                                <div class="px-6 py-3 flex justify-between items-center"
                                    :class="photo.has_faces ? 'bg-blue-50/30' : ''">
                                    <span class="text-xs text-slate-500 flex items-center gap-2">
                                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        Rostros
                                    </span>
                                    <div class="flex items-center gap-2">
                                        <span v-if="photo.has_faces" class="text-sm font-bold text-blue-700">
                                            {{ photo.face_encodings ? (typeof photo.face_encodings === 'string' ?
                                                JSON.parse(photo.face_encodings).length : photo.face_encodings.length) : 0
                                            }}
                                        </span>
                                        <span v-else class="text-sm text-slate-400 font-mono">—</span>
                                        <span v-if="photo.has_faces"
                                            class="px-2 py-0.5 bg-blue-600 text-white text-[9px] font-bold uppercase tracking-wider rounded">
                                            Detectado
                                        </span>
                                    </div>
                                </div>

                                <!--  NUEVO: Dorsales Detectados -->
                                <div class="px-6 py-3 flex justify-between items-start"
                                    :class="photo.bib_processed && formatBibNumbers(photo.bib_numbers).length > 0 ? 'bg-emerald-50/30' : ''">
                                    <span class="text-xs text-slate-500 flex items-center gap-2 pt-1">
                                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14" />
                                        </svg>
                                        Dorsales
                                    </span>
                                    <div class="flex flex-col items-end gap-2">
                                        <div v-if="photo.bib_processed && formatBibNumbers(photo.bib_numbers).length > 0"
                                            class="flex flex-wrap gap-1 justify-end">
                                            <span v-for="bib in formatBibNumbers(photo.bib_numbers)" :key="bib"
                                                class="px-2 py-1 bg-emerald-100 border border-emerald-300 text-emerald-800 text-xs font-bold font-mono rounded">
                                                #{{ bib }}
                                            </span>
                                        </div>
                                        <div v-else class="flex items-center gap-2">
                                            <span class="text-sm text-slate-400 font-mono">—</span>
                                            <span
                                                class="px-2 py-0.5 bg-slate-200 text-slate-500 text-[9px] font-bold uppercase tracking-wider rounded">
                                                No detectado
                                            </span>
                                        </div>
                                    </div>
                                </div>


                                <div class="px-6 py-3 flex justify-between items-center">
                                    <span class="text-xs text-slate-500">Subida</span>
                                    <span class="text-sm font-mono text-slate-700 text-right">{{
                                        formatDate(photo.created_at) }}
                                    </span>
                                </div>



                            </div>
                        </div>

                        <div class="bg-white border border-gray-200 rounded-sm shadow-sm overflow-hidden">
                            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50">
                                <h3
                                    class="text-xs font-bold uppercase tracking-widest text-slate-900 flex items-center gap-2">
                                    <ArrowDownTrayIcon class="w-4 h-4" /> Rendimiento
                                </h3>
                            </div>
                            <div class="p-6 flex items-center justify-between">
                                <span class="text-sm text-slate-600">Descargas Totales</span>
                                <span class="text-xl font-sans font-bold text-slate-900">{{ photo.downloads }}</span>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>