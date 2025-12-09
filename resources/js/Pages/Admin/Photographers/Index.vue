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

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

                <div
                    class="flex flex-col md:flex-row md:items-end justify-between mb-10 border-b border-gray-200 pb-6 gap-4">
                    <div>
                        <span class="text-xs font-bold tracking-[0.2em] text-slate-400 uppercase mb-2 block">
                            Administración
                        </span>
                        <h1 class="text-3xl font-serif font-bold text-slate-900">
                            Gestión de Fotógrafos
                        </h1>
                    </div>
                    <Link :href="route('admin.dashboard')"
                        class="text-xs font-bold uppercase tracking-widest text-slate-500 hover:text-slate-900 border-b border-transparent hover:border-slate-900 transition pb-1">
                        ← Volver al Panel
                    </Link>
                </div>


                <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-10">
                    <button v-for="(stat, key) in {
                        'all': { label: 'Total', count: stats.total },
                        'pending': { label: 'Pendientes', count: stats.pending },
                        'approved': { label: 'Activos', count: stats.approved },
                        'rejected': { label: 'Rechazados', count: stats.rejected },
                        'suspended': { label: 'Suspendidos', count: stats.suspended }
                    }" :key="key" @click="filterByStatus(key)" :class="[
                        'p-4 text-left border transition-all duration-200 rounded-sm flex flex-col justify-between h-24 group',
                        searchForm.status === key
                            ? 'bg-slate-900 border-slate-900 text-white shadow-md'
                            : 'bg-white border-gray-200 text-slate-500 hover:border-slate-400 hover:text-slate-900'
                    ]">
                        <span class="text-[10px] font-bold uppercase tracking-widest opacity-80">{{ stat.label }}</span>
                        <span
                            :class="['text-3xl font-serif font-medium', searchForm.status === key ? 'text-white' : 'text-slate-900']">
                            {{ stat.count }}
                        </span>
                    </button>
                </div>

                <div class="flex flex-col md:flex-row gap-4 mb-6">
                    <div class="relative flex-1">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <MagnifyingGlassIcon class="h-5 w-5 text-gray-400" />
                        </div>
                        <input v-model="searchForm.search" @keyup.enter="handleSearch" type="text"
                            placeholder="Buscar por nombre, email o ID..."
                            class="block w-full pl-10 pr-3 py-3 border-gray-300 rounded-sm leading-5 bg-white placeholder-gray-400 focus:outline-none focus:placeholder-gray-500 focus:border-slate-900 focus:ring-0 sm:text-sm transition-colors">
                    </div>
                    <button @click="handleSearch"
                        class="bg-slate-900 text-white px-8 py-3 rounded-sm text-xs font-bold uppercase tracking-widest hover:bg-slate-800 transition">
                        Filtrar
                    </button>
                </div>

                <div class="bg-white border border-gray-200 shadow-sm rounded-sm overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-100">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col"
                                        class="px-6 py-4 text-left text-[10px] font-bold text-slate-500 uppercase tracking-widest">
                                        Profesional</th>
                                    <th scope="col"
                                        class="px-6 py-4 text-left text-[10px] font-bold text-slate-500 uppercase tracking-widest">
                                        Región</th>
                                    <th scope="col"
                                        class="px-6 py-4 text-left text-[10px] font-bold text-slate-500 uppercase tracking-widest">
                                        Estado</th>
                                    <th scope="col"
                                        class="px-6 py-4 text-center text-[10px] font-bold text-slate-500 uppercase tracking-widest">
                                        Eventos</th>
                                    <th scope="col"
                                        class="px-6 py-4 text-center text-[10px] font-bold text-slate-500 uppercase tracking-widest">
                                        Fotos</th>
                                    <th scope="col"
                                        class="px-6 py-4 text-right text-[10px] font-bold text-slate-500 uppercase tracking-widest">
                                        Gestión</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-100">
                                <tr v-if="photographers.data.length === 0">
                                    <td colspan="6" class="px-6 py-12 text-center text-slate-500">
                                        <p class="font-serif italic">No se encontraron registros que coincidan con la
                                            búsqueda.</p>
                                    </td>
                                </tr>

                                <tr v-for="photographer in photographers.data" :key="photographer.id"
                                    class="hover:bg-gray-50/80 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div
                                                class="flex-shrink-0 h-10 w-10 bg-slate-100 rounded-sm flex items-center justify-center text-slate-500 font-serif font-bold text-lg border border-gray-200">
                                                {{ photographer.business_name.charAt(0) }}
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-bold text-slate-900 font-serif">{{
                                                    photographer.business_name }}</div>
                                                <div class="text-xs text-slate-500 font-mono">{{ photographer.user.email
                                                    }}</div>
                                            </div>
                                        </div>
                                    </td>

                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-xs text-slate-600 font-medium uppercase tracking-wide">{{
                                            photographer.region }}</div>
                                    </td>

                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <span
                                                :class="['h-2 w-2 rounded-full mr-2', getStatusConfig(photographer.status).dot]"></span>
                                            <span
                                                :class="['text-xs font-bold uppercase tracking-wider', getStatusConfig(photographer.status).class]">
                                                {{ getStatusConfig(photographer.status).text }}
                                            </span>
                                        </div>
                                    </td>

                                    <td
                                        class="px-6 py-4 whitespace-nowrap text-center text-sm font-mono text-slate-600">
                                        {{ photographer.events_count || 0 }}
                                    </td>
                                    <td
                                        class="px-6 py-4 whitespace-nowrap text-center text-sm font-mono text-slate-600">
                                        {{ photographer.photos_count || 0 }}
                                    </td>

                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <div class="flex items-center justify-end gap-3">

                                            <template v-if="photographer.status === 'pending'">
                                                <button @click="approvePhotographer(photographer)" title="Aprobar"
                                                    class="text-emerald-600 hover:text-emerald-900 transition">
                                                    <CheckCircleIcon class="h-5 w-5" />
                                                </button>
                                                <button @click="openRejectModal(photographer)" title="Rechazar"
                                                    class="text-red-600 hover:text-red-900 transition">
                                                    <XCircleIcon class="h-5 w-5" />
                                                </button>
                                            </template>

                                            <template v-if="photographer.status === 'approved'">
                                                <button @click="openSuspendModal(photographer)" title="Suspender acceso"
                                                    class="text-amber-500 hover:text-amber-700 transition">
                                                    <NoSymbolIcon class="h-5 w-5" />
                                                </button>
                                            </template>

                                            <template v-if="photographer.status === 'suspended'">
                                                <button @click="reactivatePhotographer(photographer)" title="Reactivar"
                                                    class="text-blue-600 hover:text-blue-900 transition">
                                                    <ArrowPathIcon class="h-5 w-5" />
                                                </button>
                                            </template>

                                            <Link :href="route('admin.photographers.show', photographer.id)"
                                                class="text-xs font-bold uppercase tracking-widest text-slate-400 hover:text-slate-900 border border-transparent hover:border-slate-900 px-2 py-1 transition ml-2">
                                                Ver
                                            </Link>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div v-if="photographers.data.length > 0"
                        class="bg-white border-t border-gray-200 px-6 py-4 flex items-center justify-between">
                        <span class="text-xs text-slate-400">
                            {{ photographers.from }} - {{ photographers.to }} de {{ photographers.total }} registros
                        </span>
                        <div class="flex gap-1">
                            <Link v-for="(link, index) in photographers.links" :key="index" :href="link.url || '#'"
                                :class="[
                                    'px-3 py-1 text-xs font-medium transition rounded-sm',
                                    link.active
                                        ? 'bg-slate-900 text-white'
                                        : (link.url ? 'bg-white text-slate-600 hover:bg-gray-100' : 'text-gray-300 cursor-not-allowed')
                                ]" v-html="link.label" :preserve-scroll="true" :preserve-state="true" />
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <div v-if="showRejectModal" class="fixed inset-0 z-50 overflow-y-auto" @click.self="showRejectModal = false">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 transition-opacity" aria-hidden="true">
                    <div class="absolute inset-0 bg-slate-900 opacity-75"></div>
                </div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div
                    class="inline-block align-bottom bg-white rounded-sm text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full border border-gray-200">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div
                                class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                                <XCircleIcon class="h-6 w-6 text-red-600" />
                            </div>
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                                <h3 class="text-lg leading-6 font-serif font-bold text-slate-900">
                                    Rechazar Solicitud
                                </h3>
                                <div class="mt-2">
                                    <p class="text-sm text-gray-500">
                                        Está a punto de rechazar a <strong>{{ selectedPhotographer?.business_name
                                            }}</strong>.
                                        Esta acción notificará al usuario.
                                    </p>
                                    <div class="mt-4">
                                        <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Motivo del
                                            rechazo</label>
                                        <textarea v-model="rejectForm.reason" rows="3"
                                            class="w-full border-gray-300 rounded-sm text-sm focus:border-slate-900 focus:ring-0 resize-none"></textarea>
                                        <p v-if="rejectForm.errors.reason" class="text-xs text-red-600 mt-1">{{
                                            rejectForm.errors.reason }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button @click="rejectPhotographer" :disabled="rejectForm.processing"
                            class="w-full inline-flex justify-center rounded-sm border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">
                            Confirmar Rechazo
                        </button>
                        <button @click="showRejectModal = false"
                            class="mt-3 w-full inline-flex justify-center rounded-sm border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Cancelar
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div v-if="showSuspendModal" class="fixed inset-0 z-50 overflow-y-auto" @click.self="showSuspendModal = false">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 transition-opacity" aria-hidden="true">
                    <div class="absolute inset-0 bg-slate-900 opacity-75"></div>
                </div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div
                    class="inline-block align-bottom bg-white rounded-sm text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full border border-gray-200">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div
                                class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-amber-100 sm:mx-0 sm:h-10 sm:w-10">
                                <NoSymbolIcon class="h-6 w-6 text-amber-600" />
                            </div>
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                                <h3 class="text-lg leading-6 font-serif font-bold text-slate-900">
                                    Suspender Cuenta
                                </h3>
                                <div class="mt-2">
                                    <p class="text-sm text-gray-500">
                                        Se revocará el acceso a <strong>{{ selectedPhotographer?.business_name
                                            }}</strong>. Sus
                                        galerías dejarán de ser visibles públicamente.
                                    </p>
                                    <div class="mt-4">
                                        <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Motivo
                                            (Opcional)</label>
                                        <textarea v-model="suspendForm.reason" rows="3"
                                            class="w-full border-gray-300 rounded-sm text-sm focus:border-slate-900 focus:ring-0 resize-none"></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button @click="suspendPhotographer" :disabled="suspendForm.processing"
                            class="w-full inline-flex justify-center rounded-sm border border-transparent shadow-sm px-4 py-2 bg-slate-900 text-base font-medium text-white hover:bg-slate-800 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">
                            Suspender
                        </button>
                        <button @click="showSuspendModal = false"
                            class="mt-3 w-full inline-flex justify-center rounded-sm border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Cancelar
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