<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { 
    ExclamationTriangleIcon, 
    XCircleIcon, 
    QuestionMarkCircleIcon, 
    ArrowLeftIcon,
    EnvelopeIcon
} from '@heroicons/vue/24/outline';

defineProps({
    reason: {
        type: String,
        default: null,
    },
});
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Estado de Solicitud" />

        <div class="py-12 flex flex-col items-center justify-center min-h-[calc(100vh-80px)]">
            <div class="max-w-2xl w-full px-4 sm:px-6 lg:px-8">

                <div class="bg-white border border-red-200 p-10 rounded-sm shadow-sm text-center relative overflow-hidden">
                    
                    <div class="absolute top-0 left-0 w-full h-1 bg-red-500"></div>

                    <div class="mb-8 flex justify-center">
                        <div class="w-20 h-20 bg-red-50 rounded-full flex items-center justify-center border border-red-100">
                            <ExclamationTriangleIcon class="w-10 h-10 text-red-500 stroke-1" />
                        </div>
                    </div>

                    <h1 class="text-3xl font-serif font-bold text-slate-900 mb-4">
                        Solicitud No Aprobada
                    </h1>
                    <p class="text-slate-500 font-light text-sm leading-relaxed max-w-lg mx-auto mb-10">
                        Tras revisar su perfil, lamentamos informarle que su solicitud de ingreso como fotógrafo no ha cumplido con nuestros criterios actuales de selección.
                    </p>

                    <div v-if="reason" class="bg-red-50 border border-red-100 rounded-sm p-6 mb-8 text-left">
                        <h4 class="text-xs font-bold uppercase tracking-widest text-red-800 mb-2 flex items-center gap-2">
                            <XCircleIcon class="w-4 h-4" /> Motivo del Rechazo
                        </h4>
                        <p class="text-sm text-red-700 font-light leading-relaxed">
                            {{ reason }}
                        </p>
                    </div>

                    <div class="text-left mb-10">
                        <h3 class="text-xs font-bold uppercase tracking-widest text-slate-900 mb-4 border-b border-gray-100 pb-2">
                            Criterios de Evaluación
                        </h3>
                        <ul class="space-y-3">
                            <li class="flex items-start gap-3 text-sm text-slate-600 font-light">
                                <span class="w-1.5 h-1.5 bg-slate-300 rounded-full mt-2 flex-shrink-0"></span>
                                Veracidad de la información de contacto y portafolio.
                            </li>
                            <li class="flex items-start gap-3 text-sm text-slate-600 font-light">
                                <span class="w-1.5 h-1.5 bg-slate-300 rounded-full mt-2 flex-shrink-0"></span>
                                Calidad técnica y artística del material presentado.
                            </li>
                            <li class="flex items-start gap-3 text-sm text-slate-600 font-light">
                                <span class="w-1.5 h-1.5 bg-slate-300 rounded-full mt-2 flex-shrink-0"></span>
                                Cumplimiento de los términos y condiciones de servicio.
                            </li>
                        </ul>
                    </div>

                    <div class="bg-slate-50 border border-gray-200 rounded-sm p-6 text-left mb-8">
                        <h3 class="text-xs font-bold uppercase tracking-widest text-slate-900 mb-4 flex items-center gap-2">
                            <QuestionMarkCircleIcon class="w-4 h-4" /> ¿Qué puede hacer ahora?
                        </h3>
                        <div class="space-y-4 text-sm text-slate-600 font-light">
                            <p>
                                <strong class="text-slate-900 font-medium">Corregir y Reintentar:</strong> Si el rechazo se debe a información faltante, puede contactar a soporte para actualizar su solicitud.
                            </p>
                            <p>
                                <strong class="text-slate-900 font-medium">Nueva Solicitud:</strong> Podrá aplicar nuevamente transcurridos 30 días desde la fecha de esta notificación.
                            </p>
                        </div>
                    </div>

                    <div class="flex flex-col sm:flex-row items-center justify-center gap-4 pt-8 border-t border-gray-50">
                        <Link :href="route('home')" class="px-6 py-3 border border-slate-200 text-slate-600 text-xs font-bold uppercase tracking-widest hover:border-slate-900 hover:text-slate-900 transition rounded-sm w-full sm:w-auto text-center flex items-center justify-center gap-2">
                            <ArrowLeftIcon class="w-4 h-4" /> Volver al Inicio
                        </Link>

                        <a href="mailto:soporte@empresa.com" class="px-6 py-3 bg-slate-900 text-white text-xs font-bold uppercase tracking-widest hover:bg-slate-800 transition rounded-sm w-full sm:w-auto text-center flex items-center justify-center gap-2">
                            <EnvelopeIcon class="w-4 h-4" />
                            Contactar Soporte
                        </a>
                    </div>

                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>