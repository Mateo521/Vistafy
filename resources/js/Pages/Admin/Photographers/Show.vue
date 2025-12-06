<script setup>
import { ref } from 'vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import {
    ArrowLeftIcon,
    CheckCircleIcon,
    XCircleIcon,
    NoSymbolIcon,
    CalendarIcon,
    PhotoIcon,
    ArrowDownTrayIcon,
    EnvelopeIcon,
    PhoneIcon,
    MapPinIcon,
    BuildingOfficeIcon,
    UserIcon,
    ClockIcon,
    ArrowPathIcon
} from '@heroicons/vue/24/outline';

const props = defineProps({
    photographer: Object,
    stats: Object,
});

const showRevertModal = ref(false);
const showSuspendModal = ref(false);
const showActivateModal = ref(false);

// ✅ Agregar formulario para suspensión
const suspendForm = useForm({
    reason: '',
});

const getStatusColor = (status) => {
    const colors = {
        pending: 'bg-yellow-100 text-yellow-800',
        approved: 'bg-green-100 text-green-800',
        rejected: 'bg-red-100 text-red-800',
        suspended: 'bg-gray-100 text-gray-800',
    };
    return colors[status] || 'bg-gray-100 text-gray-800';
};

const getStatusText = (status) => {
    const texts = {
        pending: 'Pendiente',
        approved: 'Aprobado',
        rejected: 'Rechazado',
        suspended: 'Suspendido',
    };
    return texts[status] || status;
};

const confirmRevert = () => {
    router.post(route('admin.photographers.revert', props.photographer.id), {}, {
        preserveScroll: true,
        onSuccess: () => {
            showRevertModal.value = false;
        },
    });
};

// ✅ Método corregido
const confirmSuspend = () => {
    console.log('🔵 Suspendiendo fotógrafo...', {
        id: props.photographer.id,
        reason: suspendForm.reason
    });

    suspendForm.post(route('admin.photographers.suspend', props.photographer.id), {
        preserveScroll: true,
        onSuccess: () => {
            console.log('✅ Fotógrafo suspendido exitosamente');
            showSuspendModal.value = false;
            suspendForm.reset();
        },
        onError: (errors) => {
            console.error('❌ Error al suspender:', errors);
        },
    });
};

const confirmActivate = () => {
    router.post(route('admin.photographers.reactivate', props.photographer.id), {}, {
        preserveScroll: true,
        onSuccess: () => {
            showActivateModal.value = false;
        },
    });
};

const formatDate = (date) => {
    if (!date) return 'N/A';
    return new Date(date).toLocaleDateString('es-ES', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};
</script>

<template>
    <AuthenticatedLayout>

        <Head :title="`Fotógrafo: ${photographer.business_name}`" />

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

            <!-- Header -->
            <div class="mb-8">
                <Link :href="route('admin.photographers.index', { status: photographer.status })"
                    class="inline-flex items-center text-sm text-gray-600 hover:text-gray-900 mb-4 transition">
                <ArrowLeftIcon class="h-4 w-4 mr-1" />
                Volver al listado
                </Link>

                <div class="flex items-start justify-between">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">
                            {{ photographer.business_name }}
                        </h1>
                        <p class="text-gray-600 mt-1">
                            <UserIcon class="inline h-4 w-4 mr-1" />
                            {{ photographer.user.name }}
                        </p>
                    </div>

                    <div class="flex items-center gap-3">
                        <span :class="[
                            'px-4 py-2 rounded-full text-sm font-semibold',
                            getStatusColor(photographer.status)
                        ]">
                            {{ getStatusText(photographer.status) }}
                        </span>

                        <!-- Acciones rápidas según estado -->
                        <div class="flex gap-2">
                            <template v-if="photographer.status === 'rejected'">
                                <button @click="showRevertModal = true"
                                    class="px-4 py-2 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700 transition inline-flex items-center gap-2">
                                    <ArrowPathIcon class="h-4 w-4" />
                                    Revertir
                                </button>
                            </template>

                            <template v-else-if="photographer.status === 'approved'">
                                <button @click="showSuspendModal = true"
                                    class="px-4 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-700 transition inline-flex items-center gap-2">
                                    <NoSymbolIcon class="h-4 w-4" />
                                    Suspender
                                </button>
                            </template>

                            <template v-else-if="photographer.status === 'suspended'">
                                <button @click="showActivateModal = true"
                                    class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition inline-flex items-center gap-2">
                                    <CheckCircleIcon class="h-4 w-4" />
                                    Reactivar
                                </button>
                            </template>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Estadísticas -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Eventos</p>
                            <p class="text-3xl font-bold text-gray-900 mt-2">{{ stats.events }}</p>
                        </div>
                        <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                            <CalendarIcon class="h-6 w-6 text-purple-600" />
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Fotos</p>
                            <p class="text-3xl font-bold text-gray-900 mt-2">{{ stats.photos }}</p>
                        </div>
                        <div class="w-12 h-12 bg-pink-100 rounded-lg flex items-center justify-center">
                            <PhotoIcon class="h-6 w-6 text-pink-600" />
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Descargas</p>
                            <p class="text-3xl font-bold text-gray-900 mt-2">{{ stats.downloads }}</p>
                        </div>
                        <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                            <ArrowDownTrayIcon class="h-6 w-6 text-blue-600" />
                        </div>
                    </div>
                </div>
            </div>

            <!-- Contenido -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

                <!-- Columna Principal -->
                <div class="lg:col-span-2 space-y-6">

                    <!-- Información General -->
                    <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
                        <h2 class="text-xl font-bold text-gray-900 mb-4 flex items-center gap-2">
                            <BuildingOfficeIcon class="h-5 w-5 text-purple-600" />
                            Información General
                        </h2>

                        <div class="space-y-4">
                            <div>
                                <label class="text-sm font-medium text-gray-700">Nombre del Negocio</label>
                                <p class="text-gray-900 mt-1 text-lg">{{ photographer.business_name }}</p>
                            </div>

                            <div v-if="photographer.bio">
                                <label class="text-sm font-medium text-gray-700">Biografía</label>
                                <p class="text-gray-900 mt-1">{{ photographer.bio }}</p>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div v-if="photographer.region">
                                    <label class="text-sm font-medium text-gray-700 flex items-center gap-1">
                                        <MapPinIcon class="h-4 w-4" />
                                        Región
                                    </label>
                                    <p class="text-gray-900 mt-1">{{ photographer.region }}</p>
                                </div>

                                <div v-if="photographer.phone">
                                    <label class="text-sm font-medium text-gray-700 flex items-center gap-1">
                                        <PhoneIcon class="h-4 w-4" />
                                        Teléfono
                                    </label>
                                    <p class="text-gray-900 mt-1">{{ photographer.phone }}</p>
                                </div>
                            </div>

                            <div>
                                <label class="text-sm font-medium text-gray-700 flex items-center gap-1">
                                    <EnvelopeIcon class="h-4 w-4" />
                                    Email
                                </label>
                                <p class="text-gray-900 mt-1">{{ photographer.user.email }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Razón de Rechazo (si existe) -->
                    <div v-if="photographer.rejection_reason" class="bg-red-50 border border-red-200 rounded-xl p-6">
                        <h2 class="text-xl font-bold text-red-900 mb-4 flex items-center gap-2">
                            <XCircleIcon class="h-5 w-5" />
                            Razón de Rechazo/Suspensión
                        </h2>
                        <p class="text-red-800 bg-white rounded-lg p-4 border border-red-200">
                            {{ photographer.rejection_reason }}
                        </p>
                    </div>

                </div>

                <!-- Sidebar -->
                <div class="space-y-6">

                    <!-- Timeline de Estados -->
                    <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
                        <h2 class="text-lg font-bold text-gray-900 mb-4 flex items-center gap-2">
                            <ClockIcon class="h-5 w-5 text-purple-600" />
                            Historial
                        </h2>

                        <div class="space-y-3">
                            <div class="flex items-start gap-3">
                                <div class="w-2 h-2 bg-gray-400 rounded-full mt-2"></div>
                                <div class="flex-1">
                                    <p class="text-sm font-medium text-gray-900">Registro</p>
                                    <p class="text-xs text-gray-600">{{ formatDate(photographer.created_at) }}</p>
                                </div>
                            </div>

                            <div v-if="photographer.approved_at" class="flex items-start gap-3">
                                <div class="w-2 h-2 bg-green-500 rounded-full mt-2"></div>
                                <div class="flex-1">
                                    <p class="text-sm font-medium text-gray-900">Aprobado</p>
                                    <p class="text-xs text-gray-600">{{ formatDate(photographer.approved_at) }}</p>
                                </div>
                            </div>

                            <div class="flex items-start gap-3">
                                <div :class="[
                                    'w-2 h-2 rounded-full mt-2',
                                    photographer.status === 'approved' ? 'bg-green-500' :
                                        photographer.status === 'rejected' ? 'bg-red-500' :
                                            photographer.status === 'suspended' ? 'bg-gray-500' :
                                                'bg-yellow-500'
                                ]"></div>
                                <div class="flex-1">
                                    <p class="text-sm font-medium text-gray-900">Estado Actual</p>
                                    <p class="text-xs font-semibold" :class="{
                                        'text-green-600': photographer.status === 'approved',
                                        'text-red-600': photographer.status === 'rejected',
                                        'text-gray-600': photographer.status === 'suspended',
                                        'text-yellow-600': photographer.status === 'pending'
                                    }">
                                        {{ getStatusText(photographer.status) }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Estado de Verificación -->
                    <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
                        <h2 class="text-lg font-bold text-gray-900 mb-4">Verificación</h2>

                        <div class="space-y-3">
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-600">Activo</span>
                                <span :class="[
                                    'px-2 py-1 rounded text-xs font-medium',
                                    photographer.is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'
                                ]">
                                    {{ photographer.is_active ? 'Sí' : 'No' }}
                                </span>
                            </div>

                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-600">Verificado</span>
                                <span :class="[
                                    'px-2 py-1 rounded text-xs font-medium',
                                    photographer.is_verified ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'
                                ]">
                                    {{ photographer.is_verified ? 'Sí' : 'No' }}
                                </span>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <!--  MODAL: Revertir Rechazo -->
        <Teleport to="body">
            <div v-if="showRevertModal" class="fixed inset-0 z-50 overflow-y-auto">
                <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                    <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75"
                        @click="showRevertModal = false">
                    </div>

                    <div
                        class="inline-block overflow-hidden text-left align-bottom transition-all transform bg-white rounded-lg shadow-xl sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                        <div class="px-4 pt-5 pb-4 bg-white sm:p-6 sm:pb-4">
                            <div class="sm:flex sm:items-start">
                                <div
                                    class="flex items-center justify-center flex-shrink-0 w-12 h-12 mx-auto bg-yellow-100 rounded-full sm:mx-0 sm:h-10 sm:w-10">
                                    <ArrowPathIcon class="w-6 h-6 text-yellow-600" />
                                </div>
                                <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left flex-1">
                                    <h3 class="text-lg font-medium leading-6 text-gray-900">
                                        Revertir Rechazo
                                    </h3>
                                    <div class="mt-2">
                                        <p class="text-sm text-gray-500">
                                            ¿Estás seguro de revertir el rechazo de
                                            <strong>{{ photographer.business_name }}</strong>?
                                        </p>
                                        <p class="text-sm text-gray-500 mt-2">
                                            El fotógrafo volverá a estado <strong
                                                class="text-yellow-600">"Pendiente"</strong>
                                            para nueva revisión.
                                        </p>

                                        <div v-if="photographer.rejection_reason" class="mt-3 p-3 bg-red-50 rounded-lg">
                                            <p class="text-xs font-medium text-red-800 mb-1">Razón anterior:</p>
                                            <p class="text-sm text-red-700">{{ photographer.rejection_reason }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="px-4 py-3 bg-gray-50 sm:px-6 sm:flex sm:flex-row-reverse gap-2">
                            <button @click="confirmRevert" type="button"
                                class="inline-flex justify-center w-full px-4 py-2 text-base font-medium text-white bg-yellow-600 border border-transparent rounded-md shadow-sm hover:bg-yellow-700 sm:ml-3 sm:w-auto sm:text-sm">
                                Sí, revertir
                            </button>
                            <button @click="showRevertModal = false" type="button"
                                class="inline-flex justify-center w-full px-4 py-2 mt-3 text-base font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 sm:mt-0 sm:w-auto sm:text-sm">
                                Cancelar
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </Teleport>

        <!--  MODAL: Suspender -->
        <!--  MODAL: Suspender -->
<Teleport to="body">
    <div v-if="showSuspendModal" class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75"
                @click="showSuspendModal = false">
            </div>

            <div
                class="inline-block overflow-hidden text-left align-bottom transition-all transform bg-white rounded-lg shadow-xl sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <form @submit.prevent="confirmSuspend">
                    <div class="px-4 pt-5 pb-4 bg-white sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div
                                class="flex items-center justify-center flex-shrink-0 w-12 h-12 mx-auto bg-orange-100 rounded-full sm:mx-0 sm:h-10 sm:w-10">
                                <NoSymbolIcon class="w-6 h-6 text-orange-600" />
                            </div>
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left flex-1">
                                <h3 class="text-lg font-medium leading-6 text-gray-900">
                                    Suspender Fotógrafo
                                </h3>
                                <div class="mt-2">
                                    <p class="text-sm text-gray-500 mb-4">
                                        ¿Estás seguro de suspender a
                                        <strong>{{ photographer.business_name }}</strong>?
                                    </p>
                                    <p class="text-sm text-gray-500 mb-4">
                                        El fotógrafo no podrá acceder a su panel ni subir contenido.
                                    </p>

                                    <!-- ✅ AGREGAR Campo de razón -->
                                    <div class="mt-4">
                                        <label for="suspend-reason" class="block text-sm font-medium text-gray-700 mb-2">
                                            Razón de suspensión (opcional)
                                        </label>
                                        <textarea
                                            id="suspend-reason"
                                            v-model="suspendForm.reason"
                                            rows="3"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-orange-500 focus:border-orange-500"
                                            placeholder="Explica por qué se suspende este fotógrafo..."
                                        ></textarea>
                                        <p v-if="suspendForm.errors.reason" class="mt-1 text-sm text-red-600">
                                            {{ suspendForm.errors.reason }}
                                        </p>
                                        <p class="mt-1 text-xs text-gray-500">
                                            {{ suspendForm.reason?.length || 0 }}/500 caracteres
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="px-4 py-3 bg-gray-50 sm:px-6 sm:flex sm:flex-row-reverse gap-2">
                        <button 
                            type="submit"
                            :disabled="suspendForm.processing"
                            class="inline-flex justify-center w-full px-4 py-2 text-base font-medium text-white bg-orange-600 border border-transparent rounded-md shadow-sm hover:bg-orange-700 disabled:opacity-50 sm:ml-3 sm:w-auto sm:text-sm">
                            {{ suspendForm.processing ? 'Suspendiendo...' : 'Sí, suspender' }}
                        </button>
                        <button 
                            type="button"
                            @click="showSuspendModal = false" 
                            class="inline-flex justify-center w-full px-4 py-2 mt-3 text-base font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 sm:mt-0 sm:w-auto sm:text-sm">
                            Cancelar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</Teleport>


        <!--  MODAL: Reactivar -->
        <Teleport to="body">
            <div v-if="showActivateModal" class="fixed inset-0 z-50 overflow-y-auto">
                <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                    <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75"
                        @click="showActivateModal = false">
                    </div>

                    <div
                        class="inline-block overflow-hidden text-left align-bottom transition-all transform bg-white rounded-lg shadow-xl sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                        <div class="px-4 pt-5 pb-4 bg-white sm:p-6 sm:pb-4">
                            <div class="sm:flex sm:items-start">
                                <div
                                    class="flex items-center justify-center flex-shrink-0 w-12 h-12 mx-auto bg-green-100 rounded-full sm:mx-0 sm:h-10 sm:w-10">
                                    <CheckCircleIcon class="w-6 h-6 text-green-600" />
                                </div>
                                <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                                    <h3 class="text-lg font-medium leading-6 text-gray-900">
                                        Reactivar Fotógrafo
                                    </h3>
                                    <div class="mt-2">
                                        <p class="text-sm text-gray-500">
                                            ¿Estás seguro de reactivar a
                                            <strong>{{ photographer.business_name }}</strong>?
                                        </p>
                                        <p class="text-sm text-gray-500 mt-2">
                                            El fotógrafo volverá a tener acceso completo a su panel.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="px-4 py-3 bg-gray-50 sm:px-6 sm:flex sm:flex-row-reverse gap-2">
                            <button @click="confirmActivate" type="button"
                                class="inline-flex justify-center w-full px-4 py-2 text-base font-medium text-white bg-green-600 border border-transparent rounded-md shadow-sm hover:bg-green-700 sm:ml-3 sm:w-auto sm:text-sm">
                                Sí, reactivar
                            </button>
                            <button @click="showActivateModal = false" type="button"
                                class="inline-flex justify-center w-full px-4 py-2 mt-3 text-base font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 sm:mt-0 sm:w-auto sm:text-sm">
                                Cancelar
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </Teleport>

    </AuthenticatedLayout>
</template>
