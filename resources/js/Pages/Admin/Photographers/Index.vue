<script setup>
import { ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import ConfirmDialog from '@/Components/ConfirmDialog.vue'; //  Importar
import {
     ArrowLeftIcon,
    MagnifyingGlassIcon,
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


const showRejectModal = ref(false);
const showSuspendModal = ref(false);
const selectedPhotographer = ref(null);


const showConfirmDialog = ref(false);
const confirmDialogData = ref({
    title: '',
    message: '',
    confirmText: 'Confirmar',
    cancelText: 'Cancelar',
    type: 'warning',
    onConfirm: () => { },
});


const rejectForm = useForm({ reason: '' });
const suspendForm = useForm({ reason: '' });


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
    <Head title="Gestión admin" />

    <AuthenticatedLayout>
        <div class="min-h-screen bg-[#F8F9FA] text-slate-800 font-sans antialiased py-12 pt-28">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">


                <div class="mb-10 flex flex-col md:flex-row md:items-end justify-between gap-6 border-b border-gray-200 pb-8">
                    <div>
                        <span class="inline-flex items-center gap-2 bg-red-50 text-[#E30613] px-3 py-1.5 rounded-full text-[10px] font-bold uppercase tracking-widest mb-3 border border-red-100">
                            <span class="w-2 h-2 rounded-full bg-[#E30613] animate-pulse"></span>
                            Administración
                        </span>
                        <h1 class="text-4xl md:text-6xl font-flux text-black tracking-wide leading-none">
                            Gestión de <span class="text-[#E30613]">fotógrafos</span>
                        </h1>
                    </div>
                    <Link :href="route('admin.dashboard')"
                        class="inline-flex items-center gap-2 bg-white px-5 py-2.5 rounded-full text-xs font-bold uppercase tracking-wider text-gray-500 hover:text-black hover:shadow-sm border border-gray-200 transition-all w-max">
                        <ArrowLeftIcon class="w-4 h-4" /> Volver al panel
                    </Link>
                </div>

                
                <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-8">
                    <button v-for="(stat, key) in {
                        'all': { label: 'Total', count: stats.total },
                        'pending': { label: 'Pendientes', count: stats.pending },
                        'approved': { label: 'Activos', count: stats.approved },
                        'rejected': { label: 'Rechazados', count: stats.rejected },
                        'suspended': { label: 'Suspendidos', count: stats.suspended }
                    }" :key="key" @click="filterByStatus(key)" 
                    :class="[
                        'p-5 md:p-6 rounded border transition-all duration-300 flex flex-col justify-between h-28 md:h-32 text-left relative overflow-hidden group',
                        searchForm.status === key
                            ? 'bg-red-50 border-red-200 shadow-sm'
                            : 'bg-white border-gray-100 hover:border-gray-300 hover:shadow-sm'
                    ]">
                        <span :class="['text-[10px] md:text-xs font-bold uppercase tracking-wider', searchForm.status === key ? 'text-[#E30613]' : 'text-gray-400 group-hover:text-gray-600']">
                            {{ stat.label }}
                        </span>
                        <span :class="['text-3xl md:text-4xl font-flux', searchForm.status === key ? 'text-[#E30613]' : 'text-black']">
                            {{ stat.count }}
                        </span>
                    </button>
                </div>

                
                <div class="flex flex-col md:flex-row gap-4 mb-8">
                    <div class="relative flex-1 group">
                        <MagnifyingGlassIcon class="absolute left-5 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400 group-focus-within:text-[#E30613] transition-colors" />
                        <input v-model="searchForm.search" @keyup.enter="handleSearch" type="text"
                            placeholder="Buscar por nombre, email o ID..."
                            class="w-full bg-white border border-transparent text-slate-800 font-medium text-sm py-4 pl-14 pr-4 rounded-full transition-all outline-none focus:border-gray-300 focus:ring-4 focus:ring-gray-100 shadow-sm placeholder-gray-400">
                    </div>
                    <button @click="handleSearch"
                        class="bg-black text-white px-8 py-4 rounded-full text-xs font-bold uppercase tracking-wider hover:bg-[#E30613] hover:shadow-lg hover:shadow-red-500/30 transition-all shadow-sm shrink-0">
                        Filtrar resultados
                    </button>
                </div>

                
                <div class="bg-white rounded- shadow-sm border border-gray-100 overflow-hidden mb-12">
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-left border-collapse">
                            <thead class="bg-gray-50/80 border-b border-gray-100">
                                <tr>
                                    <th scope="col" class="px-6 py-4 text-[10px] font-bold text-gray-400 uppercase tracking-widest">Profesional</th>
                                    <th scope="col" class="px-6 py-4 text-[10px] font-bold text-gray-400 uppercase tracking-widest">Región</th>
                                    <th scope="col" class="px-6 py-4 text-[10px] font-bold text-gray-400 uppercase tracking-widest">Estado</th>
                                    <th scope="col" class="px-6 py-4 text-center text-[10px] font-bold text-gray-400 uppercase tracking-widest">Eventos</th>
                                    <th scope="col" class="px-6 py-4 text-center text-[10px] font-bold text-gray-400 uppercase tracking-widest">Fotos</th>
                                    <th scope="col" class="px-6 py-4 text-right text-[10px] font-bold text-gray-400 uppercase tracking-widest">Gestión</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                
                                <tr v-if="photographers.data.length === 0">
                                    <td colspan="6" class="px-6 py-20 text-center">
                                        <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-4">
                                            <MagnifyingGlassIcon class="w-8 h-8 text-gray-300" />
                                        </div>
                                        <p class="text-sm font-medium text-gray-500">No se encontraron fotógrafos que coincidan con la búsqueda.</p>
                                    </td>
                                </tr>

                               
                                <tr v-for="photographer in photographers.data" :key="photographer.id"
                                    class="hover:bg-gray-50/50 transition-colors group">
                                    
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center gap-4">
                                            <div class="flex-shrink-0 h-10 w-10 bg-gray-100 text-gray-500 font-bold rounded-full flex items-center justify-center text-lg">
                                                {{ photographer.business_name.charAt(0) }}
                                            </div>
                                            <div>
                                                <div class="text-sm font-bold text-slate-800">{{ photographer.business_name }}</div>
                                                <div class="text-[10px] text-gray-400 font-medium mt-0.5">{{ photographer.user.email }}</div>
                                            </div>
                                        </div>
                                    </td>

                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-xs font-bold text-gray-500 uppercase tracking-wider">{{ photographer.region }}</div>
                                    </td>

                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center gap-2">
                                            <span :class="['h-2 w-2 rounded-full', getStatusConfig(photographer.status).dot]"></span>
                                            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-600">
                                                {{ getStatusConfig(photographer.status).text }}
                                            </span>
                                        </div>
                                    </td>

                                    <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-bold text-slate-700">
                                        {{ photographer.events_count || 0 }}
                                    </td>
                                    
                                    <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-bold text-slate-700">
                                        {{ photographer.photos_count || 0 }}
                                    </td>

                                    <td class="px-6 py-4 whitespace-nowrap text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            
                                            <template v-if="photographer.status === 'pending'">
                                                <button @click="approvePhotographer(photographer)" title="Aprobar" class="p-2 text-green-500 hover:bg-green-50 rounded-full transition-colors">
                                                    <CheckCircleIcon class="h-5 w-5" />
                                                </button>
                                                <button @click="openRejectModal(photographer)" title="Rechazar" class="p-2 text-red-500 hover:bg-red-50 rounded-full transition-colors">
                                                    <XCircleIcon class="h-5 w-5" />
                                                </button>
                                            </template>

                                            <template v-if="photographer.status === 'approved'">
                                                <button @click="openSuspendModal(photographer)" title="Suspender acceso" class="p-2 text-orange-500 hover:bg-orange-50 rounded-full transition-colors">
                                                    <NoSymbolIcon class="h-5 w-5" />
                                                </button>
                                            </template>

                                            <template v-if="photographer.status === 'suspended'">
                                                <button @click="reactivatePhotographer(photographer)" title="Reactivar" class="p-2 text-blue-500 hover:bg-blue-50 rounded-full transition-colors">
                                                    <ArrowPathIcon class="h-5 w-5" />
                                                </button>
                                            </template>

                                            <Link :href="route('admin.photographers.show', photographer.id)"
                                                class="ml-2 bg-white border border-gray-200 text-gray-600 hover:text-black hover:border-black hover:shadow-sm px-4 py-1.5 rounded-full text-[10px] font-bold uppercase tracking-wider transition-all">
                                                Visualizar
                                            </Link>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    
                    <div v-if="photographers.data.length > 0" class="bg-white border-t border-gray-100 px-6 py-4 flex flex-col md:flex-row items-center justify-between gap-4">
                        <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">
                            Mostrando {{ photographers.from }} a {{ photographers.to }} de {{ photographers.total }} registros
                        </span>
                        <div class="flex flex-wrap gap-1">
                            <Link v-for="(link, index) in photographers.links" :key="index" :href="link.url || '#'"
                                :class="[
                                    'min-w-[32px] h-8 flex items-center justify-center px-2 text-xs font-bold rounded-full transition-colors',
                                    link.active
                                        ? 'bg-black text-white'
                                        : (link.url ? 'bg-transparent text-gray-500 hover:bg-gray-100 hover:text-black' : 'bg-transparent text-gray-300 cursor-not-allowed')
                                ]" v-html="link.label" :preserve-scroll="true" :preserve-state="true" />
                        </div>
                    </div>
                </div>

            </div>
        </div>

    
        <div v-if="showRejectModal" class="fixed inset-0 z-[100] overflow-y-auto" @click.self="showRejectModal = false">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
                <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm transition-opacity" aria-hidden="true"></div>

                <div class="relative inline-block align-bottom bg-white rounded text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle w-full max-w-lg border border-gray-100">
                    <div class="px-6 sm:px-8 pt-8 pb-6">
                        <div class="sm:flex sm:items-start gap-6">
                            <div class="mx-auto flex-shrink-0 flex items-center justify-center h-16 w-16 rounded-full bg-red-50 sm:mx-0">
                                <XCircleIcon class="h-8 w-8 text-[#E30613]" />
                            </div>
                            <div class="mt-4 text-center sm:mt-0 sm:text-left w-full">
                                <h3 class="text-3xl font-flux text-black leading-none mb-2">Rechazar solicitud</h3>
                                <p class="text-sm text-gray-500 font-medium mb-6">Se notificará al usuario de esta decisión.</p>
                                
                                <div class="bg-gray-50 rounded p-4 mb-6">
                                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Profesional objetivo</p>
                                    <p class="text-sm font-bold text-slate-800">{{ selectedPhotographer?.business_name }}</p>
                                </div>

                                <div>
                                    <label class="block text-xs font-bold uppercase text-gray-500 mb-2 tracking-wider ml-1">Motivo del rechazo (Opcional)</label>
                                    <textarea v-model="rejectForm.reason" rows="3"
                                        class="w-full bg-white border border-gray-200 text-slate-800 text-sm p-4 rounded-xl focus:border-gray-300 focus:ring-4 focus:ring-gray-100 resize-none transition-all outline-none placeholder-gray-400"
                                        placeholder="Ingresa la razón técnica o administrativa..."></textarea>
                                    <p v-if="rejectForm.errors.reason" class="text-xs text-[#E30613] font-bold mt-2 ml-1">{{ rejectForm.errors.reason }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-6 py-4 sm:px-8 flex flex-col-reverse sm:flex-row sm:justify-end gap-3 border-t border-gray-100">
                        <button @click="showRejectModal = false"
                            class="w-full sm:w-auto px-6 py-3 bg-white border border-gray-200 text-gray-600 rounded-full text-xs font-bold uppercase tracking-wider hover:bg-gray-100 transition-colors shadow-sm">
                            Cancelar
                        </button>
                        <button @click="rejectPhotographer" :disabled="rejectForm.processing"
                            class="w-full sm:w-auto px-6 py-3 bg-[#E30613] text-white rounded-full text-xs font-bold uppercase tracking-wider hover:bg-red-700 hover:shadow-lg hover:shadow-red-500/30 transition-all disabled:opacity-50">
                            Confirmar Rechazo
                        </button>
                    </div>
                </div>
            </div>
        </div>


        <div v-if="showSuspendModal" class="fixed inset-0 z-[100] overflow-y-auto" @click.self="showSuspendModal = false">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
                <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm transition-opacity" aria-hidden="true"></div>

                <div class="relative inline-block align-bottom bg-white rounded text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle w-full max-w-lg border border-gray-100">
                    <div class="px-6 sm:px-8 pt-8 pb-6">
                        <div class="sm:flex sm:items-start gap-6">
                            <div class="mx-auto flex-shrink-0 flex items-center justify-center h-16 w-16 rounded-full bg-orange-50 sm:mx-0">
                                <NoSymbolIcon class="h-8 w-8 text-orange-500" />
                            </div>
                            <div class="mt-4 text-center sm:mt-0 sm:text-left w-full">
                                <h3 class="text-3xl font-flux text-black leading-none mb-2">Suspender cuenta</h3>
                                <p class="text-sm text-gray-500 font-medium mb-6">Las galerías quedarán offline y el acceso será revocado.</p>
                                
                                <div class="bg-gray-50 rounded p-4 mb-6">
                                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Profesional objetivo</p>
                                    <p class="text-sm font-bold text-slate-800">{{ selectedPhotographer?.business_name }}</p>
                                </div>

                                <div>
                                    <label class="block text-xs font-bold uppercase text-gray-500 mb-2 tracking-wider ml-1">Motivo (Opcional)</label>
                                    <textarea v-model="suspendForm.reason" rows="3"
                                        class="w-full bg-white border border-gray-200 text-slate-800 text-sm p-4 rounded-xl focus:border-gray-300 focus:ring-4 focus:ring-gray-100 resize-none transition-all outline-none placeholder-gray-400"
                                        placeholder="Registro interno / Razón de suspensión..."></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-6 py-4 sm:px-8 flex flex-col-reverse sm:flex-row sm:justify-end gap-3 border-t border-gray-100">
                        <button @click="showSuspendModal = false"
                            class="w-full sm:w-auto px-6 py-3 bg-white border border-gray-200 text-gray-600 rounded-full text-xs font-bold uppercase tracking-wider hover:bg-gray-100 transition-colors shadow-sm">
                            Cancelar
                        </button>
                        <button @click="suspendPhotographer" :disabled="suspendForm.processing"
                            class="w-full sm:w-auto px-6 py-3 bg-orange-500 text-white rounded-full text-xs font-bold uppercase tracking-wider hover:bg-orange-600 hover:shadow-lg hover:shadow-orange-500/30 transition-all disabled:opacity-50">
                            Suspender cuenta
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