<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    reason: {
        type: String,
        default: null,
    },
});
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Cuenta Rechazada" />

        <div class="py-12">
            <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Card con tema rojo -->
                <div class="bg-gradient-to-br from-red-50 to-pink-50 rounded-3xl shadow-2xl p-8 md:p-12 text-center">
                    <!-- Icono -->
                    <div class="mb-6">
                        <div class="inline-flex items-center justify-center w-24 h-24 bg-red-100 rounded-full">
                            <svg class="w-12 h-12 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </div>
                    </div>

                    <!-- Título -->
                    <h1 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
                         Solicitud No Aprobada
                    </h1>

                    <!-- Descripción -->
                    <p class="text-lg text-gray-700 mb-6">
                        Lamentamos informarte que tu solicitud de registro como fotógrafo no ha sido aprobada.
                    </p>

                    <!-- Motivo del rechazo -->
                    <div v-if="reason" class="bg-white/60 backdrop-blur-sm rounded-2xl p-6 mb-8 text-left">
                        <h2 class="font-bold text-gray-900 mb-3 flex items-center gap-2">
                            <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Motivo del rechazo:
                        </h2>
                        <p class="text-gray-700 bg-red-50 p-4 rounded-lg border border-red-200">
                            {{ reason }}
                        </p>
                    </div>

                    <!-- Razones comunes -->
                    <div class="bg-blue-50 rounded-2xl p-6 mb-8 text-left">
                        <h3 class="font-bold text-gray-900 mb-3">Razones comunes de rechazo:</h3>
                        <ul class="space-y-2 text-gray-700 text-sm">
                            <li class="flex items-start gap-2">
                                <span class="text-red-500">•</span>
                                <span>Información incompleta o incorrecta en el perfil</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <span class="text-red-500">•</span>
                                <span>Falta de portafolio o trabajos previos</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <span class="text-red-500">•</span>
                                <span>Calidad insuficiente del trabajo presentado</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <span class="text-red-500">•</span>
                                <span>No cumplir con los estándares de servicio requeridos</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <span class="text-red-500">•</span>
                                <span>Datos de contacto no verificables</span>
                            </li>
                        </ul>
                    </div>

                    <!-- ¿Qué puedes hacer? -->
                    <div class="bg-green-50 rounded-2xl p-6 mb-8 text-left">
                        <h3 class="font-bold text-gray-900 mb-3 flex items-center gap-2">
                            <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            ¿Qué puedes hacer?
                        </h3>
                        <ul class="space-y-3 text-gray-700">
                            <li class="flex items-start gap-2">
                                <span class="text-green-600 font-bold">1.</span>
                                <span>Revisa el motivo del rechazo y corrige los problemas mencionados.</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <span class="text-green-600 font-bold">2.</span>
                                <span>Mejora tu portafolio y asegúrate de cumplir con nuestros estándares.</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <span class="text-green-600 font-bold">3.</span>
                                <span>Puedes volver a aplicar después de <strong>30 días</strong>.</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <span class="text-green-600 font-bold">4.</span>
                                <span>Contacta a nuestro equipo si tienes preguntas o necesitas orientación.</span>
                            </li>
                        </ul>
                    </div>

                    <!-- Contacto -->
                    <div class="bg-white/60 backdrop-blur-sm rounded-2xl p-6 mb-8">
                        <p class="text-gray-700 mb-3">
                            Si crees que esto es un error o deseas más información, contáctanos:
                        </p>
                        <a href="mailto:soporte@photomarket.com" class="text-indigo-600 font-bold hover:underline text-lg">
                            soporte@photomarket.com
                        </a>
                    </div>

                    <!-- Acciones -->
                    <div class="flex flex-col sm:flex-row gap-4 justify-center">
                        <Link
                            :href="route('home')"
                            class="inline-flex items-center justify-center px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl shadow-lg hover:shadow-xl transform hover:scale-105 transition"
                        >
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                            </svg>
                            Volver al Inicio
                        </Link>

                        <a
                            href="mailto:soporte@photomarket.com"
                            class="inline-flex items-center justify-center px-6 py-3 bg-white hover:bg-gray-50 text-gray-700 font-bold rounded-xl shadow-lg hover:shadow-xl transform hover:scale-105 transition border-2 border-gray-200"
                        >
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                            Contactar Soporte
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
