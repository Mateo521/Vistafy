<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import { computed, ref, onMounted, onUnmounted } from 'vue';
import { ShoppingCartIcon } from '@heroicons/vue/24/outline';
import axios from 'axios';

const page = usePage();
const user = computed(() => page.props.auth?.user);
const mobileMenuOpen = ref(false);
const scrolled = ref(false);
const cartCount = ref(0);

// Detectar ruta para aplicar estilos específicos
const isHomePage = computed(() => page.url === '/');

// Lógica de Scroll para cambiar el estado de la barra de navegación
const handleScroll = () => {
    scrolled.value = window.scrollY > 20;
};

// 🛒 Cargar contador del carrito
const loadCartCount = async () => {
    if (!user.value) {
        cartCount.value = 0;
        return;
    }

    try {
        const response = await axios.get(route('cart.count'));
        cartCount.value = response.data.count || 0;
    } catch (error) {
        console.error('Error cargando contador del carrito:', error);
        cartCount.value = 0;
    }
};

onMounted(() => {
    window.addEventListener('scroll', handleScroll);
    loadCartCount();

    // Escuchar eventos personalizados para actualizar el contador
    window.addEventListener('cart-updated', loadCartCount);
});

onUnmounted(() => {
    window.removeEventListener('scroll', handleScroll);
    window.removeEventListener('cart-updated', loadCartCount);
});

// Lógica de redirección y texto según rol
const dashboardInfo = computed(() => {
    if (!user.value) return null;
    
    if (user.value.is_admin) {
        return { route: route('admin.dashboard'), text: 'ADMINISTRACIÓN' };
    }
    if (user.value.role === 'photographer') {
        return { route: route('photographer.dashboard'), text: 'PANEL PROFESIONAL' };
    }
    return { route: route('profile.edit'), text: 'MI CUENTA' };  
});
</script>

<template>
    <div class="min-h-screen bg-white font-sans text-slate-900 selection:bg-slate-900 selection:text-white">
        
        <nav :class="[
            'fixed top-0 w-full z-50 transition-all duration-500 ease-in-out border-b',
            isHomePage && !scrolled 
                ? 'bg-transparent border-transparent py-6' 
                : 'bg-white/95 backdrop-blur-md border-gray-100 py-4 shadow-sm'
        ]">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center">

                    <!-- Logo -->
                    <Link href="/" class="group z-50 relative">
                        <div class="flex flex-col">
                            <span :class="[
                                'text-2xl font-serif font-bold tracking-tight transition-colors duration-300',
                                isHomePage && !scrolled ? 'text-white' : 'text-slate-900'
                            ]">
                                EMPRESA
                            </span>
                            <span :class="[
                                'text-[0.60rem] uppercase tracking-[0.3em] transition-colors duration-300',
                                isHomePage && !scrolled ? 'text-white/60' : 'text-slate-500'
                            ]">
                                Photography
                            </span>
                        </div>
                    </Link>

                    <!-- Desktop Menu -->
                    <div class="hidden md:flex items-center space-x-12">
                        <div class="flex space-x-8">
                            <Link v-for="item in [
                                { label: 'Inicio', route: '/', active: $page.url === '/' },
                                { label: 'Eventos', route: route('events.index'), active: $page.url.startsWith('/eventos') },
                                { label: 'Galería', route: route('gallery.index'), active: $page.url.startsWith('/galeria') },
                                { label: 'Fotógrafos', route: route('photographers.index'), active: $page.url.startsWith('/fotografos') }
                            ]" 
                            :key="item.label"
                            :href="item.route" 
                            :class="[
                                'text-xs font-bold uppercase tracking-widest transition-all duration-300 border-b-2',
                                isHomePage && !scrolled 
                                    ? (item.active ? 'text-white border-white' : 'text-white/70 border-transparent hover:text-white hover:border-white/50')
                                    : (item.active ? 'text-slate-900 border-slate-900' : 'text-slate-500 border-transparent hover:text-slate-900 hover:border-slate-300')
                            ]">
                                {{ item.label }}
                            </Link>
                        </div>

                        <div :class="['h-4 w-px', isHomePage && !scrolled ? 'bg-white/20' : 'bg-slate-200']"></div>

                        <div class="flex items-center space-x-6">
                            <!-- 🛒 Carrito (solo para usuarios autenticados) -->
                            <Link v-if="user" :href="route('cart.index')" 
                                class="relative group">
                                <ShoppingCartIcon :class="[
                                    'w-5 h-5 transition-colors duration-300',
                                    isHomePage && !scrolled ? 'text-white group-hover:text-white/80' : 'text-slate-900 group-hover:text-slate-600'
                                ]" />
                                
                                <!-- Contador -->
                                <span v-if="cartCount > 0" :class="[
                                    'absolute -top-2 -right-2 min-w-[1.25rem] h-5 flex items-center justify-center px-1 rounded-full text-[10px] font-bold transition-colors duration-300',
                                    isHomePage && !scrolled 
                                        ? 'bg-white text-slate-900' 
                                        : 'bg-slate-900 text-white'
                                ]">
                                    {{ cartCount > 99 ? '99+' : cartCount }}
                                </span>
                            </Link>

                            <!-- Guest: Login/Register -->
                            <template v-if="!user">
                                <Link :href="route('login')" :class="[
                                    'text-xs font-bold uppercase tracking-widest transition-colors',
                                    isHomePage && !scrolled ? 'text-white hover:text-white/80' : 'text-slate-900 hover:text-slate-600'
                                ]">
                                    Ingresar
                                </Link>
                                <Link :href="route('register')" :class="[
                                    'px-6 py-2 text-xs font-bold uppercase tracking-widest border transition-all duration-300',
                                    isHomePage && !scrolled 
                                        ? 'border-white text-white hover:bg-white hover:text-slate-900' 
                                        : 'border-slate-900 text-slate-900 hover:bg-slate-900 hover:text-white'
                                ]">
                                    Registrarse
                                </Link>
                            </template>

                            <!-- Authenticated: Dashboard/Logout -->
                            <template v-else>
                                <div class="flex items-center space-x-4">
                                    <Link v-if="dashboardInfo" :href="dashboardInfo.route" :class="[
                                        'text-xs font-bold uppercase tracking-widest transition-colors',
                                        isHomePage && !scrolled ? 'text-white hover:text-white/80' : 'text-slate-900 hover:text-slate-600'
                                    ]">
                                        {{ dashboardInfo.text }}
                                    </Link>
                                    <Link :href="route('logout')" method="post" as="button" :class="[
                                        'text-xs font-bold uppercase tracking-widest transition-colors',
                                        isHomePage && !scrolled ? 'text-red-300 hover:text-red-100' : 'text-red-600 hover:text-red-800'
                                    ]">
                                        Salir
                                    </Link>
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- Mobile Menu Button -->
                    <button @click="mobileMenuOpen = !mobileMenuOpen" class="md:hidden z-50 focus:outline-none">
                        <div class="space-y-1.5">
                            <span :class="['block w-6 h-0.5 transition-all duration-300', mobileMenuOpen ? 'rotate-45 translate-y-2 bg-slate-900' : (isHomePage && !scrolled ? 'bg-white' : 'bg-slate-900')]"></span>
                            <span :class="['block w-6 h-0.5 transition-all duration-300', mobileMenuOpen ? 'opacity-0' : (isHomePage && !scrolled ? 'bg-white' : 'bg-slate-900')]"></span>
                            <span :class="['block w-6 h-0.5 transition-all duration-300', mobileMenuOpen ? '-rotate-45 -translate-y-2 bg-slate-900' : (isHomePage && !scrolled ? 'bg-white' : 'bg-slate-900')]"></span>
                        </div>
                    </button>
                </div>
            </div>

            <!-- Mobile Menu -->
            <transition
                enter-active-class="transition duration-300 ease-out"
                enter-from-class="opacity-0 -translate-y-4"
                enter-to-class="opacity-100 translate-y-0"
                leave-active-class="transition duration-200 ease-in"
                leave-from-class="opacity-100 translate-y-0"
                leave-to-class="opacity-0 -translate-y-4"
            >
                <div v-show="mobileMenuOpen" class="absolute top-0 left-0 w-full bg-white shadow-xl pt-24 pb-10 px-6 border-b border-gray-100 md:hidden">
                    <div class="flex flex-col space-y-6 text-center">
                        <!-- Navigation Links -->
                        <Link :href="route('home')" 
                            class="text-sm font-bold uppercase tracking-widest text-slate-900 hover:text-slate-600">
                            Inicio
                        </Link>
                        <Link :href="route('events.index')" 
                            class="text-sm font-bold uppercase tracking-widest text-slate-900 hover:text-slate-600">
                            Eventos
                        </Link>
                        <Link :href="route('gallery.index')" 
                            class="text-sm font-bold uppercase tracking-widest text-slate-900 hover:text-slate-600">
                            Galería
                        </Link>
                        <Link :href="route('photographers.index')" 
                            class="text-sm font-bold uppercase tracking-widest text-slate-900 hover:text-slate-600">
                            Fotógrafos
                        </Link>

                        <!-- 🛒 Carrito Mobile -->
                        <Link v-if="user" :href="route('cart.index')" 
                            class="text-sm font-bold uppercase tracking-widest text-slate-900 hover:text-slate-600 flex items-center justify-center gap-2">
                            <ShoppingCartIcon class="w-5 h-5" />
                            Carrito
                            <span v-if="cartCount > 0" class="bg-slate-900 text-white text-xs font-bold px-2 py-0.5 rounded-full">
                                {{ cartCount }}
                            </span>
                        </Link>

                        <hr class="border-gray-100 w-1/3 mx-auto my-4">
                        
                        <!-- Auth Links -->
                        <template v-if="!user">
                            <Link :href="route('login')" class="text-sm text-slate-600 hover:text-slate-900">
                                Iniciar Sesión
                            </Link>
                            <Link :href="route('register')" class="text-sm font-bold text-slate-900">
                                Crear Cuenta
                            </Link>
                        </template>
                        <template v-else>
                            <Link v-if="dashboardInfo" :href="dashboardInfo.route" 
                                class="text-sm font-bold text-slate-900">
                                {{ dashboardInfo.text }}
                            </Link>
                            <Link :href="route('logout')" method="post" class="text-sm text-red-600">
                                Cerrar Sesión
                            </Link>
                        </template>
                    </div>
                </div>
            </transition>
        </nav>

        <main :class="['relative z-0 min-h-screen', isHomePage ? 'pt-0' : 'pt-24']">
            <slot />
        </main>

        <footer class="bg-slate-900 text-white border-t border-slate-800">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
                <div class="flex flex-col md:flex-row justify-between items-center md:items-start space-y-8 md:space-y-0">
                    
                    <div class="text-center md:text-left">
                        <span class="text-xl font-serif font-bold tracking-wide block">EMPRESA</span>
                        <span class="text-[0.60rem] uppercase tracking-[0.3em] text-slate-500 block mt-1">Professional Photography</span>
                    </div>

                    <div class="flex space-x-8 text-xs font-bold uppercase tracking-widest text-slate-400">
                        <Link :href="route('events.index')" class="hover:text-white transition">Eventos</Link>
                        <Link href="#" class="hover:text-white transition">Nosotros</Link>
                        <Link href="#" class="hover:text-white transition">Soporte</Link>
                    </div>
                </div>

                <div class="mt-12 pt-8 border-t border-slate-800 flex flex-col md:flex-row justify-between items-center text-xs text-slate-500">
                    <p>© {{ new Date().getFullYear() }} Empresa S.A.</p>
                    <div class="flex space-x-6 mt-4 md:mt-0">
                        <a href="#" class="hover:text-slate-300 transition">Privacidad</a>
                        <a href="#" class="hover:text-slate-300 transition">Términos</a>
                    </div>
                </div>
            </div>
        </footer>

    </div>
</template>
