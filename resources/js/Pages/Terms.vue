<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { ArrowLeftIcon, ShieldCheckIcon, DocumentTextIcon } from '@heroicons/vue/24/outline';
</script>

<template>
    <Head title="Términos y Condiciones" />

    <AppLayout>
        <div class="min-h-screen bg-[#F8F9FA] text-slate-800 font-sans antialiased py-12 pt-28">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-20">

            
                <div class="mb-8">
                    <Link :href="route('home')"
                        class="inline-flex items-center gap-2 bg-white px-4 py-2 rounded-full text-xs font-bold uppercase tracking-wider text-gray-500 hover:text-black hover:shadow-sm border border-gray-200 transition-all">
                        <ArrowLeftIcon class="w-4 h-4" /> Volver al inicio
                    </Link>
                </div>

                
                <div class="mb-10">
                    <div class="w-16 h-16 bg-red-50 rounded-full flex items-center justify-center mb-6 shadow-sm border border-red-100">
                        <ShieldCheckIcon class="w-8 h-8 text-[#E30613]" />
                    </div>
                    <h1 class="font-flux text-5xl md:text-7xl text-black tracking-wide leading-none mb-4">
                        Términos y <span class="text-[#E30613]">condiciones</span>
                    </h1>
                    <p class="text-sm font-bold text-gray-500 uppercase tracking-wider flex items-center gap-2">
                        <DocumentTextIcon class="w-4 h-4" /> Última actualización: 12 de Agosto de 2026
                    </p>
                </div>

            
                <div class="bg-white border border-gray-100 rounded p-8 md:p-12 shadow-sm">
                    
                    <div class="prose prose-slate max-w-none prose-headings:font-bold prose-h2:text-2xl prose-h2:mb-4 prose-h2:mt-8 prose-p:text-gray-500 prose-p:leading-relaxed prose-li:text-gray-500">
                        
                        <p class="text-lg font-medium text-slate-700">
                            Bienvenido a F33. Al acceder y usar nuestro sitio web, plataforma y servicios, acepta estar sujeto a los siguientes términos y condiciones. Por favor, léalos detenidamente antes de hacer cualquier transacción.
                        </p>

                        <hr class="my-8 border-gray-100" />

                        <h2 class="flex items-center gap-2 text-black">
                            <span class="w-6 h-6 rounded-full bg-gray-100 flex items-center justify-center text-xs text-gray-500">1</span>
                            Uso de la plataforma
                        </h2>
                        <p>
                            F33 es una plataforma que conecta a fotógrafos con clientes y asistentes a eventos. Los usuarios pueden explorar catálogos, buscar fotografías mediante inteligencia artificial (biometría y lectura de dorsales) y adquirir licencias de uso de tales imágenes.
                        </p>

                        <h2 class="flex items-center gap-2 text-black">
                            <span class="w-6 h-6 rounded-full bg-gray-100 flex items-center justify-center text-xs text-gray-500">2</span>
                            Propiedad intelectual y licencias
                        </h2>
                        <p>
                            Todas las fotografías mostradas en la plataforma con la marca de agua de F33 son propiedad intelectual de los fotógrafos registrados. Al adquirir una fotografía, usted compra una <strong>Licencia de Uso Estándar</strong>, la cual le permite:
                        </p>
                        <ul class="list-disc pl-5 space-y-2 mt-4">
                            <li>Uso personal y no comercial en redes sociales.</li>
                            <li>Impresión para uso estrictamente privado.</li>
                        </ul>
                        <p class="mt-4">
                            Queda estrictamente prohibida la reventa de las imágenes, su uso en campañas publicitarias masivas, o la remoción no autorizada de marcas de agua mediante software de terceros antes de la compra.
                        </p>

                        <h2 class="flex items-center gap-2 text-black">
                            <span class="w-6 h-6 rounded-full bg-gray-100 flex items-center justify-center text-xs text-gray-500">3</span>
                            Pagos y entregas
                        </h2>
                        <p>
                            Todos los pagos se procesan a través de la pasarela de Mercado Pago. Una vez acreditado el pago, el archivo original en alta resolución se va a habilitar automáticamente para su descarga en la sección de sus compras o se va a enviar un enlace seguro al correo proporcionado en el caso de usuarios invitados.
                        </p>
                        <p>
                            Debido a la naturaleza de los bienes digitales, <strong>no se realizarán reembolsos</strong> una vez que el archivo original haya sido descargado, salvo que se demuestre un error técnico por parte del sistema F33.
                        </p>

                        <h2 class="flex items-center gap-2 text-black">
                            <span class="w-6 h-6 rounded-full bg-gray-100 flex items-center justify-center text-xs text-gray-500">4</span>
                            Privacidad y datos biométricos
                        </h2>
                        <p>
                            Para usar la función de reconocimiento facial, los usuarios pueden subir una imagen de referencia (selfie). F33 garantiza que estas imágenes de referencia se procesan en tiempo real exclusivamente para arrojar resultados en la galería y <strong>no se almacenan permanentemente</strong> en nuestras bases de datos tras terminar la sesión de búsqueda.
                        </p>

                        <h2 class="flex items-center gap-2 text-black">
                            <span class="w-6 h-6 rounded-full bg-gray-100 flex items-center justify-center text-xs text-gray-500">5</span>
                            Modificaciones de los términos
                        </h2>
                        <p>
                            F33 se reserva el derecho de modificar o reemplazar estos términos en cualquier momento. Si una revisión es importante, vamos a intentar proporcionar un aviso con al menos 15 días de antelación antes de que los nuevos términos entren en vigencia. El uso continuo de la plataforma tras dichos cambios constituye la aceptación de los nuevos términos.
                        </p>
                    </div>

                
                    <div class="mt-12 pt-8 border-t border-gray-100 flex flex-col sm:flex-row items-center justify-between gap-4">
                        <p class="text-sm font-bold text-gray-400 uppercase tracking-wider">
                            F33  - San Luis, Argentina
                        </p>
                        <a href="mailto:soporte@f33.click" class="px-6 py-2.5 bg-gray-50 text-gray-600 rounded-full text-xs font-bold uppercase tracking-wider hover:bg-black hover:text-white transition-colors">
                            Contactar soporte
                        </a>
                    </div>
                </div>

            </div>
        </div>
    </AppLayout>
</template>