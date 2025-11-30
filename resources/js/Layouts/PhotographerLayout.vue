<script setup>
import { ref, computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';

const showingNavigationDropdown = ref(false);

const page = usePage();
const user = computed(() => page.props.auth.user);

// Determinar la ruta del dashboard según el rol
const dashboardRoute = computed(() => {
    if (user.value.role === 'photographer') {
        return route('photographer.dashboard');
    } else if (user.value.role === 'admin') {
        return route('admin.dashboard');
    } else {
        return route('home');
    }
});
</script>

<template>
    <div class="min-h-screen bg-gray-50">
        <!-- Navigation para Fotógrafos -->
        <nav class="bg-white border-b border-gray-200 sticky top-0 z-50 shadow-sm">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center h-16">

                    <!-- Logo -->
                    <Link :href="dashboardRoute" class="flex items-center space-x-3 group">
                    <div
                        class="w-10 h-10 bg-gradient-to-br from-purple-600 to-indigo-600 rounded-xl flex items-center justify-center transition-all group-hover:from-purple-700 group-hover:to-indigo-700">
                        <svg class="w-6 h-6 text-white transition-transform group-hover:scale-110" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <div>
                        <span class="text-xl font-bold text-gray-900 block leading-none">Empresa</span>
                        <span class="text-xs text-purple-600 font-medium">Panel {{ user.role === 'photographer' ?
                            'Fotógrafo' : user.role === 'admin' ? 'Admin' : 'Usuario' }}</span>
                    </div>
                    </Link>

                    <!-- Menu Principal (Desktop) -->
                    <div class="hidden md:flex items-center space-x-1">
                        <!-- Dashboard -->
                        <Link :href="dashboardRoute" :class="[
                            'px-4 py-2 rounded-lg font-medium transition-all flex items-center space-x-2',
                            ($page.url.startsWith('/fotografo/dashboard') || $page.url.startsWith('/admin/panel'))
                                ? 'bg-purple-50 text-purple-600'
                                : 'text-gray-700 hover:bg-gray-100 hover:text-purple-600'
                        ]">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                        </svg>
                        <span>Dashboard</span>
                        </Link>

                        <!-- Eventos (Solo Fotógrafo) -->
                        <Link v-if="user.role === 'photographer'" :href="route('photographer.events.index')" :class="[
                            'px-4 py-2 rounded-lg font-medium transition-all flex items-center space-x-2',
                            $page.url.includes('/eventos')
                                ? 'bg-purple-50 text-purple-600'
                                : 'text-gray-700 hover:bg-gray-100 hover:text-purple-600'
                        ]">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        <span>Eventos</span>
                        </Link>

                        <!-- Fotos (Solo Fotógrafo) -->
                        <Link v-if="user.role === 'photographer'" :href="route('photographer.photos.index')" :class="[
                            'px-4 py-2 rounded-lg font-medium transition-all flex items-center space-x-2',
                            $page.url.includes('/fotos')
                                ? 'bg-purple-50 text-purple-600'
                                : 'text-gray-700 hover:bg-gray-100 hover:text-purple-600'
                        ]">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        <span>Fotos</span>
                        </Link>

                        <!-- Perfil -->
                        <Link :href="route('profile.edit')" :class="[
                            'px-4 py-2 rounded-lg font-medium transition-all flex items-center space-x-2',
                            $page.url.includes('/profile')
                                ? 'bg-purple-50 text-purple-600'
                                : 'text-gray-700 hover:bg-gray-100 hover:text-purple-600'
                        ]">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                        <span>Perfil</span>
                        </Link>
                    </div>

                    <!-- Right Side -->
                    <div class="hidden md:flex items-center space-x-3">
                        <!-- Botón Ver Sitio Público -->
                        <Link href="/"
                            class="px-4 py-2 text-gray-600 hover:text-gray-900 font-medium transition-all flex items-center space-x-2 border border-gray-300 rounded-lg hover:border-gray-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span class="text-sm">Ver Sitio</span>
                        </Link>

                        <!-- User Dropdown -->
                        <div class="relative">
                            <button @click="showingNavigationDropdown = !showingNavigationDropdown"
                                class="flex items-center space-x-3 px-3 py-2 rounded-lg hover:bg-gray-100 transition-all">
                                <div
                                    class="w-9 h-9 bg-gradient-to-br from-purple-600 to-indigo-600 rounded-full flex items-center justify-center">
                                    <span class="text-white font-semibold text-sm">
                                        {{ user.name.charAt(0).toUpperCase() }}
                                    </span>
                                </div>
                                <div class="text-left">
                                    <div class="text-sm font-semibold text-gray-900">{{ user.name }}</div>
                                    <div class="text-xs text-gray-500 capitalize">{{ user.role }}</div>
                                </div>
                                <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>

                            <!-- Dropdown Menu -->
                            <div v-show="showingNavigationDropdown" @click="showingNavigationDropdown = false"
                                class="absolute right-0 mt-2 w-56 bg-white rounded-xl shadow-lg border border-gray-200 z-50">
                                <div class="p-3 border-b border-gray-100">
                                    <p class="text-sm font-semibold text-gray-900">{{ user.name }}</p>
                                    <p class="text-xs text-gray-500">{{ user.email }}</p>
                                </div>

                                <div class="py-2">
                                    <Link :href="route('profile.edit')"
                                        class="flex items-center space-x-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 transition">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                    <span>Configuración</span>
                                    </Link>
                                </div>

                                <div class="border-t border-gray-100 py-2">
                                    <Link :href="route('logout')" method="post" as="button"
                                        class="flex items-center space-x-2 w-full px-4 py-2 text-sm text-red-600 hover:bg-red-50 transition">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                    </svg>
                                    <span>Cerrar Sesión</span>
                                    </Link>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Mobile menu button -->
                    <div class="md:hidden">
                        <button @click="showingNavigationDropdown = !showingNavigationDropdown"
                            class="p-2 rounded-lg text-gray-700 hover:bg-gray-100 transition-colors">
                            <svg v-if="!showingNavigationDropdown" class="w-6 h-6" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                            <svg v-else class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Mobile Menu -->
            <div v-show="showingNavigationDropdown" class="md:hidden border-t border-gray-200 bg-white">
                <div class="px-4 py-4 space-y-2">
                    <!-- User Info -->
                    <div class="flex items-center space-x-3 px-4 py-3 bg-gray-50 rounded-lg mb-4">
                        <div
                            class="w-10 h-10 bg-gradient-to-br from-purple-600 to-indigo-600 rounded-full flex items-center justify-center">
                            <span class="text-white font-semibold">
                                {{ user.name.charAt(0).toUpperCase() }}
                            </span>
                        </div>
                        <div>
                            <div class="text-sm font-semibold text-gray-900">{{ user.name }}</div>
                            <div class="text-xs text-gray-500 capitalize">{{ user.role }}</div>
                        </div>
                    </div>

                    <Link :href="dashboardRoute" :class="[
                        'flex items-center space-x-3 px-4 py-3 rounded-lg font-medium transition-all',
                        ($page.url.startsWith('/fotografo/dashboard') || $page.url.startsWith('/admin/panel'))
                            ? 'bg-purple-50 text-purple-600'
                            : 'text-gray-700 hover:bg-gray-100'
                    ]">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                    <span>Dashboard</span>
                    </Link>

                    <Link v-if="user.role === 'photographer'" :href="route('photographer.events.index')" :class="[
                        'flex items-center space-x-3 px-4 py-3 rounded-lg font-medium transition-all',
                        $page.url.includes('/eventos')
                            ? 'bg-purple-50 text-purple-600'
                            : 'text-gray-700 hover:bg-gray-100'
                    ]">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    <span>Eventos</span>
                    </Link>

                    <Link v-if="user.role === 'photographer'" :href="route('photographer.photos.index')" :class="[
                        'flex items-center space-x-3 px-4 py-3 rounded-lg font-medium transition-all',
                        $page.url.includes('/fotos')
                            ? 'bg-purple-50 text-purple-600'
                            : 'text-gray-700 hover:bg-gray-100'
                    ]">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    <span>Fotos</span>
                    </Link>

                    <Link :href="route('profile.edit')" :class="[
                        'flex items-center space-x-3 px-4 py-3 rounded-lg font-medium transition-all',
                        $page.url.includes('/profile')
                            ? 'bg-purple-50 text-purple-600'
                            : 'text-gray-700 hover:bg-gray-100'
                    ]">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                    <span>Perfil</span>
                    </Link>

                    <div class="pt-4 border-t border-gray-200 space-y-2">
                        <Link href="/"
                            class="flex items-center space-x-3 px-4 py-3 rounded-lg font-medium text-gray-700 hover:bg-gray-100 transition-all">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span>Ver Sitio Público</span>
                        </Link>

                        <Link :href="route('logout')" method="post" as="button"
                            class="flex items-center space-x-3 w-full px-4 py-3 rounded-lg font-medium text-red-600 hover:bg-red-50 transition-all">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                        </svg>
                        <span>Cerrar Sesión</span>
                        </Link>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Page Heading -->
        <header v-if="$slots.header" class="bg-white shadow-sm border-b border-gray-200">
            <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                <slot name="header" />
            </div>
        </header>

        <!-- Page Content -->
        <main class="py-6">
            <slot />
        </main>

        <!-- Footer Simple -->
        <footer class="bg-white border-t border-gray-200 py-8 mt-12">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center text-gray-500 text-sm">
                    © {{ new Date().getFullYear() }} Empresa. Panel de {{ user.role === 'photographer' ? 'Fotógrafo' :
                        user.role === 'admin' ? 'Administrador' : 'Usuario' }}.
                </div>
            </div>
        </footer>
    </div>
</template>
