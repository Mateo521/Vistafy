<script setup>
import { ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import PhotographerMap from '@/Components/PhotographerMap.vue'; // <--- ESTO FALTABA
import {
    MagnifyingGlassIcon,
    MapPinIcon,
    AdjustmentsHorizontalIcon,
    XMarkIcon
} from '@heroicons/vue/24/outline';

const props = defineProps({
    photographers: Object,
    regions: Array,
    filters: Object,
});

const search = ref(props.filters.search || '');
const selectedRegion = ref(props.filters.region || '');
const sortBy = ref(props.filters.sort || 'recent');
const showFilters = ref(false);

const handleSearch = () => {
    router.get('/fotografos', {
        search: search.value,
        region: selectedRegion.value,
        sort: sortBy.value,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
};

const clearFilters = () => {
    search.value = '';
    selectedRegion.value = '';
    sortBy.value = 'recent';
    router.get('/fotografos');
};

const hasActiveFilters = computed(() => {
    return search.value || selectedRegion.value || sortBy.value !== 'recent';
});

const getInitials = (name) => {
    return name
        .split(' ')
        .map(word => word[0])
        .join('')
        .toUpperCase()
        .substring(0, 2);
};
</script>

<template>
    <AppLayout>

        <Head title="Directorio de Profesionales" />

        <div class="bg-white border-b border-gray-100 pt-24 pb-12 md:pt-32 md:pb-16">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="max-w-3xl">
                    <span class="text-xs font-bold tracking-[0.2em] text-slate-400 uppercase mb-2 block">
                        Staff Oficial
                    </span>
                    <h1 class="text-4xl md:text-5xl font-serif font-bold text-slate-900 mb-6">
                        Fotógrafos Profesionales
                    </h1>
                    <p class="text-lg text-slate-500 font-light leading-relaxed">
                        Conecte con los talentos detrás de la lente. Profesionales verificados comprometidos con la
                        excelencia visual.
                    </p>
                </div>
            </div>
        </div>

        <div class="w-full h-96 bg-slate-100 border-b border-gray-200 relative z-0">
            <PhotographerMap :photographers="photographers.data" />
            
            <div class="absolute top-4 left-4 z-[400] bg-white/90 backdrop-blur px-3 py-1 text-[10px] font-bold uppercase tracking-widest text-slate-900 shadow-sm">
                Cobertura Nacional
            </div>
        </div>

        <div class="min-h-screen bg-gray-50 py-12">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

                <div class="bg-white border border-gray-200 p-6 mb-12 rounded-sm shadow-sm">
                    <div class="flex flex-col lg:flex-row gap-4">

                        <div class="flex-1 relative">
                            <input v-model="search" @keyup.enter="handleSearch" type="text"
                                placeholder="Buscar por nombre o especialidad..."
                                class="w-full pl-4 pr-10 py-3 rounded-sm border-gray-300 text-sm focus:border-slate-900 focus:ring-0 placeholder-gray-400 transition-colors" />
                            <div class="absolute right-3 top-3 text-slate-400">
                                <MagnifyingGlassIcon class="w-5 h-5" />
                            </div>
                        </div>

                        <div class="flex gap-3">
                            <button @click="showFilters = !showFilters" :class="[
                                'px-6 py-3 rounded-sm border text-xs font-bold uppercase tracking-widest transition-all duration-300 flex items-center gap-2 whitespace-nowrap',
                                showFilters
                                    ? 'bg-slate-100 border-slate-300 text-slate-900'
                                    : 'bg-white border-gray-300 text-slate-600 hover:border-slate-900 hover:text-slate-900'
                            ]">
                                <AdjustmentsHorizontalIcon class="w-4 h-4" />
                                <span>Filtros</span>
                            </button>

                            <button @click="handleSearch"
                                class="bg-slate-900 text-white px-8 py-3 rounded-sm text-xs font-bold uppercase tracking-widest hover:bg-slate-800 transition shadow-sm">
                                Buscar
                            </button>
                        </div>
                    </div>

                    <div v-show="showFilters"
                        class="grid grid-cols-1 md:grid-cols-3 gap-6 pt-6 mt-6 border-t border-gray-100 animate-fade-in-down">
                        <div>
                            <label
                                class="block text-xs font-bold uppercase tracking-wide text-slate-500 mb-2">Región</label>
                            <div class="relative">
                                <select v-model="selectedRegion" @change="handleSearch"
                                    class="w-full rounded-sm border-gray-300 text-sm focus:border-slate-900 focus:ring-0 bg-gray-50 appearance-none">
                                    <option value="">Todas las regiones</option>
                                    <option v-for="region in regions" :key="region" :value="region">{{ region }}
                                    </option>
                                </select>
                                <MapPinIcon
                                    class="absolute right-3 top-2.5 w-4 h-4 text-gray-400 pointer-events-none" />
                            </div>
                        </div>

                        <div>
                            <label
                                class="block text-xs font-bold uppercase tracking-wide text-slate-500 mb-2">Ordenamiento</label>
                            <select v-model="sortBy" @change="handleSearch"
                                class="w-full rounded-sm border-gray-300 text-sm focus:border-slate-900 focus:ring-0 bg-gray-50">
                                <option value="recent">Recientes incorporaciones</option>
                                <option value="name">Alfabético (A-Z)</option>
                                <option value="popular">Mayor popularidad</option>
                                <option value="events">Más eventos cubiertos</option>
                            </select>
                        </div>

                        <div class="flex items-end justify-end">
                            <button v-if="hasActiveFilters" @click="clearFilters"
                                class="text-xs font-bold uppercase tracking-widest text-red-600 hover:text-red-800 flex items-center gap-1 transition border-b border-transparent hover:border-red-600 pb-0.5">
                                <XMarkIcon class="w-4 h-4" />
                                Limpiar Búsqueda
                            </button>
                        </div>
                    </div>

                    <div class="mt-4 pt-4 border-t border-gray-100 flex justify-between items-center">
                        <span class="text-xs text-slate-500 uppercase tracking-wider">
                            Mostrando <strong class="text-slate-900">{{ photographers.data.length }}</strong>
                            profesionales
                        </span>
                    </div>
                </div>

                <div v-if="photographers.data.length > 0">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                        <Link v-for="photographer in photographers.data" :key="photographer.id"
                            :href="route('photographers.show', photographer.slug)"
                            class="group bg-white rounded-sm border border-gray-200 hover:border-slate-400 hover:shadow-xl transition-all duration-300 overflow-hidden flex flex-col relative">

                        <div class="h-40 overflow-hidden bg-gray-100 relative">
                            <img v-if="photographer.banner_photo_url" :src="photographer.banner_photo_url"
                                :alt="photographer.business_name"
                                class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105 filter saturate-50 group-hover:saturate-100" />
                            <div v-else class="w-full h-full bg-slate-100 flex items-center justify-center">
                                <svg class="w-12 h-12 text-slate-300" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1"
                                        d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <div class="absolute inset-0 bg-black/10 group-hover:bg-transparent transition-colors">
                            </div>

                            <div
                                class="absolute top-3 right-3 bg-slate-900 text-white text-[10px] font-bold uppercase tracking-widest px-2 py-1 flex items-center gap-1 shadow-sm">
                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                        clip-rule="evenodd" />
                                </svg>
                                Verificado
                            </div>
                        </div>

                        <div class="p-6 pt-12 relative flex-1 flex flex-col">
                            <div class="absolute -top-10 left-6">
                                <div class="w-20 h-20 bg-white p-1 shadow-sm border border-gray-100 rounded-sm">
                                    <img v-if="photographer.profile_photo_url" :src="photographer.profile_photo_url"
                                        :alt="photographer.business_name"
                                        class="w-full h-full object-cover rounded-sm filter grayscale group-hover:grayscale-0 transition duration-300" />
                                    <div v-else
                                        class="w-full h-full bg-slate-100 flex items-center justify-center rounded-sm">
                                        <span class="text-xl font-serif font-bold text-slate-400">
                                            {{ getInitials(photographer.business_name) }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-4">
                                <h3
                                    class="text-xl font-serif font-bold text-slate-900 group-hover:text-blue-900 transition-colors truncate">
                                    {{ photographer.business_name }}
                                </h3>
                                <div v-if="photographer.region"
                                    class="flex items-center text-xs text-slate-500 mt-1 uppercase tracking-wider font-medium">
                                    <MapPinIcon class="h-3 w-3 mr-1" />
                                    {{ photographer.region }}
                                </div>
                            </div>

                            <p v-if="photographer.bio"
                                class="text-sm text-slate-600 font-light line-clamp-2 mb-6 leading-relaxed">
                                {{ photographer.bio }}
                            </p>

                            <div class="mt-auto pt-4 border-t border-gray-100 grid grid-cols-2 gap-4">
                                <div>
                                    <span class="block text-lg font-serif font-bold text-slate-900 leading-none">{{
                                        photographer.events_count }}</span>
                                    <span class="text-[10px] uppercase tracking-widest text-slate-400">Eventos</span>
                                </div>
                                <div>
                                    <span class="block text-lg font-serif font-bold text-slate-900 leading-none">{{
                                        photographer.photos_count }}</span>
                                    <span class="text-[10px] uppercase tracking-widest text-slate-400">Fotos</span>
                                </div>
                            </div>

                            <div class="mt-6">
                                <span
                                    class="block w-full text-center py-3 border border-slate-900 text-slate-900 text-xs font-bold uppercase tracking-widest group-hover:bg-slate-900 group-hover:text-white transition-all duration-300">
                                    Ver Portafolio
                                </span>
                            </div>
                        </div>
                        </Link>
                    </div>
                </div>

                <div v-else class="text-center py-24 bg-white border border-dashed border-gray-300 rounded-sm">
                    <div class="mx-auto h-16 w-16 text-gray-200 mb-4">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1"
                                d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-serif text-slate-900 mb-2">Sin resultados</h3>
                    <p class="text-slate-500 font-light mb-8">No encontramos profesionales que coincidan con sus
                        criterios.</p>
                    <button @click="clearFilters"
                        class="text-xs font-bold uppercase tracking-widest text-slate-900 border-b border-slate-900 pb-1 hover:text-slate-600 hover:border-slate-600 transition">
                        Limpiar todos los filtros
                    </button>
                </div>

                <div v-if="photographers.last_page > 1" class="mt-16 border-t border-gray-200 pt-8">
                    <div class="flex items-center justify-center gap-2">
                        <template v-for="(link, index) in photographers.links" :key="index">
                            <Link v-if="link.url" :href="link.url"
                                class="px-4 py-2 text-sm font-medium transition-colors border border-transparent rounded-sm"
                                :class="link.active
                                    ? 'bg-slate-900 text-white border-slate-900'
                                    : 'text-slate-600 hover:text-slate-900 hover:border-slate-300'">
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