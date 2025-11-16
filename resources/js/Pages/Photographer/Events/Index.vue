<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';

const props = defineProps({
    events: Object,
    stats: Object,
});

const deleteEvent = (eventId) => {
    if (confirm('¿Estás seguro de eliminar este evento? Se eliminarán todas las fotos asociadas.')) {
        router.delete(route('photographer.events.destroy', eventId), {
            preserveScroll: true,
        });
    }
};

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString('es-ES', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
};
</script>

<template>
    <Head title="Mis Eventos" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Mis Eventos
                </h2>
                <Link
                    :href="route('photographer.events.create')"
                    class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-3 rounded-lg font-semibold transition shadow-lg hover:shadow-xl inline-flex items-center"
                >
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Crear Evento
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                
                <!-- Stats Cards -->
                <div v-if="stats" class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                    <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg shadow-lg p-6 text-white">
                        <div class="text-3xl font-bold mb-2">{{ stats.total_events }}</div>
                        <div class="text-blue-100">Total de Eventos</div>
                    </div>
                    <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-lg shadow-lg p-6 text-white">
                        <div class="text-3xl font-bold mb-2">{{ stats.active_events }}</div>
                        <div class="text-green-100">Eventos Activos</div>
                    </div>
                    <div class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-lg shadow-lg p-6 text-white">
                        <div class="text-3xl font-bold mb-2">{{ stats.total_photos }}</div>
                        <div class="text-purple-100">Fotos Totales</div>
                    </div>
                    <div class="bg-gradient-to-br from-pink-500 to-pink-600 rounded-lg shadow-lg p-6 text-white">
                        <div class="text-3xl font-bold mb-2">{{ stats.total_sales || 0 }}</div>
                        <div class="text-pink-100">Ventas Totales</div>
                    </div>
                </div>

                <!-- Events Grid -->
                <div class="bg-white rounded-lg shadow-lg p-6">
                    <h3 class="text-2xl font-bold text-gray-900 mb-6">Mis Eventos</h3>

                    <!-- Empty State -->
                    <div v-if="!events.data || events.data.length === 0" class="text-center py-16">
                        <div class="text-6xl mb-4">📅</div>
                        <h4 class="text-xl font-semibold text-gray-900 mb-2">No tienes eventos creados</h4>
                        <p class="text-gray-600 mb-6">Crea tu primer evento para comenzar a subir fotos</p>
                        <Link
                            :href="route('photographer.events.create')"
                            class="inline-block bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-3 rounded-lg font-semibold transition"
                        >
                            ➕ Crear Primer Evento
                        </Link>
                    </div>

                    <!-- Events List -->
                    <div v-else>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-6">
                            <div
                                v-for="event in events.data"
                                :key="event.id"
                                class="bg-white border-2 border-gray-200 rounded-lg overflow-hidden hover:border-indigo-300 hover:shadow-xl transition"
                            >
                                <!-- Cover Image -->
                                <div class="relative h-48 bg-gradient-to-br from-indigo-400 to-purple-500">
                                     <img v-if="event.cover_image_url" :src="event.cover_image_url"
                        :alt="event.name"
                        class="w-full h-full object-cover transition-transform duration-300 hover:scale-110" @error="(e) => {
                            e.target.style.display = 'none';
                            e.target.nextElementSibling.style.display = 'flex';
                        }" />
                                    <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent"></div>
                                    
                                    <!-- Status Badge -->
                                    <div class="absolute top-3 right-3">
                                        <span
                                            :class="[
                                                'px-3 py-1 rounded-full text-xs font-bold shadow-lg',
                                                event.is_active
                                                    ? 'bg-green-500 text-white'
                                                    : 'bg-red-500 text-white'
                                            ]"
                                        >
                                            {{ event.is_active ? '✓ Activo' : '✗ Inactivo' }}
                                        </span>
                                    </div>

                                    <!-- Privacy Badge -->
                                    <div class="absolute top-3 left-3">
                                        <span
                                            :class="[
                                                'px-3 py-1 rounded-full text-xs font-bold shadow-lg',
                                                event.is_private
                                                    ? 'bg-yellow-500 text-yellow-900'
                                                    : 'bg-blue-500 text-white'
                                            ]"
                                        >
                                            {{ event.is_private ? '🔒 Privado' : '🌐 Público' }}
                                        </span>
                                    </div>

                                    <!-- Photo Count -->
                                    <div class="absolute bottom-3 right-3 bg-white/90 backdrop-blur-sm px-3 py-2 rounded-lg shadow-lg">
                                        <div class="flex items-center gap-2">
                                            <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                            </svg>
                                            <span class="text-sm font-bold text-gray-800">
                                                {{ event.photos_count || 0 }} fotos
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Event Info -->
                                <div class="p-5">
                                    <h4 class="text-lg font-bold text-gray-900 mb-2 line-clamp-1">
                                        {{ event.name }}
                                    </h4>
                                    
                                    <div class="space-y-2 text-sm text-gray-600 mb-4">
                                        <div class="flex items-center">
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                            </svg>
                                            {{ formatDate(event.event_date) }}
                                        </div>
                                        <div v-if="event.location" class="flex items-center">
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                            </svg>
                                            {{ event.location }}
                                        </div>
                                    </div>

                                    <!-- Actions -->
                                    <div class="flex gap-2">
                                        <Link
                                            :href="route('photographer.events.show', event.id)"
                                            class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white text-center px-4 py-2 rounded-lg font-semibold text-sm transition"
                                        >
                                            Ver
                                        </Link>
                                        <Link
                                            :href="route('photographer.events.edit', event.id)"
                                            class="flex-1 bg-gray-600 hover:bg-gray-700 text-white text-center px-4 py-2 rounded-lg font-semibold text-sm transition"
                                        >
                                            Editar
                                        </Link>
                                        <button
                                            @click="deleteEvent(event.id)"
                                            class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg font-semibold text-sm transition"
                                        >
                                            🗑️
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Pagination -->
                        <div v-if="events.last_page > 1" class="flex items-center justify-center gap-2 flex-wrap">
                            <template v-for="(link, index) in events.links" :key="index">
                                <Link
                                    v-if="link.url"
                                    :href="link.url"
                                    v-html="link.label"
                                    :class="[
                                        'px-4 py-2 rounded-lg font-medium transition',
                                        link.active
                                            ? 'bg-indigo-600 text-white shadow-lg'
                                            : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
                                    ]"
                                />
                                <span
                                    v-else
                                    v-html="link.label"
                                    class="px-4 py-2 rounded-lg font-medium bg-gray-100 text-gray-400 cursor-not-allowed"
                                />
                            </template>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
