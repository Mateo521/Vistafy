<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { 
    EnvelopeIcon, 
    PhoneIcon, 
    MapPinIcon,
    CheckCircleIcon 
} from '@heroicons/vue/24/outline';

const form = useForm({
    name: '',
    email: '',
    phone: '',
    subject: '',
    message: '',
});

const submit = () => {
    form.post(route('contact.store'), {
        preserveScroll: true,
        onSuccess: () => form.reset(),
    });
};
</script>

<template>
    <Head title="Contacto" />

    <AppLayout>
        <!-- Header -->
        <div class="bg-white border-b border-gray-100 pt-24 pb-12 md:pt-32 md:pb-16">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="max-w-3xl">
                    <span class="text-xs font-bold tracking-[0.2em] text-slate-400 uppercase mb-2 block">
                        Estamos acá para ayudarte
                    </span>
                    <h1 class="text-4xl md:text-5xl font-serif font-bold text-slate-900">
                        Contacto
                    </h1>
                    <p class="text-lg text-slate-600 mt-4 font-light">
                        ¿Tenés alguna pregunta? Completa el formulario y te vamos a responder a la brevedad.
                    </p>
                </div>
            </div>
        </div>

        <div class="min-h-screen bg-gray-50 py-16">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                
                <!-- Alerta de éxito -->
                <div v-if="$page.props.flash.success" 
                    class="mb-8 bg-emerald-50 border-2 border-emerald-200 rounded-sm p-6 flex items-start gap-4 animate-fade-in-down">
                    <CheckCircleIcon class="w-6 h-6 text-emerald-600 flex-shrink-0 mt-0.5" />
                    <div>
                        <h3 class="font-bold text-emerald-900 mb-1">¡Mensaje Enviado!</h3>
                        <p class="text-sm text-emerald-700">{{ $page.props.flash.success }}</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-12">
                    
                    <!-- Información de contacto -->
                    <div class="lg:col-span-1 space-y-8">
                        <div class="bg-white border border-gray-200 p-8 rounded-sm shadow-sm">
                            <h2 class="text-sm font-bold uppercase tracking-widest text-slate-900 mb-6">
                                Información
                            </h2>
                            
                            <div class="space-y-6">
                                <div class="flex items-start gap-4">
                                    <div class="p-3 bg-slate-50 border border-gray-200 rounded-sm">
                                        <EnvelopeIcon class="w-5 h-5 text-slate-600" />
                                    </div>
                                    <div>
                                        <p class="text-xs font-bold uppercase tracking-wide text-slate-500 mb-1">Email</p>
                                        <a href="mailto:info@tudominio.com" 
                                            class="text-slate-900 hover:text-blue-600 transition">
                                            info@tudominio.com
                                        </a>
                                    </div>
                                </div>

                                <div class="flex items-start gap-4">
                                    <div class="p-3 bg-slate-50 border border-gray-200 rounded-sm">
                                        <PhoneIcon class="w-5 h-5 text-slate-600" />
                                    </div>
                                    <div>
                                        <p class="text-xs font-bold uppercase tracking-wide text-slate-500 mb-1">Teléfono</p>
                                        <a href="tel:+1234567890" 
                                            class="text-slate-900 hover:text-blue-600 transition">
                                            +1 (234) 567-890
                                        </a>
                                    </div>
                                </div>

                                <div class="flex items-start gap-4">
                                    <div class="p-3 bg-slate-50 border border-gray-200 rounded-sm">
                                        <MapPinIcon class="w-5 h-5 text-slate-600" />
                                    </div>
                                    <div>
                                        <p class="text-xs font-bold uppercase tracking-wide text-slate-500 mb-1">Ubicación</p>
                                        <p class="text-slate-900">
                                            Ciudad de México<br>
                                            México
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="bg-slate-900 text-white p-8 rounded-sm">
                            <h3 class="text-sm font-bold uppercase tracking-widest mb-4">Horario de Atención</h3>
                            <div class="space-y-2 text-sm">
                                <div class="flex justify-between border-b border-white/10 pb-2">
                                    <span class="text-white/60">Lunes - Viernes</span>
                                    <span class="font-mono">9:00 - 18:00</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-white/60">Sábados</span>
                                    <span class="font-mono">10:00 - 14:00</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Formulario -->
                    <div class="lg:col-span-2">
                        <div class="bg-white border border-gray-200 p-8 md:p-12 rounded-sm shadow-sm">
                            <h2 class="text-2xl font-serif font-bold text-slate-900 mb-8">
                                Envíanos un mensaje
                            </h2>

                            <form @submit.prevent="submit" class="space-y-6">
                                
                                <!-- Nombre -->
                                <div>
                                    <label class="block text-xs font-bold uppercase tracking-widest text-slate-900 mb-2">
                                        Nombre Completo *
                                    </label>
                                    <input 
                                        v-model="form.name"
                                        type="text" 
                                        class="w-full px-4 py-3 border-2 border-gray-200 focus:border-slate-900 focus:ring-0 rounded-sm text-slate-900"
                                        :class="{ 'border-red-500': form.errors.name }"
                                        placeholder="Juan Pérez"
                                    />
                                    <p v-if="form.errors.name" class="mt-2 text-xs text-red-600">
                                        {{ form.errors.name }}
                                    </p>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <!-- Email -->
                                    <div>
                                        <label class="block text-xs font-bold uppercase tracking-widest text-slate-900 mb-2">
                                            Correo Electrónico *
                                        </label>
                                        <input 
                                            v-model="form.email"
                                            type="email" 
                                            class="w-full px-4 py-3 border-2 border-gray-200 focus:border-slate-900 focus:ring-0 rounded-sm"
                                            :class="{ 'border-red-500': form.errors.email }"
                                            placeholder="juan@ejemplo.com"
                                        />
                                        <p v-if="form.errors.email" class="mt-2 text-xs text-red-600">
                                            {{ form.errors.email }}
                                        </p>
                                    </div>

                                    <!-- Teléfono -->
                                    <div>
                                        <label class="block text-xs font-bold uppercase tracking-widest text-slate-900 mb-2">
                                            Teléfono (Opcional)
                                        </label>
                                        <input 
                                            v-model="form.phone"
                                            type="tel" 
                                            class="w-full px-4 py-3 border-2 border-gray-200 focus:border-slate-900 focus:ring-0 rounded-sm"
                                            placeholder="+52 123 456 7890"
                                        />
                                    </div>
                                </div>

                                <!-- Asunto -->
                                <div>
                                    <label class="block text-xs font-bold uppercase tracking-widest text-slate-900 mb-2">
                                        Asunto *
                                    </label>
                                    <input 
                                        v-model="form.subject"
                                        type="text" 
                                        class="w-full px-4 py-3 border-2 border-gray-200 focus:border-slate-900 focus:ring-0 rounded-sm"
                                        :class="{ 'border-red-500': form.errors.subject }"
                                        placeholder="¿En qué podemos ayudarte?"
                                    />
                                    <p v-if="form.errors.subject" class="mt-2 text-xs text-red-600">
                                        {{ form.errors.subject }}
                                    </p>
                                </div>

                                <!-- Mensaje -->
                                <div>
                                    <label class="block text-xs font-bold uppercase tracking-widest text-slate-900 mb-2">
                                        Mensaje *
                                    </label>
                                    <textarea 
                                        v-model="form.message"
                                        rows="6"
                                        class="w-full px-4 py-3 border-2 border-gray-200 focus:border-slate-900 focus:ring-0 rounded-sm resize-none"
                                        :class="{ 'border-red-500': form.errors.message }"
                                        placeholder="Escribe tu mensaje acá..."
                                    ></textarea>
                                    <p v-if="form.errors.message" class="mt-2 text-xs text-red-600">
                                        {{ form.errors.message }}
                                    </p>
                                </div>

                                <button 
                                    type="submit" 
                                    :disabled="form.processing"
                                    class="w-full bg-slate-900 text-white py-4 px-8 rounded-sm text-xs font-bold uppercase tracking-widest hover:bg-slate-800 transition-colors disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-3">
                                    <svg v-if="form.processing" class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    <span>{{ form.processing ? 'Enviando...' : 'Enviar Mensaje' }}</span>
                                </button>

                            </form>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
@keyframes fadeInDown {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.animate-fade-in-down {
    animation: fadeInDown 0.3s ease-out;
}
</style>
