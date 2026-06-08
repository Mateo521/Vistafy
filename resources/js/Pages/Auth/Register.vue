<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
/*
import CustomCursor from '@/Components/CustomCursor.vue';
*/
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>

    <GuestLayout>

        <Head title="Registro de Usuario" />

        <div class="text-center mb-10">
            <span class="text-xs font-bold  text-white/60 uppercase mb-3 block">
                Nuevos miembros
            </span>
            <h1 class="text-3xl md:text-4xl font-sans font-bold text-white mb-4">
                Crear cuenta
            </h1>
            <p class="text-white/60 text-sm font-light max-w-xs mx-auto leading-relaxed">
                Unite a nuestra plataforma para acceder a galerías exclusivas y gestionar sus pedidos fotográficos.
            </p>
        </div>

        <form @submit.prevent="submit" class="space-y-5">

            <div>
                <label for="name" class="block text-[10px] font-bold uppercase tracking-widest text-white/70 mb-2">
                    Nombre Completo
                </label>
                <TextInput id="name" type="text"
                    class="block w-full bg-white/5 border-white/20 text-white placeholder-white/20 rounded-sm focus:border-white focus:ring-0 transition-colors py-3 px-4 text-sm"
                    v-model="form.name" placeholder="Juan Pérez" required autofocus autocomplete="name" />
                <InputError class="mt-2 text-red-300 text-xs" :message="form.errors.name" />
            </div>

            <div>
                <label for="email" class="block text-[10px] font-bold uppercase tracking-widest text-white/70 mb-2">
                    Correo Electrónico
                </label>
                <TextInput id="email" type="email"
                    class="block w-full bg-white/5 border-white/20 text-white placeholder-white/20 rounded-sm focus:border-white focus:ring-0 transition-colors py-3 px-4 text-sm"
                    v-model="form.email" placeholder="cliente@ejemplo.com" required autocomplete="username" />
                <InputError class="mt-2 text-red-300 text-xs" :message="form.errors.email" />
            </div>

            <div>
                <label for="password" class="block text-[10px] font-bold uppercase tracking-widest text-white/70 mb-2">
                    Contraseña
                </label>
                <TextInput id="password" type="password"
                    class="block w-full bg-white/5 border-white/20 text-white placeholder-white/20 rounded-sm focus:border-white focus:ring-0 transition-colors py-3 px-4 text-sm"
                    v-model="form.password" placeholder="Mínimo 8 caracteres" required autocomplete="new-password" />
                <InputError class="mt-2 text-red-300 text-xs" :message="form.errors.password" />
            </div>

            <div>
                <label for="password_confirmation"
                    class="block text-[10px] font-bold uppercase tracking-widest text-white/70 mb-2">
                    Confirmar Contraseña
                </label>
                <TextInput id="password_confirmation" type="password"
                    class="block w-full bg-white/5 border-white/20 text-white placeholder-white/20 rounded-sm focus:border-white focus:ring-0 transition-colors py-3 px-4 text-sm"
                    v-model="form.password_confirmation" placeholder="Repita su contraseña" required
                    autocomplete="new-password" />
                <InputError class="mt-2 text-red-300 text-xs" :message="form.errors.password_confirmation" />
            </div>

            <div class="pt-6">
                <PrimaryButton
                    class="w-full justify-center !text-black bg-white hover:bg-gray-100 text-slate-900 font-bold py-4 rounded-sm uppercase tracking-widest text-xs border border-transparent transition-all duration-300"
                    :class="{ 'opacity-70 cursor-not-allowed': form.processing }" :disabled="form.processing">
                    <span v-if="form.processing">Registrando...</span>
                    <span v-else>Completar Registro</span>
                </PrimaryButton>
            </div>

            <p class="text-[10px] text-white/40 text-center leading-relaxed px-4">
                Al registrarse, usted acepta nuestros
                <a href="#" class="underline hover:text-white transition">Términos de Servicio</a>
                y confirma haber leído nuestra
                <a href="#" class="underline hover:text-white transition">Política de Privacidad</a>.
            </p>

            <div class="relative my-8">
                <div class="absolute inset-0 flex items-center">
                    <div class="w-full border-t border-white/10"></div>
                </div>
                <div class="relative flex justify-center">
                    <span class="px-4 bg-transparent text-[10px] uppercase tracking-widest text-white/40">o registrarse
                        con</span>
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
                    ¿Ya tenés una cuenta?
                    <Link :href="route('login')" class="text-white font-bold hover:underline ml-1 transition">
                        Iniciar Sesión
                    </Link>
                </p>
            </div>
        </template>
    </GuestLayout>
</template>