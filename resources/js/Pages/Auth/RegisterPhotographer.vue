<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
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
        <Head title="Registro de Fotógrafo" />

        <!-- Título -->
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-white mb-2">
                 Únete como Fotógrafo
            </h1>
            <p class="text-white/80">
                Comparte tu trabajo y llega a más clientes
            </p>
        </div>

        <!-- Formulario -->
        <form @submit.prevent="submit" class="space-y-6">
            <!-- Información Personal -->
            <div class="bg-white/5 backdrop-blur-sm rounded-xl p-6 border border-white/10">
                <h3 class="text-white font-bold text-lg mb-4"> Información Personal</h3>
                
                <!-- Nombre Completo -->
                <div class="mb-4">
                    <InputLabel for="name" value="Nombre Completo" class="text-white font-medium mb-2" />
                    <TextInput
                        id="name"
                        type="text"
                        class="block w-full bg-white/10 backdrop-blur-sm border-white/20 text-white placeholder-white/50 rounded-xl"
                        v-model="form.name"
                        placeholder="Tu nombre completo"
                        required
                        autofocus
                    />
                    <InputError class="mt-2 text-red-300" :message="form.errors.name" />
                </div>

                <!-- Email -->
                <div class="mb-4">
                    <InputLabel for="email" value="Correo Electrónico" class="text-white font-medium mb-2" />
                    <TextInput
                        id="email"
                        type="email"
                        class="block w-full bg-white/10 backdrop-blur-sm border-white/20 text-white placeholder-white/50 rounded-xl"
                        v-model="form.email"
                        placeholder="tu@email.com"
                        required
                    />
                    <InputError class="mt-2 text-red-300" :message="form.errors.email" />
                </div>

                <!-- Password -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <InputLabel for="password" value="Contraseña" class="text-white font-medium mb-2" />
                        <TextInput
                            id="password"
                            type="password"
                            class="block w-full bg-white/10 backdrop-blur-sm border-white/20 text-white placeholder-white/50 rounded-xl"
                            v-model="form.password"
                            placeholder="Mínimo 8 caracteres"
                            required
                        />
                        <InputError class="mt-2 text-red-300" :message="form.errors.password" />
                    </div>

                    <div>
                        <InputLabel for="password_confirmation" value="Confirmar Contraseña" class="text-white font-medium mb-2" />
                        <TextInput
                            id="password_confirmation"
                            type="password"
                            class="block w-full bg-white/10 backdrop-blur-sm border-white/20 text-white placeholder-white/50 rounded-xl"
                            v-model="form.password_confirmation"
                            placeholder="Repite tu contraseña"
                            required
                        />
                        <InputError class="mt-2 text-red-300" :message="form.errors.password_confirmation" />
                    </div>
                </div>
            </div>

            <!-- Información Profesional -->
            <div class="bg-white/5 backdrop-blur-sm rounded-xl p-6 border border-white/10">
                <h3 class="text-white font-bold text-lg mb-4"> Información Profesional</h3>

                <!-- Nombre del Negocio -->
                <div class="mb-4">
                    <InputLabel for="business_name" value="Nombre del Negocio / Estudio" class="text-white font-medium mb-2" />
                    <TextInput
                        id="business_name"
                        type="text"
                        class="block w-full bg-white/10 backdrop-blur-sm border-white/20 text-white placeholder-white/50 rounded-xl"
                        v-model="form.business_name"
                        placeholder="Ej: Estudio Lumina"
                        required
                    />
                    <InputError class="mt-2 text-red-300" :message="form.errors.business_name" />
                </div>

                <!-- Región y Teléfono -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <InputLabel for="region" value="Región" class="text-white font-medium mb-2" />
                        <select
                            id="region"
                            v-model="form.region"
                            class="block w-full bg-white/10 backdrop-blur-sm border-white/20 text-white rounded-xl focus:ring-2 focus:ring-white/50"
                            required
                        >
                            <option value="" disabled class="bg-gray-800">Selecciona tu región</option>
                            <option v-for="region in regions" :key="region" :value="region" class="bg-gray-800">
                                {{ region }}
                            </option>
                        </select>
                        <InputError class="mt-2 text-red-300" :message="form.errors.region" />
                    </div>

                    <div>
                        <InputLabel for="phone" value="Teléfono" class="text-white font-medium mb-2" />
                        <TextInput
                            id="phone"
                            type="tel"
                            class="block w-full bg-white/10 backdrop-blur-sm border-white/20 text-white placeholder-white/50 rounded-xl"
                            v-model="form.phone"
                            placeholder="+54 9 11 1234-5678"
                            required
                        />
                        <InputError class="mt-2 text-red-300" :message="form.errors.phone" />
                    </div>
                </div>

                <!-- Biografía -->
                <div class="mb-4">
                    <InputLabel for="bio" value="Biografía / Descripción" class="text-white font-medium mb-2" />
                    <textarea
                        id="bio"
                        v-model="form.bio"
                        rows="4"
                        class="block w-full bg-white/10 backdrop-blur-sm border-white/20 text-white placeholder-white/50 rounded-xl focus:ring-2 focus:ring-white/50 resize-none"
                        placeholder="Cuéntanos sobre tu experiencia, estilo fotográfico, especializaciones..."
                    ></textarea>
                    <InputError class="mt-2 text-red-300" :message="form.errors.bio" />
                    <p class="text-white/50 text-xs mt-1">{{ form.bio?.length || 0 }} / 1000 caracteres</p>
                </div>

                <!-- Foto de Perfil -->
                <div>
                    <InputLabel for="profile_photo" value="Foto de Perfil" class="text-white font-medium mb-2" />
                    <div class="mt-2 flex items-center gap-4">
                        <label
                            for="profile_photo"
                            class="cursor-pointer bg-white/10 backdrop-blur-sm border-2 border-dashed border-white/30 rounded-xl px-6 py-4 text-center hover:bg-white/20 transition flex-1"
                        >
                            <svg class="mx-auto h-12 w-12 text-white/50 mb-2" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            <span class="text-white/80 text-sm">
                                {{ form.profile_photo ? form.profile_photo.name : 'Sube tu foto de perfil' }}
                            </span>
                            <input
                                id="profile_photo"
                                type="file"
                                class="sr-only"
                                accept="image/*"
                                @change="handleFileUpload"
                            />
                        </label>
                    </div>
                    <InputError class="mt-2 text-red-300" :message="form.errors.profile_photo" />
                    <p class="text-white/50 text-xs mt-1">Formato: JPG, PNG. Máximo 2MB</p>
                </div>
            </div>

            <!-- Aviso de Aprobación -->
            <div class="bg-yellow-500/10 backdrop-blur-sm border border-yellow-500/30 rounded-xl p-4">
                <div class="flex gap-3">
                    <svg class="w-6 h-6 text-yellow-300 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <div>
                        <p class="text-yellow-100 font-medium mb-1">Verificación requerida</p>
                        <p class="text-yellow-200/80 text-sm">
                            Tu cuenta será revisada por nuestro equipo antes de activarse. 
                            Te notificaremos por email cuando sea aprobada. Este proceso puede tomar entre 24-48 horas.
                        </p>
                    </div>
                </div>
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
                <span v-else>Registrarme como Fotógrafo</span>
            </PrimaryButton>

            <!-- Términos -->
            <p class="text-xs text-white/60 text-center">
                Al registrarte, aceptas nuestros
                <a href="#" class="underline hover:text-white/80">Términos de Servicio</a>
                y
                <a href="#" class="underline hover:text-white/80">Política de Privacidad</a>
            </p>
        </form>

        <!-- Footer -->
        <template #footer>
            <p class="text-white/80">
                ¿Ya tienes cuenta?
                <Link
                    :href="route('login')"
                    class="font-bold text-white hover:text-white/80 underline transition"
                >
                    Inicia sesión aquí
                </Link>
            </p>
            <p class="text-white/60 text-sm mt-2">
                ¿Eres cliente?
                <Link
                    :href="route('register')"
                    class="text-white/80 hover:text-white underline transition"
                >
                    Regístrate como cliente
                </Link>
            </p>
        </template>
    </GuestLayout>
</template>
