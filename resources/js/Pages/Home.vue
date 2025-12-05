<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';
import RecentPhotosSection from '@/Components/RecentPhotosSection.vue';
import AppLayout from '@/Layouts/AppLayout.vue';
const props = defineProps({
    canLogin: Boolean,
    canRegister: Boolean,
    recentEvents: {
        type: Array,
        default: () => []
    },
    recentPhotos: {
        type: Array,
        default: () => []
    },
    stats: {
        type: Object,
        default: () => ({
            total_photos: 0,
            total_events: 0,
            total_photographers: 0
        })
    }
});

// Animación de conteo para las estadísticas
const animatedPhotos = ref(0);
const animatedEvents = ref(0);
const animatedPhotographers = ref(0);

const animateCount = (target, ref, duration = 2000) => {
    const start = 0;
    const increment = target / (duration / 16); // 60fps

    const timer = setInterval(() => {
        ref.value += increment;
        if (ref.value >= target) {
            ref.value = target;
            clearInterval(timer);
        }
    }, 16);
};

onMounted(() => {
    // Animar los contadores cuando se monta el componente
    setTimeout(() => {
        animateCount(props.stats.total_photos || 0, animatedPhotos);
        animateCount(props.stats.total_events || 0, animatedEvents);
        animateCount(props.stats.total_photographers || 0, animatedPhotographers);
    }, 500);
});

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString('es-ES', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
};

const handleImageError = (e) => {
    // Si falla la imagen, mostrar placeholder con gradiente
    e.target.style.display = 'none';
    const parent = e.target.parentElement;
    if (!parent.querySelector('.placeholder-gradient')) {
        const placeholder = document.createElement('div');
        placeholder.className = 'placeholder-gradient w-full h-full flex items-center justify-center';
        placeholder.innerHTML = `
            <svg class="w-24 h-24 text-white/30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
        `;
        parent.appendChild(placeholder);
    }
};


const videos = [
    '/videos/promo-3.mp4',
    '/videos/promo-4.mp4',
    '/videos/promo-5.mp4',
    '/videos/promo-6.mp4',
    '/videos/promo-7.mp4',
    '/videos/promo-8.mp4',
];

const currentIndex = ref(0);
const currentVideo = ref(videos[currentIndex.value]);
const promoVideo = ref(null);

onMounted(() => {
    const videoEl = promoVideo.value;

    videoEl.addEventListener('ended', () => {
        // avanzar al siguiente video
        currentIndex.value = (currentIndex.value + 1) % videos.length;
        currentVideo.value = videos[currentIndex.value];

        // recargar y reproducir
        videoEl.load();
        videoEl.play();
    });
});





</script>

<template>

    <Head title="Inicio - Empresa" />



    <AppLayout>



        <div class="min-h-screen bg-gray-50">

            <!-- Navigation -->
            <!--nav class="absolute top-0 w-full z-50 bg-transparent">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center h-20">
                    
                    <Link href="/" class="flex items-center space-x-3">
                    <div
                        class="w-12 h-12 bg-white/10 backdrop-blur-md rounded-xl flex items-center justify-center border border-white/20">
                        <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <span class="text-2xl font-bold text-white drop-shadow-lg">
                        Empresa
                    </span>
                    </Link>
 
                    <div class="flex items-center space-x-6">
                        <Link :href="route('events.index')"
                            class="text-white/90 hover:text-white font-medium transition backdrop-blur-sm">
                        Eventos
                        </Link>

                        <template v-if="canLogin">
                            <Link :href="route('login')" class="text-white/90 hover:text-white font-medium transition">
                            Iniciar Sesión
                            </Link>

                            <Link v-if="canRegister" :href="route('register')"
                                class="bg-white/20 backdrop-blur-md text-white px-6 py-2.5 rounded-lg hover:bg-white/30 transition font-semibold border border-white/30">
                            Registrarse
                            </Link>
                        </template>
</div>
</div>
</div>
</nav-->



            <!-- Hero Section with Video Background -->
            <div class="relative h-screen overflow-hidden">
                <!-- Video Background -->
                <video autoplay muted playsinline class="absolute inset-0 w-full h-full object-cover" ref="promoVideo">
                    <source :src="currentVideo" type="video/mp4">
                    Tu navegador no soporta video HTML5.
                </video>


                <!-- Overlay oscuro para mejorar legibilidad -->
                <div class="absolute inset-0 bg-gradient-to-br from-cyan-900/90 via-indigo-900/70 to-orange-900/50">
                </div>

                <!-- Contenido del Hero -->
                <div class="relative h-full flex items-center justify-center">
                    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                        <h1 class="text-5xl md:text-7xl font-bold text-white mb-6 drop-shadow-2xl animate-fade-in">
                            Reviví tus momentos
                            <span
                                class="block mt-2 text-transparent bg-clip-text bg-gradient-to-r from-yellow-300 via-pink-300 to-purple-300">
                                en alta calidad perrix 🤙
                            </span>
                        </h1>
                        <p class="text-xl md:text-2xl text-white/90 mb-10 max-w-2xl mx-auto drop-shadow-lg">
                            Lorem ipsum dolor sit amet consectetur adipisicing elit. Impedit, hic. Iste ...
                        </p>
                        <div class="flex flex-col sm:flex-row gap-4 justify-center">
                            <Link :href="route('gallery.index')"
                                class="bg-white text-purple-900 px-10 py-4 rounded-xl hover:bg-gray-100 transition font-bold text-lg shadow-2xl hover:shadow-white/20 hover:scale-105 transform duration-300">
                            Buscar mis fotos
                            </Link>
                            <a href="#eventos"
                                class="bg-white/10 backdrop-blur-md text-white px-10 py-4 rounded-xl hover:bg-white/20 transition font-bold text-lg shadow-2xl border border-white/30">
                                Ver eventos
                            </a>
                        </div>

                        <!-- Stats en el Hero con animación -->
                        <div class="mt-16 grid grid-cols-3 gap-8 max-w-2xl mx-auto">
                            <div class="text-center backdrop-blur-sm bg-white/5 rounded-2xl p-6 border border-white/10">
                                <div class="text-5xl font-bold text-white mb-2">
                                    {{ Math.floor(animatedPhotos) }}+
                                </div>
                                <div class="text-white/80 text-sm font-medium">Fotos disponibles</div>
                            </div>
                            <div class="text-center backdrop-blur-sm bg-white/5 rounded-2xl p-6 border border-white/10">
                                <div class="text-5xl font-bold text-white mb-2">
                                    {{ Math.floor(animatedEvents) }}+
                                </div>
                                <div class="text-white/80 text-sm font-medium">Eventos</div>
                            </div>
                            <div class="text-center backdrop-blur-sm bg-white/5 rounded-2xl p-6 border border-white/10">
                                <div class="text-5xl font-bold text-white mb-2">
                                    {{ Math.floor(animatedPhotographers) }}+
                                </div>
                                <div class="text-white/80 text-sm font-medium">Fotógrafos</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Scroll indicator -->
                <div class="absolute bottom-8 left-1/2 transform -translate-x-1/2 animate-bounce">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M19 14l-7 7m0 0l-7-7m7 7V3" />
                    </svg>
                </div>
            </div>

            <!-- Sección de Eventos Destacados -->
            <div id="eventos" class="bg-white py-20">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="text-center mb-16">
                        <h2 class="text-4xl md:text-5xl font-bold text-gray-900 mb-4">
                            Eventos Destacados
                        </h2>
                        <p class="text-xl text-gray-600">
                            Descubrí las galerías de nuestros eventos más recientes
                        </p>
                    </div>

                    <div v-if="recentEvents && recentEvents.length > 0"
                        class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 mb-12">
                        <Link v-for="event in recentEvents" :key="event.id" :href="route('events.show', event.slug)"
                            class="group bg-white rounded-2xl shadow-lg overflow-hidden hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-2">
                        <!-- Imagen del Evento -->
                        <div
                            class="relative h-64 overflow-hidden bg-gradient-to-br from-purple-400 via-indigo-500 to-pink-500">
                            <img v-if="event.cover_image_url" :src="event.cover_image_url" :alt="event.name"
                                class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110"
                                @error="handleImageError" />
                            <div v-else class="w-full h-full flex items-center justify-center">
                                <svg class="w-24 h-24 text-white/30" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            </div>

                            <!-- Overlay con info -->
                            <div
                                class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                                <div class="absolute bottom-4 left-4 right-4">
                                    <div class="flex items-center justify-between">
                                        <span
                                            class="bg-white/90 backdrop-blur-sm px-4 py-2 rounded-full text-sm font-bold text-gray-900">
                                            {{ event.photos_count || 0 }} fotos
                                        </span>
                                        <span v-if="!event.is_private"
                                            class="bg-green-500 text-white px-3 py-1 rounded-full text-xs font-bold">
                                            Público
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Info del Evento -->
                        <div class="p-6">
                            <h3
                                class="text-xl font-bold text-gray-900 mb-2 group-hover:text-purple-600 transition line-clamp-1">
                                {{ event.name }}
                            </h3>
                            <p v-if="event.description" class="text-gray-600 text-sm mb-4 line-clamp-2">
                                {{ event.description }}
                            </p>

                            <div class="space-y-2 text-sm text-gray-500">
                                <div class="flex items-center">
                                    <svg class="w-4 h-4 mr-2 text-purple-500" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                    {{ formatDate(event.event_date) }}
                                </div>
                                <div v-if="event.location" class="flex items-center">
                                    <svg class="w-4 h-4 mr-2 text-purple-500" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                    {{ event.location }}
                                </div>
                                <div v-if="event.photographer" class="flex items-center">
                                    <svg class="w-4 h-4 mr-2 text-purple-500" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                    {{ event.photographer }}
                                </div>
                            </div>
                        </div>

                        <!-- CTA -->
                        <div class="px-6 pb-6">
                            <div
                                class="flex items-center justify-center text-purple-600 font-semibold text-sm py-3 border-t group-hover:text-purple-700 transition">
                                Ver galería del evento
                                <svg class="ml-2 w-4 h-4 group-hover:translate-x-1 transition-transform" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M17 8l4 4m0 0l-4 4m4-4H3" />
                                </svg>
                            </div>
                        </div>
                        </Link>
                    </div>

                    <!-- Empty state -->
                    <div v-else class="text-center py-20">
                        <div class="text-8xl mb-6"></div>
                        <h3 class="text-2xl font-bold text-gray-900 mb-2">No hay eventos disponibles</h3>
                        <p class="text-gray-600">Los eventos aparecerán acá próximamente</p>
                    </div>

                    <!-- Botón Ver Todos -->
                    <div class="text-center mt-12">
                        <Link :href="route('events.index')"
                            class="inline-flex items-center bg-gradient-to-r from-purple-600 to-indigo-600 text-white px-10 py-4 rounded-xl hover:from-purple-700 hover:to-indigo-700 transition font-bold text-lg shadow-lg hover:shadow-xl transform hover:scale-105">
                        Ver Todos los Eventos
                        <svg class="ml-3 w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 8l4 4m0 0l-4 4m4-4H3" />
                        </svg>
                        </Link>
                    </div>
                </div>
            </div>
            <RecentPhotosSection :photos="recentPhotos" title="Últimas Fotos Subidas"
                subtitle="Descubrí las fotografías más recientes loremmm " />
            <!-- Footer -->
            <!--footer class="bg-gray-900 text-white py-16">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="text-center">
                        <div class="flex items-center justify-center space-x-3 mb-6">
                            <div
                                class="w-10 h-10 bg-gradient-to-br from-purple-600 to-indigo-600 rounded-lg flex items-center justify-center">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <span class="text-2xl font-bold">Empresa</span>
                        </div>
                        <p class="text-gray-400 mb-8">
                            Capturando tus mejores momentos
                        </p>
                        <p class="text-gray-500 text-sm">
                            © {{ new Date().getFullYear() }} Empresa. Todos los derechos reservados.
                        </p>
                    </div>
                </div>
            </footer-->

        </div>

    </AppLayout>

</template>

<style scoped>
@keyframes fade-in {
    from {
        opacity: 0;
        transform: translateY(20px);
    }

    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.animate-fade-in {
    animation: fade-in 1s ease-out;
}

.placeholder-gradient {
    background: linear-gradient(135deg, rgb(168, 85, 247) 0%, rgb(99, 102, 241) 50%, rgb(236, 72, 153) 100%);
}
</style>
