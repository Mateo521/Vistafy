<script setup>
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Link, useForm, usePage } from '@inertiajs/vue3';

defineProps({
    mustVerifyEmail: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const user = usePage().props.auth.user;

const form = useForm({
    name: user.name,
    email: user.email,
});
</script>

<template>
    <section class="bg-white border border-gray-200 rounded-sm p-8 shadow-sm">
        <header>
            <h2 class="text-lg font-serif font-bold text-slate-900">
                Información del Perfil
            </h2>

            <p class="mt-1 text-sm text-slate-500 font-light">
                Actualice la información de su cuenta y la dirección de correo electrónico asociada.
            </p>
        </header>

        <form @submit.prevent="form.patch(route('profile.update'))" class="mt-6 space-y-6">
            <div>
                <InputLabel for="name" value="Nombre Completo" class="block text-[10px] font-bold uppercase tracking-widest text-slate-500 mb-2" />

                <TextInput
                    id="name"
                    type="text"
                    class="block w-full border-gray-300 rounded-sm focus:border-slate-900 focus:ring-0 text-slate-900 text-sm"
                    v-model="form.name"
                    required
                    autofocus
                    autocomplete="name"
                />

                <InputError class="mt-2 text-xs text-red-600" :message="form.errors.name" />
            </div>

            <div>
                <InputLabel for="email" value="Correo Electrónico" class="block text-[10px] font-bold uppercase tracking-widest text-slate-500 mb-2" />

                <TextInput
                    id="email"
                    type="email"
                    class="block w-full border-gray-300 rounded-sm focus:border-slate-900 focus:ring-0 text-slate-900 text-sm"
                    v-model="form.email"
                    required
                    autocomplete="username"
                />

                <InputError class="mt-2 text-xs text-red-600" :message="form.errors.email" />
            </div>

            <div v-if="mustVerifyEmail && user.email_verified_at === null">
                <div class="mt-2 p-3 bg-amber-50 border border-amber-200 rounded-sm">
                    <p class="text-xs text-amber-800">
                        Su dirección de correo no está verificada.
                        <Link
                            :href="route('verification.send')"
                            method="post"
                            as="button"
                            class="underline text-amber-900 font-bold hover:text-slate-900 rounded-sm focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-amber-500 ml-1"
                        >
                            Haga clic aquí para reenviar el correo de verificación.
                        </Link>
                    </p>
                </div>

                <div
                    v-show="status === 'verification-link-sent'"
                    class="mt-2 text-xs font-medium text-emerald-600 flex items-center gap-1"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                    Se ha enviado un nuevo enlace de verificación a su correo.
                </div>
            </div>

            <div class="flex items-center gap-4 pt-4 border-t border-gray-100">
                <PrimaryButton 
                    :disabled="form.processing"
                    class="bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold uppercase tracking-widest px-6 py-3 rounded-sm transition shadow-md"
                >
                    Guardar Cambios
                </PrimaryButton>

                <Transition
                    enter-active-class="transition ease-in-out"
                    enter-from-class="opacity-0"
                    leave-active-class="transition ease-in-out"
                    leave-to-class="opacity-0"
                >
                    <p v-if="form.recentlySuccessful" class="text-xs text-emerald-600 font-medium flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                        Guardado
                    </p>
                </Transition>
            </div>
        </form>
    </section>
</template>