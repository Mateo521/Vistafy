<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import {
    CalendarIcon,
    PhotoIcon,
    ArrowDownTrayIcon,
    MapPinIcon,
    PlusCircleIcon,
    BellAlertIcon,
    EyeIcon
} from '@heroicons/vue/24/outline';

const props = defineProps({
    photographer: Object,
    stats: Object,
    recentPhotos: Array,
    recentEvents: Array,
    pendingInvitations: {
        type: Array,
        default: () => []
    },
});

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString('es-ES', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
};

const getImageUrl = (photo) => {
    return photo.watermarked_url || photo.thumbnail_url || null;
};

const handleImageError = (e) => {
    e.target.style.display = 'none';
    const parent = e.target.parentElement;
    if (parent) {
        parent.innerHTML = `
            <div class="w-full h-full flex items-center justify-center bg-gray-50 border border-gray-100 rounded">
                <span class="font-bold text-gray-300 text-xl">F33</span>
            </div>
        `;
    }
};
</script>

<template>

    <Head title="Panel de Control | F33" />

    <AuthenticatedLayout>
        <div class="py-12 bg-[#F8F9FA] min-h-screen text-slate-800 antialiased pt-24 md:pt-28">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">


                <div class="flex flex-col md:flex-row md:items-end justify-between gap-6 mb-8">
                    <div>
                        <div class="flex items-center gap-2 mb-3">
                            <span class="relative flex h-2.5 w-2.5">
                                <span
                                    class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-[#E30613]"></span>
                            </span>
                            <span class="text-xs font-bold text-gray-500 uppercase tracking-wider">
                                Panel fotógrafo
                            </span>
                        </div>
                        <h1 class="text-4xl md:text-5xl font-flux text-black tracking-wide leading-none">
                            {{ photographer.business_name || photographer.user.name }}


                        </h1>
                        <div class="bg-yellow-100 p-4 mb-4 text-xs font-mono overflow-auto">
                            DEBUG inv.: {{ pendingInvitations }}
                        </div>
                    </div>


                    <div class="flex flex-wrap gap-3">
                        <Link :href="route('photographer.photos.create')"
                            class="px-6 py-3 bg-white border border-gray-200 text-black text-xs font-bold uppercase tracking-wider hover:bg-gray-50 hover:shadow-sm transition-all rounded-full flex items-center gap-2">
                            <PhotoIcon class="w-4 h-4" /> Cargar Fotos
                        </Link>
                        <Link :href="route('photographer.events.create')"
                            class="px-6 py-3 bg-[#E30613] text-white text-xs font-bold uppercase tracking-wider hover:bg-red-700 hover:shadow-lg hover:shadow-red-500/30 transition-all rounded-full flex items-center gap-2">
                            <CalendarIcon class="w-4 h-4" /> Nuevo Evento
                        </Link>
                    </div>
                </div>


                <div v-if="pendingInvitations && pendingInvitations.length > 0" class="mb-8 space-y-3">
                    <div v-for="invitation in pendingInvitations" :key="invitation.id"
                        class="bg-white border border-blue-100 p-5 md:p-6 rounded shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4 relative overflow-hidden">


                        <div
                            class="absolute top-0 right-0 w-32 h-32 bg-blue-50 rounded-full blur-3xl -mr-10 -mt-10 pointer-events-none">
                        </div>

                        <div class="flex items-start gap-4 relative z-10">
                            <div
                                class="w-10 h-10 rounded-full bg-blue-50 flex items-center justify-center shrink-0 text-blue-600">
                                <BellAlertIcon class="w-5 h-5 animate-bounce" style="animation-iteration-count: 3;" />
                            </div>
                            <div>
                                <div class="flex items-center gap-2 mb-1">
                                    <span class="text-sm font-bold text-black">{{ invitation.name }}</span>
                                    <span
                                        class="bg-blue-100 text-blue-700 text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-full">
                                        Nueva invitación
                                    </span>
                                </div>
                                <p class="text-sm text-gray-500">
                                    <strong class="text-slate-700">{{ invitation.photographer.business_name }}</strong>
                                    te invitó a colaborar como fotógrafo.
                                </p>
                            </div>
                        </div>

                        <div class="flex gap-2 shrink-0 relative z-10 w-full md:w-auto">
                            <Link :href="route('photographer.opportunities.reject', invitation.id)" method="post"
                                as="button"
                                class="flex-1 md:flex-none px-6 py-2.5 bg-gray-50 text-gray-600 hover:bg-gray-100 hover:text-black rounded-full text-xs font-bold uppercase tracking-wider transition-colors text-center border border-gray-200">
                                Rechazar
                            </Link>
                            <Link :href="route('photographer.opportunities.accept', invitation.id)" method="post"
                                as="button"
                                class="flex-1 md:flex-none px-6 py-2.5 bg-black text-white hover:bg-gray-800 rounded-full text-xs font-bold uppercase tracking-wider transition-colors shadow-md text-center">
                                Aceptar
                            </Link>
                        </div>
                    </div>
                </div>


                <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 md:gap-6 mb-12">

                    <div
                        class="bg-white p-6 rounded shadow-sm border border-gray-100 flex flex-col justify-between group hover:shadow-md hover:-translate-y-1 transition-all">
                        <div class="flex justify-between items-start mb-4">
                            <div
                                class="w-10 h-10 rounded-full bg-gray-50 flex items-center justify-center group-hover:bg-red-50 transition-colors">
                                <PhotoIcon class="w-5 h-5 text-gray-400 group-hover:text-[#E30613]" />
                            </div>
                        </div>
                        <div>
                            <span class="text-4xl md:text-5xl font-flux text-black tracking-wide block mb-1">{{
                                stats.total_photos }}</span>
                            <span class="text-xs font-bold uppercase tracking-wider text-gray-400">Total Archivo</span>
                        </div>
                    </div>


                    <div
                        class="bg-white p-6 rounded shadow-sm border border-gray-100 flex flex-col justify-between group hover:shadow-md hover:-translate-y-1 transition-all">
                        <div class="flex justify-between items-start mb-4">
                            <div
                                class="w-10 h-10 rounded-full bg-gray-50 flex items-center justify-center group-hover:bg-red-50 transition-colors">
                                <EyeIcon class="w-5 h-5 text-gray-400 group-hover:text-[#E30613]" />
                            </div>
                        </div>
                        <div>
                            <span class="text-4xl md:text-5xl font-flux text-black tracking-wide block mb-1">{{
                                stats.active_photos }}</span>
                            <span class="text-xs font-bold uppercase tracking-wider text-gray-400">Públicas</span>
                        </div>
                    </div>


                    <div
                        class="bg-white p-6 rounded shadow-sm border border-gray-100 flex flex-col justify-between group hover:shadow-md hover:-translate-y-1 transition-all">
                        <div class="flex justify-between items-start mb-4">
                            <div
                                class="w-10 h-10 rounded-full bg-gray-50 flex items-center justify-center group-hover:bg-red-50 transition-colors">
                                <ArrowDownTrayIcon class="w-5 h-5 text-gray-400 group-hover:text-[#E30613]" />
                            </div>
                        </div>
                        <div>
                            <span class="text-4xl md:text-5xl font-flux text-black tracking-wide block mb-1">{{
                                stats.total_downloads }}</span>
                            <span class="text-xs font-bold uppercase tracking-wider text-gray-400">Descargas</span>
                        </div>
                    </div>


                    <div
                        class="bg-white p-6 rounded shadow-sm border border-gray-100 flex flex-col justify-between group hover:shadow-md hover:-translate-y-1 transition-all">
                        <div class="flex justify-between items-start mb-4">
                            <div
                                class="w-10 h-10 rounded-full bg-gray-50 flex items-center justify-center group-hover:bg-red-50 transition-colors">
                                <CalendarIcon class="w-5 h-5 text-gray-400 group-hover:text-[#E30613]" />
                            </div>
                        </div>
                        <div>
                            <span class="text-4xl md:text-5xl font-flux text-black tracking-wide block mb-1">{{
                                stats.total_events }}</span>
                            <span class="text-xs font-bold uppercase tracking-wider text-gray-400">Misiones /
                                Eventos</span>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 xl:grid-cols-3 gap-8 xl:gap-12">


                    <div class="xl:col-span-2">
                        <div class="flex items-center justify-between mb-6">
                            <h3 class="text-2xl font-flux text-black tracking-wide">Eventos recientes</h3>
                            <Link :href="route('photographer.events.index')"
                                class="text-xs font-bold uppercase tracking-wider text-gray-500 hover:text-[#E30613] transition-colors">
                                Ver registro &rarr;
                            </Link>
                        </div>

                        <div v-if="recentEvents && recentEvents.length > 0" class="space-y-4">
                            <Link v-for="(event, index) in recentEvents" :key="event.id"
                                :href="route('photographer.events.show', event.id)"
                                class="group flex flex-col sm:flex-row bg-white border border-gray-100 hover:shadow-md transition-all duration-300 rounded overflow-hidden">


                                <div
                                    class="w-full sm:w-48 h-48 sm:h-auto relative overflow-hidden bg-gray-100 shrink-0">
                                    <img v-if="event.cover_image_url" :src="event.cover_image_url" :alt="event.name"
                                        class="w-full h-full object-cover transition-transform duration-700 "
                                        @error="handleImageError" />
                                    <div v-else class="w-full h-full flex items-center justify-center text-gray-300">
                                        <PhotoIcon class="w-10 h-10" />
                                    </div>


                                    <div class="absolute top-3 left-3">
                                        <span :class="[
                                            'px-2.5 py-1 rounded-md text-[10px] font-bold uppercase tracking-wider shadow-sm backdrop-blur-md',
                                            event.is_private ? 'bg-red-50 text-[#E30613]' : 'bg-white/90 text-black'
                                        ]">
                                            {{ event.is_private ? 'Clasificado' : 'Público' }}
                                        </span>
                                    </div>
                                </div>


                                <div class="p-6 flex flex-col justify-between flex-1">
                                    <div>
                                        <div class="flex justify-between items-start mb-2">
                                            <h4
                                                class="text-xl font-bold text-black group-hover:text-[#E30613] transition-colors line-clamp-1">
                                                {{ event.name }}
                                            </h4>
                                            <span
                                                class="text-[10px] font-bold text-gray-400 uppercase tracking-wider whitespace-nowrap ml-4 bg-gray-50 px-2 py-1 rounded-md">
                                                {{ formatDate(event.event_date) }}
                                            </span>
                                        </div>
                                        <p v-if="event.description"
                                            class="text-sm text-gray-500 line-clamp-2 mb-4 leading-relaxed">
                                            {{ event.description }}
                                        </p>
                                    </div>

                                    <div class="flex items-end justify-between mt-4">
                                        <div
                                            class="flex items-center gap-4 text-xs font-bold text-gray-500 uppercase tracking-wider">
                                            <span class="flex items-center gap-1.5">
                                                <PhotoIcon class="w-4 h-4 text-gray-400" />
                                                {{ event.photos_count || 0 }} fotos
                                            </span>
                                            <span v-if="event.location"
                                                class="flex items-center gap-1.5 truncate max-w-[150px]">
                                                <MapPinIcon class="w-4 h-4 text-gray-400" />
                                                {{ event.location }}
                                            </span>
                                        </div>
                                        <span
                                            class="w-8 h-8 rounded-full bg-gray-50 group-hover:bg-[#E30613] flex items-center justify-center transition-colors">
                                            <svg class="w-4 h-4 text-gray-400 group-hover:text-white transition-colors"
                                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M9 5l7 7-7 7"></path>
                                            </svg>
                                        </span>
                                    </div>
                                </div>
                            </Link>
                        </div>


                        <div v-else
                            class="text-center py-16 bg-white rounded border border-gray-100 shadow-sm flex flex-col items-center">
                            <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mb-4">
                                <CalendarIcon class="w-8 h-8 text-gray-300" />
                            </div>
                            <p class="text-gray-500 text-sm font-bold uppercase tracking-wider mb-6">Sin eventos activos
                            </p>
                            <Link :href="route('photographer.events.create')"
                                class="px-6 py-3 bg-black text-white rounded-full text-xs font-bold uppercase tracking-wider hover:bg-gray-800 transition-colors">
                                Crear evento
                            </Link>
                        </div>
                    </div>


                    <div class="xl:col-span-1">
                        <div class="flex items-center justify-between mb-6">
                            <h3 class="text-2xl font-flux text-black tracking-wide">Últimas fotos</h3>
                            <Link :href="route('photographer.photos.index')"
                                class="text-xs font-bold uppercase tracking-wider text-gray-500 hover:text-[#E30613] transition-colors">
                                Ver Todo &rarr;
                            </Link>
                        </div>

                        <div v-if="recentPhotos && recentPhotos.length > 0" class="grid grid-cols-2 gap-3">
                            <div v-for="photo in recentPhotos" :key="photo.id"
                                class="aspect-square bg-gray-100 rounded overflow-hidden relative group border border-gray-200 shadow-sm hover:shadow-md transition-all">

                                <img v-if="getImageUrl(photo)" :src="getImageUrl(photo)" :alt="photo.unique_id"
                                    class="w-full h-full object-cover transition-transform duration-700 "
                                    @error="handleImageError" />

                                <div v-else class="w-full h-full flex items-center justify-center bg-gray-50">
                                    <span class="font-bold text-xl text-gray-300">F33</span>
                                </div>


                                <div
                                    class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex flex-col justify-end p-3">
                                    <span class="text-white font-bold text-[10px] uppercase tracking-wider truncate">
                                        ID: {{ photo.unique_id.substring(0, 6) }}
                                    </span>
                                </div>

                                <Link :href="route('photographer.photos.show', photo.id)" class="absolute inset-0 z-10">
                                </Link>
                            </div>
                        </div>


                        <div v-else
                            class="text-center py-16 bg-white rounded border border-gray-100 shadow-sm flex flex-col items-center h-full justify-center">
                            <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mb-4">
                                <PhotoIcon class="w-8 h-8 text-gray-300" />
                            </div>
                            <p
                                class="text-gray-500 text-xs font-bold uppercase tracking-wider mb-6 text-center max-w-[200px]">
                                No hay datos<br>transmitidos</p>
                            <Link :href="route('photographer.photos.create')"
                                class="px-6 py-3 bg-white border border-gray-200 text-black rounded-full text-xs font-bold uppercase tracking-wider hover:bg-gray-50 transition-colors">
                                Iniciar carga
                            </Link>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>