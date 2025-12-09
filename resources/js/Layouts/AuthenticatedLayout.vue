<script setup>
import { ref, computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import { UserCircleIcon, ArrowRightOnRectangleIcon, Cog6ToothIcon, GlobeAltIcon } from '@heroicons/vue/24/outline';
import ToastContainer from '@/Components/ToastContainer.vue';
import ConfirmDialog from '@/Components/ConfirmDialog.vue';
import CustomCursor from '@/Components/CustomCursor.vue';
import { useConfirm } from '@/Composables/useConfirm';

const showingNavigationDropdown = ref(false);

const page = usePage();
const user = computed(() => page.props.auth.user);
const { confirmState, handleConfirm, handleCancel } = useConfirm();

// Obtener foto de perfil
const profilePhotoUrl = computed(() => {
    if (user.value.role === 'photographer' && user.value.photographer?.profile_photo_url) {
        return user.value.photographer.profile_photo_url;
    }
    return null;
});

// Obtener iniciales
const userInitials = computed(() => {
    return user.value.name.charAt(0).toUpperCase();
});

// Determinar la ruta del dashboard según el rol
const dashboardRoute = computed(() => {
    if (user.value.role === 'photographer') {
        return route('photographer.dashboard');
    } else if (user.value.role === 'admin') {
        return route('admin.dashboard');
    } else {
        return route('home'); // O un dashboard de usuario cliente si existe
    }
});

// Helper para clases de link activo (Estilo minimalista)
const navLinkClasses = (active) => {
    return [
        'inline-flex items-center px-1 pt-1 border-b-2 text-xs font-bold uppercase tracking-widest transition-all duration-300 h-full',
        active
            ? 'border-slate-900 text-slate-900'
            : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300'
    ];
};

const mobileNavLinkClasses = (active) => {
    return [
        'block pl-3 pr-4 py-3 border-l-4 text-sm font-medium transition-colors',
        active
            ? 'border-slate-900 text-slate-900 bg-slate-50'
            : 'border-transparent text-slate-600 hover:text-slate-800 hover:bg-gray-50 hover:border-gray-300'
    ];
};
</script>

<template>
    <CustomCursor />
    <div class="min-h-screen bg-white font-sans text-slate-900">

        <nav class="bg-white border-b border-gray-100 sticky top-0 z-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-20">

                    <div class="flex">
                        <div class="shrink-0 flex items-center">
                            <Link :href="dashboardRoute" class="group">
                                <div class="flex flex-col">
                                    <span
                                        class="font-serif font-bold text-xl tracking-tight text-slate-900 group-hover:opacity-80 transition-opacity">EMPRESA</span>
                                    <span class="text-[9px] uppercase tracking-[0.3em] text-slate-400">
                                        {{ user.role === 'admin' ? 'Administration' : (user.role === 'photographer' ?
                                            'Professional' : 'Client Area') }}
                                    </span>
                                </div>
                            </Link>
                        </div>

                        <div class="hidden space-x-8 sm:-my-px sm:ml-12 md:flex">

                            <Link :href="dashboardRoute"
                                :class="navLinkClasses($page.url.startsWith('/fotografo/dashboard') || $page.url.startsWith('/admin/panel'))">
                                Mi panel
                            </Link>

                            <Link v-if="user.role === 'photographer'" :href="route('photographer.events.index')"
                                :class="navLinkClasses($page.url.includes('/eventos'))">
                                Mis Eventos
                            </Link>

                            <Link v-if="user.role === 'photographer'" :href="route('photographer.photos.index')"
                                :class="navLinkClasses($page.url.includes('/fotos'))">
                                Mis Fotos
                            </Link>

                            <Link v-if="user.role === 'photographer'" :href="route('photographer.opportunities.index')"
                                :class="navLinkClasses($page.url.includes('/oportunidades'))">
                                Oportunidades
                            </Link>

                            <Link v-if="user.role === 'admin'" :href="route('admin.photographers.index')"
                                :class="navLinkClasses($page.url.includes('/admin/fotografos'))">
                                Fotógrafos
                            </Link>
                        </div>




                    </div>

                    <div class="hidden md:flex md:items-center md:ml-6">

                        <Link href="/"
                            class="text-xs font-bold uppercase tracking-widest text-slate-400 hover:text-slate-900 mr-8 transition-colors flex items-center gap-1">
                            <GlobeAltIcon class="w-4 h-4" />
                            <span>Volver al sitio</span>
                        </Link>

                        <div class="relative ml-3">
                            <div class="relative" @click="showingNavigationDropdown = !showingNavigationDropdown">
                                <button type="button"
                                    class="flex items-center gap-3 text-sm focus:outline-none transition group cursor-pointer">
                                    <div class="text-right hidden lg:block">
                                        <div class="font-bold text-slate-900 group-hover:text-slate-700">{{ user.name }}
                                        </div>
                                        <div class="text-[10px] uppercase tracking-wider text-slate-400 text-right">{{
                                            user.role }}</div>
                                    </div>

                                    <div
                                        class="h-9 w-9 rounded-sm overflow-hidden bg-slate-100 border border-gray-200 group-hover:border-slate-400 transition-colors">
                                        <img v-if="profilePhotoUrl" :src="profilePhotoUrl" :alt="user.name"
                                            class="h-full w-full object-cover" />
                                        <div v-else
                                            class="h-full w-full flex items-center justify-center bg-slate-900 text-white font-serif">
                                            {{ userInitials }}
                                        </div>
                                    </div>
                                </button>

                                <div v-show="showingNavigationDropdown"
                                    class="absolute right-0 mt-3 w-48 bg-white rounded-sm shadow-xl border border-gray-100 py-1 origin-top-right z-50 ring-1 ring-black ring-opacity-5 focus:outline-none"
                                    @mouseleave="showingNavigationDropdown = false">

                                    <div class="px-4 py-3 border-b border-gray-50 lg:hidden">
                                        <p class="text-sm font-medium text-slate-900">{{ user.name }}</p>
                                        <p class="text-xs text-slate-500 truncate">{{ user.email }}</p>
                                    </div>

                                    <Link :href="route('profile.edit')"
                                        class="block px-4 py-2 text-xs uppercase tracking-wider text-slate-600 hover:bg-gray-50 hover:text-slate-900 w-full text-left">
                                        Configuración
                                    </Link>

                                    <Link v-if="user.role === 'photographer'" :href="route('photographer.profile.edit')"
                                        class="block px-4 py-2 text-xs uppercase tracking-wider text-slate-600 hover:bg-gray-50 hover:text-slate-900 w-full text-left">
                                        Perfil Público
                                    </Link>

                                    <div class="border-t border-gray-50 my-1"></div>

                                    <Link :href="route('logout')" method="post" as="button"
                                        class="block px-4 py-2 text-xs uppercase tracking-wider text-red-600 hover:bg-red-50 w-full text-left">
                                        Cerrar Sesión
                                    </Link>





                                </div>
                            </div>

                            <div v-show="showingNavigationDropdown" @click="showingNavigationDropdown = false"
                                class="fixed inset-0 z-40 cursor-default"></div>
                        </div>
                    </div>

                    <div class="-mr-2 flex items-center md:hidden">
                        <button @click="showingNavigationDropdown = !showingNavigationDropdown"
                            class="inline-flex items-center justify-center p-2 rounded-sm text-slate-400 hover:text-slate-500 hover:bg-gray-100 focus:outline-none transition duration-150 ease-in-out">
                            <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                                <path
                                    :class="{ 'hidden': showingNavigationDropdown, 'inline-flex': !showingNavigationDropdown }"
                                    stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 6h16M4 12h16M4 18h16" />
                                <path
                                    :class="{ 'hidden': !showingNavigationDropdown, 'inline-flex': showingNavigationDropdown }"
                                    stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <div :class="{ 'block': showingNavigationDropdown, 'hidden': !showingNavigationDropdown }"
                class="md:hidden border-b border-gray-200 bg-white">
                <div class="pt-2 pb-3 space-y-1">
                    <Link :href="dashboardRoute"
                        :class="mobileNavLinkClasses($page.url.startsWith('/fotografo/dashboard') || $page.url.startsWith('/admin/panel'))">
                        Mi panel
                    </Link>

                    <template v-if="user.role === 'photographer'">
                        <Link :href="route('photographer.events.index')"
                            :class="mobileNavLinkClasses($page.url.includes('/eventos'))">
                            Mis Eventos
                        </Link>
                        <Link :href="route('photographer.photos.index')"
                            :class="mobileNavLinkClasses($page.url.includes('/fotos'))">
                            Mis Fotos
                        </Link>


                        <Link :href="route('photographer.opportunities.index')"
                            :class="mobileNavLinkClasses($page.url.includes('/oportunidades'))">
                            Oportunidades
                        </Link>
                    </template>

                    <template v-if="user.role === 'admin'">
                        <Link :href="route('admin.photographers.index')"
                            :class="mobileNavLinkClasses($page.url.includes('/admin/fotografos'))">
                            Fotógrafos
                        </Link>
                    </template>
                </div>

                <div class="pt-4 pb-1 border-t border-gray-200">
                    <div class="flex items-center px-4 mb-3">
                        <div class="shrink-0">
                            <div
                                class="h-10 w-10 rounded-sm bg-slate-900 flex items-center justify-center text-white font-serif text-lg">
                                {{ userInitials }}
                            </div>
                        </div>
                        <div class="ml-3">
                            <div class="font-medium text-base text-slate-800">{{ user.name }}</div>
                            <div class="font-medium text-sm text-slate-500">{{ user.email }}</div>
                        </div>
                    </div>

                    <div class="mt-3 space-y-1">
                        <Link :href="route('profile.edit')"
                            class="block pl-3 pr-4 py-2 border-l-4 border-transparent text-base font-medium text-slate-600 hover:text-slate-800 hover:bg-gray-50 hover:border-gray-300">
                            Configuración
                        </Link>

                        <Link v-if="user.role === 'photographer'" :href="route('photographer.profile.edit')"
                            class="block pl-3 pr-4 py-2 border-l-4 border-transparent text-base font-medium text-slate-600 hover:text-slate-800 hover:bg-gray-50 hover:border-gray-300">
                            Mi Perfil Público
                        </Link>

                        <Link href="/"
                            class="block pl-3 pr-4 py-2 border-l-4 border-transparent text-base font-medium text-slate-600 hover:text-slate-800 hover:bg-gray-50 hover:border-gray-300">
                            Volver al sitio
                        </Link>

                        <Link :href="route('logout')" method="post" as="button"
                            class="w-full text-left block pl-3 pr-4 py-2 border-l-4 border-transparent text-base font-medium text-red-600 hover:text-red-800 hover:bg-red-50 hover:border-red-300">
                            Cerrar Sesión
                        </Link>
                    </div>
                </div>
            </div>
        </nav>

        <header v-if="$slots.header" class="bg-gray-50 border-b border-gray-100">
            <div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
                <slot name="header" />
            </div>
        </header>

        <main class="bg-gray-50 min-h-[calc(100vh-160px)]">
            <slot />
        </main>

        <footer class="bg-white border-t border-gray-200 py-6">
            <div
                class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex justify-between items-center text-[10px] uppercase tracking-widest text-slate-400">
                <span>© {{ new Date().getFullYear() }} EMPRESA.</span>
                <span>
                    {{ user.role === 'admin' ? 'Administrator Access' : 'Professional Access' }}
                </span>
            </div>
        </footer>
        <ToastContainer />
        <ConfirmDialog :show="confirmState.show" :title="confirmState.title" :message="confirmState.message"
            :confirm-text="confirmState.confirmText" :cancel-text="confirmState.cancelText" :type="confirmState.type"
            @confirm="handleConfirm" @cancel="handleCancel" @close="handleCancel" />
    </div>


</template>