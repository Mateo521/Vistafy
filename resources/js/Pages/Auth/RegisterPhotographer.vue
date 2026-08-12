<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ArrowRightIcon, PhotoIcon, CheckBadgeIcon, InformationCircleIcon } from '@heroicons/vue/24/outline';

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
        <Head title="Alta de Fotógrafo | F33" />

        <div class="min-h-screen bg-[#F8F9FA] font-sans antialiased py-12 md:py-24 text-slate-800">
            <div class="max-w-[1400px] mx-auto px-4 sm:px-6 lg:px-8 grid grid-cols-1 lg:grid-cols-12 gap-12 lg:gap-20">

                
                <div class="lg:col-span-5 relative">
                    <div class="lg:sticky lg:top-12">

                        <Link href="/" class="inline-block mb-10 group">
                            <span class="font-flux text-5xl md:text-6xl text-black flex items-center gap-1 group-hover:scale-105 transition-transform duration-300">
                                f33<span class="w-3 h-3 rounded-full bg-[#E30613] block mb-2 shadow-sm shadow-red-500/30"></span>
                            </span>
                        </Link>

                        <div class="inline-block bg-red-50 text-[#E30613] text-[10px] font-bold uppercase tracking-widest px-3 py-1.5 rounded-md border border-red-100 mb-6">
                            Portal para profesionales
                        </div>

                        <h1 class="font-flux text-5xl md:text-7xl text-black leading-[0.9] tracking-wide mb-6">
                            Unite a <br><span class="text-[#E30613]">F33</span>
                        </h1>

                        <p class="text-gray-500 text-sm leading-relaxed mb-10 max-w-md font-medium">
                            Buscamos fotógrafos y artistas visuales talentosos. Tu solicitud será evaluada por nuestro equipo bajo los más altos estándares técnicos y estéticos.
                        </p>

                    
                        <div class="bg-white border border-gray-200 rounded p-6 shadow-sm relative overflow-hidden">
                        
                            <div class="flex items-start gap-4">
                                <div class="w-10 h-10 rounded-full bg-red-50 flex items-center justify-center shrink-0">
                                    <InformationCircleIcon class="w-5 h-5 text-[#E30613]" />
                                </div>
                                <div>
                                    <h4 class="font-bold text-black text-sm mb-1 uppercase tracking-wider">Proceso de Curaduría</h4>
                                    <p class="text-xs text-gray-500 leading-relaxed">
                                        Al enviar tus datos, tu perfil quedará en estado <strong class="text-[#E30613]">en espera</strong>. 
                                        Vas a recibir una respuesta en un plazo de 48hs hábiles.
                                    </p>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

            
                <div class="lg:col-span-7 pb-12">
                    <form @submit.prevent="submit" class="space-y-8">

                    
                        <div class="bg-white rounded p-8 md:p-10 shadow-sm border border-gray-100">
                            <h3 class="text-xs font-bold uppercase tracking-widest text-gray-400 mb-6 flex items-center gap-2">
                                <span class="w-6 h-6 rounded-full bg-gray-100 text-black flex items-center justify-center">1</span> 
                                Datos de acceso
                            </h3>

                            <div class="space-y-6">
                                <div>
                                    <label for="name" class="block text-xs font-bold uppercase tracking-wider text-gray-500 mb-2 ml-1">
                                        Nombre completo
                                    </label>
                                    <input id="name" type="text" v-model="form.name" required autofocus placeholder="Nombre y Apellido"
                                        class="w-full bg-gray-50 border border-transparent focus:bg-white focus:border-gray-300 focus:ring-4 focus:ring-gray-100 text-slate-800 font-medium text-sm py-3.5 px-4 rounded transition-all outline-none placeholder-gray-400" />
                                    <p v-if="form.errors.name" class="text-[#E30613] text-xs font-bold mt-2 ml-1">{{ form.errors.name }}</p>
                                </div>

                                <div>
                                    <label for="email" class="block text-xs font-bold uppercase tracking-wider text-gray-500 mb-2 ml-1">
                                        Correo electrónico
                                    </label>
                                    <input id="email" type="email" v-model="form.email" required placeholder="contacto@ejemplo.com"
                                        class="w-full bg-gray-50 border border-transparent focus:bg-white focus:border-gray-300 focus:ring-4 focus:ring-gray-100 text-slate-800 font-medium text-sm py-3.5 px-4 rounded transition-all outline-none placeholder-gray-400" />
                                    <p v-if="form.errors.email" class="text-[#E30613] text-xs font-bold mt-2 ml-1">{{ form.errors.email }}</p>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label for="password" class="block text-xs font-bold uppercase tracking-wider text-gray-500 mb-2 ml-1">
                                            Contraseña
                                        </label>
                                        <input id="password" type="password" v-model="form.password" required placeholder="••••••••"
                                            class="w-full bg-gray-50 border border-transparent focus:bg-white focus:border-gray-300 focus:ring-4 focus:ring-gray-100 text-slate-800 font-medium text-sm py-3.5 px-4 rounded transition-all outline-none placeholder-gray-400" />
                                        <p v-if="form.errors.password" class="text-[#E30613] text-xs font-bold mt-2 ml-1">{{ form.errors.password }}</p>
                                    </div>
                                    <div>
                                        <label for="password_confirmation" class="block text-xs font-bold uppercase tracking-wider text-gray-500 mb-2 ml-1">
                                            Confirmar Contraseña
                                        </label>
                                        <input id="password_confirmation" type="password" v-model="form.password_confirmation" required placeholder="••••••••"
                                            class="w-full bg-gray-50 border border-transparent focus:bg-white focus:border-gray-300 focus:ring-4 focus:ring-gray-100 text-slate-800 font-medium text-sm py-3.5 px-4 rounded transition-all outline-none placeholder-gray-400" />
                                    </div>
                                </div>
                            </div>
                        </div>

                        
                        <div class="bg-white rounded p-8 md:p-10 shadow-sm border border-gray-100">
                            <h3 class="text-xs font-bold uppercase tracking-widest text-gray-400 mb-6 flex items-center gap-2">
                                <span class="w-6 h-6 rounded-full bg-gray-100 text-black flex items-center justify-center">2</span> 
                                Perfil profesional
                            </h3>

                            <div class="space-y-6">
                                <div>
                                    <label for="business_name" class="block text-xs font-bold uppercase tracking-wider text-gray-500 mb-2 ml-1">
                                        Nombre del Estudio o Alias
                                    </label>
                                    <input id="business_name" type="text" v-model="form.business_name" required placeholder="Ej: Visual Arts Studio"
                                        class="w-full bg-gray-50 border border-transparent focus:bg-white focus:border-gray-300 focus:ring-4 focus:ring-gray-100 text-slate-800 font-medium text-sm py-3.5 px-4 rounded transition-all outline-none placeholder-gray-400" />
                                    <p v-if="form.errors.business_name" class="text-[#E30613] text-xs font-bold mt-2 ml-1">{{ form.errors.business_name }}</p>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label for="region" class="block text-xs font-bold uppercase tracking-wider text-gray-500 mb-2 ml-1">
                                            Sector operativo
                                        </label>
                                        <div class="relative">
                                            <select id="region" v-model="form.region" required
                                                class="w-full bg-gray-50 border border-transparent focus:bg-white focus:border-gray-300 focus:ring-4 focus:ring-gray-100 text-slate-800 font-medium text-sm py-3.5 px-4 rounded transition-all outline-none appearance-none cursor-pointer">
                                                <option value="" disabled selected>Selecciona tu región...</option>
                                                <option v-for="region in regions" :key="region" :value="region">{{ region }}</option>
                                            </select>
                                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-gray-500">
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                                            </div>
                                        </div>
                                        <p v-if="form.errors.region" class="text-[#E30613] text-xs font-bold mt-2 ml-1">{{ form.errors.region }}</p>
                                    </div>

                                    <div>
                                        <label for="phone" class="block text-xs font-bold uppercase tracking-wider text-gray-500 mb-2 ml-1">
                                            Teléfono de contacto
                                        </label>
                                        <input id="phone" type="tel" v-model="form.phone" required placeholder="+54 9 ..."
                                            class="w-full bg-gray-50 border border-transparent focus:bg-white focus:border-gray-300 focus:ring-4 focus:ring-gray-100 text-slate-800 font-medium text-sm py-3.5 px-4 rounded transition-all outline-none placeholder-gray-400" />
                                        <p v-if="form.errors.phone" class="text-[#E30613] text-xs font-bold mt-2 ml-1">{{ form.errors.phone }}</p>
                                    </div>
                                </div>

                                <div>
                                    <label for="bio" class="block text-xs font-bold uppercase tracking-wider text-gray-500 mb-2 ml-1">
                                        Biografía / Equipamiento
                                    </label>
                                    <textarea id="bio" v-model="form.bio" rows="3" placeholder="Cuéntanos sobre tu experiencia, equipo actual y estilo fotográfico..."
                                        class="w-full bg-gray-50 border border-transparent focus:bg-white focus:border-gray-300 focus:ring-4 focus:ring-gray-100 text-slate-800 font-medium text-sm py-3.5 px-4 rounded transition-all outline-none resize-none placeholder-gray-400"></textarea>
                                    <div class="flex justify-end mt-1">
                                        <span class="text-xs text-gray-400 font-medium">{{ form.bio?.length || 0 }} / 1000</span>
                                    </div>
                                    <p v-if="form.errors.bio" class="text-[#E30613] text-xs font-bold mt-1 ml-1">{{ form.errors.bio }}</p>
                                </div>

                            
                                <div>
                                    <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 mb-3 ml-1">
                                        Identificador (Foto / Logo)
                                    </label>
                                    <div class="flex items-center gap-6 bg-gray-50 p-4 rounded border border-gray-100">
                                        <div class="w-16 h-16 rounded-full bg-white flex items-center justify-center border border-gray-200 shrink-0 overflow-hidden shadow-sm">
                                            <span v-if="form.profile_photo" class="text-[8px] font-bold text-center px-1 break-words">
                                                {{ form.profile_photo.name }}
                                            </span>
                                            <PhotoIcon v-else class="w-6 h-6 text-gray-300" />
                                        </div>
                                        <div class="flex-1">
                                            <label class="cursor-pointer bg-white border border-gray-200 text-gray-700 px-5 py-2.5 rounded-full text-xs font-bold uppercase tracking-wider hover:bg-gray-100 transition-colors shadow-sm inline-block">
                                                Seleccionar Archivo
                                                <input id="profile_photo" type="file" class="hidden" accept="image/*" @change="handleFileUpload" />
                                            </label>
                                            <p class="text-[10px] text-gray-400 mt-2 font-medium">JPG, PNG. Tamaño máximo recomendado: 2MB.</p>
                                        </div>
                                    </div>
                                    <p v-if="form.errors.profile_photo" class="text-[#E30613] text-xs font-bold mt-2 ml-1">{{ form.errors.profile_photo }}</p>
                                </div>
                            </div>
                        </div>

                    
                        <div class="bg-white rounded p-8 md:p-10 shadow-sm border border-gray-100">
                            <h3 class="text-xs font-bold uppercase tracking-widest text-gray-400 mb-6 flex items-center gap-2">
                                <span class="w-6 h-6 rounded-full bg-gray-100 text-black flex items-center justify-center">3</span> 
                                Presencia web
                            </h3>

                            <div class="space-y-6">
                                <div>
                                    <label for="website" class="block text-xs font-bold uppercase tracking-wider text-gray-500 mb-2 ml-1">
                                        Portafolio web (Opcional)
                                    </label>
                                    <input id="website" type="url" v-model="form.website" placeholder="https://tu-sitio.com"
                                        class="w-full bg-gray-50 border border-transparent focus:bg-white focus:border-gray-300 focus:ring-4 focus:ring-gray-100 text-slate-800 font-medium text-sm py-3.5 px-4 rounded transition-all outline-none placeholder-gray-400" />
                                    <p v-if="form.errors.website" class="text-[#E30613] text-xs font-bold mt-2 ml-1">{{ form.errors.website }}</p>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label for="instagram" class="block text-xs font-bold uppercase tracking-wider text-gray-500 mb-2 ml-1">
                                            Instagram
                                        </label>
                                        <div class="relative">
                                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 font-bold">@</span>
                                            <input id="instagram" type="text" v-model="form.instagram" placeholder="usuario"
                                                class="w-full bg-gray-50 border border-transparent focus:bg-white focus:border-gray-300 focus:ring-4 focus:ring-gray-100 text-slate-800 font-medium text-sm py-3.5 pl-9 pr-4 rounded transition-all outline-none placeholder-gray-400" />
                                        </div>
                                        <p v-if="form.errors.instagram" class="text-[#E30613] text-xs font-bold mt-2 ml-1">{{ form.errors.instagram }}</p>
                                    </div>
                                    
                                    <div>
                                        <label for="facebook" class="block text-xs font-bold uppercase tracking-wider text-gray-500 mb-2 ml-1">
                                            Facebook
                                        </label>
                                        <input id="facebook" type="text" v-model="form.facebook" placeholder="Nombre de página"
                                            class="w-full bg-gray-50 border border-transparent focus:bg-white focus:border-gray-300 focus:ring-4 focus:ring-gray-100 text-slate-800 font-medium text-sm py-3.5 px-4 rounded transition-all outline-none placeholder-gray-400" />
                                        <p v-if="form.errors.facebook" class="text-[#E30613] text-xs font-bold mt-2 ml-1">{{ form.errors.facebook }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                
                        <div class="pt-4">
                            <button type="submit" :disabled="form.processing"
                                class="w-full bg-black text-white font-bold text-sm uppercase tracking-widest py-5 rounded-full hover:bg-[#E30613] hover:shadow-lg hover:shadow-red-500/30 hover:-translate-y-1 transition-all flex items-center justify-center gap-3 disabled:opacity-50 disabled:cursor-not-allowed group">
                                <span v-if="form.processing" class="w-5 h-5 border-2 border-white/30 border-t-white rounded-full animate-spin"></span>
                                <span v-else>Enviar Solicitud</span>
                                <ArrowRightIcon v-if="!form.processing" class="w-5 h-5 group-hover:translate-x-1 transition-transform" />
                            </button>
                            <p class="text-xs text-gray-400 text-center mt-4 font-medium px-4">
                                Al enviar esta solicitud, aceptas los <a href="#" class="text-gray-600 font-bold hover:text-black hover:underline transition-colors">Términos de Servicio</a> y normas de publicación de F33.
                            </p>
                        </div>

                    </form>

                
                    <div class="mt-16 pt-8 border-t border-gray-200 flex flex-col sm:flex-row justify-between items-center gap-4">
                        <p class="text-sm text-gray-500 font-medium">
                            ¿Ya tenés una cuenta aprobada?
                            <Link :href="route('login')" class="text-black font-bold ml-1 hover:text-[#E30613] transition-colors">
                                Iniciar sesión
                            </Link>
                        </p>
                        <Link :href="route('register')" class="text-xs font-bold uppercase tracking-wider text-gray-400 hover:text-black transition-colors">
                            Registro para clientes
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