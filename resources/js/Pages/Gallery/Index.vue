<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    photos: Object,
    events: Array,
    regions: Array,
    filters: Object,
});

const filterForm = useForm({
    search: props.filters.search || '',
    region: props.filters.region || 'all',
    event: props.filters.event || '',
    sort: props.filters.sort || 'recent',
});

const showFilters = ref(false);

const applyFilters = () => {
    filterForm.get(route('gallery.index'), {
        preserveState: true,
        preserveScroll: true,
    });
};

const clearFilters = () => {
    filterForm.reset();
    filterForm.region = 'all';
    filterForm.event = '';
    filterForm.search = '';
    applyFilters();
};

const changeSort = (sortValue) => {
    filterForm.sort = sortValue;
    applyFilters();
};

const sortOptions = [
    { value: 'recent', label: 'Recientes' },
    { value: 'popular', label: 'Populares' },
    { value: 'price_low', label: 'Precio: Menor' },
    { value: 'price_high', label: 'Precio: Mayor' },
];
</script>

<template>
    <Head title="Galería de Fotos" />

    <AppLayout>
        <div class="bg-white border-b border-gray-100 pt-24 pb-12 md:pt-32 md:pb-12">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-6">
                    <div class="max-w-3xl">
                        <span class="text-xs font-bold tracking-[0.2em] text-slate-400 uppercase mb-2 block">
                            Catálogo Completo
                        </span>
                        <h1 class="text-4xl md:text-5xl font-serif font-bold text-slate-900">
                            Galería de Fotos
                        </h1>
                    </div>
                    <div class="text-right hidden md:block">
                        <span class="text-3xl font-serif font-bold text-slate-900 block leading-none">
                            {{ photos.total }}
                        </span>
                        <span class="text-[10px] font-bold uppercase tracking-widest text-slate-400">
                            Fotografías Disponibles
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <div class="min-h-screen bg-gray-50 py-8">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

                <div class="bg-white border border-gray-200 p-6 mb-8 rounded-sm">
                    <form @submit.prevent="applyFilters">
                        <div class="flex flex-col md:flex-row gap-4">
                            <div class="flex-1 relative">
                                <input v-model="filterForm.search" type="text"
                                    placeholder="Buscar por ID, título o fotógrafo..."
                                    class="w-full pl-4 pr-10 py-3 rounded-sm border-gray-300 text-sm focus:border-slate-900 focus:ring-0 placeholder-gray-400 transition-colors" 
                                />
                                <button type="submit" class="absolute right-3 top-3 text-slate-400 hover:text-slate-900">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                                </button>
                            </div>

                            <div class="flex gap-3">
                                <button type="button" @click="showFilters = !showFilters"
                                    :class="[
                                        'px-6 py-3 rounded-sm border text-xs font-bold uppercase tracking-widest transition-all duration-300 flex items-center gap-2',
                                        showFilters 
                                            ? 'bg-slate-100 border-slate-300 text-slate-900' 
                                            : 'bg-white border-gray-300 text-slate-600 hover:border-slate-900 hover:text-slate-900'
                                    ]">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" /></svg>
                                    Filtros
                                </button>
                                <button type="submit"
                                    class="bg-slate-900 text-white px-8 py-3 rounded-sm text-xs font-bold uppercase tracking-widest hover:bg-slate-800 transition shadow-sm">
                                    Buscar
                                </button>
                            </div>
                        </div>

                        <div v-show="showFilters" class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-6 mt-6 border-t border-gray-100 animate-fade-in-down">
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wide text-slate-500 mb-2">Región</label>
                                <select v-model="filterForm.region"
                                    class="w-full rounded-sm border-gray-300 text-sm focus:border-slate-900 focus:ring-0 bg-gray-50">
                                    <option v-for="region in regions" :key="region.value" :value="region.value">
                                        {{ region.label }}
                                    </option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wide text-slate-500 mb-2">Evento</label>
                                <select v-model="filterForm.event"
                                    class="w-full rounded-sm border-gray-300 text-sm focus:border-slate-900 focus:ring-0 bg-gray-50">
                                    <option value="">Todos los eventos</option>
                                    <option v-for="event in events" :key="event.id" :value="event.id">
                                        {{ event.name }}
                                    </option>
                                </select>
                            </div>

                            <div class="md:col-span-2 flex justify-end">
                                <button type="button" @click="clearFilters"
                                    class="text-xs font-bold uppercase tracking-widest text-slate-400 hover:text-red-600 transition border-b border-transparent hover:border-red-600 pb-0.5">
                                    Restablecer Filtros
                                </button>
                            </div>
                        </div>
                    </form>
                </div>

                <div class="flex flex-col md:flex-row justify-between items-center mb-8 border-b border-gray-200 pb-2">
                    <div class="text-sm text-slate-500 mb-4 md:mb-0 md:hidden">
                        {{ photos.total }} resultados
                    </div>
                    <div class="flex gap-6 overflow-x-auto pb-2 md:pb-0 w-full md:w-auto">
                        <button v-for="option in sortOptions" :key="option.value"
                            @click="changeSort(option.value)"
                            :class="[
                                'text-xs font-bold uppercase tracking-widest pb-3 transition border-b-2 whitespace-nowrap',
                                filterForm.sort === option.value
                                    ? 'text-slate-900 border-slate-900'
                                    : 'text-slate-400 border-transparent hover:text-slate-600'
                            ]">
                            {{ option.label }}
                        </button>
                    </div>
                </div>

                <div v-if="photos.data && photos.data.length > 0">
                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-6 mb-12">
                        <Link v-for="photo in photos.data" :key="photo.id" :href="route('gallery.show', photo.unique_id)"
                            class="group bg-white border border-gray-100 hover:border-gray-300 transition-all duration-300 hover:shadow-lg rounded-sm overflow-hidden flex flex-col">
                            
                            <div class="aspect-square bg-gray-100 relative overflow-hidden">
                                <img :src="photo.watermarked_url" :alt="photo.unique_id"
                                    class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105 filter grayscale-[0.3] group-hover:grayscale-0"
                                    @error="(e) => e.target.src = 'https://via.placeholder.com/400?text=N/A'" 
                                />
                                
                                <div class="absolute top-2 right-2 bg-white/95 backdrop-blur-sm border border-gray-200 px-2 py-1 text-[10px] font-bold text-slate-900 shadow-sm">
                                    ${{ photo.price }}
                                </div>
                            </div>

                            <div class="p-4 bg-white border-t border-gray-50 flex flex-col items-start">
                                <span class="text-[10px] uppercase tracking-widest text-slate-400 font-bold truncate w-full">
                                    {{ photo.unique_id }}
                                </span>
                                <span class="text-xs font-serif font-bold text-slate-900 truncate w-full mt-1 group-hover:text-blue-900 transition-colors">
                                    {{ photo.photographer }}
                                </span>
                            </div>
                        </Link>
                    </div>
                </div>

                <div v-else class="bg-white border border-dashed border-gray-300 p-24 text-center rounded-sm">
                    <div class="w-16 h-16 mx-auto text-gray-200 mb-6">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                    </div>
                    <h3 class="text-xl font-serif text-slate-900 mb-2">Sin resultados</h3>
                    <p class="text-slate-500 font-light mb-8 max-w-md mx-auto">No hemos encontrado fotografías que coincidan con sus criterios de búsqueda actuales.</p>
                    <button @click="clearFilters"
                        class="px-6 py-2 border border-slate-900 text-slate-900 text-xs font-bold uppercase tracking-widest hover:bg-slate-900 hover:text-white transition-colors">
                        Ver todo el catálogo
                    </button>
                </div>

                <div v-if="photos.data && photos.data.length > 0 && photos.last_page > 1" class="border-t border-gray-200 pt-8">
                    <div class="flex items-center justify-center gap-2">
                        <template v-for="(link, index) in photos.links" :key="index">
                            <Link v-if="link.url" :href="link.url" 
                                class="px-4 py-2 text-sm font-medium transition-colors border border-transparent rounded-sm"
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
    </AppLayout>
</template>

<style scoped>
/* Animación suave para el menú desplegable */
@keyframes fadeInDown {
    from { opacity: 0; transform: translateY(-10px); }
    to { opacity: 1; transform: translateY(0); }
}
.animate-fade-in-down {
    animation: fadeInDown 0.3s ease-out;
}
</style>