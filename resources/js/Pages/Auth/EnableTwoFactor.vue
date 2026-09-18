<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { QrCodeIcon, ShieldCheckIcon, DocumentDuplicateIcon } from '@heroicons/vue/24/outline';
import { useToast } from '@/Composables/useToast';

const props = defineProps({
    qrCode: String,
    secret: String,
});

const form = useForm({
    code: '',
});

const { success } = useToast();

const copyToClipboard = () => {
    navigator.clipboard.writeText(props.secret);
    success('Código manual copiado al portapapeles');
};

const submit = () => {
    form.post(route('2fa.confirm'), {
        preserveScroll: true,
        onSuccess: () => form.reset(),
    });
};
</script>

<template>
    <Head title="Configurar autenticación 2FA" />

    <AuthenticatedLayout>
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white border border-gray-200 p-8 rounded-sm shadow-sm">
                    
                    <div class="flex items-center gap-3 mb-6 border-b border-gray-100 pb-4">
                        <ShieldCheckIcon class="w-6 h-6 text-slate-900" />
                        <h2 class="text-lg font-bold text-slate-900">Configurar autenticación de dos factores</h2>
                    </div>

                    <div class="space-y-6 text-sm text-slate-600 leading-relaxed">
                        <p>
                            Para habilitar la autenticación de dos factores, escaneá el siguiente código QR usando la aplicación de autenticación en tu teléfono (como <strong>Google Authenticator</strong> o <strong>Authy</strong>).
                        </p>


                        <div class="flex flex-col items-center justify-center p-6 bg-slate-50 border border-slate-100 rounded-sm">
                            <img :src="qrCode" alt="Código QR 2FA" class="w-48 h-48 bg-white p-2 border border-gray-200 shadow-sm rounded-sm" />
                            
                            <div class="mt-6 text-center">
                                <p class="text-xs font-bold uppercase tracking-widest text-slate-400 mb-2">¿No podés escanear el QR?</p>
                                <p class="text-xs text-slate-500 mb-2">Ingresá este código manualmente en tu aplicación:</p>
                                <div class="flex items-center justify-center gap-2">
                                    <code class="bg-white px-3 py-1.5 border border-gray-200 rounded-sm text-slate-900 font-mono text-sm tracking-widest">{{ secret }}</code>
                                    <button @click="copyToClipboard" class="text-slate-400 hover:text-slate-900 transition" title="Copiar código">
                                        <DocumentDuplicateIcon class="w-5 h-5" />
                                    </button>
                                </div>
                            </div>
                        </div>


                        <div class="pt-4 border-t border-gray-100">
                            <p class="mb-4">Para confirmar que configuraste la aplicación correctamente, Ingresá el código de 6 dígitos que aparece en tu teléfono.</p>
                            
                            <form @submit.prevent="submit" class="max-w-xs">
                                <div>
                                    <label class="block text-xs font-bold uppercase tracking-wide text-slate-500 mb-2">Código de verificación</label>
                                    <input 
                                        v-model="form.code" 
                                        type="text" 
                                        inputmode="numeric" 
                                        maxlength="6" 
                                        pattern="[0-9]*"
                                        class="w-full text-center tracking-[0.5em] font-mono text-lg border-gray-300 rounded-sm focus:border-slate-900 focus:ring-0 text-slate-900 placeholder-slate-300"
                                        placeholder="123456"
                                        required 
                                        autofocus
                                    />
                                    <p v-if="form.errors.code" class="text-red-600 text-xs mt-2">{{ form.errors.code }}</p>
                                </div>

                                <div class="flex items-center gap-4 mt-6">
                                    <button 
                                        type="submit" 
                                        :disabled="form.processing"
                                        class="bg-slate-900 text-white px-6 py-2.5 rounded-sm text-[10px] font-bold uppercase tracking-widest hover:bg-slate-800 transition shadow-sm disabled:opacity-50"
                                    >
                                        <span v-if="form.processing">Verificando...</span>
                                        <span v-else>Confirmar y Activar</span>
                                    </button>
                                    
                                    <Link :href="route('profile.edit')" class="text-[10px] font-bold uppercase tracking-widest text-slate-500 hover:text-slate-900 transition">
                                        Cancelar
                                    </Link>
                                </div>
                            </form>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>