<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { 
    ClockIcon, 
    CheckCircleIcon, 
    ArrowRightOnRectangleIcon, 
    EnvelopeIcon
} from '@heroicons/vue/24/outline';
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Estado de Solicitud" />

        <div class="py-12 flex flex-col items-center justify-center min-h-[calc(100vh-80px)]">
            <div class="max-w-2xl w-full px-4 sm:px-6 lg:px-8">
                
                <div class="bg-white border border-gray-200 p-10 rounded-sm shadow-sm text-center relative overflow-hidden">
                    
                    <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-amber-400 via-slate-900 to-amber-400"></div>

                    <div class="mb-8 flex justify-center">
                        <div class="w-20 h-20 bg-slate-50 rounded-full flex items-center justify-center border border-slate-100">
                            <ClockIcon class="w-10 h-10 text-slate-400 stroke-1" />
                        </div>
                    </div>

                    <h1 class="text-3xl font-serif font-bold text-slate-900 mb-4">
                        Solicitud en Proceso
                    </h1>
                    <p class="text-slate-500 font-light text-sm leading-relaxed max-w-lg mx-auto mb-10">
                        Hemos recibido su registro correctamente. Actualmente, su perfil se encuentra en la etapa de validación por parte de nuestro equipo de curaduría.
                    </p>

                    <div class="max-w-md mx-auto mb-12">
                        <div class="relative">
                            <div class="absolute left-4 top-0 h-full w-px bg-gray-200"></div>

                            <div class="relative flex items-start mb-8 group">
                                <div class="absolute left-0 w-8 h-8 bg-white border-2 border-emerald-500 rounded-full flex items-center justify-center z-10">
                                    <CheckCircleIcon class="w-5 h-5 text-emerald-600" />
                                </div>
                                <div class="ml-12 text-left">
                                    <h4 class="text-xs font-bold uppercase tracking-widest text-emerald-700">Registro Completado</h4>
                                    <p class="text-[10px] text-slate-400 mt-1">Sus datos han sido guardados.</p>
                                </div>
                            </div>

                            <div class="relative flex items-start mb-8 group">
                                <div class="absolute left-0 w-8 h-8 bg-white border-2 border-amber-400 rounded-full flex items-center justify-center z-10 shadow-sm">
                                    <div class="w-2 h-2 bg-amber-400 rounded-full animate-pulse"></div>
                                </div>
                                <div class="ml-12 text-left">
                                    <h4 class="text-xs font-bold uppercase tracking-widest text-slate-900">Validación de Perfil</h4>
                                    <p class="text-xs text-slate-500 mt-1">Estimado: 24 - 48 horas hábiles.</p>
                                    <div class="mt-2 inline-flex items-center px-2 py-1 bg-amber-50 border border-amber-100 rounded-sm">
                                        <span class="text-[9px] font-bold text-amber-700 uppercase tracking-wider">En curso</span>
                                    </div>
                                </div>
                            </div>

                            <div class="relative flex items-start group opacity-50">
                                <div class="absolute left-0 w-8 h-8 bg-white border-2 border-gray-200 rounded-full flex items-center justify-center z-10">
                                    <div class="w-2 h-2 bg-gray-200 rounded-full"></div>
                                </div>
                                <div class="ml-12 text-left">
                                    <h4 class="text-xs font-bold uppercase tracking-widest text-slate-400">Activación de Cuenta</h4>
                                    <p class="text-[10px] text-slate-400 mt-1">Acceso total al panel profesional.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="flex flex-col sm:flex-row items-center justify-center gap-4 pt-8 border-t border-gray-50">
                        <Link :href="route('home')" class="px-6 py-3 border border-slate-200 text-slate-600 text-xs font-bold uppercase tracking-widest hover:border-slate-900 hover:text-slate-900 transition rounded-sm w-full sm:w-auto text-center">
                            Volver al Sitio
                        </Link>

                        <a href="mailto:soporte@empresa.com" class="px-6 py-3 bg-slate-900 text-white text-xs font-bold uppercase tracking-widest hover:bg-slate-800 transition rounded-sm w-full sm:w-auto text-center flex items-center justify-center gap-2">
                            <EnvelopeIcon class="w-4 h-4" />
                            Contactar Soporte
                        </a>
                    </div>

                </div>

                <div class="text-center mt-6">
                    <Link :href="route('logout')" method="post" as="button" class="text-xs text-slate-400 hover:text-red-600 transition flex items-center justify-center gap-1 mx-auto">
                        <ArrowRightOnRectangleIcon class="w-3 h-3" />
                        Cerrar Sesión
                    </Link>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>