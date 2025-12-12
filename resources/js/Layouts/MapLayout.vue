<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import ToastContainer from '@/Components/ToastContainer.vue';
import CustomCursor from '@/Components/CustomCursor.vue';
import ConfirmDialog from '@/Components/ConfirmDialog.vue';
import { useConfirm } from '@/Composables/useConfirm';
import {
    ChevronLeftIcon,
    MapIcon,
    PhotoIcon,
    UsersIcon,
    HomeIcon,
    CalendarIcon,
} from '@heroicons/vue/24/outline';

const page = usePage();
const user = computed(() => page.props.auth?.user);
const { confirmState, handleConfirm, handleCancel } = useConfirm();
</script>

<template>
    <CustomCursor />

    <!-- Contenedor fullscreen -->
    <div class="min-h-screen bg-slate-900 text-white relative overflow-hidden">

        <!-- Encabezado muy sutil arriba (puedes simplificar aún más si quieres) -->
        <header
            class="fixed top-0 left-0 right-0 z-20 bg-gradient-to-b from-black/80 via-black/40 to-transparent px-4 sm:px-6 lg:px-8 pt-4 pb-6 flex items-center justify-between pointer-events-none">
            <div class="pointer-events-auto flex items-center gap-3">
                <!-- Botón volver a Inicio -->
                <Link href="/"
                    class="inline-flex items-center gap-1 text-xs uppercase tracking-[0.2em] text-slate-300 hover:text-white transition">
                    <ChevronLeftIcon class="w-4 h-4" />
                    <span>Volver</span>
                </Link>
            </div>

            <div class="pointer-events-auto text-right">
                <p class="text-[10px] uppercase tracking-[0.25em] text-slate-300 mb-1">
                    Mapa
                </p>
                <h1 class="text-base sm:text-lg md:text-xl font-serif font-bold">
                    Próximos eventos
                </h1>
            </div>
        </header>

        <!-- Contenido principal: el mapa ocupa todo -->
        <main class="w-full h-screen">
            <div class="w-full h-full">
                <slot />
            </div>
        </main>

        <!-- FOOTER-FUNCIÓN-HEADER: Barra de navegación inferior flotante -->
        <nav
            class="fixed bottom-0 left-0 right-0 z-30 bg-black/80 backdrop-blur-sm border-t border-slate-800 px-4 sm:px-6 lg:px-8 py-3">
            <div
                class="max-w-4xl mx-auto flex items-center justify-between gap-4 text-[11px] sm:text-xs uppercase tracking-[0.2em] text-slate-300">

                <!-- Sección izquierda: navegación principal -->
                <div class="flex items-center gap-5 sm:gap-8">
                    <Link href="/" class="flex items-center gap-1 hover:text-white transition">
                        <HomeIcon class="w-4 h-4" />
                        <span class="hidden xs:inline">Inicio</span>
                    </Link>

                    <Link :href="route('events.index')" class="flex items-center gap-1 hover:text-white transition">
                        <CalendarIcon class="w-4 h-4" />
                        <span class="hidden xs:inline">Eventos</span>
                    </Link>

                    <Link :href="route('gallery.index')" class="flex items-center gap-1 hover:text-white transition">
                        <PhotoIcon class="w-4 h-4" />
                        <span class="hidden xs:inline">Galería</span>
                    </Link>

                    <Link :href="route('photographers.index')"
                        class="flex items-center gap-1 hover:text-white transition">
                        <UsersIcon class="w-4 h-4" />
                        <span class="hidden xs:inline">Fotógrafos</span>
                    </Link>

                    <!-- Vista actual (mapa) -->
                    <Link :href="route('future-events.map')"
                        class="flex items-center gap-1 text-blue-400 hover:text-blue-300 transition">
                        <MapIcon class="w-4 h-4" />
                        <span class="hidden xs:inline">Mapa</span>
                    </Link>
                </div>

                <!-- Sección derecha: usuario o CTA -->
                <div class="flex items-center gap-4">
                    <!-- Si hay usuario -->
                    <template v-if="user">
                        <span class="hidden sm:inline text-[10px] text-slate-400 truncate max-w-[150px]">
                            {{ user.name }}
                        </span>
                        <Link v-if="user.role === 'photographer'" :href="route('photographer.dashboard')"
                            class="text-[10px] sm:text-xs font-bold uppercase tracking-[0.2em] text-white hover:text-slate-200 transition">
                            Panel Pro
                        </Link>
                        <Link v-else-if="user.is_admin" :href="route('admin.dashboard')"
                            class="text-[10px] sm:text-xs font-bold uppercase tracking-[0.2em] text-white hover:text-slate-200 transition">
                            Admin
                        </Link>
                        <Link :href="route('logout')" method="post" as="button"
                            class="text-[10px] sm:text-xs font-bold uppercase tracking-[0.2em] text-red-400 hover:text-red-200 transition">
                            Salir
                        </Link>
                    </template>

                    <!-- Si NO hay usuario -->
                    <template v-else>
                        <Link :href="route('photographer.register')"
                            class="hidden sm:inline text-[10px] sm:text-xs font-bold uppercase tracking-[0.2em] text-slate-200 hover:text-white transition">
                            Soy fotógrafo
                        </Link>
                        <Link :href="route('login')"
                            class="text-[10px] sm:text-xs font-bold uppercase tracking-[0.2em] text-white hover:text-slate-200 transition">
                            Ingresar
                        </Link>
                    </template>
                </div>
            </div>
        </nav>

        <!-- Toast & Confirm -->
        <ToastContainer />
        <ConfirmDialog :show="confirmState.show" :title="confirmState.title" :message="confirmState.message"
            :confirm-text="confirmState.confirmText" :cancel-text="confirmState.cancelText" :type="confirmState.type"
            @confirm="handleConfirm" @cancel="handleCancel" @close="handleCancel" />
    </div>
</template>
<style>
.leaflet-control-zoom.leaflet-bar.leaflet-control {
    bottom: 40px !important;
    /* Ajusta según altura aproximada de tu footer */
}
</style>
