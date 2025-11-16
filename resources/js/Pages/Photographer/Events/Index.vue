<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';

const props = defineProps({
    events: Object,
});

const deleteEvent = (event) => {
    if (confirm(`¿Estás seguro de eliminar el evento "${event.name}"?`)) {
        router.delete(route('photographer.events.destroy', event.id), {
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
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Mis Eventos</h2>
                <Link
                    :href="route('photographer.events.create')"
                    class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg font-semibold transition"
                >
                    + Crear Evento
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Success Message -->
                <div v-if="$page.props.flash?.success" class="mb-6 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                    {{ $page.props.flash.success }}
                </div>

                <!-- Empty State -->
                <div v-if="!events?.data || events.data.length === 0" class="bg-white rounded-lg shadow p-16 text-center">
                    <div class="text-6xl mb-4">📅</div>
                    <h3 class="text-2xl font-semibold text-gray-900 mb-3">No tienes eventos aún</h3>
                    <p class="text-gray-600 mb-6">Crea tu primer evento para organizar tus fotos</p>
                    <Link
                        :href="route('photographer.events.create')"
                        class="inline-block bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-3 rounded-lg font-semibold transition"
                    >
                        Crear Primer Evento
                    </Link>
                </div>

                <!-- Events Grid -->
                <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div
                        v-for="event in events.data"
                        :key="event.id"
                        class="bg-white rounded-lg shadow-lg overflow-hidden hover:shadow-xl transition"
                    >
                        <!-- Cover Image -->
                        <div class="relative h-48 bg-gradient-to-br from-indigo-500 to-purple-600">
                            <img
                                v-if="event.cover_image"
                                :src="`/storage/${event.cover_image}`"
                                :alt="event.name"
                                class="w-full h-full object-cover"
                                @error="(e) => e.target.style.display = 'none'"
                            />
                            <div class="absolute top-3 right-3">
                                <span
                                    :class="event.is_private ? 'bg-red-500' : 'bg-green-500'"
                                    class="px-3 py-1 rounded-full text-white text-xs font-bold shadow"
                                >
                                    {{ event.is_private ? '🔒 Privado' : '🌐 Público' }}
                                </span>
                            </div>
                            <div class="absolute bottom-3 left-3 bg-white/90 backdrop-blur px-3 py-1 rounded-full text-sm font-bold text-indigo-600">
                                {{ event.photos_count || 0 }} fotos
                            </div>
                        </div>

                        <!-- Info -->
                        <div class="p-6">
                            <h3 class="text-xl font-bold text-gray-900 mb-2 line-clamp-1">{{ event.name }}</h3>
                            <p v-if="event.description" class="text-gray-600 text-sm mb-4 line-clamp-2">
                                {{ event.description }}
                            </p>
                            <div class="flex items-center text-sm text-gray-500 mb-4">
                                <span class="mr-2">📅</span>
                                {{ formatDate(event.event_date) }}
                            </div>
                            <div v-if="event.location" class="flex items-center text-sm text-gray-500 mb-4">
                                <span class="mr-2">📍</span>
                                {{ event.location }}
                            </div>

                            <!-- Actions -->
                            <div class="flex items-center gap-2 pt-4 border-t">
                                <Link
                                    :href="route('photographer.events.show', event.id)"
                                    class="flex-1 bg-indigo-100 hover:bg-indigo-200 text-indigo-700 text-center px-4 py-2 rounded-lg font-medium transition text-sm"
                                >
                                    Ver
                                </Link>
                                <Link
                                    :href="route('photographer.events.edit', event.id)"
                                    class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-700 text-center px-4 py-2 rounded-lg font-medium transition text-sm"
                                >
                                    Editar
                                </Link>
                                <button
                                    @click="deleteEvent(event)"
                                    class="bg-red-100 hover:bg-red-200 text-red-700 px-4 py-2 rounded-lg font-medium transition text-sm"
                                    title="Eliminar"
                                >
                                    🗑️
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pagination -->
                <div v-if="events?.data && events.data.length > 0 && events.links" class="mt-8">
                    <div class="flex items-center justify-center gap-2">
                        <template v-for="(link, index) in events.links" :key="index">
                            <Link
                                v-if="link.url"
                                :href="link.url"
                                v-html="link.label"
                                :class="[
                                    'px-4 py-2 rounded-lg font-medium transition',
                                    link.active
                                        ? 'bg-indigo-600 text-white shadow-lg'
                                        : 'bg-white text-gray-700 hover:bg-gray-100 shadow'
                                ]"
                            />
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
