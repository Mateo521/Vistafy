<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Cuenta Pendiente" />

        <div class="py-12">
            <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Card con glassmorphism -->
                <div class="bg-gradient-to-br from-yellow-50 to-orange-50 rounded-3xl shadow-2xl p-8 md:p-12 text-center">
                    <!-- Icono animado -->
                    <div class="mb-6">
                        <div class="inline-flex items-center justify-center w-24 h-24 bg-yellow-100 rounded-full animate-pulse">
                            <svg class="w-12 h-12 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>

                    <!-- Título -->
                    <h1 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
                         Cuenta Pendiente de Aprobación
                    </h1>

                    <!-- Descripción -->
                    <p class="text-lg text-gray-700 mb-6">
                        ¡Gracias por registrarte como fotógrafo en <strong>Empresa</strong>!
                    </p>

                    <div class="bg-white/60 backdrop-blur-sm rounded-2xl p-6 mb-8 text-left">
                        <h2 class="font-bold text-gray-900 mb-3 flex items-center gap-2">
                            <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            ¿Qué sigue ahora?
                        </h2>
                        <ul class="space-y-3 text-gray-700">
                            <li class="flex items-start gap-2">
                                <span class="text-indigo-600 font-bold">1.</span>
                                <span>Nuestro equipo está revisando tu perfil y documentación.</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <span class="text-indigo-600 font-bold">2.</span>
                                <span>Verificaremos que cumples con nuestros estándares de calidad.</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <span class="text-indigo-600 font-bold">3.</span>
                                <span>Te vamos a notificar por <strong>email</strong> cuando tu cuenta sea aprobada.</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <span class="text-indigo-600 font-bold">4.</span>
                                <span>El proceso suele tomar entre <strong>24 y 48 horas</strong>.</span>
                            </li>
                        </ul>
                    </div>

                    <!-- Información de contacto -->
                    <div class="bg-blue-50 rounded-2xl p-6 mb-8 text-left">
                        <h3 class="font-bold text-gray-900 mb-3 flex items-center gap-2">
                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                            ¿tenés preguntas?
                        </h3>
                        <p class="text-gray-700">
                            Si tenés alguna duda o necesitas actualizar tu información, contáctanos en:
                        </p>
                        <a href="mailto:soporte@empresa.com" class="text-indigo-600 font-bold hover:underline">
                            soporte@empresa.com
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

                        <Link
                            :href="route('profile.edit')"
                            class="inline-flex items-center justify-center px-6 py-3 bg-white hover:bg-gray-50 text-gray-700 font-bold rounded-xl shadow-lg hover:shadow-xl transform hover:scale-105 transition border-2 border-gray-200"
                        >
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                            Ver mi Perfil
                        </Link>
                    </div>

                    <!-- Timeline estimado -->
                    <div class="mt-10 pt-8 border-t border-gray-200">
                        <p class="text-sm text-gray-500 mb-4">Timeline estimado:</p>
                        <div class="flex items-center justify-center gap-2 text-xs text-gray-600">
                            <div class="flex flex-col items-center">
                                <div class="w-8 h-8 bg-green-500 rounded-full flex items-center justify-center text-white mb-1">
                                    ✓
                                </div>
                                <span>Registro</span>
                            </div>
                            <div class="w-12 h-0.5 bg-gray-300"></div>
                            <div class="flex flex-col items-center">
                                <div class="w-8 h-8 bg-yellow-500 rounded-full flex items-center justify-center text-white mb-1 animate-pulse">
                                    ⏳
                                </div>
                                <span>Revisión</span>
                            </div>
                            <div class="w-12 h-0.5 bg-gray-300"></div>
                            <div class="flex flex-col items-center opacity-50">
                                <div class="w-8 h-8 bg-gray-300 rounded-full flex items-center justify-center text-white mb-1">
                                    ?
                                </div>
                                <span>Aprobación</span>
                            </div>
                            <div class="w-12 h-0.5 bg-gray-300"></div>
                            <div class="flex flex-col items-center opacity-50">
                                <div class="w-8 h-8 bg-gray-300 rounded-full flex items-center justify-center text-white mb-1">
                                    🚀
                                </div>
                                <span>Activo</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
