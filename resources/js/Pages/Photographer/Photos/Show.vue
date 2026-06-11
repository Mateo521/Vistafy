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

    <Head :title="`ACTIVO_${photo.unique_id}`" />

    <AuthenticatedLayout>
        <div class="py-12 bg-white min-h-screen">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

                <div class="mb-8 border-b-4 border-black pb-6 flex flex-col md:flex-row md:items-end justify-between gap-6">
                    <div>
                        <Link :href="route('photographer.photos.index')"
                            class="font-mono text-[10px] font-bold uppercase tracking-widest text-zinc-500 hover:text-black hover:bg-zinc-100 p-1 mb-4 inline-flex items-center gap-2 transition-colors border border-transparent hover:border-black">
                            <ArrowLeftIcon class="w-3 h-3" /> < Volver_al_Archivo
                        </Link>
                        <h1 class="text-5xl md:text-6xl font-bebas text-black uppercase leading-none tracking-tight">
                            Detalle de Activo
                        </h1>
                        <p class="mt-2 font-mono text-xs font-bold uppercase tracking-widest text-white bg-black inline-block px-3 py-1">
                            ID // {{ photo.unique_id }}
                        </p>
                    </div>

                    <div class="flex gap-4">
                        <Link :href="route('photographer.photos.edit', photo.id)"
                            class="px-6 py-3 border-2 border-black bg-black text-white font-mono text-[10px] font-bold uppercase tracking-widest hover:bg-white hover:text-black hover:-translate-y-1 hover:shadow-[4px_4px_0px_0px_rgba(227,6,19,1)] transition-all flex items-center gap-2">
                            <PencilSquareIcon class="w-4 h-4" /> Editar_Metadata
                        </Link>
                        <button @click="deletePhoto"
                            class="px-6 py-3 border-2 border-[#E30613] bg-white text-[#E30613] font-mono text-[10px] font-bold uppercase tracking-widest hover:bg-[#E30613] hover:text-white hover:-translate-y-1 hover:shadow-[4px_4px_0px_0px_rgba(0,0,0,1)] transition-all flex items-center gap-2">
                            <TrashIcon class="w-4 h-4" /> Purgar
                        </button>
                    </div>
                </div>

                <div class="grid grid-cols-1 xl:grid-cols-3 gap-10">

                    <div class="xl:col-span-2">
                        <div class="bg-zinc-100 border-4 border-black p-4 md:p-8 flex items-center justify-center min-h-[500px] relative group">
                            <div class="absolute inset-0 opacity-[0.03] pointer-events-none bg-[url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAiIGhlaWdodD0iMjAiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PGNpcmNsZSBjeD0iMSIgY3k9IjEiIHI9IjEiIGZpbGw9InJnYmEoMCwwLDAsMSkiLz48L3N2Zz4=')]"></div>
                            
                            <img :src="photo.watermarked_url || photo.thumbnail_url" :alt="photo.unique_id"
                                class="max-w-full max-h-[70vh] object-contain border-4 border-black bg-white shadow-[8px_8px_0px_0px_rgba(0,0,0,1)] group-hover:shadow-[12px_12px_0px_0px_rgba(227,6,19,1)] transition-all duration-300 relative z-10" />
                        </div>
                        
                        <div class="mt-4 flex justify-between items-center font-mono text-[10px] uppercase font-bold text-zinc-500">
                            <span>> PREVIEW: WATERMARK_APPLIED</span>
                            <a :src="photo.watermarked_url" target="_blank"
                                class="text-black border-b-2 border-transparent hover:border-[#E30613] hover:text-[#E30613] transition-colors pb-0.5">
                                [ RAW_PREVIEW ]
                            </a>
                        </div>
                    </div>

                    <div class="xl:col-span-1 space-y-8">

                        <div class="bg-white border-4 border-black shadow-[6px_6px_0px_0px_rgba(0,0,0,1)] rounded-none">
                            <div class="px-6 py-4 border-b-4 border-black bg-black text-white">
                                <h3 class="font-mono text-[10px] font-bold uppercase tracking-widest flex items-center gap-2">
                                    <CurrencyDollarIcon class="w-4 h-4 text-[#E30613]" /> SYS_COMMERCE
                                </h3>
                            </div>
                            <div class="p-6 space-y-6">
                                <div>
                                    <label class="block font-mono text-[10px] font-bold uppercase tracking-widest text-zinc-500 mb-1">Valor Asignado</label>
                                    <p class="font-bebas text-6xl text-[#E30613] leading-none">${{ photo.price }}</p>
                                </div>

                                <div>
                                    <label class="block font-mono text-[10px] font-bold uppercase tracking-widest text-zinc-500 mb-2">Estado de Red</label>
                                    <div class="flex items-center gap-3">
                                        <div :class="['w-3 h-3 border-2 border-black', photo.is_active ? 'bg-[#E30613]' : 'bg-white']"></div>
                                        <span :class="['font-mono text-xs font-bold uppercase tracking-widest', photo.is_active ? 'text-black' : 'text-zinc-400']">
                                            {{ photo.is_active ? 'PUBLICADA_ACTIVA' : 'OFFLINE_OCULTA' }}
                                        </span>
                                    </div>
                                </div>

                                <div v-if="photo.event" class="pt-4 border-t-2 border-black border-dashed">
                                    <label class="block font-mono text-[10px] font-bold uppercase tracking-widest text-zinc-500 mb-2">Evento Vinculado</label>
                                    <Link :href="route('photographer.events.show', photo.event.id)"
                                        class="inline-block font-mono text-sm font-bold text-black border-b-2 border-black hover:bg-black hover:text-white transition-colors pb-0.5">
                                        {{ photo.event.name }}
                                    </Link>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white border-4 border-black shadow-[6px_6px_0px_0px_rgba(0,0,0,1)] rounded-none">
                            <div class="px-6 py-4 border-b-4 border-black bg-black text-white">
                                <h3 class="font-mono text-[10px] font-bold uppercase tracking-widest flex items-center gap-2">
                                    <TagIcon class="w-4 h-4 text-[#E30613]" /> METADATA_TÉCNICA
                                </h3>
                            </div>
                            
                            <div class="divide-y-2 divide-black font-mono">
                                <div class="px-6 py-4 flex justify-between items-center hover:bg-zinc-50">
                                    <span class="text-[10px] font-bold uppercase tracking-widest text-zinc-500">Resolución</span>
                                    <span class="text-sm font-bold text-black">{{ photo.width }} x {{ photo.height }} px</span>
                                </div>
                                
                                <div class="px-6 py-4 flex justify-between items-center hover:bg-zinc-50">
                                    <span class="text-[10px] font-bold uppercase tracking-widest text-zinc-500">Peso en Disco</span>
                                    <span class="text-sm font-bold text-black">{{ formatFileSize(photo.file_size) }}</span>
                                </div>
                                
                                <div class="px-6 py-4 flex justify-between items-center hover:bg-zinc-50">
                                    <span class="text-[10px] font-bold uppercase tracking-widest text-zinc-500">Formato Ext</span>
                                    <span class="text-sm font-bold text-black uppercase bg-black text-white px-2 py-0.5">
                                        .{{ photo.file_path ? photo.file_path.split('.').pop() : 'N/A' }}
                                    </span>
                                </div>

                                <div class="px-6 py-4 flex flex-col gap-3" :class="photo.has_faces ? 'bg-zinc-100' : ''">
                                    <div class="flex justify-between items-center">
                                        <span class="text-[10px] font-bold uppercase tracking-widest text-zinc-500 flex items-center gap-2">
                                            <svg class="w-4 h-4 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                            IA_Rostros
                                        </span>
                                        <div class="flex items-center gap-3">
                                            <span v-if="photo.has_faces" class="text-xl font-bebas text-[#E30613]">
                                                {{ photo.face_encodings ? (typeof photo.face_encodings === 'string' ? JSON.parse(photo.face_encodings).length : photo.face_encodings.length) : 0 }}
                                            </span>
                                            <span v-else class="text-sm text-zinc-400">—</span>
                                            
                                            <span v-if="photo.has_faces" class="px-2 py-0.5 border-2 border-black bg-black text-white text-[9px] font-bold uppercase tracking-wider">
                                                Detectado
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <div class="px-6 py-4 flex flex-col gap-3" :class="photo.bib_processed && formatBibNumbers(photo.bib_numbers).length > 0 ? 'bg-red-50/50' : ''">
                                    <div class="flex justify-between items-start">
                                        <span class="text-[10px] font-bold uppercase tracking-widest text-zinc-500 flex items-center gap-2 pt-1">
                                            <svg class="w-4 h-4 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14" />
                                            </svg>
                                            OCR_Dorsales
                                        </span>
                                        <div class="flex flex-col items-end gap-2">
                                            <div v-if="photo.bib_processed && formatBibNumbers(photo.bib_numbers).length > 0" class="flex flex-wrap gap-1 justify-end">
                                                <span v-for="bib in formatBibNumbers(photo.bib_numbers)" :key="bib"
                                                    class="px-2 py-1 bg-[#E30613] text-white text-xs font-bold font-mono border-2 border-black">
                                                    #{{ bib }}
                                                </span>
                                            </div>
                                            <div v-else class="flex items-center gap-2">
                                                <span class="text-sm text-zinc-400 font-mono">—</span>
                                                <span class="px-2 py-0.5 border-2 border-zinc-200 text-zinc-400 text-[9px] font-bold uppercase tracking-wider">
                                                    N/A
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="px-6 py-4 flex justify-between items-center bg-zinc-50">
                                    <span class="text-[10px] font-bold uppercase tracking-widest text-zinc-500">Timestamp</span>
                                    <span class="text-[10px] font-bold text-black text-right uppercase">
                                        {{ formatDate(photo.created_at) }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white border-4 border-black shadow-[6px_6px_0px_0px_rgba(0,0,0,1)] rounded-none">
                            <div class="px-6 py-4 border-b-4 border-black bg-black text-white">
                                <h3 class="font-mono text-[10px] font-bold uppercase tracking-widest flex items-center gap-2">
                                    <ArrowDownTrayIcon class="w-4 h-4 text-[#E30613]" /> ANALÍTICAS
                                </h3>
                            </div>
                            <div class="p-6 flex items-center justify-between">
                                <span class="font-mono text-[10px] font-bold uppercase tracking-widest text-zinc-500">Descargas Totales</span>
                                <span class="text-5xl font-bebas text-black">{{ photo.downloads }}</span>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>