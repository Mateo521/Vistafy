<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { ref, computed } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import { 
    ArrowLeftIcon, 
    ShoppingCartIcon, 
    XMarkIcon, 
    CheckIcon,
    InformationCircleIcon,
    TagIcon
} from '@heroicons/vue/24/outline';

const props = defineProps({
    photo: Object,
    relatedPhotos: Array,
});

const showFullImage = ref(false);
const loading = ref(false);
const page = usePage();
const showEmailModal = ref(false);
const guestEmail = ref('');
const createAccount = ref(false);
const emailError = ref('');

// Verificar autenticación
const isAuthenticated = computed(() => page.props.auth?.user !== null);

const handlePurchaseClick = () => {
    if (isAuthenticated.value) {
        submitPurchase();
    } else {
        showEmailModal.value = true;
    }
};

const submitPurchase = async () => {
    if (loading.value) return;

    if (!isAuthenticated.value && !guestEmail.value) {
        emailError.value = 'El email es requerido';
        return;
    }

    loading.value = true;
    emailError.value = '';

    try {
        const payload = {
            email: guestEmail.value || undefined,
            create_account: createAccount.value || false,
        };

        const response = await axios.post(
            `/pago/fotos/${props.photo.id}/comprar`,
            payload
        );

        if (response.data.success) {
            const initPoint = response.data.sandbox_init_point;
            window.location.href = initPoint;
        } else {
            alert('Error al iniciar el pago. Por favor intenta nuevamente.');
        }
    } catch (error) {
        console.error('Error al iniciar compra:', error);
        if (error.response?.data?.message) {
            emailError.value = error.response.data.message;
        } else {
            alert('Error al procesar la compra. Por favor intenta nuevamente.');
        }
    } finally {
        loading.value = false;
    }
};

const handleImageError = (e) => {
    e.target.style.display = 'none';
    const parent = e.target.parentElement;
    if (!parent.querySelector('.placeholder-institutional')) {
        const placeholder = document.createElement('div');
        placeholder.className = 'placeholder-institutional w-full h-full flex items-center justify-center bg-gray-100 text-slate-300';
        placeholder.innerHTML = `<svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>`;
        parent.appendChild(placeholder);
    }
};
</script>

<template>
    <Head :title="`Foto ${photo.unique_id}`" />

    <AppLayout>
        <div class="min-h-screen bg-white">
            
            <div class="border-b border-gray-100 bg-white sticky top-0 z-40">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center">
                    <Link href="/galeria" class="text-xs font-bold uppercase tracking-widest text-slate-500 hover:text-slate-900 flex items-center gap-2 transition-colors">
                        <ArrowLeftIcon class="w-3 h-3" /> Volver a la galería
                    </Link>
                </div>
            </div>

            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-12">
                    
                    <div class="lg:col-span-8">
                        <div class="bg-gray-50 border border-gray-100 rounded-sm p-2 md:p-8 flex items-center justify-center shadow-inner relative group">
                            <img 
                                :src="photo.watermarked_url || photo.thumbnail_url" 
                                :alt="photo.title" 
                                class="max-w-full max-h-[80vh] object-contain shadow-lg cursor-zoom-in transition-transform duration-300"
                                @click="showFullImage = true"
                                @error="handleImageError" 
                            />
                            
                            <div class="absolute bottom-4 left-1/2 -translate-x-1/2 bg-black/70 backdrop-blur-sm text-white text-[10px] font-bold uppercase tracking-widest px-4 py-2 rounded-full flex items-center gap-2 opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none">
                                <InformationCircleIcon class="w-3 h-3" /> Vista previa con marca de agua
                            </div>
                        </div>
                    </div>

                    <div class="lg:col-span-4 space-y-8">
                        
                        <div>
                            <h1 class="text-2xl font-serif font-bold text-slate-900 mb-2">
                                Fotografía #{{ photo.unique_id }}
                            </h1>
                            <div class="flex flex-wrap gap-2 mb-6">
                                <span v-if="photo.event" class="text-xs font-medium bg-gray-100 text-slate-600 px-2 py-1 rounded-sm border border-gray-200">
                                    {{ photo.event.name }}
                                </span>
                                <span class="text-xs font-medium bg-gray-100 text-slate-600 px-2 py-1 rounded-sm border border-gray-200">
                                    {{ photo.width }} x {{ photo.height }} px
                                </span>
                            </div>
                            
                            <div class="border-t border-b border-gray-100 py-6 mb-6">
                                <p class="text-sm text-slate-500 font-light mb-1">Precio de licencia</p>
                                <p class="text-4xl font-serif font-medium text-slate-900">${{ photo.price }}</p>
                            </div>

                            <button @click="handlePurchaseClick" :disabled="loading"
                                class="w-full bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold uppercase tracking-widest py-4 rounded-sm transition-all shadow-md hover:shadow-lg flex items-center justify-center gap-2 disabled:opacity-70 disabled:cursor-not-allowed">
                                <ShoppingCartIcon v-if="!loading" class="w-4 h-4" />
                                <span v-if="loading">Procesando...</span>
                                <span v-else>Adquirir Imagen</span>
                            </button>
                            
                            <p class="text-[10px] text-slate-400 text-center mt-3 font-light">
                                Descarga digital inmediata en alta resolución tras el pago.
                            </p>
                        </div>

                      
                      
                       <div class="bg-gray-50 p-6 border border-gray-200 rounded-sm">
    <h3 class="text-xs font-bold uppercase tracking-widest text-slate-900 mb-4 border-b border-gray-200 pb-2">
        Autor
    </h3>
    <div class="flex items-center gap-4">
        <div class="w-12 h-12 bg-white border border-gray-200 rounded-sm overflow-hidden flex-shrink-0">
            <img v-if="photo.photographer?.profile_photo_url" :src="photo.photographer.profile_photo_url" class="w-full h-full object-cover" />
            <div v-else class="w-full h-full flex items-center justify-center bg-slate-200 text-slate-400 font-serif font-bold">
                {{ photo.photographer?.business_name?.charAt(0) || '?' }}
            </div>
        </div>
        
        <div>
            <p class="text-sm font-bold text-slate-900">
                {{ photo.photographer?.business_name || 'Fotógrafo Desconocido' }}
            </p>
            
            <Link 
                v-if="photo.photographer?.slug"
                :href="route('photographers.show', photo.photographer.slug)" 
                class="text-xs text-slate-500 hover:text-slate-900 underline decoration-1 underline-offset-2"
            >
                Ver portafolio completo
            </Link>
        </div>
    </div>
</div>



                    </div>
                </div>

                <div v-if="relatedPhotos && relatedPhotos.length > 0" class="mt-24 border-t border-gray-100 pt-12">
                    <h2 class="text-xl font-serif font-bold text-slate-900 mb-8">Más de esta colección</h2>
                    <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-5 gap-6">
                        <Link v-for="related in relatedPhotos" :key="related.id" 
                            :href="route('gallery.show', related.unique_id)"
                            class="group block"
                        >
                            <div class="aspect-square bg-gray-100 overflow-hidden rounded-sm border border-gray-200 relative mb-2">
                                <img :src="related.thumbnail_url" 
                                    class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105 filter grayscale-[0.3] group-hover:grayscale-0"
                                    @error="handleImageError" 
                                />
                                <div class="absolute inset-0 bg-slate-900/0 group-hover:bg-slate-900/10 transition-colors"></div>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-[10px] uppercase tracking-widest text-slate-500 font-bold group-hover:text-slate-900 transition-colors">#{{ related.unique_id }}</span>
                                <span class="text-xs font-bold text-slate-900">${{ related.price }}</span>
                            </div>
                        </Link>
                    </div>
                </div>
            </div>
        </div>

        <div v-if="showFullImage" class="fixed inset-0 z-50 bg-black/95 backdrop-blur-sm flex items-center justify-center" @click="showFullImage = false">
            <button class="absolute top-6 right-6 text-white/50 hover:text-white transition">
                <XMarkIcon class="w-8 h-8" />
            </button>
            <img :src="photo.watermarked_url || photo.thumbnail_url" class="max-h-[90vh] max-w-[90vw] object-contain shadow-2xl" @click.stop />
        </div>

        <div v-if="showEmailModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" @click="showEmailModal = false"></div>
            
            <div class="bg-white rounded-sm shadow-2xl w-full max-w-md relative z-10 overflow-hidden">
                <div class="p-8">
                    <h3 class="text-xl font-serif font-bold text-slate-900 mb-2">Finalizar Compra</h3>
                    <p class="text-sm text-slate-500 font-light mb-6">Ingrese su correo electrónico para recibir el enlace de descarga segura de su fotografía.</p>
                    
                    <form @submit.prevent="submitPurchase" class="space-y-4">
                        <div>
                            <label class="block text-[10px] font-bold uppercase tracking-widest text-slate-500 mb-2">Email</label>
                            <input v-model="guestEmail" type="email" required
                                class="w-full border-gray-300 rounded-sm focus:border-slate-900 focus:ring-0 text-slate-900 text-sm"
                                placeholder="cliente@ejemplo.com"
                            >
                            <p v-if="emailError" class="text-red-600 text-xs mt-1">{{ emailError }}</p>
                        </div>

                        <div class="flex items-start pt-2">
                            <div class="flex items-center h-5">
                                <input id="createAccount" v-model="createAccount" type="checkbox" class="focus:ring-slate-900 h-4 w-4 text-slate-900 border-gray-300 rounded-sm">
                            </div>
                            <div class="ml-3 text-xs">
                                <label for="createAccount" class="font-medium text-slate-700">Crear cuenta automáticamente</label>
                                <p class="text-slate-400">Guardaremos su historial de compras.</p>
                            </div>
                        </div>

                        <div class="pt-4 flex gap-3">
                            <button type="button" @click="showEmailModal = false" 
                                class="flex-1 px-4 py-3 border border-gray-300 text-slate-600 text-xs font-bold uppercase tracking-widest hover:bg-gray-50 transition rounded-sm">
                                Cancelar
                            </button>
                            <button type="submit" :disabled="loading" 
                                class="flex-1 px-4 py-3 bg-slate-900 text-white text-xs font-bold uppercase tracking-widest hover:bg-slate-800 transition rounded-sm disabled:opacity-70">
                                {{ loading ? 'Procesando...' : 'Continuar' }}
                            </button>
                        </div>
                    </form>
                </div>
                <div class="bg-gray-50 px-8 py-4 border-t border-gray-100 text-center">
                    <p class="text-[10px] text-slate-400">
                        ¿Ya tiene cuenta? <Link :href="route('login')" class="text-slate-900 font-bold hover:underline">Inicie sesión</Link>
                    </p>
                </div>
            </div>
        </div>

    </AppLayout>
</template>