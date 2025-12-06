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

const handleImageError = (e) => {
    e.target.style.display = 'none';
    const parent = e.target.parentElement;
    if (!parent.querySelector('.placeholder-institutional')) {
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
    <Head title="Archivo de Eventos" />

    <AppLayout>
        <div class="bg-white border-b border-gray-100 pt-24 pb-12 md:pt-32 md:pb-16">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="max-w-3xl">
                    <span class="text-xs font-bold tracking-[0.2em] text-slate-400 uppercase mb-2 block">
                        Galería Oficial
                    </span>
                    <h1 class="text-4xl md:text-5xl font-serif font-bold text-slate-900 mb-6">
                        Archivo de Eventos
                    </h1>
                    <p class="text-lg text-slate-500 font-light leading-relaxed">
                        Explore nuestra colección cronológica. Utilice las herramientas de búsqueda para filtrar por ubicación, fecha o profesional.
                    </p>
                </div>
            </div>
        </div>

        <div class="min-h-screen bg-gray-50 py-12 md:py-16">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                
                <div class="bg-white border border-gray-200 p-6 mb-12 rounded-sm shadow-sm">
                    <form @submit.prevent="submitFilters">
                        <div class="flex flex-col lg:flex-row gap-4">
                            
                            <div class="flex-1 relative">
                                <input 
                                    v-model="form.search" 
                                    type="text"
                                    placeholder="Buscar por nombre, ciudad o ubicación..."
                                    class="w-full pl-10 pr-4 py-3 rounded-sm border-gray-300 text-sm focus:border-slate-900 focus:ring-0 placeholder-gray-400 transition-colors" 
                                />
                                <div class="absolute left-3 top-3 text-slate-400">
                                    <MagnifyingGlassIcon class="w-5 h-5" />
                                </div>
                            </div>

                            <div class="flex gap-3">
                                <button type="button" @click="showFilters = !showFilters" 
                                    :class="[
                                        'px-6 py-3 rounded-sm border text-xs font-bold uppercase tracking-widest transition-all duration-300 flex items-center gap-2 whitespace-nowrap',
                                        showFilters 
                                            ? 'bg-slate-100 border-slate-300 text-slate-900' 
                                            : 'bg-white border-gray-300 text-slate-600 hover:border-slate-900 hover:text-slate-900'
                                    ]">
                                    <AdjustmentsHorizontalIcon class="w-4 h-4" />
                                    <span>Filtros Avanzados</span>
                                </button>
                                
                                <button type="submit" 
                                    class="bg-slate-900 text-white px-8 py-3 rounded-sm text-xs font-bold uppercase tracking-widest hover:bg-slate-800 transition shadow-sm">
                                    Buscar
                                </button>
                            </div>
                        </div>

                        <div v-show="showFilters" class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-6 mt-6 border-t border-gray-100 animate-fade-in-down">
                            
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wide text-slate-500 mb-2">Fecha del Evento</label>
                                <div class="relative">
                                    <input 
                                        v-model="form.date"
                                        type="date" 
                                        class="w-full rounded-sm border-gray-300 text-sm focus:border-slate-900 focus:ring-0 text-slate-700"
                                    >
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wide text-slate-500 mb-2">Fotógrafo</label>
                                <select 
                                    v-model="form.photographer_id"
                                    class="w-full rounded-sm border-gray-300 text-sm focus:border-slate-900 focus:ring-0 bg-white text-slate-700"
                                >
                                    <option value="">Todos los fotógrafos</option>
                                    <option v-for="photographer in photographers" :key="photographer.id" :value="photographer.id">
                                        {{ photographer.business_name || photographer.user?.name }}
                                    </option>
                                </select>
                            </div>

                            <div class="md:col-span-2 flex justify-end">
                                <button type="button" @click="clearFilters" 
                                    class="text-xs font-bold uppercase tracking-widest text-red-600 hover:text-red-800 flex items-center gap-1 transition border-b border-transparent hover:border-red-600 pb-0.5">
                                    <XMarkIcon class="w-4 h-4" />
                                    Limpiar todos los filtros
                                </button>
                            </div>
                        </div>
                    </form>

                    <div class="mt-6 pt-4 border-t border-gray-100 flex justify-between items-center text-xs text-slate-400">
                        <span>
                            Mostrando <strong class="text-slate-900">{{ events.data.length }}</strong> resultados
                        </span>
                        <span v-if="form.search || form.date || form.photographer_id" class="text-slate-500">
                            Filtros activos
                        </span>
                    </div>
                </div>

                <div v-if="!events.data || events.data.length === 0" class="flex flex-col items-center justify-center py-24 border border-dashed border-gray-300 rounded-sm bg-white">
                    <div class="w-16 h-16 text-gray-300 mb-4">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" /></svg>
                    </div>
                    <h3 class="text-xl font-serif font-medium text-slate-900 mb-2">Sin resultados</h3>
                    <p class="text-slate-500 mb-8 font-light max-w-md text-center">No encontramos eventos que coincidan con sus criterios de búsqueda.</p>
                    <button @click="clearFilters"
                        class="text-xs font-bold uppercase tracking-widest text-slate-900 border-b border-slate-900 pb-1 hover:text-slate-600 hover:border-slate-600 transition">
                        Ver todos los eventos
                    </button>
                </div>

                <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    <Link v-for="event in events.data" :key="event.id" :href="route('events.show', event.slug)"
                        class="group flex flex-col bg-white border border-gray-200 hover:border-gray-300 hover:shadow-xl transition-all duration-500 ease-out rounded-sm">
                        
                        <div class="relative h-64 overflow-hidden bg-gray-100 border-b border-gray-100">
                            <img v-if="event.cover_image_url" :src="event.cover_image_url"
                                :alt="event.name"
                                class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105 filter saturate-[0.7] group-hover:saturate-100" 
                                @error="handleImageError" 
                            />

                            <div v-else class="w-full h-full flex items-center justify-center bg-gray-100">
                                <svg class="w-12 h-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1"
                                        d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            </div>

                            <div class="absolute top-0 left-0 w-full p-4 flex justify-between items-start">
                                <span class="bg-white/95 backdrop-blur-md px-3 py-1 text-[10px] font-bold uppercase tracking-widest text-slate-900 shadow-sm border border-gray-100">
                                    {{ formatDate(event.event_date) }}
                                </span>
                            </div>
                        </div>

                        <div class="p-6 flex-1 flex flex-col">
                            <h3 class="text-xl font-serif font-bold text-slate-900 mb-3 line-clamp-2 group-hover:text-blue-900 transition-colors">
                                {{ event.name }}
                            </h3>

                            <p v-if="event.description" class="text-slate-500 text-sm font-light mb-6 line-clamp-3 leading-relaxed">
                                {{ event.description }}
                            </p>

                            <div class="mt-auto pt-4 border-t border-gray-100 flex items-center justify-between text-xs">
                                <div v-if="event.location" class="flex items-center text-slate-500">
                                    <MapPinIcon class="w-3 h-3 mr-1.5" />
                                    <span class="uppercase tracking-wide truncate max-w-[120px]">{{ event.location }}</span>
                                </div>
                                <div v-else></div>

                                <span class="text-slate-900 font-bold uppercase tracking-wider group-hover:underline decoration-1 underline-offset-4">
                                    Ver Galería
                                </span>
                            </div>
                        </div>
                    </Link>
                </div>

                <div v-if="events.data && events.data.length > 0 && events.last_page > 1" class="mt-16 border-t border-gray-200 pt-8">
                    <div class="flex items-center justify-center gap-2">
                        <template v-for="(link, index) in events.links" :key="index">
                            <Link v-if="link.url" :href="link.url" 
                                class="px-4 py-2 text-sm font-medium transition-colors border border-transparent rounded-sm"
                                :class="link.active 
                                    ? 'bg-slate-900 text-white border-slate-900' 
                                    : 'text-slate-600 hover:text-slate-900 hover:border-slate-300'"
                            >
                                <span v-html="link.label"></span>
                            </Link>
                            <span v-else v-html="link.label" class="px-4 py-2 text-sm font-medium text-gray-300"></span>
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
</style>