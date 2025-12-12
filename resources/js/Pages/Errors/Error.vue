<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { HomeIcon, ExclamationTriangleIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
    status: Number,
    message: String
});

const titles = {
    403: 'Acceso Denegado',
    401: 'No Autorizado',
    500: 'Error del Servidor',
    503: 'Servicio No Disponible'
};

const title = titles[props.status] || 'Error';
</script>

<template>
    <Head :title="title" />

    <AppLayout>
        <div class="min-h-screen bg-gradient-to-br from-red-50 to-red-100 flex items-center justify-center px-4 py-16">
            <div class="max-w-2xl w-full text-center">
                
                <div class="mb-8">
                    <ExclamationTriangleIcon class="w-24 h-24 text-red-500 mx-auto mb-6" />
                    
                    <h1 class="text-6xl font-serif font-bold text-slate-900 mb-4">
                        {{ status }}
                    </h1>
                    
                    <h2 class="text-3xl font-serif font-bold text-slate-900 mb-4">
                        {{ title }}
                    </h2>
                    
                    <p class="text-lg text-slate-600 mb-8">
                        {{ message }}
                    </p>
                </div>

                <div class="flex flex-col sm:flex-row gap-4 justify-center items-center">
                    <Link 
                        :href="route('home')"
                        class="inline-flex items-center gap-2 bg-slate-900 text-white font-medium px-8 py-4 rounded-lg hover:bg-slate-800 transition-all shadow-lg"
                    >
                        <HomeIcon class="w-5 h-5" />
                        <span>Volver al inicio</span>
                    </Link>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
