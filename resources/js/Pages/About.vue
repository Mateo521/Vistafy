<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { 
    FaceSmileIcon, 
    ShieldCheckIcon, 
    CursorArrowRaysIcon,
    CameraIcon,
    UserGroupIcon,
    ArrowLongRightIcon,
    MapPinIcon
} from '@heroicons/vue/24/outline';
</script>

<template>
    <Head title="Nuestra Misión | Plataforma de Fotografía" />

    <AppLayout>
        <section class="relative bg-white text-zinc-900 pt-32 pb-24 border-b border-zinc-100">
            <div class="max-w-7xl mx-auto px-6 lg:px-8">
                <div class="max-w-4xl mx-auto text-center">
                    <p class="text-xs font-bold tracking-[0.25em] text-zinc-400 uppercase mb-8">
                        La nueva era de la fotografía
                    </p>
                    <h1 class="text-5xl md:text-7xl font-serif font-medium mb-8 leading-[1.1] text-black">
                        No busques tu foto.<br>
                        <span class="italic text-zinc-500">Deja que ella te encuentre a ti.</span>
                    </h1>
                    <p class="text-xl text-zinc-600 mb-12 font-light max-w-2xl mx-auto leading-relaxed">
                        Transformamos el caos de miles de imágenes en una experiencia personal. Conectamos fotógrafos profesionales con asistentes a eventos a través de tecnología invisible.
                    </p>
                </div>
            </div>
        </section>

        <section class="py-24 bg-zinc-50">
            <div class="max-w-7xl mx-auto px-6 lg:px-8">
                <div class="grid md:grid-cols-2 gap-16 items-center">
                    <div>
                        <h2 class="text-3xl md:text-4xl font-serif text-black mb-6 leading-tight">
                            El fin de buscar una aguja en un pajar.
                        </h2>
                        <div class="h-0.5 w-16 bg-black mb-8"></div>
                        <div class="space-y-6 text-lg text-zinc-600 font-light leading-relaxed">
                            <p>
                                Imagina haber asistido a una maratón, un concierto o una gran boda. Sabes que te tomaron fotos, pero encontrarlas implica revisar manualmente álbumes con miles de imágenes durante horas.
                            </p>
                            <p>
                                <strong class="text-black font-medium">Esa era terminó.</strong>
                            </p>
                            <p>
                                Creamos un ecosistema donde la Inteligencia Artificial hace el trabajo pesado. Solo necesitas una selfie para desbloquear tus recuerdos en segundos, protegiendo siempre tu privacidad y el trabajo del artista.
                            </p>
                        </div>
                    </div>
                    <div class="relative">
                        <div class="aspect-square bg-white border border-zinc-200 p-8 flex flex-col justify-center items-center text-center shadow-sm">
                            <FaceSmileIcon class="w-24 h-24 text-zinc-200 stroke-1 mb-6" />
                            <p class="font-serif text-2xl text-black mb-2">Reconocimiento Facial</p>
                            <p class="text-zinc-400 text-sm tracking-widest uppercase">Tecnología Biométrica</p>
                        </div>
                        <div class="absolute -bottom-6 -right-6 w-full h-full border border-zinc-300 -z-10"></div>
                    </div>
                </div>
            </div>
        </section>

        <section class="py-24 bg-white border-t border-zinc-100">
            <div class="max-w-7xl mx-auto px-6 lg:px-8">
                <div class="text-center mb-20">
                    <h2 class="text-3xl font-serif text-black mb-4">Un ecosistema, dos protagonistas</h2>
                    <p class="text-zinc-500 font-light">Diseñado para conectar creadores y protagonistas.</p>
                </div>

                <div class="grid lg:grid-cols-2 gap-12 lg:divide-x lg:divide-zinc-100">
                    
                    <div class="lg:pr-12">
                        <div class="flex items-center gap-4 mb-8">
                            <div class="p-3 bg-zinc-50 rounded-full border border-zinc-100">
                                <UserGroupIcon class="w-6 h-6 text-black" />
                            </div>
                            <h3 class="text-2xl font-serif text-black">Para Asistentes</h3>
                        </div>
                        <ul class="space-y-12">
                            <li class="flex gap-6">
                                <span class="text-4xl font-serif text-zinc-200">01</span>
                                <div>
                                    <h4 class="text-lg font-bold text-black mb-2">Búsqueda Instantánea</h4>
                                    <p class="text-zinc-600 font-light text-sm leading-relaxed">
                                        Olvídate del scroll infinito. Sube una selfie y nuestra IA escaneará miles de fotos para encontrar tu rostro en menos de 3 segundos.
                                    </p>
                                </div>
                            </li>
                            <li class="flex gap-6">
                                <span class="text-4xl font-serif text-zinc-200">02</span>
                                <div>
                                    <h4 class="text-lg font-bold text-black mb-2">Compra Segura</h4>
                                    <p class="text-zinc-600 font-light text-sm leading-relaxed">
                                        Adquiere tus recuerdos en alta resolución. Pagos encriptados y precios transparentes definidos por el fotógrafo.
                                    </p>
                                </div>
                            </li>
                            <li class="flex gap-6">
                                <span class="text-4xl font-serif text-zinc-200">03</span>
                                <div>
                                    <h4 class="text-lg font-bold text-black mb-2">Privacidad Total</h4>
                                    <p class="text-zinc-600 font-light text-sm leading-relaxed">
                                        Tus fotos originales están protegidas hasta la compra. Utilizamos enlaces temporales únicos para garantizar que solo tú tengas acceso a tu compra.
                                    </p>
                                </div>
                            </li>
                        </ul>
                    </div>

                    <div class="lg:pl-12 mt-16 lg:mt-0">
                        <div class="flex items-center gap-4 mb-8">
                            <div class="p-3 bg-zinc-50 rounded-full border border-zinc-100">
                                <CameraIcon class="w-6 h-6 text-black" />
                            </div>
                            <h3 class="text-2xl font-serif text-black">Para Fotógrafos</h3>
                        </div>
                        <ul class="space-y-12">
                            <li class="flex gap-6">
                                <span class="text-4xl font-serif text-zinc-200">01</span>
                                <div>
                                    <h4 class="text-lg font-bold text-black mb-2">Monetización Directa</h4>
                                    <p class="text-zinc-600 font-light text-sm leading-relaxed">
                                        Tú defines el precio. Nosotros ponemos la plataforma para que vendas tu trabajo sin intermediarios ni complicaciones logísticas.
                                    </p>
                                </div>
                            </li>
                            <li class="flex gap-6">
                                <span class="text-4xl font-serif text-zinc-200">02</span>
                                <div>
                                    <h4 class="text-lg font-bold text-black mb-2">Protección de Autoría</h4>
                                    <p class="text-zinc-600 font-light text-sm leading-relaxed">
                                        Sistema automático de marcas de agua dinámicas y visualización protegida. Tu trabajo original nunca se expone sin pago previo.
                                    </p>
                                </div>
                            </li>
                            <li class="flex gap-6">
                                <span class="text-4xl font-serif text-zinc-200">03</span>
                                <div>
                                    <h4 class="text-lg font-bold text-black mb-2">Oportunidades Futuras</h4>
                                    <p class="text-zinc-600 font-light text-sm leading-relaxed">
                                        Publica tu agenda. Permite que los asistentes sepan dónde estarás fotografiando próximamente a través de nuestro mapa interactivo.
                                    </p>
                                </div>
                            </li>
                        </ul>
                    </div>

                </div>
            </div>
        </section>

        <section class="py-24 bg-black text-white">
            <div class="max-w-7xl mx-auto px-6 lg:px-8">
                <div class="grid md:grid-cols-3 gap-12 text-center md:text-left">
                    <div class="space-y-4">
                        <CursorArrowRaysIcon class="w-10 h-10 text-white mx-auto md:mx-0 stroke-1" />
                        <h3 class="text-xl font-serif">Simplicidad Radical</h3>
                        <p class="text-zinc-400 font-light leading-relaxed">
                            No necesitas registrarte para buscar. Diseñamos un flujo sin fricción: sube tu selfie, encuentra tus fotos, compra y descarga.
                        </p>
                    </div>
                    <div class="space-y-4">
                        <ShieldCheckIcon class="w-10 h-10 text-white mx-auto md:mx-0 stroke-1" />
                        <h3 class="text-xl font-serif">Seguridad Bancaria</h3>
                        <p class="text-zinc-400 font-light leading-relaxed">
                            Integración con Mercado Pago y sistemas de tokens efímeros. Tus datos financieros y tus archivos personales nunca están en riesgo.
                        </p>
                    </div>
                    <div class="space-y-4">
                        <MapPinIcon class="w-10 h-10 text-white mx-auto md:mx-0 stroke-1" />
                        <h3 class="text-xl font-serif">Siempre Conectados</h3>
                        <p class="text-zinc-400 font-light leading-relaxed">
                            Desde una maratón local hasta un festival internacional. Geolocalizamos eventos para que sepas qué fotógrafos cubrieron tu zona.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <section class="py-32 bg-zinc-50">
            <div class="max-w-7xl mx-auto px-6 lg:px-8">
                <div class="text-center mb-16">
                    <h2 class="text-4xl font-serif text-black mb-6">Empieza tu experiencia</h2>
                </div>

                <div class="grid md:grid-cols-2 gap-8 max-w-4xl mx-auto">
                    <div class="bg-white p-10 border border-zinc-200 hover:border-zinc-400 transition-all duration-300 group">
                        <h3 class="text-2xl font-serif text-black mb-4">¿Buscas tus fotos?</h3>
                        <p class="text-zinc-600 font-light mb-8 h-12">
                            Encontrá ese momento único en segundos.
                        </p>
                        <Link 
                            :href="route('events.index')"
                            class="inline-flex items-center gap-3 text-black font-bold tracking-wider text-sm border-b border-black pb-1 hover:text-zinc-600 hover:border-zinc-600 transition-all"
                        >
                            IR A LA GALERÍA
                            <ArrowLongRightIcon class="w-4 h-4 group-hover:translate-x-1 transition-transform" />
                        </Link>
                    </div>

                    <div class="bg-black p-10 text-white group">
                        <h3 class="text-2xl font-serif mb-4">Sos fotógrafo?</h3>
                        <p class="text-zinc-400 font-light mb-8 h-12">
                            Únete a la plataforma y monetiza tu talento.
                        </p>
                        <Link 
                            :href="route('register')" 
                            class="inline-flex items-center gap-3 text-white font-bold tracking-wider text-sm border-b border-white pb-1 hover:text-zinc-300 hover:border-zinc-300 transition-all"
                        >
                            APLICAR COMO FOTÓGRAFO
                            <ArrowLongRightIcon class="w-4 h-4 group-hover:translate-x-1 transition-transform" />
                        </Link>
                    </div>
                </div>
            </div>
        </section>
    </AppLayout>
</template>