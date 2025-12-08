<script setup>
import { ref, onMounted } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import { CalendarIcon, MapPinIcon, ClockIcon, ArrowRightIcon } from '@heroicons/vue/24/outline';
import axios from 'axios';

const props = defineProps({
    isAuthenticated: Boolean,
    userRole: String,
});

// --- ESTADO PARA SCROLL INFINITO ---
const allEvents = ref([]);
const isPhotographer = ref(false);
const totalEvents = ref(0);
const loading = ref(true);
const loadingMore = ref(false);
const currentPage = ref(1);
const hasMorePages = ref(true);

// --- CARGAR PRIMERA PÁGINA ---
onMounted(async () => {
    await loadEvents(1);
});

// --- FUNCIÓN: Cargar eventos (página específica) ---
const loadEvents = async (page = 1) => {
    try {
        const response = await axios.get('/api/future-events', {
            params: { page }
        });

        const data = response.data;

        if (page === 1) {
            // Primera carga: reemplazar
            allEvents.value = data.future_events;
        } else {
            // Scroll infinito: concatenar
            allEvents.value = [...allEvents.value, ...data.future_events];
        }

        isPhotographer.value = data.is_photographer;
        totalEvents.value = data.total_events;
        currentPage.value = data.current_page || page;
        hasMorePages.value = data.has_more_pages || false;

    } catch (error) {
        console.error('Error cargando eventos futuros:', error);
    } finally {
        loading.value = false;
        loadingMore.value = false;
    }
};

// --- FUNCIÓN: Cargar más (siguiente página) ---
const loadMore = async () => {
    if (!hasMorePages.value || loadingMore.value) return;

    loadingMore.value = true;
    await loadEvents(currentPage.value + 1);
};

/**
 * ✅ Formatear días hasta el evento
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
 * ✅ Obtener color del badge según proximidad
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
    <section class="py-24 bg-gradient-to-br from-slate-50 via-gray-50 to-slate-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="text-center mb-16">
                <span class="text-[10px] font-bold uppercase tracking-[0.3em] text-slate-400 block mb-4">
                    Próximos Eventos
                </span>
                <h2 class="text-5xl font-serif font-bold text-slate-900 mb-6">
                    Eventos Futuros
                </h2>
                <p class="text-slate-600 text-lg max-w-2xl mx-auto leading-relaxed">
                    Oportunidades exclusivas para fotógrafos profesionales
                </p>
                
                <!-- Total Count -->
                <div v-if="totalEvents > 0" class="mt-4">
                    <span class="inline-block px-4 py-2 bg-slate-900 text-white text-xs font-bold uppercase tracking-widest rounded-full">
                        {{ totalEvents }} evento{{ totalEvents !== 1 ? 's' : '' }} disponible{{ totalEvents !== 1 ? 's' : '' }}
                    </span>
                </div>
            </div>

            <!-- Loading State (Primera Carga) -->
            <div v-if="loading" class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div v-for="i in 3" :key="i" class="animate-pulse">
                    <div class="bg-gray-200 h-64 rounded-sm mb-4"></div>
                    <div class="h-4 bg-gray-200 rounded w-3/4 mb-2"></div>
                    <div class="h-4 bg-gray-200 rounded w-1/2"></div>
                </div>
            </div>

            <!-- Events Grid -->
            <div v-else-if="allEvents.length > 0">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-12">
                    <div v-for="event in allEvents" :key="event.id"
                        class="group bg-white border border-gray-200 rounded-sm overflow-hidden hover:shadow-2xl transition-all duration-500 hover:-translate-y-2">
                        <!-- Image -->
                        <div class="relative h-64 overflow-hidden bg-slate-900">
                            <img :src="event.cover_image" :alt="event.title"
                                class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700"
                                loading="lazy" @error="handleImageError">

                            <!-- Overlay gradient -->
                            <div
                                class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500">
                            </div>

                            <!-- Days Badge -->
                            <div class="absolute top-4 right-4 bg-white/95 backdrop-blur-sm rounded-sm shadow-lg"> <!--  px-4 py-2 -->
                                <span :class="[
                                    'inline-flex items-center px-3 py-1 text-xs font-semibold',
                                    getDaysBadgeColor(event.days_until)
                                ]">
                                    <ClockIcon class="w-3 h-3 mr-1" />
                                    {{ getDaysText(event.days_until) }}
                                </span>
                            </div>
                        </div>

                        <!-- Content -->
                        <div class="p-6">
                            <h3
                                class="text-xl font-serif font-bold text-slate-900 mb-3 group-hover:text-slate-700 transition line-clamp-2">
                                {{ event.title }}
                            </h3>

                            <p class="text-sm text-slate-600 mb-4 line-clamp-2">
                                {{ event.description }}
                            </p>

                            <!-- Meta Info -->
                            <div class="space-y-2 mb-4">
                                <div class="flex items-center gap-2 text-xs text-slate-500">
                                    <CalendarIcon class="w-4 h-4" />
                                    <span>{{ event.formatted_date }}</span>
                                </div>
                                <div class="flex items-center gap-2 text-xs text-slate-500">
                                    <MapPinIcon class="w-4 h-4" />
                                    <span>{{ event.location }}</span>
                                </div>
                            </div>

                            <!-- Photographer Info -->
                            <div class="text-xs text-slate-400 mb-4">
                                Por {{ event.photographer.business_name }}
                            </div>

                            <!-- Action Button -->
                            <button v-if="isPhotographer"
                                class="w-full py-3 bg-slate-900 text-white text-xs font-bold uppercase tracking-widest hover:bg-slate-800 transition flex items-center justify-center gap-2 group/btn">
                                Ver Detalles
                                <ArrowRightIcon class="w-4 h-4 group-hover/btn:translate-x-1 transition" />
                            </button>
                            <div v-else class="text-center py-3 border-2 border-dashed border-gray-300 rounded-sm">
                                <span class="text-xs text-slate-400 uppercase tracking-wide">Requiere cuenta de
                                    fotógrafo</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Load More Button -->
                <div v-if="hasMorePages" class="text-center">
                    <button @click="loadMore" :disabled="loadingMore"
                        class="px-8 py-4 bg-slate-900 text-white font-bold text-sm uppercase tracking-widest hover:bg-slate-800 transition disabled:opacity-50 disabled:cursor-not-allowed rounded-sm shadow-lg hover:shadow-xl">
                        <span v-if="loadingMore" class="flex items-center gap-2">
                            <svg class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none"
                                viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                    stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                </path>
                            </svg>
                            Cargando...
                        </span>
                        <span v-else>Cargar Más Eventos</span>
                    </button>
                </div>
            </div>

            <!-- Empty State -->
            <div v-else class="text-center py-16">
                <CalendarIcon class="w-16 h-16 text-slate-300 mx-auto mb-4" />
                <p class="text-slate-400 text-lg">No hay eventos futuros disponibles</p>
            </div>

            <!-- CTA for Non-Photographers -->
            <div v-if="!isPhotographer && totalEvents > 3 && !loading"
                class="relative overflow-hidden bg-gradient-to-r from-slate-900 via-slate-800 to-slate-900 rounded-sm p-12 text-center mt-16">
                <div class="absolute inset-0 bg-[url('/images/pattern.svg')] opacity-5"></div>
                <div class="relative z-10">
                    <h3 class="text-3xl font-serif font-bold text-white mb-4">
                        ¿Sos Fotógrafo?
                    </h3>
                    <p class="text-slate-300 text-lg mb-8 max-w-2xl mx-auto">
                        Accede a <strong>{{ totalEvents }}</strong> eventos futuros y genera nuevos ingresos con tu
                        talento
                    </p>
                    <div class="flex gap-4 justify-center flex-wrap">
                        <Link v-if="!isAuthenticated" :href="route('photographer.register')"
                            class="px-8 py-4 bg-white text-slate-900 font-bold text-sm uppercase tracking-widest hover:bg-slate-100 transition rounded-sm shadow-xl">
                        Registrarme como Fotógrafo
                        </Link>
                        <Link v-if="!isAuthenticated" :href="route('login')"
                            class="px-8 py-4 border-2 border-white text-white font-bold text-sm uppercase tracking-widest hover:bg-white hover:text-slate-900 transition rounded-sm">
                        Iniciar Sesión
                        </Link>
                        <div v-else class="text-slate-300 text-sm">
                            Contacta al administrador para cambiar tu rol a fotógrafo
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>
