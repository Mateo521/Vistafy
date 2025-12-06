<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import { computed, ref, onMounted, onUnmounted } from 'vue';

const page = usePage();
const user = computed(() => page.props.auth?.user);
const mobileMenuOpen = ref(false);
const scrolled = ref(false);

// Detectar ruta para aplicar estilos específicos
const isHomePage = computed(() => page.url === '/');

// Lógica de Scroll para cambiar el estado de la barra de navegación
const handleScroll = () => {
    // Cambiamos el estado después de 20px de scroll para una respuesta rápida
    scrolled.value = window.scrollY > 20;
};

onMounted(() => {
    window.addEventListener('scroll', handleScroll);
});

onUnmounted(() => {
    window.removeEventListener('scroll', handleScroll);
});

// Lógica de redirección y texto según rol (Simplificada)
const dashboardInfo = computed(() => {
    if (!user.value) return null;
    
    if (user.value.is_admin) {
        return { route: route('admin.dashboard'), text: 'ADMINISTRACIÓN' };
    }
    if (user.value.role === 'photographer') {
        return { route: route('photographer.dashboard'), text: 'PANEL PROFESIONAL' };
    }
    return { route: route('home'), text: 'MI CUENTA' }; // Clientes al home o perfil
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

                            <template v-else>
                                <div class="flex items-center space-x-4">
                                    <Link v-if="dashboardInfo" :href="dashboardInfo.route" :class="[
                                        'text-xs font-bold uppercase tracking-widest transition-colors',
                                        isHomePage && !scrolled ? 'text-white' : 'text-slate-900'
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

                    <button @click="mobileMenuOpen = !mobileMenuOpen" class="md:hidden z-50 focus:outline-none">
                        <div class="space-y-1.5">
                            <span :class="['block w-6 h-0.5 transition-all duration-300', mobileMenuOpen ? 'rotate-45 translate-y-2 bg-slate-900' : (isHomePage && !scrolled ? 'bg-white' : 'bg-slate-900')]"></span>
                            <span :class="['block w-6 h-0.5 transition-all duration-300', mobileMenuOpen ? 'opacity-0' : (isHomePage && !scrolled ? 'bg-white' : 'bg-slate-900')]"></span>
                            <span :class="['block w-6 h-0.5 transition-all duration-300', mobileMenuOpen ? '-rotate-45 -translate-y-2 bg-slate-900' : (isHomePage && !scrolled ? 'bg-white' : 'bg-slate-900')]"></span>
                        </div>
                    </button>
                </div>
            </div>

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
                        <Link v-for="item in ['Inicio', 'Eventos', 'Galería', 'Fotógrafos']" 
                            :key="item" href="#" 
                            class="text-sm font-bold uppercase tracking-widest text-slate-900 hover:text-slate-600">
                            {{ item }}
                        </Link>
                        <hr class="border-gray-100 w-1/3 mx-auto my-4">
                        <template v-if="!user">
                            <Link :href="route('login')" class="text-sm text-slate-600">Iniciar Sesión</Link>
                            <Link :href="route('register')" class="text-sm font-bold text-slate-900">Crear Cuenta</Link>
                        </template>
                        <template v-else>
                            <Link :href="route('logout')" method="post" class="text-sm text-red-600">Cerrar Sesión</Link>
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
                        <Link href="#" class="hover:text-white transition">Eventos</Link>
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