<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import { PlusIcon, PencilIcon, TrashIcon, CalendarIcon, MapPinIcon } from '@heroicons/vue/24/outline';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    opportunities: Object,
    participating_opportunities: Array,  
});
const deleteOpportunity = (id) => {
    if (confirm('¿Estás seguro de eliminar esta oportunidad? Se borrará del sistema de forma permanente.')) {
        router.delete(route('photographer.opportunities.destroy', id), {
            preserveScroll: true,
        });
    }
};

const getDaysText = (days) => {
    const roundedDays = Math.round(days);
    if (roundedDays === 0) return 'HOY';
    if (roundedDays === 1) return 'MAÑANA';
    if (roundedDays < 0) return `HACE ${Math.abs(roundedDays)} DÍAS`;
    return `EN ${roundedDays} DÍAS`;
};
</script>

<template>

    <Head title="Mis Oportunidades" />

    <AuthenticatedLayout>
        <div class="min-h-screen bg-[#F8F9FA] text-slate-800 font-sans antialiased py-12 pt-28">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">


                <div
                    class="flex flex-col md:flex-row md:items-end justify-between mb-10 border-b border-gray-200 pb-8 gap-6">
                    <div>
                        <span
                            class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-2 flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-[#E30613] animate-pulse"></span>
                            Planificador
                        </span>
                        <h1 class="text-5xl md:text-7xl font-flux text-black tracking-wide leading-none">
                            Mis <span class="text-[#E30613]">oportunidades</span>
                        </h1>
                        <p class="text-sm font-medium text-gray-500 mt-3 max-w-md">
                            Gestión de prospectos y eventos en fase de organización y planificación.
                        </p>
                    </div>

                    <Link :href="route('photographer.opportunities.create')"
                        class="bg-black text-white px-8 py-3.5 rounded-full text-xs font-bold uppercase tracking-wider hover:bg-[#E30613] hover:shadow-lg hover:shadow-red-500/30 transition-all flex items-center justify-center gap-2 w-max">
                        <PlusIcon class="w-5 h-5" />
                        Nueva oportunidad
                    </Link>
                </div>


                <div v-if="opportunities.data.length > 0"
                    class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 xl:gap-8 mb-16">
                    <div v-for="opportunity in opportunities.data" :key="opportunity.id"
                        class="group bg-white rounded overflow-hidden border border-gray-100 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300 flex flex-col">


                        <div class="relative h-56 bg-gray-100 overflow-hidden shrink-0">
                            <img v-if="opportunity.cover_image" :src="opportunity.cover_image" :alt="opportunity.title"
                                class="w-full h-full object-cover transition-transform duration-700 " />

                            <div v-else class="w-full h-full flex flex-col items-center justify-center text-gray-300">
                                <CalendarIcon class="w-12 h-12 mb-2" />
                                <span class="text-[10px] font-bold uppercase tracking-wider">Sin imagen</span>
                            </div>

                            <div
                                class="absolute inset-0 bg-gradient-to-t from-black/40 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none">
                            </div>


                            <div class="absolute top-4 right-4">
                                <span :class="[
                                    'px-3 py-1.5 rounded-full text-[10px] font-bold uppercase tracking-wider shadow-sm backdrop-blur-md border',
                                    opportunity.days_until <= 3 ? 'bg-red-50 text-[#E30613] border-red-100' : 'bg-white/90 text-black border-white/20'
                                ]">
                                    {{ getDaysText(opportunity.days_until) }}
                                </span>
                            </div>
                        </div>


                        <div class="p-6 md:p-8 flex-1 flex flex-col bg-white">


                            <div class="mb-6">
                                <h3
                                    class="text-2xl font-flux text-black leading-none mb-3 group-hover:text-[#E30613] transition-colors line-clamp-2">
                                    {{ opportunity.title }}
                                </h3>
                                <p class="text-sm text-gray-500 line-clamp-2 leading-relaxed">
                                    {{ opportunity.description }}
                                </p>
                            </div>


                            <div class="space-y-3 mb-6 pt-4 border-t border-gray-100 mt-auto">
                                <div
                                    class="flex items-center gap-2.5 text-xs font-bold text-gray-500 uppercase tracking-wider">
                                    <div
                                        class="w-6 h-6 rounded-full bg-gray-50 flex items-center justify-center shrink-0">
                                        <CalendarIcon class="w-3.5 h-3.5 text-[#E30613]" />
                                    </div>
                                    {{ opportunity.formatted_date }}
                                </div>
                                <div
                                    class="flex items-center gap-2.5 text-xs font-bold text-gray-500 uppercase tracking-wider">
                                    <div
                                        class="w-6 h-6 rounded-full bg-gray-50 flex items-center justify-center shrink-0">
                                        <MapPinIcon class="w-3.5 h-3.5 text-[#E30613]" />
                                    </div>
                                    <span class="truncate">{{ opportunity.location }}</span>
                                </div>
                            </div>

                            <div v-if="opportunity.collaborators && opportunity.collaborators.length > 0"
                                class="mb-6 flex items-center justify-between bg-gray-50/50 p-3 rounded-lg border border-gray-100">
                                <span class="text-[9px] font-bold text-gray-400 uppercase tracking-widest">
                                    Equipo ({{ opportunity.collaborators.length }})
                                </span>


                                <div class="flex items-center -space-x-2 overflow-hidden">
                                    <template v-for="(collaborator, index) in opportunity.collaborators.slice(0, 4)"
                                        :key="collaborator.id">
                                        <img :src="collaborator.profile_photo_url || `https://ui-avatars.com/api/?name=${collaborator.business_name}&background=random`"
                                            :title="collaborator.business_name"
                                            class="inline-block h-7 w-7 rounded-full ring-2 ring-white object-cover hover:z-10 hover:scale-110 transition-transform cursor-help"
                                            :style="{ zIndex: 10 - index }" />
                                    </template>


                                    <div v-if="opportunity.collaborators.length > 4"
                                        class="inline-block h-7 w-7 rounded-full ring-2 ring-white bg-gray-200 flex items-center justify-center z-0">
                                        <span class="text-[9px] font-bold text-gray-600">+{{
                                            opportunity.collaborators.length - 4 }}</span>
                                    </div>
                                </div>
                            </div>

                            <div v-if="opportunity.days_until > 0" class="flex gap-3">
                                <Link :href="route('photographer.opportunities.edit', opportunity.id)"
                                    class="flex-1 py-3 bg-gray-50 text-slate-700 border border-gray-200 rounded-full text-xs font-bold uppercase tracking-wider hover:bg-black hover:text-white hover:border-black transition-colors flex items-center justify-center gap-2">
                                    <PencilIcon class="w-4 h-4" /> Editar
                                </Link>
                                <button @click="deleteOpportunity(opportunity.id)" title="Eliminar oportunidad"
                                    class="w-12 h-12 flex items-center justify-center rounded-full bg-red-50 text-[#E30613] hover:bg-[#E30613] hover:text-white hover:shadow-lg hover:shadow-red-500/30 transition-all shrink-0">
                                    <TrashIcon class="w-5 h-5" />
                                </button>
                            </div>


                            <div v-else class="flex gap-3">
                                <Link :href="route('photographer.opportunities.convert', opportunity.id)" method="post"
                                    as="button"
                                    class="flex-1 py-3 bg-[#E30613] text-white rounded-full text-xs font-bold uppercase tracking-wider hover:bg-red-700 transition-colors shadow-md flex items-center justify-center gap-2 group/convert">
                                    <CameraIcon class="w-4 h-4 group-hover/convert:scale-110 transition-transform" />
                                    Subir archivo de fotos
                                </Link>
                            </div>


                        </div>
                    </div>
                </div>


                <div v-else
                    class="text-center py-24 bg-white rounded border border-gray-100 shadow-sm flex flex-col items-center mt-8">
                    <div class="w-20 h-20 bg-gray-50 rounded-full flex items-center justify-center mb-6">
                        <CalendarIcon class="w-10 h-10 text-gray-300" />
                    </div>
                    <h3 class="font-flux text-4xl text-black mb-3">Sin oportunidades</h3>
                    <p class="text-sm font-medium text-gray-500 mb-8 max-w-md mx-auto">
                        Aún no tenés prospectos ni eventos en planificación. Creá tu primera oportunidad para empezar a
                        organizar tu agenda.
                    </p>
                    <Link :href="route('photographer.opportunities.create')"
                        class="inline-block bg-black text-white px-8 py-3.5 rounded-full text-xs font-bold uppercase tracking-wider hover:bg-[#E30613] hover:shadow-lg hover:shadow-red-500/30 hover:-translate-y-1 transition-all">
                        Crear oportunidad
                    </Link>
                </div>


                <div v-if="opportunities.links && opportunities.last_page > 1" class="mt-16 flex justify-center">
                    <div class="flex flex-wrap gap-2 bg-white p-2 rounded-full shadow-sm border border-gray-100">
                        <template v-for="(link, index) in opportunities.links" :key="index">
                            <Link v-if="link.url" :href="link.url"
                                class="min-w-[40px] h-10 flex items-center justify-center px-4 rounded-full text-xs font-bold transition-colors"
                                :class="link.active
                                    ? 'bg-[#E30613] text-white shadow-md'
                                    : 'bg-transparent text-gray-600 hover:bg-gray-100 hover:text-black'"
                                v-html="link.label" />
                            <span v-else v-html="link.label"
                                class="min-w-[40px] h-10 flex items-center justify-center px-4 rounded-full text-xs font-bold text-gray-300 cursor-not-allowed"></span>
                        </template>
                    </div>
                </div>

                <div v-if="participating_opportunities && participating_opportunities.length > 0" class="mt-20 mb-16">
                    <div class="mb-8 border-b border-gray-200 pb-4">
                        <h2 class="text-3xl font-flux text-black">Mis postulaciones y <span class="text-[#E30613]">colaboraciones</span></h2>
                        <p class="text-sm text-gray-500 mt-2">Eventos de otros organizadores donde enviaste solicitud o ya sos parte del equipo.</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <div v-for="participation in participating_opportunities" :key="participation.id" 
                             class="bg-white rounded-lg border border-gray-100 shadow-sm p-6 flex flex-col hover:shadow-md transition-shadow">
                            
                            <div class="flex items-center gap-4 mb-4 border-b border-gray-50 pb-4">
                                <img v-if="participation.cover_image" :src="participation.cover_image" class="w-16 h-16 rounded object-cover" />
                                <div v-else class="w-16 h-16 rounded bg-gray-100 flex items-center justify-center">
                                    <CalendarIcon class="w-6 h-6 text-gray-400" />
                                </div>
                                <div>
                                    <h4 class="font-bold text-black line-clamp-1">{{ participation.title }}</h4>
                                    <p class="text-xs text-gray-500 flex items-center gap-1 mt-1">
                                        Organiza: <span class="font-bold text-gray-700">{{ participation.owner_name }}</span>
                                    </p>
                                </div>
                            </div>

                            <div class="space-y-2 mb-6 text-xs font-bold text-gray-500 uppercase tracking-wider">
                                <p class="flex items-center gap-2"><CalendarIcon class="w-4 h-4 text-[#E30613]"/> {{ participation.formatted_date }}</p>
                                <p class="flex items-center gap-2"><MapPinIcon class="w-4 h-4 text-[#E30613]"/> {{ participation.location }}</p>
                            </div>


                            <div class="mt-auto">
                                <div v-if="participation.my_status === 'approved'" class="bg-green-50 border border-green-200 text-green-700 px-4 py-2 rounded-full text-xs font-bold uppercase tracking-wider text-center flex items-center justify-center gap-2">
                                    <span class="w-2 h-2 rounded-full bg-green-500 animate-pulse"></span> Equipo Confirmado
                                </div>
                                <div v-else-if="participation.my_status === 'requested'" class="bg-yellow-50 border border-yellow-200 text-yellow-700 px-4 py-2 rounded-full text-xs font-bold uppercase tracking-wider text-center">
                                    En revisión
                                </div>
                                <div v-else-if="participation.my_status === 'rejected'" class="bg-red-50 border border-red-200 text-red-700 px-4 py-2 rounded-full text-xs font-bold uppercase tracking-wider text-center">
                                    No seleccionado
                                </div>
                            </div>

                        </div>
                    </div>
                </div>

            </div>


            
        </div>

        
    </AuthenticatedLayout>
</template>