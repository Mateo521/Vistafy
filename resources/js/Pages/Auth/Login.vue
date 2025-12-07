<script setup>
import Checkbox from '@/Components/Checkbox.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
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
        <Head title="Acceso Seguro" />

        <div class="text-center mb-10">
            <span class="text-xs font-bold tracking-[0.2em] text-white/60 uppercase mb-3 block">
                Portal de Usuarios
            </span>
            <h1 class="text-3xl md:text-4xl font-serif font-bold text-white mb-4">
                Credenciales de Acceso
            </h1>
            <p class="text-white/60 text-sm font-light max-w-xs mx-auto leading-relaxed">
                Ingrese sus datos para acceder al panel de gestión y galerías privadas.
            </p>
        </div>

        <div v-if="status" class="mb-6 p-4 bg-emerald-500/10 border border-emerald-500/20 rounded-sm text-emerald-200 text-xs font-medium text-center uppercase tracking-wide">
            {{ status }}
        </div>

        <form @submit.prevent="submit" class="space-y-6">
            <div>
                <label for="email" class="block text-[10px] font-bold uppercase tracking-widest text-white/70 mb-2">
                    Correo Electrónico
                </label>
                <TextInput
                    id="email"
                    type="email"
                    class="block w-full bg-white/5 border-white/20 text-white placeholder-white/20 rounded-sm focus:border-white focus:ring-0 transition-colors py-3 px-4 text-sm"
                    v-model="form.email"
                    placeholder="ejemplo@empresa.com"
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

            <div class="grid grid-cols-2 gap-4">
                <button type="button" disabled class="flex items-center justify-center px-4 py-3 bg-transparent border border-white/20 rounded-sm text-white/60 font-medium text-xs hover:bg-white/5 hover:text-white hover:border-white/40 transition cursor-not-allowed opacity-50">
                    <svg class="w-4 h-4 mr-2" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" />
                        <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" />
                        <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" />
                        <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" />
                    </svg>
                    Google
                </button>

                <button type="button" disabled class="flex items-center justify-center px-4 py-3 bg-transparent border border-white/20 rounded-sm text-white/60 font-medium text-xs hover:bg-white/5 hover:text-white hover:border-white/40 transition cursor-not-allowed opacity-50">
                    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 2C6.477 2 2 6.477 2 12c0 4.42 2.865 8.17 6.839 9.49.5.092.682-.217.682-.482 0-.237-.008-.866-.013-1.7-2.782.603-3.369-1.34-3.369-1.34-.454-1.156-1.11-1.463-1.11-1.463-.908-.62.069-.608.069-.608 1.003.07 1.531 1.03 1.531 1.03.892 1.529 2.341 1.087 2.91.832.092-.647.35-1.088.636-1.338-2.22-.253-4.555-1.11-4.555-4.943 0-1.091.39-1.984 1.029-2.683-.103-.253-.446-1.27.098-2.647 0 0 .84-.269 2.75 1.025A9.578 9.578 0 0112 6.836c.85.004 1.705.114 2.504.336 1.909-1.294 2.747-1.025 2.747-1.025.546 1.377.203 2.394.1 2.647.64.699 1.028 1.592 1.028 2.683 0 3.842-2.339 4.687-4.566 4.935.359.309.678.919.678 1.852 0 1.336-.012 2.415-.012 2.743 0 .267.18.578.688.48C19.138 20.167 22 16.418 22 12c0-5.523-4.477-10-10-10z"/>
                    </svg>
                    GitHub
                </button>
            </div>
        </form>

        <template #footer>
            <div class="text-center mt-8">
                <p class="text-white/50 text-xs font-light">
                    ¿Aún no tiene cuenta?
                    <Link :href="route('register')" class="text-white font-bold hover:underline ml-1 transition">
                        Solicitar registro
                    </Link>
                </p>
            </div>
        </template>
    </GuestLayout>
</template>