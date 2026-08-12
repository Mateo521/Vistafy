<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { KeyIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
    email: {
        type: String,
        required: true,
    },
    token: {
        type: String,
        required: true,
    },
});

const form = useForm({
    token: props.token,
    email: props.email,
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('password.store'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Restablecer contraseña | F33" />

   
        <div class="text-center mb-10 relative z-10">
            <div class="w-14 h-14 bg-red-50 rounded-full flex items-center justify-center mx-auto mb-6 shadow-sm border border-red-100">
                <KeyIcon class="w-7 h-7 text-[#E30613]" />
            </div>
            <h2 class="font-flux text-4xl md:text-5xl text-black leading-none mb-3 tracking-wide">
                Restablecer <span class="text-[#E30613]">Contraseña</span>
            </h2>
            <p class="text-sm font-medium text-gray-500 max-w-sm mx-auto leading-relaxed">
                Ingresá tu nueva contraseña para recuperar el acceso a tu cuenta.
            </p>
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
                    class="w-full bg-gray-50 border border-transparent focus:bg-white focus:border-gray-300 focus:ring-4 focus:ring-gray-100 text-slate-800 font-medium text-sm py-3.5 px-4 rounded transition-all outline-none placeholder-gray-400"
                />
                <p v-if="form.errors.email" class="text-[#E30613] text-xs font-bold mt-2 ml-1">
                    {{ form.errors.email }}
                </p>
            </div>

           
            <div>
                <label for="password" class="block text-xs font-bold uppercase tracking-wider text-gray-500 mb-2 ml-1">
                    Nueva contraseña
                </label>
                <input
                    id="password"
                    type="password"
                    v-model="form.password"
                    required
                    autocomplete="new-password"
                    placeholder="Mínimo 8 caracteres"
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
                    required
                    autocomplete="new-password"
                    placeholder="Repite tu nueva contraseña"
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
                    {{ form.processing ? 'Actualizando...' : 'Restablecer contraseña' }}
                </button>
            </div>
        </form>
    </GuestLayout>
</template>