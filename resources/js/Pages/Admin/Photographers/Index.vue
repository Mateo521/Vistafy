<script setup>
import { ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import ConfirmDialog from '@/Components/ConfirmDialog.vue'; //  Importar
import {
    MagnifyingGlassIcon,
    FunnelIcon,
    CheckCircleIcon,
    XCircleIcon,
    NoSymbolIcon,
    ArrowPathIcon
} from '@heroicons/vue/24/outline';

const props = defineProps({
    photographers: {
        type: Object,
        required: true,
    },
    stats: {
        type: Object,
        required: true,
    },
    filters: {
        type: Object,
        default: () => ({ status: 'all', search: '' }),
    },
});

// Estado para modales
const showRejectModal = ref(false);
const showSuspendModal = ref(false);
const selectedPhotographer = ref(null);

//  Estado para ConfirmDialog
const showConfirmDialog = ref(false);
const confirmDialogData = ref({
    title: '',
    message: '',
    confirmText: 'Confirmar',
    cancelText: 'Cancelar',
    type: 'warning',
    onConfirm: () => { },
});

// Formularios
const rejectForm = useForm({ reason: '' });
const suspendForm = useForm({ reason: '' });

// Búsqueda y filtros
const searchForm = useForm({
    search: props.filters.search,
    status: props.filters.status,
});

const handleSearch = () => {
    searchForm.get(route('admin.photographers.index'), {
        preserveState: true,
        preserveScroll: true,
    });
};

const filterByStatus = (status) => {
    searchForm.status = status;
    handleSearch();
};

//  Acciones con ConfirmDialog
const approvePhotographer = (photographer) => {
    confirmDialogData.value = {
        title: 'Aprobar Fotógrafo',
        message: `¿Confirmar la aprobación de <strong>${photographer.business_name}</strong>?<br><br>El fotógrafo podrá crear eventos y gestionar sus galerías.`,
        confirmText: 'Aprobar',
        cancelText: 'Cancelar',
        type: 'success',
        onConfirm: () => {
            router.post(route('admin.photographers.approve', photographer.id), {}, {
                preserveScroll: true
            });
        },
    };
    showConfirmDialog.value = true;
};

const openRejectModal = (photographer) => {
    selectedPhotographer.value = photographer;
    rejectForm.reason = '';
    showRejectModal.value = true;
};

const rejectPhotographer = () => {
    rejectForm.post(route('admin.photographers.reject', selectedPhotographer.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            showRejectModal.value = false;
            rejectForm.reset();
        },
    });
};

const openSuspendModal = (photographer) => {
    selectedPhotographer.value = photographer;
    suspendForm.reason = '';
    showSuspendModal.value = true;
};

const suspendPhotographer = () => {
    suspendForm.post(route('admin.photographers.suspend', selectedPhotographer.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            showSuspendModal.value = false;
            suspendForm.reset();
        },
    });
};

const reactivatePhotographer = (photographer) => {
    confirmDialogData.value = {
        title: 'Reactivar Fotógrafo',
        message: `¿Restaurar el acceso a <strong>${photographer.business_name}</strong>?<br><br>El fotógrafo volverá a tener acceso completo a la plataforma.`,
        confirmText: 'Reactivar',
        cancelText: 'Cancelar',
        type: 'info',
        onConfirm: () => {
            router.post(route('admin.photographers.reactivate', photographer.id), {}, {
                preserveScroll: true
            });
        },
    };
    showConfirmDialog.value = true;
};

// Helpers de diseño
const getStatusConfig = (status) => {
    const configs = {
        pending: { dot: 'bg-amber-400', text: 'Pendiente', class: 'text-amber-700' },
        approved: { dot: 'bg-emerald-500', text: 'Activo', class: 'text-emerald-700' },
        rejected: { dot: 'bg-red-500', text: 'Rechazado', class: 'text-red-700' },
        suspended: { dot: 'bg-gray-400', text: 'Suspendido', class: 'text-gray-600' },
    };
    return configs[status] || { dot: 'bg-gray-300', text: status, class: 'text-gray-500' };
};
</script>

<template>
    <AuthenticatedLayout>

        <Head title="Gestión de Profesionales" />

        <div class="min-h-screen bg-black py-12 text-white selection:bg-red-600 selection:text-black">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

                <div class="flex flex-col md:flex-row md:items-end justify-between mb-12 border-b-2 border-zinc-800 pb-8 relative">
                    <div class="absolute top-0 right-0 w-16 h-16 border-t-4 border-r-4 border-red-600 opacity-20 pointer-events-none"></div>
                    
                    <div>
                        <span class="font-mono text-xs font-bold text-red-600 uppercase tracking-widest mb-4 block animate-pulse">
                            [SYS_ADMIN] Administración
                        </span>
                        <h1 class="text-5xl md:text-6xl font-black uppercase tracking-tighter text-white leading-none">
                            Gestión de<br>Fotógrafos.
                        </h1>
                    </div>
                    <Link :href="route('admin.dashboard')"
                        class="mt-6 md:mt-0 font-mono text-[10px] font-bold uppercase tracking-widest text-zinc-500 hover:text-white border-b border-zinc-800 hover:border-white transition-colors pb-1">
                        ← Volver al Panel
                    </Link>
                </div>

                <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-12 relative">
                    <button v-for="(stat, key) in {
                        'all': { label: 'Total', count: stats.total },
                        'pending': { label: 'Pendientes', count: stats.pending },
                        'approved': { label: 'Activos', count: stats.approved },
                        'rejected': { label: 'Rechazados', count: stats.rejected },
                        'suspended': { label: 'Suspendidos', count: stats.suspended }
                    }" :key="key" @click="filterByStatus(key)" :class="[
                        'p-4 text-left border transition-all duration-300 rounded-none flex flex-col justify-between h-28 group relative overflow-hidden',
                        searchForm.status === key
                            ? 'bg-red-600 border-red-600 text-black shadow-[4px_4px_0px_rgba(255,255,255,1)]'
                            : 'bg-zinc-950 border-zinc-800 text-zinc-500 hover:border-white hover:text-white'
                    ]">
                        <div v-if="searchForm.status !== key" class="absolute top-2 left-2 w-2 h-2 border-t border-l border-zinc-700 opacity-0 group-hover:opacity-100 transition-opacity"></div>
                        <div v-if="searchForm.status !== key" class="absolute bottom-2 right-2 w-2 h-2 border-b border-r border-zinc-700 opacity-0 group-hover:opacity-100 transition-opacity"></div>

                        <span class="font-mono text-[10px] font-bold uppercase tracking-widest relative z-10">{{ stat.label }}</span>
                        <span :class="['text-4xl font-black tracking-tighter relative z-10', searchForm.status === key ? 'text-black' : 'text-white']">
                            {{ stat.count }}
                        </span>
                    </button>
                </div>

                <div class="flex flex-col md:flex-row gap-4 mb-8">
                    <div class="relative flex-1 group">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <MagnifyingGlassIcon class="h-5 w-5 text-zinc-600 group-focus-within:text-red-600 transition-colors" />
                        </div>
                        <input v-model="searchForm.search" @keyup.enter="handleSearch" type="text"
                            placeholder="BUSCAR POR NOMBRE, EMAIL O ID..."
                            class="block w-full pl-12 pr-4 py-4 bg-black border-2 border-zinc-800 rounded-none text-white font-mono text-sm uppercase placeholder-zinc-700 focus:outline-none focus:border-red-600 focus:ring-0 transition-colors">
                    </div>
                    <button @click="handleSearch"
                        class="bg-red-600 text-black px-10 py-4 font-black uppercase tracking-widest text-sm hover:bg-white hover:text-black transition-colors border-2 border-red-600 hover:border-white">
                        Filtrar
                    </button>
                </div>

                <div class="bg-zinc-950 border border-zinc-800 relative">
                    <div class="absolute top-0 left-0 w-4 h-4 border-t-2 border-l-2 border-zinc-600 pointer-events-none"></div>
                    <div class="absolute top-0 right-0 w-4 h-4 border-t-2 border-r-2 border-zinc-600 pointer-events-none"></div>
                    <div class="absolute bottom-0 left-0 w-4 h-4 border-b-2 border-l-2 border-zinc-600 pointer-events-none"></div>
                    <div class="absolute bottom-0 right-0 w-4 h-4 border-b-2 border-r-2 border-zinc-600 pointer-events-none"></div>

                    <div class="overflow-x-auto p-1">
                        <table class="min-w-full text-left border-collapse">
                            <thead class="bg-black border-b-2 border-zinc-800">
                                <tr>
                                    <th scope="col" class="px-6 py-4 font-mono text-[10px] font-bold text-zinc-500 uppercase tracking-widest">Profesional</th>
                                    <th scope="col" class="px-6 py-4 font-mono text-[10px] font-bold text-zinc-500 uppercase tracking-widest">Región</th>
                                    <th scope="col" class="px-6 py-4 font-mono text-[10px] font-bold text-zinc-500 uppercase tracking-widest">Estado</th>
                                    <th scope="col" class="px-6 py-4 text-center font-mono text-[10px] font-bold text-zinc-500 uppercase tracking-widest">Eventos</th>
                                    <th scope="col" class="px-6 py-4 text-center font-mono text-[10px] font-bold text-zinc-500 uppercase tracking-widest">Fotos</th>
                                    <th scope="col" class="px-6 py-4 text-right font-mono text-[10px] font-bold text-zinc-500 uppercase tracking-widest">Gestión</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-zinc-900 bg-zinc-950">
                                <tr v-if="photographers.data.length === 0">
                                    <td colspan="6" class="px-6 py-20 text-center">
                                        <p class="font-mono text-sm text-zinc-600 uppercase tracking-widest">No se encontraron registros // ARCHIVO VACÍO</p>
                                    </td>
                                </tr>

                                <tr v-for="photographer in photographers.data" :key="photographer.id"
                                    class="hover:bg-black transition-colors group">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center gap-4">
                                            <div class="flex-shrink-0 h-12 w-12 bg-black border border-zinc-800 rounded-none flex items-center justify-center text-white font-black text-xl group-hover:border-zinc-500 transition-colors">
                                                {{ photographer.business_name.charAt(0) }}
                                            </div>
                                            <div>
                                                <div class="text-sm font-black text-white uppercase tracking-tight">{{ photographer.business_name }}</div>
                                                <div class="text-[10px] text-zinc-500 font-mono mt-1">{{ photographer.user.email }}</div>
                                            </div>
                                        </div>
                                    </td>

                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="font-mono text-xs text-zinc-400 uppercase tracking-widest">{{ photographer.region }}</div>
                                    </td>

                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center gap-2">
                                            <span :class="['h-2 w-2 rounded-none', getStatusConfig(photographer.status).dot]"></span>
                                            <span class="font-mono text-[10px] font-bold uppercase tracking-widest text-zinc-300">
                                                {{ getStatusConfig(photographer.status).text }}
                                            </span>
                                        </div>
                                    </td>

                                    <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-black text-white">
                                        {{ photographer.events_count || 0 }}
                                    </td>
                                    
                                    <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-black text-white">
                                        {{ photographer.photos_count || 0 }}
                                    </td>

                                    <td class="px-6 py-4 whitespace-nowrap text-right">
                                        <div class="flex items-center justify-end gap-4 opacity-70 group-hover:opacity-100 transition-opacity">
                                            
                                            <template v-if="photographer.status === 'pending'">
                                                <button @click="approvePhotographer(photographer)" title="Aprobar" class="text-emerald-500 hover:text-emerald-400 transition-colors">
                                                    <CheckCircleIcon class="h-6 w-6" />
                                                </button>
                                                <button @click="openRejectModal(photographer)" title="Rechazar" class="text-red-600 hover:text-red-500 transition-colors">
                                                    <XCircleIcon class="h-6 w-6" />
                                                </button>
                                            </template>

                                            <template v-if="photographer.status === 'approved'">
                                                <button @click="openSuspendModal(photographer)" title="Suspender acceso" class="text-amber-500 hover:text-amber-400 transition-colors">
                                                    <NoSymbolIcon class="h-6 w-6" />
                                                </button>
                                            </template>

                                            <template v-if="photographer.status === 'suspended'">
                                                <button @click="reactivatePhotographer(photographer)" title="Reactivar" class="text-white hover:text-zinc-300 transition-colors">
                                                    <ArrowPathIcon class="h-6 w-6" />
                                                </button>
                                            </template>

                                            <Link :href="route('admin.photographers.show', photographer.id)"
                                                class="font-mono text-[10px] font-bold uppercase tracking-widest text-zinc-400 hover:text-white border border-zinc-700 hover:border-white px-3 py-1.5 transition-colors ml-2 bg-black">
                                                Visualizar
                                            </Link>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div v-if="photographers.data.length > 0" class="bg-black border-t border-zinc-800 px-6 py-4 flex flex-col md:flex-row items-center justify-between gap-4">
                        <span class="font-mono text-[10px] text-zinc-500 uppercase tracking-widest">
                            INDEX {{ photographers.from }} - {{ photographers.to }} // TOTAL {{ photographers.total }}
                        </span>
                        <div class="flex gap-1">
                            <Link v-for="(link, index) in photographers.links" :key="index" :href="link.url || '#'"
                                :class="[
                                    'px-3 py-1 font-mono text-[10px] font-bold transition-colors rounded-none border',
                                    link.active
                                        ? 'bg-red-600 text-black border-red-600'
                                        : (link.url ? 'bg-zinc-950 text-zinc-400 border-zinc-800 hover:bg-white hover:text-black hover:border-white' : 'bg-black text-zinc-800 border-zinc-900 cursor-not-allowed')
                                ]" v-html="link.label" :preserve-scroll="true" :preserve-state="true" />
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div v-if="showRejectModal" class="fixed inset-0 z-50 overflow-y-auto" @click.self="showRejectModal = false">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
                <div class="fixed inset-0 bg-black/90 transition-opacity backdrop-blur-sm" aria-hidden="true"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div class="inline-block align-bottom bg-zinc-950 border-4 border-red-600 text-left overflow-hidden transform transition-all sm:my-8 sm:align-middle w-full max-w-lg rounded-none shadow-[10px_10px_0px_rgba(220,38,38,0.2)]">
                    <div class="px-6 pt-8 pb-6">
                        <div class="sm:flex sm:items-start gap-6">
                            <div class="mx-auto flex-shrink-0 flex items-center justify-center h-16 w-16 border-2 border-red-600 bg-black sm:mx-0">
                                <XCircleIcon class="h-8 w-8 text-red-600" />
                            </div>
                            <div class="mt-4 text-center sm:mt-0 sm:text-left w-full">
                                <h3 class="text-3xl font-black uppercase text-white tracking-tighter leading-none mb-4">
                                    Rechazar<br>Solicitud
                                </h3>
                                <div class="mt-2">
                                    <p class="font-mono text-xs text-zinc-400 mb-6 border-l-2 border-red-600 pl-3">
                                        TARGET: <strong class="text-white">{{ selectedPhotographer?.business_name }}</strong><br>
                                        STATUS: Se notificará al usuario.
                                    </p>
                                    <div class="mt-4">
                                        <label class="block font-mono text-[10px] font-bold uppercase text-red-600 mb-2 tracking-widest">Motivo del rechazo</label>
                                        <textarea v-model="rejectForm.reason" rows="4"
                                            class="w-full bg-black border-2 border-zinc-800 text-white font-mono text-xs p-3 focus:border-red-600 focus:ring-0 resize-none transition-colors rounded-none placeholder-zinc-700"
                                            placeholder="INGRESE RAZÓN TÉCNICA O ADMINISTRATIVA..."></textarea>
                                        <p v-if="rejectForm.errors.reason" class="font-mono text-[10px] text-red-600 mt-2 uppercase">{{ rejectForm.errors.reason }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-black border-t-2 border-zinc-900 px-6 py-4 flex flex-col-reverse sm:flex-row sm:justify-end gap-3">
                        <button @click="showRejectModal = false"
                            class="w-full inline-flex justify-center border-2 border-zinc-700 px-6 py-3 bg-transparent text-xs font-black uppercase tracking-widest text-zinc-400 hover:bg-white hover:text-black hover:border-white transition-colors sm:w-auto rounded-none">
                            Abortar
                        </button>
                        <button @click="rejectPhotographer" :disabled="rejectForm.processing"
                            class="w-full inline-flex justify-center border-2 border-red-600 px-6 py-3 bg-red-600 text-xs font-black uppercase tracking-widest text-black hover:bg-white hover:border-white transition-colors sm:w-auto rounded-none disabled:opacity-50">
                            Confirmar Rechazo
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div v-if="showSuspendModal" class="fixed inset-0 z-50 overflow-y-auto" @click.self="showSuspendModal = false">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
                <div class="fixed inset-0 bg-black/90 transition-opacity backdrop-blur-sm" aria-hidden="true"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div class="inline-block align-bottom bg-zinc-950 border-4 border-amber-500 text-left overflow-hidden transform transition-all sm:my-8 sm:align-middle w-full max-w-lg rounded-none shadow-[10px_10px_0px_rgba(245,158,11,0.2)]">
                    <div class="px-6 pt-8 pb-6">
                        <div class="sm:flex sm:items-start gap-6">
                            <div class="mx-auto flex-shrink-0 flex items-center justify-center h-16 w-16 border-2 border-amber-500 bg-black sm:mx-0">
                                <NoSymbolIcon class="h-8 w-8 text-amber-500" />
                            </div>
                            <div class="mt-4 text-center sm:mt-0 sm:text-left w-full">
                                <h3 class="text-3xl font-black uppercase text-white tracking-tighter leading-none mb-4">
                                    Suspender<br>Cuenta
                                </h3>
                                <div class="mt-2">
                                    <p class="font-mono text-xs text-zinc-400 mb-6 border-l-2 border-amber-500 pl-3">
                                        TARGET: <strong class="text-white">{{ selectedPhotographer?.business_name }}</strong><br>
                                        STATUS: Galerías offline. Acceso revocado.
                                    </p>
                                    <div class="mt-4">
                                        <label class="block font-mono text-[10px] font-bold uppercase text-amber-500 mb-2 tracking-widest">Motivo (Opcional)</label>
                                        <textarea v-model="suspendForm.reason" rows="4"
                                            class="w-full bg-black border-2 border-zinc-800 text-white font-mono text-xs p-3 focus:border-amber-500 focus:ring-0 resize-none transition-colors rounded-none placeholder-zinc-700"
                                            placeholder="REGISTRO INTERNO / RAZÓN DE SUSPENSIÓN..."></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-black border-t-2 border-zinc-900 px-6 py-4 flex flex-col-reverse sm:flex-row sm:justify-end gap-3">
                        <button @click="showSuspendModal = false"
                            class="w-full inline-flex justify-center border-2 border-zinc-700 px-6 py-3 bg-transparent text-xs font-black uppercase tracking-widest text-zinc-400 hover:bg-white hover:text-black hover:border-white transition-colors sm:w-auto rounded-none">
                            Abortar
                        </button>
                        <button @click="suspendPhotographer" :disabled="suspendForm.processing"
                            class="w-full inline-flex justify-center border-2 border-amber-500 px-6 py-3 bg-amber-500 text-xs font-black uppercase tracking-widest text-black hover:bg-white hover:border-white transition-colors sm:w-auto rounded-none disabled:opacity-50">
                            Ejecutar Suspensión
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <ConfirmDialog :show="showConfirmDialog" :title="confirmDialogData.title" :message="confirmDialogData.message"
            :confirm-text="confirmDialogData.confirmText" :cancel-text="confirmDialogData.cancelText"
            :type="confirmDialogData.type" @confirm="confirmDialogData.onConfirm" @cancel="showConfirmDialog = false" />
    </AuthenticatedLayout>
</template>