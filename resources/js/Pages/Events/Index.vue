<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import { 
    MagnifyingGlassIcon, 
    AdjustmentsHorizontalIcon, 
    XMarkIcon, 
    CalendarIcon, 
    MapPinIcon 
} from '@heroicons/vue/24/outline';

const props = defineProps({
    events: Object,
    photographers: {
        type: Array,
        default: () => [] // Lista de fotógrafos para el filtro {id, business_name}
    },
    filters: {
        type: Object,
        default: () => ({ search: '', date: '', photographer_id: '' })
    }
});

const showFilters = ref(false);

const form = useForm({
    search: props.filters.search || '',
    date: props.filters.date || '',
    photographer_id: props.filters.photographer_id || '',
});

const submitFilters = () => {
    form.get(route('events.index'), {
        preserveState: true,
        preserveScroll: true,
    });
};

const clearFilters = () => {
    form.search = '';
    form.date = '';
    form.photographer_id = '';
    submitFilters();
};

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString('es-ES', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
};

// Actualizado para el Dark Theme
const handleImageError = (e) => {
    e.target.style.display = 'none';
    const parent = e.target.parentElement;
    if (!parent.querySelector('.placeholder-institutional')) {
        const placeholder = document.createElement('div');
        placeholder.className = 'placeholder-institutional w-full h-full flex items-center justify-center bg-[#111920]';
        placeholder.innerHTML = `
            <span class="font-['Cormorant_Garamond'] text-5xl italic opacity-20 text-[#EEE9DF]">f33</span>
        `;
        parent.appendChild(placeholder);
    }
};
</script>

<template>
    <Head title="Archivo de eventos" />

    <AppLayout>
        <div class="bg-[#111920] pt-32 pb-16 md:pt-40 md:pb-20 font-['Syne'] relative overflow-hidden">
            <div class="absolute -top-10 right-0 font-['Cormorant_Garamond'] text-[150px] md:text-[200px] font-light text-[#1B2632] opacity-50 leading-none pointer-events-none select-none">
                ARCHIVO
            </div>
            
            <div class="max-w-7xl mx-auto px-8 md:px-16 relative z-10">
                <div class="max-w-3xl">
                    <span class="text-[10px] font-bold tracking-[0.3em] text-[#FFB162] uppercase mb-4 block flex items-center gap-3 after:content-[''] after:h-px after:w-12 after:bg-[#A35139]">
                        Galería Oficial
                    </span>
                    <h1 class="text-5xl md:text-7xl font-['Cormorant_Garamond'] font-light text-[#EEE9DF] mb-6 leading-tight">
                        Archivo de <em class="italic text-[#C9C1B1]">eventos</em>
                    </h1>
                    <p class="text-[14px] md:text-base text-[#C9C1B1] font-light leading-relaxed max-w-xl">
                        Explorá nuestra colección cronológica. Usá las herramientas de búsqueda para filtrar por ubicación, fecha o profesional.
                    </p>
                </div>
            </div>
        </div>

        <div class="min-h-screen bg-[#111920] py-12 md:py-20 font-['Syne']">
            <div class="max-w-7xl mx-auto px-8 md:px-16">
                
                <div class="bg-[#1B2632] border border-[#C9C1B1]/10 p-8 mb-16 shadow-2xl">
                    <form @submit.prevent="submitFilters">
                        <div class="flex flex-col lg:flex-row gap-6">
                            
                            <div class="flex-1 relative">
                                <input 
                                    v-model="form.search" 
                                    type="text"
                                    placeholder="Buscar por nombre, ciudad o ubicación..."
                                    class="w-full pl-12 pr-4 py-4 bg-[#111920] border border-[#C9C1B1]/20 text-[#EEE9DF] text-[13px] focus:border-[#FFB162] focus:ring-0 placeholder-[#C9C1B1]/40 transition-colors rounded-none outline-none" 
                                />
                                <div class="absolute left-4 top-4 text-[#FFB162]">
                                    <MagnifyingGlassIcon class="w-5 h-5" />
                                </div>
                            </div>

                            <div class="flex gap-4">
                                <button type="button" @click="showFilters = !showFilters" 
                                    :class="[
                                        'px-8 py-4 border text-[10px] font-bold uppercase tracking-widest transition-all duration-300 flex items-center gap-3 whitespace-nowrap',
                                        showFilters 
                                            ? 'bg-[#FFB162]/10 border-[#FFB162] text-[#FFB162]' 
                                            : 'bg-[#111920] border-[#C9C1B1]/20 text-[#C9C1B1] hover:border-[#FFB162] hover:text-[#FFB162]'
                                    ]">
                                    <AdjustmentsHorizontalIcon class="w-4 h-4" />
                                    <span>Filtros Avanzados</span>
                                </button>
                                
                                <button type="submit" 
                                    class="bg-[#FFB162] text-[#1B2632] px-10 py-4 text-[10px] font-bold uppercase tracking-widest hover:bg-[#EEE9DF] transition-colors shadow-lg">
                                    Buscar
                                </button>
                            </div>
                        </div>

                        <div v-show="showFilters" class="grid grid-cols-1 md:grid-cols-2 gap-8 pt-8 mt-8 border-t border-[#C9C1B1]/10 animate-fade-in-down">
                            
                            <div>
                                <label class="block text-[9px] font-bold uppercase tracking-[0.2em] text-[#C9C1B1] mb-3">Fecha del Evento</label>
                                <div class="relative">
                                    <input 
                                        v-model="form.date"
                                        type="date" 
                                        class="w-full py-3 px-4 border border-[#C9C1B1]/20 bg-[#111920] text-[#EEE9DF] text-[13px] focus:border-[#FFB162] focus:ring-0 appearance-none outline-none rounded-none [color-scheme:dark]"
                                    >
                                </div>
                            </div>

                            <div>
                                <label class="block text-[9px] font-bold uppercase tracking-[0.2em] text-[#C9C1B1] mb-3">Fotógrafo</label>
                                <div class="relative">
                                    <select 
                                        v-model="form.photographer_id"
                                        class="w-full py-3 pl-4 pr-10 border border-[#C9C1B1]/20 bg-[#111920] text-[#EEE9DF] text-[13px] focus:border-[#FFB162] focus:ring-0 appearance-none outline-none rounded-none"
                                    >
                                        <option value="" class="bg-[#1B2632]">Todos los fotógrafos</option>
                                        <option v-for="photographer in photographers" :key="photographer.id" :value="photographer.id" class="bg-[#1B2632]">
                                            {{ photographer.business_name || photographer.user?.name }}
                                        </option>
                                    </select>
                                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-[#FFB162]">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                                    </div>
                                </div>
                            </div>

                            <div class="md:col-span-2 flex justify-end">
                                <button type="button" @click="clearFilters" 
                                    class="text-[9px] font-bold uppercase tracking-[0.2em] text-[#A35139] hover:text-[#FFB162] flex items-center gap-2 transition-colors border-b border-[#A35139] hover:border-[#FFB162] pb-1">
                                    <XMarkIcon class="w-3.5 h-3.5" />
                                    Limpiar todos los filtros
                                </button>
                            </div>
                        </div>
                    </form>

                    <div class="mt-8 pt-6 border-t border-[#C9C1B1]/10 flex justify-between items-center text-[10px] text-[#C9C1B1] uppercase tracking-[0.2em]">
                        <span>
                            Mostrando <strong class="text-[#FFB162] text-[12px]">{{ events.data.length }}</strong> resultados
                        </span>
                        <span v-if="form.search || form.date || form.photographer_id" class="text-[#A35139] font-bold">
                            Filtros activos
                        </span>
                    </div>
                </div>

                <div v-if="!events.data || events.data.length === 0" class="flex flex-col items-center justify-center py-32 border border-dashed border-[#C9C1B1]/20 bg-[#1B2632]">
                    <div class="w-16 h-16 text-[#FFB162] opacity-50 mb-6">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" /></svg>
                    </div>
                    <h3 class="text-3xl font-['Cormorant_Garamond'] text-[#EEE9DF] mb-3">Sin resultados</h3>
                    <p class="text-[#C9C1B1] text-[13px] font-light mb-8 max-w-md text-center">No encontramos eventos que coincidan con sus criterios de búsqueda.</p>
                    <button @click="clearFilters"
                        class="text-[10px] font-bold uppercase tracking-[0.2em] text-[#FFB162] border-b border-[#FFB162] pb-1 hover:text-[#EEE9DF] hover:border-[#EEE9DF] transition-colors">
                        Ver todos los eventos
                    </button>
                </div>

                <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-10">
                    <Link v-for="event in events.data" :key="event.id" :href="route('events.show', event.slug)"
                        class="group flex flex-col bg-[#1B2632] border border-[#C9C1B1]/10 hover:border-[#FFB162]/50 transition-colors duration-500 overflow-hidden relative">
                        
                        <div class="relative h-64 overflow-hidden bg-[#111920]">
                            <img v-if="event.cover_image_url" :src="event.cover_image_url"
                                :alt="event.name"
                                class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105 filter saturate-[0.6] group-hover:saturate-100" 
                                @error="handleImageError" 
                            />

                            <div class="absolute inset-0 bg-gradient-to-t from-[#1B2632] via-transparent to-transparent opacity-90"></div>

                            <div class="absolute top-4 left-4 flex justify-between items-start z-10">
                                <span class="bg-[#1B2632]/80 backdrop-blur-md border border-[#C9C1B1]/20 px-3 py-1.5 text-[9px] font-bold uppercase tracking-widest text-[#FFB162] shadow-xl">
                                    {{ formatDate(event.event_date) }}
                                </span>
                            </div>
                        </div>

                        <div class="p-8 flex-1 flex flex-col relative z-10">
                            <h3 class="text-3xl font-['Cormorant_Garamond'] text-[#EEE9DF] mb-4 line-clamp-2 group-hover:text-[#FFB162] transition-colors leading-tight">
                                {{ event.name }}
                            </h3>

                            <p v-if="event.description" class="text-[#C9C1B1]/80 text-[13px] font-light mb-8 line-clamp-3 leading-relaxed">
                                {{ event.description }}
                            </p>

                            <div class="mt-auto pt-6 border-t border-[#C9C1B1]/10 flex items-center justify-between text-[10px] font-bold uppercase tracking-[0.2em]">
                                <div v-if="event.location" class="flex items-center text-[#C9C1B1]">
                                    <MapPinIcon class="w-3.5 h-3.5 mr-1.5 text-[#FFB162]" />
                                    <span class="truncate max-w-[120px]">{{ event.location }}</span>
                                </div>
                                <div v-else></div>

                                <span class="text-[#FFB162] group-hover:text-[#EEE9DF] transition-colors flex items-center gap-2">
                                    Ver Galería <span class="group-hover:translate-x-1 transition-transform">&rarr;</span>
                                </span>
                            </div>
                        </div>
                    </Link>
                </div>

                <div v-if="events.data && events.data.length > 0 && events.last_page > 1" class="mt-20 flex justify-center">
                    <div class="flex items-center gap-2 bg-[#1B2632] p-2 border border-[#C9C1B1]/10 shadow-xl">
                        <template v-for="(link, index) in events.links" :key="index">
                            <Link v-if="link.url" :href="link.url" 
                                class="min-w-[40px] h-10 flex items-center justify-center px-4 text-[11px] font-bold transition-colors"
                                :class="link.active 
                                    ? 'bg-[#FFB162] text-[#1B2632]' 
                                    : 'text-[#C9C1B1] hover:bg-[#2C3B4D] hover:text-[#EEE9DF]'"
                            >
                                <span v-html="link.label"></span>
                            </Link>
                            <span v-else v-html="link.label" class="min-w-[40px] h-10 flex items-center justify-center px-4 text-[11px] font-bold text-[#C9C1B1]/30"></span>
                        </template>
                    </div>
                </div>

            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
@keyframes fadeInDown {
    from { opacity: 0; transform: translateY(-10px); }
    to { opacity: 1; transform: translateY(0); }
}
.animate-fade-in-down {
    animation: fadeInDown 0.3s ease-out;
}

/* Fuerza el ícono de calendario en Chrome a ser blanco/claro en modo oscuro */
input[type="date"]::-webkit-calendar-picker-indicator {
    filter: invert(1);
    opacity: 0.5;
    cursor: pointer;
}
input[type="date"]::-webkit-calendar-picker-indicator:hover {
    opacity: 0.8;
}
</style>