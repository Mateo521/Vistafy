<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import { computed, ref, onMounted, onUnmounted } from 'vue';
import { ShoppingCartIcon, ChevronDownIcon } from '@heroicons/vue/24/outline';
import axios from 'axios';
import ToastContainer from '@/Components/ToastContainer.vue';
import { useConfirm } from '@/Composables/useConfirm';
import ConfirmDialog from '@/Components/ConfirmDialog.vue';

import * as faceapi from 'face-api.js';
import '@tensorflow/tfjs-backend-webgl';

const { confirmState, handleConfirm, handleCancel } = useConfirm();
const page = usePage();
const user = computed(() => page.props.auth?.user);
const mobileMenuOpen = ref(false);
const cartCount = ref(0);
const eventsMenuOpen = ref(false);
const userMenuOpen = ref(false);

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
    loadCartCount();

    // Escuchar eventos personalizados para actualizar el contador
    window.addEventListener('cart-updated', loadCartCount);

    // Hacer faceapi disponible globalmente
    if (typeof window !== 'undefined') {
        window.faceapi = faceapi;

        // Inicializar backend de TensorFlow
        try {
            await faceapi.tf.setBackend('webgl');
            await faceapi.tf.ready();
        } catch (err) {
            console.warn('WebGL no disponible, usando CPU');
            await faceapi.tf.setBackend('cpu');
            await faceapi.tf.ready();
        }
    }
});

onUnmounted(() => {
    window.removeEventListener('cart-updated', loadCartCount);
});

const dashboardInfo = computed(() => {
    if (!user.value) return null;

    if (user.value.is_admin) {
        return {
            route: route('admin.dashboard'),
            text: 'ADMINISTRACIÓN',
            single: true
        };
    }
    if (user.value.role === 'photographer') {
        return {
            route: route('photographer.dashboard'),
            text: 'PANEL PROFESIONAL',
            single: true
        };
    }

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
        active: page.url.startsWith('/eventos') || page.url.startsWith('/proximos'),
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
    <div class="min-h-screen bg-[#050505] font-sans text-white selection:bg-[#E30613] selection:text-black">

        <nav class="fixed top-0 w-full z-50 bg-[#050505] border-b-2 border-[#E30613] py-4 shadow-[0_4px_20px_rgba(0,0,0,0.8)]">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center">

                    <Link href="/" class="group z-50 relative flex items-center">
                        <img src="/images/logo.png" alt="f33 Photography"
                            class="h-10 w-auto transition-opacity duration-300 group-hover:opacity-80" />
                    </Link>

                    <div class="hidden md:flex items-center space-x-10">
                        <div class="flex space-x-8">
                            <template v-for="item in navigationItems" :key="item.label">

                                <div v-if="item.hasDropdown" class="relative">
                                    <button @click="eventsMenuOpen = !eventsMenuOpen" :class="[
                                        'flex items-center gap-1.5 text-[10px] font-bold uppercase transition-all duration-300',
                                        item.active
                                            ? 'text-[#E30613]'
                                            : 'text-white hover:text-[#E30613]'
                                    ]">
                                        {{ item.label }}
                                        <ChevronDownIcon :class="[
                                            'w-3 h-3 transition-transform duration-300',
                                            eventsMenuOpen ? 'rotate-180 text-[#E30613]' : ''
                                        ]" />
                                    </button>

                                    <transition enter-active-class="transition duration-300 ease-out"
                                        enter-from-class="opacity-0 translate-y-2"
                                        enter-to-class="opacity-100 translate-y-0"
                                        leave-active-class="transition duration-200 ease-in"
                                        leave-from-class="opacity-100 translate-y-0"
                                        leave-to-class="opacity-0 translate-y-2">
                                        <div v-show="eventsMenuOpen" @click.away="eventsMenuOpen = false"
                                            class="absolute left-0 mt-6 w-56 bg-black border border-[#E30613] rounded-none shadow-[4px_4px_0px_0px_rgba(227,6,19,0.3)] overflow-hidden">
                                            <Link v-for="subItem in item.items" :key="subItem.route"
                                                :href="subItem.route" @click="eventsMenuOpen = false"
                                                class="block px-5 py-4 text-[11px] font-bold tracking-widest uppercase text-white hover:bg-[#E30613] hover:text-black transition-colors border-b border-zinc-800 last:border-0">
                                                {{ subItem.label }}
                                            </Link>
                                        </div>
                                    </transition>
                                </div>

                                <Link v-else :href="item.route" :class="[
                                    'text-[10px] font-bold uppercase transition-all duration-300',
                                    item.active
                                        ? 'text-[#E30613]'
                                        : 'text-white hover:text-[#E30613]'
                                ]">
                                    {{ item.label }}
                                </Link>
                            </template>
                        </div>

                        <div class="h-4 w-px bg-zinc-700"></div>

                        <div class="flex items-center space-x-6">
                            <Link v-if="user" :href="route('cart.index')"
                                class="relative group flex items-center justify-center w-8 h-8 transition-colors duration-300">
                                <ShoppingCartIcon
                                    class="w-5 h-5 text-white group-hover:text-[#E30613] transition-colors duration-300" />
                                <span v-if="cartCount > 0"
                                    class="absolute -top-2 -right-2 min-w-[1.2rem] h-[1.2rem] flex items-center justify-center px-1 rounded-none text-[9px] font-bold bg-[#E30613] text-black border border-black">
                                    {{ cartCount > 99 ? '99+' : cartCount }}
                                </span>
                            </Link>

                            <template v-if="!user">
                                <Link :href="route('photographer.register')"
                                    class="text-[10px] font-bold uppercase text-white hover:text-[#E30613] transition-colors">
                                    Soy fotógrafo
                                </Link>
                                <Link :href="route('login')"
                                    class="text-[10px] font-bold uppercase text-white hover:text-[#E30613] transition-colors">
                                    Ingresar
                                </Link>
                                <Link :href="route('register')"
                                    class="px-6 py-2.5 text-[10px] font-bold uppercase border-2 border-white text-white hover:bg-white hover:text-black transition-all duration-300 rounded-none">
                                    Registrarse
                                </Link>
                            </template>

                            <template v-else>
                                <div class="flex items-center space-x-4">
                                    <Link v-if="dashboardInfo?.single" :href="dashboardInfo.route"
                                        class="text-[10px] font-bold uppercase text-[#E30613] hover:text-white transition-colors">
                                        {{ dashboardInfo.text }}
                                    </Link>

                                    <div v-else class="relative">
                                        <button @click="userMenuOpen = !userMenuOpen"
                                            class="flex items-center gap-2 text-[10px] font-bold uppercase text-white hover:text-[#E30613] transition-colors">
                                            {{ user.name }}
                                            <ChevronDownIcon
                                                :class="['w-3 h-3 transition-transform duration-300', userMenuOpen ? 'rotate-180 text-[#E30613]' : '']" />
                                        </button>

                                        <transition enter-active-class="transition duration-300 ease-out"
                                            enter-from-class="opacity-0 translate-y-2"
                                            enter-to-class="opacity-100 translate-y-0"
                                            leave-active-class="transition duration-200 ease-in"
                                            leave-from-class="opacity-100 translate-y-0"
                                            leave-to-class="opacity-0 translate-y-2">
                                            <div v-show="userMenuOpen" @click.away="userMenuOpen = false"
                                                class="absolute right-0 mt-6 w-56 bg-black border border-zinc-800 rounded-none shadow-[4px_4px_0px_0px_rgba(255,255,255,0.05)] overflow-hidden">

                                                <div class="px-5 py-4 text-[10px] text-zinc-500 border-b border-zinc-800 truncate tracking-widest uppercase">
                                                    {{ user.email }}
                                                </div>

                                                <Link v-for="item in dashboardInfo.items" :key="item.route"
                                                    :href="item.route"
                                                    class="block px-5 py-4 text-[11px] font-bold uppercase tracking-widest text-white hover:bg-[#E30613] hover:text-black transition-colors border-b border-zinc-800">
                                                    {{ item.text }}
                                                </Link>

                                                <Link :href="route('logout')" method="post" as="button"
                                                    class="w-full text-left px-5 py-4 text-[11px] font-bold uppercase tracking-widest text-[#E30613] hover:bg-[#E30613] hover:text-black transition-colors">
                                                    Cerrar Sesión
                                                </Link>
                                            </div>
                                        </transition>
                                    </div>

                                    <Link v-if="dashboardInfo?.single" :href="route('logout')" method="post" as="button"
                                        class="text-[10px] font-bold uppercase text-zinc-500 hover:text-[#E30613] transition-colors">
                                        Salir
                                    </Link>
                                </div>
                            </template>
                        </div>
                    </div>

                    <button @click="mobileMenuOpen = !mobileMenuOpen" class="md:hidden z-50 focus:outline-none p-2">
                        <div class="relative w-6 h-4">
                            <span
                                :class="['absolute w-6 h-[2px] bg-white transition-all duration-300', mobileMenuOpen ? 'rotate-45 top-2' : 'top-0']"></span>
                            <span
                                :class="['absolute w-6 h-[2px] bg-white transition-all duration-300', mobileMenuOpen ? 'opacity-0 top-2' : 'top-2']"></span>
                            <span
                                :class="['absolute w-6 h-[2px] bg-white transition-all duration-300', mobileMenuOpen ? '-rotate-45 top-2' : 'top-4']"></span>
                        </div>
                    </button>
                </div>
            </div>

            <transition enter-active-class="transition duration-400 ease-out"
                enter-from-class="opacity-0 -translate-y-full" enter-to-class="opacity-100 translate-y-0"
                leave-active-class="transition duration-300 ease-in" leave-from-class="opacity-100 translate-y-0"
                leave-to-class="opacity-0 -translate-y-full">
                <div v-show="mobileMenuOpen"
                    class="absolute top-0 left-0 w-full h-screen bg-black shadow-2xl pt-28 pb-10 px-8 border-b-4 border-[#E30613] md:hidden flex flex-col overflow-y-auto z-40">
                    <div class="flex flex-col space-y-8 text-center flex-1">

                        <Link href="/" class="text-sm font-bold uppercase text-white hover:text-[#E30613]">Inicio</Link>

                        <div class="flex flex-col space-y-4">
                            <span class="text-[10px] font-bold uppercase text-[#E30613]">Eventos</span>
                            <Link :href="route('events.index')" class="text-sm tracking-widest text-zinc-400 hover:text-white uppercase">Vigentes</Link>
                            <Link :href="route('future-events.map')" class="text-sm tracking-widest text-zinc-400 hover:text-white uppercase">Próximos</Link>
                        </div>

                        <Link :href="route('gallery.index')" class="text-sm font-bold uppercase text-white hover:text-[#E30613]">Galería</Link>
                        <Link :href="route('photographers.index')" class="text-sm font-bold uppercase text-white hover:text-[#E30613]">Fotógrafos</Link>

                        <Link v-if="user" :href="route('cart.index')"
                            class="text-sm font-bold uppercase text-[#E30613] flex items-center justify-center gap-3">
                            <ShoppingCartIcon class="w-5 h-5" />
                            Carrito
                            <span v-if="cartCount > 0"
                                class="bg-[#E30613] text-black text-xs font-bold px-2 py-0.5 rounded-none border border-black">{{ cartCount }}</span>
                        </Link>

                        <div class="h-px w-1/3 mx-auto bg-zinc-800 my-4"></div>

                        <template v-if="!user">
                            <Link :href="route('login')" class="text-xs font-bold tracking-widest uppercase text-white hover:text-[#E30613]">Iniciar sesión</Link>
                            <Link :href="route('register')" class="text-xs font-bold tracking-widest uppercase text-[#E30613] border-2 border-[#E30613] py-3 mx-10 rounded-none hover:bg-[#E30613] hover:text-black transition-colors">Crear cuenta</Link>
                        </template>

                        <template v-else>
                            <Link v-if="dashboardInfo?.single" :href="dashboardInfo.route" class="text-xs font-bold tracking-widest uppercase text-[#E30613]">
                                {{ dashboardInfo.text }}
                            </Link>
                            <template v-else>
                                <Link v-for="item in dashboardInfo.items" :key="item.route" :href="item.route"
                                    class="text-xs font-bold tracking-widest uppercase text-white hover:text-[#E30613]">
                                    {{ item.text }}
                                </Link>
                            </template>
                            <Link :href="route('logout')" method="post"
                                class="text-xs font-bold tracking-widest uppercase text-zinc-500 hover:text-[#E30613] mt-4">
                                Cerrar Sesión
                            </Link>
                        </template>
                    </div>
                </div>
            </transition>
        </nav>

        <main class="relative z-0 min-h-screen pt-24 bg-[#050505]">
            <slot />
        </main>

        <footer class="bg-black text-zinc-400 border-t border-zinc-800">
            <div class="max-w-7xl mx-auto px-8 md:px-16 py-20">
                <div class="flex flex-col md:flex-row justify-between items-center md:items-start space-y-10 md:space-y-0">

                    <Link href="/" class="group z-50 relative flex items-center">
                        <img src="/images/logo-dark.png" alt="f33 Photography"
                            class="h-10 w-auto transition-opacity duration-300 group-hover:opacity-80" />
                    </Link>

                    <div class="flex flex-wrap justify-center gap-8 text-[10px] font-bold uppercase text-zinc-500">
                        <Link :href="route('events.index')" class="hover:text-white transition-colors">Eventos</Link>
                        <Link :href="route('about')" class="hover:text-white transition-colors">Nosotros</Link>
                        <Link :href="route('contact.index')" class="hover:text-white transition-colors">Soporte</Link>
                    </div>
                </div>

                <div class="mt-16 pt-8 border-t border-zinc-800 flex flex-col md:flex-row justify-between items-center text-[11px] tracking-wider text-zinc-600">
                    <p>© {{ new Date().getFullYear() }} f33. Todos los derechos reservados.</p>
                    <div class="flex space-x-8 mt-6 md:mt-0 uppercase tracking-widest text-[9px]">
                        <a href="#" class="hover:text-white transition-colors">Privacidad</a>
                        <a href="#" class="hover:text-white transition-colors">Términos</a>
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