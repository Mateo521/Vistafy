<script setup>
import { Head, Link, usePage, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { ref, computed, watch, onUnmounted } from 'vue';
import { useToast } from '@/Composables/useToast';
import ProtectedImage from '@/Components/ProtectedImage.vue';
import {
    ArrowLeftIcon,
    ShoppingCartIcon,
    XMarkIcon,
    InformationCircleIcon,
    EnvelopeIcon,
    CheckIcon,
    PlusIcon
} from '@heroicons/vue/24/outline';
import axios from 'axios';

const { success, error } = useToast();

const props = defineProps({
    photo: Object,
    relatedPhotos: Array,
});

const showFullImage = ref(false);
const showEmailModal = ref(false);
const loading = ref(false);
const addingToCart = ref(false);
const guestEmail = ref('');
const createAccount = ref(false);
const emailError = ref('');

const page = usePage();
const isAuthenticated = computed(() => page.props.auth?.user !== null);

const handleKeydown = (e) => {
    if (e.key === 'Escape') {
        showFullImage.value = false;
        showEmailModal.value = false;
    }
};

watch([showFullImage, showEmailModal], ([fullImage, emailModal]) => {
    if (fullImage || emailModal) {
        document.addEventListener('keydown', handleKeydown);
        document.body.style.overflow = 'hidden';
    } else {
        document.removeEventListener('keydown', handleKeydown);
        document.body.style.overflow = '';
    }
});

onUnmounted(() => {
    document.removeEventListener('keydown', handleKeydown);
    document.body.style.overflow = '';
});


const handlePurchaseClick = () => {
    if (isAuthenticated.value) {
        addToCart();
    } else {
        showEmailModal.value = true;
    }
};

const addToCart = async () => {
    if (addingToCart.value) return;
    addingToCart.value = true;

    try {
        const response = await axios.post(route('cart.add', props.photo.id));

        if (response.data.success) {
            success('FOTOGRAFÍA ENCOLADA AL CARRITO');
            window.dispatchEvent(new Event('cart-updated'));
        } else {
            error('FOTOGRAFÍA YA EXISTENTE EN COLA');
        }
    } catch (err) {
        console.error('Error agregando al carrito:', err);
        if (err.response) {
            error('ERROR AL ENCOLAR DATO');
        } else {
            error('ERROR DE CONEXIÓN DEL NODO');
        }
    } finally {
        addingToCart.value = false;
    }
};


const submitPurchase = async () => {
    if (loading.value) return;

    if (!isAuthenticated.value && !guestEmail.value) {
        emailError.value = 'IDENTIFICADOR DE CORREO REQUERIDO';
        return;
    }

    loading.value = true;
    emailError.value = '';

    try {
        const payload = { create_account: createAccount.value };

        if (!isAuthenticated.value) {
            payload.email = guestEmail.value;
        }

        const response = await axios.post(
            route('payment.initiate', props.photo.id),
            payload
        );

        const paymentUrl = response.data.init_point || response.data.sandbox_init_point;

        if (response.data.success && paymentUrl) {
            window.location.href = paymentUrl;
        } else {
            emailError.value = 'FALLO AL GENERAR PASARELA DE PAGO';
        }

    } catch (err) {
        console.error('Error en compra:', err);

        if (err.response?.status === 422 && err.response?.data?.email_exists) {
            emailError.value = err.response.data.message;
            if (confirm(err.response.data.message + '\n\n¿EJECUTAR LOGIN?')) {
                window.location.href = route('login');
            }
        } else if (err.response?.data?.errors) {
            const errors = err.response.data.errors;
            emailError.value = errors.email ? errors.email[0] : 'ERROR EN LA SOLICITUD';
        } else {
            emailError.value = err.response?.data?.message || 'ERROR AL PROCESAR TRANSACCIÓN';
        }
    } finally {
        loading.value = false;
    }
};


const handleImageError = (e) => {
    e.target.style.display = 'none';
    const parent = e.target.parentElement;
    if (!parent.querySelector('.placeholder-img')) {
        const placeholder = document.createElement('div');
        placeholder.className = 'placeholder-img w-full h-full min-h-[300px] flex items-center justify-center bg-gray-900 border border-red-600/30';
        placeholder.innerHTML = `<span class="font-mono text-xs text-red-600 uppercase tracking-widest">[ ERROR DE LECTURA ]</span>`;
        parent.appendChild(placeholder);
    }
};
</script>

<template>
    <Head :title="`ID_${photo.unique_id} — F33`" />

    <AppLayout>
        <div class="min-h-screen bg-black text-white font-sans selection:bg-red-600 selection:text-white">

            <div class="border-b border-white/20 bg-black/90 backdrop-blur-sm sticky top-0 z-30 pt-16 md:pt-0">
                <div class="max-w-[1500px] mx-auto px-4 md:px-8 h-14 flex items-center justify-between font-mono text-[10px] uppercase tracking-widest">
                    <Link :href="route('gallery.index')"
                        class="text-gray-400 hover:text-white flex items-center gap-3 transition-none border border-transparent hover:border-white px-3 py-1">
                        <ArrowLeftIcon class="w-3.5 h-3.5" /> [ CATÁLOGO ]
                    </Link>

                    <Link v-if="isAuthenticated" :href="route('cart.index')"
                        class="text-white hover:text-black hover:bg-white transition-none flex items-center gap-2 border border-white px-3 py-1">
                        <ShoppingCartIcon class="w-4 h-4" />
                        <span>COLA DE DESCARGAS</span>
                    </Link>
                </div>
            </div>

            <div class="max-w-[1500px] mx-auto px-4 md:px-8 py-12 md:py-20">
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-16">

                    <div class="lg:col-span-8">
                        <div class="bg-gray-950 border-[6px] border-black hover:border-red-600 p-2 flex items-center justify-center relative group cursor-crosshair transition-none"
                            @click="showFullImage = true">
                            
                            <div class="absolute top-4 left-4 z-20 bg-red-600 text-black font-mono text-[9px] font-bold px-2 py-1 tracking-widest uppercase opacity-0 group-hover:opacity-100 transition-none pointer-events-none">
                                CLICK PARA EXPANDIR
                            </div>

                            <ProtectedImage :src="photo.watermarked_url || photo.thumbnail_url" :alt="photo.title"
                                class="w-full max-h-[80vh] object-contain grayscale-[0.3] group-hover:grayscale-0 transition-none"
                                @error="handleImageError" />
                            
                            <div class="absolute bottom-4 right-4 bg-black border border-white text-white text-[9px] font-mono font-bold uppercase tracking-widest px-3 py-1.5 opacity-0 group-hover:opacity-100 transition-none pointer-events-none">
                                MUESTRA CON PROTECCIÓN
                            </div>
                        </div>
                    </div>

                    <div class="lg:col-span-4 flex flex-col font-mono">

                        <div class="mb-8">
                            <div class="flex items-center justify-between border-b border-white/20 pb-4 mb-6">
                                <span class="text-[10px] font-bold uppercase tracking-[0.3em] text-red-600 bg-red-600/10 px-2 py-1 border border-red-600">ID_REF: {{ photo.unique_id }}</span>
                            </div>
                            
                            <h1 class="font-black font-sans text-5xl md:text-6xl text-white mb-6 leading-[0.85] tracking-tighter uppercase">
                                {{ photo.title || 'FRAME CAPTURADO' }}
                            </h1>

                            <div class="flex flex-col gap-2 mb-8 text-[10px] tracking-widest text-gray-400 uppercase">
                                <div class="flex justify-between border-b border-white/10 pb-2">
                                    <span>RESOLUCIÓN NATIVA:</span>
                                    <span class="text-white">{{ photo.width }} x {{ photo.height }} PX</span>
                                </div>
                                <div v-if="photo.event" class="flex justify-between border-b border-white/10 pb-2">
                                    <span>EVENTO / LOCACIÓN:</span>
                                    <span class="text-red-600 truncate ml-4">{{ photo.event.name }}</span>
                                </div>
                            </div>

                            <div class="border-t-[4px] border-white pt-6 mb-8 flex items-end justify-between">
                                <span class="text-[10px] font-bold uppercase tracking-[0.2em] text-gray-500">VALOR DE LICENCIA</span>
                                <span class="text-5xl font-black font-sans text-white leading-none tracking-tighter">${{ photo.price }}</span>
                            </div>

                            <button @click="handlePurchaseClick" :disabled="loading || addingToCart"
                                class="w-full bg-red-600 hover:bg-white text-black text-[12px] font-black uppercase tracking-[0.25em] py-5 border-[4px] border-red-600 hover:border-white transition-none flex items-center justify-center gap-3 disabled:opacity-30 disabled:cursor-not-allowed group">
                                <template v-if="isAuthenticated">
                                    <PlusIcon v-if="!addingToCart" class="w-5 h-5" />
                                    <span v-if="addingToCart">EJECUTANDO...</span>
                                    <span v-else>ENCOLAR A CARRITO</span>
                                </template>
                                <template v-else>
                                    <ShoppingCartIcon v-if="!loading" class="w-5 h-5" />
                                    <span v-if="loading">INICIALIZANDO...</span>
                                    <span v-else>ADQUIRIR AHORA</span>
                                </template>
                            </button>

                            <p class="text-[9px] text-gray-600 mt-4 leading-relaxed tracking-widest uppercase font-bold text-center">
                                <template v-if="isAuthenticated">
                                    SISTEMA DE PAGOS EN LOTE ACTIVO.
                                </template>
                                <template v-else>
                                    PASARELA SEGURA. DESCARGA VÍA PROTOCOLO DE EMAIL.
                                </template>
                            </p>
                        </div>

                        <div class="border border-white/20 p-6 mt-auto bg-gray-950 hover:border-white transition-none">
                            <h3 class="text-[9px] font-bold uppercase tracking-[0.3em] text-gray-500 mb-4 border-b border-white/10 pb-2">
                                OPERADOR / CREADOR
                            </h3>
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 bg-black border border-red-600 flex-shrink-0 flex items-center justify-center">
                                    <ProtectedImage v-if="photo.photographer?.profile_photo_url"
                                        :src="photo.photographer.profile_photo_url"
                                        class="w-full h-full object-cover grayscale" />
                                    <div v-else class="text-red-600 font-black text-xl font-sans">
                                        {{ photo.photographer?.business_name?.charAt(0) || 'F' }}
                                    </div>
                                </div>
                                <div>
                                    <p class="text-xs font-bold text-white mb-1 uppercase tracking-widest">{{ photo.photographer?.business_name || 'OPERADOR F33' }}</p>
                                    <Link v-if="photo.photographer?.slug"
                                        :href="route('photographers.show', photo.photographer.slug)"
                                        class="text-[9px] uppercase tracking-[0.2em] font-bold text-red-600 hover:text-white border-b border-red-600 hover:border-white transition-none">
                                        [ INSPECCIONAR CATÁLOGO ]
                                    </Link>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                <div v-if="relatedPhotos && relatedPhotos.length > 0" class="mt-24 md:mt-32 pt-16 border-t border-white/20">
                    <h2 class="text-4xl md:text-6xl font-black font-sans text-white uppercase tracking-tighter mb-10">
                        ARCHIVOS <span class="text-red-600">RELACIONADOS</span>
                    </h2>
                    
                    <div class="columns-2 md:columns-4 lg:columns-5 gap-2 space-y-2 masonry-grid">
                        <Link v-for="related in relatedPhotos" :key="related.id"
                            :href="route('gallery.show', related.unique_id)" 
                            class="break-inside-avoid block group relative bg-gray-950 overflow-hidden border-[4px] border-black hover:border-red-600 transition-none cursor-crosshair">
                            
                            <div class="relative w-full h-auto">
                                <ProtectedImage :src="related.thumbnail_url"
                                    class="w-full h-auto object-cover grayscale opacity-70 group-hover:grayscale-0 group-hover:opacity-100 transition-none pointer-events-none"
                                    @error="handleImageError" />
                                
                                <div class="absolute top-2 left-2 bg-black border border-white px-2 py-1 text-[9px] font-mono text-white tracking-widest pointer-events-none">
                                    ${{ related.price }}
                                </div>
                                
                                <div class="absolute bottom-2 right-2 bg-red-600 text-black px-1.5 py-0.5 text-[8px] font-mono font-bold tracking-widest opacity-0 group-hover:opacity-100 transition-none pointer-events-none">
                                    {{ related.unique_id }}
                                </div>
                            </div>
                        </Link>
                    </div>
                </div>
            </div>
        </div>

        <div v-if="showEmailModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 font-mono">
            <div class="absolute inset-0 bg-black/90" @click="showEmailModal = false"></div>

            <div class="relative bg-black border-[4px] border-white w-full max-w-md shadow-[10px_10px_0_rgba(220,38,38,1)] overflow-hidden">
                <div class="bg-white text-black px-4 py-2 flex justify-between items-center font-bold text-xs uppercase tracking-widest">
                    <span>F33 // TERMINAL DE PAGO</span>
                    <button @click="showEmailModal = false" class="hover:text-red-600 transition-none">[X]</button>
                </div>

                <div class="p-8">
                    <form @submit.prevent="submitPurchase" class="space-y-6">
                        <div>
                            <label class="block text-[10px] font-bold uppercase tracking-widest text-white mb-2">
                                > ENTRADA DE CORREO_
                            </label>
                            <input v-model="guestEmail" type="email" required
                                class="w-full bg-gray-950 border border-gray-600 focus:border-red-600 focus:ring-0 text-white font-mono text-xs py-3 px-4 outline-none transition-none"
                                placeholder="usuario@nodo.com">
                            <p v-if="emailError" class="text-red-600 text-[9px] uppercase tracking-widest font-bold mt-2">
                                ! {{ emailError }}
                            </p>
                        </div>

                        <label class="flex items-start cursor-pointer group p-4 border border-gray-800 bg-gray-950 hover:border-white transition-none">
                            <div class="flex items-center h-4 mt-0.5">
                                <input id="createAccount" v-model="createAccount" type="checkbox"
                                    class="focus:ring-0 focus:ring-offset-0 h-4 w-4 text-red-600 border-gray-600 bg-black rounded-none cursor-pointer">
                            </div>
                            <div class="ml-3">
                                <span class="block text-[9px] font-bold uppercase tracking-widest text-white group-hover:text-red-600 transition-none">
                                    [ GENERAR USUARIO ]
                                </span>
                                <span class="block text-[9px] text-gray-500 mt-1 leading-relaxed">
                                    SE ALMACENARÁ EL HISTORIAL. RECIBIRÁ CREDENCIALES TEMPORALES VÍA EMAIL.
                                </span>
                            </div>
                        </label>

                        <div class="pt-4 flex flex-col gap-3">
                            <button type="submit" :disabled="loading"
                                class="w-full py-4 bg-red-600 text-black text-[11px] font-black uppercase tracking-[0.25em] hover:bg-white transition-none disabled:opacity-50 disabled:cursor-not-allowed">
                                <span v-if="loading">EJECUTANDO...</span>
                                <span v-else>INICIAR TRANSACCIÓN</span>
                            </button>
                            <button type="button" @click="showEmailModal = false"
                                class="w-full py-3 text-gray-500 text-[9px] font-bold uppercase tracking-widest hover:text-white transition-none border border-transparent hover:border-gray-500">
                                [ ABORTAR ]
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <Transition enter-active-class="transition-none" enter-from-class="opacity-0"
            enter-to-class="opacity-100" leave-active-class="transition-none"
            leave-from-class="opacity-100" leave-to-class="opacity-0">
            
            <div v-if="showFullImage" class="fixed inset-0 z-[100] bg-black cursor-zoom-out flex flex-col"
                @click="showFullImage = false">
                
                <div class="p-4 flex justify-end">
                    <button @click="showFullImage = false" class="text-white border-2 border-white hover:bg-white hover:text-black font-mono font-bold text-xs uppercase tracking-widest px-4 py-2 transition-none">
                        [ CERRAR VISOR ]
                    </button>
                </div>

                <div class="flex-1 flex items-center justify-center p-4 md:p-12 overflow-hidden">
                    <ProtectedImage :src="photo.watermarked_url || photo.thumbnail_url" :alt="photo.title"
                        class="max-h-full max-w-full object-contain border-4 border-white" @click.stop />
                </div>

                <div class="p-4 border-t border-white/20 text-center font-mono text-[10px] text-gray-500 uppercase tracking-[0.3em]">
                    SISTEMA DE REVISIÓN F33 // MARCA DE AGUA ACTIVA
                </div>
            </div>
        </Transition>

    </AppLayout>
</template>

<style scoped>

.masonry-grid {
    column-fill: balance;
}


::-webkit-scrollbar {
    width: 8px;
}
::-webkit-scrollbar-track {
    background: #000000;
    border-left: 1px solid #333;
}
::-webkit-scrollbar-thumb {
    background: #ffffff;
    border-radius: 0;
}
::-webkit-scrollbar-thumb:hover {
    background: #dc2626;
}
</style>