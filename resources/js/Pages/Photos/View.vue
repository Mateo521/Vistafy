<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import ProtectedImage from '@/Components/ProtectedImage.vue';
import { 
    ArrowLeftIcon, 
    CalendarIcon, 
    UserIcon,
    PhotoIcon,
    ShoppingCartIcon 
} from '@heroicons/vue/24/outline';

const props = defineProps({
    photo: Object
});

const addToCart = () => {
    // Implementar lógica de agregar al carrito
    console.log('Agregar al carrito:', props.photo.id);
};

const formatFileSize = (bytes) => {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
};
</script>

<template>
    <AppLayout>
        <Head :title="`Foto - ${photo.original_name}`" />

        <div class="min-h-screen bg-slate-50 pt-24 pb-16">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                
                <!-- Breadcrumb -->
                <div class="mb-8">
                    <Link 
                        :href="route('events.show', photo.event.slug)"
                        class="inline-flex items-center gap-2 text-sm text-slate-600 hover:text-slate-900 transition-colors"
                    >
                        <ArrowLeftIcon class="w-4 h-4" />
                        Volver al evento: {{ photo.event.name }}
                    </Link>
                </div>

                <div class="grid lg:grid-cols-3 gap-8">
                    
                    <!-- Imagen Principal -->
                    <div class="lg:col-span-2">
                        <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                            <div class="relative bg-slate-900 flex items-center justify-center" style="min-height: 600px;">
                                <ProtectedImage
                                    :src="photo.watermarked_url"
                                    :alt="photo.original_name"
                                    class="w-full h-auto max-h-[80vh]"
                                    object-fit="contain"
                                />
                            </div>
                        </div>

                        <!-- Aviso de marca de agua -->
                        <div class="mt-4 bg-blue-50 border border-blue-200 rounded-lg p-4">
                            <div class="flex items-start gap-3">
                                <PhotoIcon class="w-5 h-5 text-blue-600 flex-shrink-0 mt-0.5" />
                                <div class="text-sm text-blue-900">
                                    <p class="font-semibold mb-1">Esta imagen tiene marca de agua</p>
                                    <p class="text-blue-700">
                                        Al adquirirla recibirás la versión en alta resolución sin marca de agua.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Sidebar de información -->
                    <div class="space-y-6">
                        
                        <!-- Información de la foto -->
                        <div class="bg-white rounded-lg shadow-lg p-6">
                            <h2 class="text-xl font-bold text-slate-900 mb-6">Detalles de la foto</h2>
                            
                            <div class="space-y-4">
                                <!-- ID Único -->
                                <div>
                                    <p class="text-xs uppercase tracking-wider text-slate-500 mb-1">ID único</p>
                                    <p class="text-sm font-mono text-slate-900">{{ photo.unique_id }}</p>
                                </div>

                                <!-- Dimensiones -->
                                <div>
                                    <p class="text-xs uppercase tracking-wider text-slate-500 mb-1">Dimensiones</p>
                                    <p class="text-sm text-slate-900">{{ photo.width }} × {{ photo.height }} px</p>
                                </div>

                                <!-- Tamaño -->
                                <div>
                                    <p class="text-xs uppercase tracking-wider text-slate-500 mb-1">Tamaño</p>
                                    <p class="text-sm text-slate-900">{{ formatFileSize(photo.file_size) }}</p>
                                </div>

                                <!-- Fecha -->
                                <div class="flex items-center gap-2 text-sm text-slate-600">
                                    <CalendarIcon class="w-4 h-4" />
                                    <span>{{ new Date(photo.created_at).toLocaleDateString('es-AR', { 
                                        year: 'numeric', 
                                        month: 'long', 
                                        day: 'numeric' 
                                    }) }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Información del evento -->
                        <div class="bg-white rounded-lg shadow-lg p-6">
                            <h3 class="text-sm uppercase tracking-wider text-slate-500 mb-3">Evento</h3>
                            <Link 
                                :href="route('events.show', photo.event.slug)"
                                class="block group"
                            >
                                <p class="text-lg font-semibold text-slate-900 group-hover:text-blue-600 transition-colors">
                                    {{ photo.event.name }}
                                </p>
                            </Link>
                        </div>

                        <!-- Información del fotógrafo -->
                        <div class="bg-white rounded-lg shadow-lg p-6">
                            <h3 class="text-sm uppercase tracking-wider text-slate-500 mb-3">Fotógrafo</h3>
                            <div class="flex items-center gap-3">
                                <UserIcon class="w-10 h-10 text-slate-400" />
                                <div>
                                    <Link 
                                        :href="route('photographers.show', photo.photographer.id)"
                                        class="font-semibold text-slate-900 hover:text-blue-600 transition-colors"
                                    >
                                        {{ photo.photographer.business_name }}
                                    </Link>
                                </div>
                            </div>
                        </div>

                        <!-- Botón de agregar al carrito -->
                        <button
                            @click="addToCart"
                            class="w-full bg-slate-900 text-white py-4 px-6 rounded-lg hover:bg-slate-800 transition-colors font-semibold flex items-center justify-center gap-3 group"
                        >
                            <ShoppingCartIcon class="w-5 h-5 group-hover:scale-110 transition-transform" />
                            Agregar al carrito
                        </button>

                        <!-- Precio (si lo tienes) -->
                        <div class="text-center">
                            <p class="text-2xl font-bold text-slate-900">$2.500</p>
                            <p class="text-sm text-slate-500">Alta resolución sin marca de agua</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
