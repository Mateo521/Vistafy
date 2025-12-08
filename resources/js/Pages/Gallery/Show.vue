<script setup>
import { Head, Link, usePage, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { ref, computed } from 'vue';
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

//  NUEVO: Lógica del carrito
const handlePurchaseClick = () => {
    if (isAuthenticated.value) {
        // Usuario autenticado: agregar al carrito
        addToCart();
    } else {
        // Invitado: compra directa
        showEmailModal.value = true;
    }
};

const addToCart = async () => {
    if (addingToCart.value) return;
    
    addingToCart.value = true;

    try {
        const response = await axios.post(route('cart.add', props.photo.id));

        if (response.data.success) {
            alert('Foto agregada al carrito');
            
            //  Disparar evento para actualizar el contador
            window.dispatchEvent(new Event('cart-updated'));
        } else {
            alert(response.data.message || 'Esta foto ya está en tu carrito');
        }
    } catch (error) {
        console.error('Error agregando al carrito:', error);
        
        if (error.response) {
            alert(error.response.data.message || 'Error al agregar al carrito');
        } else {
            alert('Error de conexión');
        }
    } finally {
        addingToCart.value = false;
    }
};


// Para invitados: compra directa como antes
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
            //  Email ya existe
            emailError.value = error.response.data.message;
            
            // Opcional: Ofrecer ir al login
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
            
            <div class="border-b border-gray-100 bg-white sticky top-0 z-30">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
                    <Link :href="route('gallery.index')" class="text-[10px] font-bold uppercase tracking-widest text-slate-500 hover:text-slate-900 flex items-center gap-2 transition-colors">
                        <ArrowLeftIcon class="w-3 h-3" /> Volver a la galería
                    </Link>

                    <!--  Icono de carrito (solo para usuarios autenticados) -->
                    <Link v-if="isAuthenticated" :href="route('cart.index')" 
                        class="text-slate-500 hover:text-slate-900 transition-colors flex items-center gap-2">
                        <ShoppingCartIcon class="w-5 h-5" />
                        <span class="text-[10px] font-bold uppercase tracking-widest">Carrito</span>
                    </Link>
                </div>
            </div>

            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-12">
                    
                    <div class="lg:col-span-8">
                        <div class="bg-gray-50 border border-gray-100 rounded-sm p-2 md:p-8 flex items-center justify-center shadow-inner relative group cursor-zoom-in" @click="showFullImage = true">
                            <img 
                                :src="photo.watermarked_url || photo.thumbnail_url" 
                                :alt="photo.title" 
                                class="max-w-full max-h-[80vh] object-contain shadow-lg transition-transform duration-300"
                                @error="handleImageError" 
                            />
                            <div class="absolute bottom-4 left-1/2 -translate-x-1/2 bg-black/70 backdrop-blur-sm text-white text-[10px] font-bold uppercase tracking-widest px-4 py-2 rounded-full flex items-center gap-2 opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none">
                                <InformationCircleIcon class="w-3 h-3" /> Vista previa con marca de agua
                            </div>
                        </div>
                    </div>

                    <div class="lg:col-span-4 space-y-8">
                        
                        <div>
                            <span class="text-[10px] uppercase tracking-widest text-slate-400 mb-1 block">ID: #{{ photo.unique_id }}</span>
                            <h1 class="text-3xl font-serif font-bold text-slate-900 mb-4 leading-tight">
                                {{ photo.title || 'Fotografía Artística' }}
                            </h1>
                            
                            <div class="flex flex-wrap gap-2 mb-8">
                                <span v-if="photo.event" class="text-[10px] font-bold uppercase tracking-wider bg-slate-100 text-slate-600 px-2 py-1 rounded-sm">
                                    {{ photo.event.name }}
                                </span>
                                <span class="text-[10px] font-bold uppercase tracking-wider bg-slate-100 text-slate-600 px-2 py-1 rounded-sm">
                                    {{ photo.width }} x {{ photo.height }} px
                                </span>
                            </div>
                            
                            <div class="border-t border-b border-gray-100 py-6 mb-6 flex items-baseline justify-between">
                                <span class="text-sm text-slate-500">Licencia Digital</span>
                                <span class="text-4xl font-serif font-bold text-slate-900">${{ photo.price }}</span>
                            </div>

                            <!--  Botón actualizado -->
                            <button @click="handlePurchaseClick" :disabled="loading || addingToCart"
                                class="w-full bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold uppercase tracking-widest py-4 rounded-sm transition-all shadow-md hover:shadow-lg flex items-center justify-center gap-2 disabled:opacity-70 disabled:cursor-not-allowed">
                                
                                <!-- Usuario autenticado: Agregar al carrito -->
                                <template v-if="isAuthenticated">
                                    <PlusIcon v-if="!addingToCart" class="w-4 h-4" />
                                    <span v-if="addingToCart">Agregando...</span>
                                    <span v-else>Agregar al Carrito</span>
                                </template>

                                <!-- Invitado: Comprar directo -->
                                <template v-else>
                                    <ShoppingCartIcon v-if="!loading" class="w-4 h-4" />
                                    <span v-if="loading">Iniciando...</span>
                                    <span v-else>Comprar Ahora</span>
                                </template>
                            </button>
                            
                            <p class="text-[10px] text-slate-400 text-center mt-3 leading-relaxed">
                                <template v-if="isAuthenticated">
                                    Agrega al carrito y paga todo junto
                                </template>
                                <template v-else>
                                    Pago seguro a través de Mercado Pago. <br> Entrega inmediata vía email.
                                </template>
                            </p>
                        </div>

                        <div class="bg-white border border-gray-200 rounded-sm p-6">
                            <h3 class="text-xs font-bold uppercase tracking-widest text-slate-900 mb-4 border-b border-gray-100 pb-2">
                                Autor
                            </h3>
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 bg-gray-100 rounded-sm overflow-hidden flex-shrink-0">
                                    <img v-if="photo.photographer?.profile_photo_url" :src="photo.photographer.profile_photo_url" class="w-full h-full object-cover" />
                                    <div v-else class="w-full h-full flex items-center justify-center text-slate-400 font-serif font-bold">
                                        {{ photo.photographer?.business_name?.charAt(0) || 'A' }}
                                    </div>
                                </div>
                                <div>
                                    <p class="text-sm font-bold text-slate-900">{{ photo.photographer?.business_name || 'Fotógrafo' }}</p>
                                    <Link v-if="photo.photographer?.slug" :href="route('photographers.show', photo.photographer.slug)" class="text-xs text-slate-500 hover:text-slate-900 underline decoration-1 underline-offset-2 transition-colors">
                                        Ver portafolio
                                    </Link>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- Fotos relacionadas (sin cambios) -->
                <div v-if="relatedPhotos && relatedPhotos.length > 0" class="mt-24 border-t border-gray-100 pt-12">
                    <h2 class="text-xl font-serif font-bold text-slate-900 mb-8">También podría interesarte</h2>
                    <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-5 gap-6">
                        <Link v-for="related in relatedPhotos" :key="related.id" 
                            :href="route('gallery.show', related.unique_id)"
                            class="group block relative"
                        >
                            <div class="aspect-square bg-gray-100 overflow-hidden rounded-sm relative mb-2">
                                <img :src="related.thumbnail_url" 
                                    class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105 filter grayscale-[0.3] group-hover:grayscale-0"
                                    @error="handleImageError" 
                                />
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-[10px] font-mono text-slate-400">#{{ related.unique_id }}</span>
                                <span class="text-xs font-bold text-slate-900">${{ related.price }}</span>
                            </div>
                        </Link>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal de email (sin cambios - solo para invitados) -->
        <div v-if="showEmailModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-slate-900/90 backdrop-blur-sm transition-opacity" @click="showEmailModal = false"></div>
            
            <div class="relative bg-white rounded-sm shadow-2xl w-full max-w-md overflow-hidden transform transition-all border border-gray-100">
                <div class="h-1 w-full bg-slate-900"></div>

                <div class="p-10">
                    <div class="flex justify-between items-start mb-8">
                        <div>
                            <span class="text-[10px] font-bold uppercase tracking-[0.2em] text-slate-400 block mb-2">Checkout</span>
                            <h3 class="text-2xl font-serif font-bold text-slate-900">Datos de Entrega</h3>
                        </div>
                        <button @click="showEmailModal = false" class="text-slate-400 hover:text-slate-900 transition">
                            <XMarkIcon class="w-6 h-6" />
                        </button>
                    </div>
                    
                    <form @submit.prevent="submitPurchase" class="space-y-6">
                        <div>
                            <label class="block text-[10px] font-bold uppercase tracking-widest text-slate-900 mb-2 flex items-center gap-2">
                                <EnvelopeIcon class="w-3 h-3" /> Correo Electrónico
                            </label>
                            <input 
                                v-model="guestEmail" 
                                type="email" 
                                required
                                class="w-full border-gray-300 rounded-sm focus:border-slate-900 focus:ring-0 text-slate-900 text-sm py-3 px-4 placeholder-gray-300 transition-colors"
                                placeholder="ejemplo@email.com"
                            >
                            <p v-if="emailError" class="text-red-600 text-xs mt-2 flex items-center gap-1">
                                <InformationCircleIcon class="w-3 h-3" /> {{ emailError }}
                            </p>
                        </div>

                        <label class="flex items-start cursor-pointer group p-4 border border-gray-100 rounded-sm hover:border-slate-300 transition-colors bg-gray-50/50">
                            <div class="flex items-center h-5">
                                <input 
                                    id="createAccount" 
                                    v-model="createAccount" 
                                    type="checkbox" 
                                    class="focus:ring-slate-900 h-4 w-4 text-slate-900 border-gray-300 rounded-sm cursor-pointer"
                                >
                            </div>
                            <div class="ml-3">
                                <span class="block text-xs font-bold uppercase tracking-wide text-slate-700 group-hover:text-slate-900 transition-colors">Crear cuenta automáticamente</span>
                                <span class="block text-[10px] text-slate-400 font-light mt-1">
                                    Guardaremos esta compra en su historial para futuras descargas. Se le enviará una contraseña temporal.
                                </span>
                            </div>
                        </label>

                        <div class="pt-4 flex flex-col gap-3">
                            <button type="submit" :disabled="loading" 
                                class="w-full py-4 bg-slate-900 text-white text-xs font-bold uppercase tracking-widest hover:bg-slate-800 transition-all shadow-md hover:shadow-lg disabled:opacity-70 disabled:cursor-not-allowed flex items-center justify-center gap-2">
                                <span v-if="loading">Procesando...</span>
                                <span v-else>Continuar al Pago</span>
                                <span v-if="!loading" class="text-lg leading-none">→</span>
                            </button>
                            
                            <button type="button" @click="showEmailModal = false" 
                                class="w-full py-3 text-slate-400 text-[10px] font-bold uppercase tracking-widest hover:text-slate-600 transition">
                                Cancelar
                            </button>
                        </div>
                    </form>
                </div>

                <div class="bg-gray-50 px-10 py-5 border-t border-gray-100 flex justify-center items-center gap-2">
                    <span class="text-[10px] text-slate-400 uppercase tracking-wider">¿Ya es miembro?</span>
                    <Link :href="route('login')" class="text-[10px] font-bold uppercase tracking-wider text-slate-900 border-b border-slate-900 pb-0.5 hover:text-slate-600 hover:border-slate-600 transition">
                        Iniciar Sesión
                    </Link>
                </div>
            </div>
        </div>

        <!-- Modal imagen completa (sin cambios) -->
        <div v-if="showFullImage" class="fixed inset-0 z-50 bg-black/95 backdrop-blur-sm flex items-center justify-center" @click="showFullImage = false">
            <button class="absolute top-6 right-6 text-white/50 hover:text-white transition z-10">
                <XMarkIcon class="w-10 h-10" />
            </button>
            <img :src="photo.watermarked_url || photo.thumbnail_url" class="max-h-[95vh] max-w-[95vw] object-contain shadow-2xl" @click.stop />
        </div>

    </AppLayout>
</template>
