<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import { 
    MagnifyingGlassIcon, 
    XMarkIcon,
    MapPinIcon,
    CalendarIcon
} from '@heroicons/vue/24/outline';

const props = defineProps({
    events: Object,
    photographers: {
        type: Array,
        default: () => []
    },
    filters: {
        type: Object,
        default: () => ({ search: '', date: '', photographer_id: '' })
    }
});

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
        month: 'short',
        day: 'numeric'
    });
};

const handleImageError = (e) => {
    e.target.style.display = 'none';
    const parent = e.target.parentElement;
    if (!parent.querySelector('.placeholder-img')) {
        const placeholder = document.createElement('div');
        placeholder.className = 'placeholder-img w-full h-full min-h-[250px] flex items-center justify-center bg-slate-100 border border-slate-200';
        placeholder.innerHTML = `<span class="font-bold text-xs text-slate-400 uppercase tracking-widest">Sin Imagen</span>`;
        parent.appendChild(placeholder);
    }
};
</script>

<template>
    <Head title="Archivo de Eventos — f33.click" />

    <AppLayout>
        <div class="min-h-screen bg-[#F8F9FA] text-slate-800 font-sans selection:bg-red-600 selection:text-white pb-20">
            
        
            <div class="pt-32 pb-16 px-4 md:px-8 max-w-7xl mx-auto text-center">
                <span class="font-bold tracking-widest text-red-600 uppercase text-sm mb-4 block">-</span>
                <h1 class="font-flux text-6xl md:text-8xl text-black mb-6 leading-none">
                    Explorá <br class="md:hidden">
                    <span class="bg-gradient-to-r from-red-600 to-black -webkit-background-clip-text text-transparent bg-clip-text">eventos</span>
                </h1>
                <p class="text-slate-500 max-w-2xl mx-auto text-lg leading-relaxed">
                    Encontrá tu evento, busca por lugar o filtrá por nuestros fotógrafos.
                </p>
            </div>

            <div class="max-w-[1400px] mx-auto px-4 md:px-8">
                
            
                <section class="bg-white p-6 md:p-8 rounded shadow-[0_8px_30px_rgb(0,0,0,0.04)] mb-12 border border-slate-100">
                    <form @submit.prevent="submitFilters" class="flex flex-col gap-6">
                        
                    
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-5 flex items-center pointer-events-none">
                                <MagnifyingGlassIcon class="h-6 w-6 text-slate-400" />
                            </div>
                            <input 
                                v-model="form.search" 
                                type="text" 
                                class="block w-full pl-14 pr-32 py-4 bg-slate-50 border border-slate-200 rounded-2xl text-base focus:ring-2 focus:ring-red-500 focus:border-red-500 transition-colors outline-none font-bold text-slate-700 placeholder-slate-400" 
                                placeholder="Buscar identificador, nombre o locación..."
                            />
                            <button type="submit" class="absolute inset-y-2 right-2 bg-black text-white px-6 rounded font-bold uppercase tracking-wider text-sm hover:bg-red-600 transition-colors">
                                Buscar
                            </button>
                        </div>

                    
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            
                        
                            <div class="relative">
                                <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2 ml-1">Fecha exacta</label>
                                <input 
                                    v-model="form.date"
                                    type="date"
                                    class="block w-full bg-slate-50 border border-slate-200 text-slate-700 py-3 px-4 rounded outline-none focus:ring-2 focus:ring-red-500 font-semibold cursor-pointer appearance-none"
                                />
                            </div>

                            
                            <div class="relative">
                                <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2 ml-1">Fotógrafo</label>
                                <select 
                                    v-model="form.photographer_id"
                                    class="block w-full appearance-none bg-slate-50 border border-slate-200 text-slate-700 py-3 px-4 pr-8 rounded outline-none focus:ring-2 focus:ring-red-500 font-semibold cursor-pointer">
                                    <option value="">Cualquier Fotógrafo</option>
                                    <option v-for="photographer in photographers" :key="photographer.id" :value="photographer.id">
                                        {{ photographer.business_name || photographer.user?.name }}
                                    </option>
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 right-0 top-6 flex items-center px-4 text-slate-500">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                </div>
                            </div>

            
                            <div class="flex items-end">
                                <button type="button" @click="clearFilters" class="w-full py-3 px-4 bg-white border border-slate-200 text-slate-500 rounded font-bold uppercase tracking-wider text-sm hover:bg-slate-50 hover:text-red-600 transition-colors flex justify-center items-center gap-2">
                                    <XMarkIcon class="w-4 h-4" /> Limpiar Filtros
                                </button>
                            </div>
                        </div>
                    </form>
                </section>

            
                <div class="flex justify-between items-center mb-8 px-2">
                    <h2 class="text-lg font-bold text-slate-500">
                        Mostrando <span class="text-black">{{ events.data.length }}</span> resultados
                    </h2>
                    <span v-if="form.search || form.date || form.photographer_id" class="text-red-600 font-bold text-sm bg-red-50 px-3 py-1 rounded-full animate-pulse">
                        Filtros Activos
                    </span>
                </div>

            
                <div v-if="!events.data || events.data.length === 0" class="text-center py-24 bg-white rounded shadow-sm border border-slate-100">
                    <div class="w-20 h-20 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-6">
                        <MagnifyingGlassIcon class="w-10 h-10 text-slate-300" />
                    </div>
                    <h3 class="font-flux text-4xl text-black mb-3">No hay coincidencias</h3>
                    <p class="font-lato text-slate-500 mb-8">Intenta ajustar los filtros o limpiar tu búsqueda.</p>
                    <button @click="clearFilters" class="bg-black text-white px-8 py-3 rounded-full font-bold uppercase tracking-wider text-sm hover:bg-red-600 transition-colors">
                        Mostrar Todos Los Eventos
                    </button>
                </div>

            
                <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                    <Link v-for="event in events.data" :key="event.id" :href="route('events.show', event.slug)"
                        class="bg-white rounded overflow-hidden shadow-[0_8px_30px_rgb(0,0,0,0.04)] hover:shadow-[0_8px_40px_rgb(230,0,0,0.12)] transition-all duration-500 group flex flex-col">
                        
                    
                        <div class="h-60 relative overflow-hidden bg-slate-100 flex-shrink-0">
                            <img v-if="event.cover_image_url" :src="event.cover_image_url" :alt="event.name"
                                class="w-full h-full object-cover transition-transform duration-700 ease-in-out " 
                                @error="handleImageError" 
                            />
                        
                            <div class="absolute top-4 right-4 bg-white/90 backdrop-blur-sm text-black font-bold px-3 py-1.5 rounded text-xs shadow-sm">
                                {{ event.is_private ? 'Privado' : 'Público' }}
                            </div>
                        </div>

                    
                        <div class="p-6 flex-1 flex flex-col relative z-10">
                            
                        
                            <div class="flex items-center gap-2 text-xs font-bold text-red-600 uppercase tracking-wider mb-3">
                                <CalendarIcon class="w-4 h-4" />
                                <span>{{ formatDate(event.event_date) }}</span>
                            </div>

                            <h3 class="text-2xl font-black font-flux text-black mb-3 line-clamp-2 leading-tight group-hover:text-red-600 transition-colors">
                                {{ event.name }}
                            </h3>

                            <p v-if="event.description" class="text-slate-500 text-sm leading-relaxed mb-6 line-clamp-2 flex-grow">
                                {{ event.description }}
                            </p>
                            <p v-else class="text-slate-400 text-sm italic mb-6 flex-grow">
                                Sin descripción detallada.
                            </p>

                        
                            <div class="mt-auto pt-4 border-t border-slate-100 flex items-center justify-between">
                                <div v-if="event.location" class="flex items-center text-xs font-bold text-slate-400 uppercase">
                                    <MapPinIcon class="w-4 h-4 mr-1" />
                                    <span class="truncate max-w-[120px]">{{ event.location }}</span>
                                </div>
                                <div v-else></div>

                                <span class="text-black font-bold uppercase text-xs flex items-center gap-1 group-hover:text-red-600 transition-colors">
                                    Ver Galería
                                    <svg class="w-4 h-4 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path></svg>
                                </span>
                            </div>
                        </div>
                    </Link>
                </div>

                <div v-if="events.data && events.data.length > 0 && events.last_page > 1" class="mt-16 flex justify-center">
                    <div class="flex flex-wrap gap-2 bg-white p-2 rounded-2xl shadow-sm border border-slate-100">
                        <template v-for="(link, index) in events.links" :key="index">
                            <Link v-if="link.url" :href="link.url" 
                                class="min-w-[40px] h-10 flex items-center justify-center px-4 text-sm font-bold rounded transition-colors"
                                :class="link.active 
                                    ? 'bg-red-600 text-white shadow-md' 
                                    : 'bg-transparent text-slate-500 hover:bg-slate-50 hover:text-black'"
                            >
                                <span v-html="link.label"></span>
                            </Link>
                            <span v-else v-html="link.label" class="min-w-[40px] h-10 flex items-center justify-center px-4 text-sm font-bold text-slate-300"></span>
                        </template>
                    </div>
                </div>

            </div>
        </div>
    </AppLayout>
</template>

<style scoped>


::-webkit-scrollbar {
    width: 8px;
}
::-webkit-scrollbar-track {
    background: #f8f9fa;
}
::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 10px;
}
::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
}


input[type="date"]::-webkit-calendar-picker-indicator {
    cursor: pointer;
    opacity: 0.6;
    transition: opacity 0.2s;
}
input[type="date"]::-webkit-calendar-picker-indicator:hover {
    opacity: 1;
}
</style>