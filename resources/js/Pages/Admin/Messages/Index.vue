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
    <AuthenticatedLayout>
        <Head title="[ADMIN] Mensajes de Contacto" />

        <div class="min-h-screen bg-[#050505] text-white selection:bg-[#E30613] selection:text-black py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                
                <div class="mb-8 flex flex-col md:flex-row md:items-end justify-between gap-6 border-b border-zinc-800 pb-6">
                    <div>
                        <span class="font-mono text-[10px] font-bold text-[#E30613] uppercase tracking-widest mb-2 block flex items-center gap-2">
                            <span class="w-2 h-2 bg-[#E30613] animate-pulse"></span>
                            Módulo de Comunicaciones
                        </span>
                        <h1 class="text-5xl md:text-6xl font-flux font-black text-white uppercase tracking-tighter leading-none">
                            Mensajes de Contacto
                        </h1>
                    </div>

                    <Link :href="route('admin.dashboard')" 
                        class="px-6 py-3 border border-zinc-800 bg-transparent text-[10px] font-mono font-bold uppercase tracking-widest text-zinc-400 hover:border-white hover:text-black hover:bg-white transition-colors text-center w-max">
                        ← Volver al Dashboard
                    </Link>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                    <div class="bg-[#09090b] p-6 border border-zinc-800 shadow-[4px_4px_0px_0px_rgba(255,255,255,0.05)]">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="font-mono text-[10px] font-bold uppercase tracking-widest text-zinc-500 mb-2">Total Recepciones</p>
                                <p class="text-5xl font-flux text-white leading-none">{{ stats.total }}</p>
                            </div>
                            <EnvelopeIcon class="w-10 h-10 text-zinc-700" />
                        </div>
                    </div>

                    <div class="bg-[#E30613]/5 p-6 border border-[#E30613] shadow-[4px_4px_0px_0px_rgba(227,6,19,1)]">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="font-mono text-[10px] font-bold uppercase tracking-widest text-[#E30613] mb-2">Requieren Atención</p>
                                <p class="text-5xl font-flux text-[#E30613] leading-none">{{ stats.unread }}</p>
                            </div>
                            <EnvelopeIcon class="w-10 h-10 text-[#E30613]" />
                        </div>
                    </div>

                    <div class="bg-black p-6 border border-zinc-800 shadow-[4px_4px_0px_0px_rgba(255,255,255,0.05)]">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="font-mono text-[10px] font-bold uppercase tracking-widest text-zinc-500 mb-2">Procesados</p>
                                <p class="text-5xl font-flux text-zinc-500 leading-none">{{ stats.read }}</p>
                            </div>
                            <EnvelopeOpenIcon class="w-10 h-10 text-zinc-800" />
                        </div>
                    </div>
                </div>

                <div class="bg-[#09090b] border border-zinc-800 p-6 mb-6">
                    <form @submit.prevent="search" class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            
                            <div class="md:col-span-2">
                                <div class="relative">
                                    <input 
                                        v-model="searchForm.search"
                                        type="text" 
                                        placeholder="BUSCAR POR NOMBRE, EMAIL O ASUNTO..."
                                        class="w-full pl-10 pr-4 py-3 bg-black border border-zinc-700 text-white font-mono text-xs uppercase placeholder-zinc-600 focus:border-[#E30613] focus:ring-0 rounded-none transition-colors"
                                    />
                                    <MagnifyingGlassIcon class="w-5 h-5 text-zinc-500 absolute left-3 top-3.5" />
                                </div>
                            </div>

                            <div>
                                <select 
                                    v-model="searchForm.status"
                                    class="w-full px-4 py-3 bg-black border border-zinc-700 text-zinc-300 font-mono text-xs uppercase focus:border-[#E30613] focus:ring-0 rounded-none transition-colors appearance-none cursor-pointer"
                                >
                                    <option value="">TODOS LOS MENSAJES</option>
                                    <option value="unread">NO LEÍDOS (NUEVOS)</option>
                                    <option value="read">LEÍDOS (PROCESADOS)</option>
                                </select>
                            </div>
                        </div>

                        <div class="flex gap-4 border-t border-zinc-800 pt-4">
                            <button 
                                type="submit"
                                class="px-8 py-3 bg-[#E30613] text-black border border-[#E30613] rounded-none text-[10px] font-mono font-bold uppercase tracking-widest hover:bg-white hover:border-white transition-colors">
                                Ejecutar Búsqueda
                            </button>
                            <button 
                                type="button"
                                @click="clearFilters"
                                v-if="searchForm.search || searchForm.status"
                                class="px-8 py-3 bg-transparent text-zinc-400 border border-zinc-700 rounded-none text-[10px] font-mono font-bold uppercase tracking-widest hover:text-white hover:border-white transition-colors flex items-center gap-2">
                                <XMarkIcon class="w-3 h-3" /> Purgar Filtros
                            </button>
                        </div>
                    </form>
                </div>

                <div class="bg-[#09090b] border border-zinc-800 overflow-hidden">
                    
                    <div v-if="messages.data.length === 0" class="p-16 text-center border-dashed border-2 border-zinc-800 m-4">
                        <EnvelopeOpenIcon class="w-16 h-16 text-zinc-800 mx-auto mb-4" />
                        <h3 class="font-flux text-3xl uppercase tracking-widest text-zinc-500 mb-2">BANDEJA_VACÍA</h3>
                        <p class="font-mono text-xs text-zinc-600">No se encontraron registros que coincidan con los parámetros.</p>
                    </div>

                    <div v-else class="divide-y divide-zinc-800">
                        <div 
                            v-for="message in messages.data" 
                            :key="message.id"
                            :class="[
                                'p-6 transition-all duration-300 border-l-4 group relative',
                                !message.is_read ? 'bg-[#E30613]/5 border-l-[#E30613] hover:bg-[#E30613]/10' : 'bg-transparent border-l-transparent hover:bg-zinc-900'
                            ]"
                        >
                            <div class="flex flex-col lg:flex-row lg:items-start justify-between gap-6">
                                <div class="flex items-start gap-4 flex-1 min-w-0">
                                    
                                    <div class="pt-1 flex-shrink-0">
                                        <EnvelopeIcon v-if="!message.is_read" class="w-6 h-6 text-[#E30613]" />
                                        <EnvelopeOpenIcon v-else class="w-6 h-6 text-zinc-600" />
                                    </div>

                                    <div class="flex-1 min-w-0">
                                        <div class="flex flex-wrap items-center gap-3 mb-1">
                                            <h3 :class="['font-flux text-2xl uppercase tracking-wider line-clamp-1', !message.is_read ? 'text-white' : 'text-zinc-400']">
                                                {{ message.name }}
                                            </h3>
                                            <span v-if="!message.is_read" class="px-2 py-0.5 bg-[#E30613] text-black text-[9px] font-mono font-bold uppercase tracking-widest">
                                                NUEVO
                                            </span>
                                        </div>
                                        
                                        <div class="font-mono text-[10px] text-zinc-500 mb-4 flex flex-wrap items-center gap-x-4 gap-y-2 uppercase tracking-widest">
                                            <a :href="`mailto:${message.email}`" class="hover:text-white transition-colors text-zinc-300 flex items-center gap-1">
                                                >_ {{ message.email }}
                                            </a>
                                            <span v-if="message.phone" class="border-l border-zinc-700 pl-4">
                                                TEL: {{ message.phone }}
                                            </span>
                                        </div>
                                        
                                        <p class="font-mono text-xs font-bold text-white mb-2 uppercase tracking-wide">
                                            ASUNTO: {{ message.subject }}
                                        </p>
                                        
                                        <p class="font-sans text-sm text-zinc-400 line-clamp-2 leading-relaxed mb-4">
                                            {{ message.message }}
                                        </p>

                                        <p class="font-mono text-[10px] text-zinc-600 font-bold tracking-widest uppercase">
                                            RECIBIDO: {{ formatDate(message.created_at) }}
                                        </p>
                                    </div>
                                </div>

                                <div class="flex items-center gap-3 lg:flex-col lg:justify-start flex-shrink-0">
                                    <Link 
                                        :href="route('admin.messages.show', message.id)"
                                        class="w-10 h-10 flex items-center justify-center border border-zinc-700 bg-black hover:border-white hover:bg-white text-zinc-400 hover:text-black transition-colors"
                                        title="Ver Detalles"
                                    >
                                        <EyeIcon class="w-4 h-4" />
                                    </Link>

                                    <button 
                                        @click="toggleRead(message)"
                                        class="w-10 h-10 flex items-center justify-center border border-zinc-700 bg-black hover:border-white hover:bg-white text-zinc-400 hover:text-black transition-colors"
                                        :title="message.is_read ? 'Marcar como no leído' : 'Marcar como leído'"
                                    >
                                        <EnvelopeIcon v-if="message.is_read" class="w-4 h-4" />
                                        <EnvelopeOpenIcon v-else class="w-4 h-4" />
                                    </button>

                                    <button 
                                        @click="deleteMessage(message)"
                                        class="w-10 h-10 flex items-center justify-center border border-zinc-700 bg-black hover:border-[#E30613] hover:bg-[#E30613] text-zinc-400 hover:text-black transition-colors"
                                        title="Purgar Mensaje"
                                    >
                                        <TrashIcon class="w-4 h-4" />
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div v-if="messages.links.length > 3" class="bg-black border-t border-zinc-800 px-6 py-4 flex flex-col md:flex-row items-center justify-between gap-4">
                        <div class="font-mono text-[10px] uppercase font-bold tracking-widest text-zinc-500">
                            MOSTRANDO REGISTROS {{ messages.from }} AL {{ messages.to }} DE {{ messages.total }}
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <Link 
                                v-for="(link, index) in messages.links" 
                                :key="index"
                                :href="link.url"
                                v-html="link.label"
                                :class="[
                                    'px-3 py-2 border font-mono text-[10px] font-bold transition-colors uppercase',
                                    link.active 
                                        ? 'bg-[#E30613] text-black border-[#E30613]' 
                                        : 'bg-black text-zinc-400 border-zinc-800 hover:text-white hover:border-white',
                                    !link.url && 'opacity-20 cursor-not-allowed pointer-events-none'
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