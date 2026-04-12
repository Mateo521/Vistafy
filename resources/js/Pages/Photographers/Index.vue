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

        <div class="bg-[#111920] pt-32 pb-16 md:pt-40 md:pb-20 font-['Syne'] relative overflow-hidden">
            <div class="absolute -top-10 right-0 font-['Cormorant_Garamond'] text-[150px] font-light text-[#1B2632] opacity-50 leading-none pointer-events-none select-none">
                STAFF
            </div>
            
            <div class="max-w-7xl mx-auto px-8 md:px-16 relative z-10">
                <div class="max-w-3xl">
                    <span class="text-[10px] font-bold tracking-[0.3em] text-[#FFB162] uppercase mb-4 block flex items-center gap-3 after:content-[''] after:h-px after:w-12 after:bg-[#A35139]">
                        Staff Oficial
                    </span>
                    <h1 class="text-5xl md:text-7xl font-['Cormorant_Garamond'] font-light text-[#EEE9DF] mb-6 leading-tight">
                        Fotógrafos <em class="italic text-[#C9C1B1]">profesionales</em>
                    </h1>
                    <p class="text-[14px] md:text-base text-[#C9C1B1] font-light leading-relaxed max-w-xl">
                        Conectá con los talentos detrás de la lente. Profesionales verificados comprometidos con la excelencia visual.
                    </p>
                </div>
            </div>
        </div>

        <div class="w-full h-[400px] bg-[#1B2632] border-y border-[#C9C1B1]/10 relative z-0">
            <PhotographerMap :photographers="photographers.data" />
            
            <div class="absolute top-6 left-6 md:left-16 z-[400] bg-[#111920]/90 backdrop-blur-md border border-[#C9C1B1]/20 px-5 py-2.5 text-[9px] font-bold uppercase tracking-[0.25em] text-[#FFB162] shadow-2xl">
                Cobertura Nacional
            </div>
        </div>

        <div class="min-h-screen bg-[#111920] py-20 font-['Syne']">
            <div class="max-w-7xl mx-auto px-8 md:px-16">

                <div class="bg-[#1B2632] border border-[#C9C1B1]/10 p-8 mb-16 shadow-2xl">
                    <div class="flex flex-col lg:flex-row gap-6">

                        <div class="flex-1 relative">
                            <input v-model="search" @keyup.enter="handleSearch" type="text"
                                placeholder="Buscar por nombre o especialidad..."
                                class="w-full pl-5 pr-12 py-4 bg-[#111920] border border-[#C9C1B1]/20 text-[#EEE9DF] text-[13px] focus:border-[#FFB162] focus:ring-0 placeholder-[#C9C1B1]/40 transition-colors rounded-none outline-none" />
                            <div class="absolute right-4 top-4 text-[#FFB162]">
                                <MagnifyingGlassIcon class="w-5 h-5" />
                            </div>
                        </div>

                        <div class="flex gap-4">
                            <button @click="showFilters = !showFilters" :class="[
                                'px-8 py-4 border text-[10px] font-bold uppercase tracking-widest transition-all duration-300 flex items-center gap-3 whitespace-nowrap',
                                showFilters
                                    ? 'bg-[#FFB162]/10 border-[#FFB162] text-[#FFB162]'
                                    : 'bg-[#111920] border-[#C9C1B1]/20 text-[#C9C1B1] hover:border-[#FFB162] hover:text-[#FFB162]'
                            ]">
                                <AdjustmentsHorizontalIcon class="w-4 h-4" />
                                <span>Filtros</span>
                            </button>

                            <button @click="handleSearch"
                                class="bg-[#FFB162] text-[#1B2632] px-10 py-4 text-[10px] font-bold uppercase tracking-widest hover:bg-[#EEE9DF] transition-colors shadow-lg">
                                Buscar
                            </button>
                        </div>
                    </div>

                    <div v-show="showFilters"
                        class="grid grid-cols-1 md:grid-cols-3 gap-8 pt-8 mt-8 border-t border-[#C9C1B1]/10 animate-fade-in-down">
                        
                        <div>
                            <label class="block text-[9px] font-bold uppercase tracking-[0.2em] text-[#C9C1B1] mb-3">Región</label>
                            <div class="relative">
                                <select v-model="selectedRegion" @change="handleSearch"
                                    class="w-full py-3 pl-4 pr-10 border border-[#C9C1B1]/20 bg-[#111920] text-[#EEE9DF] text-[13px] focus:border-[#FFB162] focus:ring-0 appearance-none outline-none rounded-none">
                                    <option value="">Todas las regiones</option>
                                    <option v-for="region in regions" :key="region" :value="region">{{ region }}</option>
                                </select>
                                <MapPinIcon class="absolute right-4 top-3.5 w-4 h-4 text-[#FFB162] pointer-events-none" />
                            </div>
                        </div>

                        <div>
                            <label class="block text-[9px] font-bold uppercase tracking-[0.2em] text-[#C9C1B1] mb-3">Ordenamiento</label>
                            <select v-model="sortBy" @change="handleSearch"
                                class="w-full py-3 px-4 border border-[#C9C1B1]/20 bg-[#111920] text-[#EEE9DF] text-[13px] focus:border-[#FFB162] focus:ring-0 outline-none rounded-none">
                                <option value="recent">Recientes incorporaciones</option>
                                <option value="name">Alfabético (A-Z)</option>
                                <option value="popular">Mayor popularidad</option>
                                <option value="events">Más eventos cubiertos</option>
                            </select>
                        </div>

                        <div class="flex items-end justify-end">
                            <button v-if="hasActiveFilters" @click="clearFilters"
                                class="text-[9px] font-bold uppercase tracking-[0.2em] text-[#A35139] hover:text-[#FFB162] flex items-center gap-2 transition-colors border-b border-[#A35139] hover:border-[#FFB162] pb-1">
                                <XMarkIcon class="w-3.5 h-3.5" />
                                Limpiar Búsqueda
                            </button>
                        </div>
                    </div>

                    <div class="mt-8 pt-6 border-t border-[#C9C1B1]/10 flex justify-between items-center">
                        <span class="text-[10px] text-[#C9C1B1] uppercase tracking-[0.2em]">
                            Mostrando <strong class="text-[#FFB162] text-[12px]">{{ photographers.data.length }}</strong> profesionales
                        </span>
                    </div>
                </div>

                <div v-if="photographers.data.length > 0">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-10">
                        <Link v-for="photographer in photographers.data" :key="photographer.id"
                            :href="route('photographers.show', photographer.slug)"
                            class="group bg-[#1B2632] border border-[#C9C1B1]/10 hover:border-[#FFB162]/50 transition-colors duration-500 overflow-hidden flex flex-col relative">

                            <div class="h-48 overflow-hidden bg-[#111920] relative">
                                <img v-if="photographer.banner_photo_url" :src="photographer.banner_photo_url"
                                    :alt="photographer.business_name"
                                    class="w-full h-full object-cover transition-transform duration-[800ms] group-hover:scale-105 saturate-[0.6] group-hover:saturate-100" />
                                
                                <div v-else class="w-full h-full flex items-center justify-center">
                                    <span class="font-['Cormorant_Garamond'] text-5xl italic opacity-20 text-[#EEE9DF]">f33</span>
                                </div>
                                
                                <div class="absolute inset-0 bg-gradient-to-t from-[#1B2632] via-transparent to-transparent opacity-90"></div>

                                <div class="absolute top-4 right-4 bg-[#FFB162] text-[#1B2632] text-[9px] font-bold uppercase tracking-widest px-3 py-1.5 flex items-center gap-1.5 shadow-xl">
                                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                    </svg>
                                    Verificado
                                </div>
                            </div>

                            <div class="p-8 pt-12 relative flex-1 flex flex-col">
                                
                                <div class="absolute -top-12 left-8">
                                    <div class="w-20 h-20 bg-[#111920] p-1 shadow-2xl border border-[#C9C1B1]/20 rounded-full overflow-hidden group-hover:border-[#FFB162] transition-colors duration-500">
                                        <img v-if="photographer.profile_photo_url" :src="photographer.profile_photo_url"
                                            :alt="photographer.business_name"
                                            class="w-full h-full object-cover rounded-full grayscale-[0.5] group-hover:grayscale-0 transition-all duration-500" />
                                        <div v-else
                                            class="w-full h-full bg-[#2C3B4D] flex items-center justify-center rounded-full">
                                            <span class="text-xl font-['Cormorant_Garamond'] text-[#FFB162]">
                                                {{ getInitials(photographer.business_name) }}
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-5">
                                    <h3 class="text-3xl font-['Cormorant_Garamond'] text-[#EEE9DF] group-hover:text-[#FFB162] transition-colors leading-tight mb-2">
                                        {{ photographer.business_name }}
                                    </h3>
                                    <div v-if="photographer.region"
                                        class="flex items-center text-[10px] text-[#C9C1B1] uppercase tracking-[0.2em] font-bold">
                                        <MapPinIcon class="h-3.5 w-3.5 mr-1.5 text-[#FFB162]" />
                                        {{ photographer.region }}
                                    </div>
                                </div>

                                <p v-if="photographer.bio"
                                    class="text-[13px] text-[#C9C1B1]/80 font-light line-clamp-3 mb-8 leading-relaxed">
                                    {{ photographer.bio }}
                                </p>

                                <div class="mt-auto pt-6 border-t border-[#C9C1B1]/10 grid grid-cols-2 gap-6">
                                    <div>
                                        <span class="block text-2xl font-['Cormorant_Garamond'] text-[#FFB162] leading-none mb-1">
                                            {{ photographer.events_count }}
                                        </span>
                                        <span class="text-[9px] uppercase tracking-[0.2em] text-[#C9C1B1]">Eventos</span>
                                    </div>
                                    <div>
                                        <span class="block text-2xl font-['Cormorant_Garamond'] text-[#FFB162] leading-none mb-1">
                                            {{ photographer.photos_count }}
                                        </span>
                                        <span class="text-[9px] uppercase tracking-[0.2em] text-[#C9C1B1]">Fotos</span>
                                    </div>
                                </div>

                                <div class="mt-8 pt-6 border-t border-[#C9C1B1]/10 flex items-center justify-between">
                                    <span class="text-[10px] font-bold uppercase tracking-[0.2em] text-[#C9C1B1] group-hover:text-[#EEE9DF] transition-colors">
                                        Ver Portafolio
                                    </span>
                                    <ArrowRightIcon class="w-4 h-4 text-[#FFB162] transform group-hover:translate-x-2 transition-transform duration-300" />
                                </div>
                            </div>
                        </Link>
                    </div>
                </div>

                <div v-else class="text-center py-32 border border-dashed border-[#C9C1B1]/20 bg-[#1B2632]">
                    <div class="mx-auto h-16 w-16 text-[#FFB162] mb-6 opacity-50">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1"
                                d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                    <h3 class="text-3xl font-['Cormorant_Garamond'] text-[#EEE9DF] mb-3">Sin resultados</h3>
                    <p class="text-[#C9C1B1] text-[13px] font-light mb-8 max-w-md mx-auto">No encontramos profesionales que coincidan con sus criterios de búsqueda o filtros seleccionados.</p>
                    <button @click="clearFilters"
                        class="text-[10px] font-bold uppercase tracking-[0.2em] text-[#FFB162] border-b border-[#FFB162] pb-1 hover:text-[#EEE9DF] hover:border-[#EEE9DF] transition-colors">
                        Limpiar todos los filtros
                    </button>
                </div>

                <div v-if="photographers.last_page > 1" class="mt-20 flex justify-center">
                    <div class="flex items-center gap-2 bg-[#1B2632] p-2 border border-[#C9C1B1]/10 shadow-xl">
                        <template v-for="(link, index) in photographers.links" :key="index">
                            <Link v-if="link.url" :href="link.url"
                                class="min-w-[40px] h-10 flex items-center justify-center px-4 text-[11px] font-bold transition-colors"
                                :class="link.active
                                    ? 'bg-[#FFB162] text-[#1B2632]'
                                    : 'text-[#C9C1B1] hover:bg-[#2C3B4D] hover:text-[#EEE9DF]'">
                                <span v-html="link.label"></span>
                            </Link>
                            <span v-else v-html="link.label" class="min-w-[40px] h-10 flex items-center justify-center px-4 text-[11px] font-bold text-[#C9C1B1]/30"></span>
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