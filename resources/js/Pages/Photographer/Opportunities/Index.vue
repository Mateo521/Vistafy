<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import { PlusIcon, PencilIcon, TrashIcon, CalendarIcon, MapPinIcon } from '@heroicons/vue/24/outline';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    opportunities: Object,
});

const deleteOpportunity = (id) => {
    if (confirm('¿Estás seguro de eliminar esta oportunidad?')) {
        router.delete(route('photographer.opportunities.destroy', id), {
            preserveScroll: true,
        });
    }
};

const getDaysText = (days) => {
    const roundedDays = Math.round(days);
    if (roundedDays === 0) return 'Hoy';
    if (roundedDays === 1) return 'Mañana';
    if (roundedDays < 0) return `Hace ${Math.abs(roundedDays)} días`;
    return `En ${roundedDays} días`;
};
</script>

<template>
    <AuthenticatedLayout>

        <Head title="Mis Oportunidades" />

        <div class="max-w-7xl mx-auto px-4 py-6 sm:px-6 lg:px-8">

            <!-- Header -->
            <div class="flex justify-between items-center mb-8">
                <div>
                    <h1 class="text-3xl font-serif font-bold text-slate-900">
                        Mis Oportunidades
                    </h1>
                    <p class="text-slate-600 mt-2">
                        Eventos futuros que estás organizando
                    </p>
                </div>
                <Link :href="route('photographer.opportunities.create')"
                    class="inline-flex items-center gap-2 px-6 py-3 bg-slate-900 text-white font-bold text-sm uppercase tracking-widest hover:bg-slate-800 transition rounded-sm">
                    <PlusIcon class="w-5 h-5" />
                    Crear Oportunidad
                </Link>
            </div>

            <!-- Grid de Oportunidades -->
            <div v-if="opportunities.data.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <div v-for="opportunity in opportunities.data" :key="opportunity.id"
                    class="bg-white border border-gray-200 rounded-sm overflow-hidden hover:shadow-lg transition">

                    <!-- Imagen -->
                    <div class="relative h-48 bg-gray-100">
                        <img v-if="opportunity.cover_image" :src="opportunity.cover_image" :alt="opportunity.title"
                            class="w-full h-full object-cover" />




                            
                        <div v-else class="w-full h-full flex items-center justify-center text-gray-400">
                            <CalendarIcon class="w-12 h-12" />
                        </div>

                        <!-- Badge de días -->
                        <div class="absolute top-2 right-2 bg-white/95 backdrop-blur-sm px-3 py-1 rounded-sm shadow-lg">
                            <span class="text-xs font-bold text-slate-900">
                                {{ getDaysText(opportunity.days_until) }}
                            </span>
                        </div>
                    </div>

                    <!-- Contenido -->
                    <div class="p-4">
                        <h3 class="font-serif font-bold text-lg text-slate-900 mb-2 line-clamp-2">
                            {{ opportunity.title }}
                        </h3>

                        <p class="text-sm text-slate-600 mb-4 line-clamp-2">
                            {{ opportunity.description }}
                        </p>

                        <div class="space-y-2 mb-4">
                            <div class="flex items-center gap-2 text-xs text-slate-500">
                                <CalendarIcon class="w-4 h-4" />
                                {{ opportunity.formatted_date }}
                            </div>
                            <div class="flex items-center gap-2 text-xs text-slate-500">
                                <MapPinIcon class="w-4 h-4" />
                                {{ opportunity.location }}
                            </div>
                        </div>

                        <!-- Acciones -->
                        <div class="flex gap-2">
                            <Link :href="route('photographer.opportunities.edit', opportunity.id)"
                                class="flex-1 py-2 bg-slate-100 text-slate-900 text-xs font-bold uppercase tracking-wider hover:bg-slate-200 transition rounded-sm flex items-center justify-center gap-1">
                                <PencilIcon class="w-4 h-4" />
                                Editar
                            </Link>
                            <button @click="deleteOpportunity(opportunity.id)"
                                class="py-2 px-4 bg-red-100 text-red-700 text-xs font-bold uppercase hover:bg-red-200 transition rounded-sm">
                                <TrashIcon class="w-4 h-4" />
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <div v-else class="text-center py-16 bg-white border-2 border-dashed border-gray-300 rounded-lg">
                <CalendarIcon class="w-16 h-16 text-gray-300 mx-auto mb-4" />
                <h3 class="text-xl font-serif font-bold text-slate-900 mb-2">
                    No tenés oportunidades creadas
                </h3>
                <p class="text-slate-600 mb-6">
                    Comenzá creando tu primera oportunidad de evento
                </p>
                <Link :href="route('photographer.opportunities.create')"
                    class="inline-flex items-center gap-2 px-6 py-3 bg-slate-900 text-white font-bold text-sm uppercase tracking-widest hover:bg-slate-800 transition rounded-sm">
                    <PlusIcon class="w-5 h-5" />
                    Crear Primera Oportunidad
                </Link>
            </div>

            <!-- Paginación -->
            <div v-if="opportunities.links && opportunities.last_page > 1" class="flex justify-center gap-2 mt-8">
                <Link v-for="(link, index) in opportunities.links" :key="index" :href="link.url || '#'"
                    v-html="link.label"
                    :class="['px-4 py-2 text-sm font-medium rounded-sm border transition',
                        link.active ? 'bg-slate-900 text-white border-slate-900' : 'bg-white text-slate-600 border-gray-200 hover:border-slate-400']" />
            </div>

        </div>
    </AuthenticatedLayout>

</template>
