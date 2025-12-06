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

// --- Lógica de Animación de Estadísticas ---
const animatedPhotos = ref(0);
const animatedEvents = ref(0);
const animatedPhotographers = ref(0);

const animateCount = (target, refVar, duration = 2000) => {
    const increment = target / (duration / 16); 
    const timer = setInterval(() => {
        refVar.value += increment;
        if (refVar.value >= target) {
            refVar.value = target;
            clearInterval(timer);
        }
    }, 16);
};

// --- Lógica de Video Background (Restaurada) ---
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
    // Iniciar contadores
    setTimeout(() => {
        animateCount(props.stats.total_photos || 0, animatedPhotos);
        animateCount(props.stats.total_events || 0, animatedEvents);
        animateCount(props.stats.total_photographers || 0, animatedPhotographers);
    }, 500);

    // Lógica de rotación de video
    const videoEl = promoVideo.value;
    if (videoEl) {
        videoEl.addEventListener('ended', () => {
            currentIndex.value = (currentIndex.value + 1) % videos.length;
            currentVideo.value = videos[currentIndex.value];
            videoEl.load();
            videoEl.play();
        });
    }
});

const formatDate = (dateString) => {
    if (!dateString) return '';
    return new Date(dateString).toLocaleDateString('es-ES', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
};

const handleImageError = (e) => {
    e.target.style.display = 'none';
    const parent = e.target.parentElement;
    if (!parent.querySelector('.placeholder-institutional')) {
        const placeholder = document.createElement('div');
        placeholder.className = 'placeholder-institutional w-full h-full flex items-center justify-center bg-gray-100';
        placeholder.innerHTML = `
            <svg class="w-12 h-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1"
                    d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
        `;
        parent.appendChild(placeholder);
    }
};
</script>

<template>
    <Head title="Inicio - Institucional" />

    <AppLayout>
        
        <div class="relative h-[85vh] min-h-[600px] overflow-hidden bg-slate-900">
            
            <video autoplay muted playsinline class="absolute inset-0 w-full h-full object-cover opacity-60" ref="promoVideo">
                <source :src="currentVideo" type="video/mp4">
            </video>

            <div class="absolute inset-0 bg-gradient-to-t from-slate-900 via-slate-900/40 to-transparent"></div>

            <div class="relative h-full flex flex-col items-center pt-32 text-center px-4">
                <span class="text-xs md:text-sm font-bold tracking-[0.3em] text-white/80 uppercase mb-4 animate-fade-in">
                    Fotografía Profesional
                </span>
                <h1 class="text-4xl md:text-6xl lg:text-7xl font-serif font-medium text-white mb-6 drop-shadow-lg max-w-4xl leading-tight animate-fade-in">
                    Preservando momentos <br/> <span class="italic text-gray-300">irrepetibles.</span>
                </h1>
                <p class="text-lg text-gray-300 mb-10 max-w-2xl font-light leading-relaxed animate-fade-in-delayed">
                    Acceda a su galería privada o explore nuestra cobertura de eventos recientes con la calidad que nos distingue.
                </p>
                
                <Link :href="route('gallery.index')" 
                    class="group relative px-8 py-3 overflow-hidden bg-white text-slate-900 text-sm font-bold uppercase tracking-widest hover:bg-gray-100 transition duration-300">
                    <span class="relative z-10">Buscar mis Fotos</span>
                </Link>
            </div>
        </div>

        <div class="relative z-20 -mt-64 pb-20 px-4 sm:px-6 lg:px-8">
            <div class="max-w-7xl mx-auto">
                
                <div class="bg-white shadow-2xl rounded-sm border-t-4 border-slate-900">
                    
                    <div class="p-8 md:p-10 border-b border-gray-100 flex flex-col md:flex-row justify-between items-end gap-4">
                        <div>
                            <h2 class="text-3xl font-serif font-bold text-slate-900">Eventos Recientes</h2>
                            <p class="text-slate-500 mt-2 font-light">Cobertura fotográfica actualizada en tiempo real.</p>
                        </div>
                        <Link :href="route('events.index')" class="group flex items-center text-sm font-bold uppercase tracking-widest text-slate-900 hover:text-slate-600 transition">
                            Ver todo el archivo
                            <svg class="ml-2 w-4 h-4 transform group-hover:translate-x-1 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" /></svg>
                        </Link>
                    </div>

                    <div class="p-8 md:p-10 bg-gray-50/50">
                        <div v-if="recentEvents && recentEvents.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                            
                            <Link v-for="event in recentEvents.slice(0, 3)" :key="event.id" :href="route('events.show', event.slug)"
                                class="group bg-white rounded-sm shadow-sm hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1 border border-gray-100">
                                
                                <div class="relative h-56 overflow-hidden bg-gray-200">
                                    <img v-if="event.cover_image_url" :src="event.cover_image_url" :alt="event.name"
                                        class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105 filter saturate-[0.85] group-hover:saturate-100"
                                        @error="handleImageError" />
                                    
                                    <div v-else class="w-full h-full flex items-center justify-center bg-gray-100">
                                        <svg class="w-10 h-10 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                    </div>

                                    <div class="absolute top-0 right-0 bg-white/95 backdrop-blur px-4 py-2 shadow-sm border-b border-l border-gray-100">
                                        <div class="text-xs font-bold uppercase tracking-wider text-slate-900">
                                            {{ formatDate(event.event_date) }}
                                        </div>
                                    </div>
                                </div>

                                <div class="p-6">
                                    <h3 class="text-xl font-serif font-bold text-slate-900 mb-2 line-clamp-1 group-hover:text-blue-900 transition-colors">
                                        {{ event.name }}
                                    </h3>
                                    
                                    <div class="flex flex-col space-y-2 mb-6">
                                        <div v-if="event.location" class="flex items-center text-sm text-slate-500">
                                            <svg class="w-4 h-4 mr-2 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                                            {{ event.location }}
                                        </div>
                                        <div class="flex items-center text-sm text-slate-500">
                                            <svg class="w-4 h-4 mr-2 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                            {{ event.photos_count || 0 }} fotografías
                                        </div>
                                    </div>

                                    <div class="w-full text-center py-3 border border-slate-900 text-slate-900 text-xs font-bold uppercase tracking-widest hover:bg-slate-900 hover:text-white transition-colors duration-300">
                                        Ver Galería
                                    </div>
                                </div>
                            </Link>

                        </div>

                        <div v-else class="text-center py-20 bg-white border border-dashed border-gray-300 rounded-sm">
                            <p class="text-slate-400 font-serif text-lg">No hay eventos recientes disponibles.</p>
                        </div>
                    </div>

                    <div class="bg-slate-50 border-t border-gray-200 px-8 py-10">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 text-center divide-y md:divide-y-0 md:divide-x divide-gray-200">
                            <div class="pt-4 md:pt-0">
                                <span class="block text-4xl font-serif font-bold text-slate-900">{{ Math.floor(animatedEvents) }}</span>
                                <span class="text-xs font-bold uppercase tracking-widest text-slate-500 mt-2 block">Eventos Cubiertos</span>
                            </div>
                            <div class="pt-4 md:pt-0">
                                <span class="block text-4xl font-serif font-bold text-slate-900">{{ Math.floor(animatedPhotos) }}</span>
                                <span class="text-xs font-bold uppercase tracking-widest text-slate-500 mt-2 block">Fotografías</span>
                            </div>
                            <div class="pt-4 md:pt-0">
                                <span class="block text-4xl font-serif font-bold text-slate-900">{{ Math.floor(animatedPhotographers) }}</span>
                                <span class="text-xs font-bold uppercase tracking-widest text-slate-500 mt-2 block">Profesionales</span>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <div class="py-16">
            <RecentPhotosSection 
                :photos="recentPhotos" 
                title="Últimas Incorporaciones" 
                subtitle="Explora las imágenes más recientes añadidas a nuestro archivo." 
            />
        </div>

    </AppLayout>
</template>

<style scoped>
@keyframes fade-in {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}

.animate-fade-in {
    animation: fade-in 1s ease-out forwards;
}

.animate-fade-in-delayed {
    opacity: 0;
    animation: fade-in 1s ease-out 0.3s forwards;
}

/* Tipografías adicionales si no cargan desde Tailwind config */
.font-serif {
    font-family: ui-serif, Georgia, Cambria, "Times New Roman", Times, serif;
}
</style>