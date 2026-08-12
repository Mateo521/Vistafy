<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import {
    CameraIcon,
    GlobeAltIcon,
    LightBulbIcon,
    ArrowLongRightIcon,
    SparklesIcon
} from '@heroicons/vue/24/outline';
</script>

<template>

    <Head title="Nuestra misión | F33" />

    <AppLayout>
        <div class="min-h-screen bg-[#F8F9FA] text-slate-800 font-sans antialiased">


            <section class="relative pt-32 pb-20 md:pt-40 md:pb-28 overflow-hidden">

                <div
                    class="absolute top-0 right-0 w-[600px] h-[600px] bg-red-50 rounded-full blur-[120px] -translate-y-1/2 translate-x-1/3 pointer-events-none">
                </div>
                <div
                    class="absolute bottom-0 left-0 w-[500px] h-[500px] bg-gray-100 rounded-full blur-[100px] translate-y-1/3 -translate-x-1/4 pointer-events-none">
                </div>

                <div class="max-w-[1200px] mx-auto px-6 lg:px-8 relative z-10 text-center">
                    <span
                        class="inline-flex items-center gap-2 bg-white border border-gray-200 text-xs font-bold uppercase tracking-widest text-gray-500 px-4 py-2 rounded-full mb-8 shadow-sm">
                        <span class="w-2 h-2 rounded-full bg-[#E30613] animate-pulse"></span> Nuestra filosofía
                    </span>

                    <h1 class="text-5xl md:text-7xl lg:text-8xl font-flux text-black leading-[0.9] tracking-wide mb-8">
                        La fotografía cambió,<br>
                        <span class="text-[#E30613]">la forma de venderla no.</span>
                    </h1>

                    <p class="text-xl md:text-2xl text-gray-500 font-medium max-w-3xl mx-auto leading-relaxed">
                        Todos los momentos. Una sola plataforma.
                    </p>
                </div>
            </section>


            <section class="py-16 md:py-24 bg-white border-y border-gray-100 relative z-10">
                <div class="max-w-[1000px] mx-auto px-6 lg:px-8">

                    <div class="grid grid-cols-1 md:grid-cols-12 gap-12 md:gap-16">


                        <div class="hidden md:block md:col-span-4 border-r border-gray-100 pr-8">
                            <div class="sticky top-32 space-y-8">
                                <div>
                                    <LightBulbIcon class="w-8 h-8 text-gray-300 mb-4" />
                                    <h3 class="text-sm font-bold uppercase tracking-wider text-black mb-2">El problema
                                    </h3>
                                    <p class="text-xs text-gray-500 leading-relaxed font-medium">El mayor desafío hoy no
                                        es técnico, es comercial.</p>
                                </div>
                                <div>
                                    <GlobeAltIcon class="w-8 h-8 text-gray-300 mb-4" />
                                    <h3 class="text-sm font-bold uppercase tracking-wider text-black mb-2">La solución
                                    </h3>
                                    <p class="text-xs text-gray-500 leading-relaxed font-medium">Un único lugar donde
                                        fotos y personas se encuentran.</p>
                                </div>
                                <div>
                                    <SparklesIcon class="w-8 h-8 text-[#E30613] mb-4" />
                                    <h3 class="text-sm font-bold uppercase tracking-wider text-[#E30613] mb-2">El
                                        objetivo</h3>
                                    <p class="text-xs text-gray-500 leading-relaxed font-medium">Más tiempo creando,
                                        menos tiempo buscando clientes.</p>
                                </div>
                            </div>
                        </div>


                        <div class="md:col-span-8 space-y-16">


                            <article>
                                <h2 class="text-3xl font-flux text-black mb-6 tracking-wide">¿Por qué creamos F33?</h2>
                                <div class="space-y-4 text-slate-600 font-medium leading-relaxed">
                                    <p>
                                        F33 nace a partir de una inquietud que compartimos quienes vivimos la fotografía
                                        detrás de la cámara: hoy, el mayor desafío de un fotógrafo no es la calidad
                                        técnica de su trabajo, sino encontrar la forma de comercializarlo (que las
                                        personas accedan a las imágenes en un único lugar).
                                    </p>
                                    <p>
                                        Durante años, desarrollar una carrera en la fotografía de eventos significó
                                        enviar decenas de propuestas, competir por precio y depender de que alguien
                                        decidiera contratarte antes siquiera de sacar la cámara de tu casa. Creemos que
                                        ese modelo ya no refleja las posibilidades que ofrece la fotografía hoy.
                                    </p>
                                </div>
                            </article>


                            <article>
                                <h2 class="text-3xl font-flux text-black mb-6 tracking-wide">¿Qué queremos lograr?</h2>
                                <div class="space-y-4 text-slate-600 font-medium leading-relaxed">
                                    <p>
                                        Nuestra misión es transformar la forma en que las fotografías llegan a las
                                        personas. Queremos que cualquier fotógrafa y fotógrafo pueda asistir a un
                                        evento, crear grandes imágenes y ofrecerlas directamente al público de forma
                                        simple, segura y transparente, sin depender de una contratación previa.
                                    </p>
                                    <p>
                                        Al mismo tiempo, buscamos simplificar la experiencia de quienes están del otro
                                        lado, nucleando todas las fotos en un único lugar donde cualquier persona pueda
                                        encontrarlas y adquirirlas, sin limitaciones logísticas y con la certeza de que
                                        siempre estarán ahí.
                                    </p>
                                </div>
                            </article>


                            <article class="bg-gray-50 p-8 md:p-10 rounded border border-gray-100">
                                <h3 class="text-xs font-bold uppercase tracking-widest text-[#E30613] mb-4">En
                                    conclusión</h3>
                                <p class="text-xl text-black font-bold leading-relaxed mb-6">
                                    Queremos construir una nueva alternativa al modelo tradicional: un ecosistema donde
                                    hacer un gran trabajo sea suficiente para tener la oportunidad de venderlo.
                                </p>
                                <p class="text-slate-600 font-medium leading-relaxed">
                                    Nuestro objetivo es simple: que las fotógrafas y fotógrafos puedan dedicar más
                                    tiempo a crear imágenes y menos tiempo a buscar quién las compre.
                                </p>
                            </article>

                        </div>
                    </div>
                </div>
            </section>


            <section class="py-20 md:py-28 bg-[#F8F9FA]">
                <div class="max-w-[1200px] mx-auto px-6 lg:px-8">

                    <div class="text-center mb-16">
                        <h2 class="text-4xl font-flux text-black tracking-wide mb-4">¿Quién está detrás de F33?</h2>
                        <p class="text-gray-500 font-medium">Un equipo dedicado a revolucionar la distribución
                            fotográfica.</p>
                    </div>

                    <!--div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                        
                        
                    

                     
                        <div class="bg-white rounded p-8 border border-gray-100 shadow-sm hover:shadow-lg transition-all duration-300 flex flex-col items-center text-center group">
                            <div class="w-32 h-32 rounded-full bg-gray-100 mb-6 overflow-hidden border-4 border-white shadow-md">
                                <img src="/images/team-2.jpg" alt="Socio Fundador" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" onerror="this.src='https://ui-avatars.com/api/?name=Co+Founder&background=f3f4f6&color=9ca3af&size=200'" />
                            </div>
                            <h3 class="text-xl font-bold text-black mb-1">Nombre</h3>
                            <p class="text-xs font-bold uppercase tracking-wider text-[#E30613] mb-4">Rol</p>
                            <p class="text-sm text-gray-500 font-medium leading-relaxed">
                                Descripción pendiente.
                            </p>
                        </div>

                       
                        <div class="bg-white rounded p-8 border border-gray-100 shadow-sm hover:shadow-lg transition-all duration-300 flex flex-col items-center text-center group">
                            <div class="w-32 h-32 rounded-full bg-gray-100 mb-6 overflow-hidden border-4 border-white shadow-md">
                                <img src="/images/team-3.jpg" alt="Socio Fundador" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" onerror="this.src='https://ui-avatars.com/api/?name=Co+Founder&background=f3f4f6&color=9ca3af&size=200'" />
                            </div>
                            <h3 class="text-xl font-bold text-black mb-1">Nombre</h3>
                            <p class="text-xs font-bold uppercase tracking-wider text-[#E30613] mb-4">rol</p>
                            <p class="text-sm text-gray-500 font-medium leading-relaxed">
                                Descripción pendiente.
                            </p>
                        </div>

                    </div-->


                </div>
            </section>


            <section class="py-24 bg-white border-t border-gray-100">
                <div class="max-w-7xl mx-auto px-6 lg:px-8">
                    <div class="grid md:grid-cols-2 gap-8">


                        <div
                            class="bg-gray-50 p-10 rounded border border-gray-200 hover:shadow-md transition-all duration-300 group">
                            <div
                                class="w-12 h-12 bg-white rounded-full flex items-center justify-center mb-6 shadow-sm">
                                <CameraIcon class="w-6 h-6 text-black" />
                            </div>
                            <h3 class="text-2xl font-bold text-black mb-3">¿Buscas tus fotos?</h3>
                            <p class="text-gray-500 font-medium mb-8">
                                Encontrá ese momento único en segundos usando nuestro escáner biométrico.
                            </p>
                            <Link :href="route('events.index')"
                                class="inline-flex items-center gap-3 text-black font-bold uppercase tracking-wider text-xs bg-white px-6 py-3.5 rounded-full shadow-sm hover:shadow-md hover:bg-black hover:text-white transition-all">
                                Ir a las galerías
                                <ArrowLongRightIcon class="w-4 h-4 group-hover:translate-x-1 transition-transform" />
                            </Link>
                        </div>


                        <div
                            class="bg-black p-10 rounded border border-black hover:shadow-lg hover:shadow-red-500/20 transition-all duration-300 group">
                            <div
                                class="w-12 h-12 bg-white/10 backdrop-blur rounded-full flex items-center justify-center mb-6 border border-white/20">
                                <SparklesIcon class="w-6 h-6 text-white" />
                            </div>
                            <h3 class="text-2xl font-bold text-white mb-3">¿sos fotógrafo?</h3>
                            <p class="text-gray-400 font-medium mb-8">
                                Unite a la plataforma, publicá tu trabajo sin barreras y monetiza tu talento.
                            </p>
                            <Link :href="route('register')"
                                class="inline-flex items-center gap-3 text-white font-bold uppercase tracking-wider text-xs bg-[#E30613] px-6 py-3.5 rounded-full shadow-sm hover:bg-red-700 transition-all">
                                Aplicar como creador
                                <ArrowLongRightIcon class="w-4 h-4 group-hover:translate-x-1 transition-transform" />
                            </Link>
                        </div>

                    </div>
                </div>
            </section>

        </div>
    </AppLayout>
</template>