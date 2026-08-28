<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
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
        <Head title="Iniciar sesión" />

        
        <div class="text-center mb-10 relative z-10">
            <h2 class="font-flux text-4xl md:text-5xl text-black leading-none mb-3 tracking-wide">
                Iniciar <span class="text-[#E30613]">sesión</span>
            </h2>
            <p class="text-sm font-medium text-gray-500 max-w-sm mx-auto leading-relaxed">
                Ingresá tus credenciales para acceder a tu cuenta y gestionar tu contenido.
            </p>
        </div>

    
        <div v-if="status" class="mb-6 bg-green-50 border border-green-100 text-green-700 px-4 py-3 rounded-2xl text-sm font-bold flex items-center gap-2 relative z-10">
            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            {{ status }}
        </div>

        <form @submit.prevent="submit" class="space-y-6 relative z-10">
            
        
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
                    autofocus
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
                    placeholder="••••••••"
                    required
                    autocomplete="current-password"
                    class="w-full bg-gray-50 border border-transparent focus:bg-white focus:border-gray-300 focus:ring-4 focus:ring-gray-100 text-slate-800 font-medium text-sm py-3.5 px-4 rounded transition-all outline-none placeholder-gray-400"
                />
                <p v-if="form.errors.password" class="text-[#E30613] text-xs font-bold mt-2 ml-1">
                    {{ form.errors.password }}
                </p>
            </div>

            
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mt-2">
                <label class="flex items-center cursor-pointer group">
                    <div class="relative flex items-center justify-center w-5 h-5 border-2 border-gray-300 rounded bg-white group-hover:border-black transition-colors">
                        <input type="checkbox" v-model="form.remember" class="peer sr-only">
                        <svg class="w-3 h-3 text-white opacity-0 peer-checked:opacity-100 transition-opacity absolute pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                        <div class="absolute inset-0 bg-black rounded-[2px] opacity-0 peer-checked:opacity-100 transition-opacity -z-10"></div>
                    </div>
                    <span class="ml-2 text-xs font-bold text-gray-500 group-hover:text-black transition-colors uppercase tracking-wider">
                        Mantener sesión
                    </span>
                </label>

                <Link v-if="canResetPassword" :href="route('password.request')"
                    class="text-xs font-bold text-gray-400 hover:text-[#E30613] transition-colors uppercase tracking-wider">
                    ¿Olvidaste tu contraseña?
                </Link>
            </div>

        
            <div class="pt-2">
                <button type="submit" :disabled="form.processing"
                    class="w-full bg-black text-white font-bold text-xs uppercase tracking-wider py-4 rounded-full hover:bg-[#E30613] hover:shadow-lg hover:shadow-red-500/30 hover:-translate-y-1 transition-all flex items-center justify-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:translate-y-0 disabled:hover:shadow-none">
                    <span v-if="form.processing" class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin"></span>
                    {{ form.processing ? 'Verificando...' : 'Ingresar' }}
                </button>
            </div>

            
            <div class="relative my-8">
                <div class="absolute inset-0 flex items-center">
                    <div class="w-full border-t border-gray-200"></div>
                </div>
                <div class="relative flex justify-center">
                    <span class="px-4 bg-white text-[10px] font-bold uppercase tracking-widest text-gray-400">
                        O acceder con
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
                Continuar con google
            </a>
        </form>

      
        <template #footer>
            <p class="text-gray-500 text-xs font-medium">
                ¿Todavía no tenés cuenta? 
                <Link :href="route('register')" class="text-black font-bold uppercase tracking-wider ml-1 hover:text-[#E30613] transition-colors">
                    Regístrate acá
                </Link>
            </p>
        </template>
    </GuestLayout>
</template>