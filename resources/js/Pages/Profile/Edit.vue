<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import DeleteUserForm from './Partials/DeleteUserForm.vue';
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue';
import { Head } from '@inertiajs/vue3';

defineProps({
    mustVerifyEmail: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});
</script>

<template>
    <Head title="Mi Perfil" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-serif font-bold text-2xl text-slate-900 leading-tight">
                Configuración de Cuenta
            </h2>
        </template>

        <div class="py-12 bg-gray-50">
            <div class="mx-auto max-w-7xl space-y-8 sm:px-6 lg:px-8">
                
                <UpdateProfileInformationForm
                    :must-verify-email="mustVerifyEmail"
                    :status="status"
                />

                <UpdatePasswordForm />

                <DeleteUserForm />
                
            </div>
        </div>
    </AuthenticatedLayout>
</template>