<script setup>
import { useToast } from '@/Composables/useToast';
import Toast from '@/Components/Toast.vue';

const { toasts, remove } = useToast();
</script>

<template>
    <div class="fixed top-4 right-4 z-50 space-y-3 pointer-events-none">
        <Toast
            v-for="toast in toasts"
            :key="toast.id"
            :show="toast.visible"
            :type="toast.type"
            :message="toast.message"
            :duration="toast.duration"
            @close="remove(toast.id)"
            class="pointer-events-auto"
        />
    </div>
</template>
