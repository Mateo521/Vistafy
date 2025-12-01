<script setup>
import { ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';

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

// Formularios
const rejectForm = useForm({
    reason: '',
});

const suspendForm = useForm({
    reason: '',
});

// Búsqueda y filtros
const searchForm = useForm({
    search: props.filters.search,
    status: props.filters.status,
});

const search = () => {
    searchForm.get(route('admin.photographers.index'), {
        preserveState: true,
        preserveScroll: true,
    });
};

// Acciones
const approvePhotographer = (photographer) => {
    if (confirm(`¿Aprobar a ${photographer.business_name}?`)) {
        router.post(route('admin.photographers.approve', photographer.id), {}, {
            preserveScroll: true,
        });
    }
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
    if (confirm(`¿Reactivar a ${photographer.business_name}?`)) {
        router.post(route('admin.photographers.reactivate', photographer.id), {}, {
            preserveScroll: true,
        });
    }
};

// Helpers
const getStatusBadge = (status) => {
    const badges = {
        pending: { color: 'bg-yellow-100 text-yellow-800 border-yellow-300', text: ' Pendiente' },
        approved: { color: 'bg-green-100 text-green-800 border-green-300', text: ' Aprobado' },
        rejected: { color: 'bg-red-100 text-red-800 border-red-300', text: ' Rechazado' },
        suspended: { color: 'bg-orange-100 text-orange-800 border-orange-300', text: ' Suspendido' },
    };
    return badges[status] || { color: 'bg-gray-100 text-gray-800', text: status };
};
</script>

<template>
    <AuthenticatedLayout>

        <Head title="Gestión de Fotógrafos" />

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="flex items-center justify-between mb-8">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900"> Gestión de Fotógrafos</h1>
                        <p class="text-gray-600 mt-2">Administra las solicitudes y estados de los fotógrafos</p>
                    </div>
                    <Link :href="route('admin.dashboard')"
                        class="inline-flex items-center px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition">
                    ← Volver al Dashboard
                    </Link>
                </div>

                <!-- Estadísticas Rápidas -->
                <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-8">
                    <button @click="searchForm.status = 'all'; search()" :class="[
                        'p-4 rounded-lg border-2 transition',
                        filters.status === 'all'
                            ? 'bg-indigo-50 border-indigo-500'
                            : 'bg-white border-gray-200 hover:border-indigo-300'
                    ]">
                        <p class="text-sm font-medium text-gray-600">Todos</p>
                        <p class="text-2xl font-bold text-gray-900">{{ stats.total }}</p>
                    </button>

                    <button @click="searchForm.status = 'pending'; search()" :class="[
                        'p-4 rounded-lg border-2 transition',
                        filters.status === 'pending'
                            ? 'bg-yellow-50 border-yellow-500'
                            : 'bg-white border-gray-200 hover:border-yellow-300'
                    ]">
                        <p class="text-sm font-medium text-yellow-700"> Pendientes</p>
                        <p class="text-2xl font-bold text-yellow-800">{{ stats.pending }}</p>
                    </button>

                    <button @click="searchForm.status = 'approved'; search()" :class="[
                        'p-4 rounded-lg border-2 transition',
                        filters.status === 'approved'
                            ? 'bg-green-50 border-green-500'
                            : 'bg-white border-gray-200 hover:border-green-300'
                    ]">
                        <p class="text-sm font-medium text-green-700"> Aprobados</p>
                        <p class="text-2xl font-bold text-green-800">{{ stats.approved }}</p>
                    </button>

                    <button @click="searchForm.status = 'rejected'; search()" :class="[
                        'p-4 rounded-lg border-2 transition',
                        filters.status === 'rejected'
                            ? 'bg-red-50 border-red-500'
                            : 'bg-white border-gray-200 hover:border-red-300'
                    ]">
                        <p class="text-sm font-medium text-red-700"> Rechazados</p>
                        <p class="text-2xl font-bold text-red-800">{{ stats.rejected }}</p>
                    </button>

                    <button @click="searchForm.status = 'suspended'; search()" :class="[
                        'p-4 rounded-lg border-2 transition',
                        filters.status === 'suspended'
                            ? 'bg-orange-50 border-orange-500'
                            : 'bg-white border-gray-200 hover:border-orange-300'
                    ]">
                        <p class="text-sm font-medium text-orange-700"> Suspendidos</p>
                        <p class="text-2xl font-bold text-orange-800">{{ stats.suspended }}</p>
                    </button>
                </div>

                <!-- Buscador -->
                <div class="bg-white rounded-lg shadow-lg p-6 mb-8">
                    <form @submit.prevent="search" class="flex gap-4">
                        <input v-model="searchForm.search" type="text"
                            placeholder="Buscar por nombre, email o negocio..."
                            class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent" />
                        <button type="submit"
                            class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition">
                            Buscar
                        </button>
                    </form>
                </div>

                <!-- Lista de Fotógrafos -->
                <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                    <div v-if="photographers.data.length === 0" class="p-12 text-center">
                        <svg class="w-16 h-16 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                        </svg>
                        <p class="text-xl font-bold text-gray-900 mb-2">No hay fotógrafos</p>
                        <p class="text-gray-600">No se encontraron fotógrafos con los filtros seleccionados.</p>
                    </div>

                    <div v-else class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Fotógrafo
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Región
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Estado
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Eventos
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Fotos
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Registro
                                    </th>
                                    <th
                                        class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Acciones
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="photographer in photographers.data" :key="photographer.id"
                                    class="hover:bg-gray-50">
                                    <!-- Fotógrafo -->
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div
                                                class="flex-shrink-0 h-10 w-10 bg-indigo-100 rounded-full flex items-center justify-center">
                                                <span class="text-indigo-600 font-bold text-lg">
                                                    {{ photographer.business_name.charAt(0) }}
                                                </span>
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900">
                                                    {{ photographer.business_name }}
                                                </div>
                                                <div class="text-sm text-gray-500">
                                                    {{ photographer.user.email }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Región -->
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ photographer.region }}</div>
                                    </td>

                                    <!-- Estado -->
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span
                                            :class="['px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full border', getStatusBadge(photographer.status).color]">
                                            {{ getStatusBadge(photographer.status).text }}
                                        </span>
                                    </td>

                                    <!-- Eventos -->
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ photographer.events_count || 0 }}
                                    </td>

                                    <!-- Fotos -->
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ photographer.photos_count || 0 }}
                                    </td>

                                    <!-- Fecha de Registro -->
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ new Date(photographer.created_at).toLocaleDateString('es-ES') }}
                                    </td>

                                    <!-- Acciones -->
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <div class="flex items-center justify-end gap-2">
                                            <!-- Aprobar (solo si está pendiente) -->
                                            <button v-if="photographer.status === 'pending'"
                                                @click="approvePhotographer(photographer)"
                                                class="inline-flex items-center px-3 py-1.5 bg-green-600 text-white text-xs font-medium rounded hover:bg-green-700 transition"
                                                title="Aprobar">
                                                Aprobar
                                            </button>

                                            <!-- Rechazar (solo si está pendiente) -->
                                            <button v-if="photographer.status === 'pending'"
                                                @click="openRejectModal(photographer)"
                                                class="inline-flex items-center px-3 py-1.5 bg-red-600 text-white text-xs font-medium rounded hover:bg-red-700 transition"
                                                title="Rechazar">
                                                Rechazar
                                            </button>

                                            <!-- Suspender (solo si está aprobado) -->
                                            <button v-if="photographer.status === 'approved'"
                                                @click="openSuspendModal(photographer)"
                                                class="inline-flex items-center px-3 py-1.5 bg-orange-600 text-white text-xs font-medium rounded hover:bg-orange-700 transition"
                                                title="Suspender">
                                                Suspender
                                            </button>

                                            <!-- Reactivar (solo si está suspendido) -->
                                            <button v-if="photographer.status === 'suspended'"
                                                @click="reactivatePhotographer(photographer)"
                                                class="inline-flex items-center px-3 py-1.5 bg-blue-600 text-white text-xs font-medium rounded hover:bg-blue-700 transition"
                                                title="Reactivar">
                                                Reactivar
                                            </button>

                                            <!-- Ver Detalles -->
                                            <Link :href="route('admin.photographers.show', photographer.id)"
                                                class="inline-flex items-center px-3 py-1.5 bg-gray-600 text-white text-xs font-medium rounded hover:bg-gray-700 transition"
                                                title="Ver detalles">
                                            Ver
                                            </Link>

                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Paginación -->
                    <div v-if="photographers.data.length > 0" class="bg-gray-50 px-6 py-4 border-t border-gray-200">
                        <div class="flex items-center justify-between">
                            <div class="text-sm text-gray-700">
                                Mostrando
                                <span class="font-medium">{{ photographers.from }}</span>
                                a
                                <span class="font-medium">{{ photographers.to }}</span>
                                de
                                <span class="font-medium">{{ photographers.total }}</span>
                                resultados
                            </div>

                            <div class="flex gap-2">
                                <Link v-for="(link, index) in photographers.links" :key="index" :href="link.url || '#'"
                                    :class="[
                                        'px-4 py-2 text-sm font-medium rounded-lg transition',
                                        link.active
                                            ? 'bg-indigo-600 text-white'
                                            : link.url
                                                ? 'bg-white text-gray-700 hover:bg-gray-100 border border-gray-300'
                                                : 'bg-gray-100 text-gray-400 cursor-not-allowed'
                                    ]" v-html="link.label" :preserve-scroll="true" :preserve-state="true" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal: Rechazar Fotógrafo -->
        <div v-if="showRejectModal" class="fixed inset-0 z-50 overflow-y-auto" @click.self="showRejectModal = false">
            <div class="flex items-center justify-center min-h-screen px-4">
                <div class="fixed inset-0 bg-black opacity-50"></div>

                <div class="relative bg-white rounded-2xl shadow-2xl max-w-lg w-full p-8">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-2xl font-bold text-gray-900"> Rechazar Fotógrafo</h3>
                        <button @click="showRejectModal = false" class="text-gray-400 hover:text-gray-600 transition">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <div class="mb-6">
                        <p class="text-gray-700 mb-4">
                            Estás a punto de rechazar a:
                        </p>
                        <div class="bg-gray-50 rounded-lg p-4 border-l-4 border-red-500">
                            <p class="font-bold text-gray-900">{{ selectedPhotographer?.business_name }}</p>
                            <p class="text-sm text-gray-600">{{ selectedPhotographer?.user.email }}</p>
                        </div>
                    </div>

                    <form @submit.prevent="rejectPhotographer">
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Motivo del rechazo <span class="text-red-500">*</span>
                            </label>
                            <textarea v-model="rejectForm.reason" rows="4"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent"
                                placeholder="Explica por qué se rechaza la solicitud (mínimo 10 caracteres)..."
                                required></textarea>
                            <p v-if="rejectForm.errors.reason" class="mt-2 text-sm text-red-600">
                                {{ rejectForm.errors.reason }}
                            </p>
                        </div>

                        <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-6">
                            <div class="flex">
                                <svg class="h-5 w-5 text-yellow-400 mr-2" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd"
                                        d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                                        clip-rule="evenodd" />
                                </svg>
                                <p class="text-sm text-yellow-700">
                                    El fotógrafo recibirá un email con el motivo del rechazo y no podrá acceder a su
                                    panel.
                                </p>
                            </div>
                        </div>

                        <div class="flex gap-4">
                            <button type="button" @click="showRejectModal = false"
                                class="flex-1 px-6 py-3 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition font-medium">
                                Cancelar
                            </button>
                            <button type="submit" :disabled="rejectForm.processing"
                                class="flex-1 px-6 py-3 bg-red-600 text-white rounded-lg hover:bg-red-700 transition font-medium disabled:opacity-50">
                                {{ rejectForm.processing ? 'Rechazando...' : 'Confirmar Rechazo' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Modal: Suspender Fotógrafo -->
        <div v-if="showSuspendModal" class="fixed inset-0 z-50 overflow-y-auto" @click.self="showSuspendModal = false">
            <div class="flex items-center justify-center min-h-screen px-4">
                <div class="fixed inset-0 bg-black opacity-50"></div>

                <div class="relative bg-white rounded-2xl shadow-2xl max-w-lg w-full p-8">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-2xl font-bold text-gray-900"> Suspender Fotógrafo</h3>
                        <button @click="showSuspendModal = false" class="text-gray-400 hover:text-gray-600 transition">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <div class="mb-6">
                        <p class="text-gray-700 mb-4">
                            Estás a punto de suspender a:
                        </p>
                        <div class="bg-gray-50 rounded-lg p-4 border-l-4 border-orange-500">
                            <p class="font-bold text-gray-900">{{ selectedPhotographer?.business_name }}</p>
                            <p class="text-sm text-gray-600">{{ selectedPhotographer?.user.email }}</p>
                        </div>
                    </div>

                    <form @submit.prevent="suspendPhotographer">
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Motivo de la suspensión (opcional)
                            </label>
                            <textarea v-model="suspendForm.reason" rows="4"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent"
                                placeholder="Explica por qué se suspende la cuenta..."></textarea>
                        </div>

                        <div class="bg-orange-50 border-l-4 border-orange-400 p-4 mb-6">
                            <div class="flex">
                                <svg class="h-5 w-5 text-orange-400 mr-2" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd"
                                        d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                                        clip-rule="evenodd" />
                                </svg>
                                <p class="text-sm text-orange-700">
                                    El fotógrafo perderá acceso a su panel y sus eventos dejarán de ser visibles. Puedes
                                    reactivarlo después.
                                </p>
                            </div>
                        </div>

                        <div class="flex gap-4">
                            <button type="button" @click="showSuspendModal = false"
                                class="flex-1 px-6 py-3 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition font-medium">
                                Cancelar
                            </button>
                            <button type="submit" :disabled="suspendForm.processing"
                                class="flex-1 px-6 py-3 bg-orange-600 text-white rounded-lg hover:bg-orange-700 transition font-medium disabled:opacity-50">
                                {{ suspendForm.processing ? 'Suspendiendo...' : 'Confirmar Suspensión' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
