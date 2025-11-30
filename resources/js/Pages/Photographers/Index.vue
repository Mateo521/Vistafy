<script setup>
import { ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import {
    MagnifyingGlassIcon,
    MapPinIcon,
    CameraIcon,
    CalendarIcon,
    PhotoIcon,
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

// Búsqueda en tiempo real
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

// Limpiar filtros
const clearFilters = () => {
    search.value = '';
    selectedRegion.value = '';
    sortBy.value = 'recent';
    router.get('/fotografos');
};

const hasActiveFilters = computed(() => {
    return search.value || selectedRegion.value || sortBy.value !== 'recent';
});

// Obtener iniciales del nombre
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

        <Head title="Fotógrafos Profesionales" />

        <!-- Hero Section -->
        <div class="bg-gradient-to-br from-purple-600 via-pink-500 to-orange-400 text-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 sm:py-24">
                <div class="text-center">
                    <h1 class="text-4xl sm:text-5xl lg:text-6xl font-extrabold tracking-tight mb-6">
                        Fotógrafos Profesionales
                    </h1>
                    <p class="text-xl sm:text-2xl text-purple-100 max-w-3xl mx-auto">
                        Descubre el talento detrás de las imágenes. Conecta con fotógrafos verificados.
                    </p>
                </div>

                <!-- Buscador Principal -->
                <div class="mt-12 max-w-3xl mx-auto">
                    <div class="bg-white rounded-2xl shadow-2xl p-2">
                        <div class="flex flex-col sm:flex-row gap-2">
                            <div class="flex-1 relative">
                                <MagnifyingGlassIcon
                                    class="absolute left-4 top-1/2 transform -translate-y-1/2 h-5 w-5 text-gray-400" />
                                <input v-model="search" @keyup.enter="handleSearch" type="text"
                                    placeholder="Buscar fotógrafo por nombre..."
                                    class="w-full pl-12 pr-4 py-4 border-0 rounded-xl text-gray-900 placeholder-gray-500 focus:ring-2 focus:ring-purple-500" />
                            </div>
                            <button @click="handleSearch"
                                class="px-8 py-4 bg-gradient-to-r from-purple-600 to-pink-600 text-white font-semibold rounded-xl hover:from-purple-700 hover:to-pink-700 transition-all duration-200 shadow-lg hover:shadow-xl">
                                Buscar
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Contenido Principal -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">

            <!-- Filtros y Ordenamiento -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-8">
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">

                    <!-- Botón de Filtros (Mobile) -->
                    <div class="lg:hidden">
                        <button @click="showFilters = !showFilters"
                            class="w-full flex items-center justify-center gap-2 px-4 py-3 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition">
                            <AdjustmentsHorizontalIcon class="h-5 w-5" />
                            {{ showFilters ? 'Ocultar Filtros' : 'Mostrar Filtros' }}
                        </button>
                    </div>

                    <!-- Filtros -->
                    <div class="flex-1 flex flex-col lg:flex-row gap-4" :class="{ 'hidden lg:flex': !showFilters }">
                        <!-- Filtro por Región -->
                        <div class="flex-1">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                <MapPinIcon class="inline h-4 w-4 mr-1" />
                                Región
                            </label>
                            <select v-model="selectedRegion" @change="handleSearch"
                                class="w-full rounded-lg border-gray-300 focus:ring-purple-500 focus:border-purple-500">
                                <option value="">Todas las regiones</option>
                                <option v-for="region in regions" :key="region" :value="region">
                                    {{ region }}
                                </option>
                            </select>
                        </div>

                        <!-- Ordenamiento -->
                        <div class="flex-1">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Ordenar por
                            </label>
                            <select v-model="sortBy" @change="handleSearch"
                                class="w-full rounded-lg border-gray-300 focus:ring-purple-500 focus:border-purple-500">
                                <option value="recent">Más recientes</option>
                                <option value="name">Nombre (A-Z)</option>
                                <option value="popular">Más populares</option>
                                <option value="events">Más eventos</option>
                            </select>
                        </div>
                    </div>

                    <!-- Limpiar Filtros -->
                    <div v-if="hasActiveFilters" class="lg:ml-4">
                        <button @click="clearFilters"
                            class="w-full lg:w-auto flex items-center justify-center gap-2 px-4 py-3 bg-red-50 text-red-600 rounded-lg hover:bg-red-100 transition">
                            <XMarkIcon class="h-5 w-5" />
                            Limpiar filtros
                        </button>
                    </div>
                </div>

                <!-- Resultados Count -->
                <div class="mt-4 pt-4 border-t border-gray-200">
                    <p class="text-sm text-gray-600">
                        Mostrando
                        <span class="font-semibold text-gray-900">{{ photographers.data.length }}</span>
                        de
                        <span class="font-semibold text-gray-900">{{ photographers.total }}</span>
                        fotógrafos
                    </p>
                </div>
            </div>

            <!-- Grid de Fotógrafos -->
            <div v-if="photographers.data.length > 0">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    <Link v-for="photographer in photographers.data" :key="photographer.id"
                        :href="route('photographers.show', photographer.slug)"
                        class="group bg-white rounded-2xl shadow-md hover:shadow-2xl transition-all duration-300 overflow-hidden border border-gray-100 hover:border-purple-300">
                    <!-- Header Card con Gradiente -->
                    <div class="h-32 bg-gradient-to-br from-purple-500 via-pink-500 to-orange-400 relative">
                        <div class="absolute inset-0 bg-black/10"></div>

                        <!-- Avatar -->
                        <div class="absolute -bottom-12 left-1/2 transform -translate-x-1/2">
                            <div class="w-24 h-24 rounded-full bg-white p-1 shadow-xl">
                                <div v-if="photographer.user.profile_photo_url"
                                    class="w-full h-full rounded-full overflow-hidden">
                                    <img :src="photographer.user.profile_photo_url" :alt="photographer.business_name"
                                        class="w-full h-full object-cover" />
                                </div>
                                <div v-else
                                    class="w-full h-full rounded-full bg-gradient-to-br from-purple-400 to-pink-400 flex items-center justify-center">
                                    <span class="text-2xl font-bold text-white">
                                        {{ getInitials(photographer.business_name) }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Contenido -->
                    <div class="pt-16 pb-6 px-6">
                        <!-- Nombre -->
                        <h3
                            class="text-xl font-bold text-gray-900 text-center mb-2 group-hover:text-purple-600 transition">
                            {{ photographer.business_name }}
                        </h3>

                        <!-- Ubicación -->
                        <div v-if="photographer.region"
                            class="flex items-center justify-center gap-1 text-sm text-gray-600 mb-4">
                            <MapPinIcon class="h-4 w-4" />
                            <span>{{ photographer.region }}</span>
                        </div>

                        <!-- Bio -->
                        <p v-if="photographer.bio" class="text-sm text-gray-600 text-center mb-6 line-clamp-2">
                            {{ photographer.bio }}
                        </p>

                        <!-- Estadísticas -->
                        <div class="grid grid-cols-2 gap-4 pt-4 border-t border-gray-100">
                            <div class="text-center">
                                <div class="flex items-center justify-center gap-1 text-purple-600 mb-1">
                                    <CalendarIcon class="h-5 w-5" />
                                </div>
                                <p class="text-2xl font-bold text-gray-900">{{ photographer.events_count }}</p>
                                <p class="text-xs text-gray-600">Eventos</p>
                            </div>
                            <div class="text-center">
                                <div class="flex items-center justify-center gap-1 text-pink-600 mb-1">
                                    <PhotoIcon class="h-5 w-5" />
                                </div>
                                <p class="text-2xl font-bold text-gray-900">{{ photographer.photos_count }}</p>
                                <p class="text-xs text-gray-600">Fotos</p>
                            </div>
                        </div>

                        <!-- Botón Ver Perfil -->
                        <div class="mt-6">
                            <div
                                class="w-full py-3 bg-gradient-to-r from-purple-600 to-pink-600 text-white text-center font-semibold rounded-xl group-hover:from-purple-700 group-hover:to-pink-700 transition-all duration-200 shadow-md group-hover:shadow-lg">
                                Ver Perfil
                            </div>
                        </div>
                    </div>

                    <!-- Badge Verificado -->
                    <div class="absolute top-4 right-4">
                        <div
                            class="bg-white/90 backdrop-blur-sm px-3 py-1 rounded-full flex items-center gap-1 shadow-lg">
                            <svg class="h-4 w-4 text-blue-500" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                    clip-rule="evenodd" />
                            </svg>
                            <span class="text-xs font-medium text-gray-700">Verificado</span>
                        </div>
                    </div>
                    </Link>
                </div>

                <!-- Paginación -->
                <div v-if="photographers.last_page > 1" class="mt-12">
                    <nav class="flex items-center justify-center gap-2 flex-wrap">
                        <template v-for="(link, index) in photographers.links" :key="index">
                            <!-- ✅ Solo renderizar si hay URL -->
                            <Link v-if="link.url" :href="link.url" :class="[
                                'px-4 py-2 rounded-lg font-medium transition-all duration-200',
                                link.active
                                    ? 'bg-gradient-to-r from-purple-600 to-pink-600 text-white shadow-lg'
                                    : 'bg-white text-gray-700 hover:bg-gray-50 border border-gray-300'
                            ]" v-html="link.label" />

                            <!-- ✅ Mostrar como span deshabilitado si NO hay URL -->
                            <span v-else :class="[
                                'px-4 py-2 rounded-lg font-medium opacity-50 cursor-not-allowed',
                                'bg-white text-gray-400 border border-gray-200'
                            ]" v-html="link.label" />
                        </template>
                    </nav>
                </div>

            </div>

            <!-- Sin Resultados -->
            <div v-else class="text-center py-16">
                <CameraIcon class="mx-auto h-24 w-24 text-gray-300 mb-6" />
                <h3 class="text-2xl font-bold text-gray-900 mb-2">No se encontraron fotógrafos</h3>
                <p class="text-gray-600 mb-8">
                    Intenta ajustar los filtros de búsqueda
                </p>
                <button @click="clearFilters"
                    class="inline-flex items-center gap-2 px-6 py-3 bg-purple-600 text-white font-semibold rounded-xl hover:bg-purple-700 transition">
                    <XMarkIcon class="h-5 w-5" />
                    Limpiar filtros
                </button>
            </div>

        </div>
    </AppLayout>
</template>
