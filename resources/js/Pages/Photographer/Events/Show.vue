<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { 
    CalendarIcon, 
    MapPinIcon, 
    PhotoIcon, 
    ArrowDownTrayIcon,
    EyeIcon,
    TrashIcon,
    CloudArrowUpIcon,
    ClipboardDocumentIcon,
    CheckIcon,
    XMarkIcon
} from '@heroicons/vue/24/outline';

const props = defineProps({
    event: { type: Object, required: true },
    photos: { type: Object, default: () => ({ data: [], links: [] }) },
    stats: { 
        type: Object, 
        default: () => ({ total_photos: 0, active_photos: 0, total_downloads: 0 }) 
    },
});

// URLs seguras
const privateUrl = computed(() => `${window.location.origin}/eventos/${props.event.slug}?token=${props.event.private_token}`);
const publicUrl = computed(() => `${window.location.origin}/eventos/${props.event.slug}`);

const copyFeedback = ref(false);
const copyToClipboard = (text) => {
    navigator.clipboard.writeText(text).then(() => {
        copyFeedback.value = true;
        setTimeout(() => copyFeedback.value = false, 2000);
    });
};

const showUploadModal = ref(false);
const selectedFiles = ref([]);
const previewUrls = ref([]);

const uploadForm = useForm({
    photos: [],
    event_id: props.event.id,
});

const handleFileSelect = (e) => {
    const files = Array.from(e.target.files);
    selectedFiles.value = files;
    uploadForm.photos = files;
    previewUrls.value = [];
    files.forEach(file => {
        const reader = new FileReader();
        reader.onload = (e) => previewUrls.value.push(e.target.result);
        reader.readAsDataURL(file);
    });
};

const uploadPhotos = () => {
    uploadForm.post(route('photographer.photos.store'), {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
            showUploadModal.value = false;
            selectedFiles.value = [];
            previewUrls.value = [];
            uploadForm.reset('photos');
        },
    });
};

const deletePhoto = (photoId) => {
    if (confirm('¿Confirmar eliminación definitiva de esta foto?')) {
        router.delete(route('photographer.photos.destroy', photoId), { preserveScroll: true });
    }
};

const updateCoverImage = (photoId) => {
    if (confirm('¿Establecer como portada del evento?')) {
        router.post(route('photographer.events.cover-image', props.event.id), { photo_id: photoId }, { preserveScroll: true });
    }
};

const formatDate = (dateString) => {
    if (!dateString) return 'S/F';
    return new Date(dateString).toLocaleDateString('es-ES', { year: 'numeric', month: 'long', day: 'numeric' });
};
</script>

<template>
    <Head :title="event.name" />

    <AuthenticatedLayout>
        
        <div class="relative h-80 bg-slate-900 overflow-hidden">
            <img v-if="event.cover_image_url" :src="event.cover_image_url" class="absolute inset-0 w-full h-full object-cover opacity-50" />
            <div v-else class="absolute inset-0 bg-slate-800 flex items-center justify-center opacity-50">
                <PhotoIcon class="w-24 h-24 text-slate-700" />
            </div>
            
            <div class="absolute inset-0 bg-gradient-to-t from-slate-900 via-slate-900/40 to-transparent"></div>

            <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-full flex flex-col justify-end pb-10">
                <div class="flex flex-col md:flex-row md:items-end justify-between gap-6">
                    <div>
                        <div class="flex items-center gap-3 mb-3 text-xs font-bold uppercase tracking-widest text-white/60">
                            <Link :href="route('photographer.events.index')" class="hover:text-white transition">Eventos</Link>
                            <span>/</span>
                            <span class="text-white">{{ event.name }}</span>
                        </div>
                        <h1 class="text-4xl md:text-5xl font-serif font-bold text-white mb-2">{{ event.name }}</h1>
                        <div class="flex items-center gap-6 text-sm text-white/80 font-light">
                            <span class="flex items-center gap-2" v-if="event.event_date">
                                <CalendarIcon class="w-4 h-4" /> {{ formatDate(event.event_date) }}
                            </span>
                            <span class="flex items-center gap-2" v-if="event.location">
                                <MapPinIcon class="w-4 h-4" /> {{ event.location }}
                            </span>
                        </div>
                    </div>

                    <div class="flex gap-3">
                        <Link :href="route('photographer.events.edit', event.id)" 
                            class="px-5 py-3 border border-white/30 text-white text-xs font-bold uppercase tracking-widest hover:bg-white hover:text-slate-900 transition rounded-sm">
                            Editar
                        </Link>
                        <button @click="showUploadModal = true" 
                            class="px-5 py-3 bg-white text-slate-900 text-xs font-bold uppercase tracking-widest hover:bg-slate-200 transition rounded-sm shadow-lg flex items-center gap-2">
                            <CloudArrowUpIcon class="w-4 h-4" /> Subir Fotos
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="py-12 bg-gray-50 min-h-screen">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    
                    <div class="lg:col-span-1 space-y-8">
                        
                        <div class="bg-white border border-gray-200 rounded-sm p-6 shadow-sm">
                            <h3 class="text-xs font-bold uppercase tracking-widest text-slate-900 mb-6 border-b border-gray-100 pb-2">Métricas</h3>
                            <div class="space-y-6">
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-slate-500">Total Archivos</span>
                                    <span class="text-xl font-serif font-bold text-slate-900">{{ stats.total_photos }}</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-slate-500">Públicas</span>
                                    <span class="text-xl font-serif font-bold text-emerald-600">{{ stats.active_photos }}</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-slate-500">Descargas</span>
                                    <span class="text-xl font-serif font-bold text-slate-900">{{ stats.total_downloads }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white border border-gray-200 rounded-sm p-6 shadow-sm">
                            <h3 class="text-xs font-bold uppercase tracking-widest text-slate-900 mb-6 border-b border-gray-100 pb-2">Acceso & Compartir</h3>
                            
                            <div v-if="event.is_private" class="mb-6">
                                <div class="flex items-center gap-2 mb-2 text-amber-600">
                                    <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                                    <span class="text-xs font-bold uppercase tracking-wide">Enlace Privado</span>
                                </div>
                                <div class="relative group">
                                    <input :value="privateUrl" readonly class="w-full bg-gray-50 border border-gray-200 text-xs text-slate-500 p-3 rounded-sm font-mono focus:outline-none" />
                                    <button @click="copyToClipboard(privateUrl)" class="absolute right-2 top-2 p-1 text-slate-400 hover:text-slate-900 transition">
                                        <ClipboardDocumentIcon class="w-4 h-4" />
                                    </button>
                                </div>
                                <p class="text-[10px] text-slate-400 mt-2 leading-relaxed">Requiere token de acceso. Solo usuarios con este link pueden ver la galería.</p>
                            </div>

                            <div v-if="!event.is_private && event.is_active">
                                <div class="flex items-center gap-2 mb-2 text-emerald-600">
                                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                    <span class="text-xs font-bold uppercase tracking-wide">Enlace Público</span>
                                </div>
                                <div class="relative group">
                                    <input :value="publicUrl" readonly class="w-full bg-gray-50 border border-gray-200 text-xs text-slate-500 p-3 rounded-sm font-mono focus:outline-none" />
                                    <button @click="copyToClipboard(publicUrl)" class="absolute right-2 top-2 p-1 text-slate-400 hover:text-slate-900 transition">
                                        <ClipboardDocumentIcon class="w-4 h-4" />
                                    </button>
                                </div>
                            </div>

                            <transition enter-active-class="transition ease-out duration-300" enter-from-class="opacity-0 translate-y-2" enter-to-class="opacity-100 translate-y-0" leave-active-class="transition ease-in duration-200" leave-from-class="opacity-100" leave-to-class="opacity-0">
                                <div v-if="copyFeedback" class="absolute bottom-4 left-1/2 -translate-x-1/2 bg-slate-900 text-white text-xs px-4 py-2 rounded-sm shadow-lg flex items-center gap-2">
                                    <CheckIcon class="w-3 h-3" /> Copiado
                                </div>
                            </transition>
                        </div>

                        <div v-if="event.description" class="bg-white border border-gray-200 rounded-sm p-6 shadow-sm">
                            <h3 class="text-xs font-bold uppercase tracking-widest text-slate-900 mb-4 border-b border-gray-100 pb-2">Notas</h3>
                            <p class="text-sm text-slate-600 font-light leading-relaxed">{{ event.description }}</p>
                        </div>
                    </div>

                    <div class="lg:col-span-2">
                        
                        <div v-if="!photos.data || photos.data.length === 0" class="text-center py-24 border border-dashed border-gray-300 rounded-sm bg-white">
                            <PhotoIcon class="h-12 w-12 mx-auto text-gray-300 mb-4 stroke-1" />
                            <h4 class="text-lg font-serif font-medium text-slate-900 mb-1">Sin material</h4>
                            <p class="text-xs text-slate-500 font-light mb-6">Esta galería está vacía.</p>
                            <button @click="showUploadModal = true" class="text-xs font-bold uppercase tracking-widest text-slate-900 border-b border-slate-900 pb-0.5 hover:text-slate-600 hover:border-slate-600 transition">
                                Iniciar Carga
                            </button>
                        </div>

                        <div v-else>
                            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4 mb-8">
                                <div v-for="photo in photos.data" :key="photo.id" class="group relative aspect-square bg-gray-100 border border-gray-200 rounded-sm overflow-hidden">
                                    <img :src="photo.thumbnail_url" :alt="photo.unique_id" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105 filter grayscale-[0.3] group-hover:grayscale-0" />
                                    
                                    <div class="absolute inset-0 bg-slate-900/80 opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex flex-col items-center justify-center gap-3 p-4">
                                        <div class="flex gap-2">
                                            <button @click="updateCoverImage(photo.id)" title="Usar como portada" class="p-2 bg-white/10 hover:bg-white text-white hover:text-slate-900 rounded-sm transition">
                                                <PhotoIcon class="w-4 h-4" />
                                            </button>
                                            <button @click="deletePhoto(photo.id)" title="Eliminar" class="p-2 bg-white/10 hover:bg-red-600 text-white rounded-sm transition">
                                                <TrashIcon class="w-4 h-4" />
                                            </button>
                                        </div>
                                        <div class="text-[9px] text-white/60 font-mono mt-2">{{ photo.unique_id }}</div>
                                    </div>

                                    <div v-if="photo.downloads > 0" class="absolute bottom-1 right-1 bg-slate-900/80 text-white text-[9px] px-1.5 py-0.5 rounded-sm flex items-center gap-1">
                                        <ArrowDownTrayIcon class="w-3 h-3" /> {{ photo.downloads }}
                                    </div>
                                </div>
                            </div>

                            <div v-if="photos.last_page > 1" class="flex justify-center gap-1 pt-6 border-t border-gray-200">
                                <Link v-for="(link, index) in photos.links" :key="index" 
                                    :href="link.url || '#'" 
                                    v-html="link.label"
                                    :class="['h-8 min-w-[2rem] px-2 flex items-center justify-center text-xs font-medium rounded-sm border transition-colors', link.active ? 'bg-slate-900 text-white border-slate-900' : 'bg-white text-slate-600 border-gray-200 hover:border-slate-400']"
                                />
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <div v-if="showUploadModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-slate-900/80 backdrop-blur-sm" @click="showUploadModal = false"></div>
            <div class="relative bg-white rounded-sm shadow-2xl max-w-3xl w-full max-h-[85vh] overflow-hidden flex flex-col">
                
                <div class="p-6 border-b border-gray-100 flex justify-between items-center bg-white">
                    <h3 class="text-lg font-serif font-bold text-slate-900">Cargar Material</h3>
                    <button @click="showUploadModal = false" class="text-slate-400 hover:text-slate-900"><XMarkIcon class="w-6 h-6" /></button>
                </div>

                <div class="p-6 overflow-y-auto flex-1">
                    <div v-if="selectedFiles.length === 0" class="border-2 border-dashed border-gray-300 rounded-sm p-12 text-center hover:border-slate-400 transition-colors bg-gray-50">
                        <input type="file" multiple accept="image/*" @change="handleFileSelect" class="hidden" id="file-upload">
                        <label for="file-upload" class="cursor-pointer block h-full">
                            <CloudArrowUpIcon class="w-12 h-12 mx-auto text-slate-300 mb-4" />
                            <span class="text-xs font-bold uppercase tracking-widest text-slate-900 block mb-2">Seleccionar Archivos</span>
                            <span class="text-xs text-slate-500 font-light">JPG, PNG • Máx 10MB</span>
                        </label>
                    </div>

                    <div v-else>
                        <div class="grid grid-cols-4 sm:grid-cols-5 gap-3 mb-6">
                            <div v-for="(url, index) in previewUrls" :key="index" class="aspect-square bg-gray-100 rounded-sm overflow-hidden border border-gray-200">
                                <img :src="url" class="w-full h-full object-cover" />
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="text-xs text-slate-500 mb-4">{{ selectedFiles.length }} archivos seleccionados</p>
                        </div>
                    </div>
                </div>

                <div class="p-6 border-t border-gray-100 bg-gray-50 flex justify-end gap-3">
                    <button @click="showUploadModal = false" class="px-6 py-3 border border-gray-300 text-slate-600 text-xs font-bold uppercase tracking-widest hover:bg-white transition rounded-sm">Cancelar</button>
                    <button @click="uploadPhotos" :disabled="uploadForm.processing || selectedFiles.length === 0" 
                        class="px-6 py-3 bg-slate-900 text-white text-xs font-bold uppercase tracking-widest hover:bg-slate-800 transition rounded-sm disabled:opacity-50">
                        {{ uploadForm.processing ? 'Subiendo...' : 'Iniciar Carga' }}
                    </button>
                </div>
            </div>
        </div>

    </AuthenticatedLayout>
</template>