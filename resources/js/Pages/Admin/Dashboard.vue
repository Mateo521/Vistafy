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

        <div class="min-h-screen bg-black py-12 text-white selection:bg-red-600 selection:text-black">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

                <div class="mb-12 border-b-2 border-zinc-800 pb-8 relative">
                    <div class="absolute top-0 right-0 w-16 h-16 border-t-4 border-r-4 border-red-600 opacity-20 pointer-events-none"></div>
                    
                    <span class="font-mono text-xs font-bold text-red-600 uppercase tracking-widest mb-4 block animate-pulse">
                        [SYS_ADMIN] Administración
                    </span>
                    <h1 class="text-5xl md:text-7xl font-black uppercase tracking-tighter text-white leading-none">
                        Panel de<br>Control.
                    </h1>
                    <p class="font-mono text-xs text-zinc-500 mt-6 uppercase tracking-wider max-w-2xl border-l-2 border-red-600 pl-4">
                        Visión general del rendimiento de la plataforma y gestión de flujos de trabajo.
                    </p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 mb-16">
                    <div class="group relative bg-zinc-950 border border-zinc-800 p-6 flex flex-col justify-between h-40 hover:border-red-600 transition-colors overflow-hidden">
                        <div class="absolute top-2 left-2 w-3 h-3 border-t border-l border-white opacity-0 group-hover:opacity-100 transition-opacity"></div>
                        <div class="absolute bottom-2 right-2 w-3 h-3 border-b border-r border-white opacity-0 group-hover:opacity-100 transition-opacity"></div>
                        
                        <div class="flex justify-between items-start relative z-10">
                            <span class="font-mono text-[10px] uppercase tracking-widest text-zinc-500 group-hover:text-white transition-colors">Fotógrafos</span>
                            <svg class="w-5 h-5 text-zinc-700 group-hover:text-red-600 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                        </div>
                        <span class="text-6xl font-black tracking-tighter text-white group-hover:text-red-600 transition-colors mt-4">{{ stats.total_photographers }}</span>
                    </div>

                    <div class="group relative bg-zinc-950 border border-zinc-800 p-6 flex flex-col justify-between h-40 hover:border-red-600 transition-colors overflow-hidden">
                        <div class="absolute top-2 left-2 w-3 h-3 border-t border-l border-white opacity-0 group-hover:opacity-100 transition-opacity"></div>
                        <div class="absolute bottom-2 right-2 w-3 h-3 border-b border-r border-white opacity-0 group-hover:opacity-100 transition-opacity"></div>

                        <div class="flex justify-between items-start relative z-10">
                            <span class="font-mono text-[10px] uppercase tracking-widest text-zinc-500 group-hover:text-white transition-colors">Eventos</span>
                            <svg class="w-5 h-5 text-zinc-700 group-hover:text-red-600 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <span class="text-6xl font-black tracking-tighter text-white group-hover:text-red-600 transition-colors mt-4">{{ stats.total_events }}</span>
                    </div>

                    <div class="group relative bg-zinc-950 border border-zinc-800 p-6 flex flex-col justify-between h-40 hover:border-red-600 transition-colors overflow-hidden">
                        <div class="absolute top-2 left-2 w-3 h-3 border-t border-l border-white opacity-0 group-hover:opacity-100 transition-opacity"></div>
                        <div class="absolute bottom-2 right-2 w-3 h-3 border-b border-r border-white opacity-0 group-hover:opacity-100 transition-opacity"></div>

                        <div class="flex justify-between items-start relative z-10">
                            <span class="font-mono text-[10px] uppercase tracking-widest text-zinc-500 group-hover:text-white transition-colors">Fotografías</span>
                            <svg class="w-5 h-5 text-zinc-700 group-hover:text-red-600 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <span class="text-6xl font-black tracking-tighter text-white group-hover:text-red-600 transition-colors mt-4">{{ stats.total_photos }}</span>
                    </div>

                    <div class="group relative bg-zinc-950 border border-zinc-800 p-6 flex flex-col justify-between h-40 hover:border-red-600 transition-colors overflow-hidden">
                        <div class="absolute top-2 left-2 w-3 h-3 border-t border-l border-white opacity-0 group-hover:opacity-100 transition-opacity"></div>
                        <div class="absolute bottom-2 right-2 w-3 h-3 border-b border-r border-white opacity-0 group-hover:opacity-100 transition-opacity"></div>

                        <div class="flex justify-between items-start relative z-10">
                            <span class="font-mono text-[10px] uppercase tracking-widest text-zinc-500 group-hover:text-white transition-colors">Usuarios</span>
                            <svg class="w-5 h-5 text-zinc-700 group-hover:text-red-600 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg>
                        </div>
                        <span class="text-6xl font-black tracking-tighter text-white group-hover:text-red-600 transition-colors mt-4">{{ stats.total_users }}</span>
                    </div>

                    <div class="group relative bg-zinc-950 border border-zinc-800 p-6 flex flex-col justify-between h-40 hover:border-red-600 transition-colors overflow-hidden">
                        <div class="absolute inset-0 bg-red-600/5 opacity-0 group-hover:opacity-100 transition-opacity"></div>
                        <div class="absolute top-2 left-2 w-3 h-3 border-t border-l border-red-600 opacity-0 group-hover:opacity-100 transition-opacity"></div>
                        <div class="absolute bottom-2 right-2 w-3 h-3 border-b border-r border-red-600 opacity-0 group-hover:opacity-100 transition-opacity"></div>

                        <div class="flex justify-between items-start relative z-10">
                            <span class="font-mono text-[10px] uppercase tracking-widest text-zinc-500 group-hover:text-red-600 transition-colors">Mensajes</span>
                            <svg class="w-5 h-5 text-zinc-700 group-hover:text-red-600 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <span class="text-6xl font-black tracking-tighter text-white group-hover:text-red-600 transition-colors mt-4">{{ stats.unread_messages }}</span>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

                    <div class="lg:col-span-2">
                        <div class="mb-6 flex items-center gap-3">
                            <div class="w-3 h-3 bg-white"></div>
                            <h3 class="font-mono text-sm font-bold uppercase tracking-widest text-white">Estado de Solicitudes</h3>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <Link :href="route('admin.photographers.index', { status: 'pending' })"
                                class="group relative bg-zinc-950 p-6 border border-zinc-800 hover:border-white hover:bg-white transition-all duration-300">
                                <div class="flex justify-between items-start mb-6">
                                    <div class="flex items-center gap-3">
                                        <span class="w-3 h-3 bg-yellow-500 shadow-[0_0_10px_rgba(234,179,8,0.5)]"></span>
                                        <span class="font-mono text-xs font-bold text-zinc-400 uppercase tracking-widest group-hover:text-black">Pendientes</span>
                                    </div>
                                    <svg class="w-5 h-5 text-zinc-700 group-hover:text-black transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                    </svg>
                                </div>
                                <p class="text-7xl font-black tracking-tighter text-white group-hover:text-black transition-colors">{{ stats.pending_photographers }}</p>
                                <p class="font-mono text-[10px] text-zinc-600 uppercase mt-2 group-hover:text-zinc-500">Requieren revisión</p>
                            </Link>

                            <Link :href="route('admin.photographers.index', { status: 'approved' })"
                                class="group relative bg-zinc-950 p-6 border border-zinc-800 hover:border-white hover:bg-white transition-all duration-300">
                                <div class="flex justify-between items-start mb-6">
                                    <div class="flex items-center gap-3">
                                        <span class="w-3 h-3 bg-emerald-500 shadow-[0_0_10px_rgba(16,185,129,0.5)]"></span>
                                        <span class="font-mono text-xs font-bold text-zinc-400 uppercase tracking-widest group-hover:text-black">Activos</span>
                                    </div>
                                    <svg class="w-5 h-5 text-zinc-700 group-hover:text-black transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                    </svg>
                                </div>
                                <p class="text-7xl font-black tracking-tighter text-white group-hover:text-black transition-colors">{{ stats.approved_photographers }}</p>
                                <p class="font-mono text-[10px] text-zinc-600 uppercase mt-2 group-hover:text-zinc-500">Operando normalmente</p>
                            </Link>

                            <Link :href="route('admin.photographers.index', { status: 'rejected' })"
                                class="group relative bg-zinc-950 p-6 border border-zinc-800 hover:border-red-600 hover:bg-red-600 transition-all duration-300">
                                <div class="flex justify-between items-start mb-6">
                                    <div class="flex items-center gap-3">
                                        <span class="w-3 h-3 bg-red-600 group-hover:bg-black transition-colors"></span>
                                        <span class="font-mono text-xs font-bold text-zinc-400 uppercase tracking-widest group-hover:text-black transition-colors">Rechazados</span>
                                    </div>
                                    <svg class="w-5 h-5 text-zinc-700 group-hover:text-black transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                    </svg>
                                </div>
                                <p class="text-7xl font-black tracking-tighter text-white group-hover:text-black transition-colors">{{ stats.rejected_photographers }}</p>
                                <p class="font-mono text-[10px] text-zinc-600 uppercase mt-2 group-hover:text-red-950 transition-colors">Histórico</p>
                            </Link>

                            <Link :href="route('admin.photographers.index', { status: 'suspended' })"
                                class="group relative bg-zinc-950 p-6 border border-zinc-800 hover:border-white hover:bg-white transition-all duration-300">
                                <div class="flex justify-between items-start mb-6">
                                    <div class="flex items-center gap-3">
                                        <span class="w-3 h-3 bg-orange-500 shadow-[0_0_10px_rgba(249,115,22,0.5)]"></span>
                                        <span class="font-mono text-xs font-bold text-zinc-400 uppercase tracking-widest group-hover:text-black">Suspendidos</span>
                                    </div>
                                    <svg class="w-5 h-5 text-zinc-700 group-hover:text-black transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                    </svg>
                                </div>
                                <p class="text-7xl font-black tracking-tighter text-white group-hover:text-black transition-colors">{{ stats.suspended_photographers }}</p>
                                <p class="font-mono text-[10px] text-zinc-600 uppercase mt-2 group-hover:text-zinc-500">Acceso revocado</p>
                            </Link>
                        </div>
                    </div>

                    <div class="lg:col-span-1">
                        <div class="bg-black border-l-4 md:border-l-0 md:border-t-4 border-red-600 pt-6 h-full flex flex-col">
                            <h3 class="font-mono text-sm font-bold uppercase tracking-widest text-red-600 mb-6 flex items-center gap-2">
                                <span class="w-2 h-2 bg-red-600"></span> Accesos Directos
                            </h3>

                            <div class="space-y-3 flex-1">
                                <Link :href="route('admin.photographers.index')"
                                    class="group flex items-center gap-4 p-4 bg-zinc-950 border border-zinc-900 hover:bg-white hover:border-white transition-colors">
                                    <div class="text-zinc-600 group-hover:text-black transition-colors">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                        </svg>
                                    </div>
                                    <div>
                                        <span class="font-sans text-lg font-black uppercase text-white group-hover:text-black leading-none block transition-colors">Gestión Usuarios</span>
                                        <span class="font-mono text-[10px] uppercase text-zinc-500 group-hover:text-zinc-500 mt-1 block">Base de datos completa</span>
                                    </div>
                                </Link>

                                <Link :href="route('admin.photographers.index', { status: 'pending' })"
                                    class="group flex items-center gap-4 p-4 bg-zinc-950 border border-zinc-900 hover:bg-white hover:border-white transition-colors">
                                    <div class="text-zinc-600 group-hover:text-black transition-colors">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </div>
                                    <div>
                                        <span class="font-sans text-lg font-black uppercase text-white group-hover:text-black leading-none block transition-colors">Aprobaciones</span>
                                        <span class="font-mono text-[10px] uppercase text-zinc-500 group-hover:text-zinc-500 mt-1 block">{{ stats.pending_photographers }} pendientes</span>
                                    </div>
                                </Link>

                                <Link :href="route('admin.messages.index')"
                                    class="group flex items-center gap-4 p-4 bg-zinc-950 border border-zinc-900 hover:bg-white hover:border-white transition-colors">
                                    <div class="text-zinc-600 group-hover:text-black transition-colors">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                        </svg>
                                    </div>
                                    <div>
                                        <span class="font-sans text-lg font-black uppercase text-white group-hover:text-black leading-none block transition-colors">Mensajes</span>
                                        <span class="font-mono text-[10px] uppercase text-zinc-500 group-hover:text-zinc-500 mt-1 block">{{ stats.unread_messages }} sin leer</span>
                                    </div>
                                </Link>

                                <Link :href="route('home')"
                                    class="group flex items-center gap-4 p-4 mt-6 bg-red-600 border border-red-600 hover:bg-white hover:border-white transition-colors">
                                    <div class="text-black group-hover:text-red-600 transition-colors">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                        </svg>
                                    </div>
                                    <div>
                                        <span class="font-sans text-lg font-black uppercase text-black leading-none block transition-colors">Ir al Sitio Público</span>
                                        <span class="font-mono text-[10px] uppercase text-black/70 mt-1 block">Live Market</span>
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