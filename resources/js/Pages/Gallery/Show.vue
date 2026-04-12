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

// Lógica del carrito
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
            success('Fotografía agregada al carrito');
            window.dispatchEvent(new Event('cart-updated'));
        } else {
            error('Esta foto ya está en tu carrito');
        }
    } catch (error) {
        console.error('Error agregando al carrito:', error);
        if (error.response) {
            error('Error al agregar al carrito');
        } else {
            error('Error de conexión');
        }
    } finally {
        addingToCart.value = false;
    }
};

// Para invitados: compra directa
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
            create_account: createAccount.value,
        };

        if (!isAuthenticated.value) {
            payload.email = guestEmail.value;
        }

        const response = await axios.post(
            route('payment.initiate', props.photo.id),
            payload
        );

        if (response.data.success) {
            window.location.href = response.data.sandbox_init_point;
        }
    } catch (error) {
        console.error('Error en compra:', error);

        if (error.response?.status === 422 && error.response?.data?.email_exists) {
            emailError.value = error.response.data.message;
            if (confirm(error.response.data.message + '\n\n¿Deseas ir a iniciar sesión?')) {
                window.location.href = route('login');
            }
        } else if (error.response?.data?.errors) {
            const errors = error.response.data.errors;
            emailError.value = errors.email ? errors.email[0] : 'Error en la solicitud';
        } else {
            emailError.value = error.response?.data?.message || 'Error al procesar la compra';
        }
    } finally {
        loading.value = false;
    }
};

// Placeholder actualizado al Dark Theme
const handleImageError = (e) => {
    e.target.style.display = 'none';
    const parent = e.target.parentElement;
    if (!parent.querySelector('.placeholder-institutional')) {
        const placeholder = document.createElement('div');
        placeholder.className = 'placeholder-institutional w-full h-full flex items-center justify-center bg-[#111920]';
        placeholder.innerHTML = `<span class="font-['Cormorant_Garamond'] text-4xl italic opacity-20 text-[#EEE9DF]">f33</span>`;
        parent.appendChild(placeholder);
    }
};
</script>

<template>
    <Head :title="`Foto ${photo.unique_id}`" />

    <AppLayout>
        <div class="min-h-screen bg-[#111920] font-['Syne']">

            <div class="border-b border-[#C9C1B1]/10 bg-[#111920]/95 backdrop-blur-md sticky top-0 z-30 pt-24 md:pt-0">
                <div class="max-w-7xl mx-auto px-8 md:px-16 h-20 flex items-center justify-between">
                    <Link :href="route('gallery.index')"
                        class="text-[10px] font-bold uppercase tracking-widest text-[#C9C1B1] hover:text-[#FFB162] flex items-center gap-3 transition-colors">
                        <ArrowLeftIcon class="w-4 h-4" /> Volver a la galería
                    </Link>

                    <Link v-if="isAuthenticated" :href="route('cart.index')"
                        class="text-[#C9C1B1] hover:text-[#FFB162] transition-colors flex items-center gap-2 group">
                        <ShoppingCartIcon class="w-5 h-5 group-hover:scale-110 transition-transform" />
                        <span class="text-[10px] font-bold uppercase tracking-widest">Carrito</span>
                    </Link>
                </div>
            </div>

            <div class="max-w-7xl mx-auto px-8 md:px-16 py-12 md:py-20">
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 lg:gap-20">

                    <div class="lg:col-span-8">
                        <div class="bg-[#1B2632] border border-[#C9C1B1]/10 p-4 md:p-8 flex items-center justify-center shadow-2xl relative group cursor-zoom-in"
                            @click="showFullImage = true">
                            <ProtectedImage :src="photo.watermarked_url || photo.thumbnail_url" :alt="photo.title"
                                class="max-w-full max-h-[75vh] object-contain shadow-[0_0_40px_rgba(0,0,0,0.5)] transition-transform duration-500 group-hover:scale-[1.02]"
                                @error="handleImageError" />
                            
                            <div class="absolute bottom-6 left-1/2 -translate-x-1/2 bg-[#111920]/80 backdrop-blur-md border border-[#FFB162]/20 text-[#FFB162] text-[9px] font-bold uppercase tracking-[0.2em] px-5 py-2.5 flex items-center gap-2 opacity-0 group-hover:opacity-100 transition-opacity duration-400 pointer-events-none">
                                <InformationCircleIcon class="w-4 h-4" /> Vista previa con marca de agua
                            </div>
                        </div>
                    </div>

                    <div class="lg:col-span-4 flex flex-col">

                        <div class="mb-10">
                            <div class="flex items-center gap-3 mb-4">
                                <span class="text-[9px] font-bold uppercase tracking-[0.3em] text-[#FFB162]">ID: #{{ photo.unique_id }}</span>
                                <div class="h-px flex-1 bg-[#C9C1B1]/10"></div>
                            </div>
                            
                            <h1 class="text-4xl md:text-5xl font-['Cormorant_Garamond'] text-[#EEE9DF] mb-6 leading-tight">
                                {{ photo.title || 'Fotografía Artística' }}
                            </h1>

                            <div class="flex flex-wrap gap-3 mb-10">
                                <span v-if="photo.event"
                                    class="text-[9px] font-bold uppercase tracking-widest bg-[#1B2632] border border-[#C9C1B1]/20 text-[#C9C1B1] px-3 py-1.5 shadow-sm">
                                    {{ photo.event.name }}
                                </span>
                                <span class="text-[9px] font-bold uppercase tracking-widest bg-[#1B2632] border border-[#C9C1B1]/20 text-[#C9C1B1] px-3 py-1.5 shadow-sm">
                                    {{ photo.width }} x {{ photo.height }} px
                                </span>
                            </div>

                            <div class="border-y border-[#C9C1B1]/10 py-8 mb-8 flex items-end justify-between">
                                <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-[#C9C1B1] mb-1">Licencia Digital</span>
                                <span class="text-5xl font-['Cormorant_Garamond'] text-[#FFB162] leading-none">${{ photo.price }}</span>
                            </div>

                            <button @click="handlePurchaseClick" :disabled="loading || addingToCart"
                                class="w-full bg-[#FFB162] hover:bg-[#EEE9DF] text-[#1B2632] text-[11px] font-bold uppercase tracking-[0.25em] py-5 transition-all duration-300 shadow-[0_0_20px_rgba(255,177,98,0.2)] hover:shadow-[0_0_30px_rgba(238,233,223,0.3)] flex items-center justify-center gap-3 disabled:opacity-50 disabled:cursor-not-allowed group">

                                <template v-if="isAuthenticated">
                                    <PlusIcon v-if="!addingToCart" class="w-5 h-5 group-hover:scale-110 transition-transform" />
                                    <span v-if="addingToCart">Procesando...</span>
                                    <span v-else>Añadir al Carrito</span>
                                </template>

                                <template v-else>
                                    <ShoppingCartIcon v-if="!loading" class="w-5 h-5 group-hover:scale-110 transition-transform" />
                                    <span v-if="loading">Iniciando...</span>
                                    <span v-else>Comprar Ahora</span>
                                </template>
                            </button>

                            <p class="text-[10px] text-[#C9C1B1]/50 text-center mt-5 leading-relaxed tracking-wider uppercase font-bold">
                                <template v-if="isAuthenticated">
                                    Añade al carrito y paga todo junto
                                </template>
                                <template v-else>
                                    Pago seguro a través de Mercado Pago. <br> Entrega inmediata vía email.
                                </template>
                            </p>
                        </div>

                        <div class="bg-[#1B2632] border border-[#C9C1B1]/10 p-8 mt-auto">
                            <h3 class="text-[9px] font-bold uppercase tracking-[0.3em] text-[#C9C1B1] mb-6 border-b border-[#C9C1B1]/10 pb-4">
                                Curaduría Visual Por
                            </h3>
                            <div class="flex items-center gap-5">
                                <div class="w-14 h-14 bg-[#111920] border border-[#FFB162]/20 rounded-full overflow-hidden flex-shrink-0">
                                    <ProtectedImage v-if="photo.photographer?.profile_photo_url"
                                        :src="photo.photographer.profile_photo_url"
                                        class="w-full h-full object-cover grayscale-[0.3]" />
                                    <div v-else
                                        class="w-full h-full flex items-center justify-center text-[#FFB162] font-['Cormorant_Garamond'] text-2xl">
                                        {{ photo.photographer?.business_name?.charAt(0) || 'f' }}
                                    </div>
                                </div>
                                <div>
                                    <p class="text-sm font-bold text-[#EEE9DF] mb-1">{{ photo.photographer?.business_name || 'Fotógrafo Oficial' }}</p>
                                    <Link v-if="photo.photographer?.slug"
                                        :href="route('photographers.show', photo.photographer.slug)"
                                        class="text-[10px] uppercase tracking-[0.2em] font-bold text-[#FFB162] hover:text-[#EEE9DF] border-b border-[#FFB162]/50 hover:border-[#EEE9DF] pb-0.5 transition-colors">
                                        Ver portafolio
                                    </Link>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                <div v-if="relatedPhotos && relatedPhotos.length > 0" class="mt-32 border-t border-[#C9C1B1]/10 pt-20">
                    <div class="flex items-center justify-between mb-10">
                        <h2 class="text-4xl font-['Cormorant_Garamond'] text-[#EEE9DF]">Obras <em class="italic text-[#C9C1B1]">Similares</em></h2>
                    </div>
                    
                    <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-5 gap-6">
                        <Link v-for="related in relatedPhotos" :key="related.id"
                            :href="route('gallery.show', related.unique_id)" class="group block relative overflow-hidden bg-[#1B2632] border border-[#C9C1B1]/5 hover:border-[#FFB162]/30 transition-colors">
                            <div class="aspect-[3/4] relative overflow-hidden">
                                <ProtectedImage :src="related.thumbnail_url"
                                    class="w-full h-full object-cover transition-transform duration-[800ms] group-hover:scale-105 saturate-[0.5] group-hover:saturate-100"
                                    @error="handleImageError" />
                                
                                <div class="absolute inset-0 bg-gradient-to-t from-[#111920]/90 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-400 flex flex-col justify-end p-4">
                                    <div class="flex justify-between items-center translate-y-2 group-hover:translate-y-0 transition-transform">
                                        <span class="text-[9px] font-mono text-[#FFB162] uppercase tracking-widest">#{{ related.unique_id }}</span>
                                        <span class="text-sm font-bold text-[#EEE9DF] font-['Cormorant_Garamond']">${{ related.price }}</span>
                                    </div>
                                </div>
                            </div>
                        </Link>
                    </div>
                </div>
            </div>
        </div>

        <div v-if="showEmailModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 font-['Syne']">
            <div class="absolute inset-0 bg-[#111920]/90 backdrop-blur-md transition-opacity" @click="showEmailModal = false"></div>

            <div class="relative bg-[#1B2632] border border-[#FFB162]/20 shadow-2xl w-full max-w-md overflow-hidden">
                <div class="h-[2px] w-full bg-gradient-to-r from-transparent via-[#FFB162] to-transparent"></div>

                <div class="p-10">
                    <div class="flex justify-between items-start mb-8 border-b border-[#C9C1B1]/10 pb-6">
                        <div>
                            <span class="text-[9px] font-bold uppercase tracking-[0.3em] text-[#FFB162] block mb-2">Checkout Rápido</span>
                            <h3 class="text-3xl font-['Cormorant_Garamond'] text-[#EEE9DF]">Datos de Entrega</h3>
                        </div>
                        <button @click="showEmailModal = false" class="text-[#C9C1B1] hover:text-[#FFB162] transition-colors p-1">
                            <XMarkIcon class="w-6 h-6" />
                        </button>
                    </div>

                    <form @submit.prevent="submitPurchase" class="space-y-8">
                        <div>
                            <label class="block text-[10px] font-bold uppercase tracking-widest text-[#C9C1B1] mb-3 flex items-center gap-2">
                                <EnvelopeIcon class="w-4 h-4 text-[#FFB162]" /> Correo Electrónico
                            </label>
                            <input v-model="guestEmail" type="email" required
                                class="w-full bg-[#111920] border border-[#C9C1B1]/20 focus:border-[#FFB162] focus:ring-0 text-[#EEE9DF] text-[13px] py-4 px-4 placeholder-[#C9C1B1]/30 transition-colors outline-none"
                                placeholder="ejemplo@email.com">
                            <p v-if="emailError" class="text-[#A35139] text-[10px] uppercase tracking-widest font-bold mt-3 flex items-center gap-1.5">
                                <InformationCircleIcon class="w-4 h-4" /> {{ emailError }}
                            </p>
                        </div>

                        <label class="flex items-start cursor-pointer group p-5 border border-[#C9C1B1]/10 bg-[#111920]/50 hover:border-[#FFB162]/50 transition-colors">
                            <div class="flex items-center h-5 mt-0.5">
                                <input id="createAccount" v-model="createAccount" type="checkbox"
                                    class="focus:ring-[#FFB162] h-4 w-4 text-[#FFB162] border-[#C9C1B1]/30 bg-transparent rounded-sm cursor-pointer">
                            </div>
                            <div class="ml-4">
                                <span class="block text-[10px] font-bold uppercase tracking-[0.2em] text-[#EEE9DF] group-hover:text-[#FFB162] transition-colors">
                                    Crear cuenta automáticamente
                                </span>
                                <span class="block text-[11px] text-[#C9C1B1]/60 font-light mt-2 leading-relaxed">
                                    Guardaremos esta compra en su historial para futuras descargas. Se le enviará una contraseña temporal.
                                </span>
                            </div>
                        </label>

                        <div class="pt-6 flex flex-col gap-4">
                            <button type="submit" :disabled="loading"
                                class="w-full py-5 bg-[#FFB162] text-[#1B2632] text-[10px] font-bold uppercase tracking-[0.25em] hover:bg-[#EEE9DF] transition-all shadow-[0_0_20px_rgba(255,177,98,0.15)] disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-3">
                                <span v-if="loading">Procesando...</span>
                                <span v-else>Continuar al Pago Seguro</span>
                                <span v-if="!loading" class="text-lg leading-none">&rarr;</span>
                            </button>

                            <button type="button" @click="showEmailModal = false"
                                class="w-full py-4 text-[#C9C1B1] text-[10px] font-bold uppercase tracking-widest hover:text-[#EEE9DF] transition-colors">
                                Cancelar
                            </button>
                        </div>
                    </form>
                </div>

                <div class="bg-[#111920] border-t border-[#C9C1B1]/10 px-10 py-6 flex justify-center items-center gap-3">
                    <span class="text-[9px] text-[#C9C1B1] uppercase tracking-[0.2em] font-bold">¿Ya eres miembro?</span>
                    <Link :href="route('login')"
                        class="text-[9px] font-bold uppercase tracking-[0.2em] text-[#FFB162] border-b border-[#FFB162]/50 pb-0.5 hover:text-[#EEE9DF] hover:border-[#EEE9DF] transition-colors">
                        Iniciar Sesión
                    </Link>
                </div>
            </div>
        </div>

        <Transition enter-active-class="transition-opacity duration-300" enter-from-class="opacity-0"
            enter-to-class="opacity-100" leave-active-class="transition-opacity duration-300"
            leave-from-class="opacity-100" leave-to-class="opacity-0">
            
            <div v-if="showFullImage" class="fixed inset-0 z-[100] bg-[#111920]/98 backdrop-blur-xl"
                @click="showFullImage = false">
                
                <button @click="showFullImage = false"
                    class="absolute top-6 right-6 md:top-8 md:right-10 text-[#C9C1B1] hover:text-[#FFB162] transition-colors z-10 p-2"
                    aria-label="Cerrar">
                    <XMarkIcon class="w-10 h-10 md:w-12 md:h-12" />
                </button>

                <div class="w-full h-full flex items-center justify-center p-8 md:p-16">
                    <ProtectedImage :src="photo.watermarked_url || photo.thumbnail_url" :alt="photo.title"
                        class="max-h-[85vh] mx-auto max-w-[90vw] object-contain shadow-[0_0_50px_rgba(0,0,0,0.8)]" @click.stop />
                </div>

                <div class="absolute bottom-8 left-1/2 -translate-x-1/2 text-[#C9C1B1]/50 text-[9px] md:text-[10px] font-bold uppercase tracking-[0.3em] pointer-events-none">
                    Presiona ESC o click fuera para cerrar
                </div>
            </div>
        </Transition>

    </AppLayout>
</template>