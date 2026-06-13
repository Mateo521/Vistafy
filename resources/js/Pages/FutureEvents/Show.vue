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


const getDaysText = computed(() => {
    const days = Math.round(props.event.days_until);

    if (days === 0) return 'HOY';
    if (days === 1) return 'MAÑANA';
    if (days < 0) return `HACE ${Math.abs(days)} DÍAS`;
    if (days > 30) {
        const months = Math.round(days / 30);
        return months === 1 ? 'EN 1 MES' : `EN ${months} MESES`;
    }

    return `EN ${days} DÍAS`;
});


const getDaysBadgeColor = computed(() => {
    const days = Math.round(props.event.days_until);

    if (days < 0) return 'border-zinc-800 text-zinc-500 bg-black'; // Pasado
    if (days === 0) return 'border-[#E30613] text-black bg-[#E30613] animate-pulse'; // Hoy
    if (days === 1) return 'border-[#E30613] text-[#E30613] bg-[#E30613]/10'; // Mañana
    if (days <= 7) return 'border-white text-white bg-white/5'; // Próximo
    return 'border-zinc-600 text-zinc-400 bg-black'; // Lejano
});

const handleImageError = (e) => {
    e.target.style.display = 'none';
    const parent = e.target.parentElement;
    if (parent && !parent.querySelector('.placeholder')) {
        const placeholder = document.createElement('div');
        placeholder.className = 'placeholder w-full h-full flex items-center justify-center bg-black border-b border-zinc-800';
        placeholder.innerHTML = `
            <span class="font-mono text-xs font-bold tracking-widest uppercase text-zinc-800">>_ NO_IMG_DATA</span>
        `;
        parent.appendChild(placeholder);
    }
};
</script>

<template>

    <Head :title="`[EVT] ${event.title}`" />

    <AppLayout>
        <section class="relative min-h-[70vh] bg-[#050505] overflow-hidden border-b-2 border-[#E30613]">
            <div class="absolute inset-0 bg-black">
                <img :src="event.cover_image" :alt="event.title" 
                    class="w-full h-full object-cover filter grayscale opacity-40 mix-blend-screen"
                    @error="handleImageError" />
            </div>

            <div class="absolute inset-0 bg-[url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAiIGhlaWdodD0iMjAiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PGNpcmNsZSBjeD0iMSIgY3k9IjEiIHI9IjEiIGZpbGw9InJnYmEoMjU1LDI1NSwyNTUsMC4wNSkiLz48L3N2Zz4=')]"></div>

            <div class="absolute top-32 left-0 w-full px-4 sm:px-6 lg:px-8 z-10 max-w-7xl mx-auto">
                <Link :href="route('home')"
                    class="inline-flex items-center text-zinc-500 hover:text-white font-mono text-[10px] font-bold uppercase tracking-widest transition-colors group bg-black border border-zinc-800 px-4 py-2 hover:border-white">
                    <ArrowLeftIcon class="w-3 h-3 mr-2 group-hover:-translate-x-1 transition-transform" />
                    CANCELAR / VOLVER
                </Link>
            </div>

            <div class="relative z-10 h-full min-h-[70vh] flex items-end">
                <div class="w-full px-4 sm:px-6 lg:px-8 py-16">
                    <div class="max-w-7xl mx-auto">
                        
                        <div class="mb-6 flex gap-3">
                            <span :class="[
                                'inline-flex items-center px-4 py-1.5 rounded-none border font-mono text-[10px] font-bold uppercase tracking-widest',
                                getDaysBadgeColor
                            ]">
                                <ClockIcon class="w-3.5 h-3.5 mr-2" />
                                {{ getDaysText }}
                            </span>
                            <span v-if="event.is_private" class="inline-flex items-center px-4 py-1.5 rounded-none border border-[#E30613] text-[#E30613] bg-black font-mono text-[10px] font-bold uppercase tracking-widest">
                                PRIVADO
                            </span>
                        </div>

                        <h1 class="text-5xl md:text-7xl lg:text-8xl font-flux text-white mb-6 max-w-5xl leading-none uppercase tracking-tighter mix-blend-difference">
                            {{ event.title }}
                        </h1>

                        <div class="flex flex-wrap items-center gap-6 font-mono text-[10px] font-bold uppercase tracking-widest text-zinc-400">
                            <div class="flex items-center gap-2 bg-black/80 border border-zinc-800 px-3 py-1.5">
                                <CalendarIcon class="w-4 h-4 text-[#E30613]" />
                                <span>{{ event.formatted_date }} <span class="mx-2 text-zinc-700">|</span> {{ event.formatted_time }}HS</span>
                            </div>
                            <div class="flex items-center gap-2 bg-black/80 border border-zinc-800 px-3 py-1.5">
                                <MapPinIcon class="w-4 h-4 text-[#E30613]" />
                                <span>{{ event.location }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="bg-[#050505] py-16 selection:bg-[#E30613] selection:text-black">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-16 lg:gap-8">

                    <div class="lg:col-span-2">
                        <h2 class="font-mono text-[10px] font-bold uppercase tracking-widest text-[#E30613] mb-6 flex items-center gap-3">
                            >_ EVENTO
                        </h2>
                        
                        <div class="mb-16">
                            <p class="font-mono text-xs md:text-sm text-zinc-400 leading-relaxed uppercase border-l-2 border-zinc-800 pl-4">
                                {{ event.description }}
                            </p>
                        </div>

                        <div class="mb-12 p-8 bg-[#09090b] border border-zinc-800 relative overflow-hidden group hover:border-white transition-colors">
                            <CameraIcon class="absolute -right-8 -bottom-8 w-48 h-48 text-zinc-900 opacity-50 transform -rotate-12 group-hover:scale-110 group-hover:text-zinc-800 transition-all duration-700 pointer-events-none" />
                            
                            <div class="relative z-10 flex flex-col sm:flex-row items-start gap-6">
                                <div class="text-white bg-black border border-zinc-800 p-3 shrink-0 group-hover:border-[#E30613] group-hover:text-[#E30613] transition-colors">
                                    <CameraIcon class="w-6 h-6" />
                                </div>
                                <div>
                                    <h3 class="font-flux text-3xl text-white mb-2 uppercase tracking-wider">
                                        FOTOGRAFÍAS PENDIENTES
                                    </h3>
                                    <p class="font-mono text-[10px] text-zinc-500 leading-relaxed max-w-xl uppercase tracking-widest">
                                        Las fotografias van a estar disponibles en este evento. <span class="text-[#E30613] font-bold">> STATUS: EN ESPERA.</span>
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="p-10 bg-black border border-[#E30613] shadow-[8px_8px_0px_0px_rgba(227,6,19,1)] relative overflow-hidden">
                            <div class="absolute top-0 right-0 bg-[#E30613] text-black font-mono text-[8px] font-bold uppercase tracking-widest px-2 py-1">
                                SISTEMA ABIERTO
                            </div>
                            
                            <h3 class="font-flux text-4xl md:text-5xl text-white mb-4 uppercase tracking-tight">
                                ¿SOS FOTÓGRAFO? <span class="text-[#E30613]">ASIGNATE A ESTE EVENTO</span>
                            </h3>
                            <p class="font-mono text-xs text-zinc-400 mb-8 max-w-2xl leading-relaxed uppercase">
                                Se requieren operadores de cámara. Envía tu solicitud de cobertura para este evento, documenta la instancia y comercializa tus capturas mediante la red f33.
                            </p>
                            
                            <div class="flex flex-wrap gap-4">
                                <button v-if="isPhotographer"
                                    class="inline-flex items-center gap-3 px-8 py-4 bg-[#E30613] text-black font-mono text-[10px] font-bold uppercase tracking-widest hover:bg-white transition-colors rounded-none border border-[#E30613] hover:border-white group">
                                    POSTULACIÓN
                                    <ArrowRightIcon class="w-4 h-4 group-hover:translate-x-1 transition-transform" />
                                </button>

                                <template v-else>
                                    <Link :href="route('photographer.register')"
                                        class="px-8 py-4 bg-[#E30613] text-black font-mono text-[10px] font-bold uppercase tracking-widest hover:bg-white transition-colors rounded-none border border-[#E30613] hover:border-white text-center">
                                        INICIAR CUENTA FOTÓGRAFO
                                    </Link>
                                    <Link :href="route('login')"
                                        class="px-8 py-4 bg-black border border-zinc-700 text-zinc-400 font-mono text-[10px] font-bold uppercase tracking-widest hover:border-white hover:text-white transition-colors rounded-none text-center">
                                        LOGIN
                                    </Link>
                                </template>
                            </div>
                        </div>
                    </div>

                    <div class="lg:col-span-1">
                        <div class="sticky top-32 space-y-6">
                            
                            <div class="bg-[#09090b] border border-zinc-800 p-8 shadow-[4px_4px_0px_0px_rgba(255,255,255,0.05)]">
                                <h3 class="font-mono text-[10px] font-bold uppercase tracking-widest text-zinc-500 mb-6 border-b border-zinc-800 pb-4 flex items-center gap-2">
                                    <UserIcon class="w-3 h-3 text-[#E30613]" />  ORGANIZADOR
                                </h3>
                                <div class="flex flex-col gap-4">
                                    <div>
                                        <h4 class="font-flux text-3xl text-white uppercase tracking-wider mb-1">
                                            {{ event.photographer.business_name }}
                                        </h4>
                                        <p class="font-mono text-[10px] text-zinc-500 uppercase tracking-widest mb-4">
                                            > OP: {{ event.photographer.name }}
                                        </p>
                                    </div>
                                    <a :href="`mailto:${event.photographer.email}`"
                                        class="inline-flex items-center justify-center gap-2 w-full px-4 py-3 bg-black border border-zinc-700 text-[10px] font-mono font-bold uppercase tracking-widest text-zinc-300 hover:text-white hover:border-[#E30613] transition-colors">
                                        <EnvelopeIcon class="w-3.5 h-3.5" />
                                        CONTACTAR
                                    </a>
                                </div>
                            </div>

                            <div class="bg-[#09090b] border border-zinc-800 p-8 shadow-[4px_4px_0px_0px_rgba(255,255,255,0.05)]">
                                <h3 class="font-mono text-[10px] font-bold uppercase tracking-widest text-zinc-500 mb-6 border-b border-zinc-800 pb-4 flex items-center gap-2">
                                    <ClockIcon class="w-3 h-3 text-[#E30613]" /> FICHA
                                </h3>
                                <div class="space-y-6">
                                    <div>
                                        <div class="font-mono text-[9px] font-bold uppercase tracking-widest text-zinc-600 mb-1">
                                            >_ FECHA
                                        </div>
                                        <p class="font-mono text-sm text-white uppercase">
                                            {{ event.formatted_date }}
                                        </p>
                                    </div>
                                    <div>
                                        <div class="font-mono text-[9px] font-bold uppercase tracking-widest text-zinc-600 mb-1">
                                            >_ HORA_COMIENZO
                                        </div>
                                        <p class="font-mono text-sm text-white uppercase">
                                            {{ event.formatted_time }}HS
                                        </p>
                                    </div>
                                    <div>
                                        <div class="font-mono text-[9px] font-bold uppercase tracking-widest text-zinc-600 mb-1">
                                            >_ COORDENADAS_MAPA
                                        </div>
                                        <p class="font-mono text-xs text-white uppercase leading-relaxed">
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
    </AppLayout>
</template>