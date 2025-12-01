<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    stats: {
        type: Object,
        required: true,
    },
});
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Panel de Administración" />

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="mb-8">
                    <h1 class="text-3xl font-bold text-gray-900">
                         Panel de Administración
                    </h1>
                    <p class="text-gray-600 mt-2">
                        Gestiona fotógrafos, eventos y estadísticas generales de la plataforma.
                    </p>
                </div>

                <!-- Estadísticas Generales -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                    <!-- Total Fotógrafos -->
                    <div class="bg-white rounded-xl shadow-lg p-6 border-l-4 border-indigo-500">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600">Total Fotógrafos</p>
                                <p class="text-3xl font-bold text-gray-900 mt-2">{{ stats.total_photographers }}</p>
                            </div>
                            <div class="bg-indigo-100 rounded-full p-3">
                                <svg class="w-8 h-8 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- Total Eventos -->
                    <div class="bg-white rounded-xl shadow-lg p-6 border-l-4 border-green-500">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600">Total Eventos</p>
                                <p class="text-3xl font-bold text-gray-900 mt-2">{{ stats.total_events }}</p>
                            </div>
                            <div class="bg-green-100 rounded-full p-3">
                                <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- Total Fotos -->
                    <div class="bg-white rounded-xl shadow-lg p-6 border-l-4 border-purple-500">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600">Total Fotos</p>
                                <p class="text-3xl font-bold text-gray-900 mt-2">{{ stats.total_photos }}</p>
                            </div>
                            <div class="bg-purple-100 rounded-full p-3">
                                <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- Total Usuarios -->
                    <div class="bg-white rounded-xl shadow-lg p-6 border-l-4 border-orange-500">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600">Total Usuarios</p>
                                <p class="text-3xl font-bold text-gray-900 mt-2">{{ stats.total_users }}</p>
                            </div>
                            <div class="bg-orange-100 rounded-full p-3">
                                <svg class="w-8 h-8 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Estados de Fotógrafos -->
                <div class="bg-white rounded-xl shadow-lg p-8 mb-8">
                    <h2 class="text-xl font-bold text-gray-900 mb-6"> Estados de Fotógrafos</h2>
                    
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                        <!-- Pendientes -->
                        <Link 
                            :href="route('admin.photographers.index', { status: 'pending' })"
                            class="bg-yellow-50 border-2 border-yellow-200 rounded-lg p-4 hover:bg-yellow-100 transition cursor-pointer"
                        >
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-sm font-medium text-yellow-800"> Pendientes</span>
                                <span class="bg-yellow-500 text-white text-xs font-bold px-2 py-1 rounded-full">
                                    {{ stats.pending_photographers }}
                                </span>
                            </div>
                            <p class="text-2xl font-bold text-yellow-700">{{ stats.pending_photographers }}</p>
                        </Link>

                        <!-- Aprobados -->
                        <Link 
                            :href="route('admin.photographers.index', { status: 'approved' })"
                            class="bg-green-50 border-2 border-green-200 rounded-lg p-4 hover:bg-green-100 transition cursor-pointer"
                        >
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-sm font-medium text-green-800"> Aprobados</span>
                                <span class="bg-green-500 text-white text-xs font-bold px-2 py-1 rounded-full">
                                    {{ stats.approved_photographers }}
                                </span>
                            </div>
                            <p class="text-2xl font-bold text-green-700">{{ stats.approved_photographers }}</p>
                        </Link>

                        <!-- Rechazados -->
                        <Link 
                            :href="route('admin.photographers.index', { status: 'rejected' })"
                            class="bg-red-50 border-2 border-red-200 rounded-lg p-4 hover:bg-red-100 transition cursor-pointer"
                        >
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-sm font-medium text-red-800"> Rechazados</span>
                                <span class="bg-red-500 text-white text-xs font-bold px-2 py-1 rounded-full">
                                    {{ stats.rejected_photographers }}
                                </span>
                            </div>
                            <p class="text-2xl font-bold text-red-700">{{ stats.rejected_photographers }}</p>
                        </Link>

                        <!-- Suspendidos -->
                        <Link 
                            :href="route('admin.photographers.index', { status: 'suspended' })"
                            class="bg-orange-50 border-2 border-orange-200 rounded-lg p-4 hover:bg-orange-100 transition cursor-pointer"
                        >
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-sm font-medium text-orange-800"> Suspendidos</span>
                                <span class="bg-orange-500 text-white text-xs font-bold px-2 py-1 rounded-full">
                                    {{ stats.suspended_photographers }}
                                </span>
                            </div>
                            <p class="text-2xl font-bold text-orange-700">{{ stats.suspended_photographers }}</p>
                        </Link>
                    </div>
                </div>

                <!-- Acciones Rápidas -->
                <div class="bg-white rounded-xl shadow-lg p-8">
                    <h2 class="text-xl font-bold text-gray-900 mb-6"> Acciones Rápidas</h2>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <Link
                            :href="route('admin.photographers.index')"
                            class="flex items-center gap-4 p-4 bg-gradient-to-r from-indigo-500 to-purple-600 text-white rounded-lg hover:from-indigo-600 hover:to-purple-700 transition shadow-lg"
                        >
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                            <div>
                                <p class="font-bold">Gestionar Fotógrafos</p>
                                <p class="text-sm opacity-90">Aprobar, rechazar o suspender</p>
                            </div>
                        </Link>

                        <Link
                            :href="route('admin.photographers.index', { status: 'pending' })"
                            class="flex items-center gap-4 p-4 bg-gradient-to-r from-yellow-500 to-orange-600 text-white rounded-lg hover:from-yellow-600 hover:to-orange-700 transition shadow-lg"
                        >
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <div>
                                <p class="font-bold">Solicitudes Pendientes</p>
                                <p class="text-sm opacity-90">{{ stats.pending_photographers }} esperando aprobación</p>
                            </div>
                        </Link>

                        <Link
                            :href="route('home')"
                            class="flex items-center gap-4 p-4 bg-gradient-to-r from-green-500 to-teal-600 text-white rounded-lg hover:from-green-600 hover:to-teal-700 transition shadow-lg"
                        >
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                            </svg>
                            <div>
                                <p class="font-bold">Ver Sitio Público</p>
                                <p class="text-sm opacity-90">Vista de usuario normal</p>
                            </div>
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
