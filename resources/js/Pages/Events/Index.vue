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
</script>


<template>

    <Head title="Eventos - Galería de Fotos" />

    
    <AppLayout>
        <!-- Hero Section -->
        <div class="bg-gradient-to-r  from-purple-600 to-indigo-600 pb-16 pt-24">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                <h1 class="text-4xl md:text-5xl font-bold text-white mb-4">
                    Eventos
                </h1>
                <p class="text-xl text-purple-100">
                    Explora los momentos capturados en cada evento
                </p>
            </div>
        </div>

        <!-- Events Grid -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <!-- Empty State -->
            <div v-if="!events.data || events.data.length === 0" class="text-center py-16">
                <div class="text-6xl mb-4"></div>
                <h3 class="text-2xl font-semibold text-gray-900 mb-2">No hay eventos disponibles</h3>
                <p class="text-gray-600 mb-6">Vuelve pronto para ver nuevos eventos</p>
                <Link :href="route('home')"
                    class="inline-block bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-3 rounded-lg font-semibold transition">
                Volver al inicio
                </Link>
            </div>

            <!-- Events Grid -->
            <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <Link v-for="event in events.data" :key="event.id" :href="route('events.show', event.slug)"
                    class="bg-white rounded-xl shadow-lg overflow-hidden hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-2">
                <!-- Thumbnail -->
                <div class="relative h-56 overflow-hidden">
                    <!-- Cover Image -->
                    <!-- Cover Image -->
                    <img v-if="event.cover_image_url" :src="event.cover_image_url"
                        :alt="event.name"
                        class="w-full h-full object-cover transition-transform duration-300 hover:scale-110" @error="(e) => {
                            e.target.style.display = 'none';
                            e.target.nextElementSibling.style.display = 'flex';
                        }" />

                    <!-- Gradient Fallback -->
                    <div :style="{ display: event.cover_image_url ? 'none' : 'flex' }"
                        class="w-full h-full bg-gradient-to-br from-purple-400 via-indigo-500 to-pink-500 items-center justify-center">
                        <svg class="w-24 h-24 text-white/50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>

                    

                    <!-- Overlay -->
                    <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-transparent to-transparent">
                        <div class="absolute bottom-3 left-3 right-3 flex items-center justify-between">
                            <!-- Photo Count -->
                            <div class="bg-white/95 backdrop-blur-sm px-4 py-2 rounded-full shadow-lg">
                                <div class="flex items-center gap-2">
                                    <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                    <span class="text-sm font-bold text-gray-800">
                                        {{ event.photos_count || 0 }} fotos
                                    </span>
                                </div>
                            </div>

                            <!-- Public Badge -->
                            <span class="bg-green-500 text-white px-3 py-1 rounded-full text-xs font-bold shadow-lg">
                                🌐 Público
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Event Info -->
                <div class="p-6">
                    <h3 class="text-2xl font-bold text-gray-900 mb-3 line-clamp-2">
                        {{ event.name }}
                    </h3>

                    <p v-if="event.description" class="text-gray-600 text-sm mb-4 line-clamp-3">
                        {{ event.description }}
                    </p>

                    <!-- Date -->
                    <div class="flex items-center text-sm text-gray-500 mb-2">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        {{ formatDate(event.event_date) }}
                    </div>

                    <!-- Location -->
                    <div v-if="event.location" class="flex items-center text-sm text-gray-500 mb-4">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        {{ event.location }}
                    </div>

                    <!-- Photographer -->
                    <div v-if="event.photographer" class="flex items-center text-sm text-gray-500">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        {{ event.photographer.business_name }}
                    </div>

                    <!-- CTA -->
                    <div class="mt-6 pt-4 border-t">
                        <div
                            class="flex items-center justify-center text-indigo-600 font-semibold text-sm hover:text-indigo-700">
                            Ver galería del evento
                            <svg class="ml-2 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 5l7 7-7 7" />
                            </svg>
                        </div>
                    </div>
                </div>
                </Link>
            </div>

            <!-- Pagination -->
            <div v-if="events.data && events.data.length > 0 && events.last_page > 1" class="mt-12">
                <div class="flex items-center justify-center gap-2">
                    <template v-for="(link, index) in events.links" :key="index">
                        <Link v-if="link.url" :href="link.url" v-html="link.label" :class="[
                            'px-4 py-2 rounded-lg font-medium transition',
                            link.active
                                ? 'bg-indigo-600 text-white shadow-lg'
                                : 'bg-white text-gray-700 hover:bg-gray-100 shadow'
                        ]" />
                        <span v-else v-html="link.label"
                            class="px-4 py-2 rounded-lg font-medium bg-gray-100 text-gray-400 cursor-not-allowed" />
                    </template>
                </div>
            </div>
        </div>

        <!-- CTA Section -->
        <div class="bg-gradient-to-r from-indigo-600 to-purple-600 py-16 mt-12">
            <div class="max-w-4xl mx-auto text-center px-4">
                <h2 class="text-3xl font-bold text-white mb-4">
                    ¿Buscas una foto específica?
                </h2>
                <p class="text-xl text-indigo-100 mb-8">
                    Usa el código único que recibiste para encontrar tus fotos
                </p>
                <Link :href="route('gallery.index')"
                    class="inline-block bg-white text-indigo-600 px-8 py-4 rounded-lg font-bold hover:bg-indigo-50 transition shadow-xl hover:shadow-2xl">
                Buscar mis fotos →
                </Link>
            </div>
        </div>
    </AppLayout>
</template>
