<script setup>
import { ref, computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import { UserCircleIcon, ArrowRightOnRectangleIcon, Cog6ToothIcon, GlobeAltIcon } from '@heroicons/vue/24/outline';
import ToastContainer from '@/Components/ToastContainer.vue';
import ConfirmDialog from '@/Components/ConfirmDialog.vue';
import { useConfirm } from '@/Composables/useConfirm';

const showingNavigationDropdown = ref(false);

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


const navLinkClasses = (active) => {
    return [
        'inline-flex items-center px-1 pt-1 border-b-[4px] font-mono text-[10px] font-bold uppercase tracking-widest transition-none h-full',
        active
            ? 'border-[#E30613] text-white'
            : 'border-transparent text-gray-500 hover:text-white hover:border-white/50'
    ];
};


const mobileNavLinkClasses = (active) => {
    return [
        'block pl-3 pr-4 py-3 border-l-[4px] font-mono text-[11px] font-bold uppercase tracking-widest transition-none',
        active
            ? 'border-[#E30613] text-white bg-[#E30613]/10'
            : 'border-transparent text-gray-500 hover:text-white hover:bg-white/5 hover:border-white/30'
    ];
};
</script>

<template>
    <div class="min-h-screen bg-[#050505] font-sans text-white selection:bg-[#E30613] selection:text-white">

        <nav class="bg-[#09090b] border-b-[4px] border-white/10 sticky top-0 z-50">
            <div class="max-w-[1500px] mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-20">

                    <div class="flex">


                        <div class="shrink-0 flex items-center">
                            <Link :href="dashboardRoute" class="group flex items-center gap-3">
                                <div class="flex flex-col justify-center items-center">

                                    <img src="/images/logo.png" alt="F33 Logo"
                                        class="h-10 w-auto object-contain transition-transform duration-300 group-hover:scale-105" />

                                    <span
                                        class="font-mono text-[9px] font-bold uppercase tracking-widest text-[#E30613] mt-1">
                                        {{ user.role === 'admin' ? 'ADMIN_NODE' : (user.role === 'photographer' ?
                                        'PRO_NODE' : 'CLIENT_NODE') }}
                                    </span>

                                </div>
                            </Link>
                        </div>



                        <div class="hidden space-x-8 sm:-my-px sm:ml-12 md:flex">
                            <Link :href="dashboardRoute"
                                :class="navLinkClasses($page.url.startsWith('/fotografo/dashboard') || $page.url.startsWith('/admin/panel'))">
                                [ Panel ]
                            </Link>

                            <Link v-if="user.role === 'photographer'" :href="route('photographer.events.index')"
                                :class="navLinkClasses($page.url.includes('/eventos'))">
                                [ Eventos ]
                            </Link>

                            <Link v-if="user.role === 'photographer'" :href="route('photographer.photos.index')"
                                :class="navLinkClasses($page.url.includes('/fotos'))">
                                [ Archivos ]
                            </Link>

                            <Link v-if="user.role === 'photographer'" :href="route('photographer.opportunities.index')"
                                :class="navLinkClasses($page.url.includes('/oportunidades'))">
                                [ Ofertas ]
                            </Link>

                            <Link v-if="user.role === 'admin'" :href="route('admin.photographers.index')"
                                :class="navLinkClasses($page.url.includes('/admin/fotografos'))">
                                [ Fotógrafos ]
                            </Link>
                        </div>
                    </div>

                    <div class="hidden md:flex md:items-center md:ml-6">

                        <Link href="/"
                            class="font-mono text-[10px] font-bold uppercase tracking-widest text-gray-500 hover:text-white mr-8 transition-none flex items-center gap-2 border-b border-transparent hover:border-white pb-1">
                            <GlobeAltIcon class="w-3.5 h-3.5" />
                            <span>SITIO PÚBLICO</span>
                        </Link>

                        <div class="relative ml-3">
                            <div class="relative" @click="showingNavigationDropdown = !showingNavigationDropdown">
                                <button type="button"
                                    class="flex items-center gap-4 focus:outline-none transition-none group cursor-pointer border border-transparent hover:border-white/20 p-1">
                                    <div class="text-right hidden lg:block">
                                        <div
                                            class="font-sans font-black text-sm text-white group-hover:text-[#E30613] uppercase tracking-tight">
                                            {{ user.name }}
                                        </div>
                                        <div
                                            class="font-mono text-[9px] font-bold uppercase tracking-widest text-gray-500 text-right">
                                            {{ user.role }}
                                        </div>
                                    </div>

                                    <div
                                        class="h-10 w-10 bg-black border-2 border-white/20 group-hover:border-[#E30613] overflow-hidden transition-none flex items-center justify-center">
                                        <img v-if="profilePhotoUrl" :src="profilePhotoUrl" :alt="user.name"
                                            class="h-full w-full object-cover grayscale contrast-125" />
                                        <div v-else
                                            class="h-full w-full flex items-center justify-center font-mono font-bold text-lg text-white">
                                            {{ userInitials }}
                                        </div>
                                    </div>
                                </button>

                                <div v-show="showingNavigationDropdown"
                                    class="absolute right-0 mt-2 w-56 bg-[#09090b] border-[2px] border-white/20 shadow-[8px_8px_0_rgba(227,6,19,1)] py-2 z-50"
                                    @mouseleave="showingNavigationDropdown = false">

                                    <Link :href="route('profile.edit')"
                                        class="block px-4 py-3 font-mono text-[10px] font-bold uppercase tracking-widest text-gray-400 hover:bg-white hover:text-black transition-none w-full text-left">
                                        > CONFIGURACIÓN
                                    </Link>

                                    <Link v-if="user.role === 'photographer'" :href="route('photographer.profile.edit')"
                                        class="block px-4 py-3 font-mono text-[10px] font-bold uppercase tracking-widest text-gray-400 hover:bg-white hover:text-black transition-none w-full text-left">
                                        > PERFIL PÚBLICO
                                    </Link>

                                    <div class="border-t border-white/10 my-1"></div>

                                    <Link :href="route('logout')" method="post" as="button"
                                        class="block px-4 py-3 font-mono text-[10px] font-bold uppercase tracking-widest text-[#E30613] hover:bg-[#E30613] hover:text-black transition-none w-full text-left">
                                        [ Cerrar Sesión ]
                                    </Link>
                                </div>
                            </div>
                            <div v-show="showingNavigationDropdown" @click="showingNavigationDropdown = false"
                                class="fixed inset-0 z-40 cursor-default"></div>
                        </div>
                    </div>

                    <div class="-mr-2 flex items-center md:hidden">
                        <button @click="showingNavigationDropdown = !showingNavigationDropdown"
                            class="inline-flex items-center justify-center p-2 text-gray-400 hover:text-white hover:bg-white/10 border border-transparent hover:border-white/20 focus:outline-none transition-none">
                            <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                                <path
                                    :class="{ 'hidden': showingNavigationDropdown, 'inline-flex': !showingNavigationDropdown }"
                                    stroke-linecap="square" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                                <path
                                    :class="{ 'hidden': !showingNavigationDropdown, 'inline-flex': showingNavigationDropdown }"
                                    stroke-linecap="square" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <div :class="{ 'block': showingNavigationDropdown, 'hidden': !showingNavigationDropdown }"
                class="md:hidden border-t-[4px] border-white/10 bg-[#050505]">
                <div class="pt-2 pb-3 space-y-1">
                    <Link :href="dashboardRoute"
                        :class="mobileNavLinkClasses($page.url.startsWith('/fotografo/dashboard') || $page.url.startsWith('/admin/panel'))">
                        [ PANEL PRINCIPAL ]
                    </Link>

                    <template v-if="user.role === 'photographer'">
                        <Link :href="route('photographer.events.index')"
                            :class="mobileNavLinkClasses($page.url.includes('/eventos'))">
                            [ EVENTOS ]
                        </Link>
                        <Link :href="route('photographer.photos.index')"
                            :class="mobileNavLinkClasses($page.url.includes('/fotos'))">
                            [ ARCHIVOS ]
                        </Link>
                        <Link :href="route('photographer.opportunities.index')"
                            :class="mobileNavLinkClasses($page.url.includes('/oportunidades'))">
                            [ OPORTUNIDADES ]
                        </Link>
                    </template>

                    <template v-if="user.role === 'admin'">
                        <Link :href="route('admin.photographers.index')"
                            :class="mobileNavLinkClasses($page.url.includes('/admin/fotografos'))">
                            [ FOTÓGRAFOS ]
                        </Link>
                    </template>
                </div>

                <div class="pt-4 pb-4 border-t border-white/10 bg-[#09090b]">
                    <div class="flex items-center px-4 mb-4">
                        <div class="shrink-0">
                            <div
                                class="h-10 w-10 border-2 border-[#E30613] bg-black flex items-center justify-center text-[#E30613] font-mono font-bold text-lg">
                                {{ userInitials }}
                            </div>
                        </div>
                        <div class="ml-3">
                            <div class="font-sans font-black uppercase text-base text-white tracking-tighter">{{
                                user.name }}</div>
                            <div class="font-mono text-[9px] tracking-widest text-gray-500 uppercase">{{ user.email }}
                            </div>
                        </div>
                    </div>

                    <div class="mt-3 space-y-1">
                        <Link :href="route('profile.edit')"
                            class="block pl-3 pr-4 py-2 border-l-[4px] border-transparent font-mono text-[10px] font-bold uppercase tracking-widest text-gray-400 hover:text-white hover:bg-white/5">
                            > CONFIGURACIÓN
                        </Link>

                        <Link v-if="user.role === 'photographer'" :href="route('photographer.profile.edit')"
                            class="block pl-3 pr-4 py-2 border-l-[4px] border-transparent font-mono text-[10px] font-bold uppercase tracking-widest text-gray-400 hover:text-white hover:bg-white/5">
                            > PERFIL PÚBLICO
                        </Link>

                        <Link href="/"
                            class="block pl-3 pr-4 py-2 border-l-[4px] border-transparent font-mono text-[10px] font-bold uppercase tracking-widest text-gray-400 hover:text-white hover:bg-white/5">
                            > VOLVER AL SITIO
                        </Link>

                        <Link :href="route('logout')" method="post" as="button"
                            class="w-full text-left block pl-3 pr-4 py-2 border-l-[4px] border-transparent font-mono text-[10px] font-bold uppercase tracking-widest text-[#E30613] hover:text-white hover:bg-[#E30613]">
                            [ CERRAR SESIÓN ]
                        </Link>
                    </div>
                </div>
            </div>
        </nav>

        <header v-if="$slots.header" class="bg-[#050505] border-b border-white/10">
            <div class="max-w-[1500px] mx-auto py-8 px-4 sm:px-6 lg:px-8">
                <slot name="header" />
            </div>
        </header>

        <main class="bg-[#050505] min-h-[calc(100vh-160px)] relative z-10">
            <slot />
        </main>

        <footer class="bg-[#09090b] border-t-[4px] border-white/10 py-8">
            <div
                class="max-w-[1500px] mx-auto px-4 sm:px-6 lg:px-8 flex flex-col md:flex-row justify-between items-center gap-4 font-mono text-[10px] uppercase tracking-widest text-gray-500">
                <span>© {{ new Date().getFullYear() }} F33. ALL RIGHTS RESERVED.</span>
                <span class="flex items-center gap-2">
                    <div class="w-2 h-2 bg-[#E30613] animate-pulse"></div>
                    {{ user.role === 'admin' ? 'SYS_ADMIN ACCESS' : 'PROFESSIONAL ACCESS' }}
                </span>
            </div>
        </footer>

        <ToastContainer />

        <ConfirmDialog :show="confirmState.show" :title="confirmState.title" :message="confirmState.message"
            :confirm-text="confirmState.confirmText" :cancel-text="confirmState.cancelText" :type="confirmState.type"
            @confirm="handleConfirm" @cancel="handleCancel" @close="handleCancel" />
    </div>
</template>