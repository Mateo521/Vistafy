<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import { 
    GlobeAltIcon, 
    ChevronDownIcon, 
    Cog6ToothIcon, 
    ArrowRightOnRectangleIcon,
    UserCircleIcon
} from '@heroicons/vue/24/outline';
import ToastContainer from '@/Components/ToastContainer.vue';
import ConfirmDialog from '@/Components/ConfirmDialog.vue';
import { useConfirm } from '@/Composables/useConfirm';

const mobileMenuOpen = ref(false);
const userMenuOpen = ref(false);

const page = usePage();
const user = computed(() => page.props.auth.user);
const { confirmState, handleConfirm, handleCancel } = useConfirm();

const profilePhotoUrl = computed(() => {
    if (user.value.role === 'photographer' && user.value.photographer?.profile_photo_url) {
        return user.value.photographer.profile_photo_url;
    }
    return null;
});

const userInitials = computed(() => {
    return user.value.name.charAt(0).toUpperCase();
});

const dashboardRoute = computed(() => {
    if (user.value.role === 'photographer') {
        return route('photographer.dashboard');
    } else if (user.value.role === 'admin') {
        return route('admin.dashboard');
    } else {
        return route('home');
    }
});


const isUrlActive = (path) => {
    return page.url.startsWith(path);
};
</script>

<template>
    <div class="min-h-screen bg-[#F8F9FA] font-sans text-slate-800 selection:bg-[#E30613] selection:text-white antialiased">

        
        <nav class="fixed top-4 left-1/2 transform -translate-x-1/2 w-[95%] max-w-[1500px] z-50 bg-white/85 backdrop-blur-md rounded-2xl shadow-[0_8px_30px_rgb(0,0,0,0.06)] border border-white/50 px-4 md:px-6 py-3 transition-all duration-300">
            <div class="flex justify-between items-center w-full">

                
                <div class="shrink-0 flex items-center">
                    <Link :href="dashboardRoute" class="group flex items-center gap-3">
                        <img src="/images/logo.png" alt="F33 Logo"
                            class="h-9 w-auto object-contain transition-transform duration-300 group-hover:scale-105" />
                        <span class="hidden lg:block font-bold text-[10px] uppercase tracking-widest text-[#E30613] bg-red-50 px-2 py-1 rounded-md">
                            {{ user.role === 'admin' ? 'ADMIN' : (user.role === 'photographer' ? 'PRO' : 'CLIENTE') }}
                        </span>
                    </Link>
                </div>

                
                <div class="hidden md:flex items-center justify-center flex-1 px-8">
                    <div class="flex space-x-8">
                        <Link :href="dashboardRoute" :class="[
                            'relative text-xs font-bold uppercase tracking-wider transition-all duration-300 after:content-[\'\'] after:absolute after:-bottom-1.5 after:left-0 after:h-0.5 after:w-0 after:bg-[#E30613] after:transition-all after:duration-300 hover:after:w-full',
                            isUrlActive('/fotografo/dashboard') || isUrlActive('/admin/panel') ? 'text-[#E30613] after:w-full' : 'text-gray-500 hover:text-black'
                        ]">
                            Panel
                        </Link>

                        <template v-if="user.role === 'photographer'">
                            <Link :href="route('photographer.events.index')" :class="[
                                'relative text-xs font-bold uppercase tracking-wider transition-all duration-300 after:content-[\'\'] after:absolute after:-bottom-1.5 after:left-0 after:h-0.5 after:w-0 after:bg-[#E30613] after:transition-all after:duration-300 hover:after:w-full',
                                isUrlActive('/fotografo/eventos') ? 'text-[#E30613] after:w-full' : 'text-gray-500 hover:text-black'
                            ]">
                                Eventos
                            </Link>
                            <Link :href="route('photographer.photos.index')" :class="[
                                'relative text-xs font-bold uppercase tracking-wider transition-all duration-300 after:content-[\'\'] after:absolute after:-bottom-1.5 after:left-0 after:h-0.5 after:w-0 after:bg-[#E30613] after:transition-all after:duration-300 hover:after:w-full',
                                isUrlActive('/fotografo/fotos') ? 'text-[#E30613] after:w-full' : 'text-gray-500 hover:text-black'
                            ]">
                                Archivos
                            </Link>
                            <Link :href="route('photographer.opportunities.index')" :class="[
                                'relative text-xs font-bold uppercase tracking-wider transition-all duration-300 after:content-[\'\'] after:absolute after:-bottom-1.5 after:left-0 after:h-0.5 after:w-0 after:bg-[#E30613] after:transition-all after:duration-300 hover:after:w-full',
                                isUrlActive('/fotografo/oportunidades') ? 'text-[#E30613] after:w-full' : 'text-gray-500 hover:text-black'
                            ]">
                                Ofertas
                            </Link>
                        </template>

                        <template v-if="user.role === 'admin'">
                            <Link :href="route('admin.photographers.index')" :class="[
                                'relative text-xs font-bold uppercase tracking-wider transition-all duration-300 after:content-[\'\'] after:absolute after:-bottom-1.5 after:left-0 after:h-0.5 after:w-0 after:bg-[#E30613] after:transition-all after:duration-300 hover:after:w-full',
                                isUrlActive('/admin/fotografos') ? 'text-[#E30613] after:w-full' : 'text-gray-500 hover:text-black'
                            ]">
                                Fotógrafos
                            </Link>
                        </template>
                    </div>
                </div>

               
                <div class="hidden md:flex items-center justify-end space-x-6 shrink-0">
                    
                  
                    <Link href="/" class="flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-gray-500 hover:text-[#E30613] transition-colors">
                        <GlobeAltIcon class="w-4 h-4" />
                        <span class="hidden lg:block">Sitio público</span>
                    </Link>

                    <div class="w-px h-5 bg-gray-200"></div>

                    
                    <div class="relative">
                        <button @click="userMenuOpen = !userMenuOpen" class="flex items-center gap-3 focus:outline-none group bg-gray-50 hover:bg-gray-100 px-2 py-1.5 rounded-full transition-colors">
                            <div class="hidden lg:block text-right">
                                <div class="text-xs font-bold text-black group-hover:text-[#E30613] transition-colors">{{ user.name.split(' ')[0] }}</div>
                            </div>
                            
                            
                            <div class="h-8 w-8 rounded-full bg-white border border-gray-200 shadow-sm overflow-hidden flex items-center justify-center shrink-0">
                                <img v-if="profilePhotoUrl" :src="profilePhotoUrl" :alt="user.name" class="h-full w-full object-cover" />
                                <span v-else class="font-bold text-sm text-gray-400">{{ userInitials }}</span>
                            </div>
                            
                            <ChevronDownIcon :class="['w-3.5 h-3.5 text-gray-400 mr-1 transition-transform duration-300', userMenuOpen ? 'rotate-180 text-[#E30613]' : '']" />
                        </button>

                        <transition enter-active-class="transition duration-200 ease-out"
                            enter-from-class="opacity-0 translate-y-4 scale-95" enter-to-class="opacity-100 translate-y-0 scale-100"
                            leave-active-class="transition duration-150 ease-in" leave-from-class="opacity-100 translate-y-0 scale-100"
                            leave-to-class="opacity-0 translate-y-2 scale-95">
                            
                            <div v-show="userMenuOpen" @click.away="userMenuOpen = false" class="absolute right-0 mt-3 w-56 bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden py-2">
                                <div class="px-5 py-3 text-[10px] text-gray-400 border-b border-gray-50 truncate tracking-widest uppercase">
                                    {{ user.email }}
                                </div>

                                <Link :href="route('profile.edit')" class="flex items-center gap-2 px-5 py-3 text-xs font-bold uppercase tracking-widest text-gray-600 hover:bg-gray-50 hover:text-[#E30613] transition-colors">
                                    <Cog6ToothIcon class="w-4 h-4" /> Configuración
                                </Link>

                                <Link v-if="user.role === 'photographer'" :href="route('photographer.profile.edit')" class="flex items-center gap-2 px-5 py-3 text-xs font-bold uppercase tracking-widest text-gray-600 hover:bg-gray-50 hover:text-[#E30613] transition-colors">
                                    <UserCircleIcon class="w-4 h-4" /> Perfil público
                                </Link>

                                <div class="border-t border-gray-50 my-1"></div>

                                <Link :href="route('logout')" method="post" as="button" class="w-full flex items-center gap-2 px-5 py-3 text-xs font-bold uppercase tracking-widest text-[#E30613] hover:bg-red-50 transition-colors">
                                    <ArrowRightOnRectangleIcon class="w-4 h-4" /> Cerrar sesión
                                </Link>
                            </div>
                        </transition>
                    </div>

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
            
            <div v-show="mobileMenuOpen" class="fixed inset-0 z-40 bg-white/95 backdrop-blur-xl md:hidden flex flex-col pt-28 pb-10 px-8 overflow-y-auto">
                

                <div class="flex items-center gap-4 mb-8 pb-8 border-b border-gray-100">
                    <div class="h-14 w-14 rounded-full bg-gray-100 shadow-inner flex items-center justify-center overflow-hidden">
                        <img v-if="profilePhotoUrl" :src="profilePhotoUrl" class="h-full w-full object-cover" />
                        <span v-else class="font-bold text-xl text-gray-400">{{ userInitials }}</span>
                    </div>
                    <div>
                        <div class="font-bold text-lg text-black">{{ user.name }}</div>
                        <div class="text-xs text-gray-500">{{ user.email }}</div>
                    </div>
                </div>


                <div class="flex flex-col space-y-6 flex-1 items-center">
                    
                    <Link :href="dashboardRoute" class="text-lg font-bold uppercase tracking-widest text-black hover:text-[#E30613]">
                        Panel Principal
                    </Link>

                    <template v-if="user.role === 'photographer'">
                        <Link :href="route('photographer.events.index')" class="text-lg font-bold uppercase tracking-widest text-gray-600 hover:text-[#E30613]">
                            Eventos
                        </Link>
                        <Link :href="route('photographer.photos.index')" class="text-lg font-bold uppercase tracking-widest text-gray-600 hover:text-[#E30613]">
                            Archivos
                        </Link>
                        <Link :href="route('photographer.opportunities.index')" class="text-lg font-bold uppercase tracking-widest text-gray-600 hover:text-[#E30613]">
                            Oportunidades
                        </Link>
                    </template>

                    <template v-if="user.role === 'admin'">
                        <Link :href="route('admin.photographers.index')" class="text-lg font-bold uppercase tracking-widest text-gray-600 hover:text-[#E30613]">
                            Fotógrafos
                        </Link>
                    </template>

                    <div class="h-px w-24 bg-gray-200 my-4"></div>

                    <Link :href="route('profile.edit')" class="text-sm font-bold uppercase tracking-widest text-gray-500 hover:text-black">
                        Configuración
                    </Link>
                    <Link v-if="user.role === 'photographer'" :href="route('photographer.profile.edit')" class="text-sm font-bold uppercase tracking-widest text-gray-500 hover:text-black">
                        Perfil público
                    </Link>
                    <Link href="/" class="text-sm font-bold uppercase tracking-widest text-[#E30613] bg-red-50 px-6 py-2 rounded-full">
                        Volver al sitio Web
                    </Link>

                    <Link :href="route('logout')" method="post" as="button" class="text-xs font-bold uppercase tracking-widest text-gray-400 hover:text-[#E30613] mt-8">
                        Cerrar sesión
                    </Link>
                </div>
            </div>
        </transition>

     
        <header v-if="$slots.header" class="bg-white border-b border-gray-100 shadow-sm mt-20 md:mt-24">
            <div class="max-w-[1500px] mx-auto py-6 px-4 sm:px-6 lg:px-8">
                <slot name="header" />
            </div>
        </header>

     
        <main class="relative z-0 min-h-[calc(100vh-160px)] pt-24 md:pt-28">
            <slot />
        </main>

    
        <footer class="bg-white border-t border-gray-200 py-8 mt-auto">
            <div class="max-w-[1500px] mx-auto px-4 sm:px-6 lg:px-8 flex flex-col md:flex-row justify-between items-center gap-4 text-xs font-bold uppercase tracking-widest text-gray-400">
                <span>© {{ new Date().getFullYear() }} F33. Todos los derechos reservados.</span>
                <span class="flex items-center gap-2 bg-gray-50 px-3 py-1.5 rounded-md">
                    <div class="w-2 h-2 rounded-full bg-[#E30613] animate-pulse"></div>
                    {{ user.role === 'admin' ? 'ADMINISTRADOR' : 'ACCESO PROFESIONAL' }}
                </span>
            </div>
        </footer>

        <ToastContainer />

        <ConfirmDialog :show="confirmState.show" :title="confirmState.title" :message="confirmState.message"
            :confirm-text="confirmState.confirmText" :cancel-text="confirmState.cancelText" :type="confirmState.type"
            @confirm="handleConfirm" @cancel="handleCancel" @close="handleCancel" />
    </div>
</template>