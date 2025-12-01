<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import { computed, ref, onMounted, onUnmounted } from 'vue';

const page = usePage();
const auth = computed(() => page.props.auth);
const user = computed(() => auth.value?.user);
const mobileMenuOpen = ref(false);

// Detectar si estamos en la página de inicio
const isHomePage = computed(() => page.url === '/');

// NUEVO: Detectar scroll
const scrolled = ref(false);

const handleScroll = () => {
    scrolled.value = window.scrollY > 50;
};

onMounted(() => {
    window.addEventListener('scroll', handleScroll);
});

onUnmounted(() => {
    window.removeEventListener('scroll', handleScroll);
});

//  NUEVO: Helpers para roles
const isAdmin = computed(() => user.value?.is_admin === true);
const isPhotographer = computed(() => user.value?.role === 'photographer');
const isClient = computed(() => user.value?.role === 'client');

//  NUEVO: Ruta dinámica del dashboard según el rol
const dashboardRoute = computed(() => {
    if (!user.value) return null;
    
    if (isAdmin.value) {
        return route('admin.dashboard');
    }
    
    if (isPhotographer.value) {
        return route('photographer.dashboard');
    }
    
    // Para clientes, ir al home
    return route('home');
});

//  NUEVO: Texto dinámico del botón
const dashboardText = computed(() => {
    if (!user.value) return 'Dashboard';
    
    if (isAdmin.value) {
        return ' Panel Admin';
    }
    
    if (isPhotographer.value) {
        return ' Mi Panel';
    }
    
    return 'Mi Cuenta';
});
</script>

<template>
    <div class="min-h-screen bg-gray-50">
        <!-- Navigation Unificada -->

        <nav :class="[
            'fixed top-0 w-full z-50 transition-all duration-300',
            // CAMBIADO: Agregar fondo cuando se hace scroll en home
            isHomePage
                ? (scrolled ? 'bg-white shadow-lg' : 'bg-transparent')
                : 'bg-white shadow-sm'
        ]">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center h-20">

                    <!-- Logo -->
                    <Link href="/" class="flex items-center space-x-3 group">
                    <div :class="[
                        'w-12 h-12 rounded-xl flex items-center justify-center transition-all',
                        // CAMBIADO: Ajustar según scroll
                        isHomePage
                            ? (scrolled
                                ? 'bg-gradient-to-br from-purple-600 to-indigo-600 group-hover:from-purple-700 group-hover:to-indigo-700'
                                : 'bg-white/10 backdrop-blur-md border border-white/20 group-hover:bg-white/20'
                            )
                            : 'bg-gradient-to-br from-purple-600 to-indigo-600 group-hover:from-purple-700 group-hover:to-indigo-700'
                    ]">
                        <svg class="w-7 h-7 transition-transform group-hover:scale-110 text-white" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <span :class="[
                        'text-2xl font-bold transition-colors',
                        // CAMBIADO: Ajustar color según scroll
                        isHomePage
                            ? (scrolled ? 'text-gray-900' : 'text-white drop-shadow-lg')
                            : 'text-gray-900'
                    ]">
                        Empresa
                    </span>
                    </Link>

                    <!-- Menu Principal (Desktop) -->
                    <div class="hidden md:flex items-center space-x-2">
                        <Link href="/" :class="[
                            'px-4 py-2 rounded-lg font-medium transition-all',
                            $page.url === '/'
                                ? (isHomePage && !scrolled ? 'bg-white/20 text-white backdrop-blur-md' : 'bg-purple-50 text-purple-600')
                                : (isHomePage && !scrolled ? 'text-white/90 hover:text-white hover:bg-white/10' : 'text-gray-700 hover:bg-gray-100 hover:text-purple-600')
                        ]">
                        Inicio
                        </Link>

                        <Link :href="route('events.index')" :class="[
                            'px-4 py-2 rounded-lg font-medium transition-all',
                            $page.url.startsWith('/eventos')
                                ? (isHomePage && !scrolled ? 'bg-white/20 text-white backdrop-blur-md' : 'bg-purple-50 text-purple-600')
                                : (isHomePage && !scrolled ? 'text-white/90 hover:text-white hover:bg-white/10' : 'text-gray-700 hover:bg-gray-100 hover:text-purple-600')
                        ]">
                        Eventos
                        </Link>

                        <Link :href="route('gallery.index')" :class="[
                            'px-4 py-2 rounded-lg font-medium transition-all',
                            $page.url.startsWith('/galeria')
                                ? (isHomePage && !scrolled ? 'bg-white/20 text-white backdrop-blur-md' : 'bg-purple-50 text-purple-600')
                                : (isHomePage && !scrolled ? 'text-white/90 hover:text-white hover:bg-white/10' : 'text-gray-700 hover:bg-gray-100 hover:text-purple-600')
                        ]">
                        Galería
                        </Link>

                        <Link :href="route('photographers.index')" :class="[
                            'px-4 py-2 rounded-lg font-medium transition-all',
                            $page.url.startsWith('/fotografos')
                                ? (isHomePage && !scrolled ? 'bg-white/20 text-white backdrop-blur-md' : 'bg-purple-50 text-purple-600')
                                : (isHomePage && !scrolled ? 'text-white/90 hover:text-white hover:bg-white/10' : 'text-gray-700 hover:bg-gray-100 hover:text-purple-600')
                        ]">
                        Fotógrafos
                        </Link>
                    </div>


                    <!-- Auth Buttons -->
                    <div class="hidden md:flex items-center space-x-4">
                        <template v-if="!user">
                            <Link :href="route('login')" :class="[
                                'px-5 py-2.5 font-medium transition-all',
                                isHomePage && !scrolled
                                    ? 'text-white/90 hover:text-white'
                                    : 'text-gray-700 hover:text-purple-600'
                            ]">
                            Iniciar Sesión
                            </Link>

                            <Link :href="route('register')" :class="[
                                'px-6 py-2.5 rounded-lg font-semibold transition-all shadow-lg hover:shadow-xl transform hover:scale-105',
                                isHomePage && !scrolled
                                    ? 'bg-white/20 backdrop-blur-md text-white border border-white/30 hover:bg-white/30'
                                    : 'bg-gradient-to-r from-purple-600 to-indigo-600 text-white hover:from-purple-700 hover:to-indigo-700'
                            ]">
                            Registrarse
                            </Link>

                            
                            <Link :href="route('photographer.register')" :class="[
                                'px-6 py-2.5 rounded-lg font-semibold transition-all border-2',
                                isHomePage && !scrolled
                                    ? 'border-white/50 text-white hover:bg-white/10 backdrop-blur-md'
                                    : 'border-purple-600 text-purple-600 hover:bg-purple-600 hover:text-white'
                            ]">
                             Soy fotógrafo
                            </Link>
                        </template>

                       
                        <template v-else>
                            <Link 
                                v-if="dashboardRoute"
                                :href="dashboardRoute" 
                                :class="[
                                    'px-5 py-2.5 font-medium transition-all rounded-lg',
                                    isHomePage && !scrolled
                                        ? 'text-white/90 hover:text-white hover:bg-white/10'
                                        : 'text-gray-700 hover:text-purple-600 hover:bg-purple-50'
                                ]"
                            >
                                {{ dashboardText }}
                            </Link>

                            <Link :href="route('logout')" method="post" as="button" :class="[
                                'px-5 py-2.5 font-medium transition-all rounded-lg',
                                isHomePage && !scrolled
                                    ? 'text-white/90 hover:text-white hover:bg-red-500/20'
                                    : 'text-gray-700 hover:text-red-600 hover:bg-red-50'
                            ]">
                            Cerrar Sesión
                            </Link>
                        </template>
                    </div>


                    <!-- Mobile menu button -->
                    <div class="md:hidden">
                        <button @click="mobileMenuOpen = !mobileMenuOpen" :class="[
                            'p-2 rounded-lg transition-colors',
                            isHomePage && !scrolled
                                ? 'text-white hover:bg-white/10'
                                : 'text-gray-700 hover:bg-gray-100'
                        ]">
                            <svg v-if="!mobileMenuOpen" class="w-6 h-6" fill="none" stroke="currentColor"
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
            <div v-show="mobileMenuOpen" class="md:hidden border-t"
                :class="isHomePage ? 'bg-black/90 backdrop-blur-lg border-white/10' : 'bg-white border-gray-200'">
                <div class="px-4 py-4 space-y-2">
                    <Link href="/" :class="[
                        'block px-4 py-3 rounded-lg font-medium transition-all',
                        $page.url === '/'
                            ? 'bg-white/20 text-white'
                            : (isHomePage ? 'text-white/90 hover:bg-white/10' : 'text-gray-700 hover:bg-gray-100')
                    ]">
                    Inicio
                    </Link>

                    <Link :href="route('events.index')" :class="[
                        'block px-4 py-3 rounded-lg font-medium transition-all',
                        $page.url.startsWith('/eventos')
                            ? 'bg-white/20 text-white'
                            : (isHomePage ? 'text-white/90 hover:bg-white/10' : 'text-gray-700 hover:bg-gray-100')
                    ]">
                    Eventos
                    </Link>

                    <Link :href="route('gallery.index')" :class="[
                        'block px-4 py-3 rounded-lg font-medium transition-all',
                        $page.url.startsWith('/galeria')
                            ? 'bg-white/20 text-white'
                            : (isHomePage ? 'text-white/90 hover:bg-white/10' : 'text-gray-700 hover:bg-gray-100')
                    ]">
                    Galería
                    </Link>

                    <Link :href="route('photographers.index')" :class="[
                        'block px-4 py-3 rounded-lg font-medium transition-all',
                        $page.url.startsWith('/fotografos')
                            ? 'bg-white/20 text-white'
                            : (isHomePage ? 'text-white/90 hover:bg-white/10' : 'text-gray-700 hover:bg-gray-100')
                    ]">
                    Fotógrafos
                    </Link>

                    <div class="pt-4 border-t" :class="isHomePage ? 'border-white/10' : 'border-gray-200'">
                        <template v-if="!user">
                            <Link :href="route('login')" :class="[
                                'block px-4 py-3 rounded-lg font-medium transition-all mb-2',
                                isHomePage ? 'text-white/90 hover:bg-white/10' : 'text-gray-700 hover:bg-gray-100'
                            ]">
                            Iniciar Sesión
                            </Link>

                            <Link :href="route('register')"
                                class="block px-4 py-3 rounded-lg font-semibold text-center bg-gradient-to-r from-purple-600 to-indigo-600 text-white hover:from-purple-700 hover:to-indigo-700 transition-all mb-2">
                            Registrarse
                            </Link>

                            <!--  NUEVO: Botón "Soy Fotógrafo" en mobile -->
                            <Link :href="route('photographer.register')"
                                :class="[
                                    'block px-4 py-3 rounded-lg font-semibold text-center border-2 transition-all',
                                    isHomePage
                                        ? 'border-white/50 text-white hover:bg-white/10'
                                        : 'border-purple-600 text-purple-600 hover:bg-purple-600 hover:text-white'
                                ]">
                             Soy fotógrafo
                            </Link>
                        </template>

                        <!--  ACTUALIZADO: Dashboard dinámico en mobile -->
                        <template v-else>
                            <Link 
                                v-if="dashboardRoute"
                                :href="dashboardRoute" 
                                :class="[
                                    'block px-4 py-3 rounded-lg font-medium transition-all mb-2',
                                    isHomePage ? 'text-white/90 hover:bg-white/10' : 'text-gray-700 hover:bg-gray-100'
                                ]"
                            >
                                {{ dashboardText }}
                            </Link>

                            <Link :href="route('logout')" method="post" as="button"
                                class="w-full text-left px-4 py-3 rounded-lg font-medium text-red-600 hover:bg-red-50 transition-all">
                            Cerrar Sesión
                            </Link>
                        </template>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Contenido de la Página -->

        <main :class="isHomePage ? '' : 'pt-20'">
            <slot />
        </main>


        <!-- Footer -->
        <footer class="bg-gray-900 text-white py-16 mt-20">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-12">
                    <!-- Logo y descripción -->
                    <div class="md:col-span-2">
                        <div class="flex items-center space-x-3 mb-4">
                            <div
                                class="w-10 h-10 bg-gradient-to-br from-purple-600 to-indigo-600 rounded-lg flex items-center justify-center">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <span class="text-2xl font-bold">Empresa</span>
                        </div>
                        <p class="text-gray-400 max-w-md">
                            Capturando tus mejores momentos en alta calidad. Encuentra y descarga tus fotos de eventos
                            especiales de manera rápida y sencilla.
                        </p>
                    </div>

                    <!-- Enlaces -->
                    <div>
                        <h3 class="font-semibold mb-4">Enlaces</h3>
                        <ul class="space-y-2 text-gray-400">
                            <li>
                                <Link href="/" class="hover:text-white transition">Inicio</Link>
                            </li>
                            <li>
                                <Link :href="route('events.index')" class="hover:text-white transition">Eventos</Link>
                            </li>
                            <li>
                                <Link :href="route('gallery.index')" class="hover:text-white transition">Galería</Link>
                            </li>
                            <li>
                                <Link :href="route('photographers.index')" class="hover:text-white transition">
                                Fotógrafos</Link>
                            </li>
                        </ul>
                    </div>

                    <!-- Contacto -->
                    <div>
                        <h3 class="font-semibold mb-4">Contacto</h3>
                        <ul class="space-y-2 text-gray-400">
                            <li>info@Empresa.com</li>
                            <li>+1 234 567 890</li>
                        </ul>
                    </div>
                </div>

                <div class="border-t border-gray-800 mt-12 pt-8 text-center text-gray-500 text-sm">
                    © {{ new Date().getFullYear() }} Empresa. Todos los derechos reservados.
                </div>
            </div>
        </footer>
    </div>
</template>
