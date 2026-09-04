<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import {
    UsersIcon,
    CameraIcon,
    PhotoIcon,
    EnvelopeIcon,
    CheckBadgeIcon,
    ExclamationTriangleIcon,
    ArrowTopRightOnSquareIcon,
    ShieldCheckIcon
} from '@heroicons/vue/24/outline';

defineProps({
    stats: {
        type: Object,
        required: true,
    },
});
</script>

<template>
    <Head title="Panel de control admin" />

    <AuthenticatedLayout>
        <div class="min-h-screen bg-[#F8F9FA] text-slate-800 font-sans antialiased py-12 pt-28">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">


                <div class="mb-10 text-center md:text-left flex flex-col md:flex-row items-center gap-6 border-b border-gray-200 pb-8">
                    <div class="w-20 h-20 bg-red-50 rounded-full flex items-center justify-center border-4 border-white shadow-sm shrink-0">
                        <ShieldCheckIcon class="w-10 h-10 text-[#E30613]" />
                    </div>
                    <div>
                        <span class="inline-flex items-center gap-2 bg-red-50 text-[#E30613] px-3 py-1.5 rounded-full text-[10px] font-bold uppercase tracking-widest mb-3 border border-red-100">
                            <span class="w-2 h-2 rounded-full bg-[#E30613] animate-pulse"></span>
                            Administración del sistema
                        </span>
                        <h1 class="text-4xl md:text-6xl font-flux text-black tracking-wide leading-none mb-3">
                            Panel de <span class="text-[#E30613]">control</span>
                        </h1>
                        <p class="text-sm font-medium text-gray-500 max-w-xl">
                            Visión general del rendimiento, métricas clave y gestión de flujos de trabajo.
                        </p>
                    </div>
                </div>


                <div class="grid grid-cols-2 lg:grid-cols-5 gap-4 mb-12">
                    

                    <div class="bg-white rounded-3xl p-6 border border-gray-100 shadow-sm hover:shadow-md transition-shadow relative overflow-hidden group">
                        <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:scale-110 transition-transform">
                            <UsersIcon class="w-16 h-16 text-black" />
                        </div>
                        <p class="text-[10px] font-bold uppercase tracking-widest text-gray-400 mb-2 relative z-10">Total fotógrafos</p>
                        <p class="text-4xl md:text-5xl font-flux text-black relative z-10">{{ stats.total_photographers }}</p>
                    </div>


                    <div class="bg-white rounded-3xl p-6 border border-gray-100 shadow-sm hover:shadow-md transition-shadow relative overflow-hidden group">
                        <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:scale-110 transition-transform">
                            <CalendarIcon class="w-16 h-16 text-black" />
                        </div>
                        <p class="text-[10px] font-bold uppercase tracking-widest text-gray-400 mb-2 relative z-10">Total eventos</p>
                        <p class="text-4xl md:text-5xl font-flux text-black relative z-10">{{ stats.total_events }}</p>
                    </div>


                    <div class="bg-white rounded-3xl p-6 border border-gray-100 shadow-sm hover:shadow-md transition-shadow relative overflow-hidden group">
                        <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:scale-110 transition-transform">
                            <PhotoIcon class="w-16 h-16 text-black" />
                        </div>
                        <p class="text-[10px] font-bold uppercase tracking-widest text-gray-400 mb-2 relative z-10">Total fotografías</p>
                        <p class="text-4xl md:text-5xl font-flux text-black relative z-10">{{ stats.total_photos }}</p>
                    </div>


                    <div class="bg-white rounded-3xl p-6 border border-gray-100 shadow-sm hover:shadow-md transition-shadow relative overflow-hidden group">
                        <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:scale-110 transition-transform">
                            <UsersIcon class="w-16 h-16 text-black" />
                        </div>
                        <p class="text-[10px] font-bold uppercase tracking-widest text-gray-400 mb-2 relative z-10">Usuarios registrados</p>
                        <p class="text-4xl md:text-5xl font-flux text-black relative z-10">{{ stats.total_users }}</p>
                    </div>


                    <div class="bg-red-50 rounded-3xl p-6 border border-red-100 shadow-sm hover:shadow-md transition-shadow relative overflow-hidden group">
                        <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:scale-110 transition-transform">
                            <EnvelopeIcon class="w-16 h-16 text-[#E30613]" />
                        </div>
                        <p class="text-[10px] font-bold uppercase tracking-widest text-red-600 mb-2 relative z-10">Mensajes no leídos</p>
                        <p class="text-4xl md:text-5xl font-flux text-[#E30613] relative z-10">{{ stats.unread_messages }}</p>
                    </div>

                </div>


                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">


                    <div class="lg:col-span-2">
                        <h2 class="text-xs font-bold uppercase tracking-widest text-gray-400 mb-6 flex items-center gap-2">
                            <span class="w-4 h-px bg-gray-300"></span> Estado de profesionales
                        </h2>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            

                            <Link :href="route('admin.photographers.index', { status: 'pending' })"
                                class="bg-white p-8 rounded-3xl border border-gray-100 shadow-sm hover:shadow-md hover:border-yellow-200 transition-all group relative overflow-hidden">
                                <div class="absolute -right-6 -top-6 w-24 h-24 bg-yellow-50 rounded-full blur-2xl group-hover:bg-yellow-100 transition-colors pointer-events-none"></div>
                                <div class="flex items-center gap-3 mb-6 relative z-10">
                                    <div class="w-10 h-10 rounded-full bg-yellow-50 flex items-center justify-center text-yellow-600">
                                        <ExclamationTriangleIcon class="w-5 h-5" />
                                    </div>
                                    <span class="font-bold text-sm uppercase tracking-wider text-slate-700">Pendientes</span>
                                </div>
                                <p class="text-5xl md:text-6xl font-flux text-black mb-2 relative z-10">{{ stats.pending_photographers }}</p>
                                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest relative z-10">Requieren revisión</p>
                            </Link>


                            <Link :href="route('admin.photographers.index', { status: 'approved' })"
                                class="bg-white p-8 rounded-3xl border border-gray-100 shadow-sm hover:shadow-md hover:border-green-200 transition-all group relative overflow-hidden">
                                <div class="absolute -right-6 -top-6 w-24 h-24 bg-green-50 rounded-full blur-2xl group-hover:bg-green-100 transition-colors pointer-events-none"></div>
                                <div class="flex items-center gap-3 mb-6 relative z-10">
                                    <div class="w-10 h-10 rounded-full bg-green-50 flex items-center justify-center text-green-600">
                                        <CheckBadgeIcon class="w-5 h-5" />
                                    </div>
                                    <span class="font-bold text-sm uppercase tracking-wider text-slate-700">Activos</span>
                                </div>
                                <p class="text-5xl md:text-6xl font-flux text-black mb-2 relative z-10">{{ stats.approved_photographers }}</p>
                                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest relative z-10">Operando normalmente</p>
                            </Link>

                            <!-- Rechazados -->
                            <Link :href="route('admin.photographers.index', { status: 'rejected' })"
                                class="bg-white p-8 rounded-3xl border border-gray-100 shadow-sm hover:shadow-md hover:border-gray-300 transition-all group relative overflow-hidden">
                                <div class="flex items-center gap-3 mb-6">
                                    <div class="w-10 h-10 rounded-full bg-gray-50 flex items-center justify-center text-gray-500">
                                        <NoSymbolIcon class="w-5 h-5" />
                                    </div>
                                    <span class="font-bold text-sm uppercase tracking-wider text-slate-700">Rechazados</span>
                                </div>
                                <p class="text-5xl md:text-6xl font-flux text-gray-400 mb-2">{{ stats.rejected_photographers }}</p>
                                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Histórico de rechazos</p>
                            </Link>


                            <Link :href="route('admin.photographers.index', { status: 'suspended' })"
                                class="bg-white p-8 rounded-3xl border border-gray-100 shadow-sm hover:shadow-md hover:border-orange-200 transition-all group relative overflow-hidden">
                                <div class="absolute -right-6 -top-6 w-24 h-24 bg-orange-50 rounded-full blur-2xl group-hover:bg-orange-100 transition-colors pointer-events-none"></div>
                                <div class="flex items-center gap-3 mb-6 relative z-10">
                                    <div class="w-10 h-10 rounded-full bg-orange-50 flex items-center justify-center text-orange-600">
                                        <ExclamationTriangleIcon class="w-5 h-5" />
                                    </div>
                                    <span class="font-bold text-sm uppercase tracking-wider text-slate-700">Suspendidos</span>
                                </div>
                                <p class="text-5xl md:text-6xl font-flux text-black mb-2 relative z-10">{{ stats.suspended_photographers }}</p>
                                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest relative z-10">Acceso revocado</p>
                            </Link>

                        </div>
                    </div>


                    <div class="lg:col-span-1">
                        <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-6 md:p-8 h-full">
                            <h2 class="text-xs font-bold uppercase tracking-widest text-gray-400 mb-6 flex items-center gap-2 border-b border-gray-100 pb-4">
                                <span class="w-4 h-px bg-gray-200"></span> Enlaces rápidos
                            </h2>

                            <div class="space-y-4">

                                <Link :href="route('admin.photographers.index')"
                                    class="flex items-center justify-between p-4 rounded-2xl bg-gray-50 hover:bg-black hover:text-white group transition-colors">
                                    <div class="flex items-center gap-4">
                                        <div class="w-10 h-10 bg-white group-hover:bg-gray-800 rounded-full flex items-center justify-center text-gray-600 group-hover:text-white transition-colors shadow-sm">
                                            <UsersIcon class="w-5 h-5" />
                                        </div>
                                        <div>
                                            <p class="font-bold text-sm text-slate-800 group-hover:text-white transition-colors">Base de datos</p>
                                            <p class="text-[10px] font-medium text-gray-500 group-hover:text-gray-400 transition-colors uppercase tracking-wider">Directorio completo</p>
                                        </div>
                                    </div>
                                </Link>


                                <Link :href="route('admin.photographers.index', { status: 'pending' })"
                                    class="flex items-center justify-between p-4 rounded-2xl bg-gray-50 hover:bg-black hover:text-white group transition-colors">
                                    <div class="flex items-center gap-4">
                                        <div class="w-10 h-10 bg-white group-hover:bg-gray-800 rounded-full flex items-center justify-center text-gray-600 group-hover:text-white transition-colors shadow-sm relative">
                                            <div v-if="stats.pending_photographers > 0" class="absolute -top-1 -right-1 w-3 h-3 bg-yellow-500 border-2 border-white rounded-full"></div>
                                            <CheckBadgeIcon class="w-5 h-5" />
                                        </div>
                                        <div>
                                            <p class="font-bold text-sm text-slate-800 group-hover:text-white transition-colors">Aprobaciones</p>
                                            <p class="text-[10px] font-medium text-gray-500 group-hover:text-gray-400 transition-colors uppercase tracking-wider">{{ stats.pending_photographers }} solicitudes</p>
                                        </div>
                                    </div>
                                </Link>


                                <Link :href="route('admin.messages.index')"
                                    class="flex items-center justify-between p-4 rounded-2xl bg-gray-50 hover:bg-black hover:text-white group transition-colors">
                                    <div class="flex items-center gap-4">
                                        <div class="w-10 h-10 bg-white group-hover:bg-gray-800 rounded-full flex items-center justify-center text-gray-600 group-hover:text-white transition-colors shadow-sm relative">
                                            <div v-if="stats.unread_messages > 0" class="absolute -top-1 -right-1 w-3 h-3 bg-[#E30613] border-2 border-white rounded-full"></div>
                                            <EnvelopeIcon class="w-5 h-5" />
                                        </div>
                                        <div>
                                            <p class="font-bold text-sm text-slate-800 group-hover:text-white transition-colors">Mensajería</p>
                                            <p class="text-[10px] font-medium text-gray-500 group-hover:text-gray-400 transition-colors uppercase tracking-wider">{{ stats.unread_messages }} sin leer</p>
                                        </div>
                                    </div>
                                </Link>

                                <hr class="border-gray-100 my-4" />


                                <Link :href="route('home')"
                                    class="w-full flex items-center justify-center gap-2 p-4 rounded-2xl bg-black text-white hover:bg-[#E30613] transition-colors shadow-sm group">
                                    <ArrowTopRightOnSquareIcon class="w-5 h-5" />
                                    <span class="font-bold text-sm uppercase tracking-wider">Ver sitio público</span>
                                </Link>
                            </div>

                        </div>
                    </div>

                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>