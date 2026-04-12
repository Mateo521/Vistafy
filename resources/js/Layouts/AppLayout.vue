<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import { computed, ref, onMounted, onUnmounted } from 'vue';
import { ShoppingCartIcon, ChevronDownIcon } from '@heroicons/vue/24/outline';
import axios from 'axios';
import ToastContainer from '@/Components/ToastContainer.vue';
import CustomCursor from '@/Components/CustomCursor.vue';
import { useConfirm } from '@/Composables/useConfirm';
import ConfirmDialog from '@/Components/ConfirmDialog.vue';

import * as faceapi from 'face-api.js';
import '@tensorflow/tfjs-backend-webgl';


const { confirmState, handleConfirm, handleCancel } = useConfirm();
const page = usePage();
const user = computed(() => page.props.auth?.user);
const mobileMenuOpen = ref(false);
const scrolled = ref(false);
const cartCount = ref(0);
const eventsMenuOpen = ref(false);
const userMenuOpen = ref(false);

// Detectar ruta para aplicar estilos específicos
const isHomePage = computed(() => page.url === '/');

// Lógica de Scroll para cambiar el estado de la barra de navegación
const handleScroll = () => {
    scrolled.value = window.scrollY > 20;
};

//  Cargar contador del carrito
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

onMounted(async () => {
    window.addEventListener('scroll', handleScroll);
    loadCartCount();

    // Escuchar eventos personalizados para actualizar el contador
    window.addEventListener('cart-updated', loadCartCount);

    //  Hacer faceapi disponible globalmente
    if (typeof window !== 'undefined') {
        window.faceapi = faceapi;
  

        // Inicializar backend de TensorFlow
        try {
            await faceapi.tf.setBackend('webgl');
            await faceapi.tf.ready();
   
        } catch (err) {
            console.warn(' WebGL no disponible, usando CPU');
            await faceapi.tf.setBackend('cpu');
            await faceapi.tf.ready();
        }
    }
});
onUnmounted(() => {
    window.removeEventListener('scroll', handleScroll);
    window.removeEventListener('cart-updated', loadCartCount);
});

// Lógica de redirección y texto según rol
const dashboardInfo = computed(() => {
    if (!user.value) return null;

    if (user.value.is_admin) {
        return {
            route: route('admin.dashboard'),
            text: 'ADMINISTRACIÓN',
            single: true // Solo un link
        };
    }
    if (user.value.role === 'photographer') {
        return {
            route: route('photographer.dashboard'),
            text: 'PANEL PROFESIONAL',
            single: true
        };
    }

    //  Usuario regular: menú desplegable
    return {
        single: false,
        items: [
            { route: route('purchases.index'), text: 'MIS COMPRAS', icon: 'shopping-bag' },
            { route: route('profile.edit'), text: 'MI CUENTA', icon: 'user' },
        ]
    };
});

const navigationItems = [
    { label: 'Inicio', route: '/', active: page.url === '/' },
    {
        label: 'Eventos',
        hasDropdown: true,
        active: page.url.startsWith('/eventos'),
        items: [
            { label: 'Eventos vigentes', route: route('events.index') },
            { label: 'Próximos eventos', route: route('future-events.map') }
        ]
    },
    { label: 'Galería', route: route('gallery.index'), active: page.url.startsWith('/galeria') },
    { label: 'Fotógrafos', route: route('photographers.index'), active: page.url.startsWith('/fotografos') }
];

</script>

<template>
    <CustomCursor />

    <div class="min-h-screen bg-[#111920] font-['Syne'] text-[#EEE9DF] selection:bg-[#FFB162] selection:text-[#1B2632]">

        <nav :class="[
            'fixed top-0 w-full z-50 transition-all duration-500 ease-in-out border-b',
            isHomePage && !scrolled
                ? 'bg-transparent border-transparent py-8'
                : 'bg-[#1B2632]/95 backdrop-blur-md border-[#C9C1B1]/10 py-4 shadow-xl'
        ]">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center">

                    <Link href="/" class="group z-50 relative flex flex-col">
                        <span class="text-3xl font-['Syne'] font-extrabold tracking-tighter text-[#EEE9DF] transition-colors duration-300">
                            f<span class="text-[#FFB162]">3</span>3
                        </span>
                        <span class="text-[0.55rem] uppercase tracking-[0.4em] text-[#C9C1B1]/60 transition-colors duration-300 -mt-1">
                            Photography
                        </span>
                    </Link>

                    <div class="hidden md:flex items-center space-x-10">
                        <div class="flex space-x-8">
                            <template v-for="item in navigationItems" :key="item.label">
                                
                                <div v-if="item.hasDropdown" class="relative">
                                    <button @click="eventsMenuOpen = !eventsMenuOpen" :class="[
                                        'flex items-center gap-1.5 text-[10px] font-bold uppercase tracking-[0.2em] transition-all duration-300 border-b',
                                        item.active 
                                            ? 'text-[#FFB162] border-[#FFB162]' 
                                            : 'text-[#C9C1B1] border-transparent hover:text-[#EEE9DF] hover:border-[#C9C1B1]/30'
                                    ]">
                                        {{ item.label }}
                                        <ChevronDownIcon :class="[
                                            'w-3 h-3 transition-transform duration-300',
                                            eventsMenuOpen ? 'rotate-180 text-[#FFB162]' : ''
                                        ]" />
                                    </button>

                                    <transition enter-active-class="transition duration-300 ease-out"
                                        enter-from-class="opacity-0 translate-y-2" enter-to-class="opacity-100 translate-y-0"
                                        leave-active-class="transition duration-200 ease-in"
                                        leave-from-class="opacity-100 translate-y-0" leave-to-class="opacity-0 translate-y-2">
                                        <div v-show="eventsMenuOpen" @click.away="eventsMenuOpen = false"
                                            class="absolute left-0 mt-6 w-56 bg-[#1B2632] border border-[#C9C1B1]/10 rounded-sm shadow-2xl overflow-hidden">
                                            <Link v-for="subItem in item.items" :key="subItem.route"
                                                :href="subItem.route" @click="eventsMenuOpen = false"
                                                class="block px-5 py-4 text-[11px] font-bold tracking-widest uppercase text-[#C9C1B1] hover:bg-[#2C3B4D] hover:text-[#FFB162] transition-colors border-b border-[#C9C1B1]/5 last:border-0">
                                                {{ subItem.label }}
                                            </Link>
                                        </div>
                                    </transition>
                                </div>

                                <Link v-else :href="item.route" :class="[
                                    'text-[10px] font-bold uppercase tracking-[0.2em] transition-all duration-300 border-b',
                                    item.active 
                                        ? 'text-[#FFB162] border-[#FFB162]' 
                                        : 'text-[#C9C1B1] border-transparent hover:text-[#EEE9DF] hover:border-[#C9C1B1]/30'
                                ]">
                                    {{ item.label }}
                                </Link>
                            </template>
                        </div>

                        <div class="h-4 w-px bg-[#C9C1B1]/20"></div>

                        <div class="flex items-center space-x-6">
                            <Link v-if="user" :href="route('cart.index')" class="relative group flex items-center justify-center w-8 h-8 rounded-full hover:bg-[#2C3B4D] transition-colors duration-300">
                                <ShoppingCartIcon class="w-4 h-4 text-[#C9C1B1] group-hover:text-[#FFB162] transition-colors duration-300" />
                                <span v-if="cartCount > 0" class="absolute -top-1 -right-1 min-w-[1.1rem] h-[1.1rem] flex items-center justify-center px-1 rounded-full text-[9px] font-bold bg-[#FFB162] text-[#1B2632]">
                                    {{ cartCount > 99 ? '99+' : cartCount }}
                                </span>
                            </Link>

                            <template v-if="!user">
                                <Link :href="route('photographer.register')" class="text-[10px] font-bold uppercase tracking-[0.2em] text-[#C9C1B1] hover:text-[#FFB162] transition-colors">
                                    Soy fotógrafo
                                </Link>
                                <Link :href="route('login')" class="text-[10px] font-bold uppercase tracking-[0.2em] text-[#C9C1B1] hover:text-[#EEE9DF] transition-colors">
                                    Ingresar
                                </Link>
                                <Link :href="route('register')" class="px-6 py-2.5 text-[10px] font-bold uppercase tracking-[0.2em] border border-[#FFB162] text-[#FFB162] hover:bg-[#FFB162] hover:text-[#1B2632] transition-all duration-300 rounded-sm">
                                    Registrarse
                                </Link>
                            </template>

                            <template v-else>
                                <div class="flex items-center space-x-4">
                                    <Link v-if="dashboardInfo?.single" :href="dashboardInfo.route" class="text-[10px] font-bold uppercase tracking-[0.2em] text-[#FFB162] hover:text-[#EEE9DF] transition-colors">
                                        {{ dashboardInfo.text }}
                                    </Link>

                                    <div v-else class="relative">
                                        <button @click="userMenuOpen = !userMenuOpen" class="flex items-center gap-2 text-[10px] font-bold uppercase tracking-[0.2em] text-[#C9C1B1] hover:text-[#EEE9DF] transition-colors">
                                            {{ user.name }}
                                            <ChevronDownIcon :class="['w-3 h-3 transition-transform duration-300', userMenuOpen ? 'rotate-180 text-[#FFB162]' : '']" />
                                        </button>

                                        <transition enter-active-class="transition duration-300 ease-out"
                                            enter-from-class="opacity-0 translate-y-2" enter-to-class="opacity-100 translate-y-0"
                                            leave-active-class="transition duration-200 ease-in"
                                            leave-from-class="opacity-100 translate-y-0" leave-to-class="opacity-0 translate-y-2">
                                            <div v-show="userMenuOpen" @click.away="userMenuOpen = false"
                                                class="absolute right-0 mt-6 w-56 bg-[#1B2632] border border-[#C9C1B1]/10 rounded-sm shadow-2xl overflow-hidden">
                                                
                                                <div class="px-5 py-4 text-[10px] text-[#C9C1B1]/60 border-b border-[#C9C1B1]/10 truncate tracking-widest">
                                                    {{ user.email }}
                                                </div>

                                                <Link v-for="item in dashboardInfo.items" :key="item.route" :href="item.route"
                                                    class="block px-5 py-4 text-[11px] font-bold uppercase tracking-widest text-[#C9C1B1] hover:bg-[#2C3B4D] hover:text-[#FFB162] transition-colors border-b border-[#C9C1B1]/5">
                                                    {{ item.text }}
                                                </Link>

                                                <Link :href="route('logout')" method="post" as="button"
                                                    class="w-full text-left px-5 py-4 text-[11px] font-bold uppercase tracking-widest text-[#A35139] hover:bg-[#A35139]/10 hover:text-[#FFB162] transition-colors">
                                                    Cerrar Sesión
                                                </Link>
                                            </div>
                                        </transition>
                                    </div>

                                    <Link v-if="dashboardInfo?.single" :href="route('logout')" method="post" as="button" class="text-[10px] font-bold uppercase tracking-[0.2em] text-[#A35139] hover:text-[#FFB162] transition-colors">
                                        Salir
                                    </Link>
                                </div>
                            </template>
                        </div>
                    </div>

                    <button @click="mobileMenuOpen = !mobileMenuOpen" class="md:hidden z-50 focus:outline-none p-2">
                        <div class="space-y-1.5 relative w-6 h-4">
                            <span :class="['absolute w-6 h-[2px] bg-[#EEE9DF] transition-all duration-300', mobileMenuOpen ? 'rotate-45 top-2' : 'top-0']"></span>
                            <span :class="['absolute w-6 h-[2px] bg-[#EEE9DF] transition-all duration-300', mobileMenuOpen ? 'opacity-0 top-2' : 'top-2']"></span>
                            <span :class="['absolute w-6 h-[2px] bg-[#EEE9DF] transition-all duration-300', mobileMenuOpen ? '-rotate-45 top-2' : 'top-4']"></span>
                        </div>
                    </button>
                </div>
            </div>

            <transition enter-active-class="transition duration-400 ease-out"
                enter-from-class="opacity-0 -translate-y-full" enter-to-class="opacity-100 translate-y-0"
                leave-active-class="transition duration-300 ease-in" leave-from-class="opacity-100 translate-y-0"
                leave-to-class="opacity-0 -translate-y-full">
                <div v-show="mobileMenuOpen" class="absolute top-0 left-0 w-full h-screen bg-[#1B2632] shadow-2xl pt-28 pb-10 px-8 border-b border-[#C9C1B1]/10 md:hidden flex flex-col overflow-y-auto">
                    <div class="flex flex-col space-y-8 text-center flex-1">
                        
                        <Link href="/" class="text-sm font-bold uppercase tracking-[0.2em] text-[#EEE9DF] hover:text-[#FFB162]">Inicio</Link>

                        <div class="flex flex-col space-y-4">
                            <span class="text-[10px] font-bold uppercase tracking-[0.3em] text-[#FFB162]">Eventos</span>
                            <Link :href="route('events.index')" class="text-sm tracking-widest text-[#C9C1B1] hover:text-[#EEE9DF]">Vigentes</Link>
                            <Link :href="route('future-events.map')" class="text-sm tracking-widest text-[#C9C1B1] hover:text-[#EEE9DF]">Próximos</Link>
                        </div>

                        <Link :href="route('gallery.index')" class="text-sm font-bold uppercase tracking-[0.2em] text-[#EEE9DF] hover:text-[#FFB162]">Galería</Link>
                        <Link :href="route('photographers.index')" class="text-sm font-bold uppercase tracking-[0.2em] text-[#EEE9DF] hover:text-[#FFB162]">Fotógrafos</Link>

                        <Link v-if="user" :href="route('cart.index')" class="text-sm font-bold uppercase tracking-[0.2em] text-[#FFB162] flex items-center justify-center gap-3">
                            <ShoppingCartIcon class="w-5 h-5" />
                            Carrito
                            <span v-if="cartCount > 0" class="bg-[#FFB162] text-[#1B2632] text-xs font-bold px-2 py-0.5 rounded-full">{{ cartCount }}</span>
                        </Link>

                        <div class="h-px w-1/3 mx-auto bg-[#C9C1B1]/20 my-4"></div>

                        <template v-if="!user">
                            <Link :href="route('login')" class="text-xs font-bold tracking-widest uppercase text-[#C9C1B1] hover:text-[#EEE9DF]">Iniciar Sesión</Link>
                            <Link :href="route('register')" class="text-xs font-bold tracking-widest uppercase text-[#FFB162] border border-[#FFB162] py-3 mx-10 rounded-sm">Crear Cuenta</Link>
                        </template>

                        <template v-else>
                            <Link v-if="dashboardInfo?.single" :href="dashboardInfo.route" class="text-xs font-bold tracking-widest uppercase text-[#FFB162]">
                                {{ dashboardInfo.text }}
                            </Link>
                            <template v-else>
                                <Link v-for="item in dashboardInfo.items" :key="item.route" :href="item.route" class="text-xs font-bold tracking-widest uppercase text-[#C9C1B1] hover:text-[#EEE9DF]">
                                    {{ item.text }}
                                </Link>
                            </template>
                            <Link :href="route('logout')" method="post" class="text-xs font-bold tracking-widest uppercase text-[#A35139] mt-4">
                                Cerrar Sesión
                            </Link>
                        </template>
                    </div>
                </div>
            </transition>
        </nav>

        <main :class="['relative z-0 min-h-screen', isHomePage ? 'pt-0' : 'pt-20']">
            <slot />
        </main>

        <footer class="bg-[#111920] text-[#C9C1B1] border-t border-[#C9C1B1]/10">
            <div class="max-w-7xl mx-auto px-8 md:px-16 py-20">
                <div class="flex flex-col md:flex-row justify-between items-center md:items-start space-y-10 md:space-y-0">
                    
                    <div class="text-center md:text-left">
                        <span class="font-['Cormorant_Garamond'] text-4xl font-light tracking-wide block text-[#EEE9DF]">f<em class="italic text-[#FFB162]">3</em>3</span>
                        <span class="text-[0.60rem] uppercase tracking-[0.4em] text-[#C9C1B1]/50 block mt-2">
                            Photography & Art
                        </span>
                    </div>

                    <div class="flex flex-wrap justify-center gap-8 text-[10px] font-bold uppercase tracking-[0.2em] text-[#C9C1B1]">
                        <Link :href="route('events.index')" class="hover:text-[#FFB162] transition-colors">Eventos</Link>
                        <Link :href="route('about')" class="hover:text-[#FFB162] transition-colors">Nosotros</Link>
                        <Link :href="route('contact.index')" class="hover:text-[#FFB162] transition-colors">Soporte</Link>
                    </div>
                </div>

                <div class="mt-16 pt-8 border-t border-[#C9C1B1]/10 flex flex-col md:flex-row justify-between items-center text-[11px] tracking-wider text-[#C9C1B1]/50">
                    <p>© {{ new Date().getFullYear() }} f33 Photography. Todos los derechos reservados.</p>
                    <div class="flex space-x-8 mt-6 md:mt-0 uppercase tracking-widest text-[9px]">
                        <a href="#" class="hover:text-[#FFB162] transition-colors">Privacidad</a>
                        <a href="#" class="hover:text-[#FFB162] transition-colors">Términos</a>
                    </div>
                </div>
            </div>
        </footer>

        <ToastContainer />
        <ConfirmDialog :show="confirmState.show" :title="confirmState.title" :message="confirmState.message"
            :confirm-text="confirmState.confirmText" :cancel-text="confirmState.cancelText" :type="confirmState.type"
            @confirm="handleConfirm" @cancel="handleCancel" @close="handleCancel" />
    </div>
</template>
<style>
/* Opcional: Ocultar el cursor nativo solo en desktop */
@media (min-width: 768px) {

    body,
    a,
    button,
    input {
        cursor: none;
        /* Oculta la flecha por defecto */
    }
}
</style>