<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { useConfirm } from '@/Composables/useConfirm';
import { useToast } from '@/Composables/useToast';
import QrcodeVue from 'qrcode.vue';
import html2canvas from 'html2canvas';

import {
    CalendarIcon, MapPinIcon, UserPlusIcon, EnvelopeIcon,
    PhotoIcon, ArrowDownTrayIcon, TrashIcon, ArrowLeftIcon,
    MagnifyingGlassIcon, QrCodeIcon, Cog6ToothIcon, PlusCircleIcon,
    LinkIcon, HashtagIcon, LockClosedIcon, CheckIcon,
    XMarkIcon, UserIcon, CheckCircleIcon, CurrencyDollarIcon
} from '@heroicons/vue/24/outline';
import { CheckCircleIcon as CheckCircleIconSolid } from '@heroicons/vue/24/solid';

const props = defineProps({
    event: { type: Object, required: true },
    photos: { type: Object, default: () => ({ data: [], links: [] }) },
    stats: { type: Object, default: () => ({ total_photos: 0, active_photos: 0, total_downloads: 0 }) },
    unassignedPhotos: { type: Array, default: () => [] },
    permissions: { type: Object, required: true },
    current_photographer_id: { type: Number, required: true },
});

const { confirm } = useConfirm();
const { success, error } = useToast();


const isSelectionMode = ref(false);
const selectedPhotos = ref([]);
const showBulkPriceModal = ref(false);
const bulkPriceForm = useForm({ price: '' });

const toggleSelectionMode = () => {
    isSelectionMode.value = !isSelectionMode.value;
    if (!isSelectionMode.value) selectedPhotos.value = [];
};

const togglePhotoSelection = (photoId) => {
    const index = selectedPhotos.value.indexOf(photoId);
    if (index > -1) selectedPhotos.value.splice(index, 1);
    else selectedPhotos.value.push(photoId);
};

const selectAllOnPage = () => {
    const pageIds = props.photos.data.map(p => p.id);
    const allSelected = pageIds.every(id => selectedPhotos.value.includes(id));
    if (allSelected) {
        selectedPhotos.value = selectedPhotos.value.filter(id => !pageIds.includes(id));
    } else {
        const newIds = pageIds.filter(id => !selectedPhotos.value.includes(id));
        selectedPhotos.value.push(...newIds);
    }
};

const bulkDelete = async () => {
    const confirmed = await confirm({
        title: 'Eliminar fotos seleccionadas',
        message: `¿Estás seguro de eliminar ${selectedPhotos.value.length} fotografías permanentemente?`,
        confirmText: 'Eliminar', cancelText: 'Cancelar', type: 'danger'
    });

    if (confirmed) {
        router.post(route('photographer.photos.bulk-destroy'), { ids: selectedPhotos.value }, {
            preserveScroll: true,
            onSuccess: () => {
                success(`${selectedPhotos.value.length} fotografías eliminadas`);
                selectedPhotos.value = [];
                isSelectionMode.value = false;
            }
        });
    }
};

const submitBulkPrice = () => {
    if (!bulkPriceForm.price || bulkPriceForm.price <= 0) return error('Ingresá un precio válido');
    router.post(route('photographer.photos.bulk-price'), {
        ids: selectedPhotos.value, price: bulkPriceForm.price
    }, {
        preserveScroll: true,
        onSuccess: () => {
            success('Precios actualizados correctamente');
            showBulkPriceModal.value = false;
            selectedPhotos.value = [];
            isSelectionMode.value = false;
            bulkPriceForm.reset();
        }
    });
};


const showInviteModal = ref(false);
const inviteForm = useForm({ email: '' });
const showQrModal = ref(false);
const qrCardRef = ref(null);
const showUploadModal = ref(false); // Modal para asignar de la bóveda
const selectedExistingPhotos = ref([]);

const privateEventUrl = computed(() => `${window.location.origin}/eventos/${props.event.slug}?token=${props.event.private_token}`);
const getCorsUrl = (url) => {
    if (!url) return null;
    return `${url}${url.includes('?') ? '&' : '?'}t=${new Date().getTime()}`;
};

const copyEventUrl = async () => {
    try {
        let url = route('events.show', props.event.slug);
        if (props.event.is_private && props.event.private_token) url += `?token=${props.event.private_token}`;
        await navigator.clipboard.writeText(url);
        success(props.event.is_private ? 'Enlace privado copiado' : 'Enlace público copiado');
    } catch (err) { error('No se pudo copiar el enlace.'); }
};

const downloadQrCard = async () => {
    if (!qrCardRef.value) return;
    try {
        const canvas = await html2canvas(qrCardRef.value, { scale: 3, useCORS: true, backgroundColor: '#ffffff' });
        const link = document.createElement('a');
        link.download = `Tarjeta-QR-${props.event.slug}.jpg`;
        link.href = canvas.toDataURL('image/jpeg', 0.9);
        link.click();
    } catch (error) { alert("Hubo un error al generar la tarjeta."); }
};

const invitePhotographer = () => {
    inviteForm.post(route('photographer.events.invite', props.event.id), {
        preserveScroll: true,
        onSuccess: () => { showInviteModal.value = false; inviteForm.reset(); success('Invitación enviada.'); },
        onError: () => error('Problema al enviar la invitación.')
    });
};

const toggleExistingPhotoSelection = (photoId) => {
    const index = selectedExistingPhotos.value.indexOf(photoId);
    if (index > -1) selectedExistingPhotos.value.splice(index, 1);
    else selectedExistingPhotos.value.push(photoId);
};

const assignExistingPhotos = () => {
    if (selectedExistingPhotos.value.length === 0) return;
    router.post(route('photographer.photos.assign-to-event'), {
        photo_ids: selectedExistingPhotos.value, event_id: props.event.id,
    }, {
        preserveScroll: true,
        onSuccess: () => { showUploadModal.value = false; selectedExistingPhotos.value = []; success('Fotos asignadas'); },
    });
};

const deletePhoto = async (photoId) => {
    const confirmed = await confirm({ title: 'Eliminar foto', message: '¿Confirmar eliminación definitiva?', confirmText: 'Eliminar', cancelText: 'Cancelar', type: 'danger' });
    if (confirmed) {
        router.delete(route('photographer.photos.destroy', photoId), { preserveScroll: true, onSuccess: () => success('Fotografía eliminada') });
    }
};

const updateCoverImage = async (photoId) => {
    const confirmed = await confirm({ title: 'Cambiar portada', message: '¿Establecer esta foto como portada?', confirmText: 'Establecer', cancelText: 'Cancelar', type: 'info' });
    if (confirmed) {
        router.post(route('photographer.events.cover-image', props.event.id), { photo_id: photoId }, { preserveScroll: true, onSuccess: () => success('Portada actualizada') });
    }
};

const formatDate = (dateString) => {
    if (!dateString) return 'S/F';
    return new Date(dateString).toLocaleDateString('es-ES', { year: 'numeric', month: '2-digit', day: '2-digit' }).replace(/\//g, '.');
};

const paginationPages = computed(() => {
    const current = props.photos.current_page;
    const last = props.photos.last_page;
    const delta = 2;
    const pages = [1];
    const rangeStart = Math.max(2, current - delta);
    const rangeEnd = Math.min(last - 1, current + delta);
    if (rangeStart > 2) pages.push('...');
    for (let i = rangeStart; i <= rangeEnd; i++) pages.push(i);
    if (rangeEnd < last - 1) pages.push('...');
    if (last > 1) pages.push(last);
    return pages;
});
</script>

<template>

    <Head :title="event.name" />

    <AuthenticatedLayout>

        <div class="relative w-full h-[40vh] min-h-[350px] bg-slate-900 overflow-hidden">
            <img v-if="event.cover_image_url" :src="event.cover_image_url"
                class="absolute inset-0 w-full h-full object-cover opacity-60 mix-blend-overlay" />
            <div v-else class="absolute inset-0 bg-gradient-to-br from-slate-800 to-black"></div>
            <div class="absolute inset-0 bg-gradient-to-t from-black/90 via-black/30 to-transparent"></div>

            <div
                class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-full flex flex-col justify-between pt-8 pb-10 z-10">
                <div>
                    <Link :href="route('photographer.events.index')"
                        class="inline-flex items-center gap-2 bg-white/20 backdrop-blur-md px-4 py-2 rounded-full text-xs font-bold uppercase tracking-wider text-white hover:bg-white hover:text-black transition-colors border border-white/30">
                        <ArrowLeftIcon class="w-4 h-4" /> Volver a mis eventos
                    </Link>
                </div>

                <div class="flex flex-col md:flex-row md:items-end justify-between gap-6">
                    <div>
                        <h1 class="font-flux text-5xl md:text-7xl text-white leading-none tracking-wide mb-4">{{
                            event.name }}</h1>
                        <div
                            class="flex flex-wrap items-center gap-4 text-xs font-bold uppercase tracking-wider text-white/80">
                            <span v-if="event.event_date"
                                class="flex items-center gap-1.5 bg-black/40 backdrop-blur-md px-3 py-1.5 rounded-full">
                                <CalendarIcon class="w-4 h-4 text-[#E30613]" /> {{ formatDate(event.event_date) }}
                            </span>
                            <span v-if="event.location"
                                class="flex items-center gap-1.5 bg-black/40 backdrop-blur-md px-3 py-1.5 rounded-full">
                                <MapPinIcon class="w-4 h-4 text-[#E30613]" /> {{ event.location }}
                            </span>
                        </div>
                    </div>

                    <div class="flex flex-wrap gap-3">
                        <Link :href="route('photographer.events.edit', event.id)"
                            class="px-6 py-3 bg-white/10 backdrop-blur-md border border-white/30 text-white text-xs font-bold uppercase tracking-wider hover:bg-white hover:text-black transition-colors rounded-full flex items-center gap-2">
                            <Cog6ToothIcon class="w-4 h-4" /> Editar evento
                        </Link>
                        <button @click="showUploadModal = true"
                            class="px-6 py-3 bg-[#E30613] text-white text-xs font-bold uppercase tracking-wider hover:bg-red-700 transition-colors rounded-full flex items-center gap-2 shadow-lg shadow-red-500/30">
                            <PlusCircleIcon class="w-5 h-5" /> Asignar fotos
                        </button>
                        <div v-if="event.collaborators && event.collaborators.length > 0">
                            <Link :href="route('photographer.events.chat', event.id)"
                                class="px-6 py-3 bg-white/10 backdrop-blur-md border border-white/30 text-white text-xs font-bold uppercase tracking-wider hover:bg-white hover:text-black transition-colors rounded-full flex items-center justify-center gap-2 shadow-lg">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                                </svg>
                                Chat grupal
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>


        <div class="py-12 bg-[#F8F9FA] min-h-screen text-slate-800 pb-32">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 xl:gap-12">


                    <div class="lg:col-span-1 space-y-6">

                        <div class="bg-white rounded p-6 md:p-8 shadow-sm border border-gray-100">
                            <h3
                                class="text-xs font-bold uppercase tracking-widest text-gray-400 mb-6 flex items-center gap-2">
                                <span class="w-4 h-px bg-gray-200"></span> Métricas de Operación
                            </h3>
                            <div class="space-y-4">
                                <div class="flex justify-between items-center bg-gray-50 p-4 rounded">
                                    <span class="text-sm font-medium text-gray-600">Total archivos</span>
                                    <span class="text-xl font-black text-black">{{ stats?.total_photos || 0 }}</span>
                                </div>
                                <div class="flex justify-between items-center bg-gray-50 p-4 rounded">
                                    <span class="text-sm font-medium text-gray-600">Públicas</span>
                                    <span class="text-xl font-black text-black">{{ stats?.active_photos || 0 }}</span>
                                </div>
                                <div class="flex justify-between items-center bg-gray-50 p-4 rounded">
                                    <span class="text-sm font-medium text-gray-600">Descargas totales</span>
                                    <span class="text-xl font-black text-[#E30613]">{{ stats?.total_downloads || 0
                                        }}</span>
                                </div>
                            </div>
                            <button @click="copyEventUrl"
                                class="mt-6 w-full border-2 transition-colors px-4 py-3.5 rounded text-xs font-bold uppercase tracking-wider flex items-center justify-center gap-2"
                                :class="event.is_private ? 'bg-red-50 border-red-200 hover:border-[#E30613] text-[#E30613] hover:text-[#E30613]' : 'bg-white border-gray-200 hover:border-black text-gray-600 hover:text-black'">
                                <LinkIcon class="w-4 h-4" /> {{ event.is_private ? 'Copiar enlace privado' : 'Copiar
                                enlace público' }}
                            </button>
                            <button v-if="event.is_private" @click="showQrModal = true"
                                class="mt-3 w-full bg-black border border-black hover:bg-gray-800 text-white transition-colors px-4 py-3.5 rounded text-xs font-bold uppercase tracking-wider flex items-center justify-center gap-2 shadow-sm">
                                <QrCodeIcon class="w-4 h-4" /> Generar tarjeta QR
                            </button>
                        </div>


                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-1 gap-4">
                            <Link :href="route('events.face-search', event.slug)"
                                class="group bg-white border border-gray-100 p-5 rounded shadow-sm hover:shadow-md transition-all flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 bg-red-50 rounded-full flex items-center justify-center">
                                        <MagnifyingGlassIcon class="w-5 h-5 text-[#E30613]" />
                                    </div>
                                    <div>
                                        <h4 class="font-bold text-sm text-black">Escáner facial</h4>
                                        <p class="text-xs text-gray-500">Probar búsqueda</p>
                                    </div>
                                </div>
                                <ArrowLeftIcon
                                    class="w-4 h-4 text-gray-400 rotate-180 group-hover:text-[#E30613] group-hover:translate-x-1 transition-all" />
                            </Link>
                            <Link :href="route('events.bib-search', event.slug)"
                                class="group bg-white border border-gray-100 p-5 rounded shadow-sm hover:shadow-md transition-all flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 bg-gray-100 rounded-full flex items-center justify-center">
                                        <HashtagIcon class="w-5 h-5 text-black" />
                                    </div>
                                    <div>
                                        <h4 class="font-bold text-sm text-black">Búsqueda OCR</h4>
                                        <p class="text-xs text-gray-500">Dorsales</p>
                                    </div>
                                </div>
                                <ArrowLeftIcon
                                    class="w-4 h-4 text-gray-400 rotate-180 group-hover:text-black group-hover:translate-x-1 transition-all" />
                            </Link>
                        </div>


                        <div class="bg-white rounded p-6 md:p-8 shadow-sm border border-gray-100">
                            <div class="flex justify-between items-center mb-6">
                                <h3
                                    class="text-xs font-bold uppercase tracking-widest text-gray-400 flex items-center gap-2">
                                    <span class="w-4 h-px bg-gray-200"></span> Equipo asignado
                                </h3>
                                <button @click="showInviteModal = true"
                                    class="text-[10px] font-bold uppercase text-red-600 hover:text-black transition-colors flex items-center gap-1">
                                    <UserPlusIcon class="w-3 h-3" /> Invitar
                                </button>
                            </div>
                            <div class="space-y-4">
                                <div class="flex items-center gap-4 bg-gray-50 p-3 rounded border border-gray-100">
                                    <div
                                        class="w-10 h-10 bg-black rounded-full flex items-center justify-center text-white font-bold shadow-sm">
                                        {{ event.photographer?.business_name?.charAt(0) || 'Y' }}
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-bold text-black truncate">{{
                                            event.photographer?.business_name || 'Tú' }}</p>
                                        <p class="text-[10px] font-bold text-[#E30613] uppercase tracking-wider">
                                            Administrador</p>
                                    </div>
                                </div>
                                <template v-if="event.collaborators && event.collaborators.length > 0">
                                    <div v-for="collab in event.collaborators" :key="collab.id"
                                        class="flex items-center gap-4 p-2 border-b border-gray-50 last:border-0">
                                        <img v-if="collab.profile_photo_url" :src="collab.profile_photo_url"
                                            class="w-10 h-10 rounded-full object-cover border border-gray-200 shadow-sm">
                                        <div v-else
                                            class="w-10 h-10 bg-gray-200 rounded-full flex items-center justify-center text-gray-500 font-bold shadow-sm">
                                            {{ collab.business_name.charAt(0) }}</div>
                                        <div class="flex-1 min-w-0 flex justify-between items-center">
                                            <div>
                                                <p class="text-sm font-bold text-slate-700 truncate">{{
                                                    collab.business_name }}</p>
                                                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">
                                                    Colaborador</p>
                                            </div>
                                            <span
                                                v-if="collab.pivot?.status === 'pending' || collab.pivot?.status === 'invited'"
                                                class="bg-yellow-50 text-yellow-600 border border-yellow-200 px-2 py-0.5 rounded text-[9px] font-bold uppercase tracking-widest">Pendiente</span>
                                            <span v-else-if="collab.pivot?.status === 'approved'"
                                                class="bg-green-50 text-green-600 border border-green-200 px-2 py-0.5 rounded text-[9px] font-bold uppercase tracking-widest">Confirmado</span>
                                        </div>
                                    </div>
                                </template>
                                <div v-else>
                                    <p class="text-xs text-gray-500 italic">No hay colaboradores asignados.</p>
                                </div>
                            </div>
                        </div>
                    </div>


                    <div class="lg:col-span-2">


                        <div
                            class="flex flex-wrap justify-between items-center bg-white p-4 rounded shadow-sm border border-gray-100 mb-6 gap-4">
                            <div class="flex gap-2">
                                <button @click="toggleSelectionMode" :class="[
                                    'px-4 py-2 rounded-full text-xs font-bold uppercase tracking-wider transition-all border flex items-center gap-2',
                                    isSelectionMode ? 'bg-[#E30613] text-white border-[#E30613]' : 'bg-gray-50 text-gray-700 border-gray-200 hover:bg-gray-100'
                                ]">
                                    <CheckCircleIcon class="w-4 h-4" />
                                    {{ isSelectionMode ? 'Cancelar Selección' : 'Selección Múltiple' }}
                                </button>

                                <button v-if="isSelectionMode && photos.data.length > 0" @click="selectAllOnPage"
                                    class="px-4 py-2 rounded-full text-xs font-bold uppercase tracking-wider bg-gray-100 text-gray-700 border border-gray-200 hover:bg-gray-200 transition-all">
                                    Seleccionar pág actual
                                </button>
                            </div>
                        </div>


                        <div v-if="!photos.data || photos.data.length === 0"
                            class="bg-white rounded border border-gray-100 shadow-sm flex flex-col items-center justify-center py-32 px-4 text-center">
                            <div class="w-20 h-20 bg-gray-50 rounded-full flex items-center justify-center mb-6">
                                <PhotoIcon class="h-10 w-10 text-gray-300" />
                            </div>
                            <h4 class="font-flux text-4xl text-black mb-2">Evento sin fotos</h4>
                            <p class="text-gray-500 mb-8 max-w-sm">No hay fotografías disponibles bajo estos filtros o
                                aún no se han
                                asignado fotos.</p>
                        </div>

                        <div v-else>
                            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4 mb-10">

                                <div v-for="photo in photos.data" :key="photo.id"
                                    @click="isSelectionMode ? togglePhotoSelection(photo.id) : null"
                                    class="group relative aspect-square bg-gray-100 rounded overflow-hidden shadow-sm transition-all duration-300 border"
                                    :class="[
                                        isSelectionMode ? 'cursor-pointer' : '',
                                        selectedPhotos.includes(photo.id) ? 'border-[#E30613] ring-2 ring-[#E30613] ring-offset-2 scale-95' : 'border-gray-200 hover:border-gray-300 hover:shadow-lg'
                                    ]">

                                    <img :src="photo.thumbnail_url" :alt="photo.unique_id"
                                        class="w-full h-full object-cover transition-transform duration-700" />


                                    <div v-if="isSelectionMode"
                                        class="absolute inset-0 bg-black/10 transition-colors z-20 flex p-3">
                                        <div class="w-6 h-6 rounded-full border-2 flex items-center justify-center bg-white/50 backdrop-blur-sm"
                                            :class="selectedPhotos.includes(photo.id) ? 'border-[#E30613] bg-[#E30613] text-white' : 'border-white text-transparent'">
                                            <CheckCircleIconSolid class="w-5 h-5"
                                                v-if="selectedPhotos.includes(photo.id)" />
                                        </div>
                                    </div>


                                    <div class="absolute top-2 left-2 z-20 flex items-center group/tooltip"
                                        :class="isSelectionMode ? 'hidden' : ''">
                                        <div
                                            class="bg-black/60 backdrop-blur-md p-1.5 rounded-full text-white cursor-help shadow-sm group-hover/tooltip:bg-[#E30613] transition-colors">
                                            <UserIcon class="w-4 h-4" />
                                        </div>
                                        <span
                                            class="absolute left-full ml-2 px-2.5 py-1.5 bg-white text-black text-[9px] font-bold uppercase tracking-wider rounded shadow-xl opacity-0 group-hover/tooltip:opacity-100 transition-opacity duration-200 whitespace-nowrap pointer-events-none border border-gray-100 z-30">
                                            {{ photo.photographer?.business_name || 'Fotógrafo F33' }}
                                        </span>
                                    </div>


                                    <div class="absolute inset-0 bg-black/60 opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex flex-col justify-between p-3 z-10"
                                        :class="isSelectionMode ? 'hidden' : ''">


                                        <div class="flex flex-col items-end gap-1">
                                            <span
                                                class="bg-white/90 backdrop-blur-sm text-black px-2 py-1 rounded text-[9px] font-bold tracking-wider">#{{
                                                photo.unique_id }}</span>

                                            <span
                                                class="bg-black/80 text-white px-1.5 py-0.5 rounded text-[8px] font-mono truncate max-w-[90%]"
                                                :title="photo.original_name">
                                                {{ photo.original_name }}
                                            </span>

                                            <span
                                                class="bg-green-500/90 text-white px-1.5 py-0.5 rounded text-[9px] font-bold mt-1">
                                                ${{ photo.price }}
                                            </span>
                                        </div>


                                        <div
                                            class="flex items-center justify-center gap-3 translate-y-4 group-hover:translate-y-0 transition-transform duration-300">
                                            <button v-if="permissions.is_creator"
                                                @click.stop="updateCoverImage(photo.id)" title="Fijar como portada"
                                                class="w-10 h-10 bg-white/20 backdrop-blur-md rounded-full flex items-center justify-center text-white hover:bg-white hover:text-black transition-colors">
                                                <PhotoIcon class="w-5 h-5" />
                                            </button>
                                            <button
                                                v-if="permissions.is_creator || photo.photographer_id === current_photographer_id"
                                                @click.stop="deletePhoto(photo.id)" title="Eliminar captura"
                                                class="w-10 h-10 bg-[#E30613]/90 backdrop-blur-md rounded-full flex items-center justify-center text-white hover:bg-[#E30613] transition-colors shadow-lg">
                                                <TrashIcon class="w-5 h-5" />
                                            </button>
                                        </div>


                                        <div class="flex justify-end">
                                            <div v-if="photo.downloads > 0"
                                                class="bg-white text-black px-2 py-1 rounded flex items-center gap-1 text-[10px] font-bold">
                                                <ArrowDownTrayIcon class="w-3 h-3 text-[#E30613]" /> {{ photo.downloads
                                                }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>


                            <div v-if="photos.last_page > 1" class="flex justify-center pt-8 border-t border-gray-200">
                                <div
                                    class="flex flex-wrap items-center gap-2 bg-white p-2 rounded-full shadow-sm border border-gray-100">
                                    <Link v-if="photos.prev_page_url" :href="photos.prev_page_url"
                                        class="h-10 px-4 flex items-center justify-center text-xs font-bold uppercase tracking-wider text-gray-600 hover:bg-gray-100 rounded-full transition-colors">
                                        Ant</Link>
                                    <span v-else
                                        class="h-10 px-4 flex items-center justify-center text-xs font-bold uppercase tracking-wider text-gray-300 cursor-not-allowed">Ant</span>

                                    <template v-for="(page, index) in paginationPages" :key="index">
                                        <span v-if="page === photos.current_page"
                                            class="h-10 w-10 flex items-center justify-center text-xs font-bold bg-black text-white rounded-full shadow-md">{{
                                            page }}</span>
                                        <span v-else-if="page === '...'"
                                            class="h-10 w-10 flex items-center justify-center text-xs text-gray-400">...</span>
                                        <Link v-else :href="photos.path + '?page=' + page"
                                            class="h-10 w-10 flex items-center justify-center text-xs font-bold text-gray-600 hover:bg-gray-100 rounded-full transition-colors">
                                            {{ page }}</Link>
                                    </template>

                                    <Link v-if="photos.next_page_url" :href="photos.next_page_url"
                                        class="h-10 px-4 flex items-center justify-center text-xs font-bold uppercase tracking-wider text-gray-600 hover:bg-gray-100 rounded-full transition-colors">
                                        Sig</Link>
                                    <span v-else
                                        class="h-10 px-4 flex items-center justify-center text-xs font-bold uppercase tracking-wider text-gray-300 cursor-not-allowed">Sig</span>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>


        <transition enter-active-class="transition duration-300 ease-out transform"
            enter-from-class="translate-y-full opacity-0" enter-to-class="translate-y-0 opacity-100"
            leave-active-class="transition duration-200 ease-in transform" leave-from-class="translate-y-0 opacity-100"
            leave-to-class="translate-y-full opacity-0">
            <div v-if="isSelectionMode && selectedPhotos.length > 0"
                class="fixed bottom-6 left-1/2 -translate-x-1/2 z-40 w-[90%] max-w-2xl bg-black rounded-2xl shadow-2xl p-4 flex items-center justify-between border border-gray-800">
                <div class="flex items-center gap-3 text-white">
                    <div class="bg-[#E30613] w-8 h-8 rounded-full flex items-center justify-center font-bold text-sm">{{
                        selectedPhotos.length }}</div>
                    <span class="text-sm font-bold uppercase tracking-wider hidden sm:block">Fotos seleccionadas</span>
                </div>
                <div class="flex items-center gap-2">
                    <button @click="showBulkPriceModal = true"
                        class="px-4 py-2.5 bg-white text-black hover:bg-gray-200 rounded-xl text-xs font-bold uppercase tracking-wider transition-colors flex items-center gap-2">
                        <CurrencyDollarIcon class="w-4 h-4" /> Precio
                    </button>
                    <button @click="bulkDelete"
                        class="px-4 py-2.5 bg-transparent border border-red-900 text-red-500 hover:bg-red-950 hover:text-red-400 rounded-xl text-xs font-bold uppercase tracking-wider transition-colors flex items-center gap-2">
                        <TrashIcon class="w-4 h-4" /> Eliminar
                    </button>
                    <button @click="toggleSelectionMode"
                        class="ml-2 p-2 text-gray-400 hover:text-white rounded-full hover:bg-gray-800">
                        <XMarkIcon class="w-5 h-5" />
                    </button>
                </div>
            </div>
        </transition>

        <transition enter-active-class="transition duration-300 ease-out" enter-from-class="opacity-0"
            enter-to-class="opacity-100" leave-active-class="transition duration-200 ease-in"
            leave-from-class="opacity-100" leave-to-class="opacity-0">
            <div v-if="showBulkPriceModal" class="fixed inset-0 z-[60] flex items-center justify-center p-4">
                <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" @click="showBulkPriceModal = false">
                </div>
                <div
                    class="relative bg-white rounded shadow-2xl max-w-sm w-full flex flex-col overflow-hidden z-10 p-6">
                    <h3 class="font-bold text-xl text-black mb-2">Modificar Precio</h3>
                    <p class="text-xs text-gray-500 mb-6">Se actualizará el precio de las {{ selectedPhotos.length }}
                        fotografías seleccionadas.</p>
                    <div class="mb-6 relative">
                        <span class="absolute left-4 top-1/2 -translate-y-1/2 text-lg font-bold text-gray-400">$</span>
                        <input v-model="bulkPriceForm.price" type="number" step="0.01"
                            class="w-full bg-gray-50 border border-gray-200 focus:bg-white focus:border-red-600 focus:ring-1 focus:ring-red-600 text-slate-800 font-bold text-xl py-3 pl-10 pr-4 rounded outline-none"
                            placeholder="0.00">
                    </div>
                    <div class="flex justify-end gap-3">
                        <button @click="showBulkPriceModal = false"
                            class="px-5 py-2.5 rounded-full border border-gray-200 text-gray-600 font-bold text-xs uppercase tracking-wider hover:bg-gray-50">Cancelar</button>
                        <button @click="submitBulkPrice"
                            class="px-5 py-2.5 rounded-full bg-black text-white font-bold text-xs uppercase tracking-wider hover:bg-gray-800">Actualizar</button>
                    </div>
                </div>
            </div>
        </transition>

        <transition enter-active-class="transition duration-300 ease-out" enter-from-class="opacity-0"
            enter-to-class="opacity-100" leave-active-class="transition duration-200 ease-in"
            leave-from-class="opacity-100" leave-to-class="opacity-0">
            <div v-if="showUploadModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6">
                <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" @click="showUploadModal = false"></div>
                <div
                    class="relative bg-white rounded shadow-2xl max-w-4xl w-full max-h-[85vh] flex flex-col overflow-hidden z-10">
                    <div class="px-6 py-5 border-b border-gray-100 bg-white flex justify-between items-center z-10">
                        <div>
                            <h3 class="font-bold text-xl text-black">Asignar fotografías cargadas anteriormente</h3>
                            <p class="text-xs text-gray-500 mt-1">Seleccioná las fotografías disponibles en tu bóveda
                                para
                                vincularlas a este evento.</p>
                        </div>
                        <button @click="showUploadModal = false"
                            class="w-10 h-10 rounded-full bg-gray-50 flex items-center justify-center text-gray-500 hover:bg-red-50 hover:text-[#E30613] transition-colors">
                            <XMarkIcon class="w-5 h-5" />
                        </button>
                    </div>
                    <div class="p-6 overflow-y-auto flex-1 bg-gray-50 hide-scrollbar">
                        <div v-if="!unassignedPhotos || unassignedPhotos.length === 0"
                            class="flex flex-col items-center justify-center py-16 text-center">
                            <div
                                class="w-16 h-16 bg-white rounded-full flex items-center justify-center shadow-sm border border-gray-100 mb-4">
                                <PhotoIcon class="w-8 h-8 text-gray-300" />
                            </div>
                            <h4 class="font-bold text-lg text-slate-700 mb-1">Sin fotografías</h4>
                            <p class="text-sm text-gray-500">No tenés fotografías pendientes de asignación en tu bóveda.
                            </p>
                        </div>
                        <div v-else>
                            <div class="flex justify-between items-center mb-4">
                                <span class="text-xs font-bold uppercase tracking-wider text-gray-500">Disponibles ({{
                                    unassignedPhotos.length }})</span>
                                <span v-if="selectedExistingPhotos.length > 0"
                                    class="text-xs font-bold text-[#E30613] bg-red-50 px-3 py-1 rounded-full">{{
                                    selectedExistingPhotos.length }} seleccionadas</span>
                            </div>
                            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-3">
                                <div v-for="photo in unassignedPhotos" :key="photo.id"
                                    @click="photo.is_active ? toggleExistingPhotoSelection(photo.id) : null"
                                    class="relative aspect-square rounded overflow-hidden shadow-sm transition-all duration-200 group"
                                    :class="[!photo.is_active ? 'cursor-not-allowed' : 'cursor-pointer hover:shadow-md', selectedExistingPhotos.includes(photo.id) ? 'ring-4 ring-[#E30613] ring-offset-2 scale-95' : '']">
                                    <img :src="photo.thumbnail_url"
                                        class="w-full h-full object-cover transition-transform duration-500"
                                        :class="!photo.is_active ? 'grayscale-[50%] opacity-80' : ''" />
                                    <div v-if="!photo.is_active"
                                        class="absolute inset-0 bg-black/50 flex flex-col items-center justify-center backdrop-blur-[1px]">
                                        <LockClosedIcon class="w-6 h-6 text-white mb-1 opacity-90" />
                                        <span
                                            class="text-white text-[10px] font-bold uppercase tracking-widest text-center px-2 opacity-90">Privada</span>
                                    </div>
                                    <div v-else class="absolute inset-0 transition-colors duration-200"
                                        :class="selectedExistingPhotos.includes(photo.id) ? 'bg-[#E30613]/20' : 'bg-black/0 group-hover:bg-black/10'">
                                        <div v-if="selectedExistingPhotos.includes(photo.id)"
                                            class="absolute top-2 right-2 w-6 h-6 bg-[#E30613] text-white rounded-full flex items-center justify-center shadow-md transform scale-100 transition-transform">
                                            <CheckIcon class="w-4 h-4" />
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="p-6 border-t border-gray-100 bg-white flex justify-end gap-3 z-10">
                        <button @click="showUploadModal = false"
                            class="px-6 py-3 rounded-full border border-gray-200 text-gray-600 font-bold text-xs uppercase tracking-wider hover:bg-gray-50 transition-colors">Cancelar</button>
                        <button @click="assignExistingPhotos" :disabled="selectedExistingPhotos.length === 0"
                            class="px-8 py-3 rounded-full bg-black text-white font-bold text-xs uppercase tracking-wider hover:bg-gray-800 transition-colors disabled:opacity-50 disabled:cursor-not-allowed shadow-md">Asignar
                            fotografías</button>
                    </div>
                </div>
            </div>
        </transition>

        <transition enter-active-class="transition duration-300 ease-out" enter-from-class="opacity-0"
            enter-to-class="opacity-100" leave-active-class="transition duration-200 ease-in"
            leave-from-class="opacity-100" leave-to-class="opacity-0">
            <div v-if="showInviteModal" class="fixed inset-0 z-[60] flex items-center justify-center p-4 sm:p-6">
                <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" @click="showInviteModal = false"></div>
                <div class="relative bg-white rounded shadow-2xl max-w-lg w-full flex flex-col overflow-hidden z-10">
                    <div class="px-6 py-5 border-b border-gray-100 bg-white flex justify-between items-center z-10">
                        <div>
                            <h3 class="font-bold text-xl text-black">Invitar fotógrafo</h3>
                            <p class="text-xs text-gray-500 mt-1">Ingresá el correo con el que está registrado en F33.
                            </p>
                        </div>
                        <button @click="showInviteModal = false"
                            class="w-10 h-10 rounded-full bg-gray-50 flex items-center justify-center text-gray-500 hover:bg-red-50 hover:text-[#E30613] transition-colors">
                            <XMarkIcon class="w-5 h-5" />
                        </button>
                    </div>
                    <form @submit.prevent="invitePhotographer" class="p-6 bg-gray-50">
                        <div class="mb-6">
                            <label
                                class="block text-xs font-bold uppercase tracking-wider text-gray-500 mb-2 ml-1">Correo
                                del
                                fotografo</label>
                            <div class="relative">
                                <EnvelopeIcon class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" />
                                <input v-model="inviteForm.email" type="email" required
                                    class="w-full bg-white border border-gray-200 focus:bg-white focus:border-red-600 focus:ring-1 focus:ring-red-600 text-slate-800 font-bold text-sm py-3 pl-12 pr-4 rounded transition-all outline-none"
                                    placeholder="ejemplo@correo.com">
                            </div>
                        </div>
                        <div class="flex justify-end gap-3">
                            <button type="button" @click="showInviteModal = false"
                                class="px-6 py-3 rounded-full border border-gray-200 text-gray-600 font-bold text-xs uppercase tracking-wider hover:bg-white transition-colors">Cancelar</button>
                            <button type="submit" :disabled="inviteForm.processing"
                                class="px-8 py-3 rounded-full bg-red-600 text-white font-bold text-xs uppercase tracking-wider hover:bg-black transition-colors disabled:opacity-50 shadow-md">
                                <span v-if="inviteForm.processing">Enviando...</span>
                                <span v-else>Enviar Invitación</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </transition>

        <!-- MODAL TARJETA QR -->
        <div v-if="showQrModal"
            class="fixed inset-0 z-[100] flex flex-col items-center justify-center bg-black/90 backdrop-blur-sm p-4 overflow-y-auto">
            <button @click="showQrModal = false" class="absolute top-6 right-6 text-white hover:text-gray-300">
                <XMarkIcon class="w-8 h-8" />
            </button>
            <div ref="qrCardRef"
                class="w-full max-w-[380px] bg-white rounded overflow-hidden shadow-2xl relative my-8 shrink-0">
                <div class="h-32 bg-black relative">
                    <img v-if="event.cover_image_url" :src="getCorsUrl(event.cover_image_url)" crossorigin="anonymous"
                        class="w-full h-full object-cover opacity-50" />
                    <div v-else class="w-full h-full bg-gradient-to-tr from-gray-900 to-gray-800"></div>
                </div>
                <div class="absolute top-16 left-1/2 -translate-x-1/2">
                    <img :src="event.photographer.profile_photo_url ? getCorsUrl(event.photographer.profile_photo_url) : `https://ui-avatars.com/api/?name=${event.photographer.business_name}&background=random`"
                        crossorigin="anonymous"
                        class="w-24 h-24 rounded-full border-4 border-white object-cover shadow-md bg-white" />
                </div>
                <div class="pt-14 pb-8 px-8 text-center flex flex-col items-center">
                    <h3 class="font-bold text-xl text-black leading-tight">{{ event.photographer.business_name }}</h3>
                    <p class="text-[10px] font-bold text-[#E30613] uppercase tracking-widest mt-1 mb-6">Fotografía</p>
                    <h4 class="font-flux text-2xl text-black leading-none">{{ event.name }}</h4>
                    <p class="text-xs text-gray-500 mt-2 uppercase font-bold tracking-wider">{{ event.event_date }}</p>
                    <div class="mt-8 p-3 bg-white border-2 border-gray-100 rounded shadow-sm inline-block">
                        <qrcode-vue :value="privateEventUrl" :size="180" level="H" foreground="#000000" />
                    </div>
                    <p class="text-xs font-bold text-gray-800 uppercase tracking-widest mt-6">Escaneá para ver tus fotos
                    </p>
                    <p class="text-[9px] text-gray-400 font-bold uppercase tracking-widest mt-1">Acceso privado</p>
                </div>
            </div>
            <button @click="downloadQrCard"
                class="bg-[#E30613] hover:bg-red-700 text-white font-bold uppercase tracking-wider text-sm px-8 py-4 rounded-full shadow-lg flex items-center gap-2 transition-transform transform hover:scale-105 shrink-0 mb-8">
                <ArrowDownTrayIcon class="w-5 h-5" /> Descargar tarjeta (jpg)
            </button>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.hide-scrollbar::-webkit-scrollbar {
    display: none;
}

.hide-scrollbar {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
</style>