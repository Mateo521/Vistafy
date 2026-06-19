<script setup>
import { ref } from 'vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import {
    ArrowLeftIcon,
    CheckCircleIcon,
    XCircleIcon,
    NoSymbolIcon,
    ArrowPathIcon,
    UserIcon,
    MapPinIcon,
    PhoneIcon,
    EnvelopeIcon,
    CalendarIcon,
    PhotoIcon,
    ArrowDownTrayIcon
} from '@heroicons/vue/24/outline';

const props = defineProps({
    photographer: Object,
    stats: Object,
});

const showRevertModal = ref(false);
const showSuspendModal = ref(false);
const showActivateModal = ref(false);

const suspendForm = useForm({
    reason: '',
});

const getStatusConfig = (status) => {
    const configs = {
        pending: { dot: 'bg-yellow-500', text: 'PENDIENTE', class: 'text-yellow-500 bg-yellow-500/10 border-yellow-500' },
        approved: { dot: 'bg-emerald-500', text: 'APROBADO', class: 'text-emerald-500 bg-emerald-500/10 border-emerald-500' },
        rejected: { dot: 'bg-[#E30613]', text: 'RECHAZADO', class: 'text-[#E30613] bg-[#E30613]/10 border-[#E30613]' },
        suspended: { dot: 'bg-zinc-500', text: 'SUSPENDIDO', class: 'text-zinc-400 bg-zinc-900 border-zinc-700' },
    };
    return configs[status] || { dot: 'bg-zinc-500', text: status.toUpperCase(), class: 'text-zinc-500 bg-zinc-900 border-zinc-700' };
};

const confirmRevert = () => {
    router.post(route('admin.photographers.revert', props.photographer.id), {}, {
        preserveScroll: true,
        onSuccess: () => showRevertModal.value = false,
    });
};

const confirmSuspend = () => {
    suspendForm.post(route('admin.photographers.suspend', props.photographer.id), {
        preserveScroll: true,
        onSuccess: () => {
            showSuspendModal.value = false;
            suspendForm.reset();
        },
    });
};

const confirmActivate = () => {
    router.post(route('admin.photographers.reactivate', props.photographer.id), {}, {
        preserveScroll: true,
        onSuccess: () => showActivateModal.value = false,
    });
};

const formatDate = (date) => {
    if (!date) return 'N/A';
    return new Date(date).toLocaleDateString('es-ES', { year: 'numeric', month: '2-digit', day: '2-digit' }).replace(/\//g, '.');
};
</script>

<template>
    <AuthenticatedLayout>
        <Head :title="`[ADMIN] ${photographer.business_name}`" />

        <div class="min-h-screen bg-[#050505] text-white selection:bg-[#E30613] selection:text-black py-12">
            <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">

                <div class="mb-8 border-b border-zinc-800 pb-6 flex flex-col md:flex-row md:items-end justify-between gap-6">
                    <div>
                        <Link :href="route('admin.photographers.index')" class="font-mono text-[10px] font-bold uppercase tracking-widest text-zinc-500 hover:text-white mb-4 transition-colors flex items-center gap-2 w-max">
                            <ArrowLeftIcon class="w-3 h-3" /> Volver al Directorio
                        </Link>
                        <h1 class="text-5xl md:text-6xl font-flux font-black text-white uppercase tracking-tighter leading-none">
                            {{ photographer.business_name }}
                        </h1>
                        <p class="font-mono text-xs text-zinc-400 mt-3 flex items-center gap-2 uppercase tracking-widest">
                            <UserIcon class="w-4 h-4 text-[#E30613]" /> {{ photographer.user.name }}
                        </p>
                    </div>

                    <div class="flex flex-wrap items-center gap-3">
                        <div :class="['px-3 py-1.5 border font-mono text-[10px] font-bold tracking-widest flex items-center gap-2', getStatusConfig(photographer.status).class]">
                            <span :class="['w-2 h-2 rounded-none animate-pulse', getStatusConfig(photographer.status).dot]"></span>
                            {{ getStatusConfig(photographer.status).text }}
                        </div>

                        <template v-if="photographer.status === 'rejected'">
                            <button @click="showRevertModal = true" class="bg-yellow-500 text-black border-2 border-yellow-500 px-4 py-1.5 text-[10px] font-mono font-bold uppercase tracking-widest hover:bg-transparent hover:text-yellow-500 transition-colors flex items-center gap-2">
                                <ArrowPathIcon class="w-4 h-4" /> Revertir
                            </button>
                        </template>

                        <template v-else-if="photographer.status === 'approved'">
                            <button @click="showSuspendModal = true" class="bg-transparent border border-zinc-700 text-zinc-400 px-4 py-1.5 text-[10px] font-mono font-bold uppercase tracking-widest hover:border-[#E30613] hover:text-[#E30613] transition-colors flex items-center gap-2">
                                <NoSymbolIcon class="w-4 h-4" /> Suspender
                            </button>
                        </template>

                        <template v-else-if="photographer.status === 'suspended'">
                            <button @click="showActivateModal = true" class="bg-emerald-600 text-black border-2 border-emerald-600 px-4 py-1.5 text-[10px] font-mono font-bold uppercase tracking-widest hover:bg-transparent hover:text-emerald-500 transition-colors flex items-center gap-2 shadow-[4px_4px_0px_0px_rgba(5,150,105,0.3)]">
                                <CheckCircleIcon class="w-4 h-4" /> Reactivar
                            </button>
                        </template>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    
                    <div class="lg:col-span-2 space-y-8">
                        
                        <div class="bg-[#09090b] border border-zinc-800 p-8 shadow-[4px_4px_0px_0px_rgba(255,255,255,0.05)]">
                            <h3 class="font-mono text-[10px] font-bold uppercase tracking-widest text-zinc-500 mb-6 border-b border-zinc-800 pb-2">
                                Información de Contacto
                            </h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block font-mono text-[10px] font-bold uppercase tracking-widest text-zinc-500 mb-2">Email</label>
                                    <p class="font-mono text-xs text-white flex items-center gap-2">
                                        <EnvelopeIcon class="w-4 h-4 text-[#E30613]" /> {{ photographer.user.email }}
                                    </p>
                                </div>
                                <div>
                                    <label class="block font-mono text-[10px] font-bold uppercase tracking-widest text-zinc-500 mb-2">Teléfono</label>
                                    <p class="font-mono text-xs text-white flex items-center gap-2">
                                        <PhoneIcon class="w-4 h-4 text-[#E30613]" /> {{ photographer.phone || 'N/A' }}
                                    </p>
                                </div>
                                <div>
                                    <label class="block font-mono text-[10px] font-bold uppercase tracking-widest text-zinc-500 mb-2">Región</label>
                                    <p class="font-mono text-xs text-white flex items-center gap-2 uppercase">
                                        <MapPinIcon class="w-4 h-4 text-[#E30613]" /> {{ photographer.region || 'N/A' }}
                                    </p>
                                </div>
                                <div>
                                    <label class="block font-mono text-[10px] font-bold uppercase tracking-widest text-zinc-500 mb-2">Registro</label>
                                    <p class="font-mono text-xs text-white">
                                        {{ formatDate(photographer.created_at) }}
                                    </p>
                                </div>
                            </div>

                            <div v-if="photographer.bio" class="mt-8 pt-8 border-t border-zinc-800">
                                <label class="block font-mono text-[10px] font-bold uppercase tracking-widest text-zinc-500 mb-3">Biografía</label>
                                <p class="font-mono text-xs text-zinc-400 leading-relaxed  pl-4">
                                    {{ photographer.bio }}
                                </p>
                            </div>
                        </div>

                        <div v-if="photographer.rejection_reason || photographer.suspension_reason" class="bg-[#E30613]/5 border border-[#E30613] p-8 relative overflow-hidden">
                            <XCircleIcon class="absolute -right-8 -bottom-8 w-48 h-48 text-[#E30613] opacity-10 pointer-events-none" />
                            <h3 class="font-mono text-[10px] font-bold uppercase tracking-widest text-[#E30613] mb-6 flex items-center gap-2 border-b border-[#E30613]/20 pb-2 relative z-10">
                                <XCircleIcon class="w-4 h-4" /> Historial de Incidencias
                            </h3>
                            
                            <div v-if="photographer.rejection_reason" class="mb-6 relative z-10">
                                <p class="font-mono text-[10px] font-bold uppercase tracking-widest text-[#E30613] mb-2">> Motivo de Rechazo</p>
                                <p class="font-mono text-xs text-white bg-black/50 p-4 border-l border-[#E30613]">{{ photographer.rejection_reason }}</p>
                            </div>
                            
                            <div v-if="photographer.suspension_reason" class="relative z-10">
                                <p class="font-mono text-[10px] font-bold uppercase tracking-widest text-[#E30613] mb-2">> Motivo de Suspensión</p>
                                <p class="font-mono text-xs text-white bg-black/50 p-4 border-l border-[#E30613]">{{ photographer.suspension_reason }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="lg:col-span-1 space-y-6">
                        <div class="bg-[#09090b] border border-zinc-800 p-8 shadow-[4px_4px_0px_0px_rgba(255,255,255,0.05)]">
                            <h3 class="font-mono text-[10px] font-bold uppercase tracking-widest text-zinc-500 mb-6 border-b border-zinc-800 pb-2">
                                Métricas Operativas
                            </h3>
                            
                            <div class="space-y-6">
                                <div class="bg-black border border-zinc-800 p-4 group hover:border-white transition-colors">
                                    <div class="flex justify-between items-center mb-2">
                                        <span class="font-mono text-[10px] font-bold uppercase tracking-widest text-zinc-500 group-hover:text-white transition-colors">Eventos</span>
                                        <CalendarIcon class="w-4 h-4 text-zinc-600" />
                                    </div>
                                    <p class="font-flux text-5xl text-white">{{ stats.events_count || 0 }}</p>
                                </div>

                                <div class="bg-black border border-zinc-800 p-4 group hover:border-[#E30613] transition-colors">
                                    <div class="flex justify-between items-center mb-2">
                                        <span class="font-mono text-[10px] font-bold uppercase tracking-widest text-zinc-500 group-hover:text-[#E30613] transition-colors">Fotografías</span>
                                        <PhotoIcon class="w-4 h-4 text-zinc-600" />
                                    </div>
                                    <p class="font-flux text-5xl text-[#E30613]">{{ stats.photos_count || 0 }}</p>
                                </div>

                                <div class="bg-black border border-zinc-800 p-4 group hover:border-white transition-colors">
                                    <div class="flex justify-between items-center mb-2">
                                        <span class="font-mono text-[10px] font-bold uppercase tracking-widest text-zinc-500 group-hover:text-white transition-colors">Descargas</span>
                                        <ArrowDownTrayIcon class="w-4 h-4 text-zinc-600" />
                                    </div>
                                    <p class="font-flux text-5xl text-white">{{ stats.downloads_count || 0 }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <Teleport to="body">
            
            <div v-if="showSuspendModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/90 backdrop-blur-sm" @click.self="showSuspendModal = false">
                <div class="bg-[#09090b] border border-[#E30613] shadow-[8px_8px_0px_0px_rgba(227,6,19,1)] max-w-md w-full">
                    <div class="p-8">
                        <h3 class="font-flux text-3xl uppercase tracking-widest text-white mb-2">Suspender Cuenta</h3>
                        <p class="font-mono text-xs text-zinc-400 mb-6">
                            Se revocará el acceso operativo a <strong class="text-white">{{ photographer.business_name }}</strong>. Podrás reactivarlo posteriormente.
                        </p>
                        
                        <label class="block font-mono text-[10px] font-bold uppercase tracking-widest text-zinc-500 mb-2">> Motivo de suspensión (Opcional)</label>
                        <textarea v-model="suspendForm.reason" rows="3" class="w-full bg-black border border-zinc-700 text-white font-mono text-xs focus:border-[#E30613] focus:ring-0 resize-none mb-6 p-3" placeholder="Explique la infracción..."></textarea>
                        
                        <div class="flex flex-col sm:flex-row justify-end gap-4">
                            <button @click="showSuspendModal = false" class="px-6 py-3 border border-zinc-700 bg-transparent font-mono text-[10px] font-bold uppercase tracking-widest text-zinc-400 hover:bg-white hover:text-black transition-colors text-center">Cancelar</button>
                            <button @click="confirmSuspend" :disabled="suspendForm.processing" class="px-6 py-3 bg-[#E30613] font-mono text-[10px] font-bold uppercase tracking-widest text-black hover:bg-white transition-colors disabled:opacity-50 text-center">Ejecutar Suspensión</button>
                        </div>
                    </div>
                </div>
            </div>

            <div v-if="showActivateModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/90 backdrop-blur-sm" @click.self="showActivateModal = false">
                <div class="bg-[#09090b] border border-emerald-500 shadow-[8px_8px_0px_0px_rgba(16,185,129,1)] max-w-md w-full">
                    <div class="p-8">
                        <h3 class="font-flux text-3xl uppercase tracking-widest text-white mb-2">Reactivar Cuenta</h3>
                        <p class="font-mono text-xs text-zinc-400 mb-8">
                            Restaurar privilegios. El perfil <strong class="text-white">{{ photographer.business_name }}</strong> volverá a estar operativo y público en la plataforma.
                        </p>
                        <div class="flex flex-col sm:flex-row justify-end gap-4">
                            <button @click="showActivateModal = false" class="px-6 py-3 border border-zinc-700 bg-transparent font-mono text-[10px] font-bold uppercase tracking-widest text-zinc-400 hover:bg-white hover:text-black transition-colors text-center">Cancelar</button>
                            <button @click="confirmActivate" class="px-6 py-3 bg-emerald-500 font-mono text-[10px] font-bold uppercase tracking-widest text-black hover:bg-white transition-colors text-center">Aprobar y Reactivar</button>
                        </div>
                    </div>
                </div>
            </div>

            <div v-if="showRevertModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/90 backdrop-blur-sm" @click.self="showRevertModal = false">
                <div class="bg-[#09090b] border border-yellow-500 shadow-[8px_8px_0px_0px_rgba(234,179,8,1)] max-w-md w-full">
                    <div class="p-8">
                        <h3 class="font-flux text-3xl uppercase tracking-widest text-white mb-2">Revertir Rechazo</h3>
                        <p class="font-mono text-xs text-zinc-400 mb-8">
                            El estado del fotógrafo volverá a <strong class="text-yellow-500">PENDIENTE</strong> y requerirá una nueva auditoría.
                        </p>
                        <div class="flex flex-col sm:flex-row justify-end gap-4">
                            <button @click="showRevertModal = false" class="px-6 py-3 border border-zinc-700 bg-transparent font-mono text-[10px] font-bold uppercase tracking-widest text-zinc-400 hover:bg-white hover:text-black transition-colors text-center">Cancelar</button>
                            <button @click="confirmRevert" class="px-6 py-3 bg-yellow-500 font-mono text-[10px] font-bold uppercase tracking-widest text-black hover:bg-white transition-colors text-center">Revertir Estado</button>
                        </div>
                    </div>
                </div>
            </div>

        </Teleport>
    </AuthenticatedLayout>
</template>