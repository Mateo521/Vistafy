<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import {
    EnvelopeIcon,
    EnvelopeOpenIcon,
    MagnifyingGlassIcon,
    TrashIcon,
    EyeIcon,
    XMarkIcon
} from '@heroicons/vue/24/outline';

const props = defineProps({
    messages: Object,
    stats: Object,
    filters: Object,
});

const searchForm = useForm({
    search: props.filters.search || '',
    status: props.filters.status || '',
});

const search = () => {
    searchForm.get(route('admin.messages.index'), {
        preserveState: true,
        preserveScroll: true,
    });
};

const clearFilters = () => {
    searchForm.reset();
    search();
};

const toggleRead = (message) => {
    router.patch(route('admin.messages.toggle-read', message.id), {}, {
        preserveScroll: true,
    });
};

const deleteMessage = (message) => {
    if (confirm('¿Estás seguro de purgar este mensaje del sistema?')) {
        router.delete(route('admin.messages.destroy', message.id), {
            preserveScroll: true,
        });
    }
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('es-ES', {
        year: 'numeric',
        month: 'short',
        day: '2-digit',
        hour: '2-digit',
        minute: '2-digit'
    }).replace(/,/g, ' -').toUpperCase();
};
</script>

<template>
    <Head title="Mensajes de contacto admin" />

    <AuthenticatedLayout>
        <div class="min-h-screen bg-[#F8F9FA] text-slate-800 font-sans antialiased py-12 pt-28">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                
                
                <div class="mb-10 flex flex-col md:flex-row md:items-end justify-between gap-6 border-b border-gray-200 pb-8">
                    <div>
                        <span class="inline-flex items-center gap-2 bg-red-50 text-[#E30613] px-3 py-1.5 rounded-full text-[10px] font-bold uppercase tracking-widest mb-3 border border-red-100">
                            <span class="w-2 h-2 rounded-full bg-[#E30613] animate-pulse"></span>
                            Modulo de mensajes
                        </span>
                        <h1 class="text-4xl md:text-6xl font-flux text-black tracking-wide leading-none">
                            Mensajes de <span class="text-[#E30613]">Contacto</span>
                        </h1>
                    </div>

                    <Link :href="route('admin.dashboard')" 
                        class="inline-flex items-center gap-2 bg-white px-5 py-2.5 rounded-full text-xs font-bold uppercase tracking-wider text-gray-500 hover:text-black hover:shadow-sm border border-gray-200 transition-all w-max">
                        <ArrowLeftIcon class="w-4 h-4" /> Volver al panel
                    </Link>
                </div>

                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                    
                    
                    <div class="bg-white rounded-3xl p-6 border border-gray-100 shadow-sm relative overflow-hidden group">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-[10px] font-bold uppercase tracking-widest text-gray-400 mb-1">Total recepciones</p>
                                <p class="text-4xl font-flux text-black leading-none">{{ stats.total }}</p>
                            </div>
                            <div class="w-12 h-12 bg-gray-50 rounded-full flex items-center justify-center text-gray-400 group-hover:scale-110 transition-transform">
                                <EnvelopeIcon class="w-6 h-6" />
                            </div>
                        </div>
                    </div>

                    
                    <div class="bg-red-50 rounded-3xl p-6 border border-red-100 shadow-sm relative overflow-hidden group">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-[10px] font-bold uppercase tracking-widest text-red-600 mb-1">Requieren atención</p>
                                <p class="text-4xl font-flux text-[#E30613] leading-none">{{ stats.unread }}</p>
                            </div>
                            <div class="w-12 h-12 bg-white rounded-full flex items-center justify-center text-[#E30613] shadow-sm group-hover:scale-110 transition-transform">
                                <EnvelopeIcon class="w-6 h-6" />
                            </div>
                        </div>
                    </div>

                    
                    <div class="bg-white rounded-3xl p-6 border border-gray-100 shadow-sm relative overflow-hidden group">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-[10px] font-bold uppercase tracking-widest text-gray-400 mb-1">Procesados</p>
                                <p class="text-4xl font-flux text-gray-500 leading-none">{{ stats.read }}</p>
                            </div>
                            <div class="w-12 h-12 bg-gray-50 rounded-full flex items-center justify-center text-gray-400 group-hover:scale-110 transition-transform">
                                <EnvelopeOpenIcon class="w-6 h-6" />
                            </div>
                        </div>
                    </div>
                </div>

            
                <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-6 mb-8">
                    <form @submit.prevent="search" class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            
                        
                            <div class="md:col-span-2">
                                <div class="relative group">
                                    <MagnifyingGlassIcon class="w-5 h-5 text-gray-400 absolute left-4 top-1/2 -translate-y-1/2 group-focus-within:text-[#E30613] transition-colors" />
                                    <input 
                                        v-model="searchForm.search"
                                        type="text" 
                                        placeholder="Buscar por nombre, email o asunto..."
                                        class="w-full pl-12 pr-4 py-3.5 bg-gray-50 border border-transparent text-slate-800 font-medium text-sm rounded-2xl focus:bg-white focus:border-gray-300 focus:ring-4 focus:ring-gray-100 transition-all outline-none placeholder-gray-400"
                                    />
                                </div>
                            </div>

                        
                            <div>
                                <select 
                                    v-model="searchForm.status"
                                    class="w-full px-4 py-3.5 bg-gray-50 border border-transparent text-slate-700 font-bold text-xs uppercase tracking-wider rounded-2xl focus:bg-white focus:border-gray-300 focus:ring-4 focus:ring-gray-100 transition-all outline-none appearance-none cursor-pointer"
                                >
                                    <option value="">Todos los mensajes</option>
                                    <option value="unread">No leídos (nuevos)</option>
                                    <option value="read">Leídos (procesados)</option>
                                </select>
                            </div>
                        </div>

                    
                        <div class="flex flex-col sm:flex-row gap-3 pt-2">
                            <button 
                                type="submit"
                                class="px-8 py-3.5 bg-black text-white rounded-full text-xs font-bold uppercase tracking-wider hover:bg-[#E30613] hover:shadow-lg hover:shadow-red-500/30 transition-all shadow-sm">
                                Filtrar resultados
                            </button>
                            <button 
                                type="button"
                                @click="clearFilters"
                                v-if="searchForm.search || searchForm.status"
                                class="px-8 py-3.5 bg-white border border-gray-200 text-gray-600 rounded-full text-xs font-bold uppercase tracking-wider hover:bg-gray-50 hover:text-black transition-colors flex items-center justify-center gap-2 shadow-sm">
                                <XMarkIcon class="w-4 h-4" /> Limpiar filtros
                            </button>
                        </div>
                    </form>
                </div>

             
                <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden mb-12">
                    
                    
                    <div v-if="messages.data.length === 0" class="py-24 text-center px-4">
                        <div class="w-20 h-20 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-6">
                            <EnvelopeOpenIcon class="w-10 h-10 text-gray-300" />
                        </div>
                        <h3 class="font-flux text-3xl text-black mb-2">Bandeja vacía</h3>
                        <p class="text-sm font-medium text-gray-500">No se encontraron mensajes que coincidan con los parámetros.</p>
                    </div>


                    <div v-else class="divide-y divide-gray-100">
                        <div 
                            v-for="message in messages.data" 
                            :key="message.id"
                            :class="[
                                'p-6 transition-all duration-300 relative',
                                !message.is_read ? 'bg-red-50/40 hover:bg-red-50/70' : 'bg-white hover:bg-gray-50/50'
                            ]"
                        >
                            <div class="flex flex-col lg:flex-row lg:items-start justify-between gap-6">
                                

                                <div class="flex items-start gap-5 flex-1 min-w-0">
                                    

                                    <div class="pt-1 flex-shrink-0">
                                        <div :class="['w-10 h-10 rounded-full flex items-center justify-center shadow-sm', !message.is_read ? 'bg-white text-[#E30613]' : 'bg-gray-50 text-gray-400']">
                                            <EnvelopeIcon v-if="!message.is_read" class="w-5 h-5" />
                                            <EnvelopeOpenIcon v-else class="w-5 h-5" />
                                        </div>
                                    </div>

                                    <div class="flex-1 min-w-0">
                                        <div class="flex flex-wrap items-center gap-3 mb-1">
                                            <h3 :class="['font-bold text-lg truncate', !message.is_read ? 'text-black' : 'text-slate-700']">
                                                {{ message.name }}
                                            </h3>
                                            <span v-if="!message.is_read" class="px-2 py-0.5 bg-[#E30613] text-white rounded text-[10px] font-bold uppercase tracking-widest shadow-sm">
                                                Nuevo
                                            </span>
                                        </div>
                                        
                                        <div class="text-xs font-medium text-gray-500 mb-4 flex flex-wrap items-center gap-x-4 gap-y-2">
                                            <a :href="`mailto:${message.email}`" class="hover:text-black transition-colors flex items-center gap-1.5">
                                                <EnvelopeIcon class="w-3.5 h-3.5" /> {{ message.email }}
                                            </a>
                                            <span v-if="message.phone" class="flex items-center gap-1.5 border-l border-gray-200 pl-4">
                                                Tel: {{ message.phone }}
                                            </span>
                                        </div>
                                        
                                        <p class="font-bold text-sm text-slate-800 mb-1.5 uppercase tracking-wide">
                                            Asunto: {{ message.subject }}
                                        </p>
                                        
                                        <p class="text-sm text-gray-600 line-clamp-2 leading-relaxed mb-4">
                                            {{ message.message }}
                                        </p>

                                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">
                                            Recibido: {{ formatDate(message.created_at) }}
                                        </p>
                                    </div>
                                </div>


                                <div class="flex items-center gap-2 lg:flex-col lg:justify-start flex-shrink-0">
                                    <Link 
                                        :href="route('admin.messages.show', message.id)"
                                        class="w-10 h-10 flex items-center justify-center rounded-full bg-white border border-gray-200 text-gray-500 hover:text-black hover:border-black hover:shadow-sm transition-all"
                                        title="Ver detalles"
                                    >
                                        <EyeIcon class="w-5 h-5" />
                                    </Link>

                                    <button 
                                        @click="toggleRead(message)"
                                        class="w-10 h-10 flex items-center justify-center rounded-full bg-white border border-gray-200 text-gray-500 hover:text-blue-600 hover:border-blue-600 hover:shadow-sm transition-all"
                                        :title="message.is_read ? 'Marcar como no leído' : 'Marcar como leído'"
                                    >
                                        <EnvelopeIcon v-if="message.is_read" class="w-5 h-5" />
                                        <EnvelopeOpenIcon v-else class="w-5 h-5" />
                                    </button>

                                    <button 
                                        @click="deleteMessage(message)"
                                        class="w-10 h-10 flex items-center justify-center rounded-full bg-white border border-gray-200 text-gray-500 hover:bg-red-50 hover:text-[#E30613] hover:border-red-200 hover:shadow-sm transition-all"
                                        title="Eliminar mensaje"
                                    >
                                        <TrashIcon class="w-5 h-5" />
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div v-if="messages.links.length > 3" class="bg-gray-50 border-t border-gray-100 px-6 py-4 flex flex-col md:flex-row items-center justify-between gap-4">
                        <div class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">
                            Mostrando {{ messages.from }} a {{ messages.to }} de {{ messages.total }} registros
                        </div>
                        <div class="flex flex-wrap gap-1">
                            <Link 
                                v-for="(link, index) in messages.links" 
                                :key="index"
                                :href="link.url || '#'"
                                v-html="link.label"
                                :class="[
                                    'min-w-[32px] h-8 flex items-center justify-center px-2 text-xs font-bold rounded-full transition-colors',
                                    link.active 
                                        ? 'bg-black text-white shadow-md' 
                                        : 'bg-transparent text-gray-500 hover:bg-gray-200 hover:text-black',
                                    !link.url && 'text-gray-300 cursor-not-allowed pointer-events-none'
                                ]"
                                :disabled="!link.url"
                            />
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>