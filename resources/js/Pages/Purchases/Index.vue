<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { computed } from 'vue';
import { 
    ShoppingBagIcon, 
    ArrowDownTrayIcon,
    CalendarIcon,
    PhotoIcon,
    ArchiveBoxIcon
} from '@heroicons/vue/24/outline';

const props = defineProps({
    purchases: Array,
});

const totalSpent = computed(() => {
    return props.purchases.reduce((sum, purchase) => sum + parseFloat(purchase.total_amount), 0).toFixed(2);
});

const totalPhotos = computed(() => {
    return props.purchases.reduce((sum, purchase) => sum + purchase.item_count, 0);
});

const formatPrice = (amount) => {
    return parseFloat(amount || 0).toFixed(2);
};

const handleImageError = (e) => {
    e.target.style.display = 'none';
    const parent = e.target.parentElement;
    if (!parent.querySelector('.placeholder-institutional')) {
        const placeholder = document.createElement('div');
        placeholder.className = 'placeholder-institutional w-full h-full flex items-center justify-center bg-gray-100 text-slate-300';
        placeholder.innerHTML = `<svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>`;
        parent.appendChild(placeholder);
    }
};
</script>

<template>
    <Head title="Mis Compras" />

    <AppLayout>
        <div class="min-h-screen bg-white">
            
            <!-- Header -->
            <div class="border-b border-gray-100 bg-white">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
                    <div class="flex items-start justify-between">
                        <div>
                            <span class="text-[10px] uppercase tracking-widest text-slate-400 block mb-2">
                                Mi Biblioteca Digital
                            </span>
                            <h1 class="text-4xl font-serif font-bold text-slate-900 mb-4">Mis Compras</h1>
                            <p class="text-slate-600 max-w-2xl">
                                Todas tus fotografías adquiridas están disponibles para descarga en cualquier momento.
                            </p>
                        </div>

                        <!-- Stats -->
                        <div class="hidden md:flex gap-8 text-right">
                            <div>
                                <div class="text-3xl font-serif font-bold text-slate-900">{{ totalPhotos }}</div>
                                <div class="text-[10px] uppercase tracking-widest text-slate-400">Fotografías</div>
                            </div>
                            <div>
                                <div class="text-3xl font-serif font-bold text-slate-900">${{ totalSpent }}</div>
                                <div class="text-[10px] uppercase tracking-widest text-slate-400">Invertido</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Content -->
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">

                <!-- Empty State -->
                <div v-if="purchases.length === 0" class="text-center py-20">
                    <div class="inline-flex items-center justify-center w-20 h-20 bg-gray-100 rounded-full mb-6">
                        <ShoppingBagIcon class="w-10 h-10 text-slate-300" />
                    </div>
                    <h2 class="text-2xl font-serif font-bold text-slate-900 mb-3">
                        Aún no has realizado compras
                    </h2>
                    <p class="text-slate-500 mb-8 max-w-md mx-auto">
                        Explora nuestra galería y adquiere las fotografías que más te gusten
                    </p>
                    <Link :href="route('gallery.index')"
                        class="inline-flex items-center gap-2 bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold uppercase tracking-widest px-8 py-4 rounded-sm transition-all shadow-md hover:shadow-lg">
                        <PhotoIcon class="w-4 h-4" /> Explorar Galería
                    </Link>
                </div>

                <!-- Purchases List -->
                <div v-else class="space-y-8">
                    <div v-for="purchase in purchases" :key="purchase.id"
                        class="bg-white border border-gray-200 rounded-sm overflow-hidden hover:border-gray-300 transition-colors">
                        
                        <!-- Purchase Header -->
                        <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
                            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                                <div>
                                    <div class="flex items-center gap-3 mb-2">
                                        <h3 class="text-sm font-bold text-slate-900">
                                            Orden #{{ purchase.id }}
                                        </h3>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-green-100 text-green-800">
                                            Completada
                                        </span>
                                    </div>
                                    <div class="flex items-center gap-4 text-[10px] uppercase tracking-widest text-slate-400">
                                        <span class="flex items-center gap-1">
                                            <CalendarIcon class="w-3 h-3" />
                                            {{ purchase.created_at }}
                                        </span>
                                        <span class="flex items-center gap-1">
                                            <PhotoIcon class="w-3 h-3" />
                                            {{ purchase.item_count }} {{ purchase.item_count === 1 ? 'Foto' : 'Fotos' }}
                                        </span>
                                    </div>
                                </div>

                                <div class="flex items-center gap-4">
                                    <div class="text-right">
                                        <div class="text-xs text-slate-500 mb-1">Total</div>
                                        <div class="text-2xl font-serif font-bold text-slate-900">
                                            ${{ formatPrice(purchase.total_amount) }}
                                        </div>
                                    </div>
                                    
                                    <!-- Download All Button -->
                                    <a :href="route('purchases.download.all', purchase.id)"
                                        class="inline-flex items-center gap-2 bg-slate-900 hover:bg-slate-800 text-white text-[10px] font-bold uppercase tracking-widest px-4 py-3 rounded-sm transition-all shadow-sm hover:shadow-md">
                                        <ArchiveBoxIcon class="w-4 h-4" />
                                        Descargar Todo
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- Photos Grid -->
                        <div class="p-6">
                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                                <div v-for="item in purchase.items" :key="item.id"
                                    class="bg-white border border-gray-100 rounded-sm overflow-hidden hover:border-gray-200 transition-colors group">
                                    
                                    <!-- Thumbnail -->
                                    <div class="relative aspect-[4/3] bg-gray-100 overflow-hidden">
                                        <img v-if="item.photo.thumbnail_url"
                                            :src="item.photo.thumbnail_url" 
                                            :alt="item.photo.title"
                                            class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105 filter grayscale-[0.3] group-hover:grayscale-0"
                                            @error="handleImageError"
                                        />

                                        <!-- Download Overlay -->
                                        <div class="absolute inset-0 bg-black/0 group-hover:bg-black/40 transition-all duration-300 flex items-center justify-center">
                                            <a :href="route('purchases.download', [purchase.id, item.photo_id])"
                                                class="opacity-0 group-hover:opacity-100 transform scale-90 group-hover:scale-100 transition-all duration-300 bg-white text-slate-900 p-3 rounded-full shadow-lg hover:shadow-xl">
                                                <ArrowDownTrayIcon class="w-6 h-6" />
                                            </a>
                                        </div>

                                        <!-- Download Count Badge -->
                                        <div v-if="item.download_count > 0" 
                                            class="absolute top-3 right-3 bg-white/90 backdrop-blur-sm px-2 py-1 rounded-full text-[10px] font-bold text-slate-600">
                                            <ArrowDownTrayIcon class="w-3 h-3 inline mr-1" />
                                            {{ item.download_count }}
                                        </div>
                                    </div>

                                    <!-- Info -->
                                    <div class="p-4">
                                        <h4 class="text-sm font-bold text-slate-900 mb-2 line-clamp-1">
                                            {{ item.photo.title || `Foto #${item.photo.unique_id}` }}
                                        </h4>
                                        
                                        <div class="flex flex-wrap gap-2 mb-3">
                                            <span v-if="item.photo.event" 
                                                class="text-[10px] font-bold uppercase tracking-wider bg-slate-100 text-slate-600 px-2 py-1 rounded-sm">
                                                {{ item.photo.event.name }}
                                            </span>
                                            <span class="text-[10px] font-bold uppercase tracking-wider bg-slate-100 text-slate-600 px-2 py-1 rounded-sm">
                                                {{ item.photo.width }} x {{ item.photo.height }}
                                            </span>
                                        </div>

                                        <a :href="route('purchases.download', [purchase.id, item.photo_id])"
                                            class="w-full inline-flex items-center justify-center gap-2 bg-slate-900 hover:bg-slate-800 text-white text-[10px] font-bold uppercase tracking-widest px-4 py-2.5 rounded-sm transition-all">
                                            <ArrowDownTrayIcon class="w-3.5 h-3.5" />
                                            Descargar Original
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </AppLayout>
</template>
