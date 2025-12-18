<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import {
    EnvelopeIcon,
    PhoneIcon,
    CalendarIcon,
    ArrowLeftIcon,
    TrashIcon,
    EnvelopeOpenIcon
} from '@heroicons/vue/24/outline';

const props = defineProps({
    message: Object,
});

const toggleRead = () => {
    router.patch(route('admin.messages.toggle-read', props.message.id), {}, {
        preserveScroll: true,
    });
};

const deleteMessage = () => {
    if (confirm('¿Estás seguro de eliminar este mensaje?')) {
        router.delete(route('admin.messages.destroy', props.message.id));
    }
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('es-ES', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};
</script>

<template>
    <AuthenticatedLayout>
        <Head :title="`Mensaje de ${message.name}`" />

        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                
                <!-- Header -->
                <div class="mb-8">
                    <Link 
                        :href="route('admin.messages.index')" 
                        class="inline-flex items-center gap-2 text-sm text-slate-600 hover:text-slate-900 transition mb-4"
                    >
                        <ArrowLeftIcon class="w-4 h-4" />
                        Volver a mensajes
                    </Link>

                    <div class="flex items-start justify-between">
                        <div>
                            <span class="text-xs font-bold tracking-[0.2em] text-slate-400 uppercase mb-2 block">
                                Mensaje de Contacto
                            </span>
                            <h1 class="text-3xl font-sans font-bold text-slate-900">
                                {{ message.subject }}
                            </h1>
                        </div>

                        <div class="flex gap-2">
                            <button 
                                @click="toggleRead"
                                class="px-4 py-2 border border-gray-300 rounded-sm text-xs font-bold uppercase tracking-widest hover:border-slate-900 transition flex items-center gap-2"
                            >
                                <EnvelopeOpenIcon v-if="message.is_read" class="w-4 h-4" />
                                <EnvelopeIcon v-else class="w-4 h-4" />
                                {{ message.is_read ? 'Marcar no leído' : 'Marcar leído' }}
                            </button>

                            <button 
                                @click="deleteMessage"
                                class="px-4 py-2 border border-red-300 text-red-600 rounded-sm text-xs font-bold uppercase tracking-widest hover:bg-red-50 transition flex items-center gap-2"
                            >
                                <TrashIcon class="w-4 h-4" />
                                Eliminar
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Información del remitente -->
                <div class="bg-white border border-gray-200 rounded-sm shadow-sm p-8 mb-6">
                    <h2 class="text-sm font-bold uppercase tracking-widest text-slate-900 mb-6">
                        Información del Remitente
                    </h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="flex items-start gap-4">
                            <div class="p-3 bg-slate-50 border border-gray-200 rounded-sm">
                                <svg class="w-5 h-5 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                            </div>
                            <div>
                                <p class="text-xs font-bold uppercase tracking-wide text-slate-500 mb-1">Nombre</p>
                                <p class="text-slate-900 font-semibold">{{ message.name }}</p>
                            </div>
                        </div>

                        <div class="flex items-start gap-4">
                            <div class="p-3 bg-slate-50 border border-gray-200 rounded-sm">
                                <EnvelopeIcon class="w-5 h-5 text-slate-600" />
                            </div>
                            <div>
                                <p class="text-xs font-bold uppercase tracking-wide text-slate-500 mb-1">Email</p>
                                <a :href="`mailto:${message.email}`" 
                                    class="text-blue-600 hover:text-blue-800 transition font-semibold">
                                    {{ message.email }}
                                </a>
                            </div>
                        </div>

                        <div v-if="message.phone" class="flex items-start gap-4">
                            <div class="p-3 bg-slate-50 border border-gray-200 rounded-sm">
                                <PhoneIcon class="w-5 h-5 text-slate-600" />
                            </div>
                            <div>
                                <p class="text-xs font-bold uppercase tracking-wide text-slate-500 mb-1">Teléfono</p>
                                <a :href="`tel:${message.phone}`" 
                                    class="text-slate-900 hover:text-blue-600 transition font-semibold">
                                    {{ message.phone }}
                                </a>
                            </div>
                        </div>

                        <div class="flex items-start gap-4">
                            <div class="p-3 bg-slate-50 border border-gray-200 rounded-sm">
                                <CalendarIcon class="w-5 h-5 text-slate-600" />
                            </div>
                            <div>
                                <p class="text-xs font-bold uppercase tracking-wide text-slate-500 mb-1">Fecha</p>
                                <p class="text-slate-900">{{ formatDate(message.created_at) }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Mensaje -->
                <div class="bg-white border border-gray-200 rounded-sm shadow-sm p-8">
                    <h2 class="text-sm font-bold uppercase tracking-widest text-slate-900 mb-6">
                        Mensaje
                    </h2>

                    <div class="prose prose-slate max-w-none">
                        <p class="text-slate-700 leading-relaxed whitespace-pre-line">{{ message.message }}</p>
                    </div>
                </div>

                <!-- Acciones rápidas -->
                <div class="mt-6 flex gap-3">
                    <a 
                        :href="`mailto:${message.email}?subject=Re: ${message.subject}`"
                        class="flex-1 bg-slate-900 text-white py-3 px-6 rounded-sm text-center text-xs font-bold uppercase tracking-widest hover:bg-slate-800 transition"
                    >
                        Responder por Email
                    </a>
                    
                    <a 
                        v-if="message.phone"
                        :href="`tel:${message.phone}`"
                        class="flex-1 border border-slate-900 text-slate-900 py-3 px-6 rounded-sm text-center text-xs font-bold uppercase tracking-widest hover:bg-slate-50 transition"
                    >
                        Llamar
                    </a>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
