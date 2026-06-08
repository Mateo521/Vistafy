<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
/*
import CustomCursor from '@/Components/CustomCursor.vue';
*/
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ArrowRightIcon, PhotoIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
    regions: {
        type: Array,
        required: true,
    },
});

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    business_name: '',
    region: '',
    phone: '',
    bio: '',
    website: '',
    instagram: '',
    facebook: '',
    profile_photo: null,
});

const handleFileUpload = (event) => {
    form.profile_photo = event.target.files[0];
};

const submit = () => {
    form.post(route('photographer.register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
        forceFormData: true,
    });
};
</script>

<template>
   
    <GuestLayout>

        <Head title="Alta de Operador" />

        <div
            class="min-h-screen bg-black font-sans py-12 md:py-24 px-6 md:px-12 lg:px-16 selection:bg-[#E30613] selection:text-white text-white">
            <div class="max-w-7xl mx-auto grid grid-cols-1 lg:grid-cols-12 gap-16 lg:gap-24">

                <div class="lg:col-span-5 relative">
                    <div class="lg:sticky lg:top-24">

                        <Link href="/"
                            class="inline-block mb-16 text-3xl font-black tracking-tighter text-white hover:text-[#E30613] transition-colors">
                            F33 <span class="text-[#E30613]">STUDIO</span>
                        </Link>

                        <div class="font-mono text-[10px] font-bold  uppercase text-[#E30613] mb-6">
                            [ Portal de fotógrafos ]
                        </div>

                        <h1 class="text-5xl md:text-7xl font-black uppercase tracking-tighter mb-8 leading-[0.9]">
                            Unite a <br><span class="text-transparent"
                                style="-webkit-text-stroke: 2px #E30613;">f33</span>
                        </h1>

                        <p
                            class="text-gray-400 font-mono text-[11px] leading-relaxed max-w-sm mb-12 uppercase tracking-wider">
                            Buscamos fotografos visuales. Tu solicitud será evaluada bajo estándares técnicos y estéticos.
                        </p>

                        <div
                            class="bg-zinc-950 border border-white/10 p-6 relative overflow-hidden  border-l-[#E30613]">
                            <h4 class="font-sans font-black text-white text-xl uppercase tracking-tighter mb-2">
                                Curaduría</h4>
                            <p class="font-mono text-[10px] text-gray-500 leading-relaxed uppercase tracking-widest">
                                Al enviar los datos, tu perfil quedará en estado <span
                                    class="text-[#E30613] font-bold">EN ESPERA</span>.  48hs hábiles.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="lg:col-span-7">
                    <form @submit.prevent="submit" class="space-y-16">

                        <div>
                            <h3
                                class="font-mono text-xs font-bold uppercase tracking-widest text-[#E30613] mb-6 border-b border-white/10 pb-2">
                                1. Acceso
                            </h3>

                            <div class="space-y-4">
                                <div>
                                    <label for="name"
                                        class="block font-mono text-[10px] font-bold uppercase tracking-widest text-gray-500 mb-2">Nombre
                                        Completo</label>
                                    <input id="name" type="text" v-model="form.name" required autofocus
                                        placeholder="Nombre y Apellido"
                                        class="w-full bg-zinc-950 border border-white/10 text-white font-mono text-xs p-4 focus:border-[#E30613] focus:ring-1 focus:ring-[#E30613] outline-none transition-colors placeholder:text-gray-700" />
                                    <InputError class="mt-2 text-[#E30613] text-[11px] font-mono"
                                        :message="form.errors.name" />
                                </div>

                                <div>
                                    <label for="email"
                                        class="block font-mono text-[10px] font-bold uppercase tracking-widest text-gray-500 mb-2">Correo
                                        electrónico</label>
                                    <input id="email" type="email" v-model="form.email" required
                                        placeholder="contacto@operador.com"
                                        class="w-full bg-zinc-950 border border-white/10 text-white font-mono text-xs p-4 focus:border-[#E30613] focus:ring-1 focus:ring-[#E30613] outline-none transition-colors placeholder:text-gray-700" />
                                    <InputError class="mt-2 text-[#E30613] text-[11px] font-mono"
                                        :message="form.errors.email" />
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label for="password"
                                            class="block font-mono text-[10px] font-bold uppercase tracking-widest text-gray-500 mb-2">Contraseña</label>
                                        <input id="password" type="password" v-model="form.password" required
                                            placeholder="••••••••"
                                            class="w-full bg-zinc-950 border border-white/10 text-white font-mono text-xs p-4 focus:border-[#E30613] focus:ring-1 focus:ring-[#E30613] outline-none transition-colors placeholder:text-gray-700" />
                                        <InputError class="mt-2 text-[#E30613] text-[11px] font-mono"
                                            :message="form.errors.password" />
                                    </div>
                                    <div>
                                        <label for="password_confirmation"
                                            class="block font-mono text-[10px] font-bold uppercase tracking-widest text-gray-500 mb-2">Confirmar
                                            Contraseña</label>
                                        <input id="password_confirmation" type="password"
                                            v-model="form.password_confirmation" required placeholder="••••••••"
                                            class="w-full bg-zinc-950 border border-white/10 text-white font-mono text-xs p-4 focus:border-[#E30613] focus:ring-1 focus:ring-[#E30613] outline-none transition-colors placeholder:text-gray-700" />
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div>
                            <h3
                                class="font-mono text-xs font-bold uppercase tracking-widest text-[#E30613] mb-6 border-b border-white/10 pb-2">
                                2. Identidad
                            </h3>

                            <div class="space-y-4">
                                <div>
                                    <label for="business_name"
                                        class="block font-mono text-[10px] font-bold uppercase tracking-widest text-gray-500 mb-2">Nombre
                                        del Estudio / Alias</label>
                                    <input id="business_name" type="text" v-model="form.business_name" required
                                        placeholder="Ej: Visual Arts"
                                        class="w-full bg-zinc-950 border border-white/10 text-white font-mono text-xs p-4 focus:border-[#E30613] focus:ring-1 focus:ring-[#E30613] outline-none transition-colors placeholder:text-gray-700" />
                                    <InputError class="mt-2 text-[#E30613] text-[11px] font-mono"
                                        :message="form.errors.business_name" />
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label for="region"
                                            class="block font-mono text-[10px] font-bold uppercase tracking-widest text-gray-500 mb-2">Sector
                                            Operativo</label>
                                        <div class="relative">
                                            <select id="region" v-model="form.region" required
                                                class="w-full bg-zinc-950 border border-white/10 text-white font-mono text-xs p-4 focus:border-[#E30613] focus:ring-1 focus:ring-[#E30613] outline-none transition-colors appearance-none cursor-pointer">
                                                <option value="" disabled class="text-gray-600">Seleccionar sector...
                                                </option>
                                                <option v-for="region in regions" :key="region" :value="region"
                                                    class="bg-zinc-900 text-white">
                                                    {{ region }}
                                                </option>
                                            </select>
                                            <div
                                                class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-[#E30613]">
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2" d="M19 9l-7 7-7-7" />
                                                </svg>
                                            </div>
                                        </div>
                                        <InputError class="mt-2 text-[#E30613] text-[11px] font-mono"
                                            :message="form.errors.region" />
                                    </div>

                                    <div>
                                        <label for="phone"
                                            class="block font-mono text-[10px] font-bold uppercase tracking-widest text-gray-500 mb-2">Comline
                                            (Teléfono)</label>
                                        <input id="phone" type="tel" v-model="form.phone" required
                                            placeholder="+54 9 ..."
                                            class="w-full bg-zinc-950 border border-white/10 text-white font-mono text-xs p-4 focus:border-[#E30613] focus:ring-1 focus:ring-[#E30613] outline-none transition-colors placeholder:text-gray-700" />
                                        <InputError class="mt-2 text-[#E30613] text-[11px] font-mono"
                                            :message="form.errors.phone" />
                                    </div>
                                </div>

                                <div>
                                    <label for="bio"
                                        class="block font-mono text-[10px] font-bold uppercase tracking-widest text-gray-500 mb-2">Reporte
                                        de Equipo / Bio</label>
                                    <textarea id="bio" v-model="form.bio" rows="3"
                                        placeholder="Setup actual y experiencia..."
                                        class="w-full bg-zinc-950 border border-white/10 text-white font-mono text-xs p-4 focus:border-[#E30613] focus:ring-1 focus:ring-[#E30613] outline-none transition-colors placeholder:text-gray-700 resize-none"></textarea>
                                    <div class="flex justify-between mt-1">
                                        <InputError class="text-[#E30613] text-[11px] font-mono"
                                            :message="form.errors.bio" />
                                        <span class="text-[9px] text-gray-600 font-mono">{{ form.bio?.length || 0 }} /
                                            1000</span>
                                    </div>
                                </div>

                                <div>
                                    <label
                                        class="block font-mono text-[10px] font-bold uppercase tracking-widest text-gray-500 mb-2">Identificador
                                        Visual (Foto/Logo)</label>
                                    <label for="profile_photo"
                                        class="flex flex-col items-center justify-center w-full h-32 border border-dashed border-white/20 bg-zinc-950 hover:border-[#E30613] hover:bg-[#E30613]/5 transition-all duration-300 cursor-pointer">
                                        <div v-if="form.profile_photo" class="text-center">
                                            <span class="block text-[#E30613] text-xs font-mono font-bold mb-1">{{
                                                form.profile_photo.name }}</span>
                                            <span class="text-gray-500 font-mono text-[9px] uppercase tracking-widest">[
                                                Remplazar Archivo ]</span>
                                        </div>
                                        <div v-else class="text-center flex flex-col items-center">
                                            <PhotoIcon class="w-6 h-6 text-gray-600 mb-2" />
                                            <span
                                                class="block text-white font-mono text-[10px] uppercase tracking-widest font-bold mb-1">Cargar
                                                Imagen</span>
                                            <span
                                                class="text-gray-600 font-mono text-[9px] uppercase tracking-widest">JPG,
                                                PNG (Max 2MB)</span>
                                        </div>
                                        <input id="profile_photo" type="file" class="hidden" accept="image/*"
                                            @change="handleFileUpload" />
                                    </label>
                                    <InputError class="mt-2 text-[#E30613] text-[11px] font-mono"
                                        :message="form.errors.profile_photo" />
                                </div>
                            </div>
                        </div>

                        <div>
                            <h3
                                class="font-mono text-xs font-bold uppercase tracking-widest text-[#E30613] mb-6 border-b border-white/10 pb-2">
                                3. Ubicación
                            </h3>

                            <div class="space-y-4">
                                <div>
                                    <label for="website"
                                        class="block font-mono text-[10px] font-bold uppercase tracking-widest text-gray-500 mb-2">Portafolio
                                        Web</label>
                                    <input id="website" type="url" v-model="form.website" placeholder="https://..."
                                        class="w-full bg-zinc-950 border border-white/10 text-white font-mono text-xs p-4 focus:border-[#E30613] focus:ring-1 focus:ring-[#E30613] outline-none transition-colors placeholder:text-gray-700" />
                                    <InputError class="mt-2 text-[#E30613] text-[11px] font-mono"
                                        :message="form.errors.website" />
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div class="relative">
                                        <label for="instagram"
                                            class="block font-mono text-[10px] font-bold uppercase tracking-widest text-gray-500 mb-2">Instagram</label>
                                        <span class="absolute bottom-4 left-4 font-mono text-xs text-gray-600">@</span>
                                        <input id="instagram" type="text" v-model="form.instagram" placeholder="usuario"
                                            class="w-full pl-8 bg-zinc-950 border border-white/10 text-white font-mono text-xs p-4 focus:border-[#E30613] focus:ring-1 focus:ring-[#E30613] outline-none transition-colors placeholder:text-gray-700" />
                                        <InputError class="mt-2 text-[#E30613] text-[11px] font-mono"
                                            :message="form.errors.instagram" />
                                    </div>
                                    <div>
                                        <label for="facebook"
                                            class="block font-mono text-[10px] font-bold uppercase tracking-widest text-gray-500 mb-2">Facebook</label>
                                        <input id="facebook" type="text" v-model="form.facebook"
                                            placeholder="Nombre de página"
                                            class="w-full bg-zinc-950 border border-white/10 text-white font-mono text-xs p-4 focus:border-[#E30613] focus:ring-1 focus:ring-[#E30613] outline-none transition-colors placeholder:text-gray-700" />
                                        <InputError class="mt-2 text-[#E30613] text-[11px] font-mono"
                                            :message="form.errors.facebook" />
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="pt-6">
                            <button type="submit" :disabled="form.processing"
                                class="w-full flex justify-center items-center gap-4 bg-[#E30613] border border-[#E30613] text-white font-sans font-black text-lg md:text-xl uppercase tracking-widest py-6 hover:bg-white hover:border-white hover:text-black transition-all duration-300 disabled:opacity-50 group">
                                <span v-if="form.processing">Transfiriendo...</span>
                                <span v-else>Enviar solicitud</span>
                                <ArrowRightIcon v-if="!form.processing"
                                    class="w-6 h-6 group-hover:translate-x-2 transition-transform" />
                            </button>
                            <p class="font-mono text-[9px] text-gray-600 text-center mt-4 tracking-widest uppercase">
                                Al transmitir, asumís las <a href="#"
                                    class="text-white hover:text-[#E30613] border-b border-white/20 pb-0.5">reglas</a> de F33.
                            </p>
                        </div>

                    </form>

                    <div
                        class="mt-24 pt-8 border-t border-white/10 flex flex-col md:flex-row justify-between items-center gap-6">
                        <p
                            class="text-gray-400 font-mono text-[10px] uppercase tracking-widest flex items-center gap-2">
                            ¿Ya tenés una cuenta?
                            <Link :href="route('login')"
                                class="text-white hover:text-[#E30613] border-b border-white/30 hover:border-[#E30613] pb-0.5 transition-colors">
                                Iniciar sesión
                            </Link>
                        </p>
                        <Link :href="route('register')"
                            class="font-mono text-[9px] uppercase  text-gray-600 hover:text-white transition-colors">
                            [ Registro Público / Clientes ]
                        </Link>
                    </div>

                </div>
            </div>
        </div>
    </GuestLayout>
</template>

<style scoped>

input[type="number"]::-webkit-inner-spin-button,
input[type="number"]::-webkit-outer-spin-button {
    -webkit-appearance: none;
    margin: 0;
}
</style>