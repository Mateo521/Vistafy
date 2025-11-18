<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { ref, computed } from 'vue';
import { router, usePage } from '@inertiajs/vue3';

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

// Verificar si el usuario está autenticado
const isAuthenticated = computed(() => page.props.auth?.user !== null);

/**
 * Manejar click en botón de compra
 */
const handlePurchaseClick = () => {
    if (isAuthenticated.value) {
        // Usuario autenticado: comprar directamente
        submitPurchase();
    } else {
        // Usuario invitado: pedir email
        showEmailModal.value = true;
    }
};

/**
 * Enviar solicitud de compra
 */
const submitPurchase = async () => {
    if (loading.value) return;

    // Validar email si es invitado
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
            // Redirigir a Mercado Pago
            const initPoint = response.data.sandbox_init_point || response.data.init_point;
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

</script>

<template>

    <Head :title="`Foto ${photo.unique_id}`" />

    <AppLayout>

        <div class="min-h-screen bg-gray-50">


            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                <!-- Back Button -->
                <Link href="/galeria"
                    class="inline-flex items-center text-indigo-600 hover:text-indigo-800 font-medium mb-6 transition">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Volver a la galería
                </Link>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <!-- Image Section -->
                    <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                        <div class="relative">
                            <!-- Main Image -->
                            <img :src="photo.preview_url || photo.thumbnail_url" :alt="photo.title"
                                class="w-full h-auto cursor-pointer" @click="showFullImage = true"
                                @error="(e) => e.target.src = 'https://via.placeholder.com/800?text=Imagen+no+disponible'" />

                            <!-- Watermark Notice -->
                            <div
                                class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black/70 to-transparent p-4">
                                <p class="text-white text-sm text-center">
                                    <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                    Vista previa con marca de agua
                                </p>
                            </div>

                            <!-- Zoom Icon -->
                            <button @click="showFullImage = true"
                                class="absolute top-4 right-4 bg-white/90 backdrop-blur p-2 rounded-lg shadow hover:bg-white transition">
                                <svg class="w-6 h-6 text-gray-700" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7" />
                                </svg>
                            </button>
                        </div>

                        <!-- Photo Info -->
                        <div class="p-6 border-t">
                            <div class="grid grid-cols-2 gap-4 text-sm">
                                <div>
                                    <span class="text-gray-500">ID:</span>
                                    <span class="font-semibold text-gray-900 ml-2">{{ photo.unique_id }}</span>
                                </div>
                                <div>
                                    <span class="text-gray-500">Dimensiones:</span>
                                    <span class="font-semibold text-gray-900 ml-2">{{ photo.width }} x {{ photo.height
                                        }}</span>
                                </div>
                                <div>
                                    <span class="text-gray-500">Descargas:</span>
                                    <span class="font-semibold text-gray-900 ml-2">{{ photo.downloads }}</span>
                                </div>
                                <div>
                                    <span class="text-gray-500">Subida:</span>
                                    <span class="font-semibold text-gray-900 ml-2">{{ photo.created_at }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Details Section -->
                    <div>
                        <!-- Title & Description -->
                        <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
                            <h1 class="text-3xl font-bold text-gray-900 mb-2">{{ photo.title }}</h1>
                            <p v-if="photo.description" class="text-gray-600 mb-4">{{ photo.description }}</p>

                            <!-- Events -->
                            <div v-if="photo.events && photo.events.length > 0" class="mb-4">
                                <h3 class="text-sm font-semibold text-gray-700 mb-2">Eventos:</h3>
                                <div class="flex flex-wrap gap-2">
                                    <span v-for="event in photo.events" :key="event.id"
                                        class="bg-indigo-100 text-indigo-800 px-3 py-1 rounded-full text-sm font-medium">
                                        {{ event.name }}
                                    </span>
                                </div>
                            </div>

                            <!-- Price -->
                            <div class="flex items-center justify-between py-4 border-t border-b">
                                <span class="text-gray-600 font-medium">Precio:</span>
                                <span class="text-4xl font-bold text-indigo-600">${{ photo.price }}</span>
                            </div>

                            <!-- Purchase Button -->
                            <!--div v-if="!photo.has_purchased" class="mt-6">
                                <button
                                    class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-4 px-6 rounded-lg shadow-lg transition transform hover:scale-105 flex items-center justify-center gap-2">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                                    </svg>
                                    Comprar Ahora
                                </button>
                                <p class="text-center text-sm text-gray-500 mt-3">
                                    Recibirás la imagen en alta resolución sin marca de agua
                                </p>
                            </div>

                           
                            <div v-else class="mt-6">
                                <a :href="photo.download_url" download
                                    class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-4 px-6 rounded-lg shadow-lg transition flex items-center justify-center gap-2">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                    </svg>
                                    Descargar Foto
                                </a>
                                <p class="text-center text-sm text-green-600 mt-3 font-medium">
                                    ✓ Ya compraste esta foto
                                </p>
                            </div-->

                            <div class="mt-6">
                                <button @click="handlePurchaseClick" :disabled="loading"
                                    class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                                    <span v-if="!loading">
                                        💳 Comprar Ahora - ${{ photo.price || '1000' }}
                                    </span>
                                    <span v-else>
                                        ⏳ Procesando...
                                    </span>
                                </button>

                                <!-- Mensaje informativo -->
                                <p class="text-sm text-gray-600 mt-2 text-center">
                                    <template v-if="$page.props.auth.user">
                                        ✅ Compra asociada a tu cuenta
                                    </template>
                                    <template v-else>
                                        💡 Ingresa tu email para recibir la foto
                                    </template>
                                </p>
                            </div>

                            <!-- 🆕 MODAL PARA EMAIL (si no está autenticado) -->
                            <teleport to="body">
                                <div v-if="showEmailModal"
                                    class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4"
                                    @click.self="showEmailModal = false">
                                    <div class="bg-white rounded-lg shadow-xl max-w-md w-full p-6">
                                        <!-- Header -->
                                        <div class="flex justify-between items-center mb-4">
                                            <h3 class="text-xl font-bold text-gray-900">
                                                📧 Ingresa tu Email
                                            </h3>
                                            <button @click="showEmailModal = false"
                                                class="text-gray-400 hover:text-gray-600">
                                                ✕
                                            </button>
                                        </div>

                                        <!-- Body -->
                                        <p class="text-gray-600 mb-4">
                                            Te enviaremos la foto a este email después del pago
                                        </p>

                                        <!-- Form -->
                                        <form @submit.prevent="submitPurchase">
                                            <div class="mb-4">
                                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                                    Email *
                                                </label>
                                                <input v-model="guestEmail" type="email" required
                                                    placeholder="tu@email.com"
                                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                                <p v-if="emailError" class="text-red-500 text-sm mt-1">
                                                    {{ emailError }}
                                                </p>
                                            </div>

                                            <!-- Checkbox opcional para crear cuenta -->
                                            <div class="mb-4">
                                                <label class="flex items-start">
                                                    <input v-model="createAccount" type="checkbox" class="mt-1 mr-2">
                                                    <span class="text-sm text-gray-600">
                                                        Crear una cuenta para guardar mi historial de compras
                                                    </span>
                                                </label>
                                            </div>

                                            <!-- Botones -->
                                            <div class="flex gap-3">
                                                <button type="button" @click="showEmailModal = false"
                                                    class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">
                                                    Cancelar
                                                </button>
                                                <button type="submit" :disabled="loading"
                                                    class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50">
                                                    <span v-if="!loading">Continuar</span>
                                                    <span v-else>⏳</span>
                                                </button>
                                            </div>
                                        </form>

                                        <!-- Link para login -->
                                        <div class="mt-4 text-center">
                                            <p class="text-sm text-gray-600">
                                                ¿Ya tienes cuenta?
                                                <a :href="route('login')" class="text-blue-600 hover:underline">
                                                    Inicia sesión
                                                </a>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </teleport>








                        </div>

                        <!-- Photographer Info -->
                        <div class="bg-white rounded-lg shadow-lg p-6">
                            <h3 class="text-lg font-bold text-gray-900 mb-4">Sobre el Fotógrafo</h3>

                            <div class="flex items-start gap-4">
                                <!-- Avatar -->
                                <div
                                    class="w-16 h-16 bg-indigo-100 rounded-full flex items-center justify-center flex-shrink-0">
                                    <svg class="w-8 h-8 text-indigo-600" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                </div>

                                <!-- Info -->
                                <div class="flex-1">
                                    <h4 class="font-bold text-gray-900">{{ photo.photographer.business_name }}</h4>
                                    <p v-if="photo.photographer.region" class="text-sm text-gray-600">{{
                                        photo.photographer.region }}</p>
                                    <p v-if="photo.photographer.bio" class="text-sm text-gray-600 mt-2">{{
                                        photo.photographer.bio }}</p>

                                    <!-- Contact -->
                                    <div class="mt-4 space-y-2">
                                        <a v-if="photo.photographer.phone" :href="`tel:${photo.photographer.phone}`"
                                            class="flex items-center text-sm text-indigo-600 hover:text-indigo-800">
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                            </svg>
                                            {{ photo.photographer.phone }}
                                        </a>
                                        <a v-if="photo.photographer.email" :href="`mailto:${photo.photographer.email}`"
                                            class="flex items-center text-sm text-indigo-600 hover:text-indigo-800">
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                            </svg>
                                            {{ photo.photographer.email }}
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Related Photos -->
                <div v-if="relatedPhotos && relatedPhotos.length > 0" class="mt-16">
                    <h2 class="text-2xl font-bold text-gray-900 mb-6">Fotos Relacionadas</h2>
                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4">
                        <Link v-for="related in relatedPhotos" :key="related.id"
                            :href="route('gallery.show', related.unique_id)"
                            class="group bg-white rounded-lg shadow overflow-hidden hover:shadow-xl transition">
                        <div class="aspect-square overflow-hidden bg-gray-100">
                            <img :src="related.thumbnail_url" :alt="related.title"
                                class="w-full h-full object-cover group-hover:scale-110 transition duration-300"
                                @error="(e) => e.target.src = 'https://via.placeholder.com/400?text=Sin+Imagen'" />
                        </div>
                        <div class="p-3">
                            <div class="text-sm font-semibold text-gray-900 text-center truncate">{{ related.title }}
                            </div>
                            <div class="text-sm font-bold text-indigo-600 text-center mt-1">${{ related.price }}</div>
                        </div>
                        </Link>
                    </div>
                </div>
            </div>

            <!-- Full Image Modal -->
            <div v-if="showFullImage" @click="showFullImage = false"
                class="fixed inset-0 bg-black bg-opacity-90 z-50 flex items-center justify-center p-4 cursor-zoom-out">
                <img :src="photo.preview_url || photo.thumbnail_url" :alt="photo.title"
                    class="max-w-full max-h-full object-contain"
                    @error="(e) => e.target.src = 'https://via.placeholder.com/800?text=Imagen+no+disponible'" />
                <button @click.stop="showFullImage = false"
                    class="absolute top-4 right-4 bg-white rounded-full p-3 shadow-lg hover:bg-gray-100 transition">
                    <svg class="w-6 h-6 text-gray-900" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>


        </div>
    </AppLayout>

</template>