<script setup>
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div class="min-h-screen flex items-center justify-center relative overflow-hidden">
        <!-- Fondo animado con gradiente -->
        <div class="absolute inset-0 bg-gradient-to-br from-indigo-600 via-purple-600 to-pink-500">
            <!-- Formas animadas de fondo -->
            <div class="absolute top-0 left-0 w-96 h-96 bg-white/10 rounded-full blur-3xl animate-blob"></div>
            <div
                class="absolute top-0 right-0 w-96 h-96 bg-white/10 rounded-full blur-3xl animate-blob animation-delay-2000">
            </div>
            <div
                class="absolute bottom-0 left-1/2 w-96 h-96 bg-white/10 rounded-full blur-3xl animate-blob animation-delay-4000">
            </div>
        </div>

        <!-- Contenedor del formulario con glassmorphism -->
        <div class="relative z-10 w-full max-w-md px-6 py-8">
            <!-- Logo -->
            <div class="flex justify-center mb-8">
                <Link :href="route('home')" class="transform hover:scale-105 transition duration-300">
                <ApplicationLogo class="h-16 w-auto" />
                </Link>
            </div>

            <!-- Card con efecto vidrio -->
            <div class="bg-white/10 backdrop-blur-xl rounded-3xl shadow-2xl border border-white/20 p-8">
                <slot />
            </div>

            <!-- Link alternativo (Login/Register) -->
            <div class="mt-6 text-center">
                <slot name="footer" />
            </div>
        </div>
    </div>
</template>

<style scoped>
@keyframes blob {

    0%,
    100% {
        transform: translate(0, 0) scale(1);
    }

    33% {
        transform: translate(30px, -50px) scale(1.1);
    }

    66% {
        transform: translate(-20px, 20px) scale(0.9);
    }
}

.animate-blob {
    animation: blob 7s infinite;
}

.animation-delay-2000 {
    animation-delay: 2s;
}

.animation-delay-4000 {
    animation-delay: 4s;
}
</style>
