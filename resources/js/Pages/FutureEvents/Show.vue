<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import {
    ArrowLeftIcon,
    CalendarIcon,
    MapPinIcon,
    ClockIcon,
    UserIcon,
    EnvelopeIcon,
    ArrowRightIcon
} from '@heroicons/vue/24/outline';

const props = defineProps({
    event: Object,
    isPhotographer: Boolean,
    isAuthenticated: Boolean,
});

/**
 *  Formatear días hasta el evento
 */
const getDaysText = computed(() => {
    const days = Math.round(props.event.days_until);

    if (days === 0) return 'Hoy';
    if (days === 1) return 'Mañana';
    if (days < 0) return `Hace ${Math.abs(days)} días`;
    if (days > 30) {
        const months = Math.round(days / 30);
        return months === 1 ? 'En 1 mes' : `En ${months} meses`;
    }

    return `En ${days} días`;
});

/**
 *  Color del badge según proximidad
 */
const getDaysBadgeColor = computed(() => {
    const days = Math.round(props.event.days_until);

    if (days < 0) return 'bg-gray-100 text-gray-700';
    if (days === 0) return 'bg-red-100 text-red-700';
    if (days === 1) return 'bg-orange-100 text-orange-700';
    if (days <= 7) return 'bg-yellow-100 text-yellow-700';
    if (days <= 30) return 'bg-blue-100 text-blue-700';
    return 'bg-green-100 text-green-700';
});

const handleImageError = (e) => {
    e.target.style.display = 'none';
    const parent = e.target.parentElement;
    if (parent && !parent.querySelector('.placeholder')) {
        const placeholder = document.createElement('div');
        placeholder.className = 'placeholder w-full h-full flex items-center justify-center bg-gray-100';
        placeholder.innerHTML = `
            <svg class="w-24 h-24 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1"
                    d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
        `;
        parent.appendChild(placeholder);
    }
};
</script>

<template>

    <Head :title="event.title" />

    <AppLayout>
        <!--  HERO: Imagen Completa del Evento -->
        <section class="relative min-h-[70vh] bg-slate-900 overflow-hidden">
            <!-- Imagen de Fondo -->
            <div class="absolute inset-0">
                <img :src="event.cover_image" :alt="event.title" class="w-full h-full object-cover opacity-50"
                    @error="handleImageError" />
            </div>

            <!-- Overlay Gradient -->
            <div class="absolute inset-0 bg-gradient-to-b from-slate-900/50 via-slate-900/70 to-slate-900"></div>

            <!-- Botón Volver -->
            <div class="absolute top-24 left-0 w-full px-4 sm:px-6 lg:px-8 z-10">
                <Link :href="route('home')"
                    class="inline-flex items-center text-white/70 hover:text-white text-xs font-bold uppercase tracking-widest transition-colors group">
                    <ArrowLeftIcon class="w-4 h-4 mr-2 group-hover:-translate-x-1 transition-transform" />
                    Volver
                </Link>
            </div>

            <!-- Contenido Principal -->
            <div class="relative z-10 h-full min-h-[70vh] flex items-end">
                <div class="w-full px-4 sm:px-6 lg:px-8 py-12">
                    <div class="max-w-7xl mx-auto">
                        <!-- Badge de Proximidad -->
                        <div class="mb-4">
                            <span :class="[
                                'inline-flex items-center px-4 py-2 rounded-sm text-xs font-bold uppercase tracking-wider',
                                getDaysBadgeColor
                            ]">
                                <ClockIcon class="w-4 h-4 mr-2" />
                                {{ getDaysText }}
                            </span>
                        </div>

                        <!-- Título -->
                        <h1
                            class="text-4xl md:text-6xl lg:text-7xl font-serif font-bold text-white mb-6 max-w-7xl leading-tight">
                            {{ event.title }}
                        </h1>

                        <!-- Meta Info -->
                        <div class="flex flex-wrap items-center gap-6 text-white/90 text-base md:text-lg">
                            <div class="flex items-center gap-2">
                                <CalendarIcon class="w-5 h-5" />
                                <span>{{ event.formatted_date }} · {{ event.formatted_time }}hs</span>
                            </div>
                            <span class="w-1 h-1 bg-white/50 rounded-full"></span>
                            <div class="flex items-center gap-2">
                                <MapPinIcon class="w-5 h-5" />
                                <span>{{ event.location }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!--  DETALLES DEL EVENTO -->
        <section class="bg-white py-16">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-12">

                    <!-- Columna Principal: Descripción -->
                    <div class="lg:col-span-2">
                        <h2 class="text-xs font-bold uppercase tracking-widest text-slate-400 mb-4">
                            Sobre el Evento
                        </h2>
                        <div class="prose prose-lg max-w-none">
                            <p class="text-slate-700 leading-relaxed text-lg">
                                {{ event.description }}
                            </p>
                        </div>

                        <!-- CTA para Fotógrafos -->
                        <div v-if="isPhotographer" class="mt-12 p-8 bg-slate-900 text-white rounded-sm">
                            <h3 class="text-2xl font-serif font-bold mb-4">
                                ¿Querés ser parte de este evento?
                            </h3>
                            <p class="text-slate-300 mb-6">
                                Postulate para cubrir este evento y amplía tu portafolio profesional.
                            </p>
                            <button
                                class="inline-flex items-center gap-2 px-8 py-4 bg-white text-slate-900 font-bold text-sm uppercase tracking-widest hover:bg-slate-100 transition rounded-sm shadow-lg group">
                                Postularme al Evento
                                <ArrowRightIcon class="w-4 h-4 group-hover:translate-x-1 transition-transform" />
                            </button>
                        </div>

                        <!-- CTA para No Fotógrafos -->
                        <div v-else
                            class="mt-12 p-8 border-2 border-dashed border-gray-300 bg-gray-50 rounded-sm text-center">
                            <h3 class="text-xl font-serif font-bold text-slate-900 mb-3">
                                ¿Sos Fotógrafo?
                            </h3>
                            <p class="text-slate-600 mb-6">
                                Registrate para poder postularte a eventos como este
                            </p>
                            <div class="flex gap-4 justify-center flex-wrap">
                                <Link v-if="!isAuthenticated" :href="route('photographer.register')"
                                    class="px-6 py-3 bg-slate-900 text-white font-bold text-sm uppercase tracking-widest hover:bg-slate-800 transition rounded-sm">
                                    Registrarme
                                </Link>
                                <Link v-if="!isAuthenticated" :href="route('login')"
                                    class="px-6 py-3 border-2 border-slate-900 text-slate-900 font-bold text-sm uppercase tracking-widest hover:bg-slate-900 hover:text-white transition rounded-sm">
                                    Iniciar Sesión
                                </Link>
                                <div v-else class="text-slate-600 text-sm">
                                    Contactá al administrador para cambiar tu rol a fotógrafo
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Sidebar: Info del Organizador -->
                    <div class="lg:col-span-1">
                        <div class="sticky top-24">
                            <!-- Card: Organizador -->
                            <div class="bg-gray-50 border border-gray-200 rounded-sm p-6 mb-6">
                                <h3 class="text-xs font-bold uppercase tracking-widest text-slate-400 mb-4">
                                    Organizado Por
                                </h3>
                                <div class="flex items-start gap-4">
                                    <div class="flex-shrink-0">
                                        <div class="w-12 h-12 bg-slate-900 rounded-sm flex items-center justify-center">
                                            <UserIcon class="w-6 h-6 text-white" />
                                        </div>
                                    </div>
                                    <div>
                                        <h4 class="font-bold text-slate-900 mb-1">
                                            {{ event.photographer.business_name }}
                                        </h4>
                                        <p class="text-sm text-slate-600 mb-3">
                                            {{ event.photographer.name }}
                                        </p>
                                        <a :href="`mailto:${event.photographer.email}`"
                                            class="inline-flex items-center gap-2 text-xs text-slate-600 hover:text-slate-900 transition">
                                            <EnvelopeIcon class="w-4 h-4" />
                                            Contactar
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <!-- Card: Detalles Rápidos -->
                            <div class="bg-slate-900 text-white rounded-sm p-6">
                                <h3 class="text-xs font-bold uppercase tracking-widest text-white/60 mb-4">
                                    Detalles del Evento
                                </h3>
                                <div class="space-y-4">
                                    <div>
                                        <div
                                            class="flex items-center gap-2 text-white/60 text-xs uppercase tracking-wide mb-1">
                                            <CalendarIcon class="w-4 h-4" />
                                            Fecha
                                        </div>
                                        <p class="text-white font-medium">
                                            {{ event.formatted_date }}
                                        </p>
                                    </div>
                                    <div>
                                        <div
                                            class="flex items-center gap-2 text-white/60 text-xs uppercase tracking-wide mb-1">
                                            <ClockIcon class="w-4 h-4" />
                                            Hora
                                        </div>
                                        <p class="text-white font-medium">
                                            {{ event.formatted_time }}hs
                                        </p>
                                    </div>
                                    <div>
                                        <div
                                            class="flex items-center gap-2 text-white/60 text-xs uppercase tracking-wide mb-1">
                                            <MapPinIcon class="w-4 h-4" />
                                            Ubicación
                                        </div>
                                        <p class="text-white font-medium">
                                            {{ event.location }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </section>

        <!--  CTA Final -->
        <section v-if="isPhotographer" class="bg-gradient-to-r from-slate-900 to-slate-800 py-16">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                <h2 class="text-3xl md:text-4xl font-serif font-bold text-white mb-4">
                    No te pierdas esta oportunidad
                </h2>
                <p class="text-slate-300 text-lg mb-8">
                    Postulate ahora y formá parte de {{ event.title }}
                </p>
                <button
                    class="inline-flex items-center gap-2 px-10 py-5 bg-white text-slate-900 font-bold text-base uppercase tracking-widest hover:bg-slate-100 transition rounded-sm shadow-2xl group">
                    Postularme Ahora
                    <ArrowRightIcon class="w-5 h-5 group-hover:translate-x-1 transition-transform" />
                </button>
            </div>
        </section>
    </AppLayout>
</template>
