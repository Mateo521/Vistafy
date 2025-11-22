<template>
    <div class="min-h-screen bg-gray-50">
        <!-- Navbar Público -->
        <nav class="bg-white shadow-lg sticky top-0 z-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <!-- Logo -->
                    <div class="flex items-center">
                        <Link :href="route('home')" class="flex items-center">
                            <span class="text-2xl font-bold text-indigo-600"> FotoGalería</span>
                        </Link>

                        <!-- Navegación principal -->
                        <div class="hidden md:flex ml-10 space-x-6">
                            <Link 
                                :href="route('home')"
                                :class="[
                                    'px-3 py-2 rounded-lg transition font-medium',
                                    route().current('home') 
                                        ? 'bg-indigo-100 text-indigo-700' 
                                        : 'text-gray-700 hover:bg-gray-100'
                                ]"
                            >
                                Inicio
                            </Link>
                            <Link 
                                :href="route('gallery.index')"
                                :class="[
                                    'px-3 py-2 rounded-lg transition font-medium',
                                    route().current('gallery.*') 
                                        ? 'bg-indigo-100 text-indigo-700' 
                                        : 'text-gray-700 hover:bg-gray-100'
                                ]"
                            >
                                Galería
                            </Link>
                            <Link 
                                :href="route('events.index')"
                                :class="[
                                    'px-3 py-2 rounded-lg transition font-medium',
                                    route().current('events.*') 
                                        ? 'bg-indigo-100 text-indigo-700' 
                                        : 'text-gray-700 hover:bg-gray-100'
                                ]"
                            >
                                Eventos
                            </Link>
                        </div>
                    </div>

                    <!-- Usuario / Login -->
                    <div class="flex items-center space-x-4">
                        <template v-if="$page.props.auth.user">
                            <!-- Usuario autenticado -->
                            <div class="relative" x-data="{ open: false }">
                                <button 
                                    @click="open = !open"
                                    class="flex items-center space-x-3 focus:outline-none"
                                >
                                    <img 
                                        v-if="$page.props.auth.user.photographer?.profile_photo_url"
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
                                    <span class="hidden md:block text-sm font-semibold text-gray-900">
                                        {{ $page.props.auth.user.name }}
                                    </span>
                                    <svg class="w-4 h-4 text-gray-500 transition-transform" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </button>

                                <!-- Dropdown -->
                                <div 
                                    x-show="open" 
                                    @click.away="open = false"
                                    x-transition:enter="transition ease-out duration-100"
                                    x-transition:enter-start="transform opacity-0 scale-95"
                                    x-transition:enter-end="transform opacity-100 scale-100"
                                    x-transition:leave="transition ease-in duration-75"
                                    x-transition:leave-start="transform opacity-100 scale-100"
                                    x-transition:leave-end="transform opacity-0 scale-95"
                                    class="absolute right-0 mt-2 w-56 bg-white rounded-lg shadow-xl py-2 z-50 border"
                                    style="display: none;"
                                >
                                    <!-- Si es fotógrafo -->
                                    <template v-if="$page.props.auth.user.role === 'photographer'">
                                        <Link 
                                            :href="route('photographer.dashboard')"
                                            class="block px-4 py-2 text-sm text-gray-700 hover:bg-indigo-50 transition"
                                        >
                                            <svg class="inline w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                                            </svg>
                                            Panel de Fotógrafo
                                        </Link>
                                        <Link 
                                            :href="route('photographer.profile')"
                                            class="block px-4 py-2 text-sm text-gray-700 hover:bg-indigo-50 transition"
                                        >
                                            <svg class="inline w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                            </svg>
                                            Mi Perfil
                                        </Link>
                                    </template>
                                    
                                    <!-- Para todos -->
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
                        </template>

                        <!-- No autenticado -->
                        <template v-else>
                            <Link 
                                :href="route('login')"
                                class="text-gray-700 hover:text-indigo-600 font-semibold transition"
                            >
                                Iniciar Sesión
                            </Link>
                            <Link 
                                :href="route('register')"
                                class="bg-indigo-600 text-white px-6 py-2 rounded-lg hover:bg-indigo-700 transition font-semibold"
                            >
                                Registrarse
                            </Link>
                        </template>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Contenido Principal -->
        <main>
            <slot />
        </main>

        <!-- Footer -->
        <footer class="bg-gray-900 text-white mt-20">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                    <div>
                        <h3 class="text-2xl font-bold mb-4"> FotoGalería</h3>
                        <p class="text-gray-400">Encuentra tus mejores momentos capturados en alta calidad.</p>
                    </div>
                    <div>
                        <h4 class="font-bold mb-4">Explorar</h4>
                        <ul class="space-y-2 text-gray-400">
                            <li><Link :href="route('gallery.index')" class="hover:text-white">Galería</Link></li>
                            <li><Link :href="route('events.index')" class="hover:text-white">Eventos</Link></li>
                        </ul>
                    </div>
                    <div>
                        <h4 class="font-bold mb-4">Fotógrafos</h4>
                        <ul class="space-y-2 text-gray-400">
                            <li><Link :href="route('login')" class="hover:text-white">Iniciar Sesión</Link></li>
                            <li><Link :href="route('register')" class="hover:text-white">Registrarse</Link></li>
                        </ul>
                    </div>
                    <div>
                        <h4 class="font-bold mb-4">Contacto</h4>
                        <ul class="space-y-2 text-gray-400">
                            <li> info@fotogaleria.com</li>
                            <li>📱 +1 234 567 8900</li>
                        </ul>
                    </div>
                </div>
                <div class="border-t border-gray-800 mt-8 pt-8 text-center text-gray-400">
                    <p>© 2025 FotoGalería. Todos los derechos reservados.</p>
                </div>
            </div>
        </footer>
    </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
</script>
