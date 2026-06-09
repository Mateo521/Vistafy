<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import {
    NoSymbolIcon,
    ExclamationTriangleIcon,
    EnvelopeIcon
} from '@heroicons/vue/24/outline';
</script>

<template>
    <Head title="Acceso Revocado" />

    <AuthenticatedLayout>
        <div class="min-h-screen bg-black py-12 text-white selection:bg-red-600 selection:text-black">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">

                <div class="mb-12 border-l-4 border-red-600 pl-8">
                    <span class="font-mono text-[10px] font-bold text-red-600 uppercase tracking-widest mb-2 block animate-pulse">
                        [STATUS: ERROR_403] Restricción de Acceso
                    </span>
                    <h1 class="text-5xl md:text-7xl font-black uppercase tracking-tighter text-white leading-none">
                        Cuenta<br>Suspendida.
                    </h1>
                    <p class="font-mono text-xs text-zinc-500 mt-6 uppercase tracking-widest max-w-xl">
                        Los privilegios operativos de este perfil fotográfico han sido revocados temporalmente por el sistema.
                    </p>
                </div>

                <div class="space-y-8">
                    <div class="border-2 border-red-600 bg-red-600/5 p-8 md:p-10 relative overflow-hidden">
                        <NoSymbolIcon class="absolute -right-8 -top-8 w-48 h-48 text-red-600 opacity-5 pointer-events-none" />
                        
                        <h2 class="font-mono text-xs font-bold uppercase tracking-widest text-red-600 mb-6 border-b border-red-600/20 pb-2">
                            1. Estado actual
                        </h2>
                        <ul class="space-y-4 font-mono text-xs text-zinc-300 uppercase tracking-wide">
                            <li class="flex items-start gap-4">
                                <span class="text-red-600 font-black">>></span> 
                                <span>Acceso denegado al panel de administración y métricas.</span>
                            </li>
                            <li class="flex items-start gap-4">
                                <span class="text-red-600 font-black">>></span> 
                                <span>Eventos y galerías fotográficas ocultas al público general.</span>
                            </li>
                            <li class="flex items-start gap-4">
                                <span class="text-red-600 font-black">>></span> 
                                <span>Restricción total para la carga de nuevos activos o creación de eventos.</span>
                            </li>
                        </ul>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div class="border border-zinc-800 bg-zinc-950 p-8">
                            <h3 class="font-mono text-[10px] font-bold uppercase tracking-widest text-zinc-500 mb-6 border-b border-zinc-800 pb-2">
                                Causas Frecuentes de Suspensión
                            </h3>
                            <ul class="space-y-3 font-mono text-[10px] text-zinc-400 uppercase tracking-wider">
                                <li class="flex items-start gap-3"><span class="text-zinc-600">--</span> Violación a los términos de servicio.</li>
                                <li class="flex items-start gap-3"><span class="text-zinc-600">--</span> Reportes negativos o disputas de clientes.</li>
                                <li class="flex items-start gap-3"><span class="text-zinc-600">--</span> Material que incumple los estándares de calidad.</li>
                                <li class="flex items-start gap-3"><span class="text-zinc-600">--</span> Abandono o falta de respuesta comercial.</li>
                                <li class="flex items-start gap-3"><span class="text-zinc-600">--</span> Detección de tráfico anómalo o fraude.</li>
                            </ul>
                        </div>

                        <div class="border border-zinc-800 bg-zinc-950 p-8">
                            <h3 class="font-mono text-[10px] font-bold uppercase tracking-widest text-zinc-500 mb-6 border-b border-zinc-800 pb-2">
                                Recuperación
                            </h3>
                            <ol class="space-y-4 font-mono text-[10px] text-zinc-400 uppercase tracking-wider">
                                <li class="flex items-start gap-3">
                                    <span class="text-white font-black bg-zinc-800 px-2 py-0.5">01</span> 
                                    <span>Revisar la notificación de auditoría enviada al correo registrado.</span>
                                </li>
                                <li class="flex items-start gap-3">
                                    <span class="text-white font-black bg-zinc-800 px-2 py-0.5">02</span> 
                                    <span>Iniciar protocolo de apelación mediante el canal oficial.</span>
                                </li>
                                <li class="flex items-start gap-3">
                                    <span class="text-white font-black bg-zinc-800 px-2 py-0.5">03</span> 
                                    <span>Adjuntar documentación respaldatoria si es requerida.</span>
                                </li>
                                <li class="flex items-start gap-3">
                                    <span class="text-white font-black bg-zinc-800 px-2 py-0.5">04</span> 
                                    <span>Aguardar resolución (Tiempo estimado: 24-48 hs hábiles).</span>
                                </li>
                            </ol>
                        </div>
                    </div>

                    <div class="border-2 border-amber-500 bg-amber-500/5 p-6 flex flex-col sm:flex-row items-start sm:items-center gap-4">
                        <ExclamationTriangleIcon class="w-8 h-8 text-amber-500 flex-shrink-0" />
                        <div>
                            <p class="font-mono text-[10px] font-bold uppercase tracking-widest text-amber-500">Advertencia del Sistema</p>
                            <p class="font-mono text-xs text-zinc-400 uppercase tracking-wide mt-1">
                                Las suspensiones reiteradas derivarán en la terminación permanente de la cuenta y la purga irrecuperable de todos los activos digitales almacenados.
                            </p>
                        </div>
                    </div>

                    <div class="border border-zinc-800 bg-zinc-950 p-8 flex flex-col md:flex-row items-center justify-between gap-6">
                        <div>
                            <p class="font-mono text-[10px] font-bold uppercase tracking-widest text-zinc-500 mb-2">Canal de Soporte Técnico</p>
                            <p class="font-mono text-sm text-white flex items-center gap-2">
                                <EnvelopeIcon class="w-4 h-4 text-zinc-400" /> soporte@f33.click
                            </p>
                        </div>
                        
                        <div class="flex flex-col sm:flex-row gap-4 w-full md:w-auto">
                            <Link :href="route('home')"
                                class="border-2 border-zinc-700 bg-black text-zinc-400 px-8 py-4 font-black uppercase tracking-widest text-[10px] hover:bg-white hover:text-black hover:border-white transition-colors text-center text-nowrap">
                                Volver al Inicio
                            </Link>
                            <a href="mailto:soporte@f33.click?subject=Apelación%20de%20Suspensión"
                                class="border-2 border-red-600 bg-red-600 text-black px-8 py-4 font-black uppercase tracking-widest text-[10px] hover:bg-white hover:border-white transition-colors text-center text-nowrap">
                                Iniciar Apelación
                            </a>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>