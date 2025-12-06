<script setup>
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const passwordInput = ref(null);
const currentPasswordInput = ref(null);

const form = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

const updatePassword = () => {
    form.put(route('password.update'), {
        preserveScroll: true,
        onSuccess: () => form.reset(),
        onError: () => {
            if (form.errors.password) {
                form.reset('password', 'password_confirmation');
                passwordInput.value.focus();
            }
            if (form.errors.current_password) {
                form.reset('current_password');
                currentPasswordInput.value.focus();
            }
        },
    });
};
</script>

<template>
    <section class="bg-white border border-gray-200 rounded-sm p-8 shadow-sm">
        <header>
            <h2 class="text-lg font-serif font-bold text-slate-900">
                Actualizar Contraseña
            </h2>

            <p class="mt-1 text-sm text-slate-500 font-light">
                Asegúrese de que su cuenta utilice una contraseña larga y segura para mantener la protección de sus datos.
            </p>
        </header>

        <form @submit.prevent="updatePassword" class="mt-6 space-y-6">
            <div>
                <InputLabel for="current_password" value="Contraseña Actual" class="block text-[10px] font-bold uppercase tracking-widest text-slate-500 mb-2" />

                <TextInput
                    id="current_password"
                    ref="currentPasswordInput"
                    v-model="form.current_password"
                    type="password"
                    class="block w-full border-gray-300 rounded-sm focus:border-slate-900 focus:ring-0 text-slate-900 text-sm"
                    autocomplete="current-password"
                />

                <InputError :message="form.errors.current_password" class="mt-2 text-xs text-red-600" />
            </div>

            <div>
                <InputLabel for="password" value="Nueva Contraseña" class="block text-[10px] font-bold uppercase tracking-widest text-slate-500 mb-2" />

                <TextInput
                    id="password"
                    ref="passwordInput"
                    v-model="form.password"
                    type="password"
                    class="block w-full border-gray-300 rounded-sm focus:border-slate-900 focus:ring-0 text-slate-900 text-sm"
                    autocomplete="new-password"
                />

                <InputError :message="form.errors.password" class="mt-2 text-xs text-red-600" />
            </div>

            <div>
                <InputLabel for="password_confirmation" value="Confirmar Contraseña" class="block text-[10px] font-bold uppercase tracking-widest text-slate-500 mb-2" />

                <TextInput
                    id="password_confirmation"
                    v-model="form.password_confirmation"
                    type="password"
                    class="block w-full border-gray-300 rounded-sm focus:border-slate-900 focus:ring-0 text-slate-900 text-sm"
                    autocomplete="new-password"
                />

                <InputError :message="form.errors.password_confirmation" class="mt-2 text-xs text-red-600" />
            </div>

            <div class="flex items-center gap-4 pt-4 border-t border-gray-100">
                <PrimaryButton 
                    :disabled="form.processing"
                    class="bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold uppercase tracking-widest px-6 py-3 rounded-sm transition shadow-md"
                >
                    Guardar
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