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
    PhotoIcon
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
        pending: { dot: 'bg-amber-400', text: 'Pendiente', class: 'text-amber-700 bg-amber-50 border-amber-200' },
        approved: { dot: 'bg-emerald-500', text: 'Aprobado', class: 'text-emerald-700 bg-emerald-50 border-emerald-200' },
        rejected: { dot: 'bg-red-500', text: 'Rechazado', class: 'text-red-700 bg-red-50 border-red-200' },
        suspended: { dot: 'bg-slate-400', text: 'Suspendido', class: 'text-slate-600 bg-slate-100 border-slate-200' },
    };
    return configs[status] || { dot: 'bg-gray-300', text: status, class: 'text-gray-500 bg-gray-50 border-gray-200' };
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
    return new Date(date).toLocaleDateString('es-ES', { year: 'numeric', month: 'long', day: 'numeric' });
};
</script>

<template>
    <AuthenticatedLayout>
        <Head :title="`Fotógrafo: ${photographer.business_name}`" />

        <div class="py-12">
            <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">

                <div class="mb-8 border-b border-gray-200 pb-6 flex flex-col md:flex-row md:items-end justify-between gap-4">
                    <div>
                        <Link :href="route('admin.photographers.index')" class="text-[10px] font-bold uppercase tracking-widest text-slate-400 hover:text-slate-900 mb-2 block transition-colors flex items-center gap-1">
                            <ArrowLeftIcon class="w-3 h-3" /> Volver al Directorio
                        </Link>
                        <h1 class="text-3xl font-serif font-bold text-slate-900">
                            {{ photographer.business_name }}
                        </h1>
                        <p class="text-sm text-slate-500 font-light mt-1 flex items-center gap-2">
                            <UserIcon class="w-4 h-4" /> {{ photographer.user.name }}
                        </p>
                    </div>

                    <div class="flex items-center gap-3">
                        <div :class="['px-3 py-1 rounded-sm border text-xs font-bold uppercase tracking-widest flex items-center gap-2', getStatusConfig(photographer.status).class]">
                            <span :class="['w-2 h-2 rounded-full', getStatusConfig(photographer.status).dot]"></span>
                            {{ getStatusConfig(photographer.status).text }}
                        </div>

                        <template v-if="photographer.status === 'rejected'">
                            <button @click="showRevertModal = true" class="bg-amber-500 text-white px-4 py-2 rounded-sm text-xs font-bold uppercase tracking-widest hover:bg-amber-600 transition shadow-sm flex items-center gap-2">
                                <ArrowPathIcon class="w-4 h-4" /> Revertir
                            </button>
                        </template>

                        <template v-else-if="photographer.status === 'approved'">
                            <button @click="showSuspendModal = true" class="border border-slate-300 text-slate-600 px-4 py-2 rounded-sm text-xs font-bold uppercase tracking-widest hover:border-slate-900 hover:text-slate-900 transition flex items-center gap-2">
                                <NoSymbolIcon class="w-4 h-4" /> Suspender
                            </button>
                        </template>

                        <template v-else-if="photographer.status === 'suspended'">
                            <button @click="showActivateModal = true" class="bg-emerald-600 text-white px-4 py-2 rounded-sm text-xs font-bold uppercase tracking-widest hover:bg-emerald-700 transition shadow-sm flex items-center gap-2">
                                <CheckCircleIcon class="w-4 h-4" /> Reactivar
                            </button>
                        </template>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    
                    <div class="lg:col-span-2 space-y-8">
                        
                        <div class="bg-white border border-gray-200 rounded-sm p-8 shadow-sm">
                            <h3 class="text-xs font-bold uppercase tracking-widest text-slate-900 mb-6 border-b border-gray-100 pb-2">Información de Contacto</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-[10px] font-bold uppercase tracking-wide text-slate-400 mb-1">Email</label>
                                    <p class="text-sm font-medium text-slate-900 flex items-center gap-2">
                                        <EnvelopeIcon class="w-4 h-4 text-slate-400" /> {{ photographer.user.email }}
                                    </p>
                                </div>
                                <div>
                                    <label class="block text-[10px] font-bold uppercase tracking-wide text-slate-400 mb-1">Teléfono</label>
                                    <p class="text-sm font-medium text-slate-900 flex items-center gap-2">
                                        <PhoneIcon class="w-4 h-4 text-slate-400" /> {{ photographer.phone || 'N/A' }}
                                    </p>
                                </div>
                                <div>
                                    <label class="block text-[10px] font-bold uppercase tracking-wide text-slate-400 mb-1">Región</label>
                                    <p class="text-sm font-medium text-slate-900 flex items-center gap-2">
                                        <MapPinIcon class="w-4 h-4 text-slate-400" /> {{ photographer.region || 'N/A' }}
                                    </p>
                                </div>
                                <div>
                                    <label class="block text-[10px] font-bold uppercase tracking-wide text-slate-400 mb-1">Registro</label>
                                    <p class="text-sm font-medium text-slate-900 font-mono text-xs">
                                        {{ formatDate(photographer.created_at) }}
                                    </p>
                                </div>
                            </div>

                            <div v-if="photographer.bio" class="mt-6 pt-6 border-t border-gray-100">
                                <label class="block text-[10px] font-bold uppercase tracking-wide text-slate-400 mb-2">Biografía</label>
                                <p class="text-sm text-slate-600 font-light leading-relaxed">{{ photographer.bio }}</p>
                            </div>
                        </div>

                        <div v-if="photographer.rejection_reason || photographer.suspension_reason" class="bg-red-50 border border-red-100 rounded-sm p-6">
                            <h3 class="text-xs font-bold uppercase tracking-widest text-red-800 mb-4 flex items-center gap-2">
                                <XCircleIcon class="w-4 h-4" /> Historial de Incidencias
                            </h3>
                            <div v-if="photographer.rejection_reason" class="mb-4">
                                <p class="text-[10px] font-bold uppercase tracking-wide text-red-400 mb-1">Motivo de Rechazo</p>
                                <p class="text-sm text-red-700 font-light">{{ photographer.rejection_reason }}</p>
                            </div>
                            <div v-if="photographer.suspension_reason">
                                <p class="text-[10px] font-bold uppercase tracking-wide text-red-400 mb-1">Motivo de Suspensión</p>
                                <p class="text-sm text-red-700 font-light">{{ photographer.suspension_reason }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="lg:col-span-1 space-y-6">
                        <div class="bg-white border border-gray-200 rounded-sm p-6 shadow-sm">
                            <h3 class="text-xs font-bold uppercase tracking-widest text-slate-900 mb-6 border-b border-gray-100 pb-2">Rendimiento</h3>
                            
                            <div class="space-y-6">
                                <div>
                                    <div class="flex justify-between items-center mb-1">
                                        <span class="text-xs text-slate-500 font-bold uppercase tracking-wide">Eventos</span>
                                        <CalendarIcon class="w-4 h-4 text-slate-400" />
                                    </div>
                                    <p class="text-3xl font-serif font-bold text-slate-900">{{ stats.events_count || 0 }}</p>
                                </div>

                                <div>
                                    <div class="flex justify-between items-center mb-1">
                                        <span class="text-xs text-slate-500 font-bold uppercase tracking-wide">Fotografías</span>
                                        <PhotoIcon class="w-4 h-4 text-slate-400" />
                                    </div>
                                    <p class="text-3xl font-serif font-bold text-slate-900">{{ stats.photos_count || 0 }}</p>
                                </div>

                                <div class="pt-4 border-t border-gray-100">
                                    <div class="flex justify-between items-center mb-1">
                                        <span class="text-xs text-slate-500 font-bold uppercase tracking-wide">Descargas</span>
                                        <ArrowDownTrayIcon class="w-4 h-4 text-slate-400" />
                                    </div>
                                    <p class="text-3xl font-serif font-bold text-slate-900">{{ stats.downloads_count || 0 }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <Teleport to="body">
            <div v-if="showSuspendModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/80 backdrop-blur-sm" @click.self="showSuspendModal = false">
                <div class="bg-white rounded-sm shadow-xl max-w-md w-full overflow-hidden">
                    <div class="p-6">
                        <h3 class="text-lg font-serif font-bold text-slate-900 mb-2">Confirmar Suspensión</h3>
                        <p class="text-sm text-slate-500 font-light mb-6">
                            Se revocará el acceso a <strong>{{ photographer.business_name }}</strong>. Esta acción es reversible.
                        </p>
                        
                        <label class="block text-[10px] font-bold uppercase tracking-wide text-slate-400 mb-2">Motivo (Opcional)</label>
                        <textarea v-model="suspendForm.reason" rows="3" class="w-full border-gray-300 rounded-sm text-sm focus:border-slate-900 focus:ring-0 resize-none mb-6" placeholder="Explique la razón..."></textarea>
                        
                        <div class="flex justify-end gap-3">
                            <button @click="showSuspendModal = false" class="px-4 py-2 border border-gray-300 rounded-sm text-xs font-bold uppercase tracking-widest text-slate-600 hover:bg-gray-50">Cancelar</button>
                            <button @click="confirmSuspend" :disabled="suspendForm.processing" class="px-4 py-2 bg-slate-900 rounded-sm text-xs font-bold uppercase tracking-widest text-white hover:bg-slate-800 disabled:opacity-50">Confirmar</button>
                        </div>
                    </div>
                </div>
            </div>

            <div v-if="showActivateModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/80 backdrop-blur-sm" @click.self="showActivateModal = false">
                <div class="bg-white rounded-sm shadow-xl max-w-md w-full overflow-hidden">
                    <div class="p-6">
                        <h3 class="text-lg font-serif font-bold text-slate-900 mb-2">Reactivar Cuenta</h3>
                        <p class="text-sm text-slate-500 font-light mb-6">
                            El fotógrafo <strong>{{ photographer.business_name }}</strong> recuperará el acceso inmediato a su panel.
                        </p>
                        <div class="flex justify-end gap-3">
                            <button @click="showActivateModal = false" class="px-4 py-2 border border-gray-300 rounded-sm text-xs font-bold uppercase tracking-widest text-slate-600 hover:bg-gray-50">Cancelar</button>
                            <button @click="confirmActivate" class="px-4 py-2 bg-emerald-600 rounded-sm text-xs font-bold uppercase tracking-widest text-white hover:bg-emerald-700">Reactivar</button>
                        </div>
                    </div>
                </div>
            </div>

            <div v-if="showRevertModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/80 backdrop-blur-sm" @click.self="showRevertModal = false">
                <div class="bg-white rounded-sm shadow-xl max-w-md w-full overflow-hidden">
                    <div class="p-6">
                        <h3 class="text-lg font-serif font-bold text-slate-900 mb-2">Revertir Rechazo</h3>
                        <p class="text-sm text-slate-500 font-light mb-6">
                            El estado volverá a <strong>Pendiente</strong> para una nueva revisión.
                        </p>
                        <div class="flex justify-end gap-3">
                            <button @click="showRevertModal = false" class="px-4 py-2 border border-gray-300 rounded-sm text-xs font-bold uppercase tracking-widest text-slate-600 hover:bg-gray-50">Cancelar</button>
                            <button @click="confirmRevert" class="px-4 py-2 bg-amber-500 rounded-sm text-xs font-bold uppercase tracking-widest text-white hover:bg-amber-600">Revertir</button>
                        </div>
                    </div>
                </div>
            </div>
        </Teleport>
    </AuthenticatedLayout>
</template>