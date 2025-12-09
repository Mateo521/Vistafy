<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import CustomCursor from '@/Components/CustomCursor.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

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
    // --- NUEVOS CAMPOS ---
    website: '',
    instagram: '',
    facebook: '',
    // ---------------------
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

        <div class="text-center mb-10">
            <span class="text-xs font-bold tracking-[0.2em] text-white/60 uppercase mb-3 block">
                Portal de Talentos
            </span>
            <h1 class="text-3xl md:text-4xl font-serif font-bold text-white mb-4">
                Solicitud de Ingreso
            </h1>
            <p class="text-white/60 text-sm font-light max-w-sm mx-auto leading-relaxed">
                Únase a nuestra red de fotógrafos certificados. Su solicitud será evaluada por nuestro equipo de curaduría.
            </p>
        </div>

        <form @submit.prevent="submit" class="space-y-10">
            
            <div>
                <div class="flex items-center gap-4 mb-6">
                    <h3 class="text-xs font-bold uppercase tracking-widest text-white whitespace-nowrap">
                        01. Credenciales
                    </h3>
                    <div class="h-[1px] w-full bg-white/10"></div>
                </div>

                <div class="space-y-5">
                    <div>
                        <label for="name" class="block text-[10px] font-bold uppercase tracking-widest text-white/70 mb-2">Nombre Completo</label>
                        <TextInput
                            id="name"
                            type="text"
                            class="block w-full bg-white/5 border-white/20 text-white placeholder-white/20 rounded-sm focus:border-white focus:ring-0 transition-colors py-3 px-4 text-sm"
                            v-model="form.name"
                            placeholder="Nombre y Apellido"
                            required
                            autofocus
                            autocomplete="name"
                        />
                        <InputError class="mt-2 text-red-300 text-xs" :message="form.errors.name" />
                    </div>

                    <div>
                        <label for="email" class="block text-[10px] font-bold uppercase tracking-widest text-white/70 mb-2">Correo Profesional</label>
                        <TextInput
                            id="email"
                            type="email"
                            class="block w-full bg-white/5 border-white/20 text-white placeholder-white/20 rounded-sm focus:border-white focus:ring-0 transition-colors py-3 px-4 text-sm"
                            v-model="form.email"
                            placeholder="contacto@estudio.com"
                            required
                        />
                        <InputError class="mt-2 text-red-300 text-xs" :message="form.errors.email" />
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <label for="password" class="block text-[10px] font-bold uppercase tracking-widest text-white/70 mb-2">Contraseña</label>
                            <TextInput
                                id="password"
                                type="password"
                                class="block w-full bg-white/5 border-white/20 text-white placeholder-white/20 rounded-sm focus:border-white focus:ring-0 transition-colors py-3 px-4 text-sm"
                                v-model="form.password"
                                placeholder="********"
                                required
                            />
                            <InputError class="mt-2 text-red-300 text-xs" :message="form.errors.password" />
                        </div>
                        <div>
                            <label for="password_confirmation" class="block text-[10px] font-bold uppercase tracking-widest text-white/70 mb-2">Confirmar</label>
                            <TextInput
                                id="password_confirmation"
                                type="password"
                                class="block w-full bg-white/5 border-white/20 text-white placeholder-white/20 rounded-sm focus:border-white focus:ring-0 transition-colors py-3 px-4 text-sm"
                                v-model="form.password_confirmation"
                                placeholder="********"
                                required
                            />
                        </div>
                    </div>
                </div>
            </div>

            <div>
                <div class="flex items-center gap-4 mb-6">
                    <h3 class="text-xs font-bold uppercase tracking-widest text-white whitespace-nowrap">
                        02. Perfil Profesional
                    </h3>
                    <div class="h-[1px] w-full bg-white/10"></div>
                </div>

                <div class="space-y-5">
                    <div>
                        <label for="business_name" class="block text-[10px] font-bold uppercase tracking-widest text-white/70 mb-2">Nombre del Estudio / Marca</label>
                        <TextInput
                            id="business_name"
                            type="text"
                            class="block w-full bg-white/5 border-white/20 text-white placeholder-white/20 rounded-sm focus:border-white focus:ring-0 transition-colors py-3 px-4 text-sm"
                            v-model="form.business_name"
                            placeholder="Ej: Visual Arts Lorem ipsum..."
                            required
                        />
                        <InputError class="mt-2 text-red-300 text-xs" :message="form.errors.business_name" />
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <label for="region" class="block text-[10px] font-bold uppercase tracking-widest text-white/70 mb-2">Región Operativa</label>
                            <div class="relative">
                                <select
                                    id="region"
                                    v-model="form.region"
                                    class="block w-full bg-white/5 border-white/20 text-white rounded-sm focus:border-white focus:ring-0 transition-colors py-3 px-4 text-sm appearance-none"
                                    required
                                >
                                    <option value="" disabled class="bg-slate-950 text-gray-400">Seleccionar...</option>
                                    <option v-for="region in regions" :key="region" :value="region" class="bg-slate-950 text-white">
                                        {{ region }}
                                    </option>
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-white/50">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                                </div>
                            </div>
                            <InputError class="mt-2 text-red-300 text-xs" :message="form.errors.region" />
                        </div>

                        <div>
                            <label for="phone" class="block text-[10px] font-bold uppercase tracking-widest text-white/70 mb-2">Teléfono</label>
                            <TextInput
                                id="phone"
                                type="tel"
                                class="block w-full bg-white/5 border-white/20 text-white placeholder-white/20 rounded-sm focus:border-white focus:ring-0 transition-colors py-3 px-4 text-sm"
                                v-model="form.phone"
                                placeholder="+54 9 ..."
                                required
                            />
                            <InputError class="mt-2 text-red-300 text-xs" :message="form.errors.phone" />
                        </div>
                    </div>

                    <div>
                        <label for="bio" class="block text-[10px] font-bold uppercase tracking-widest text-white/70 mb-2">Reseña Profesional</label>
                        <textarea
                            id="bio"
                            v-model="form.bio"
                            rows="3"
                            class="block w-full bg-white/5 border-white/20 text-white placeholder-white/20 rounded-sm focus:border-white focus:ring-0 transition-colors py-3 px-4 text-sm resize-none"
                            placeholder="Describa su experiencia, estilo y equipo técnico..."
                        ></textarea>
                        <div class="flex justify-between mt-1">
                            <InputError class="text-red-300 text-xs" :message="form.errors.bio" />
                            <span class="text-[10px] text-white/30">{{ form.bio?.length || 0 }} / 1000</span>
                        </div>
                    </div>

                    <div>
                        <label class="block text-[10px] font-bold uppercase tracking-widest text-white/70 mb-2">Foto de Perfil / Logo</label>
                        <label for="profile_photo" class="group relative flex flex-col items-center justify-center w-full h-24 border border-white/20 border-dashed rounded-sm cursor-pointer hover:bg-white/5 hover:border-white/40 transition-all duration-300">
                            <div class="flex flex-row items-center gap-3">
                                <svg class="w-6 h-6 text-white/30 group-hover:text-white/60 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" /></svg>
                                <div class="text-left">
                                    <p class="text-xs text-white/60 group-hover:text-white/90 font-light">
                                        <span v-if="form.profile_photo" class="font-medium text-white">{{ form.profile_photo.name }}</span>
                                        <span v-else>Click para subir imagen</span>
                                    </p>
                                    <p v-if="!form.profile_photo" class="text-[9px] text-white/30 mt-0.5 uppercase tracking-wider">JPG, PNG (Max 2MB)</p>
                                </div>
                            </div>
                            <input id="profile_photo" type="file" class="hidden" accept="image/*" @change="handleFileUpload" />
                        </label>
                        <InputError class="mt-2 text-red-300 text-xs" :message="form.errors.profile_photo" />
                    </div>
                </div>
            </div>

            <div>
                <div class="flex items-center gap-4 mb-6">
                    <h3 class="text-xs font-bold uppercase tracking-widest text-white whitespace-nowrap">
                        03. Presencia Digital
                    </h3>
                    <div class="h-[1px] w-full bg-white/10"></div>
                </div>

                <div class="space-y-5">
                    <div>
                        <label for="website" class="block text-[10px] font-bold uppercase tracking-widest text-white/70 mb-2">Sitio Web / Portfolio</label>
                        <TextInput
                            id="website"
                            type="url"
                            class="block w-full bg-white/5 border-white/20 text-white placeholder-white/20 rounded-sm focus:border-white focus:ring-0 transition-colors py-3 px-4 text-sm"
                            v-model="form.website"
                            placeholder="https://www.miestudio.com"
                        />
                        <InputError class="mt-2 text-red-300 text-xs" :message="form.errors.website" />
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <label for="instagram" class="block text-[10px] font-bold uppercase tracking-widest text-white/70 mb-2">Instagram</label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-white/40 text-sm">@</span>
                                <TextInput
                                    id="instagram"
                                    type="text"
                                    class="block w-full pl-8 bg-white/5 border-white/20 text-white placeholder-white/20 rounded-sm focus:border-white focus:ring-0 transition-colors py-3 pr-4 text-sm"
                                    v-model="form.instagram"
                                    placeholder="usuario"
                                />
                            </div>
                            <InputError class="mt-2 text-red-300 text-xs" :message="form.errors.instagram" />
                        </div>
                        <div>
                            <label for="facebook" class="block text-[10px] font-bold uppercase tracking-widest text-white/70 mb-2">Facebook</label>
                            <TextInput
                                id="facebook"
                                type="text"
                                class="block w-full bg-white/5 border-white/20 text-white placeholder-white/20 rounded-sm focus:border-white focus:ring-0 transition-colors py-3 px-4 text-sm"
                                v-model="form.facebook"
                                placeholder="Nombre de página"
                            />
                            <InputError class="mt-2 text-red-300 text-xs" :message="form.errors.facebook" />
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-amber-900/20 border border-amber-700/30 p-4 rounded-sm flex items-start gap-4">
                <svg class="w-5 h-5 text-amber-500/80 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                <div>
                    <h4 class="text-xs font-bold uppercase tracking-widest text-amber-500 mb-1">Proceso de Aprobación</h4>
                    <p class="text-xs text-amber-100/60 leading-relaxed font-light">
                        Su perfil pasará a estado "Pendiente". Nuestro equipo revisará su solicitud en 24-48hs.
                    </p>
                </div>
            </div>

            <div class="pt-2">
                <PrimaryButton
                    class="w-full justify-center !text-black bg-white hover:bg-gray-100 text-slate-900 font-bold py-4 rounded-sm uppercase tracking-widest text-xs border border-transparent transition-all duration-300 shadow-xl"
                    :class="{ 'opacity-70 cursor-not-allowed': form.processing }"
                    :disabled="form.processing"
                >
                    <span v-if="form.processing">Procesando...</span>
                    <span v-else>Enviar Solicitud</span>
                </PrimaryButton>
            </div>

            <p class="text-[10px] text-white/30 text-center leading-relaxed px-4">
                Al enviar esta solicitud, acepta los <a href="#" class="underline hover:text-white transition">Términos Comerciales</a> para profesionales.
            </p>
        </form>

        <template #footer>
            <div class="text-center mt-8 pt-8 border-t border-white/10">
                <p class="text-white/50 text-xs font-light mb-2">
                    ¿Ya tiene una cuenta profesional?
                    <Link :href="route('login')" class="text-white font-bold hover:underline ml-1 transition">
                        Acceso Socios
                    </Link>
                </p>
                <Link :href="route('register')" class="text-[10px] uppercase tracking-widest text-white/40 hover:text-white transition">
                    Registro para Clientes
                </Link>
            </div>
        </template>
    </GuestLayout>
</template>