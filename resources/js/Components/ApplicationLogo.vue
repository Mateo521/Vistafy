<script setup>
defineProps({
    variant: {
        type: String,
        default: 'default', // 'default', 'dark', 'icon'
    },
    class: {
        type: String,
        default: '',
    },
});
</script>

<template>
    <!-- Logo SVG Inline (Recomendado para mejor control) -->
    <svg v-if="variant === 'icon'" :class="class" viewBox="0 0 100 100" fill="currentColor"
        xmlns="http://www.w3.org/2000/svg">
        <!--  Reemplazar -->
        <circle cx="50" cy="50" r="45" fill="currentColor" />
        <text x="50" y="70" font-size="60" text-anchor="middle" fill="white" font-weight="bold">V</text>
    </svg>

    <!-- Logo PNG (para diseños complejos) -->
    <img v-else-if="variant === 'dark'" :src="'/images/logo-dark.png'" alt="Empresa Logo" :class="class" />

    <!-- Logo por defecto -->
    <img v-else :src="'/images/logo.png'" alt="Empresa Logo" :class="class" />
</template>
