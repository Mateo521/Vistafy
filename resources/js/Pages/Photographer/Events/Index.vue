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
    PlusIcon,
    CheckBadgeIcon
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
        day: 'numeric'
    });
};

const handleImageError = (e) => {
    e.target.style.display = 'none';
    const parent = e.target.parentElement;
    if (!parent.querySelector('.placeholder-institutional')) {
        const placeholder = document.createElement('div');
        placeholder.className = 'placeholder-institutional w-full h-full flex flex-col items-center justify-center bg-gray-50 border-b border-gray-100 text-gray-300';
        placeholder.innerHTML = `
            <svg class="w-10 h-10 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
            <span class="text-xs font-bold uppercase tracking-wider text-gray-400">Sin Imagen</span>
        `;
        parent.appendChild(placeholder);
    }
};
</script>

<template>
    <Head title="Gestión de Eventos" />

    <AuthenticatedLayout>
        <div class="min-h-screen bg-[#F8F9FA] text-slate-800 font-sans antialiased py-12 pt-28">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                
                
                <div class="flex flex-col md:flex-row md:items-end justify-between mb-10 border-b border-gray-200 pb-8 gap-6">
                    <div>
                        <span class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-2 flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-[#E30613] animate-pulse"></span>
                            Panel de control
                        </span>
                        <h1 class="text-5xl md:text-7xl font-flux text-black tracking-wide leading-none">
                            Mis <span class="text-[#E30613]">eventos</span>
                        </h1>
                    </div>
                    <Link
                        :href="route('photographer.events.create')"
                        class="bg-black text-white px-8 py-3.5 rounded-full text-xs font-bold uppercase tracking-wider hover:bg-[#E30613] hover:shadow-lg hover:shadow-red-500/30 transition-all flex items-center justify-center gap-2 w-max"
                    >
                        <PlusIcon class="w-5 h-5" />
                        Nuevo evento
                    </Link>
                </div>

                
                <div v-if="stats" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4 md:gap-6 mb-12">
                    
                    
                    <div class="bg-white p-6 md:p-8 rounded border border-gray-100 flex flex-col justify-between group hover:shadow-md hover:-translate-y-1 transition-all duration-300">
                        <div class="flex justify-between items-start mb-4">
                            <div class="w-10 h-10 rounded-full bg-gray-50 flex items-center justify-center group-hover:bg-red-50 transition-colors">
                                <CalendarIcon class="h-5 w-5 text-gray-400 group-hover:text-[#E30613]" />
                            </div>
                        </div>
                        <div>
                            <span class="text-4xl md:text-5xl font-flux text-black block mb-1">{{ stats.total_events }}</span>
                            <span class="text-xs font-bold uppercase tracking-wider text-gray-500">Eventos totales</span>
                        </div>
                    </div>

                    
                    <div class="bg-white p-6 md:p-8 rounded border border-gray-100 flex flex-col justify-between group hover:shadow-md hover:-translate-y-1 transition-all duration-300">
                        <div class="flex justify-between items-start mb-4">
                            <div class="w-10 h-10 rounded-full bg-gray-50 flex items-center justify-center group-hover:bg-green-50 transition-colors">
                                <CheckBadgeIcon class="h-5 w-5 text-gray-400 group-hover:text-green-600" />
                            </div>
                        </div>
                        <div>
                            <span class="text-4xl md:text-5xl font-flux text-black block mb-1">{{ stats.active_events }}</span>
                            <span class="text-xs font-bold uppercase tracking-wider text-gray-500">Publicados</span>
                        </div>
                    </div>

                  
                    <div class="bg-white p-6 md:p-8 rounded border border-gray-100 flex flex-col justify-between group hover:shadow-md hover:-translate-y-1 transition-all duration-300">
                        <div class="flex justify-between items-start mb-4">
                            <div class="w-10 h-10 rounded-full bg-gray-50 flex items-center justify-center group-hover:bg-red-50 transition-colors">
                                <PhotoIcon class="h-5 w-5 text-gray-400 group-hover:text-[#E30613]" />
                            </div>
                        </div>
                        <div>
                            <span class="text-4xl md:text-5xl font-flux text-[#E30613] block mb-1">{{ stats.total_photos }}</span>
                            <span class="text-xs font-bold uppercase tracking-wider text-gray-500">Material cargado</span>
                        </div>
                    </div>

                   
                    <div class="bg-white p-6 md:p-8 rounded border border-gray-100 flex flex-col justify-between group hover:shadow-md hover:-translate-y-1 transition-all duration-300">
                        <div class="flex justify-between items-start mb-4">
                            <div class="w-10 h-10 rounded-full bg-gray-50 flex items-center justify-center group-hover:bg-green-50 transition-colors">
                                <CurrencyDollarIcon class="h-5 w-5 text-gray-400 group-hover:text-green-600" />
                            </div>
                        </div>
                        <div>
                            <span class="text-4xl md:text-5xl font-flux text-black block mb-1">{{ stats.total_sales || 0 }}</span>
                            <span class="text-xs font-bold uppercase tracking-wider text-gray-500">Ventas registradas</span>
                        </div>
                    </div>

                </div>

             
                <div v-if="!events.data || events.data.length === 0" class="text-center py-24 bg-white rounded border border-gray-100 shadow-sm flex flex-col items-center">
                    <div class="w-20 h-20 bg-gray-50 rounded-full flex items-center justify-center mb-6">
                        <CalendarIcon class="h-10 w-10 text-gray-300" />
                    </div>
                    <h4 class="text-4xl font-flux text-black mb-3">Sin eventos</h4>
                    <p class="text-sm font-medium text-gray-500 mb-8 max-w-md mx-auto">Aún no se creó ninguna galería. Empezá a organizar tus sesiones de fotos para vender tu material.</p>
                    <Link :href="route('photographer.events.create')"
                        class="inline-block bg-black text-white px-8 py-3.5 rounded-full text-xs font-bold uppercase tracking-wider hover:bg-[#E30613] hover:shadow-lg hover:shadow-red-500/30 hover:-translate-y-1 transition-all">
                        Crear primer evento
                    </Link>
                </div>

                
                <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 xl:gap-8">
                    <div v-for="event in events.data" :key="event.id"
                        class="group bg-white rounded overflow-hidden border border-gray-100 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300 flex flex-col"
                    >
                       
                        <div class="relative h-56 bg-gray-100 overflow-hidden shrink-0">
                            <img v-if="event.cover_image_url" :src="event.cover_image_url" 
                                :alt="event.name"
                                class="w-full h-full object-cover transition-transform duration-700 "
                                @error="handleImageError" 
                            />
                            <div v-else class="w-full h-full flex flex-col items-center justify-center text-gray-300">
                                <PhotoIcon class="w-12 h-12 mb-2" />
                            </div>

                            <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none"></div>
                            
                         
                            <div class="absolute top-4 left-4 flex gap-2">
                                <span :class="[
                                    'px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider shadow-sm backdrop-blur-md',
                                    event.is_private 
                                        ? 'bg-red-50 text-[#E30613]' 
                                        : 'bg-white/90 text-black'
                                ]">
                                    {{ event.is_private ? 'Privado' : 'Público' }}
                                </span>
                            </div>
                        </div>

                      
                        <div class="p-6 md:p-8 flex-1 flex flex-col bg-white">
                            
                        
                            <div class="mb-4">
                                <h3 class="text-2xl font-flux text-black leading-none mb-2 group-hover:text-[#E30613] transition-colors line-clamp-1">
                                    {{ event.name }}
                                </h3>
                                <div class="flex items-center gap-4 text-xs font-bold text-gray-400 uppercase tracking-wider">
                                    <span class="flex items-center gap-1.5 bg-gray-50 px-2 py-1 rounded-md">
                                        <CalendarIcon class="w-4 h-4 text-gray-400" />
                                        {{ formatDate(event.event_date) }}
                                    </span>
                                </div>
                            </div>

                          
                            <div v-if="event.location" class="flex items-center text-xs font-bold text-gray-500 mb-4">
                                <MapPinIcon class="w-4 h-4 mr-1.5 text-[#E30613]" />
                                <span class="truncate">{{ event.location }}</span>
                            </div>

                         
                            <p v-if="event.description" class="text-sm text-gray-500 line-clamp-2 mb-6 leading-relaxed flex-1">
                                {{ event.description }}
                            </p>

                         
                            <div class="mt-auto pt-6 border-t border-gray-100 flex items-center justify-between">
                                <div class="text-xs font-bold text-gray-500 uppercase tracking-wider flex items-center gap-1.5">
                                    <PhotoIcon class="w-4 h-4" />
                                    <strong class="text-black">{{ event.photos_count || 0 }}</strong> fotos
                                </div>
                                
                                <div class="flex gap-2">
                                    <Link :href="route('photographer.events.show', event.id)" title="Ver galería"
                                        class="w-10 h-10 flex items-center justify-center rounded-full bg-gray-50 text-gray-600 hover:bg-black hover:text-white transition-colors">
                                        <EyeIcon class="w-5 h-5" />
                                    </Link>
                                    <Link :href="route('photographer.events.edit', event.id)" title="Editar detalles"
                                        class="w-10 h-10 flex items-center justify-center rounded-full bg-gray-50 text-gray-600 hover:bg-black hover:text-white transition-colors">
                                        <PencilSquareIcon class="w-5 h-5" />
                                    </Link>

                                    <button  @click="deleteEvent(event.id)" title="Eliminar evento"
                                        class="w-10 h-10 flex items-center justify-center rounded-full bg-red-50 text-[#E30613] hover:bg-[#E30613] hover:text-white hover:shadow-lg hover:shadow-red-500/30 transition-all">
                                        <TrashIcon class="w-5 h-5" />
                                    </button>

                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            
                <div v-if="events.last_page > 1" class="mt-16 flex justify-center">
                    <div class="flex flex-wrap gap-2 bg-white p-2 rounded-full shadow-sm border border-gray-100">
                        <template v-for="(link, index) in events.links" :key="index">
                            <Link v-if="link.url" :href="link.url" 
                                class="min-w-[40px] h-10 flex items-center justify-center px-4 rounded-full text-xs font-bold transition-colors"
                                :class="link.active 
                                    ? 'bg-[#E30613] text-white shadow-md' 
                                    : 'bg-transparent text-gray-600 hover:bg-gray-100 hover:text-black'"
                            >
                                <span v-html="link.label"></span>
                            </Link>
                            <span v-else v-html="link.label" class="min-w-[40px] h-10 flex items-center justify-center px-4 rounded-full text-xs font-bold text-gray-300 cursor-not-allowed"></span>
                        </template>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>