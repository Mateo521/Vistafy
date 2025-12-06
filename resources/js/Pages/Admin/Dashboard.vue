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
        <Head title="Panel de Control" />

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                
                <div class="mb-10 border-b border-gray-200 pb-6">
                    <span class="text-xs font-bold tracking-[0.2em] text-slate-400 uppercase mb-2 block">
                        Administración
                    </span>
                    <h1 class="text-3xl font-serif font-bold text-slate-900">
                        Panel de Control
                    </h1>
                    <p class="text-sm text-slate-500 mt-2 font-light max-w-2xl">
                        Visión general del rendimiento de la plataforma y gestión de flujos de trabajo.
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-12">
                    <div class="bg-white p-6 border border-gray-200 rounded-sm shadow-sm flex flex-col justify-between h-32">
                        <div class="flex justify-between items-start">
                            <span class="text-[10px] font-bold uppercase tracking-widest text-slate-500">Fotógrafos</span>
                            <svg class="w-5 h-5 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                        </div>
                        <span class="text-4xl font-serif font-medium text-slate-900">{{ stats.total_photographers }}</span>
                    </div>

                    <div class="bg-white p-6 border border-gray-200 rounded-sm shadow-sm flex flex-col justify-between h-32">
                        <div class="flex justify-between items-start">
                            <span class="text-[10px] font-bold uppercase tracking-widest text-slate-500">Eventos</span>
                            <svg class="w-5 h-5 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                        </div>
                        <span class="text-4xl font-serif font-medium text-slate-900">{{ stats.total_events }}</span>
                    </div>

                    <div class="bg-white p-6 border border-gray-200 rounded-sm shadow-sm flex flex-col justify-between h-32">
                        <div class="flex justify-between items-start">
                            <span class="text-[10px] font-bold uppercase tracking-widest text-slate-500">Fotografías</span>
                            <svg class="w-5 h-5 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                        </div>
                        <span class="text-4xl font-serif font-medium text-slate-900">{{ stats.total_photos }}</span>
                    </div>

                    <div class="bg-white p-6 border border-gray-200 rounded-sm shadow-sm flex flex-col justify-between h-32">
                        <div class="flex justify-between items-start">
                            <span class="text-[10px] font-bold uppercase tracking-widest text-slate-500">Usuarios</span>
                            <svg class="w-5 h-5 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                        </div>
                        <span class="text-4xl font-serif font-medium text-slate-900">{{ stats.total_users }}</span>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    
                    <div class="lg:col-span-2">
                        <div class="bg-white border border-gray-200 rounded-sm shadow-sm">
                            <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center">
                                <h3 class="text-sm font-bold uppercase tracking-wide text-slate-900">Estado de Solicitudes</h3>
                            </div>
                            
                            <div class="p-6 grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <Link :href="route('admin.photographers.index', { status: 'pending' })"
                                    class="group p-4 border border-gray-100 bg-gray-50/50 hover:bg-white hover:border-slate-900 hover:shadow-md transition-all duration-300 rounded-sm">
                                    <div class="flex justify-between items-start mb-2">
                                        <div class="flex items-center gap-2">
                                            <span class="w-2 h-2 rounded-full bg-yellow-500"></span>
                                            <span class="text-xs font-bold text-slate-600 uppercase tracking-wide group-hover:text-slate-900">Pendientes</span>
                                        </div>
                                        <svg class="w-4 h-4 text-gray-300 group-hover:text-slate-900 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                                    </div>
                                    <p class="text-3xl font-serif text-slate-900">{{ stats.pending_photographers }}</p>
                                    <p class="text-[10px] text-slate-400 mt-1">Requieren revisión</p>
                                </Link>

                                <Link :href="route('admin.photographers.index', { status: 'approved' })"
                                    class="group p-4 border border-gray-100 bg-gray-50/50 hover:bg-white hover:border-slate-900 hover:shadow-md transition-all duration-300 rounded-sm">
                                    <div class="flex justify-between items-start mb-2">
                                        <div class="flex items-center gap-2">
                                            <span class="w-2 h-2 rounded-full bg-emerald-600"></span>
                                            <span class="text-xs font-bold text-slate-600 uppercase tracking-wide group-hover:text-slate-900">Activos</span>
                                        </div>
                                        <svg class="w-4 h-4 text-gray-300 group-hover:text-slate-900 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                                    </div>
                                    <p class="text-3xl font-serif text-slate-900">{{ stats.approved_photographers }}</p>
                                    <p class="text-[10px] text-slate-400 mt-1">Operando normalmente</p>
                                </Link>

                                <Link :href="route('admin.photographers.index', { status: 'rejected' })"
                                    class="group p-4 border border-gray-100 bg-gray-50/50 hover:bg-white hover:border-slate-900 hover:shadow-md transition-all duration-300 rounded-sm">
                                    <div class="flex justify-between items-start mb-2">
                                        <div class="flex items-center gap-2">
                                            <span class="w-2 h-2 rounded-full bg-red-500"></span>
                                            <span class="text-xs font-bold text-slate-600 uppercase tracking-wide group-hover:text-slate-900">Rechazados</span>
                                        </div>
                                        <svg class="w-4 h-4 text-gray-300 group-hover:text-slate-900 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                                    </div>
                                    <p class="text-3xl font-serif text-slate-900">{{ stats.rejected_photographers }}</p>
                                    <p class="text-[10px] text-slate-400 mt-1">Histórico</p>
                                </Link>

                                <Link :href="route('admin.photographers.index', { status: 'suspended' })"
                                    class="group p-4 border border-gray-100 bg-gray-50/50 hover:bg-white hover:border-slate-900 hover:shadow-md transition-all duration-300 rounded-sm">
                                    <div class="flex justify-between items-start mb-2">
                                        <div class="flex items-center gap-2">
                                            <span class="w-2 h-2 rounded-full bg-orange-400"></span>
                                            <span class="text-xs font-bold text-slate-600 uppercase tracking-wide group-hover:text-slate-900">Suspendidos</span>
                                        </div>
                                        <svg class="w-4 h-4 text-gray-300 group-hover:text-slate-900 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                                    </div>
                                    <p class="text-3xl font-serif text-slate-900">{{ stats.suspended_photographers }}</p>
                                    <p class="text-[10px] text-slate-400 mt-1">Acceso revocado</p>
                                </Link>
                            </div>
                        </div>
                    </div>

                    <div class="lg:col-span-1">
                        <div class="bg-slate-900 rounded-sm shadow-lg text-white h-full p-6 flex flex-col">
                            <h3 class="text-sm font-bold uppercase tracking-widest text-white/80 mb-6">Accesos Directos</h3>
                            
                            <div class="space-y-4 flex-1">
                                <Link :href="route('admin.photographers.index')" 
                                    class="block p-4 rounded-sm bg-white/5 border border-white/10 hover:bg-white/10 hover:border-white/30 transition group">
                                    <div class="flex items-center gap-3">
                                        <div class="p-2 bg-white/10 rounded-sm group-hover:bg-white group-hover:text-slate-900 transition-colors">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                                        </div>
                                        <div>
                                            <span class="text-sm font-bold block">Gestión de Usuarios</span>
                                            <span class="text-xs text-white/50">Base de datos completa</span>
                                        </div>
                                    </div>
                                </Link>

                                <Link :href="route('admin.photographers.index', { status: 'pending' })" 
                                    class="block p-4 rounded-sm bg-white/5 border border-white/10 hover:bg-white/10 hover:border-white/30 transition group">
                                    <div class="flex items-center gap-3">
                                        <div class="p-2 bg-white/10 rounded-sm group-hover:bg-white group-hover:text-slate-900 transition-colors">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                        </div>
                                        <div>
                                            <span class="text-sm font-bold block">Aprobaciones</span>
                                            <span class="text-xs text-white/50">{{ stats.pending_photographers }} pendientes</span>
                                        </div>
                                    </div>
                                </Link>

                                <Link :href="route('home')" 
                                    class="block p-4 rounded-sm bg-white/5 border border-white/10 hover:bg-white/10 hover:border-white/30 transition group">
                                    <div class="flex items-center gap-3">
                                        <div class="p-2 bg-white/10 rounded-sm group-hover:bg-white group-hover:text-slate-900 transition-colors">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" /></svg>
                                        </div>
                                        <div>
                                            <span class="text-sm font-bold block">Ir al Sitio</span>
                                            <span class="text-xs text-white/50">Vista pública</span>
                                        </div>
                                    </div>
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>