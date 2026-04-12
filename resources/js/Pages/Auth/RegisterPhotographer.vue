<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import CustomCursor from '@/Components/CustomCursor.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ArrowRightIcon } from '@heroicons/vue/24/outline'; // <-- Nuevo ícono añadido

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
    <CustomCursor/>
    <GuestLayout>
        <Head title="Solicitud Profesional" />

        <div class="min-h-screen bg-[#111920] font-['Syne'] py-12 md:py-24 px-6 md:px-12 lg:px-16 selection:bg-[#FFB162] selection:text-[#1B2632]">
            <div class="max-w-[1400px] mx-auto grid grid-cols-1 lg:grid-cols-12 gap-16 lg:gap-24">

                <div class="lg:col-span-5 relative">
                    <div class="lg:sticky lg:top-24">
                        <Link href="/" class="inline-block mb-16">
                            <span class="text-3xl font-['Syne'] font-extrabold tracking-tighter text-[#EEE9DF]">
                                f<span class="text-[#FFB162]">3</span>3
                            </span>
                        </Link>

                        <div class="text-[9px] font-bold tracking-[0.35em] uppercase text-[#FFB162] mb-6 flex items-center gap-3 after:content-[''] after:h-px after:w-12 after:bg-[#A35139]">
                            Portal de Talentos
                        </div>
                        
                        <h1 class="text-5xl md:text-7xl font-['Cormorant_Garamond'] text-[#EEE9DF] mb-8 leading-[0.95] font-light">
                            Únete a la <br><em class="italic text-[#C9C1B1]">élite visual.</em>
                        </h1>
                        
                        <p class="text-[#C9C1B1]/80 text-[14px] leading-relaxed max-w-sm mb-12 font-light">
                            Buscamos fotógrafos excepcionales. Tu solicitud será evaluada meticulosamente por nuestro equipo de curaduría para garantizar que se alinee con los estándares estéticos y profesionales de la agencia.
                        </p>

                        <div class="bg-[#1B2632] border-l-2 border-[#FFB162] p-8 shadow-2xl relative overflow-hidden group">
                            <div class="absolute -right-4 -bottom-4 text-[#FFB162] opacity-[0.03] group-hover:scale-110 transition-transform duration-700">
                                <span class="font-['Cormorant_Garamond'] text-9xl italic">f33</span>
                            </div>
                            <h4 class="font-['Cormorant_Garamond'] text-3xl text-[#EEE9DF] mb-3 relative z-10">Curaduría</h4>
                            <p class="text-[12px] text-[#C9C1B1] leading-relaxed relative z-10">
                                Al enviar el formulario, su perfil pasará a estado <span class="text-[#FFB162] font-bold">"Pendiente"</span>. Revisaremos su portafolio en un plazo de 24 a 48 horas hábiles.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="lg:col-span-7">
                    <form @submit.prevent="submit" class="space-y-16">
                        
                        <div>
                            <h3 class="font-['Cormorant_Garamond'] text-4xl text-[#EEE9DF] italic mb-8 border-b border-[#C9C1B1]/10 pb-4">
                                01. Credenciales
                            </h3>

                            <div class="space-y-8">
                                <div>
                                    <label for="name" class="block text-[9px] font-bold uppercase tracking-widest text-[#C9C1B1]/70 mb-1">Nombre Completo</label>
                                    <TextInput id="name" type="text" v-model="form.name" required autofocus autocomplete="name" placeholder="Nombre y Apellido"
                                        class="block w-full !bg-transparent !border-x-0 !border-t-0 !border-b !border-[#C9C1B1]/20 !text-[#EEE9DF] placeholder:text-[#C9C1B1]/20 focus:!border-[#FFB162] focus:!ring-0 !py-3 !px-0 text-[14px] !rounded-none !shadow-none transition-colors" />
                                    <InputError class="mt-2 text-[#A35139] text-[11px]" :message="form.errors.name" />
                                </div>

                                <div>
                                    <label for="email" class="block text-[9px] font-bold uppercase tracking-widest text-[#C9C1B1]/70 mb-1">Correo Profesional</label>
                                    <TextInput id="email" type="email" v-model="form.email" required placeholder="contacto@estudio.com"
                                        class="block w-full !bg-transparent !border-x-0 !border-t-0 !border-b !border-[#C9C1B1]/20 !text-[#EEE9DF] placeholder:text-[#C9C1B1]/20 focus:!border-[#FFB162] focus:!ring-0 !py-3 !px-0 text-[14px] !rounded-none !shadow-none transition-colors" />
                                    <InputError class="mt-2 text-[#A35139] text-[11px]" :message="form.errors.email" />
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                                    <div>
                                        <label for="password" class="block text-[9px] font-bold uppercase tracking-widest text-[#C9C1B1]/70 mb-1">Contraseña</label>
                                        <TextInput id="password" type="password" v-model="form.password" required placeholder="********"
                                            class="block w-full !bg-transparent !border-x-0 !border-t-0 !border-b !border-[#C9C1B1]/20 !text-[#EEE9DF] placeholder:text-[#C9C1B1]/20 focus:!border-[#FFB162] focus:!ring-0 !py-3 !px-0 text-[14px] !rounded-none !shadow-none transition-colors" />
                                        <InputError class="mt-2 text-[#A35139] text-[11px]" :message="form.errors.password" />
                                    </div>
                                    <div>
                                        <label for="password_confirmation" class="block text-[9px] font-bold uppercase tracking-widest text-[#C9C1B1]/70 mb-1">Confirmar Contraseña</label>
                                        <TextInput id="password_confirmation" type="password" v-model="form.password_confirmation" required placeholder="********"
                                            class="block w-full !bg-transparent !border-x-0 !border-t-0 !border-b !border-[#C9C1B1]/20 !text-[#EEE9DF] placeholder:text-[#C9C1B1]/20 focus:!border-[#FFB162] focus:!ring-0 !py-3 !px-0 text-[14px] !rounded-none !shadow-none transition-colors" />
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div>
                            <h3 class="font-['Cormorant_Garamond'] text-4xl text-[#EEE9DF] italic mb-8 border-b border-[#C9C1B1]/10 pb-4 mt-16">
                                02. Perfil Profesional
                            </h3>

                            <div class="space-y-8">
                                <div>
                                    <label for="business_name" class="block text-[9px] font-bold uppercase tracking-widest text-[#C9C1B1]/70 mb-1">Nombre del Estudio / Marca</label>
                                    <TextInput id="business_name" type="text" v-model="form.business_name" required placeholder="Ej: Visual Arts Studio"
                                        class="block w-full !bg-transparent !border-x-0 !border-t-0 !border-b !border-[#C9C1B1]/20 !text-[#EEE9DF] placeholder:text-[#C9C1B1]/20 focus:!border-[#FFB162] focus:!ring-0 !py-3 !px-0 text-[14px] !rounded-none !shadow-none transition-colors" />
                                    <InputError class="mt-2 text-[#A35139] text-[11px]" :message="form.errors.business_name" />
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                                    <div>
                                        <label for="region" class="block text-[9px] font-bold uppercase tracking-widest text-[#C9C1B1]/70 mb-1">Región Operativa</label>
                                        <div class="relative">
                                            <select id="region" v-model="form.region" required
                                                class="block w-full !bg-transparent !border-x-0 !border-t-0 !border-b !border-[#C9C1B1]/20 !text-[#EEE9DF] focus:!border-[#FFB162] focus:!ring-0 !py-3 !px-0 text-[14px] !rounded-none !shadow-none transition-colors appearance-none cursor-pointer">
                                                <option value="" disabled class="bg-[#1B2632] text-[#C9C1B1]">Seleccionar región...</option>
                                                <option v-for="region in regions" :key="region" :value="region" class="bg-[#1B2632] text-[#EEE9DF]">
                                                    {{ region }}
                                                </option>
                                            </select>
                                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center text-[#FFB162]">
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                                            </div>
                                        </div>
                                        <InputError class="mt-2 text-[#A35139] text-[11px]" :message="form.errors.region" />
                                    </div>

                                    <div>
                                        <label for="phone" class="block text-[9px] font-bold uppercase tracking-widest text-[#C9C1B1]/70 mb-1">Teléfono</label>
                                        <TextInput id="phone" type="tel" v-model="form.phone" required placeholder="+54 9 ..."
                                            class="block w-full !bg-transparent !border-x-0 !border-t-0 !border-b !border-[#C9C1B1]/20 !text-[#EEE9DF] placeholder:text-[#C9C1B1]/20 focus:!border-[#FFB162] focus:!ring-0 !py-3 !px-0 text-[14px] !rounded-none !shadow-none transition-colors" />
                                        <InputError class="mt-2 text-[#A35139] text-[11px]" :message="form.errors.phone" />
                                    </div>
                                </div>

                                <div>
                                    <label for="bio" class="block text-[9px] font-bold uppercase tracking-widest text-[#C9C1B1]/70 mb-1">Reseña Profesional</label>
                                    <textarea id="bio" v-model="form.bio" rows="3" placeholder="Describa su experiencia, estilo fotográfico y equipo técnico principal..."
                                        class="block w-full !bg-transparent !border-x-0 !border-t-0 !border-b !border-[#C9C1B1]/20 !text-[#EEE9DF] placeholder:text-[#C9C1B1]/20 focus:!border-[#FFB162] focus:!ring-0 !py-3 !px-0 text-[14px] !rounded-none !shadow-none transition-colors resize-none"></textarea>
                                    <div class="flex justify-between mt-2">
                                        <InputError class="text-[#A35139] text-[11px]" :message="form.errors.bio" />
                                        <span class="text-[10px] text-[#C9C1B1]/40 font-mono">{{ form.bio?.length || 0 }} / 1000</span>
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-[9px] font-bold uppercase tracking-widest text-[#C9C1B1]/70 mb-4">Foto de Perfil / Logo</label>
                                    <label for="profile_photo" class="group relative flex flex-col items-center justify-center w-full h-32 border border-[#C9C1B1]/20 border-dashed bg-[#1B2632]/30 hover:bg-[#1B2632] hover:border-[#FFB162]/50 transition-all duration-300 cursor-pointer">
                                        <div v-if="form.profile_photo" class="text-center">
                                            <span class="block text-[#FFB162] text-[13px] font-bold mb-1">{{ form.profile_photo.name }}</span>
                                            <span class="text-[#C9C1B1] text-[10px] uppercase tracking-widest">Cambiar archivo</span>
                                        </div>
                                        <div v-else class="text-center flex flex-col items-center">
                                            <svg class="w-6 h-6 text-[#C9C1B1]/30 group-hover:text-[#FFB162] transition-colors mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" /></svg>
                                            <span class="block text-[#EEE9DF] text-[11px] uppercase tracking-widest font-bold mb-1">Subir Foto o Logo</span>
                                            <span class="text-[#C9C1B1]/50 text-[9px] uppercase tracking-widest">Formatos: JPG, PNG (Max 2MB)</span>
                                        </div>
                                        <input id="profile_photo" type="file" class="hidden" accept="image/*" @change="handleFileUpload" />
                                    </label>
                                    <InputError class="mt-2 text-[#A35139] text-[11px]" :message="form.errors.profile_photo" />
                                </div>
                            </div>
                        </div>

                        <div>
                            <h3 class="font-['Cormorant_Garamond'] text-4xl text-[#EEE9DF] italic mb-8 border-b border-[#C9C1B1]/10 pb-4 mt-16">
                                03. Presencia Digital
                            </h3>

                            <div class="space-y-8">
                                <div>
                                    <label for="website" class="block text-[9px] font-bold uppercase tracking-widest text-[#C9C1B1]/70 mb-1">Sitio Web / Portfolio</label>
                                    <TextInput id="website" type="url" v-model="form.website" placeholder="https://www.miestudio.com"
                                        class="block w-full !bg-transparent !border-x-0 !border-t-0 !border-b !border-[#C9C1B1]/20 !text-[#EEE9DF] placeholder:text-[#C9C1B1]/20 focus:!border-[#FFB162] focus:!ring-0 !py-3 !px-0 text-[14px] !rounded-none !shadow-none transition-colors" />
                                    <InputError class="mt-2 text-[#A35139] text-[11px]" :message="form.errors.website" />
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                                    <div>
                                        <label for="instagram" class="block text-[9px] font-bold uppercase tracking-widest text-[#C9C1B1]/70 mb-1">Instagram</label>
                                        <div class="relative">
                                            <span class="absolute inset-y-0 left-0 flex items-center text-[#C9C1B1]/30 text-lg font-light pt-1">@</span>
                                            <TextInput id="instagram" type="text" v-model="form.instagram" placeholder="usuario"
                                                class="block w-full pl-6 !bg-transparent !border-x-0 !border-t-0 !border-b !border-[#C9C1B1]/20 !text-[#EEE9DF] placeholder:text-[#C9C1B1]/20 focus:!border-[#FFB162] focus:!ring-0 !py-3 text-[14px] !rounded-none !shadow-none transition-colors" />
                                        </div>
                                        <InputError class="mt-2 text-[#A35139] text-[11px]" :message="form.errors.instagram" />
                                    </div>
                                    <div>
                                        <label for="facebook" class="block text-[9px] font-bold uppercase tracking-widest text-[#C9C1B1]/70 mb-1">Facebook</label>
                                        <TextInput id="facebook" type="text" v-model="form.facebook" placeholder="Nombre de página"
                                            class="block w-full !bg-transparent !border-x-0 !border-t-0 !border-b !border-[#C9C1B1]/20 !text-[#EEE9DF] placeholder:text-[#C9C1B1]/20 focus:!border-[#FFB162] focus:!ring-0 !py-3 !px-0 text-[14px] !rounded-none !shadow-none transition-colors" />
                                        <InputError class="mt-2 text-[#A35139] text-[11px]" :message="form.errors.facebook" />
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="pt-10">
                            <button type="submit" :disabled="form.processing"
                                class="w-full flex justify-center items-center gap-4 bg-[#FFB162] text-[#1B2632] font-bold text-[11px] uppercase tracking-[0.25em] py-6 hover:bg-[#EEE9DF] transition-all duration-300 disabled:opacity-50 shadow-2xl group">
                                <span v-if="form.processing">Procesando Aplicación...</span>
                                <span v-else>Enviar Solicitud a Evaluación</span>
                                <ArrowRightIcon v-if="!form.processing" class="w-5 h-5 group-hover:translate-x-2 transition-transform" />
                            </button>
                            <p class="text-[10px] text-[#C9C1B1]/40 text-center mt-4 tracking-widest">
                                Al aplicar, aceptas los <a href="#" class="underline hover:text-[#C9C1B1] transition-colors">Términos Comerciales</a> de la plataforma.
                            </p>
                        </div>

                    </form>

                    <div class="mt-20 pt-10 border-t border-[#C9C1B1]/10 flex flex-col md:flex-row justify-between items-center gap-6">
                        <p class="text-[#C9C1B1] text-[10px] uppercase tracking-widest font-bold">
                            ¿Ya eres miembro del staff?
                            <Link :href="route('login')" class="text-[#FFB162] hover:text-[#EEE9DF] ml-2 transition-colors border-b border-[#FFB162]/30 hover:border-[#EEE9DF] pb-1">
                                Acceso Socios
                            </Link>
                        </p>
                        <Link :href="route('register')" class="text-[9px] uppercase tracking-[0.2em] text-[#C9C1B1]/50 hover:text-[#FFB162] transition-colors">
                            Registro para Clientes Generales
                        </Link>
                    </div>

                </div>
            </div>
        </div>
    </GuestLayout>
</template>

<style scoped>
/* Eliminar flechas en inputs de tipo number si llegaras a usarlos */
input[type="number"]::-webkit-inner-spin-button,
input[type="number"]::-webkit-outer-spin-button {
  -webkit-appearance: none;
  margin: 0;
}
</style>