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

 
    window.addEventListener('cart-updated', loadCartCount);

 
    if (typeof window !== 'undefined') {
        window.faceapi = faceapi;

 
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
    <div class="min-h-screen bg-[#F2F0EB] font-sans text-[#050505] selection:bg-[#E30613] selection:text-white">

        <nav class="fixed top-4 left-1/2 transform -translate-x-1/2 w-[95%] max-w-7xl z-50 bg-white/85 backdrop-blur-md rounded-2xl shadow-[0_8px_30px_rgb(0,0,0,0.06)] border border-white/50 px-4 md:px-6 py-3 transition-all duration-300">
            <div class="flex justify-between items-center w-full">

                <Link href="/" class="group z-50 relative flex items-center shrink-0">
                    <img src="/images/logo.png" alt="f33 Photography"
                        class="h-9 w-auto transition-transform duration-300 group-hover:scale-105" />
                </Link>

                <div class="hidden md:flex items-center justify-center flex-1 px-8">
                    <div class="flex space-x-8">
                        <template v-for="item in navigationItems" :key="item.label">

                            <div v-if="item.hasDropdown" class="relative group/nav">
                                <button @click="eventsMenuOpen = !eventsMenuOpen" :class="[
                                    'flex items-center gap-1.5 text-xs font-bold uppercase tracking-wider transition-all duration-300',
                                    item.active ? 'text-[#E30613]' : 'text-gray-600 hover:text-black'
                                ]">
                                    {{ item.label }}
                                    <ChevronDownIcon :class="[
                                        'w-3.5 h-3.5 transition-transform duration-300',
                                        eventsMenuOpen ? 'rotate-180 text-[#E30613]' : ''
                                    ]" />
                                </button>

                                <transition enter-active-class="transition duration-200 ease-out"
                                    enter-from-class="opacity-0 translate-y-4 scale-95"
                                    enter-to-class="opacity-100 translate-y-0 scale-100"
                                    leave-active-class="transition duration-150 ease-in"
                                    leave-from-class="opacity-100 translate-y-0 scale-100"
                                    leave-to-class="opacity-0 translate-y-2 scale-95">
                                    <div v-show="eventsMenuOpen" @click.away="eventsMenuOpen = false"
                                        class="absolute left-1/2 -translate-x-1/2 mt-4 w-56 bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden py-2">
                                        <Link v-for="subItem in item.items" :key="subItem.route"
                                            :href="subItem.route" @click="eventsMenuOpen = false"
                                            class="block px-5 py-3 text-xs font-bold tracking-widest uppercase text-gray-600 hover:bg-gray-50 hover:text-[#E30613] transition-colors">
                                            {{ subItem.label }}
                                        </Link>
                                    </div>
                                </transition>
                            </div>


                            <Link v-else :href="item.route" :class="[
                                'relative text-xs font-bold uppercase tracking-wider transition-all duration-300 after:content-[\'\'] after:absolute after:-bottom-1.5 after:left-0 after:h-0.5 after:w-0 after:bg-[#E30613] after:transition-all after:duration-300 hover:after:w-full',
                                item.active ? 'text-[#E30613] after:w-full' : 'text-gray-600 hover:text-black'
                            ]">
                                {{ item.label }}
                            </Link>
                        </template>
                    </div>
                </div>


                <div class="hidden md:flex items-center justify-end space-x-6 shrink-0">
                    

                    <Link v-if="user" :href="route('cart.index')"
                        class="relative group flex items-center justify-center w-10 h-10 bg-gray-50 rounded-full hover:bg-red-50 transition-colors duration-300">
                        <ShoppingCartIcon class="w-5 h-5 text-gray-700 group-hover:text-[#E30613] transition-colors duration-300" />
                        <span v-if="cartCount > 0"
                            class="absolute -top-1 -right-1 min-w-[20px] h-[20px] flex items-center justify-center px-1 rounded-full text-[10px] font-bold bg-[#E30613] text-white shadow-sm shadow-red-500/30">
                            {{ cartCount > 99 ? '99+' : cartCount }}
                        </span>
                    </Link>


                    <template v-if="!user">
                        <div class="flex items-center space-x-4">
                            <Link :href="route('photographer.register')"
                                class="text-xs font-bold uppercase tracking-wider text-gray-500 hover:text-black transition-colors hidden lg:block">
                                Soy fotógrafo
                            </Link>
                            <div class="w-px h-4 bg-gray-300 hidden lg:block"></div>
                            <Link :href="route('login')"
                                class="text-xs font-bold uppercase tracking-wider text-black hover:text-[#E30613] transition-colors">
                                Ingresar
                            </Link>
                            <Link :href="route('register')"
                                class="px-5 py-2.5 text-xs font-bold uppercase tracking-wider bg-black text-white hover:bg-[#E30613] hover:shadow-lg hover:shadow-red-500/25 transition-all duration-300 rounded-full">
                                Registrarse
                            </Link>
                        </div>
                    </template>


                    <template v-else>
                        <div class="flex items-center space-x-4">
                            <Link v-if="dashboardInfo?.single" :href="dashboardInfo.route"
                                class="text-xs font-bold uppercase tracking-wider text-[#E30613] hover:text-black transition-colors">
                                {{ dashboardInfo.text }}
                            </Link>

                            <div v-else class="relative">
                                <button @click="userMenuOpen = !userMenuOpen"
                                    class="flex items-center gap-2 px-4 py-2 bg-gray-50 rounded-full text-xs font-bold uppercase text-black hover:bg-gray-100 transition-colors">
                                    {{ user.name.split(' ')[0] }}
                                    <ChevronDownIcon :class="['w-3.5 h-3.5 transition-transform duration-300', userMenuOpen ? 'rotate-180 text-[#E30613]' : '']" />
                                </button>

                                <transition enter-active-class="transition duration-200 ease-out"
                                    enter-from-class="opacity-0 translate-y-4 scale-95"
                                    enter-to-class="opacity-100 translate-y-0 scale-100"
                                    leave-active-class="transition duration-150 ease-in"
                                    leave-from-class="opacity-100 translate-y-0 scale-100"
                                    leave-to-class="opacity-0 translate-y-2 scale-95">
                                    <div v-show="userMenuOpen" @click.away="userMenuOpen = false"
                                        class="absolute right-0 mt-3 w-56 bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden py-2">

                                        <div class="px-5 py-3 text-[10px] text-gray-400 border-b border-gray-50 truncate tracking-widest uppercase">
                                            {{ user.email }}
                                        </div>

                                        <Link v-for="item in dashboardInfo.items" :key="item.route"
                                            :href="item.route"
                                            class="block px-5 py-3 text-xs font-bold uppercase tracking-widest text-gray-600 hover:bg-gray-50 hover:text-[#E30613] transition-colors">
                                            {{ item.text }}
                                        </Link>

                                        <Link :href="route('logout')" method="post" as="button"
                                            class="w-full text-left px-5 py-3 text-xs font-bold uppercase tracking-widest text-[#E30613] hover:bg-red-50 transition-colors">
                                            Cerrar Sesión
                                        </Link>
                                    </div>
                                </transition>
                            </div>
                        </div>
                    </template>
                </div>


                <button @click="mobileMenuOpen = !mobileMenuOpen" class="md:hidden z-50 focus:outline-none p-2 bg-gray-50 rounded-full w-10 h-10 flex items-center justify-center">
                    <div class="relative w-5 h-4">
                        <span :class="['absolute left-0 w-5 h-[2px] bg-black rounded-full transition-all duration-300', mobileMenuOpen ? 'rotate-45 top-2' : 'top-0']"></span>
                        <span :class="['absolute left-0 w-5 h-[2px] bg-black rounded-full transition-all duration-300', mobileMenuOpen ? 'opacity-0 top-2' : 'top-[7px]']"></span>
                        <span :class="['absolute left-0 w-5 h-[2px] bg-black rounded-full transition-all duration-300', mobileMenuOpen ? '-rotate-45 top-2' : 'top-[14px]']"></span>
                    </div>
                </button>
            </div>
        </nav>


        <transition enter-active-class="transition duration-300 ease-out"
            enter-from-class="opacity-0 backdrop-blur-none" enter-to-class="opacity-100 backdrop-blur-xl"
            leave-active-class="transition duration-200 ease-in" leave-from-class="opacity-100 backdrop-blur-xl"
            leave-to-class="opacity-0 backdrop-blur-none">
            <div v-show="mobileMenuOpen" class="fixed inset-0 z-40 bg-white/90 backdrop-blur-xl md:hidden flex flex-col pt-28 pb-10 px-8 overflow-y-auto">
                <div class="flex flex-col space-y-6 flex-1 items-center justify-start mt-10">

                    <Link href="/" class="text-xl font-black uppercase tracking-widest text-black hover:text-[#E30613]">Inicio</Link>

                    <div class="flex flex-col space-y-4 items-center">
                        <span class="text-xs font-bold uppercase tracking-widest text-gray-400">Eventos</span>
                        <Link :href="route('events.index')" class="text-lg font-bold tracking-widest text-gray-800 hover:text-[#E30613] uppercase">Vigentes</Link>
                        <Link :href="route('future-events.map')" class="text-lg font-bold tracking-widest text-gray-800 hover:text-[#E30613] uppercase">Próximos</Link>
                    </div>

                    <Link :href="route('gallery.index')" class="text-xl font-black uppercase tracking-widest text-black hover:text-[#E30613]">Galería</Link>
                    <Link :href="route('photographers.index')" class="text-xl font-black uppercase tracking-widest text-black hover:text-[#E30613]">Fotógrafos</Link>

                    <Link v-if="user" :href="route('cart.index')"
                        class="text-lg font-black uppercase tracking-widest text-[#E30613] flex items-center justify-center gap-3 mt-4">
                        <ShoppingCartIcon class="w-6 h-6" />
                        Mi Carrito
                        <span v-if="cartCount > 0" class="bg-[#E30613] text-white text-xs font-bold px-3 py-1 rounded-full">{{ cartCount }}</span>
                    </Link>

                    <div class="h-px w-24 bg-gray-200 my-8"></div>

                    <template v-if="!user">
                        <Link :href="route('login')" class="text-sm font-bold tracking-widest uppercase text-gray-600 hover:text-[#E30613]">Iniciar sesión</Link>
                        <Link :href="route('register')" class="text-sm font-bold tracking-widest uppercase bg-black text-white px-8 py-3 rounded-full hover:bg-[#E30613] hover:shadow-lg hover:shadow-red-500/30 transition-all">Crear cuenta</Link>
                    </template>

                    <template v-else>
                        <Link v-if="dashboardInfo?.single" :href="dashboardInfo.route" class="text-sm font-bold tracking-widest uppercase text-[#E30613] bg-red-50 px-6 py-2 rounded-full">
                            {{ dashboardInfo.text }}
                        </Link>
                        <template v-else>
                            <Link v-for="item in dashboardInfo.items" :key="item.route" :href="item.route"
                                class="text-sm font-bold tracking-widest uppercase text-gray-600 hover:text-black">
                                {{ item.text }}
                            </Link>
                        </template>
                        <Link :href="route('logout')" method="post"
                            class="text-xs font-bold tracking-widest uppercase text-gray-400 hover:text-[#E30613] mt-6">
                            Cerrar Sesión
                        </Link>
                    </template>
                </div>
            </div>
        </transition>


        <main class="relative z-0 min-h-screen pt-28 bg-[#F2F0EB]">
            <slot />
        </main>


        <footer class="bg-white text-gray-600 border-t border-gray-200">
            <div class="max-w-7xl mx-auto px-8 md:px-16 py-20">
                <div class="flex flex-col md:flex-row justify-between items-center md:items-start space-y-10 md:space-y-0">
                    <Link href="/" class="group relative flex items-center">
                        <img src="/images/logo.png" alt="f33 Photography"
                            class="h-24 w-auto transition-opacity duration-300 group-hover:opacity-80" />
                    </Link>

                    <div class="flex flex-wrap justify-center gap-8 text-[11px] font-bold uppercase text-gray-500">
                        <Link :href="route('events.index')" class="hover:text-black transition-colors">Eventos</Link>
                        <Link :href="route('about')" class="hover:text-black transition-colors">Nosotros</Link>
                        <Link :href="route('contact.index')" class="hover:text-black transition-colors">Soporte</Link>
                    </div>
                </div>

                <div class="mt-16 pt-8 border-t border-gray-100 flex flex-col md:flex-row justify-between items-center text-[11px] tracking-wider text-gray-400">
                    <p>© {{ new Date().getFullYear() }} f33. Todos los derechos reservados.</p>
                    <div class="flex space-x-8 mt-6 md:mt-0 uppercase tracking-widest text-[10px]">
                        <Link :href="route('privacy')" class="hover:text-black transition-colors">Privacidad</Link>
                        <Link :href="route('terms')" class="hover:text-black transition-colors">Términos de servicio</Link>
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