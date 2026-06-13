<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { 
    CalendarIcon, 
    MapPinIcon, 
    PhotoIcon, 
    CurrencyDollarIcon,
    TrashIcon,
    PencilSquareIcon,
    EyeIcon,
    PlusIcon
} from '@heroicons/vue/24/outline';

const props = defineProps({
    events: Object,
    stats: Object,
});

const deleteEvent = (eventId) => {
    if (confirm('¿Confirmar purga del evento? Se perderán todas las fotos y datos asociados de forma permanente en el sistema.')) {
        router.delete(route('photographer.events.destroy', eventId), {
            preserveScroll: true,
        });
    }
};

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString('es-ES', {
        year: 'numeric',
        month: 'short',
        day: '2-digit'
    }).replace(/,/g, '').toUpperCase();
};

const handleImageError = (e) => {
    e.target.style.display = 'none';
    const parent = e.target.parentElement;
    if (!parent.querySelector('.placeholder-institutional')) {
        const placeholder = document.createElement('div');
        placeholder.className = 'placeholder-institutional w-full h-full flex items-center justify-center bg-zinc-950 text-zinc-800 border-b border-zinc-800';
        placeholder.innerHTML = `<svg class="w-12 h-12 opacity-20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>`;
        parent.appendChild(placeholder);
    }
};
</script>

<template>
    <Head title="Gestión de Eventos" />

    <AuthenticatedLayout>
        <div class="min-h-screen bg-[#050505] text-white selection:bg-[#E30613] selection:text-black py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                
                <div class="flex flex-col md:flex-row md:items-end justify-between mb-10 border-b border-zinc-800 pb-6 gap-6">
                    <div>
                        <span class="font-mono text-[10px] font-bold text-[#E30613] uppercase tracking-widest mb-2 block flex items-center gap-2">
                            <span class="w-2 h-2 bg-[#E30613] animate-pulse"></span>
                            Panel
                        </span>
                        <h1 class="text-5xl md:text-6xl font-flux font-black text-white uppercase tracking-tighter leading-none">
                            Eventos
                        </h1>
                    </div>
                    <Link
                        :href="route('photographer.events.create')"
                        class="bg-[#E30613] text-black px-6 py-3 border border-[#E30613] text-[10px] font-mono font-bold uppercase tracking-widest hover:bg-transparent hover:text-white transition-colors flex items-center justify-center gap-2 rounded-none w-max"
                    >
                        <PlusIcon class="w-4 h-4" />
                        Nuevo Evento
                    </Link>
                </div>

                <div v-if="stats" class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-12">
                    <div class="bg-[#09090b] p-6 border border-zinc-800 flex flex-col justify-between group hover:border-white transition-colors shadow-[4px_4px_0px_0px_rgba(255,255,255,0.05)]">
                        <div class="flex justify-between items-start mb-4">
                            <span class="font-mono text-[10px] font-bold uppercase tracking-widest text-zinc-500 group-hover:text-white transition-colors">Eventos Totales</span>
                            <CalendarIcon class="h-5 w-5 text-zinc-700" />
                        </div>
                        <span class="text-4xl font-flux text-white">{{ stats.total_events }}</span>
                    </div>

                    <div class="bg-[#09090b] p-6 border border-zinc-800 flex flex-col justify-between group hover:border-emerald-500 transition-colors shadow-[4px_4px_0px_0px_rgba(255,255,255,0.05)]">
                        <div class="flex justify-between items-start mb-4">
                            <span class="font-mono text-[10px] font-bold uppercase tracking-widest text-zinc-500 group-hover:text-emerald-500 transition-colors">Publicados</span>
                            <div class="h-2 w-2 rounded-none bg-emerald-500 animate-pulse mt-1.5"></div>
                        </div>
                        <span class="text-4xl font-flux text-white">{{ stats.active_events }}</span>
                    </div>

                    <div class="bg-[#09090b] p-6 border border-zinc-800 flex flex-col justify-between group hover:border-[#E30613] transition-colors shadow-[4px_4px_0px_0px_rgba(255,255,255,0.05)]">
                        <div class="flex justify-between items-start mb-4">
                            <span class="font-mono text-[10px] font-bold uppercase tracking-widest text-zinc-500 group-hover:text-[#E30613] transition-colors">Material Cargado</span>
                            <PhotoIcon class="h-5 w-5 text-zinc-700 group-hover:text-[#E30613] transition-colors" />
                        </div>
                        <span class="text-4xl font-flux text-[#E30613]">{{ stats.total_photos }}</span>
                    </div>

                    <div class="bg-[#09090b] p-6 border border-zinc-800 flex flex-col justify-between group hover:border-white transition-colors shadow-[4px_4px_0px_0px_rgba(255,255,255,0.05)]">
                        <div class="flex justify-between items-start mb-4">
                            <span class="font-mono text-[10px] font-bold uppercase tracking-widest text-zinc-500 group-hover:text-white transition-colors">Ventas</span>
                            <CurrencyDollarIcon class="h-5 w-5 text-zinc-700" />
                        </div>
                        <span class="text-4xl font-flux text-white">{{ stats.total_sales || 0 }}</span>
                    </div>
                </div>

                <div>
                    <div v-if="!events.data || events.data.length === 0" class="text-center py-24 border-2 border-dashed border-zinc-800 bg-[#09090b]">
                        <CalendarIcon class="h-16 w-16 mx-auto text-zinc-700 mb-4 stroke-1" />
                        <h4 class="text-2xl font-flux text-zinc-500 uppercase tracking-widest mb-2">SYSTEM_EMPTY // 0 EVENTOS</h4>
                        <p class="font-mono text-xs text-zinc-600 mb-8 max-w-sm mx-auto uppercase">Requiere creación de instancia operativa para organizar sesiones fotográficas.</p>
                        <Link :href="route('photographer.events.create')"
                            class="inline-block border border-zinc-700 bg-black text-white px-8 py-3 font-mono text-[10px] font-bold uppercase tracking-widest hover:border-[#E30613] hover:text-[#E30613] transition-colors">
                            Inicializar Nuevo Evento
                        </Link>
                    </div>

                    <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                        <div v-for="event in events.data" :key="event.id"
                            class="group bg-[#09090b] border border-zinc-800 overflow-hidden hover:border-[#E30613] hover:shadow-[8px_8px_0px_0px_rgba(227,6,19,1)] transition-all duration-300 flex flex-col"
                        >
                            <div class="relative h-56 bg-zinc-950 overflow-hidden border-b border-zinc-800">
                                <img v-if="event.cover_image_url" :src="event.cover_image_url" 
                                    :alt="event.name"
                                    class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105 filter grayscale opacity-60 group-hover:grayscale-0 group-hover:opacity-100"
                                    @error="handleImageError" 
                                />
                                <div v-else class="w-full h-full flex items-center justify-center text-zinc-800">
                                    <PhotoIcon class="w-12 h-12" />
                                </div>

                                <div class="absolute inset-0 bg-[#E30613]/0 group-hover:bg-[#E30613]/10 transition-colors duration-300 pointer-events-none mix-blend-multiply"></div>
                                
                                <div class="absolute top-4 right-4 flex gap-2">
                                    <span :class="[
                                        'px-2 py-1 font-mono text-[9px] font-bold uppercase tracking-widest border',
                                        event.is_private 
                                            ? 'bg-[#E30613] text-black border-[#E30613]' 
                                            : 'bg-black/80 backdrop-blur-sm text-white border-zinc-700'
                                    ]">
                                        {{ event.is_private ? 'PRIVADO' : 'PÚBLICO' }}
                                    </span>
                                </div>

                                <div class="absolute bottom-4 left-4">
                                    <div class="flex items-center font-mono text-[10px] font-bold text-white bg-black/80 border border-zinc-700 px-2 py-1 uppercase tracking-widest">
                                        <CalendarIcon class="w-3 h-3 mr-2 text-[#E30613]" />
                                        {{ formatDate(event.event_date) }}
                                    </div>
                                </div>
                            </div>

                            <div class="p-6 flex-1 flex flex-col bg-[#09090b]">
                                <h3 class="text-2xl font-flux text-white uppercase tracking-wider mb-2 group-hover:text-[#E30613] transition-colors line-clamp-1">
                                    {{ event.name }}
                                </h3>

                                <div v-if="event.location" class="flex items-center font-mono text-[10px] text-zinc-500 mb-4 uppercase tracking-widest">
                                    <MapPinIcon class="w-3 h-3 mr-2 text-[#E30613]" />
                                    {{ event.location }}
                                </div>

                                <p v-if="event.description" class="font-mono text-xs text-zinc-400 line-clamp-2 mb-6 leading-relaxed flex-1">
                                    {{ event.description }}
                                </p>

                                <div class="pt-4 border-t border-zinc-800 flex items-center justify-between">
                                    <div class="font-mono text-[10px] font-bold text-zinc-500 uppercase tracking-widest flex items-center gap-1.5">
                                        <PhotoIcon class="w-3.5 h-3.5" />
                                        {{ event.photos_count || 0 }}
                                    </div>
                                    
                                    <div class="flex gap-2">
                                        <Link :href="route('photographer.events.show', event.id)" title="Ver / Gestionar Fotos"
                                            class="w-8 h-8 flex items-center justify-center bg-black border border-zinc-700 text-zinc-400 hover:text-white hover:border-white transition-colors">
                                            <EyeIcon class="w-4 h-4" />
                                        </Link>
                                        <Link :href="route('photographer.events.edit', event.id)" title="Editar Parámetros"
                                            class="w-8 h-8 flex items-center justify-center bg-black border border-zinc-700 text-zinc-400 hover:text-white hover:border-white transition-colors">
                                            <PencilSquareIcon class="w-4 h-4" />
                                        </Link>
                                        <button @click="deleteEvent(event.id)" title="Purgar Evento"
                                            class="w-8 h-8 flex items-center justify-center bg-black border border-zinc-700 text-zinc-400 hover:bg-[#E30613] hover:text-black hover:border-[#E30613] transition-colors">
                                            <TrashIcon class="w-4 h-4" />
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div v-if="events.last_page > 1" class="mt-12 pt-8 border-t border-zinc-800 flex flex-wrap items-center justify-center gap-2">
                        <template v-for="(link, index) in events.links" :key="index">
                            <Link v-if="link.url" :href="link.url" 
                                class="h-10 min-w-[2.5rem] px-3 flex items-center justify-center font-mono text-[10px] font-bold uppercase tracking-widest rounded-none transition-colors border"
                                :class="link.active 
                                    ? 'bg-[#E30613] text-black border-[#E30613]' 
                                    : 'bg-black text-zinc-500 border-zinc-800 hover:border-white hover:text-white'"
                            >
                                <span v-html="link.label"></span>
                            </Link>
                            <span v-else v-html="link.label" class="h-10 min-w-[2.5rem] px-3 flex items-center justify-center font-mono text-[10px] font-bold uppercase text-zinc-700 border border-transparent cursor-not-allowed"></span>
                        </template>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>