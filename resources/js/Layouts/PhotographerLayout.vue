<template>
    <div class="min-h-screen bg-gray-50">
        <!-- Navbar -->
        <nav class="bg-white shadow-lg border-b-4 border-indigo-600">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <!-- Logo y Navegación -->
                    <div class="flex items-center space-x-8">
                        <Link :href="route('photographer.dashboard')" class="flex items-center">
                            <span class="text-2xl font-bold text-indigo-600">📸 Panel Fotógrafo</span>
                        </Link>

                        <div class="hidden md:flex space-x-6">
                            <Link 
                                :href="route('photographer.dashboard')"
                                :class="[
                                    'flex items-center px-3 py-2 rounded-lg transition',
                                    route().current('photographer.dashboard') 
                                        ? 'bg-indigo-100 text-indigo-700 font-semibold' 
                                        : 'text-gray-700 hover:bg-gray-100'
                                ]"
                            >
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                                </svg>
                                Dashboard
                            </Link>

                            <Link 
                                :href="route('photographer.photos.index')"
                                :class="[
                                    'flex items-center px-3 py-2 rounded-lg transition',
                                    route().current('photographer.photos.*') 
                                        ? 'bg-indigo-100 text-indigo-700 font-semibold' 
                                        : 'text-gray-700 hover:bg-gray-100'
                                ]"
                            >
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                Mis Fotos
                            </Link>

                            <Link 
                                :href="route('photographer.events.index')"
                                :class="[
                                    'flex items-center px-3 py-2 rounded-lg transition',
                                    route().current('photographer.events.*') 
                                        ? 'bg-indigo-100 text-indigo-700 font-semibold' 
                                        : 'text-gray-700 hover:bg-gray-100'
                                ]"
                            >
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                Eventos
                            </Link>
                        </div>
                    </div>

                    <!-- Usuario -->
                    <div class="flex items-center space-x-4">
                        <Link 
                            :href="route('home')"
                            class="text-gray-700 hover:text-indigo-600 transition"
                            target="_blank"
                        >
                            Ver Sitio Público
                        </Link>

                        <div class="relative" x-data="{ open: false }">
                            <button 
                                @click="open = !open"
                                class="flex items-center space-x-3 focus:outline-none"
                            >
                                <img 
                                    v-if="$page.props.auth.user.photographer.profile_photo_url"
                                    :src="$page.props.auth.user.photographer.profile_photo_url" 
                                    class="w-10 h-10 rounded-full object-cover border-2 border-indigo-600"
                                    :alt="$page.props.auth.user.name"
                                >
                                <div 
                                    v-else
                                    class="w-10 h-10 rounded-full bg-indigo-600 flex items-center justify-center text-white font-bold border-2 border-indigo-700"
                                >
                                    {{ $page.props.auth.user.name.charAt(0).toUpperCase() }}
                                </div>
                                <div class="hidden md:block text-left">
                                    <p class="text-sm font-semibold text-gray-900">{{ $page.props.auth.user.name }}</p>
                                    <p class="text-xs text-gray-500">{{ $page.props.auth.user.photographer.business_name }}</p>
                                </div>
                                <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>

                            <div 
                                x-show="open" 
                                @click.away="open = false"
                                x-transition
                                class="absolute right-0 mt-2 w-56 bg-white rounded-lg shadow-xl py-2 z-50 border"
                            >
                                <Link 
                                    :href="route('photographer.profile')"
                                    class="block px-4 py-2 text-sm text-gray-700 hover:bg-indigo-50 transition"
                                >
                                    <svg class="inline w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                    Mi Perfil
                                </Link>
                                <Link 
                                    :href="route('profile.edit')"
                                    class="block px-4 py-2 text-sm text-gray-700 hover:bg-indigo-50 transition"
                                >
                                    <svg class="inline w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                    Configuración
                                </Link>
                                <hr class="my-2">
                                                              <Link 
                                    :href="route('logout')" 
                                    method="post" 
                                    as="button"
                                    class="block w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50 transition"
                                >
                                    <svg class="inline w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                    </svg>
                                    Cerrar Sesión
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Contenido Principal -->
        <main>
            <slot />
        </main>
    </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
</script>
