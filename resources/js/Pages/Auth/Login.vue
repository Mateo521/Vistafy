<script setup>
import Checkbox from '@/Components/Checkbox.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
/*
import CustomCursor from '@/Components/CustomCursor.vue';
*/
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
 
    <GuestLayout>
        <Head title="Acceso" />

        <div class="text-center mb-10">
            <span class="text-xs font-bold  text-white/60 uppercase mb-3 block">
                Usuarios
            </span>
            <h1 class="text-3xl md:text-4xl font-sans font-bold text-white mb-4">
                 Acceso
            </h1>
            <p class="text-white/60 text-sm font-light max-w-xs mx-auto leading-relaxed">
                Ingresá tus datos para acceder.
            </p>
        </div>

        <div v-if="status" class="mb-6 p-4 bg-emerald-500/10 border border-emerald-500/20 rounded-sm text-emerald-200 text-xs font-medium text-center uppercase tracking-wide">
            {{ status }}
        </div>

        <form @submit.prevent="submit" class="space-y-6">
            <div>
                <label for="email" class="block text-[10px] font-bold uppercase tracking-widest text-white/70 mb-2">
                    Correo electrónico
                </label>
                <TextInput
                    id="email"
                    type="email"
                    class="block w-full bg-white/5 border-white/20 text-white placeholder-white/20 rounded-sm focus:border-white focus:ring-0 transition-colors py-3 px-4 text-sm"
                    v-model="form.email"
                    placeholder="ejemplo@f33.com"
                    required
                    autofocus
                    autocomplete="username"
                />
                <InputError class="mt-2 text-red-300 text-xs" :message="form.errors.email" />
            </div>

            <div>
                <label for="password" class="block text-[10px] font-bold uppercase tracking-widest text-white/70 mb-2">
                    Contraseña
                </label>
                <TextInput
                    id="password"
                    type="password"
                    class="block w-full bg-white/5 border-white/20 text-white placeholder-white/20 rounded-sm focus:border-white focus:ring-0 transition-colors py-3 px-4 text-sm"
                    v-model="form.password"
                    placeholder="••••••••"
                    required
                    autocomplete="current-password"
                />
                <InputError class="mt-2 text-red-300 text-xs" :message="form.errors.password" />
            </div>

            <div class="flex items-center justify-between mt-4">
                <label class="flex items-center cursor-pointer group">
                    <Checkbox 
                        name="remember" 
                        v-model:checked="form.remember"
                        class="rounded-sm border-white/30 bg-transparent text-slate-900 focus:ring-offset-slate-900 focus:ring-white"
                    />
                    <span class="ml-2 text-xs text-white/60 group-hover:text-white transition uppercase tracking-wide">
                        Recordar sesión
                    </span>
                </label>

                <Link
                    v-if="canResetPassword"
                    :href="route('password.request')"
                    class="text-xs text-white/60 hover:text-white transition underline underline-offset-4 decoration-white/30 hover:decoration-white"
                >
                    Recuperar contraseña
                </Link>
            </div>

            <div class="pt-4">
                <PrimaryButton
                    class="w-full justify-center !text-black bg-white hover:bg-gray-100 text-slate-900 font-bold py-4 rounded-sm uppercase tracking-widest text-xs border border-transparent transition-all duration-300"
                    :class="{ 'opacity-70 cursor-not-allowed': form.processing }"
                    :disabled="form.processing"
                >
                    <span v-if="form.processing">Procesando...</span>
                    <span v-else>Ingresar</span>
                </PrimaryButton>
            </div>

            <div class="relative my-8">
                <div class="absolute inset-0 flex items-center">
                    <div class="w-full border-t border-white/10"></div>
                </div>
                <div class="relative flex justify-center">
                    <span class="px-4 bg-transparent text-[10px] uppercase tracking-widest text-white/40">o acceder con</span>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-4">
                <a :href="route('auth.google')" 
   class="flex items-center justify-center px-4 py-3 bg-transparent border border-white/20 rounded-sm text-white font-bold text-xs hover:bg-white hover:text-black hover:border-white transition-all duration-300 uppercase tracking-widest cursor-pointer">
    <svg class="w-4 h-4 mr-3" viewBox="0 0 24 24" fill="currentColor">
        </svg>
    Google
</a>

                
            </div>
        </form>

        <template #footer>
            <div class="text-center mt-8">
                <p class="text-white/50 text-xs font-light">
                    Todavía no tenés cuenta?
                    <Link :href="route('register')" class="text-white font-bold hover:underline ml-1 transition">
                        Solicitar registro
                    </Link>
                </p>
            </div>
        </template>
    </GuestLayout>
</template>