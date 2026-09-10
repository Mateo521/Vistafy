<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import {
    FlagIcon,
    ShieldCheckIcon,
    EyeSlashIcon,
    CheckCircleIcon,
    ExclamationCircleIcon,
    MagnifyingGlassIcon
} from '@heroicons/vue/24/outline';

const props = defineProps({
    reports: {
        type: Object,
        required: true,
    },
});


const reasonTranslations = {
    'copyright': 'Derechos de Autor (Copyright)',
    'privacy': 'Violación de Privacidad',
    'inappropriate': 'Contenido Inapropiado/Explícito',
    'other': 'Otro motivo'
};

const getReasonText = (reason) => reasonTranslations[reason] || reason;


const acceptReport = (reportId) => {
    if (confirm('¿Estás seguro de que deseas OCULTAR esta fotografía? Se dará de baja inmediatamente del sitio público.')) {
        router.post(route('admin.reports.accept', reportId), {}, {
            preserveScroll: true,
        });
    }
};


const rejectReport = (reportId) => {
    if (confirm('¿Descartar este reporte? La fotografía seguirá pública y el reporte se archivará.')) {
        router.post(route('admin.reports.reject', reportId), {}, {
            preserveScroll: true,
        });
    }
};
</script>

<template>
    <Head title="Moderación de Fotografías" />

    <AuthenticatedLayout>
        <div class="min-h-screen bg-[#F8F9FA] text-slate-800 font-sans antialiased py-12 pt-28">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

                
                <div class="mb-10 text-center md:text-left flex flex-col md:flex-row items-center justify-between gap-6 border-b border-gray-200 pb-8">
                    <div class="flex items-center gap-6">
                        <div class="w-20 h-20 bg-red-50 rounded-full flex items-center justify-center border-4 border-white shadow-sm shrink-0">
                            <FlagIcon class="w-10 h-10 text-[#E30613]" />
                        </div>
                        <div>
                            <span class="inline-flex items-center gap-2 bg-red-50 text-[#E30613] px-3 py-1.5 rounded-full text-[10px] font-bold uppercase tracking-widest mb-3 border border-red-100">
                                <span class="w-2 h-2 rounded-full bg-[#E30613] animate-pulse"></span>
                                Moderación de contenido
                            </span>
                            <h1 class="text-4xl md:text-5xl font-flux text-black tracking-wide leading-none mb-2">
                                Reportes <span class="text-[#E30613]">pendientes</span>
                            </h1>
                            <p class="text-sm font-medium text-gray-500 max-w-xl">
                                Revisá las denuncias de los usuarios y decidí si ocultar el material o descartar el reporte.
                            </p>
                        </div>
                    </div>
                    
                    <Link :href="route('admin.dashboard')" class="text-xs font-bold uppercase tracking-widest text-gray-400 hover:text-black transition-colors">
                        &larr; Volver al panel
                    </Link>
                </div>

            
                <div v-if="reports.data.length === 0" class="bg-white rounded p-12 border border-gray-100 shadow-sm text-center">
                    <div class="w-24 h-24 bg-green-50 rounded-full flex items-center justify-center mx-auto mb-6">
                        <ShieldCheckIcon class="w-12 h-12 text-green-500" />
                    </div>
                    <h3 class="text-2xl font-flux text-black mb-2">Todo en orden</h3>
                    <p class="text-gray-500 text-sm max-w-md mx-auto">
                        No hay reportes de fotografías pendientes de revisión. El sitio está operando con normalidad.
                    </p>
                </div>

        
                <div v-else class="space-y-6">
                    <div v-for="report in reports.data" :key="report.id" 
                        class="bg-white rounded border border-gray-100 shadow-sm p-6 flex flex-col md:flex-row gap-8 hover:shadow-md transition-shadow">
                        
                
                        <div class="shrink-0 w-full md:w-64 relative group">
                            <img :src="report.photo.thumbnail_url" :alt="'Ref: ' + report.photo.unique_id"
                                class="w-full h-48 object-cover rounded border border-gray-200" />
                            <div class="absolute top-2 left-2 bg-black/80 text-white text-[10px] font-bold px-2 py-1 rounded uppercase tracking-wider">
                                Ref: {{ report.photo.unique_id }}
                            </div>
                            <a :href="route('gallery.show', report.photo.unique_id)" target="_blank"
                                class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition-opacity rounded flex items-center justify-center text-white gap-2 text-xs font-bold uppercase tracking-widest">
                                <MagnifyingGlassIcon class="w-5 h-5" /> Ver en galería
                            </a>
                        </div>

                    
                        <div class="flex-1 flex flex-col justify-center">
                            <div class="flex items-start justify-between mb-4">
                                <div>
                                    <span class="inline-flex items-center gap-1.5 bg-orange-50 text-orange-700 px-2.5 py-1 rounded text-[10px] font-bold uppercase tracking-widest mb-3">
                                        <ExclamationCircleIcon class="w-3.5 h-3.5" />
                                        {{ getReasonText(report.reason) }}
                                    </span>
                                    <p class="text-sm text-gray-800 bg-gray-50 p-4 rounded border border-gray-100 italic mb-4">
                                        "{{ report.message || 'Sin mensaje adicional por parte del usuario.' }}"
                                    </p>
                                </div>
                            </div>
                            
                            <div class="grid grid-cols-2 gap-4 text-xs">
                                <div>
                                    <span class="block font-bold text-gray-400 uppercase tracking-wider mb-1">Denunciante</span>
                                    <a :href="'mailto:' + report.email" class="text-black font-medium hover:underline">{{ report.email }}</a>
                                </div>
                                <div>
                                    <span class="block font-bold text-gray-400 uppercase tracking-wider mb-1">Fotógrafo acusado</span>
                                    <Link :href="route('admin.photographers.show', report.photo.photographer.id)" class="text-black font-medium hover:underline">
                                        {{ report.photo.photographer.business_name || report.photo.photographer.user.name }}
                                    </Link>
                                </div>
                            </div>
                        </div>

                    
                        <div class="shrink-0 flex flex-col justify-center gap-3 border-t md:border-t-0 md:border-l border-gray-100 pt-6 md:pt-0 md:pl-6 min-w-[200px]">
                            
                            <button @click="acceptReport(report.id)"
                                class="w-full flex items-center justify-center gap-2 bg-[#E30613] hover:bg-red-700 text-white p-3 rounded text-[10px] font-bold uppercase tracking-widest transition-colors shadow-sm">
                                <EyeSlashIcon class="w-4 h-4" />
                                Ocultar fotografía
                            </button>

                            <button @click="rejectReport(report.id)"
                                class="w-full flex items-center justify-center gap-2 bg-white hover:bg-gray-50 border border-gray-200 text-gray-600 p-3 rounded text-[10px] font-bold uppercase tracking-widest transition-colors">
                                <CheckCircleIcon class="w-4 h-4" />
                                Descartar reporte
                            </button>
                            
                        </div>
                    </div>
                </div>

                
                <div v-if="reports.links && reports.links.length > 3" class="mt-8 flex justify-center">
                    <div class="flex gap-1">
                        <Link v-for="(link, k) in reports.links" :key="k"
                            :href="link.url || '#'"
                            class="px-4 py-2 text-sm font-bold rounded transition-colors"
                            :class="[
                                link.active ? 'bg-black text-white' : 'bg-white text-gray-500 hover:bg-gray-100',
                                !link.url ? 'opacity-50 cursor-not-allowed' : ''
                            ]"
                            v-html="link.label" />
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>