<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
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

        
        <div class="text-center mb-10 relative z-10">
            <h2 class="font-flux text-4xl md:text-5xl text-black leading-none mb-3 tracking-wide">
                Crear <span class="text-[#E30613]">cuenta</span>
            </h2>
            <p class="text-sm font-medium text-gray-500 max-w-sm mx-auto leading-relaxed">
                Unite a nuestra plataforma para acceder a galerías exclusivas y gestionar tus pedidos fotográficos.
            </p>
        </div>

        <form @submit.prevent="submit" class="space-y-5 relative z-10">
            
           
            <div>
                <label for="name" class="block text-xs font-bold uppercase tracking-wider text-gray-500 mb-2 ml-1">
                    Nombre completo
                </label>
                <input
                    id="name"
                    type="text"
                    v-model="form.name"
                    placeholder="Juan Pérez"
                    required
                    autofocus
                    autocomplete="name"
                    class="w-full bg-gray-50 border border-transparent focus:bg-white focus:border-gray-300 focus:ring-4 focus:ring-gray-100 text-slate-800 font-medium text-sm py-3.5 px-4 rounded transition-all outline-none placeholder-gray-400"
                />
                <p v-if="form.errors.name" class="text-[#E30613] text-xs font-bold mt-2 ml-1">
                    {{ form.errors.name }}
                </p>
            </div>

        
            <div>
                <label for="email" class="block text-xs font-bold uppercase tracking-wider text-gray-500 mb-2 ml-1">
                    Correo electrónico
                </label>
                <input
                    id="email"
                    type="email"
                    v-model="form.email"
                    placeholder="tu@correo.com"
                    required
                    autocomplete="username"
                    class="w-full bg-gray-50 border border-transparent focus:bg-white focus:border-gray-300 focus:ring-4 focus:ring-gray-100 text-slate-800 font-medium text-sm py-3.5 px-4 rounded transition-all outline-none placeholder-gray-400"
                />
                <p v-if="form.errors.email" class="text-[#E30613] text-xs font-bold mt-2 ml-1">
                    {{ form.errors.email }}
                </p>
            </div>

            
            <div>
                <label for="password" class="block text-xs font-bold uppercase tracking-wider text-gray-500 mb-2 ml-1">
                    Contraseña
                </label>
                <input
                    id="password"
                    type="password"
                    v-model="form.password"
                    placeholder="Mínimo 8 caracteres"
                    required
                    autocomplete="new-password"
                    class="w-full bg-gray-50 border border-transparent focus:bg-white focus:border-gray-300 focus:ring-4 focus:ring-gray-100 text-slate-800 font-medium text-sm py-3.5 px-4 rounded transition-all outline-none placeholder-gray-400"
                />
                <p v-if="form.errors.password" class="text-[#E30613] text-xs font-bold mt-2 ml-1">
                    {{ form.errors.password }}
                </p>
            </div>

           
            <div>
                <label for="password_confirmation" class="block text-xs font-bold uppercase tracking-wider text-gray-500 mb-2 ml-1">
                    Confirmar contraseña
                </label>
                <input
                    id="password_confirmation"
                    type="password"
                    v-model="form.password_confirmation"
                    placeholder="Repite tu contraseña"
                    required
                    autocomplete="new-password"
                    class="w-full bg-gray-50 border border-transparent focus:bg-white focus:border-gray-300 focus:ring-4 focus:ring-gray-100 text-slate-800 font-medium text-sm py-3.5 px-4 rounded transition-all outline-none placeholder-gray-400"
                />
                <p v-if="form.errors.password_confirmation" class="text-[#E30613] text-xs font-bold mt-2 ml-1">
                    {{ form.errors.password_confirmation }}
                </p>
            </div>

        
            <div class="pt-4">
                <button type="submit" :disabled="form.processing"
                    class="w-full bg-black text-white font-bold text-xs uppercase tracking-wider py-4 rounded-full hover:bg-[#E30613] hover:shadow-lg hover:shadow-red-500/30 hover:-translate-y-1 transition-all flex items-center justify-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:translate-y-0 disabled:hover:shadow-none">
                    <span v-if="form.processing" class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin"></span>
                    {{ form.processing ? 'Registrando cuenta...' : 'Completar Registro' }}
                </button>
            </div>

        
            <p class="text-[10px] text-gray-400 font-medium text-center leading-relaxed px-4 pt-2">
                Al registrarte, aceptas nuestros
                <Link :href="route('terms')" class="font-bold text-gray-500 hover:text-black transition-colors">Términos de Servicio</Link>
                y confirmas haber leído nuestra
                <Link :href="route('privacy')" class="font-bold text-gray-500 hover:text-black transition-colors">Política de Privacidad</Link>.
            </p>

            
            <div class="relative my-8">
                <div class="absolute inset-0 flex items-center">
                    <div class="w-full border-t border-gray-200"></div>
                </div>
                <div class="relative flex justify-center">
                    <span class="px-4 bg-white text-[10px] font-bold uppercase tracking-widest text-gray-400">
                        O registrarse con
                    </span>
                </div>
            </div>

            
            <a :href="route('auth.google')" 
                class="w-full flex items-center justify-center gap-3 bg-white border border-gray-200 text-slate-700 font-bold text-xs uppercase tracking-wider py-3.5 rounded-full hover:bg-gray-50 hover:border-gray-300 transition-all shadow-sm hover:shadow">
                <svg class="w-5 h-5" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
                    <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
                    <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/>
                    <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
                </svg>
                Continuar con Google
            </a>
        </form>

        <template #footer>
            <p class="text-gray-500 text-xs font-medium">
                ¿Ya tenés una cuenta? 
                <Link :href="route('login')" class="text-black font-bold uppercase tracking-wider ml-1 hover:text-[#E30613] transition-colors">
                    Iniciar Sesión
                </Link>
            </p>
        </template>
    </GuestLayout>
</template>