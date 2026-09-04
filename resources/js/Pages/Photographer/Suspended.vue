<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import {
    NoSymbolIcon,
    ExclamationTriangleIcon,
    EnvelopeIcon,
    InformationCircleIcon
} from '@heroicons/vue/24/outline';
</script>

<template>
    <Head title="Acceso Restringido | F33" />

    <AuthenticatedLayout>
        <div class="min-h-screen bg-[#F8F9FA] text-slate-800 font-sans antialiased py-12 pt-28">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">


                <div class="mb-10 text-center md:text-left flex flex-col md:flex-row items-center gap-6">
                    <div class="w-24 h-24 bg-red-50 rounded-full flex items-center justify-center border-4 border-white shadow-sm shrink-0 relative">
                        <div class="absolute inset-0 rounded-full border border-red-100 animate-ping opacity-20"></div>
                        <NoSymbolIcon class="w-12 h-12 text-[#E30613]" />
                    </div>
                    <div>
                        <span class="inline-flex items-center gap-2 bg-red-50 text-[#E30613] px-3 py-1.5 rounded-full text-[10px] font-bold uppercase tracking-widest mb-3 border border-red-100">
                            <span class="w-2 h-2 rounded-full bg-[#E30613] animate-pulse"></span>
                            Error 403: Acceso restringido
                        </span>
                        <h1 class="text-4xl md:text-6xl font-flux text-black tracking-wide leading-none mb-3">
                            Cuenta <span class="text-[#E30613]">suspendida</span>
                        </h1>
                        <p class="text-sm font-medium text-gray-500 max-w-lg">
                            Los privilegios operativos de este perfil fueron revocados temporalmente.
                        </p>
                    </div>
                </div>

                <div class="space-y-6">

                
                    <div class="bg-white rounded-3xl p-6 md:p-8 border border-red-100 shadow-sm relative overflow-hidden">
                        <div class="absolute top-0 right-0 w-32 h-32 bg-red-50 rounded-full blur-3xl -mr-10 -mt-10 pointer-events-none"></div>
                        
                        <h2 class="text-xs font-bold uppercase tracking-widest text-gray-400 mb-6 flex items-center gap-2 border-b border-gray-100 pb-4 relative z-10">
                            <span class="w-6 h-6 rounded-full bg-red-50 text-[#E30613] flex items-center justify-center">1</span> 
                            Estado actual
                        </h2>
                        
                        <ul class="space-y-4 text-sm font-medium text-slate-600 relative z-10">
                            <li class="flex items-start gap-3">
                                <span class="w-5 h-5 rounded-full bg-red-50 text-[#E30613] flex items-center justify-center shrink-0 mt-0.5">
                                    <NoSymbolIcon class="w-3 h-3" />
                                </span> 
                                Acceso denegado al panel de administración y herramientas.
                            </li>
                            <li class="flex items-start gap-3">
                                <span class="w-5 h-5 rounded-full bg-red-50 text-[#E30613] flex items-center justify-center shrink-0 mt-0.5">
                                    <NoSymbolIcon class="w-3 h-3" />
                                </span> 
                                Eventos y galerías ocultas al público general.
                            </li>
                            <li class="flex items-start gap-3">
                                <span class="w-5 h-5 rounded-full bg-red-50 text-[#E30613] flex items-center justify-center shrink-0 mt-0.5">
                                    <NoSymbolIcon class="w-3 h-3" />
                                </span> 
                                Restricción total para la carga de nuevas fotos o creación de eventos.
                            </li>
                        </ul>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        

                        <div class="bg-white rounded-3xl p-6 md:p-8 border border-gray-100 shadow-sm">
                            <h3 class="text-xs font-bold uppercase tracking-widest text-gray-400 mb-6 flex items-center gap-2 border-b border-gray-100 pb-4">
                                <InformationCircleIcon class="w-4 h-4 text-gray-400" /> Posibles causas
                            </h3>
                            <ul class="space-y-3 text-sm font-medium text-slate-600">
                                <li class="flex items-start gap-2"><span class="text-gray-300">•</span> Violación a los <Link :href="route('terms')" class="text-black hover:underline font-bold transition-all">términos de servicio</Link>.</li>
                                <li class="flex items-start gap-2"><span class="text-gray-300">•</span> Reportes negativos o disputas de clientes.</li>
                                <li class="flex items-start gap-2"><span class="text-gray-300">•</span> Material que incumple los estándares de calidad.</li>
                                <li class="flex items-start gap-2"><span class="text-gray-300">•</span> Abandono o falta de respuesta comercial.</li>
                                <li class="flex items-start gap-2"><span class="text-gray-300">•</span> Detección de tráfico anómalo o fraude.</li>
                            </ul>
                        </div>

                        
                        <div class="bg-white rounded-3xl p-6 md:p-8 border border-gray-100 shadow-sm">
                            <h3 class="text-xs font-bold uppercase tracking-widest text-gray-400 mb-6 flex items-center gap-2 border-b border-gray-100 pb-4">
                                <span class="w-4 h-px bg-gray-200"></span> Proceso de recuperación
                            </h3>
                            <ol class="space-y-4 text-sm font-medium text-slate-600 relative">
                                <div class="absolute left-2.5 top-2 bottom-2 w-px bg-gray-100 -z-10"></div>
                                <li class="flex items-start gap-3 bg-white">
                                    <span class="w-5 h-5 rounded-full bg-gray-100 text-gray-500 flex items-center justify-center shrink-0 text-[10px] font-bold">1</span> 
                                    <span>Revisar la notificación enviada al correo registrado.</span>
                                </li>
                                <li class="flex items-start gap-3 bg-white">
                                    <span class="w-5 h-5 rounded-full bg-gray-100 text-gray-500 flex items-center justify-center shrink-0 text-[10px] font-bold">2</span> 
                                    <span>Iniciar protocolo de apelación.</span>
                                </li>
                                <li class="flex items-start gap-3 bg-white">
                                    <span class="w-5 h-5 rounded-full bg-gray-100 text-gray-500 flex items-center justify-center shrink-0 text-[10px] font-bold">3</span> 
                                    <span>Adjuntar documentación respaldatoria si es requerida.</span>
                                </li>
                                <li class="flex items-start gap-3 bg-white">
                                    <span class="w-5 h-5 rounded-full bg-gray-100 text-gray-500 flex items-center justify-center shrink-0 text-[10px] font-bold">4</span> 
                                    <span>Esperar resolución (Tiempo estimado: 24-48 hs hábiles).</span>
                                </li>
                            </ol>
                        </div>
                    </div>


                    <div class="bg-orange-50 border border-orange-100 rounded p-6 flex flex-col sm:flex-row items-start sm:items-center gap-4 shadow-sm">
                        <ExclamationTriangleIcon class="w-8 h-8 text-orange-500 shrink-0" />
                        <div>
                            <p class="text-xs font-bold uppercase tracking-wider text-orange-600 mb-1">Advertencia</p>
                            <p class="text-sm font-medium text-orange-800/80 leading-relaxed">
                                Las suspensiones reiteradas derivarán en la terminación permanente de la cuenta y el borrado irrecuperable de todas las fotos almacenadas.
                            </p>
                        </div>
                    </div>

                
                    <div class="bg-white border border-gray-100 rounded-3xl p-6 md:p-8 flex flex-col md:flex-row items-center justify-between gap-6 shadow-sm">
                        <div class="text-center md:text-left">
                            <p class="text-xs font-bold uppercase tracking-wider text-gray-400 mb-2">Soporte</p>
                            <p class="text-sm font-bold text-slate-700 flex items-center justify-center md:justify-start gap-2">
                                <EnvelopeIcon class="w-4 h-4 text-gray-400" /> contacto@f33.click
                            </p>
                        </div>
                        
                        <div class="flex flex-col sm:flex-row gap-3 w-full md:w-auto">
                            <Link :href="route('home')"
                                class="flex-1 md:flex-none px-6 py-3.5 bg-white border border-gray-200 text-gray-600 rounded-full text-xs font-bold uppercase tracking-wider hover:bg-gray-50 hover:text-black transition-colors text-center shadow-sm">
                                Volver al inicio
                            </Link>
                            <a href="mailto:contacto@f33.click?subject=Apelación%20de%20suspensión"
                                class="flex-1 md:flex-none px-6 py-3.5 bg-black text-white rounded-full text-xs font-bold uppercase tracking-wider hover:bg-[#E30613] hover:shadow-lg hover:shadow-red-500/30 transition-all text-center">
                                Iniciar apelación
                            </a>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>