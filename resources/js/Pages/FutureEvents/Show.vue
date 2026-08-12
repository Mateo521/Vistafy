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
    CameraIcon,
    InformationCircleIcon
} from '@heroicons/vue/24/outline';

const props = defineProps({
    event: Object,
    isPhotographer: Boolean,
    isAuthenticated: Boolean,
    userApplicationStatus: {
        type: String,
        default: 'none'
    }
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

    if (days < 0) return 'border-gray-200 text-gray-500 bg-gray-50'; // Pasado
    if (days === 0) return 'border-red-200 text-[#E30613] bg-red-50 animate-pulse'; // Hoy
    if (days === 1) return 'border-orange-200 text-orange-600 bg-orange-50'; // Mañana
    if (days <= 7) return 'border-blue-200 text-blue-600 bg-blue-50'; // Próximo
    return 'border-gray-200 text-gray-600 bg-white'; // Lejano
});

const handleImageError = (e) => {
    e.target.style.display = 'none';
    const parent = e.target.parentElement;
    if (parent && !parent.querySelector('.placeholder')) {
        const placeholder = document.createElement('div');
        placeholder.className = 'placeholder w-full h-full flex flex-col items-center justify-center bg-gray-100 text-gray-400';
        placeholder.innerHTML = `
            <svg class="w-12 h-12 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
            <span class="text-xs font-bold uppercase tracking-wider">Sin Portada</span>
        `;
        parent.appendChild(placeholder);
    }
};
</script>

<template>

    <Head :title="`${event.title} | F33`" />

    <AppLayout>
        <div class="min-h-screen bg-[#F8F9FA] text-slate-800 font-sans antialiased py-12 pt-28">
            <div class="max-w-[1500px] mx-auto px-4 sm:px-6 lg:px-8">


                <div class="mb-6">
                    <Link :href="route('home')"
                        class="inline-flex items-center gap-2 bg-white px-4 py-2 rounded-full text-xs font-bold uppercase tracking-wider text-gray-500 hover:text-black hover:shadow-sm border border-gray-200 transition-all">
                        <ArrowLeftIcon class="w-4 h-4" /> Volver al inicio
                    </Link>
                </div>


                <div
                    class="relative w-full h-[50vh] min-h-[400px] rounded overflow-hidden shadow-xl mb-12 flex flex-col justify-end group">

                    <div class="absolute inset-0 w-full h-full bg-slate-900">
                        <img :src="event.cover_image" :alt="event.title"
                            class="w-full h-full object-cover opacity-80 mix-blend-overlay transition-transform duration-1000 "
                            @error="handleImageError" />
                        <div class="absolute inset-0 bg-gradient-to-t from-black/90 via-black/30 to-transparent"></div>
                    </div>


                    <div class="relative z-10 p-8 md:p-16">
                        <div class="flex flex-wrap items-center gap-3 mb-6">
                            <span :class="[
                                'inline-flex items-center px-4 py-1.5 rounded-full text-xs font-bold uppercase tracking-wider border shadow-sm',
                                getDaysBadgeColor
                            ]">
                                <ClockIcon class="w-4 h-4 mr-1.5" />
                                {{ getDaysText }}
                            </span>
                            <span v-if="event.is_private"
                                class="inline-flex items-center px-4 py-1.5 rounded-full bg-red-50/20 backdrop-blur-md border border-[#E30613]/50 text-red-100 text-xs font-bold uppercase tracking-wider">
                                Privado
                            </span>
                        </div>

                        <h1
                            class="font-flux text-5xl md:text-7xl lg:text-8xl text-white leading-none tracking-wide mb-6">
                            {{ event.title }}
                        </h1>

                        <div
                            class="flex flex-wrap items-center gap-4 text-white/90 text-xs font-bold uppercase tracking-wider">
                            <div
                                class="flex items-center gap-2 bg-white/20 backdrop-blur-md border border-white/20 px-4 py-2 rounded-full">
                                <CalendarIcon class="w-4 h-4 text-[#E30613]" />
                                <span>{{ event.formatted_date }} <span class="mx-1 text-white/50">|</span> {{
                                    event.formatted_time }} HS</span>
                            </div>
                            <div
                                class="flex items-center gap-2 bg-white/20 backdrop-blur-md border border-white/20 px-4 py-2 rounded-full">
                                <MapPinIcon class="w-4 h-4 text-[#E30613]" />
                                <span class="truncate max-w-[200px] md:max-w-none">{{ event.location }}</span>
                            </div>
                        </div>
                    </div>
                </div>


                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 xl:gap-12 pb-16">


                    <div class="lg:col-span-2 space-y-8">


                        <div>
                            <h2
                                class="text-xs font-bold uppercase tracking-widest text-gray-400 mb-4 flex items-center gap-2">
                                <span class="w-4 h-px bg-gray-200"></span> Acerca del evento
                            </h2>
                            <p class="text-lg md:text-xl text-slate-700 leading-relaxed font-medium">
                                {{ event.description }}
                            </p>
                        </div>


                        <div
                            class="bg-blue-50 border border-blue-100 rounded p-6 md:p-8 flex flex-col sm:flex-row items-start gap-6 relative overflow-hidden">
                            <div
                                class="absolute top-0 right-0 w-32 h-32 bg-blue-100 rounded-full blur-3xl -mr-10 -mt-10 pointer-events-none">
                            </div>
                            <div
                                class="w-12 h-12 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center shrink-0 relative z-10">
                                <CameraIcon class="w-6 h-6" />
                            </div>
                            <div class="relative z-10">
                                <h3 class="font-bold text-xl text-slate-800 mb-2">
                                    Fotografías en espera
                                </h3>
                                <p class="text-sm text-blue-800/80 leading-relaxed">
                                    Las capturas fotográficas de este evento van a estar disponibles acá próximamente.
                                    <span class="font-bold text-blue-600 block mt-1">Estatus: Planificación /
                                        Organización.</span>
                                </p>
                            </div>
                        </div>


                        <div
                            class="bg-white border border-gray-100 shadow-sm rounded p-8 md:p-10 relative overflow-hidden group">
                            <div
                                class="absolute -right-10 -bottom-10 opacity-5  transition-transform duration-700 pointer-events-none">
                                <CameraIcon class="w-64 h-64 text-black" />
                            </div>

                            <div class="relative z-10">
                                <div
                                    class="inline-block bg-red-50 text-[#E30613] text-[10px] font-bold uppercase tracking-widest px-3 py-1.5 rounded-md border border-red-100 mb-4">
                                    Convocatoria abierta
                                </div>
                                <h3 class="font-flux text-4xl md:text-5xl text-black mb-4">
                                    ¿Sos fotógrafo? <span class="text-[#E30613] block md:inline">Participa.</span>
                                </h3>
                                <p class="text-sm text-gray-500 mb-8 max-w-2xl leading-relaxed font-medium">
                                    Se requieren operadores de cámara. Envía tu solicitud de cobertura, documentá la
                                    instancia y comercializá tus fotos.
                                </p>


                                <div class="flex flex-wrap gap-4">


                                    <template v-if="isPhotographer">

                                        <Link v-if="userApplicationStatus === 'none'"
                                            :href="route('photographer.future-events.apply', event.id)" method="post"
                                            as="button"
                                            class="inline-flex items-center justify-center gap-3 px-8 py-4 bg-black text-white font-bold text-xs uppercase tracking-wider rounded-full hover:bg-[#E30613] hover:shadow-lg hover:shadow-red-500/30 transition-all hover:-translate-y-1 group/btn">
                                            Enviar postulación
                                            <ArrowRightIcon
                                                class="w-4 h-4 group-hover/btn:translate-x-1 transition-transform" />
                                        </Link>


                                        <div v-else-if="userApplicationStatus === 'requested'"
                                            class="inline-flex items-center justify-center px-8 py-4 bg-orange-50 text-orange-600 border border-orange-200 font-bold text-xs uppercase tracking-wider rounded-full cursor-default">
                                            Postulación en revisión...
                                        </div>


                                        <div v-else-if="userApplicationStatus === 'approved'"
                                            class="inline-flex items-center justify-center px-8 py-4 bg-green-50 text-green-700 border border-green-200 font-bold text-xs uppercase tracking-wider rounded-full cursor-default">
                                            Colaborador confirmado
                                        </div>
                                    </template>

                                    <template v-else>
                                        <Link :href="route('photographer.register')"
                                            class="inline-flex items-center justify-center px-8 py-4 bg-[#E30613] text-white font-bold text-xs uppercase tracking-wider rounded-full hover:bg-red-700 hover:shadow-lg hover:shadow-red-500/30 transition-all hover:-translate-y-1 text-center">
                                            Crear cuenta profesional
                                        </Link>
                                        <Link :href="route('login')"
                                            class="inline-flex items-center justify-center px-8 py-4 bg-white border border-gray-200 text-slate-700 font-bold text-xs uppercase tracking-wider hover:bg-gray-50 hover:text-black transition-colors rounded-full text-center">
                                            Iniciar Sesión
                                        </Link>
                                    </template>


                                </div>



                            </div>
                        </div>

                    </div>


                    <div class="lg:col-span-1 space-y-6">


                        <div class="bg-white rounded p-6 md:p-8 border border-gray-100 shadow-sm">
                            <h3
                                class="text-xs font-bold uppercase tracking-widest text-gray-400 mb-6 flex items-center gap-2 border-b border-gray-100 pb-4">
                                <UserIcon class="w-4 h-4 text-[#E30613]" /> Organizador
                            </h3>

                            <div class="flex flex-col gap-5">
                                <div class="flex items-center gap-4">
                                    <div
                                        class="w-14 h-14 bg-gray-50 rounded-full border border-gray-200 flex items-center justify-center text-xl font-bold text-gray-400 shrink-0">
                                        {{ event.photographer.business_name.charAt(0) }}
                                    </div>
                                    <div class="min-w-0">
                                        <h4 class="font-bold text-lg text-black truncate">
                                            {{ event.photographer.business_name }}
                                        </h4>
                                        <p class="text-xs font-bold text-gray-400 uppercase tracking-wider truncate">
                                            Dirige: {{ event.photographer.name }}
                                        </p>
                                    </div>
                                </div>

                                <a :href="`mailto:${event.photographer.email}`"
                                    class="w-full flex items-center justify-center gap-2 px-4 py-3 bg-white border-2 border-gray-100 text-xs font-bold uppercase tracking-wider text-slate-600 hover:border-black hover:text-black rounded-full transition-colors">
                                    <EnvelopeIcon class="w-4 h-4" /> Contactar
                                </a>
                            </div>
                        </div>


                        <div class="bg-white rounded p-6 md:p-8 border border-gray-100 shadow-sm">
                            <h3
                                class="text-xs font-bold uppercase tracking-widest text-gray-400 mb-6 flex items-center gap-2 border-b border-gray-100 pb-4">
                                <InformationCircleIcon class="w-4 h-4 text-[#E30613]" /> Ficha técnica
                            </h3>

                            <div class="space-y-6">
                                <div>
                                    <p class="text-[10px] font-bold uppercase tracking-widest text-gray-400 mb-1">Fecha
                                    </p>
                                    <p class="text-sm font-bold text-slate-700">{{ event.formatted_date }}</p>
                                </div>
                                <div class="w-full h-px bg-gray-50"></div>
                                <div>
                                    <p class="text-[10px] font-bold uppercase tracking-widest text-gray-400 mb-1">
                                        Horario</p>
                                    <p class="text-sm font-bold text-slate-700">{{ event.formatted_time }} HS</p>
                                </div>
                                <div class="w-full h-px bg-gray-50"></div>
                                <div>
                                    <p class="text-[10px] font-bold uppercase tracking-widest text-gray-400 mb-1">
                                        Ubicación / Coordenadas
                                    </p>
                                    <p class="text-sm font-medium text-slate-700 leading-relaxed">{{ event.location }}
                                    </p>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

            </div>
        </div>
    </AppLayout>
</template>