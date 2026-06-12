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
        placeholder.className = 'placeholder-institutional w-full h-full flex items-center justify-center bg-zinc-950 text-zinc-800 border-b border-zinc-800';
        placeholder.innerHTML = `<svg class="w-12 h-12 opacity-20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>`;
        parent.appendChild(placeholder);
    }
};
</script>

<template>
    <Head title="Biblioteca Digital" />

    <AppLayout>
        <div class="min-h-screen bg-[#050505] text-white selection:bg-[#E30613] selection:text-black">
            
            <div class="border-b border-zinc-800 bg-black">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
                    <div class="flex flex-col md:flex-row md:items-start justify-between gap-8">
                        <div>
                            <span class="inline-block bg-[#E30613] text-black font-mono text-[10px] uppercase font-bold tracking-widest px-3 py-1 mb-4 border border-[#E30613]">
                                >_ BIBLIOTECA_DIGITAL
                            </span>
                            <h1 class="text-6xl md:text-8xl font-flux text-white leading-none uppercase tracking-tighter mb-4">
                                Mis Compras
                            </h1>
                            
                        </div>

                        <div class="flex gap-4 self-start">
                            <div class="bg-[#09090b] border border-zinc-800 p-4 md:p-6 text-center min-w-[120px] md:min-w-[140px] shadow-[4px_4px_0px_0px_rgba(227,6,19,1)] hover:-translate-y-1 transition-transform">
                                <div class="text-4xl md:text-6xl font-flux text-white leading-none mb-1">{{ totalPhotos }}</div>
                                <div class="font-mono text-[10px] font-bold uppercase tracking-widest text-zinc-500">Activos</div>
                            </div>
                            <div class="bg-[#09090b] border border-zinc-800 p-4 md:p-6 text-center min-w-[120px] md:min-w-[140px] shadow-[4px_4px_0px_0px_rgba(227,6,19,1)] hover:-translate-y-1 transition-transform">
                                <div class="text-4xl md:text-6xl font-flux text-[#E30613] leading-none mb-1">${{ totalSpent }}</div>
                                <div class="font-mono text-[10px] font-bold uppercase tracking-widest text-zinc-500">Inversión</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">

                <div v-if="purchases.length === 0" class="text-center py-24 border border-zinc-800 border-dashed bg-[#09090b]">
                    <div class="inline-flex items-center justify-center w-24 h-24 bg-black rounded-none border border-zinc-800 shadow-[6px_6px_0px_0px_rgba(227,6,19,1)] mb-8">
                        <ShoppingBagIcon class="w-10 h-10 text-zinc-600" />
                    </div>
                    <h2 class="text-4xl font-flux uppercase text-white mb-2 tracking-widest">
                         // 0 COMPRAS
                    </h2>
                    <p class="font-mono text-xs text-zinc-500 mb-10">
                        No existen transacciones.
                    </p>
                    <Link :href="route('gallery.index')"
                        class="inline-flex items-center gap-2 bg-[#E30613] hover:bg-white text-black font-mono text-[10px] font-bold uppercase tracking-widest px-8 py-4 border border-[#E30613] hover:border-white shadow-[6px_6px_0px_0px_rgba(255,255,255,0.1)] hover:shadow-none hover:translate-x-1 hover:translate-y-1 transition-all">
                        <PhotoIcon class="w-4 h-4" /> Iniciar exploración
                    </Link>
                </div>

                <div v-else class="space-y-12">
                    <div v-for="purchase in purchases" :key="purchase.id"
                        class="bg-[#09090b] border border-zinc-800 shadow-[4px_4px_0px_0px_rgba(255,255,255,0.05)] rounded-none overflow-hidden group hover:border-[#E30613] transition-colors duration-300">
                        
                        <div class="bg-black text-white px-6 py-4 border-b border-zinc-800">
                            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                                <div>
                                    <div class="flex flex-wrap items-center gap-3 mb-2">
                                        <h3 class="text-3xl font-flux tracking-widest text-white uppercase">
                                            ORDEN_ID: {{ purchase.id }}
                                        </h3>
                                        <span class="px-2 py-0.5 border border-[#E30613] text-[#E30613] font-mono text-[9px] font-bold uppercase tracking-widest">
                                            COMPLETADA_OK
                                        </span>
                                    </div>
                                    <div class="flex items-center gap-4 font-mono text-[10px] uppercase font-bold tracking-widest text-zinc-500">
                                        <span class="flex items-center gap-1">
                                            <CalendarIcon class="w-3 h-3 text-[#E30613]" /> {{ purchase.created_at }}
                                        </span>
                                        <span class="flex items-center gap-1">
                                            <PhotoIcon class="w-3 h-3 text-[#E30613]" /> {{ purchase.item_count }} ACTIVOS
                                        </span>
                                    </div>
                                </div>

                                <div class="flex items-center gap-6 sm:border-l sm:border-zinc-800 sm:pl-6">
                                    <div class="text-right">
                                        <div class="font-mono text-[9px] text-zinc-500 uppercase tracking-widest mb-1">Total Abonado</div>
                                        <div class="text-4xl font-flux text-white leading-none">
                                            ${{ formatPrice(purchase.total_amount) }}
                                        </div>
                                    </div>
                                    
                                    
                                </div>
                            </div>
                        </div>

                        <div class="p-6 bg-[#09090b]">
                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                                
                                <div v-for="item in purchase.items" :key="item.id"
                                    class="bg-black border border-zinc-800 rounded-none overflow-hidden hover:-translate-y-1 hover:border-[#E30613] hover:shadow-[4px_4px_0px_0px_rgba(227,6,19,1)] transition-all group/item flex flex-col">
                                    
                                    <div class="relative aspect-[4/3] bg-zinc-950 border-b border-zinc-800 overflow-hidden flex-shrink-0">
                                        <img v-if="item.photo.thumbnail_url"
                                            :src="item.photo.thumbnail_url" 
                                            :alt="item.photo.title"
                                            class="w-full h-full object-cover transition-transform duration-500 group-hover/item:scale-110 filter grayscale opacity-60 group-hover/item:grayscale-0 group-hover/item:opacity-100"
                                            @error="handleImageError"
                                        />

                                        <div class="absolute inset-0 bg-[#E30613]/0 group-hover/item:bg-[#E30613]/20 transition-colors duration-300 flex items-center justify-center pointer-events-none mix-blend-multiply"></div>
                                        
                                        <a :href="route('purchases.download', [purchase.id, item.photo_id])"
                                            class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 opacity-0 group-hover/item:opacity-100 transform scale-50 group-hover/item:scale-100 transition-all duration-300 bg-[#E30613] text-black p-4 border border-black rounded-none hover:bg-white hover:border-white">
                                            <ArrowDownTrayIcon class="w-6 h-6" />
                                        </a>

                                        <div v-if="item.download_count > 0" 
                                            class="absolute top-2 right-2 bg-black/80 backdrop-blur-sm text-zinc-300 border border-zinc-700 px-2 py-1 font-mono text-[9px] font-bold uppercase tracking-widest">
                                            <ArrowDownTrayIcon class="w-2.5 h-2.5 inline mr-1 text-[#E30613]" />
                                            {{ item.download_count }} DL
                                        </div>
                                    </div>

                                    <div class="p-4 flex flex-col flex-grow justify-between bg-[#09090b]">
                                        <div class="mb-4">
                                            <h4 class="font-mono text-xs font-bold text-white uppercase tracking-wide line-clamp-1 border-b border-zinc-800 pb-2 mb-3 group-hover/item:text-[#E30613] transition-colors">
                                                {{ item.photo.title || `ASSET_${item.photo.unique_id}` }}
                                            </h4>
                                            
                                            <div class="flex flex-col gap-1.5 font-mono text-[9px] font-bold uppercase tracking-widest text-zinc-500">
                                                <span v-if="item.photo.event">
                                                    > EVT: <span class="text-zinc-400">{{ item.photo.event.name }}</span>
                                                </span>
                                                <span>
                                                    > RES: <span class="text-zinc-400">{{ item.photo.width }}x{{ item.photo.height }}</span>
                                                </span>
                                            </div>
                                        </div>

                                        <a :href="route('purchases.download', [purchase.id, item.photo_id])"
                                            class="w-full inline-flex items-center justify-center gap-2 bg-transparent hover:bg-white text-zinc-300 hover:text-black font-mono text-[10px] font-bold uppercase tracking-widest px-4 py-3 border border-zinc-700 hover:border-white transition-colors mt-auto">
                                            <ArrowDownTrayIcon class="w-3 h-3" /> Descargar
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