<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import { PlusIcon, PencilIcon, TrashIcon, CalendarIcon, MapPinIcon } from '@heroicons/vue/24/outline';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    opportunities: Object,
});

const deleteOpportunity = (id) => {
    if (confirm('¿Confirmar eliminación? Se purgará esta oportunidad del sistema de forma permanente.')) {
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
        <div class="min-h-screen bg-[#050505] text-white selection:bg-[#E30613] selection:text-black py-12">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

                <div class="flex flex-col md:flex-row md:items-end justify-between mb-10 border-b border-zinc-800 pb-6 gap-6">
                    <div>
                        <span class="font-mono text-[10px] font-bold text-[#E30613] uppercase tracking-widest mb-2 block flex items-center gap-2">
                            <span class="w-2 h-2 bg-[#E30613] animate-pulse"></span>
                            Planificador Futuro
                        </span>
                        <h1 class="text-5xl md:text-6xl font-flux font-black text-white uppercase tracking-tighter leading-none">
                            Mis Oportunidades
                        </h1>
                        <p class="font-mono text-xs text-zinc-500 mt-4 uppercase tracking-widest  pl-3">
                            Gestión de prospectos y eventos en fase de organización
                        </p>
                    </div>
                    <Link :href="route('photographer.opportunities.create')"
                        class="bg-[#E30613] text-black px-6 py-3 border border-[#E30613] text-[10px] font-mono font-bold uppercase tracking-widest hover:bg-transparent hover:text-white transition-colors flex items-center justify-center gap-2 rounded-none w-max">
                        <PlusIcon class="w-4 h-4" />
                        Crear Instancia
                    </Link>
                </div>

                <div v-if="opportunities.data.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    <div v-for="opportunity in opportunities.data" :key="opportunity.id"
                        class="group bg-[#09090b] border border-zinc-800 overflow-hidden hover:border-[#E30613] hover:shadow-[8px_8px_0px_0px_rgba(227,6,19,1)] transition-all duration-300 flex flex-col">

                        <div class="relative h-56 bg-zinc-950 border-b border-zinc-800 overflow-hidden">
                            <img v-if="opportunity.cover_image" :src="opportunity.cover_image" :alt="opportunity.title"
                                class="w-full h-full object-cover filter grayscale opacity-60 group-hover:grayscale-0 group-hover:opacity-100 group-hover:scale-105 transition-all duration-700" />
                            
                            <div v-else class="w-full h-full flex items-center justify-center text-zinc-800">
                                <CalendarIcon class="w-12 h-12" />
                            </div>

                            <div class="absolute inset-0 bg-[#E30613]/0 group-hover:bg-[#E30613]/10 transition-colors duration-300 pointer-events-none mix-blend-multiply"></div>

                            <div class="absolute top-4 right-4 bg-[#E30613] text-black px-2 py-1 border border-black shadow-[4px_4px_0px_0px_rgba(0,0,0,1)]">
                                <span class="font-mono text-[9px] font-bold uppercase tracking-widest">
                                    {{ getDaysText(opportunity.days_until) }}
                                </span>
                            </div>
                        </div>

                        <div class="p-6 flex-1 flex flex-col">
                            <h3 class="font-flux text-3xl uppercase tracking-wider text-white mb-3 group-hover:text-[#E30613] transition-colors line-clamp-2 leading-none">
                                {{ opportunity.title }}
                            </h3>

                            <p class="font-mono text-xs text-zinc-400 mb-6 line-clamp-3 leading-relaxed flex-1">
                                {{ opportunity.description }}
                            </p>

                            <div class="space-y-3 mb-6 pt-4 border-t border-zinc-800">
                                <div class="flex items-center gap-3 font-mono text-[10px] font-bold uppercase tracking-widest text-zinc-500 group-hover:text-zinc-300 transition-colors">
                                    <CalendarIcon class="w-4 h-4 text-[#E30613]" />
                                    {{ opportunity.formatted_date }}
                                </div>
                                <div class="flex items-center gap-3 font-mono text-[10px] font-bold uppercase tracking-widest text-zinc-500 group-hover:text-zinc-300 transition-colors">
                                    <MapPinIcon class="w-4 h-4 text-[#E30613]" />
                                    <span class="line-clamp-1">{{ opportunity.location }}</span>
                                </div>
                            </div>

                            <div class="flex gap-2 mt-auto">
                                <Link :href="route('photographer.opportunities.edit', opportunity.id)"
                                    class="flex-1 py-3 bg-black border border-zinc-700 text-white font-mono text-[10px] font-bold uppercase tracking-widest hover:bg-white hover:text-black hover:border-white transition-colors flex items-center justify-center gap-2">
                                    <PencilIcon class="w-3.5 h-3.5" />
                                    Editar
                                </Link>
                                <button @click="deleteOpportunity(opportunity.id)"
                                    class="w-12 flex items-center justify-center bg-black border border-zinc-700 text-zinc-500 hover:bg-[#E30613] hover:text-black hover:border-[#E30613] transition-colors" title="Purgar">
                                    <TrashIcon class="w-4 h-4" />
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-else class="text-center py-24 border-2 border-dashed border-zinc-800 bg-[#09090b] mt-8">
                    <CalendarIcon class="w-16 h-16 text-zinc-700 mx-auto mb-4 stroke-1" />
                    <h3 class="font-flux text-2xl uppercase tracking-widest text-zinc-500 mb-2">
                        SYSTEM_EMPTY // 0 OPORTUNIDADES
                    </h3>
                    <p class="font-mono text-xs text-zinc-600 mb-8 uppercase tracking-widest">
                        Aún no existen registros de prospectos. Inicializa una nueva instancia.
                    </p>
                    <Link :href="route('photographer.opportunities.create')"
                        class="inline-block border border-zinc-700 bg-black text-white px-8 py-3 font-mono text-[10px] font-bold uppercase tracking-widest hover:border-[#E30613] hover:text-[#E30613] transition-colors">
                        Inicializar Registro
                    </Link>
                </div>

                <div v-if="opportunities.links && opportunities.last_page > 1" class="mt-12 pt-8 border-t border-zinc-800 flex flex-wrap items-center justify-center gap-2">
                    <template v-for="(link, index) in opportunities.links" :key="index">
                        <Link v-if="link.url" :href="link.url"
                            class="h-10 min-w-[2.5rem] px-3 flex items-center justify-center font-mono text-[10px] font-bold uppercase tracking-widest rounded-none transition-colors border"
                            :class="link.active 
                                ? 'bg-[#E30613] text-black border-[#E30613]' 
                                : 'bg-black text-zinc-500 border-zinc-800 hover:border-white hover:text-white'"
                            v-html="link.label"
                        />
                        <span v-else v-html="link.label" class="h-10 min-w-[2.5rem] px-3 flex items-center justify-center font-mono text-[10px] font-bold uppercase text-zinc-700 border border-transparent cursor-not-allowed"></span>
                    </template>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>