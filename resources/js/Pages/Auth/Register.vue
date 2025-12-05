<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
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
        <Head title="Registro" />

        <!-- Título -->
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-white mb-2">
                Crea tu cuenta 
            </h1>
            <p class="text-white/80">
                Unite y Descubrí increíbles fotografías de pies
            </p>
        </div>

        <!-- Formulario -->
        <form @submit.prevent="submit" class="space-y-6">
            <!-- Nombre -->
            <div>
                <InputLabel for="name" value="Nombre Completo" class="text-white font-medium mb-2" />
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-white/50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                    </div>
                    <TextInput
                        id="name"
                        type="text"
                        class="block w-full pl-12 bg-white/10 backdrop-blur-sm border-white/20 text-white placeholder-white/50 rounded-xl focus:ring-2 focus:ring-white/50 focus:border-white/50 transition"
                        v-model="form.name"
                        placeholder="Juan Pérez"
                        required
                        autofocus
                        autocomplete="name"
                    />
                </div>
                <InputError class="mt-2 text-red-300" :message="form.errors.name" />
            </div>

            <!-- Email -->
            <div>
                <InputLabel for="email" value="Correo Electrónico" class="text-white font-medium mb-2" />
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-white/50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207" />
                        </svg>
                    </div>
                    <TextInput
                        id="email"
                        type="email"
                        class="block w-full pl-12 bg-white/10 backdrop-blur-sm border-white/20 text-white placeholder-white/50 rounded-xl focus:ring-2 focus:ring-white/50 focus:border-white/50 transition"
                        v-model="form.email"
                        placeholder="tu@email.com"
                        required
                        autocomplete="username"
                    />
                </div>
                <InputError class="mt-2 text-red-300" :message="form.errors.email" />
            </div>

            <!-- Password -->
            <div>
                <InputLabel for="password" value="Contraseña" class="text-white font-medium mb-2" />
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-white/50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                    </div>
                    <TextInput
                        id="password"
                        type="password"
                        class="block w-full pl-12 bg-white/10 backdrop-blur-sm border-white/20 text-white placeholder-white/50 rounded-xl focus:ring-2 focus:ring-white/50 focus:border-white/50 transition"
                        v-model="form.password"
                        placeholder="Mínimo 8 caracteres"
                        required
                        autocomplete="new-password"
                    />
                </div>
                <InputError class="mt-2 text-red-300" :message="form.errors.password" />
            </div>

            <!-- Confirm Password -->
            <div>
                <InputLabel for="password_confirmation" value="Confirmar Contraseña" class="text-white font-medium mb-2" />
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-white/50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <TextInput
                        id="password_confirmation"
                        type="password"
                        class="block w-full pl-12 bg-white/10 backdrop-blur-sm border-white/20 text-white placeholder-white/50 rounded-xl focus:ring-2 focus:ring-white/50 focus:border-white/50 transition"
                        v-model="form.password_confirmation"
                        placeholder="Repite tu contraseña"
                        required
                        autocomplete="new-password"
                    />
                </div>
                <InputError class="mt-2 text-red-300" :message="form.errors.password_confirmation" />
            </div>

            <!-- Botón de registro -->
            <PrimaryButton
                class="w-full justify-center bg-white hover:bg-white/90 text-indigo-600 font-bold py-3 rounded-xl shadow-lg hover:shadow-xl transform hover:scale-105 transition duration-200"
                :class="{ 'opacity-50 cursor-not-allowed': form.processing }"
                :disabled="form.processing"
            >
                <svg v-if="form.processing" class="animate-spin -ml-1 mr-3 h-5 w-5 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span v-else>Crear Cuenta</span>
            </PrimaryButton>

            <!-- Divider -->
            <div class="relative my-6">
                <div class="absolute inset-0 flex items-center">
                    <div class="w-full border-t border-white/20"></div>
                </div>
                <div class="relative flex justify-center text-sm">
                    <span class="px-4 bg-transparent text-white/70">o regístrate con</span>
                </div>
            </div>

            <!-- Botones de redes sociales -->
            <div class="grid grid-cols-2 gap-4">
                <button
                    type="button"
                    disabled
                    class="flex items-center justify-center px-4 py-3 bg-white/10 backdrop-blur-sm border border-white/20 rounded-xl text-white font-medium hover:bg-white/20 transition cursor-not-allowed opacity-50"
                >
                    <svg class="w-5 h-5 mr-2" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
                        <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
                        <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/>
                        <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
                    </svg>
                    Google
                </button>

                <button
                    type="button"
                    disabled
                    class="flex items-center justify-center px-4 py-3 bg-white/10 backdrop-blur-sm border border-white/20 rounded-xl text-white font-medium hover:bg-white/20 transition cursor-not-allowed opacity-50"
                >
                    <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 2C6.477 2 2 6.477 2 12c0 4.42 2.865 8.17 6.839 9.49.5.092.682-.217.682-.482 0-.237-.008-.866-.013-1.7-2.782.603-3.369-1.34-3.369-1.34-.454-1.156-1.11-1.463-1.11-1.463-.908-.62.069-.608.069-.608 1.003.07 1.531 1.03 1.531 1.03.892 1.529 2.341 1.087 2.91.832.092-.647.35-1.088.636-1.338-2.22-.253-4.555-1.11-4.555-4.943 0-1.091.39-1.984 1.029-2.683-.103-.253-.446-1.27.098-2.647 0 0 .84-.269 2.75 1.025A9.578 9.578 0 0112 6.836c.85.004 1.705.114 2.504.336 1.909-1.294 2.747-1.025 2.747-1.025.546 1.377.203 2.394.1 2.647.64.699 1.028 1.592 1.028 2.683 0 3.842-2.339 4.687-4.566 4.935.359.309.678.919.678 1.852 0 1.336-.012 2.415-.012 2.743 0 .267.18.578.688.48C19.138 20.167 22 16.418 22 12c0-5.523-4.477-10-10-10z"/>
                    </svg>
                    GitHub
                </button>
            </div>

            <!-- Términos y condiciones -->
            <p class="text-xs text-white/60 text-center">
                Al registrarte, aceptas nuestros
                <a href="#" class="underline hover:text-white/80">Términos de Servicio</a>
                y
                <a href="#" class="underline hover:text-white/80">Política de Privacidad</a>
            </p>
        </form>

        <!-- Footer con link a login -->
        <template #footer>
            <p class="text-white/80">
                ¿Ya tenés cuenta?
                <Link
                    :href="route('login')"
                    class="font-bold text-white hover:text-white/80 underline transition"
                >
                    Inicia sesión acá
                </Link>
            </p>
        </template>
    </GuestLayout>
</template>
