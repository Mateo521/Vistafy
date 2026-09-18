<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ShieldCheckIcon } from '@heroicons/vue/24/outline';

const form = useForm({
    code: '',
});

const submit = () => {
    form.post(route('2fa.verify'), {
        onFinish: () => form.reset('code'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Verificación en dos pasos" />

        <div class="mb-6 text-center">
            <ShieldCheckIcon class="w-12 h-12 text-slate-900 mx-auto mb-4" />
            <h2 class="text-xl font-bold text-slate-900">Autenticación de dos factores</h2>
            <p class="text-sm text-slate-500 mt-2">
                Por favor, abrí la aplicación de autenticación en tu dispositivo móvil e ingresá el código de 6 dígitos.
            </p>
        </div>

        <form @submit.prevent="submit">
            <div>
                <label for="code" class="block text-xs font-bold uppercase tracking-wide text-slate-500 mb-2 text-center">
                    Código de seguridad
                </label>
                
                <input
                    id="code"
                    type="text"
                    inputmode="numeric"
                    maxlength="6"
                    pattern="[0-9]*"
                    class="block w-full text-center tracking-[0.75em] font-mono text-2xl py-3 border-gray-300 rounded-sm focus:border-slate-900 focus:ring-0 text-slate-900"
                    v-model="form.code"
                    required
                    autofocus
                    autocomplete="one-time-code"
                />

                <p v-if="form.errors.code" class="text-red-600 text-xs mt-2 text-center">
                    {{ form.errors.code }}
                </p>
            </div>

            <div class="mt-8 flex justify-center">
                <button 
                    type="submit" 
                    :disabled="form.processing"
                    class="w-full justify-center bg-slate-900 text-white px-6 py-3 rounded-sm text-xs font-bold uppercase tracking-widest hover:bg-slate-800 transition shadow-sm disabled:opacity-50"
                >
                    <span v-if="form.processing">Verificando...</span>
                    <span v-else>Verificar Código</span>
                </button>
            </div>
            
            <div class="mt-6 text-center">
                <a :href="route('login')" class="text-xs text-slate-500 hover:text-slate-900 underline transition">
                    Volver al inicio de sesión
                </a>
            </div>
        </form>
    </GuestLayout>
</template>