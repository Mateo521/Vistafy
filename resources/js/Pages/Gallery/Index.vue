<script setup>
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue'; // Nota: Ya no necesitamos 'watch'
import AppLayout from '@/Layouts/AppLayout.vue';
import {
    MagnifyingGlassIcon,
    AdjustmentsHorizontalIcon,
    XMarkIcon,
    ArrowDownTrayIcon
} from '@heroicons/vue/24/outline';

const props = defineProps({
    photos: Object, // Paginator inicial de Laravel
    events: Array,
    regions: Array,
    filters: Object,
});

// --- ESTADO PARA SCROLL INFINITO / CARGAR MÁS ---
// Inicializamos con los datos que llegan desde el servidor (primera página)
const allPhotos = ref(props.photos.data);
const nextUrl = ref(props.photos.next_page_url);
const loadingMore = ref(false);

// FUNCIÓN 1: Cargar más (Acumular)
const loadMore = () => {
    if (!nextUrl.value || loadingMore.value) return;

    loadingMore.value = true;

    router.visit(nextUrl.value, {
        preserveState: true,
        preserveScroll: true,
        only: ['photos'], // Solo traemos la prop 'photos' actualizada
        onSuccess: (page) => {
            const newPhotos = page.props.photos.data;

            // AQUÍ ESTÁ LA CLAVE: Concatenamos lo anterior + lo nuevo
            allPhotos.value = [...allPhotos.value, ...newPhotos];

            // Actualizamos la URL para la siguiente página
            nextUrl.value = page.props.photos.next_page_url;
            loadingMore.value = false;
        },
        onError: () => {
            loadingMore.value = false;
        }
    });
};

// --- FILTROS ---
const filterForm = useForm({
    search: props.filters.search || '',
    region: props.filters.region || 'all',
    event: props.filters.event || '',
    sort: props.filters.sort || 'recent',
});

const showFilters = ref(false);

// FUNCIÓN 2: Aplicar Filtros (Reiniciar lista)
const applyFilters = () => {
    filterForm.get(route('gallery.index'), {
        preserveState: true,
        preserveScroll: true,
        only: ['photos'], // Opcional: para optimizar la carga si el resto no cambia
        onSuccess: (page) => {
            // AL FILTRAR: Reiniciamos la lista completamente con los nuevos resultados
            // porque no estamos paginando, estamos buscando algo nuevo.
            allPhotos.value = page.props.photos.data;
            nextUrl.value = page.props.photos.next_page_url;
        }
    });
};

const clearFilters = () => {
    filterForm.reset();
    filterForm.region = 'all';
    filterForm.event = '';
    filterForm.search = '';
    applyFilters(); // Esto llamará a la lógica de reinicio de arriba
};

const changeSort = (sortValue) => {
    filterForm.sort = sortValue;
    applyFilters(); // Esto llamará a la lógica de reinicio de arriba
};

const sortOptions = [
    { value: 'recent', label: 'Recientes' },
    { value: 'popular', label: 'Populares' },
    { value: 'price_low', label: 'Precio: Menor' },
    { value: 'price_high', label: 'Precio: Mayor' },
];

const handleImageError = (e) => {
    e.target.style.display = 'none';
    const parent = e.target.parentElement;
    if (parent && !parent.querySelector('.placeholder-institutional')) {
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

                <div class="bg-white border border-gray-200 p-6 mb-8 rounded-sm shadow-sm">
                    <form @submit.prevent="applyFilters">
                        <div class="flex flex-col md:flex-row gap-4">
                            <div class="flex-1 relative">
                                <input v-model="filterForm.search" type="text"
                                    placeholder="Buscar por ID, título o fotógrafo..."
                                    class="w-full pl-10 pr-4 py-3 rounded-sm border-gray-300 text-sm focus:border-slate-900 focus:ring-0 placeholder-gray-400 transition-colors" />
                                <div class="absolute left-3 top-3 text-slate-400">
                                    <MagnifyingGlassIcon class="w-5 h-5" />
                                </div>
                            </div>

                            <div class="flex gap-3">
                                <button type="button" @click="showFilters = !showFilters" :class="[
                                    'px-6 py-3 rounded-sm border text-xs font-bold uppercase tracking-widest transition-all duration-300 flex items-center gap-2 whitespace-nowrap',
                                    showFilters
                                        ? 'bg-slate-100 border-slate-300 text-slate-900'
                                        : 'bg-white border-gray-300 text-slate-600 hover:border-slate-900 hover:text-slate-900'
                                ]">
                                    <AdjustmentsHorizontalIcon class="w-4 h-4" />
                                    <span>Filtros</span>
                                </button>
                                <button type="submit"
                                    class="bg-slate-900 text-white px-8 py-3 rounded-sm text-xs font-bold uppercase tracking-widest hover:bg-slate-800 transition shadow-sm">
                                    Buscar
                                </button>
                            </div>
                        </div>

                        <div v-show="showFilters"
                            class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-6 mt-6 border-t border-gray-100 animate-fade-in-down">
                            <div>
                                <label
                                    class="block text-xs font-bold uppercase tracking-wide text-slate-500 mb-2">Región</label>
                                <select v-model="filterForm.region"
                                    class="w-full rounded-sm border-gray-300 text-sm focus:border-slate-900 focus:ring-0 bg-gray-50">
                                    <option value="all">Todas las regiones</option>
                                    <option v-for="region in regions" :key="region" :value="region">
                                        {{ region }}
                                    </option>
                                </select>
                            </div>

                            <div>
                                <label
                                    class="block text-xs font-bold uppercase tracking-wide text-slate-500 mb-2">Evento</label>
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
                                    class="text-xs font-bold uppercase tracking-widest text-red-600 hover:text-red-800 flex items-center gap-1 transition border-b border-transparent hover:border-red-600 pb-0.5">
                                    <XMarkIcon class="w-4 h-4" />
                                    Limpiar Filtros
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
                        <button v-for="option in sortOptions" :key="option.value" @click="changeSort(option.value)"
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

                <div v-if="allPhotos.length > 0">
                    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-6 mb-12">
                        <Link v-for="photo in allPhotos" :key="photo.id" :href="route('gallery.show', photo.unique_id)"
                            class="group bg-white border border-gray-100 hover:border-gray-300 transition-all duration-300 hover:shadow-lg rounded-sm overflow-hidden flex flex-col">

                        <div class="aspect-square bg-gray-100 relative overflow-hidden">
                            <img :src="photo.watermarked_url || photo.thumbnail_url" :alt="photo.unique_id"
                                class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105 filter grayscale-[0.3] group-hover:grayscale-0"
                                loading="lazy" @error="handleImageError" />

                            <div
                                class="absolute top-2 right-2 bg-white/95 backdrop-blur-sm border border-gray-200 px-2 py-1 text-[10px] font-bold text-slate-900 shadow-sm">
                                ${{ photo.price }}
                            </div>
                        </div>

                        <div class="p-4 bg-white border-t border-gray-50 flex flex-col items-start">
                            <span
                                class="text-[10px] uppercase tracking-widest text-slate-400 font-bold truncate w-full">
                                {{ photo.unique_id }}
                            </span>
                            <span
                                class="text-xs font-serif font-bold text-slate-900 truncate w-full mt-1 group-hover:text-blue-900 transition-colors">
                                {{ photo.photographer }}
                            </span>
                        </div>
                        </Link>
                    </div>

                    <div v-if="nextUrl" class="flex justify-center pt-8 border-t border-gray-100">
                        <button @click="loadMore" :disabled="loadingMore"
                            class="px-8 py-3 bg-white border border-slate-900 text-slate-900 text-xs font-bold uppercase tracking-widest hover:bg-slate-900 hover:text-white transition-all duration-300 rounded-sm disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2">
                            <svg v-if="loadingMore" class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg"
                                fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                    stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                </path>
                            </svg>
                            <span v-else>Cargar Más</span>
                        </button>
                    </div>
                </div>

                <div v-else class="bg-white border border-dashed border-gray-300 p-24 text-center rounded-sm">
                    <div class="w-16 h-16 mx-auto text-gray-200 mb-6">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-serif text-slate-900 mb-2">Sin resultados</h3>
                    <p class="text-slate-500 font-light mb-8 max-w-md mx-auto">No hemos encontrado fotografías que
                        coincidan con sus criterios de búsqueda actuales.</p>
                    <button @click="clearFilters"
                        class="px-6 py-2 border border-slate-900 text-slate-900 text-xs font-bold uppercase tracking-widest hover:bg-slate-900 hover:text-white transition-colors">
                        Ver todo el catálogo
                    </button>
                </div>

            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
@keyframes fadeInDown {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }

    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.animate-fade-in-down {
    animation: fadeInDown 0.3s ease-out;
}
</style>