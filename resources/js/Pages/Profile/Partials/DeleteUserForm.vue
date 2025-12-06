<script setup>
import DangerButton from '@/Components/DangerButton.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import Modal from '@/Components/Modal.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { useForm } from '@inertiajs/vue3';
import { nextTick, ref } from 'vue';

const confirmingUserDeletion = ref(false);
const passwordInput = ref(null);

const form = useForm({
    password: '',
});

const confirmUserDeletion = () => {
    confirmingUserDeletion.value = true;
    nextTick(() => passwordInput.value.focus());
};

const deleteUser = () => {
    form.delete(route('profile.destroy'), {
        preserveScroll: true,
        onSuccess: () => closeModal(),
        onError: () => passwordInput.value.focus(),
        onFinish: () => form.reset(),
    });
};

const closeModal = () => {
    confirmingUserDeletion.value = false;
    form.clearErrors();
    form.reset();
};
</script>

<template>
    <section class="bg-red-50/30 border border-red-100 rounded-sm p-8 shadow-sm">
        <header>
            <h2 class="text-lg font-serif font-bold text-red-900">
                Eliminar Cuenta
            </h2>

            <p class="mt-1 text-sm text-red-800/70 font-light leading-relaxed max-w-xl">
                Una vez que se elimine su cuenta, todos sus recursos y datos serán eliminados permanentemente. Antes de proceder, por favor descargue cualquier dato o información que desee conservar.
            </p>
        </header>

        <div class="mt-6">
            <DangerButton @click="confirmUserDeletion" class="rounded-sm text-xs font-bold uppercase tracking-widest px-6 py-3">
                Eliminar Cuenta
            </DangerButton>
        </div>

        <Modal :show="confirmingUserDeletion" @close="closeModal">
            <div class="p-8 bg-white rounded-sm">
                <h2 class="text-xl font-serif font-bold text-slate-900">
                    ¿Está seguro de que desea eliminar su cuenta?
                </h2>

                <p class="mt-4 text-sm text-slate-500 font-light leading-relaxed">
                    Esta acción es irreversible. Todos sus datos, fotografías y configuraciones serán eliminados permanentemente de nuestros servidores. Por favor, ingrese su contraseña para confirmar que desea eliminar su cuenta de forma definitiva.
                </p>

                <div class="mt-6">
                    <InputLabel for="password" value="Contraseña" class="sr-only" />

                    <TextInput
                        id="password"
                        ref="passwordInput"
                        v-model="form.password"
                        type="password"
                        class="mt-1 block w-3/4 border-gray-300 rounded-sm focus:border-red-500 focus:ring-red-500 text-sm"
                        placeholder="Contraseña"
                        @keyup.enter="deleteUser"
                    />

                    <InputError :message="form.errors.password" class="mt-2 text-xs text-red-600" />
                </div>

                <div class="mt-8 flex justify-end gap-3">
                    <SecondaryButton @click="closeModal" class="rounded-sm border-gray-300 text-slate-600 hover:text-slate-900 text-xs font-bold uppercase tracking-widest">
                        Cancelar
                    </SecondaryButton>

                    <DangerButton
                        class="rounded-sm text-xs font-bold uppercase tracking-widest bg-red-600 hover:bg-red-700"
                        :class="{ 'opacity-25': form.processing }"
                        :disabled="form.processing"
                        @click="deleteUser"
                    >
                        Confirmar Eliminación
                    </DangerButton>
                </div>
            </div>
        </Modal>
    </section>
</template>