<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { KeyIcon, ArrowLeftIcon } from '@heroicons/vue/24/outline';

defineProps({
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
});

const submit = () => {
    form.post(route('password.email'));
};
</script>

<template>
    <GuestLayout>
        <Head title="Recuperar Contraseña | F33" />

        <div class="min-h-screen flex flex-col items-center justify-center py-12 px-4 sm:px-6 lg:px-8 bg-[#F8F9FA] antialiased font-sans">
            
            <div class="w-full max-w-md">
                
                
                <Link :href="route('login')" class="inline-flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-gray-500 hover:text-black transition-colors mb-8">
                    <ArrowLeftIcon class="w-4 h-4" /> Volver al login
                </Link>

                
                <div class="bg-white rounded shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-gray-100 p-8 md:p-10 relative overflow-hidden">
                    
                    
                    <div class="absolute top-0 right-0 w-32 h-32 bg-red-50 rounded-full blur-3xl -mr-10 -mt-10 pointer-events-none"></div>

                    
                    <div class="w-14 h-14 bg-red-50 rounded-full flex items-center justify-center mb-6 relative z-10">
                        <KeyIcon class="w-7 h-7 text-[#E30613]" />
                    </div>

                    
                    <div class="relative z-10">
                        <h2 class="font-flux text-4xl text-black leading-none mb-3 tracking-wide">
                            Recuperar <span class="text-[#E30613]">acceso</span>
                        </h2>

                        <p class="text-sm font-medium text-gray-500 mb-8 leading-relaxed">
                            ¿Olvidaste tu contraseña? No hay problema. Ingresa tu correo electrónico y te enviaremos un enlace para que puedas elegir una nueva.
                        </p>
                    </div>

                    
                    <div v-if="status" class="mb-6 bg-green-50 border border-green-100 text-green-700 px-4 py-3 rounded text-sm font-bold flex items-center gap-2 relative z-10">
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
                                required
                                autofocus
                                autocomplete="username"
                                placeholder="tu@correo.com"
                                class="w-full bg-gray-50 border border-transparent focus:bg-white focus:border-gray-300 focus:ring-4 focus:ring-gray-100 text-slate-800 font-medium text-sm py-3.5 px-4 rounded transition-all outline-none"
                            />
                            <p v-if="form.errors.email" class="text-[#E30613] text-xs font-bold mt-2 ml-1">
                                {{ form.errors.email }}
                            </p>
                        </div>

                        
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="w-full bg-black text-white font-bold text-xs uppercase tracking-wider py-4 rounded-full hover:bg-[#E30613] hover:shadow-lg hover:shadow-red-500/30 hover:-translate-y-1 transition-all flex items-center justify-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:translate-y-0 disabled:hover:shadow-none"
                        >
                            <span v-if="form.processing" class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin"></span>
                            {{ form.processing ? 'Enviando enlace...' : 'Enviar enlace de recuperación' }}
                        </button>
                    </form>

                </div>
            </div>
            
        </div>
    </GuestLayout>
</template>