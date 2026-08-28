<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { ArrowLeftIcon, LockClosedIcon, DocumentTextIcon } from '@heroicons/vue/24/outline';
</script>

<template>
    <Head title="Política de Privacidad" />

    <AppLayout>
        <div class="min-h-screen bg-[#F8F9FA] text-slate-800 font-sans antialiased py-12 pt-28">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-20">

                
                <div class="mb-8">
                    <Link :href="route('home')"
                        class="inline-flex items-center gap-2 bg-white px-4 py-2 rounded-full text-xs font-bold uppercase tracking-wider text-gray-500 hover:text-black hover:shadow-sm border border-gray-200 transition-all">
                        <ArrowLeftIcon class="w-4 h-4" /> Volver al Inicio
                    </Link>
                </div>

            
                <div class="mb-10">
                    <div class="w-16 h-16 bg-blue-50 rounded-full flex items-center justify-center mb-6 shadow-sm border border-blue-100">
                        <LockClosedIcon class="w-8 h-8 text-blue-600" />
                    </div>
                    <h1 class="font-flux text-5xl md:text-7xl text-black tracking-wide leading-none mb-4">
                        Política de <span class="text-blue-600">Privacidad</span>
                    </h1>
                    <p class="text-sm font-bold text-gray-500 uppercase tracking-wider flex items-center gap-2">
                        <DocumentTextIcon class="w-4 h-4" /> Vigente desde: 12 de Agosto de 2026
                    </p>
                </div>

            
                <div class="bg-white border border-gray-100 rounded p-8 md:p-12 shadow-sm">
                    
                    <div class="prose prose-slate max-w-none prose-headings:font-bold prose-h2:text-2xl prose-h2:mb-4 prose-h2:mt-8 prose-p:text-gray-500 prose-p:leading-relaxed prose-li:text-gray-500">
                        
                        <p class="text-lg font-medium text-slate-700">
                            En F33 valoramos y respetamos profundamente tu privacidad. Esta política explica de manera clara y transparente cómo recopilamos, usamos, compartimos y protegemos tu información personal cuando interactuas con nuestra plataforma.
                        </p>

                        <hr class="my-8 border-gray-100" />

                        <h2 class="flex items-center gap-2 text-black">
                            <span class="w-6 h-6 rounded-full bg-gray-100 flex items-center justify-center text-xs text-gray-500">1</span>
                            Información que recopilamos
                        </h2>
                        <p>
                            Recopilamos información que nos proporcionas directamente al registrarte o interactuar con nuestros servicios:
                        </p>
                        <ul class="list-disc pl-5 space-y-2 mt-4">
                            <li><strong>Datos de contacto:</strong> Nombre, correo electrónico y teléfono (si aplica).</li>
                            <li><strong>Datos de cuenta:</strong> Credenciales de acceso seguras.</li>
                            <li><strong>Datos profesionales (solo fotógrafos):</strong> Alias, enlaces de redes sociales, ubicación y biografía.</li>
                        </ul>

                        <h2 class="flex items-center gap-2 text-black">
                            <span class="w-6 h-6 rounded-full bg-gray-100 flex items-center justify-center text-xs text-gray-500">2</span>
                            Uso de inteligencia artificial y biometría
                        </h2>
                        <p>
                            F33 ofrece una función de búsqueda mediante reconocimiento facial para facilitar la localización de fotografías en los eventos.
                        </p>
                        <p class="mt-4 p-4 bg-blue-50 border border-blue-100 rounded text-blue-800 font-medium text-sm">
                            <strong>Nota importante sobre tus selfies:</strong> La imagen que subis temporalmente para usar el escáner facial es procesada localmente o de manera efímera para generar un vector de búsqueda. <strong>F33 no almacena, no comparte ni vende tu fotografía de referencia</strong>. Una vez que cerrás la ventana de búsqueda, dicha imagen se descarta de nuestros sistemas de forma permanente.
                        </p>

                        <h2 class="flex items-center gap-2 text-black">
                            <span class="w-6 h-6 rounded-full bg-gray-100 flex items-center justify-center text-xs text-gray-500">3</span>
                            Información de pagos
                        </h2>
                        <p>
                            Todas las transacciones comerciales son procesadas de forma segura a través de <strong>Mercado Pago</strong>. F33 no tiene acceso, no procesa ni almacena directamente los datos de tus tarjetas de crédito, débito o cuentas bancarias.
                        </p>

                        <h2 class="flex items-center gap-2 text-black">
                            <span class="w-6 h-6 rounded-full bg-gray-100 flex items-center justify-center text-xs text-gray-500">4</span>
                            Cookies y tecnologías similares
                        </h2>
                        <p>
                            Usamos cookies esenciales para mantener tu sesión activa, recordar los artículos en tu carrito de compras y asegurar el correcto funcionamiento técnico de la plataforma. No usamos cookies invasivas de seguimiento publicitario de terceros.
                        </p>

                        <h2 class="flex items-center gap-2 text-black">
                            <span class="w-6 h-6 rounded-full bg-gray-100 flex items-center justify-center text-xs text-gray-500">5</span>
                            Tus derechos
                        </h2>
                        <p>
                            Tenés el derecho de acceder, actualizar o solicitar la eliminación de tu información personal en cualquier momento. Si deseas purgar tu cuenta o revocar cualquier consentimiento, puedes hacerlo desde el panel de configuración o contactándonos directamente.
                        </p>
                    </div>

                
                    <div class="mt-12 pt-8 border-t border-gray-100 flex flex-col sm:flex-row items-center justify-between gap-4">
                        <p class="text-sm font-bold text-gray-400 uppercase tracking-wider">
                            Protección de datos
                        </p>
                        <a href="mailto:contacto@f33.click" class="px-6 py-2.5 bg-gray-50 text-gray-600 rounded-full text-xs font-bold uppercase tracking-wider hover:bg-black hover:text-white transition-colors">
                            contacto@f33.click
                        </a>
                    </div>
                </div>

            </div>
        </div>
    </AppLayout>
</template>