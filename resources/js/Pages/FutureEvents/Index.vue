<script setup>
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import FutureEventsSection from '@/Components/FutureEventsSection.vue';

const props = defineProps({
    auth: Object,
});

const isAuthenticated = computed(() => !!props.auth?.user);
const userRole = computed(() => props.auth?.user?.role || null);
</script>

<template>
    <AppLayout>
        <Head title="Eventos Futuros" />

        <FutureEventsSection
            :is-authenticated="isAuthenticated"
            :user-role="userRole"
        />
    </AppLayout>
</template>
