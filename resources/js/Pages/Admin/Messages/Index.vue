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
    FunnelIcon,
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
        onSuccess: () => {
            // Recargar la página actual
        }
    });
};

const deleteMessage = (message) => {
    if (confirm('¿Estás seguro de eliminar este mensaje?')) {
        router.delete(route('admin.messages.destroy', message.id), {
            preserveScroll: true,
        });
    }
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('es-ES', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Mensajes de Contacto" />

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                
                <!-- Header -->
                <div class="mb-8 flex items-center justify-between">
                    <div>
                        <span class="text-xs font-bold tracking-[0.2em] text-slate-400 uppercase mb-2 block">
                            Centro de Mensajes
                        </span>
                        <h1 class="text-3xl font-sans font-bold text-slate-900">
                            Mensajes de Contacto
                        </h1>
                    </div>

                    <Link :href="route('admin.dashboard')" 
                        class="px-4 py-2 border border-gray-300 rounded-sm text-xs font-bold uppercase tracking-widest text-slate-600 hover:border-slate-900 hover:text-slate-900 transition">
                        ← Volver al Dashboard
                    </Link>
                </div>

                <!-- Stats Cards -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                    <div class="bg-white p-6 border border-gray-200 rounded-sm shadow-sm">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-xs font-bold uppercase tracking-widest text-slate-500 mb-2">Total</p>
                                <p class="text-3xl font-sans text-slate-900">{{ stats.total }}</p>
                            </div>
                            <EnvelopeIcon class="w-10 h-10 text-slate-300" />
                        </div>
                    </div>

                    <div class="bg-blue-50 p-6 border-2 border-blue-200 rounded-sm shadow-sm">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-xs font-bold uppercase tracking-widest text-blue-600 mb-2">No Leídos</p>
                                <p class="text-3xl font-sans text-blue-900">{{ stats.unread }}</p>
                            </div>
                            <EnvelopeIcon class="w-10 h-10 text-blue-300" />
                        </div>
                    </div>

                    <div class="bg-white p-6 border border-gray-200 rounded-sm shadow-sm">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-xs font-bold uppercase tracking-widest text-slate-500 mb-2">Leídos</p>
                                <p class="text-3xl font-sans text-slate-900">{{ stats.read }}</p>
                            </div>
                            <EnvelopeOpenIcon class="w-10 h-10 text-slate-300" />
                        </div>
                    </div>
                </div>

                <!-- Filtros y Búsqueda -->
                <div class="bg-white border border-gray-200 rounded-sm shadow-sm p-6 mb-6">
                    <form @submit.prevent="search" class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <!-- Búsqueda -->
                            <div class="md:col-span-2">
                                <div class="relative">
                                    <input 
                                        v-model="searchForm.search"
                                        type="text" 
                                        placeholder="Buscar por nombre, email o asunto..."
                                        class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-sm focus:border-slate-900 focus:ring-0 text-sm"
                                    />
                                    <MagnifyingGlassIcon class="w-5 h-5 text-gray-400 absolute left-3 top-3.5" />
                                </div>
                            </div>

                            <!-- Filtro Estado -->
                            <div>
                                <select 
                                    v-model="searchForm.status"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-sm focus:border-slate-900 focus:ring-0 text-sm"
                                >
                                    <option value="">Todos los mensajes</option>
                                    <option value="unread">No leídos</option>
                                    <option value="read">Leídos</option>
                                </select>
                            </div>
                        </div>

                        <div class="flex gap-3">
                            <button 
                                type="submit"
                                class="px-6 py-2 bg-slate-900 text-white rounded-sm text-xs font-bold uppercase tracking-widest hover:bg-slate-800 transition">
                                Buscar
                            </button>
                            <button 
                                type="button"
                                @click="clearFilters"
                                v-if="searchForm.search || searchForm.status"
                                class="px-6 py-2 border border-gray-300 text-slate-600 rounded-sm text-xs font-bold uppercase tracking-widest hover:border-slate-900 hover:text-slate-900 transition">
                                Limpiar
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Lista de Mensajes -->
                <div class="bg-white border border-gray-200 rounded-sm shadow-sm overflow-hidden">
                    
                    <div v-if="messages.data.length === 0" class="p-12 text-center">
                        <EnvelopeOpenIcon class="w-16 h-16 text-gray-300 mx-auto mb-4" />
                        <h3 class="text-lg font-bold text-slate-900 mb-2">No hay mensajes</h3>
                        <p class="text-slate-500">No se encontraron mensajes con los filtros aplicados.</p>
                    </div>

                    <div v-else class="divide-y divide-gray-100">
                        <div 
                            v-for="message in messages.data" 
                            :key="message.id"
                            :class="[
                                'p-6 hover:bg-gray-50 transition-colors',
                                !message.is_read && 'bg-blue-50/30'
                            ]"
                        >
                            <div class="flex items-start justify-between gap-4">
                                <div class="flex items-start gap-4 flex-1 min-w-0">
                                    <!-- Indicador de estado -->
                                    <div class="pt-1">
                                        <EnvelopeIcon 
                                            v-if="!message.is_read" 
                                            class="w-6 h-6 text-blue-600" 
                                        />
                                        <EnvelopeOpenIcon 
                                            v-else 
                                            class="w-6 h-6 text-slate-300" 
                                        />
                                    </div>

                                    <!-- Contenido -->
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center gap-3 mb-2">
                                            <h3 :class="[
                                                'font-bold text-slate-900',
                                                !message.is_read && 'text-blue-900'
                                            ]">
                                                {{ message.name }}
                                            </h3>
                                            <span v-if="!message.is_read" 
                                                class="px-2 py-0.5 bg-blue-600 text-white text-[10px] font-bold uppercase tracking-wider rounded">
                                                Nuevo
                                            </span>
                                        </div>
                                        
                                        <p class="text-sm text-slate-600 mb-1">
                                            <a :href="`mailto:${message.email}`" class="hover:text-blue-600 transition">
                                                {{ message.email }}
                                            </a>
                                            <span v-if="message.phone" class="text-slate-400 ml-2">
                                                • {{ message.phone }}
                                            </span>
                                        </p>
                                        
                                        <p class="font-semibold text-slate-900 mb-2">
                                            {{ message.subject }}
                                        </p>
                                        
                                        <p class="text-sm text-slate-600 line-clamp-2 mb-3">
                                            {{ message.message }}
                                        </p>

                                        <p class="text-xs text-slate-400">
                                            {{ formatDate(message.created_at) }}
                                        </p>
                                    </div>
                                </div>

                                <!-- Acciones -->
                                <div class="flex items-center gap-2 flex-shrink-0">
                                    <Link 
                                        :href="route('admin.messages.show', message.id)"
                                        class="p-2 hover:bg-slate-100 rounded transition group"
                                        title="Ver detalles"
                                    >
                                        <EyeIcon class="w-5 h-5 text-slate-400 group-hover:text-slate-900" />
                                    </Link>

                                    <button 
                                        @click="toggleRead(message)"
                                        class="p-2 hover:bg-blue-100 rounded transition group"
                                        :title="message.is_read ? 'Marcar como no leído' : 'Marcar como leído'"
                                    >
                                        <EnvelopeIcon 
                                            v-if="message.is_read"
                                            class="w-5 h-5 text-slate-400 group-hover:text-blue-600" 
                                        />
                                        <EnvelopeOpenIcon 
                                            v-else
                                            class="w-5 h-5 text-blue-600 group-hover:text-blue-800" 
                                        />
                                    </button>

                                    <button 
                                        @click="deleteMessage(message)"
                                        class="p-2 hover:bg-red-100 rounded transition group"
                                        title="Eliminar"
                                    >
                                        <TrashIcon class="w-5 h-5 text-slate-400 group-hover:text-red-600" />
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Paginación -->
                    <div v-if="messages.links.length > 3" class="border-t border-gray-200 px-6 py-4 flex items-center justify-between">
                        <div class="text-sm text-slate-500">
                            Mostrando {{ messages.from }} - {{ messages.to }} de {{ messages.total }}
                        </div>
                        <div class="flex gap-2">
                            <Link 
                                v-for="(link, index) in messages.links" 
                                :key="index"
                                :href="link.url"
                                v-html="link.label"
                                :class="[
                                    'px-3 py-2 border rounded-sm text-xs font-bold transition',
                                    link.active 
                                        ? 'bg-slate-900 text-white border-slate-900' 
                                        : 'bg-white text-slate-600 border-gray-300 hover:border-slate-900',
                                    !link.url && 'opacity-50 cursor-not-allowed'
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
