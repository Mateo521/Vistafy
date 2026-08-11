<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import {
    PencilSquareIcon,
    TrashIcon,
    ArrowLeftIcon,
    CurrencyDollarIcon,
    TagIcon,
    ArrowDownTrayIcon,
    CheckBadgeIcon,
    EyeSlashIcon,
    FaceSmileIcon,
    HashtagIcon,
    DocumentMagnifyingGlassIcon,
    CalendarIcon
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
    <Head :title="`Captura #${photo.unique_id}`" />

    <AuthenticatedLayout>
        <div class="min-h-screen bg-[#F8F9FA] text-slate-800 font-sans antialiased py-12 pt-28">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            
                <div class="flex flex-col md:flex-row md:items-end justify-between mb-8 border-b border-gray-200 pb-8 gap-6">
                    <div>
                        <Link :href="route('photographer.photos.index')"
                            class="inline-flex items-center gap-2 bg-white px-4 py-2 rounded-full text-xs font-bold uppercase tracking-wider text-gray-500 hover:text-black hover:shadow-sm border border-gray-200 transition-all mb-6">
                            <ArrowLeftIcon class="w-4 h-4" /> Volver al archivo
                        </Link>
                        
                        <div class="flex items-center gap-4 mb-2">
                            <h1 class="text-4xl md:text-5xl font-flux text-black uppercase leading-none tracking-wide">
                                Detalle de captura
                            </h1>
                            <span class="bg-gray-100 text-gray-600 font-bold px-3 py-1 rounded-md text-xs uppercase tracking-wider border border-gray-200">
                                #{{ photo.unique_id }}
                            </span>
                        </div>
                    </div>

                    <div class="flex flex-wrap gap-3">
                        <Link :href="route('photographer.photos.edit', photo.id)"
                            class="px-6 py-3.5 bg-white border border-gray-200 text-black text-xs font-bold uppercase tracking-wider hover:bg-gray-50 hover:shadow-sm transition-all rounded-full flex items-center gap-2">
                            <PencilSquareIcon class="w-4 h-4" /> Editar metadata
                        </Link>
                        <button @click="deletePhoto"
                            class="px-6 py-3.5 bg-white border border-red-200 text-[#E30613] text-xs font-bold uppercase tracking-wider hover:bg-red-50 transition-all rounded-full flex items-center gap-2">
                            <TrashIcon class="w-4 h-4" /> Eliminar
                        </button>
                    </div>
                </div>

              
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 xl:gap-10">

                  
                    <div class="lg:col-span-2 flex flex-col gap-4">
                        
                       
                        <div class="bg-gray-100 rounded overflow-hidden border border-gray-200 shadow-inner flex items-center justify-center min-h-[500px] relative group">
                            
                            
                            <div class="absolute inset-0 opacity-[0.4] pointer-events-none" style="background-image: radial-gradient(#cbd5e1 1px, transparent 1px); background-size: 24px 24px;"></div>

                            <img :src="photo.watermarked_url || photo.thumbnail_url" :alt="photo.unique_id"
                                class="max-w-full max-h-[70vh] object-contain relative z-10 drop-shadow-2xl transition-transform duration-500 " />
                            
                            
                            <div class="absolute top-4 left-4 z-20">
                                <span class="bg-white/90 backdrop-blur text-black text-[10px] font-bold uppercase tracking-wider px-3 py-1.5 rounded-full shadow-sm flex items-center gap-1.5">
                                    <CheckBadgeIcon class="w-3.5 h-3.5 text-[#E30613]" /> Marca de agua aplicada
                                </span>
                            </div>
                        </div>

                        
                        <div class="flex justify-end">
                            <a :href="photo.watermarked_url" target="_blank"
                                class="inline-flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-gray-500 hover:text-[#E30613] transition-colors bg-white px-5 py-2.5 rounded-full border border-gray-200 shadow-sm hover:shadow-md">
                                <DocumentMagnifyingGlassIcon class="w-4 h-4" /> Ver foto original
                            </a>
                        </div>
                    </div>

                    
                    <div class="lg:col-span-1 space-y-6">

                        
                        <div class="bg-white rounded p-6 md:p-8 border border-gray-100 shadow-sm hover:shadow-md transition-shadow">
                            <h3 class="text-xs font-bold uppercase tracking-widest text-gray-400 mb-6 flex items-center gap-2">
                                <span class="w-4 h-px bg-gray-200"></span> Comercialización
                            </h3>

                            <div class="space-y-6">
                                <div>
                                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Precio Asignado</p>
                                    <p class="text-5xl font-flux text-black leading-none">${{ photo.price }}</p>
                                </div>

                                <div class="pt-6 border-t border-gray-100 flex items-center justify-between">
                                    <div>
                                        <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Visibilidad</p>
                                        <div class="flex items-center gap-2">
                                            <span :class="[
                                                'px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider flex items-center gap-1.5',
                                                photo.is_active ? 'bg-green-50 text-green-600' : 'bg-gray-100 text-gray-500'
                                            ]">
                                                <div :class="['w-1.5 h-1.5 rounded-full', photo.is_active ? 'bg-green-500 animate-pulse' : 'bg-gray-400']"></div>
                                                {{ photo.is_active ? 'Pública' : 'Oculta' }}
                                            </span>
                                        </div>
                                    </div>
                                    
                                    <div v-if="photo.downloads > 0" class="text-right">
                                        <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Descargas</p>
                                        <div class="flex items-center gap-1 text-lg font-bold text-[#E30613]">
                                            <ArrowDownTrayIcon class="w-4 h-4" /> {{ photo.downloads }}
                                        </div>
                                    </div>
                                </div>

                                <div v-if="photo.event" class="pt-6 border-t border-gray-100">
                                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Evento vinculado</p>
                                    <Link :href="route('photographer.events.show', photo.event.id)"
                                        class="inline-flex bg-gray-50 hover:bg-red-50 text-slate-700 hover:text-[#E30613] px-4 py-2.5 rounded text-sm font-bold transition-colors w-full group">
                                        <span class="truncate">{{ photo.event.name }}</span>
                                        <span class="ml-auto text-[#E30613] opacity-0 group-hover:opacity-100 transition-opacity">&rarr;</span>
                                    </Link>
                                </div>
                                <div v-else class="pt-6 border-t border-gray-100">
                                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Evento vinculado</p>
                                    <span class="inline-flex bg-gray-50 text-gray-400 px-4 py-2.5 rounded text-sm font-bold italic w-full">
                                        Sin evento asignado
                                    </span>
                                </div>
                            </div>
                        </div>

                        
                        <div class="bg-white rounded p-6 md:p-8 border border-gray-100 shadow-sm hover:shadow-md transition-shadow">
                            <h3 class="text-xs font-bold uppercase tracking-widest text-gray-400 mb-6 flex items-center gap-2">
                                <span class="w-4 h-px bg-gray-200"></span> Análisis F33 (IA)
                            </h3>

                            <div class="space-y-4">
                            
                                <div class="flex justify-between items-center bg-gray-50 p-4 rounded border border-transparent hover:border-gray-200 transition-colors">
                                    <div class="flex items-center gap-3">
                                        <div :class="['w-8 h-8 rounded-full flex items-center justify-center', photo.has_faces ? 'bg-red-50 text-[#E30613]' : 'bg-gray-200 text-gray-400']">
                                            <FaceSmileIcon class="w-4 h-4" />
                                        </div>
                                        <span class="text-sm font-bold text-slate-700">Rostros detectados</span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <span v-if="photo.has_faces" class="text-lg font-black text-[#E30613]">
                                            {{ photo.face_encodings ? (typeof photo.face_encodings === 'string' ? JSON.parse(photo.face_encodings).length : photo.face_encodings.length) : 0 }}
                                        </span>
                                        <span v-else class="text-sm text-gray-400 font-bold">0</span>
                                    </div>
                                </div>

                                
                                <div class="flex flex-col bg-gray-50 p-4 rounded border border-transparent hover:border-gray-200 transition-colors">
                                    <div class="flex justify-between items-center mb-3">
                                        <div class="flex items-center gap-3">
                                            <div :class="['w-8 h-8 rounded-full flex items-center justify-center', photo.bib_processed && formatBibNumbers(photo.bib_numbers).length > 0 ? 'bg-red-50 text-[#E30613]' : 'bg-gray-200 text-gray-400']">
                                                <HashtagIcon class="w-4 h-4" />
                                            </div>
                                            <span class="text-sm font-bold text-slate-700">Dorsales (OCR)</span>
                                        </div>
                                    </div>
                                    
                                    <div v-if="photo.bib_processed && formatBibNumbers(photo.bib_numbers).length > 0" class="flex flex-wrap gap-2 pl-11">
                                        <span v-for="bib in formatBibNumbers(photo.bib_numbers)" :key="bib"
                                            class="bg-white border border-gray-200 text-black px-2.5 py-1 rounded-md text-xs font-bold shadow-sm">
                                            #{{ bib }}
                                        </span>
                                    </div>
                                    <div v-else class="pl-11">
                                        <span class="text-xs text-gray-400 font-medium italic">No se detectaron dorsales</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        
                        <div class="bg-white rounded p-6 md:p-8 border border-gray-100 shadow-sm hover:shadow-md transition-shadow">
                            <h3 class="text-xs font-bold uppercase tracking-widest text-gray-400 mb-6 flex items-center gap-2">
                                <span class="w-4 h-px bg-gray-200"></span> Especificaciones
                            </h3>

                            <div class="space-y-4">
                                <div class="flex justify-between items-center">
                                    <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">Resolución</span>
                                    <span class="text-sm font-bold text-slate-700">{{ photo.width }} x {{ photo.height }} px</span>
                                </div>
                                <div class="w-full h-px bg-gray-50"></div>

                                <div class="flex justify-between items-center">
                                    <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">Peso</span>
                                    <span class="text-sm font-bold text-slate-700">{{ formatFileSize(photo.file_size) }}</span>
                                </div>
                                <div class="w-full h-px bg-gray-50"></div>

                                <div class="flex justify-between items-center">
                                    <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">Formato</span>
                                    <span class="bg-gray-100 text-gray-600 px-2 py-1 rounded text-[10px] font-bold uppercase tracking-wider">
                                        {{ photo.file_path ? photo.file_path.split('.').pop() : 'N/A' }}
                                    </span>
                                </div>
                                <div class="w-full h-px bg-gray-50"></div>

                                <div class="flex flex-col gap-1">
                                    <span class="text-xs font-bold text-gray-400 uppercase tracking-wider flex items-center gap-1">
                                        <CalendarIcon class="w-3.5 h-3.5" /> Fecha
                                    </span>
                                    <span class="text-sm font-bold text-slate-700 text-right">
                                        {{ formatDate(photo.created_at) }}
                                    </span>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>