<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { HomeIcon, MagnifyingGlassIcon, ArrowLeftIcon } from '@heroicons/vue/24/outline';
</script>

<template>
    <Head title="Página no encontrada" />

    <AppLayout>
        <div class="min-h-screen bg-gradient-to-br from-slate-50 to-slate-100 flex items-center justify-center px-4 py-16">
            <div class="max-w-2xl w-full text-center">
                
                <!-- Error 404 -->
                <div class="mb-8">
                    <h1 class="text-9xl font-serif font-bold text-slate-900 mb-4">
                        404
                    </h1>
                    <div class="w-24 h-1 bg-slate-900 mx-auto mb-6"></div>
                    <h2 class="text-3xl font-serif font-bold text-slate-900 mb-4">
                        Página no encontrada
                    </h2>
                    <p class="text-lg text-slate-600 mb-8">
                        Lo sentimos, la página que estás buscando no existe o fue movida.
                    </p>
                </div>

                <!-- Ilustración o imagen -->
                <div class="mb-12">
                    <svg class="w-64 h-64 mx-auto text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" 
                              d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>

                <!-- Acciones -->
                <div class="flex flex-col sm:flex-row gap-4 justify-center items-center">
                    <Link 
                        :href="route('home')"
                        class="inline-flex items-center gap-2 bg-slate-900 text-white font-medium px-8 py-4 rounded-lg hover:bg-slate-800 transition-all shadow-lg hover:shadow-xl"
                    >
                        <HomeIcon class="w-5 h-5" />
                        <span>Volver al inicio</span>
                    </Link>

                    <Link 
                        :href="route('events.index')"
                        class="inline-flex items-center gap-2 bg-white text-slate-900 font-medium px-8 py-4 rounded-lg hover:bg-slate-50 transition-all shadow-md hover:shadow-lg border border-slate-200"
                    >
                        <MagnifyingGlassIcon class="w-5 h-5" />
                        <span>Ver eventos</span>
                    </Link>

                    <button 
                        @click="$router.back()"
                        class="inline-flex items-center gap-2 text-slate-600 hover:text-slate-900 font-medium px-6 py-4 rounded-lg hover:bg-slate-100 transition-all"
                    >
                        <ArrowLeftIcon class="w-5 h-5" />
                        <span>Volver atrás</span>
                    </button>
                </div>

                <!-- Sugerencias -->
                <div class="mt-16 pt-8 border-t border-slate-200">
                    <p class="text-sm text-slate-500 mb-4">
                        ¿Necesitas ayuda? Prueba con:
                    </p>
                    <div class="flex flex-wrap gap-3 justify-center">
                        <Link 
                            :href="route('events.index')"
                            class="text-sm text-slate-600 hover:text-slate-900 hover:underline"
                        >
                            Eventos vigentes
                        </Link>
                        <span class="text-slate-300">•</span>
                        <Link 
                            :href="route('gallery.index')"
                            class="text-sm text-slate-600 hover:text-slate-900 hover:underline"
                        >
                            Galería completa
                        </Link>
                        <span class="text-slate-300">•</span>
                        <Link 
                            :href="route('photographers.index')"
                            class="text-sm text-slate-600 hover:text-slate-900 hover:underline"
                        >
                            Fotógrafos
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
