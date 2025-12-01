<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Cuenta Suspendida" />

        <div class="py-12">
            <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Card con tema naranja/rojo -->
                <div class="bg-gradient-to-br from-orange-50 to-red-50 rounded-3xl shadow-2xl p-8 md:p-12 text-center">
                    <!-- Icono -->
                    <div class="mb-6">
                        <div class="inline-flex items-center justify-center w-24 h-24 bg-orange-100 rounded-full">
                            <svg class="w-12 h-12 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                            </svg>
                        </div>
                    </div>

                    <!-- Título -->
                    <h1 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
                         Cuenta Suspendida
                    </h1>

                    <!-- Descripción -->
                    <p class="text-lg text-gray-700 mb-6">
                        Tu cuenta de fotógrafo ha sido suspendida temporalmente.
                    </p>

                    <!-- Información importante -->
                    <div class="bg-white/60 backdrop-blur-sm rounded-2xl p-6 mb-8 text-left">
                        <h2 class="font-bold text-gray-900 mb-3 flex items-center gap-2">
                            <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                            ¿Qué significa esto?
                        </h2>
                        <ul class="space-y-3 text-gray-700">
                            <li class="flex items-start gap-2">
                                <span class="text-orange-600">•</span>
                                <span>No puedes acceder a tu panel de fotógrafo.</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <span class="text-orange-600">•</span>
                                <span>Tus eventos y fotos no están visibles públicamente.</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <span class="text-orange-600">•</span>
                                <span>No puedes subir nuevas fotos ni crear eventos.</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <span class="text-orange-600">•</span>
                                <span>La suspensión puede ser temporal o permanente.</span>
                            </li>
                        </ul>
                    </div>

                    <!-- Razones comunes -->
                    <div class="bg-red-50 rounded-2xl p-6 mb-8 text-left">
                        <h3 class="font-bold text-gray-900 mb-3">Razones comunes de suspensión:</h3>
                        <ul class="space-y-2 text-gray-700 text-sm">
                            <li class="flex items-start gap-2">
                                <span class="text-red-500">•</span>
                                <span>Violación de los términos de servicio</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <span class="text-red-500">•</span>
                                <span>Quejas o reportes de clientes</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <span class="text-red-500">•</span>
                                <span>Contenido inapropiado o de baja calidad</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <span class="text-red-500">•</span>
                                <span>Falta de respuesta a solicitudes de clientes</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <span class="text-red-500">•</span>
                                <span>Actividad sospechosa o fraudulenta</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <span class="text-red-500">•</span>
                                <span>Incumplimiento de compromisos con clientes</span>
                            </li>
                        </ul>
                    </div>

                    <!-- Qué hacer -->
                    <div class="bg-blue-50 rounded-2xl p-6 mb-8 text-left">
                        <h3 class="font-bold text-gray-900 mb-3 flex items-center gap-2">
                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            ¿Qué puedes hacer?
                        </h3>
                        <p class="text-gray-700 mb-4">
                            Para resolver esta situación y solicitar la reactivación de tu cuenta:
                        </p>
                        <ul class="space-y-3 text-gray-700">
                            <li class="flex items-start gap-2">
                                <span class="text-blue-600 font-bold">1.</span>
                                <span>Revisa el email que te enviamos con los detalles de la suspensión.</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <span class="text-blue-600 font-bold">2.</span>
                                <span>Contacta inmediatamente a nuestro equipo de soporte.</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <span class="text-blue-600 font-bold">3.</span>
                                <span>Proporciona cualquier información adicional que se te solicite.</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <span class="text-blue-600 font-bold">4.</span>
                                <span>Espera la respuesta de nuestro equipo (generalmente 24-48 horas).</span>
                            </li>
                        </ul>
                    </div>

                    <!-- Contacto urgente -->
                    <div class="bg-gradient-to-r from-indigo-50 to-purple-50 rounded-2xl p-6 mb-8 border-2 border-indigo-200">
                        <h3 class="font-bold text-gray-900 mb-3">📧 Contacto Urgente</h3>
                        <p class="text-gray-700 mb-3">
                            Para apelar esta suspensión o solicitar más información:
                        </p>
                        <div class="space-y-2">
                            <a href="mailto:soporte@photomarket.com" class="block text-indigo-600 font-bold hover:underline text-lg">
                                📧 soporte@photomarket.com
                            </a>
                            <p class="text-sm text-gray-600">
                                Asunto sugerido: "Apelación de Suspensión - [Tu Nombre]"
                            </p>
                        </div>
                    </div>

                    <!-- Advertencia -->
                    <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-8 text-left">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm text-yellow-700">
                                    <strong>Importante:</strong> Las suspensiones repetidas pueden resultar en la eliminación permanente de tu cuenta.
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Acciones -->
                    <div class="flex flex-col sm:flex-row gap-4 justify-center">
                        <Link
                            :href="route('home')"
                            class="inline-flex items-center justify-center px-6 py-3 bg-gray-600 hover:bg-gray-700 text-white font-bold rounded-xl shadow-lg hover:shadow-xl transform hover:scale-105 transition"
                        >
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                            </svg>
                            Volver al Inicio
                        </Link>

                        <a
                            href="mailto:soporte@photomarket.com?subject=Apelación de Suspensión"
                            class="inline-flex items-center justify-center px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl shadow-lg hover:shadow-xl transform hover:scale-105 transition"
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
