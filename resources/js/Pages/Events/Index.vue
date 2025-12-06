<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    events: Object,
});

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
                        Explore nuestra colección cronológica de coberturas fotográficas. Cada evento ha sido documentado preservando la esencia del momento.
                    </p>
                </div>
            </div>
        </div>

        <div class="min-h-screen bg-gray-50 py-12 md:py-16">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                
                <div v-if="!events.data || events.data.length === 0" class="flex flex-col items-center justify-center py-24 border border-dashed border-gray-300 rounded-sm bg-white">
                    <div class="w-16 h-16 text-gray-300 mb-4">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" /></svg>
                    </div>
                    <h3 class="text-xl font-serif font-medium text-slate-900 mb-2">Archivo no disponible</h3>
                    <p class="text-slate-500 mb-8 font-light">No hay eventos públicos para mostrar en este momento.</p>
                    <Link :href="route('home')"
                        class="text-xs font-bold uppercase tracking-widest text-slate-900 border-b border-slate-900 pb-1 hover:text-slate-600 hover:border-slate-600 transition">
                        Volver al inicio
                    </Link>
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

                            <div class="absolute bottom-0 left-0 w-full bg-gradient-to-t from-black/60 to-transparent p-4 opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                                <div class="flex items-center text-white/90 text-xs font-medium">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                    {{ event.photos_count || 0 }} fotografías
                                </div>
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
                                    <svg class="w-3 h-3 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
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
                                class="px-4 py-2 text-sm font-medium transition-colors border border-transparent"
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

        <div class="bg-slate-900 py-20 border-t border-slate-800">
            <div class="max-w-4xl mx-auto text-center px-4">
                <span class="text-xs font-bold tracking-[0.2em] text-slate-400 uppercase mb-4 block">
                    Búsqueda Directa
                </span>
                <h2 class="text-3xl font-serif font-bold text-white mb-6">
                    ¿Tiene un código de acceso?
                </h2>
                <p class="text-slate-400 mb-10 font-light text-lg">
                    Ingrese a su galería privada directamente utilizando el código único proporcionado en su evento.
                </p>
                <Link :href="route('gallery.index')"
                    class="inline-block border border-white text-white px-8 py-3 text-sm font-bold uppercase tracking-widest hover:bg-white hover:text-slate-900 transition-colors duration-300">
                    Ir a búsqueda
                </Link>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
/* Asegura que el footer se quede abajo si hay poco contenido */
.min-h-screen {
    min-height: calc(100vh - 80px); /* Ajuste aproximado por el header */
}
</style>