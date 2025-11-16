<template>
    <div class="min-h-screen bg-gray-50">
        <!-- Navbar -->
        <nav class="bg-white shadow-lg">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <!-- Logo -->
                    <div class="flex items-center">
                        <Link :href="route('home')" class="flex items-center">
                            <span class="text-2xl font-bold text-indigo-600">📸 Verify</span>
                        </Link>
                    </div>

                    <!-- Navigation Links -->
                    <div class="hidden md:flex items-center space-x-8">
                        <Link 
                            :href="route('gallery.index')" 
                            class="text-gray-700 hover:text-indigo-600 transition"
                        >
                            Galería
                        </Link>
                        <Link 
                            :href="route('events.index')" 
                            class="text-gray-700 hover:text-indigo-600 transition"
                        >
                            Eventos
                        </Link>

                        <!-- Usuario autenticado -->
                        <template v-if="$page.props.auth.user">
                            <Link 
                                v-if="$page.props.auth.user.role === 'photographer'"
                                :href="route('photographer.dashboard')" 
                                class="text-indigo-600 font-semibold"
                            >
                                Dashboard
                            </Link>
                            
                            <div class="relative" x-data="{ open: false }">
                                <button 
                                    @click="open = !open"
                                    class="flex items-center space-x-2 text-gray-700 hover:text-indigo-600"
                                >
                                    <span>{{ $page.props.auth.user.name }}</span>
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </button>

                                <div 
                                    x-show="open" 
                                    @click.away="open = false"
                                    class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-50"
                                >
                                    <Link 
                                        :href="route('profile.edit')" 
                                        class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                                    >
                                        Perfil
                                    </Link>
                                    <Link 
                                        :href="route('logout')" 
                                        method="post" 
                                        as="button"
                                        class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                                    >
                                        Cerrar Sesión
                                    </Link>
                                </div>
                            </div>
                        </template>

                        <!-- Usuario no autenticado -->
                        <template v-else>
                            <Link 
                                :href="route('login')" 
                                class="text-gray-700 hover:text-indigo-600 transition"
                            >
                                Iniciar Sesión
                            </Link>
                            <Link 
                                :href="route('register')" 
                                class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 transition"
                            >
                                Registrarse
                            </Link>
                        </template>
                    </div>

                    <!-- Mobile menu button -->
                    <div class="md:hidden flex items-center">
                        <button 
                            @click="mobileMenuOpen = !mobileMenuOpen"
                            class="text-gray-700 hover:text-indigo-600"
                        >
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Mobile Menu -->
            <div v-if="mobileMenuOpen" class="md:hidden bg-white border-t">
                <div class="px-2 pt-2 pb-3 space-y-1">
                    <Link 
                        :href="route('gallery.index')" 
                        class="block px-3 py-2 text-gray-700 hover:bg-gray-100 rounded"
                    >
                        Galería
                    </Link>
                    <Link 
                        :href="route('events.index')" 
                        class="block px-3 py-2 text-gray-700 hover:bg-gray-100 rounded"
                    >
                        Eventos
                    </Link>
                </div>
            </div>
        </nav>

        <!-- Slot para contenido -->
        <main>
            <slot />
        </main>

        <!-- Footer -->
        <footer class="bg-gray-800 text-white mt-20">
            <div class="max-w-7xl mx-auto px-4 py-8">
                <div class="text-center">
                    <p>© 2024 Verify. Todos los derechos reservados.</p>
                </div>
            </div>
        </footer>
    </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import { ref } from 'vue';

const mobileMenuOpen = ref(false);
</script>
