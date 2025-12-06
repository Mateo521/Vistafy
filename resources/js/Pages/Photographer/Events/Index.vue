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
    if (confirm('¿Confirmar eliminación del evento? Se perderán todas las fotos y datos asociados de forma permanente.')) {
        router.delete(route('photographer.events.destroy', eventId), {
            preserveScroll: true,
        });
    }
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
    if (!parent.querySelector('.placeholder-institutional')) {
        const placeholder = document.createElement('div');
        placeholder.className = 'placeholder-institutional w-full h-full flex items-center justify-center bg-gray-100 text-slate-300';
        placeholder.innerHTML = `<svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>`;
        parent.appendChild(placeholder);
    }
};
</script>

<template>
    <Head title="Gestión de Eventos" />

    <AuthenticatedLayout>
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                
                <div class="flex flex-col md:flex-row md:items-end justify-between mb-10 border-b border-gray-200 pb-6 gap-4">
                    <div>
                        <span class="text-xs font-bold tracking-[0.2em] text-slate-400 uppercase mb-2 block">
                            Portafolio
                        </span>
                        <h1 class="text-3xl font-serif font-bold text-slate-900">
                            Cartera de Eventos
                        </h1>
                    </div>
                    <Link
                        :href="route('photographer.events.create')"
                        class="bg-slate-900 hover:bg-slate-800 text-white px-6 py-3 rounded-sm text-xs font-bold uppercase tracking-widest transition shadow-sm inline-flex items-center gap-2"
                    >
                        <PlusIcon class="w-4 h-4" />
                        Nuevo Evento
                    </Link>
                </div>

                <div v-if="stats" class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-12">
                    <div class="bg-white p-6 border border-gray-200 rounded-sm flex flex-col justify-between group hover:border-slate-300 transition-colors">
                        <div class="flex justify-between items-start mb-4">
                            <span class="text-[10px] font-bold uppercase tracking-widest text-slate-500">Eventos Totales</span>
                            <CalendarIcon class="h-5 w-5 text-slate-300" />
                        </div>
                        <span class="text-3xl font-serif font-medium text-slate-900">{{ stats.total_events }}</span>
                    </div>

                    <div class="bg-white p-6 border border-gray-200 rounded-sm flex flex-col justify-between group hover:border-slate-300 transition-colors">
                        <div class="flex justify-between items-start mb-4">
                            <span class="text-[10px] font-bold uppercase tracking-widest text-slate-500">Publicados</span>
                            <div class="h-2 w-2 rounded-full bg-emerald-500"></div>
                        </div>
                        <span class="text-3xl font-serif font-medium text-slate-900">{{ stats.active_events }}</span>
                    </div>

                    <div class="bg-white p-6 border border-gray-200 rounded-sm flex flex-col justify-between group hover:border-slate-300 transition-colors">
                        <div class="flex justify-between items-start mb-4">
                            <span class="text-[10px] font-bold uppercase tracking-widest text-slate-500">Material Corgado</span>
                            <PhotoIcon class="h-5 w-5 text-slate-300" />
                        </div>
                        <span class="text-3xl font-serif font-medium text-slate-900">{{ stats.total_photos }}</span>
                    </div>

                    <div class="bg-white p-6 border border-gray-200 rounded-sm flex flex-col justify-between group hover:border-slate-300 transition-colors">
                        <div class="flex justify-between items-start mb-4">
                            <span class="text-[10px] font-bold uppercase tracking-widest text-slate-500">Ventas</span>
                            <CurrencyDollarIcon class="h-5 w-5 text-slate-300" />
                        </div>
                        <span class="text-3xl font-serif font-medium text-slate-900">{{ stats.total_sales || 0 }}</span>
                    </div>
                </div>

                <div>
                    <div v-if="!events.data || events.data.length === 0" class="text-center py-24 border border-dashed border-gray-300 rounded-sm bg-gray-50">
                        <CalendarIcon class="h-16 w-16 mx-auto text-gray-300 mb-4 stroke-1" />
                        <h4 class="text-lg font-serif font-medium text-slate-900 mb-2">Sin eventos registrados</h4>
                        <p class="text-sm text-slate-500 font-light mb-8 max-w-sm mx-auto">Comience creando un evento para organizar sus sesiones fotográficas.</p>
                        <Link :href="route('photographer.events.create')"
                            class="inline-block border-b border-slate-900 text-slate-900 text-xs font-bold uppercase tracking-widest pb-1 hover:text-slate-600 hover:border-slate-600 transition">
                            Crear Primer Evento
                        </Link>
                    </div>

                    <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                        <div v-for="event in events.data" :key="event.id"
                            class="group bg-white border border-gray-200 rounded-sm overflow-hidden hover:shadow-xl hover:border-slate-300 transition-all duration-300 flex flex-col"
                        >
                            <div class="relative h-56 bg-gray-100 overflow-hidden border-b border-gray-100">
                                <img v-if="event.cover_image_url" :src="event.cover_image_url" 
                                    :alt="event.name"
                                    class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105 filter grayscale-[0.3] group-hover:grayscale-0"
                                    @error="handleImageError" 
                                />
                                <div v-else class="w-full h-full flex items-center justify-center text-slate-300">
                                    <PhotoIcon class="w-12 h-12" />
                                </div>

                                <div class="absolute inset-0 bg-gradient-to-t from-slate-900/80 via-transparent to-transparent opacity-60 group-hover:opacity-40 transition-opacity"></div>
                                
                                <div class="absolute top-4 right-4">
                                    <span :class="[
                                        'px-2 py-1 text-[10px] font-bold uppercase tracking-widest border',
                                        event.is_private 
                                            ? 'bg-slate-900 text-white border-slate-900' 
                                            : 'bg-white text-slate-900 border-white'
                                    ]">
                                        {{ event.is_private ? 'Privado' : 'Público' }}
                                    </span>
                                </div>

                                <div class="absolute bottom-4 left-4 text-white">
                                    <div class="flex items-center text-xs font-medium">
                                        <CalendarIcon class="w-4 h-4 mr-1.5" />
                                        {{ formatDate(event.event_date) }}
                                    </div>
                                </div>
                            </div>

                            <div class="p-6 flex-1 flex flex-col">
                                <div class="flex justify-between items-start mb-2">
                                    <h3 class="text-xl font-serif font-bold text-slate-900 line-clamp-1 group-hover:text-slate-600 transition-colors">
                                        {{ event.name }}
                                    </h3>
                                </div>

                                <div v-if="event.location" class="flex items-center text-xs text-slate-500 mb-4 uppercase tracking-wide">
                                    <MapPinIcon class="w-3 h-3 mr-1" />
                                    {{ event.location }}
                                </div>

                                <p v-if="event.description" class="text-sm text-slate-500 font-light line-clamp-2 mb-6 leading-relaxed flex-1">
                                    {{ event.description }}
                                </p>

                                <div class="pt-4 border-t border-gray-100 flex items-center justify-between">
                                    <div class="text-xs font-bold text-slate-400 uppercase tracking-widest">
                                        {{ event.photos_count || 0 }} Fotos
                                    </div>
                                    
                                    <div class="flex gap-3">
                                        <Link :href="route('photographer.events.show', event.id)" title="Ver"
                                            class="text-slate-400 hover:text-slate-900 transition">
                                            <EyeIcon class="w-5 h-5" />
                                        </Link>
                                        <Link :href="route('photographer.events.edit', event.id)" title="Editar"
                                            class="text-slate-400 hover:text-slate-900 transition">
                                            <PencilSquareIcon class="w-5 h-5" />
                                        </Link>
                                        <button @click="deleteEvent(event.id)" title="Eliminar"
                                            class="text-slate-400 hover:text-red-600 transition">
                                            <TrashIcon class="w-5 h-5" />
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div v-if="events.last_page > 1" class="mt-12 pt-8 border-t border-gray-200">
                        <div class="flex items-center justify-center gap-2">
                            <template v-for="(link, index) in events.links" :key="index">
                                <Link v-if="link.url" :href="link.url" 
                                    class="h-8 min-w-[2rem] px-2 flex items-center justify-center text-xs font-medium rounded-sm transition-colors border"
                                    :class="link.active 
                                        ? 'bg-slate-900 text-white border-slate-900' 
                                        : 'bg-white text-slate-600 border-gray-200 hover:border-slate-400 hover:text-slate-900'"
                                >
                                    <span v-html="link.label"></span>
                                </Link>
                                <span v-else v-html="link.label" class="h-8 min-w-[2rem] px-2 flex items-center justify-center text-xs text-gray-300 border border-transparent cursor-not-allowed"></span>
                            </template>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>