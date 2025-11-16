<template>
    <PhotographerLayout>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <!-- Portada -->
            <div class="relative h-64 rounded-xl overflow-hidden shadow-lg mb-8">
                <img 
                    v-if="photographer.cover_photo_url"
                    :src="photographer.cover_photo_url" 
                    class="w-full h-full object-cover"
                    alt="Portada"
                >
                <div 
                    v-else
                    class="w-full h-full bg-gradient-to-r from-indigo-500 to-purple-600"
                ></div>
                
                <!-- Foto de perfil superpuesta -->
                <div class="absolute bottom-0 left-8 transform translate-y-1/2">
                    <div class="relative">
                        <img 
                            v-if="photographer.profile_photo_url"
                            :src="photographer.profile_photo_url" 
                            class="w-32 h-32 rounded-full border-4 border-white shadow-xl object-cover"
                            :alt="photographer.business_name"
                        >
                        <div 
                            v-else
                            class="w-32 h-32 rounded-full border-4 border-white shadow-xl bg-indigo-600 flex items-center justify-center text-white text-4xl font-bold"
                        >
                            {{ photographer.business_name.charAt(0).toUpperCase() }}
                        </div>
                    </div>
                </div>

                <!-- Botón editar -->
                <Link 
                    :href="route('photographer.profile.edit')"
                    class="absolute top-4 right-4 bg-white text-gray-700 px-4 py-2 rounded-lg shadow-lg hover:bg-gray-100 transition font-semibold"
                >
                    <svg class="inline w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                    Editar Perfil
                </Link>
            </div>

            <!-- Información -->
            <div class="mt-20">
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">{{ photographer.business_name }}</h1>
                        <p class="text-gray-500 mt-1">
                            <svg class="inline w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            Región: <span class="font-semibold">{{ photographer.region }}</span>
                        </p>
                    </div>
                </div>

                <!-- Estadísticas -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                    <div class="bg-white rounded-lg shadow p-6 text-center">
                        <p class="text-3xl font-bold text-indigo-600">{{ photographer.total_photos }}</p>
                        <p class="text-gray-500 mt-1">Fotos Totales</p>
                    </div>
                    <div class="bg-white rounded-lg shadow p-6 text-center">
                        <p class="text-3xl font-bold text-green-600">{{ photographer.active_photos }}</p>
                        <p class="text-gray-500 mt-1">Fotos Activas</p>
                    </div>
                    <div class="bg-white rounded-lg shadow p-6 text-center">
                        <p class="text-3xl font-bold text-purple-600">{{ photographer.total_downloads }}</p>
                        <p class="text-gray-500 mt-1">Descargas</p>
                    </div>
                    <div class="bg-white rounded-lg shadow p-6 text-center">
                        <p class="text-3xl font-bold text-yellow-600">{{ photographer.total_events }}</p>
                        <p class="text-gray-500 mt-1">Eventos</p>
                    </div>
                </div>

                <!-- Biografía -->
                <div class="bg-white rounded-lg shadow p-6 mb-8">
                    <h2 class="text-xl font-bold text-gray-900 mb-4">Acerca de mí</h2>
                    <p v-if="photographer.bio" class="text-gray-700 leading-relaxed">
                        {{ photographer.bio }}
                    </p>
                    <p v-else class="text-gray-400 italic">No has agregado una biografía aún.</p>
                </div>

                <!-- Información de contacto -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-xl font-bold text-gray-900 mb-4">Información de Contacto</h2>
                    <div class="space-y-3">
                        <div class="flex items-center text-gray-700">
                            <svg class="w-5 h-5 mr-3 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                            <span>{{ $page.props.auth.user.email }}</span>
                        </div>
                        <div v-if="photographer.phone" class="flex items-center text-gray-700">
                            <svg class="w-5 h-5 mr-3 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                            </svg>
                            <span>{{ photographer.phone }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </PhotographerLayout>
</template>

<script setup>
import PhotographerLayout from '@/Layouts/PhotographerLayout.vue';
import { Link } from '@inertiajs/vue3';

defineProps({
    photographer: Object,
});
</script>
