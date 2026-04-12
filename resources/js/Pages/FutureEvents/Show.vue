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
    ArrowRightIcon,
    CameraIcon
} from '@heroicons/vue/24/outline';

const props = defineProps({
    event: Object,
    isPhotographer: Boolean,
    isAuthenticated: Boolean,
});

/**
 * Formatear días hasta el evento
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
 * Color del badge según proximidad (Adaptado al Dark Theme)
 */
const getDaysBadgeColor = computed(() => {
    const days = Math.round(props.event.days_until);

    if (days < 0) return 'border-[#C9C1B1]/30 text-[#C9C1B1] bg-[#1B2632]';
    if (days === 0) return 'border-[#FFB162] text-[#FFB162] bg-[#FFB162]/10';
    if (days === 1) return 'border-[#FFB162]/70 text-[#FFB162] bg-[#FFB162]/5';
    if (days <= 7) return 'border-[#EEE9DF]/50 text-[#EEE9DF] bg-[#EEE9DF]/5';
    return 'border-[#C9C1B1]/40 text-[#EEE9DF] bg-[#1B2632]/50';
});

const handleImageError = (e) => {
    e.target.style.display = 'none';
    const parent = e.target.parentElement;
    if (parent && !parent.querySelector('.placeholder')) {
        const placeholder = document.createElement('div');
        placeholder.className = 'placeholder w-full h-full flex items-center justify-center bg-[#1B2632]';
        placeholder.innerHTML = `
            <span class="font-['Cormorant_Garamond'] text-6xl italic opacity-20 text-[#EEE9DF]">f33</span>
        `;
        parent.appendChild(placeholder);
    }
};
</script>

<template>

    <Head :title="event.title" />

    <AppLayout>
        <section class="relative min-h-[70vh] bg-[#111920] overflow-hidden font-['Syne']">
            <div class="absolute inset-0">
                <img :src="event.cover_image" :alt="event.title" class="w-full h-full object-cover opacity-40 saturate-[0.6]"
                    @error="handleImageError" />
            </div>

            <div class="absolute inset-0 bg-gradient-to-t from-[#111920] via-[#111920]/60 to-transparent"></div>

            <div class="absolute top-32 left-0 w-full px-8 md:px-16 z-10 max-w-7xl mx-auto left-0 right-0">
                <Link :href="route('home')"
                    class="inline-flex items-center text-[#C9C1B1] hover:text-[#FFB162] text-[10px] font-bold uppercase tracking-[0.2em] transition-colors group">
                    <ArrowLeftIcon class="w-4 h-4 mr-2 group-hover:-translate-x-1 transition-transform" />
                    Volver al inicio
                </Link>
            </div>

            <div class="relative z-10 h-full min-h-[70vh] flex items-end">
                <div class="w-full px-8 md:px-16 py-16">
                    <div class="max-w-7xl mx-auto">
                        <div class="mb-6">
                            <span :class="[
                                'inline-flex items-center px-4 py-2 rounded-none border text-[9px] font-bold uppercase tracking-[0.2em] backdrop-blur-sm',
                                getDaysBadgeColor
                            ]">
                                <ClockIcon class="w-3.5 h-3.5 mr-2" />
                                {{ getDaysText }}
                            </span>
                        </div>

                        <h1 class="text-5xl md:text-7xl lg:text-8xl font-['Cormorant_Garamond'] font-light text-[#EEE9DF] mb-6 max-w-5xl leading-[0.95]">
                            {{ event.title }}
                        </h1>

                        <div class="flex flex-wrap items-center gap-6 text-[#C9C1B1] text-[11px] font-bold uppercase tracking-[0.2em]">
                            <div class="flex items-center gap-2">
                                <CalendarIcon class="w-4 h-4 text-[#FFB162]" />
                                <span>{{ event.formatted_date }} <span class="mx-1 text-[#C9C1B1]/30">|</span> {{ event.formatted_time }}hs</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <MapPinIcon class="w-4 h-4 text-[#FFB162]" />
                                <span>{{ event.location }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="bg-[#111920] py-16 font-['Syne']">
            <div class="max-w-7xl mx-auto px-8 md:px-16">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-16 lg:gap-24">

                    <div class="lg:col-span-2">
                        <h2 class="text-[9px] font-bold uppercase tracking-[0.35em] text-[#FFB162] mb-6 flex items-center gap-3 after:content-[''] after:h-px after:w-12 after:bg-[#A35139]">
                            Sobre el Evento
                        </h2>
                        
                        <div class="prose prose-lg max-w-none mb-16 text-[#C9C1B1]/90">
                            <p class="leading-relaxed text-[15px] md:text-lg font-light">
                                {{ event.description }}
                            </p>
                        </div>

                        <div class="mb-12 p-8 bg-[#1B2632] border border-[#FFB162]/20 flex flex-col sm:flex-row items-start gap-6 relative overflow-hidden group">
                            <CameraIcon class="absolute -right-8 -bottom-8 w-48 h-48 text-[#FFB162] opacity-[0.03] transform -rotate-12 group-hover:scale-110 transition-transform duration-700" />
                            
                            <div class="text-[#FFB162] mt-1 shrink-0">
                                <CameraIcon class="w-8 h-8" />
                            </div>
                            <div class="relative z-10">
                                <h3 class="font-['Cormorant_Garamond'] text-2xl md:text-3xl text-[#EEE9DF] mb-2 italic">
                                    ¡Próximamente las fotos oficiales!
                                </h3>
                                <p class="text-[#C9C1B1] text-sm leading-relaxed max-w-xl">
                                    Asistí al evento, disfrutá el momento y sonreí. Una vez terminado, vas a poder buscar y adquirir tus mejores fotos directamente en esta página con la calidad de <span class="text-[#FFB162] font-bold">f33</span>.
                                </p>
                            </div>
                        </div>

                        <div class="p-10 bg-[#2C3B4D] border-l-4 border-[#FFB162] shadow-2xl relative">
                            <h3 class="font-['Cormorant_Garamond'] text-3xl md:text-4xl text-[#EEE9DF] mb-4">
                                ¿Sos fotógrafo? <em class="italic text-[#FFB162]">Cubrí este evento</em>
                            </h3>
                            <p class="text-[#C9C1B1] text-sm mb-8 max-w-2xl leading-relaxed">
                                Buscamos talentos para capturar los mejores momentos. Postulate, amplía tu portafolio y generá ingresos vendiendo tus fotos a los asistentes a través de nuestra plataforma.
                            </p>
                            
                            <div class="flex flex-wrap gap-4">
                                <button v-if="isPhotographer"
                                    class="inline-flex items-center gap-3 px-8 py-4 bg-[#FFB162] text-[#1B2632] font-bold text-[10px] uppercase tracking-[0.2em] hover:bg-[#EEE9DF] transition-colors rounded-sm shadow-lg group">
                                    Postularme al Evento
                                    <ArrowRightIcon class="w-4 h-4 group-hover:translate-x-1 transition-transform" />
                                </button>

                                <template v-else>
                                    <Link :href="route('photographer.register')"
                                        class="px-8 py-4 bg-[#FFB162] text-[#1B2632] font-bold text-[10px] uppercase tracking-[0.2em] hover:bg-[#EEE9DF] transition-colors rounded-sm shadow-lg text-center">
                                        Crear cuenta de Fotógrafo
                                    </Link>
                                    <Link :href="route('login')"
                                        class="px-8 py-4 border border-[#C9C1B1]/30 text-[#C9C1B1] font-bold text-[10px] uppercase tracking-[0.2em] hover:border-[#FFB162] hover:text-[#FFB162] transition-colors rounded-sm text-center">
                                        Ya tengo cuenta
                                    </Link>
                                </template>
                            </div>
                        </div>
                    </div>

                    <div class="lg:col-span-1">
                        <div class="sticky top-32 space-y-6">
                            
                            <div class="bg-[#1B2632] border border-[#C9C1B1]/10 p-8">
                                <h3 class="text-[9px] font-bold uppercase tracking-[0.3em] text-[#C9C1B1] mb-6 border-b border-[#C9C1B1]/10 pb-4">
                                    Organizado Por
                                </h3>
                                <div class="flex items-start gap-4">
                                    <div class="flex-shrink-0">
                                        <div class="w-14 h-14 bg-[#111920] border border-[#FFB162]/20 rounded-full flex items-center justify-center">
                                            <UserIcon class="w-6 h-6 text-[#FFB162]" />
                                        </div>
                                    </div>
                                    <div class="pt-1">
                                        <h4 class="font-bold text-[#EEE9DF] text-sm tracking-wide mb-1">
                                            {{ event.photographer.business_name }}
                                        </h4>
                                        <p class="text-xs text-[#C9C1B1] mb-4">
                                            {{ event.photographer.name }}
                                        </p>
                                        <a :href="`mailto:${event.photographer.email}`"
                                            class="inline-flex items-center gap-2 text-[10px] font-bold uppercase tracking-widest text-[#FFB162] hover:text-[#EEE9DF] transition-colors pb-1 border-b border-[#FFB162]/30 hover:border-[#EEE9DF]/50">
                                            <EnvelopeIcon class="w-3.5 h-3.5" />
                                            Contactar
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <div class="bg-[#1B2632] border border-[#C9C1B1]/10 p-8">
                                <h3 class="text-[9px] font-bold uppercase tracking-[0.3em] text-[#C9C1B1] mb-6 border-b border-[#C9C1B1]/10 pb-4">
                                    Detalles del Evento
                                </h3>
                                <div class="space-y-6">
                                    <div>
                                        <div class="flex items-center gap-2 text-[#C9C1B1]/60 text-[9px] uppercase tracking-[0.2em] mb-1.5">
                                            <CalendarIcon class="w-3.5 h-3.5" /> Fecha
                                        </div>
                                        <p class="text-[#EEE9DF] text-sm font-medium">
                                            {{ event.formatted_date }}
                                        </p>
                                    </div>
                                    <div>
                                        <div class="flex items-center gap-2 text-[#C9C1B1]/60 text-[9px] uppercase tracking-[0.2em] mb-1.5">
                                            <ClockIcon class="w-3.5 h-3.5" /> Hora
                                        </div>
                                        <p class="text-[#EEE9DF] text-sm font-medium">
                                            {{ event.formatted_time }}hs
                                        </p>
                                    </div>
                                    <div>
                                        <div class="flex items-center gap-2 text-[#C9C1B1]/60 text-[9px] uppercase tracking-[0.2em] mb-1.5">
                                            <MapPinIcon class="w-3.5 h-3.5" /> Ubicación
                                        </div>
                                        <p class="text-[#EEE9DF] text-sm font-medium leading-relaxed">
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

        <section v-if="isPhotographer" class="bg-[#1B2632] py-24 border-t border-[#C9C1B1]/10 relative overflow-hidden">
            <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 font-['Cormorant_Garamond'] text-[120px] md:text-[200px] font-light text-[#111920] opacity-50 leading-none pointer-events-none whitespace-nowrap">
                UNIRSE
            </div>
            
            <div class="max-w-3xl mx-auto px-8 relative z-10 text-center font-['Syne']">
                <h2 class="font-['Cormorant_Garamond'] text-4xl md:text-5xl font-light text-[#EEE9DF] mb-4">
                    No te pierdas esta <em class="italic text-[#FFB162]">oportunidad</em>
                </h2>
                <p class="text-[#C9C1B1] text-sm tracking-wide mb-10">
                    Postulate ahora y formá parte de la cobertura oficial de {{ event.title }}.
                </p>
                <button
                    class="inline-flex items-center justify-center gap-3 px-10 py-5 bg-[#FFB162] text-[#1B2632] font-bold text-[10px] uppercase tracking-[0.25em] hover:bg-[#EEE9DF] transition-all rounded-sm shadow-2xl group">
                    Postularme Ahora
                    <ArrowRightIcon class="w-4 h-4 group-hover:translate-x-1 transition-transform" />
                </button>
            </div>
        </section>
    </AppLayout>
</template>