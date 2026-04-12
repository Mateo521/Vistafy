<script setup>
import { ref, onMounted } from 'vue';
import { Link } from '@inertiajs/vue3';
import { CalendarIcon, MapPinIcon, ClockIcon, ArrowRightIcon, UserPlusIcon } from '@heroicons/vue/24/outline';
import axios from 'axios';

const props = defineProps({
    isAuthenticated: Boolean,
    userRole: String,
});

// --- ESTADO ---
const allEvents = ref([]);
const isPhotographer = ref(false);
const isUserAuthenticated = ref(false);
const totalEvents = ref(0);
const showingLimited = ref(false);
const loading = ref(true);
const loadingMore = ref(false);
const currentPage = ref(1);
const hasMorePages = ref(false);

//  DOBLE BLOQUEO: Variable de módulo + ref
let isLoadingInProgress = false;
const clickCount = ref(0); //  Contador de clics para debugging

// --- CARGAR PRIMERA PÁGINA ---
onMounted(async () => {
    await loadEvents(1);
});

// --- FUNCIÓN: Cargar eventos (página específica) ---
const loadEvents = async (page = 1) => {
    //  BLOQUEO ABSOLUTO
    if (isLoadingInProgress) {
        console.warn(' BLOQUEADO: Ya hay una carga en progreso');
        return;
    }

    if (loadingMore.value) {
        console.warn(' BLOQUEADO: loadingMore está activo');
        return;
    }

    isLoadingInProgress = true;
    loadingMore.value = true;

    try {


        const response = await axios.get('/eventos-futuros/api', {
            params: { page }
        });

        const data = response.data;



        if (page === 1) {
            // Primera carga: reemplazar
            allEvents.value = data.future_events;
        } else {
            // Scroll infinito: concatenar
            const beforeCount = allEvents.value.length;
            allEvents.value = [...allEvents.value, ...data.future_events];
        }

        // Actualizar estado
        isPhotographer.value = data.is_photographer;
        isUserAuthenticated.value = data.is_authenticated;
        totalEvents.value = data.total_events;
        showingLimited.value = data.showing_limited;
        currentPage.value = data.current_page || page;
        hasMorePages.value = data.has_more_pages || false;



    } catch (error) {
        console.error(' Error cargando eventos futuros:', error);
    } finally {
        //  IMPORTANTE: Delay antes de liberar bloqueo
        setTimeout(() => {
            loading.value = false;
            loadingMore.value = false;
            isLoadingInProgress = false;
     
        }, 300); // 300ms de cooldown
    }
};

// --- FUNCIÓN: Cargar más (siguiente página) ---
const loadMore = (event) => {
    clickCount.value++;



    //  BLOQUEOS MÚLTIPLES
    if (!hasMorePages.value) {
        console.warn(' BLOQUEADO: No hay más páginas');
        return;
    }

    if (loadingMore.value) {
        console.warn(' BLOQUEADO: Ya está cargando (loadingMore)');
        return;
    }

    if (isLoadingInProgress) {
        console.warn(' BLOQUEADO: Ya está cargando (isLoadingInProgress)');
        return;
    }

    if (!isPhotographer.value) {
        console.warn(' BLOQUEADO: Solo fotógrafos pueden cargar más');
        return;
    }

    //  Prevenir propagación del evento
    if (event) {
        event.preventDefault();
        event.stopPropagation();
    }


    loadEvents(currentPage.value + 1);
};

/**
 *  Formatear días hasta el evento
 */
const getDaysText = (days) => {
    const roundedDays = Math.round(days);

    if (roundedDays === 0) return 'Hoy';
    if (roundedDays === 1) return 'Mañana';
    if (roundedDays === -1) return 'Ayer';
    if (roundedDays < 0) return `Hace ${Math.abs(roundedDays)} días`;
    if (roundedDays > 30) {
        const months = Math.round(roundedDays / 30);
        return months === 1 ? 'En 1 mes' : `En ${months} meses`;
    }

    return `En ${roundedDays} días`;
};

/**
 *  Obtener color del badge según proximidad
 */
const getDaysBadgeColor = (days) => {
    const roundedDays = Math.round(days);

    if (roundedDays < 0) return 'bg-gray-100 text-gray-700';
    if (roundedDays === 0) return 'bg-red-100 text-red-700';
    if (roundedDays === 1) return 'bg-orange-100 text-orange-700';
    if (roundedDays <= 7) return 'bg-yellow-100 text-yellow-700';
    if (roundedDays <= 30) return 'bg-blue-100 text-blue-700';
    return 'bg-green-100 text-green-700';
};

const handleImageError = (e) => {
    e.target.style.display = 'none';
    const parent = e.target.parentElement;
    if (parent && !parent.querySelector('.placeholder-institutional')) {
        const placeholder = document.createElement('div');
        placeholder.className = 'placeholder-institutional w-full h-full flex items-center justify-center bg-gray-100';
        placeholder.innerHTML = `
            <svg class="w-12 h-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1"
                    d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
        `;
        parent.appendChild(placeholder);
    }
};
</script>

<template>
    <section class="py-24 bg-[#1B2632] font-['Syne'] relative border-t border-[#C9C1B1]/10">
        <div class="absolute top-10 right-10 font-['Cormorant_Garamond'] text-[150px] font-light text-[#2C3B4D]/40 leading-none pointer-events-none tracking-tighter">
            Próximos
        </div>

        <div class="max-w-7xl mx-auto px-8 md:px-16 relative z-10">
            <div class="mb-16">
                <div class="text-[9px] font-bold tracking-[0.35em] uppercase text-[#FFB162] mb-3 flex items-center gap-3 after:content-[''] after:h-px after:w-12 after:bg-[#A35139]">
                    Próximos eventos
                </div>
                <h2 class="font-['Cormorant_Garamond'] text-5xl md:text-6xl font-light text-[#EEE9DF] mb-4">
                    Oportunidades <em class="italic text-[#C9C1B1]">exclusivas</em>
                </h2>
                <div v-if="totalEvents > 0" class="mt-4">
                    <span class="inline-block px-4 py-2 border border-[#FFB162]/30 text-[#FFB162] text-[10px] font-bold uppercase tracking-widest bg-[#FFB162]/5">
                        {{ totalEvents }} evento{{ totalEvents !== 1 ? 's' : '' }} disponible{{ totalEvents !== 1 ? 's' : '' }}
                    </span>
                </div>
            </div>

            <div v-if="loading" class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div v-for="i in 3" :key="i" class="animate-pulse bg-[#2C3B4D] aspect-[4/3] rounded-sm"></div>
            </div>

            <div v-else-if="allEvents.length > 0">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-16">
                    <div v-for="(event, index) in allEvents" :key="`event-${event.id}-${index}`"
                        class="group relative bg-[#2C3B4D]/50 border border-[#C9C1B1]/10 overflow-hidden hover:border-[#FFB162]/50 transition-colors duration-500 flex flex-col">

                        <div class="relative h-56 overflow-hidden bg-[#1B2632]">
                            <img :src="event.cover_image" :alt="event.title"
                                class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700 saturate-50 group-hover:saturate-100"
                                loading="lazy" @error="handleImageError">
                            <div class="absolute inset-0 bg-gradient-to-t from-[#1B2632] to-transparent opacity-80"></div>

                            <div class="absolute top-4 right-4 bg-[#1B2632] border border-[#C9C1B1]/20">
                                <span class="inline-flex items-center px-3 py-1.5 text-[10px] uppercase tracking-widest font-bold" :class="getDaysBadgeColor(event.days_until) ? 'text-[#FFB162]' : 'text-[#C9C1B1]'">
                                    {{ getDaysText(event.days_until) }}
                                </span>
                            </div>
                        </div>

                        <div class="p-6 flex-1 flex flex-col">
                            <div class="text-[9px] font-bold uppercase tracking-[0.2em] text-[#C9C1B1] mb-2">
                                {{ event.formatted_date }} &bull; {{ event.location }}
                            </div>
                            <h3 class="font-['Cormorant_Garamond'] text-2xl text-[#EEE9DF] mb-3 leading-tight group-hover:text-[#FFB162] transition-colors">
                                {{ event.title }}
                            </h3>
                            <p class="text-[13px] text-[#C9C1B1]/80 mb-6 line-clamp-2 leading-relaxed flex-1">
                                {{ event.description }}
                            </p>

                            <Link v-if="isPhotographer" :href="`/eventos-futuros/${event.id}`"
                                class="text-[10px] font-bold uppercase tracking-[0.2em] text-[#FFB162] border-t border-[#C9C1B1]/10 pt-4 flex items-center justify-between group/btn hover:text-[#EEE9DF] transition-colors">
                                Ver Detalles
                                <span class="group-hover/btn:translate-x-2 transition-transform">&rarr;</span>
                            </Link>
                            <div v-else class="text-[10px] text-center uppercase tracking-widest text-[#C9C1B1]/50 border-t border-[#C9C1B1]/10 pt-4">
                                Requiere cuenta fotógrafo
                            </div>
                        </div>
                    </div>
                </div>

                <div class="text-center">
                    <button v-if="isPhotographer && hasMorePages" @click.prevent.stop="loadMore" :disabled="loadingMore || isLoadingInProgress"
                        class="bg-transparent border border-[#FFB162] text-[#FFB162] px-8 py-4 text-[11px] font-bold uppercase tracking-widest hover:bg-[#FFB162] hover:text-[#1B2632] transition-colors disabled:opacity-50">
                        <span v-if="loadingMore">Cargando...</span>
                        <span v-else>Cargar Más Eventos</span>
                    </button>

                    <div v-else-if="showingLimited && totalEvents > 6" class="bg-[#2C3B4D] border border-[#C9C1B1]/20 p-10 max-w-2xl mx-auto">
                        <h3 class="font-['Cormorant_Garamond'] text-3xl text-[#EEE9DF] mb-2">¿Querés ver más eventos?</h3>
                        <p class="text-[13px] text-[#C9C1B1] mb-8">Hay <strong>{{ totalEvents - 6 }} eventos más</strong> disponibles para fotógrafos profesionales.</p>
                        
                        <div class="flex flex-wrap gap-4 justify-center">
                            <template v-if="!isUserAuthenticated">
                                <Link :href="route('photographer.register')" class="bg-[#FFB162] text-[#1B2632] px-6 py-3 text-[10px] font-bold uppercase tracking-widest hover:bg-[#A35139] hover:text-[#EEE9DF] transition-colors">
                                    Registrarme
                                </Link>
                                <Link :href="route('login')" class="border border-[#C9C1B1] text-[#C9C1B1] px-6 py-3 text-[10px] font-bold uppercase tracking-widest hover:border-[#FFB162] hover:text-[#FFB162] transition-colors">
                                    Iniciar Sesión
                                </Link>
                            </template>
                            <div v-else class="text-[11px] text-[#FFB162] tracking-widest uppercase border border-[#FFB162]/30 px-6 py-3">
                                Contactá al admin para rol de Fotógrafo
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div v-else class="text-center py-20">
                <p class="text-[#C9C1B1] font-['Cormorant_Garamond'] text-2xl italic">No hay eventos futuros disponibles</p>
            </div>
        </div>
    </section>
</template>
