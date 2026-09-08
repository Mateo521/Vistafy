<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { computed } from 'vue';
import {
    ShoppingBagIcon,
    ArrowDownTrayIcon,
    CalendarIcon,
    PhotoIcon,
    ArchiveBoxIcon,
    CheckBadgeIcon,
    ArrowRightIcon
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
    if (!parent.querySelector('.placeholder-sleek')) {
        const placeholder = document.createElement('div');
        placeholder.className = 'placeholder-sleek w-full h-full flex flex-col items-center justify-center bg-gray-50 text-gray-300 border-b border-gray-100';
        placeholder.innerHTML = `
            <svg class="w-10 h-10 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
            <span class="text-[10px] font-bold uppercase tracking-wider text-gray-400">Sin Imagen</span>
        `;
        parent.appendChild(placeholder);
    }
};
</script>

<template>

    <Head title="Mis compras" />

    <AppLayout>
        <div class="min-h-screen bg-[#F8F9FA] text-slate-800 font-sans antialiased py-12 pt-28">


            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mb-12">
                <div class="flex flex-col md:flex-row md:items-end justify-between gap-8 border-b border-gray-200 pb-8">


                    <div>
                        <div class="flex items-center gap-2 mb-3">
                            <span class="w-8 h-8 bg-red-50 rounded-full flex items-center justify-center shadow-sm">
                                <ArchiveBoxIcon class="w-4 h-4 text-[#E30613]" />
                            </span>
                            <span class="text-xs font-bold uppercase tracking-wider text-gray-500">Biblioteca</span>
                        </div>
                        <h1 class="text-5xl md:text-7xl font-flux text-black leading-none tracking-wide">
                            Mis <span class="text-[#E30613]">Compras</span>
                        </h1>
                        <p class="text-sm font-medium text-gray-500 mt-4 max-w-md">
                            Accedé a todos los activos digitales que compraste, listos para descargar en alta
                            resolución.
                        </p>
                    </div>


                    <div class="flex gap-4 self-start md:self-end">
                        <div
                            class="bg-white rounded-3xl p-6 border border-gray-100 shadow-sm min-w-[130px] md:min-w-[160px]">
                            <div
                                class="text-[10px] font-bold uppercase tracking-widest text-gray-400 mb-1 flex items-center gap-1.5">
                                <PhotoIcon class="w-4 h-4" /> Activos
                            </div>
                            <div class="text-4xl md:text-5xl font-flux text-black">{{ totalPhotos }}</div>
                        </div>

                        <div
                            class="bg-white rounded-3xl p-6 border border-gray-100 shadow-sm min-w-[130px] md:min-w-[160px]">
                            <div
                                class="text-[10px] font-bold uppercase tracking-widest text-gray-400 mb-1 flex items-center gap-1.5">
                                <ShoppingBagIcon class="w-4 h-4" /> Inversión
                            </div>
                            <div class="text-4xl md:text-5xl font-flux text-[#E30613]">${{ totalSpent }}</div>
                        </div>
                    </div>
                </div>
            </div>


            <div class="max-w-[1400px] mx-auto px-4 sm:px-6 lg:px-8 pb-16">


                <div v-if="purchases.length === 0"
                    class="text-center py-24 bg-white rounded-3xl shadow-sm border border-gray-100 flex flex-col items-center">
                    <div
                        class="w-24 h-24 bg-gray-50 rounded-full flex items-center justify-center mb-6 border border-gray-100 shadow-inner">
                        <ShoppingBagIcon class="w-10 h-10 text-gray-300" />
                    </div>
                    <h2 class="text-3xl md:text-4xl font-flux text-black mb-3">
                        Sin compras hechas
                    </h2>
                    <p class="text-sm font-medium text-gray-500 mb-8 max-w-sm">
                        Aún no adquiriste ninguna foto. Explorá nuestros eventos y encontrá tus mejores momentos.
                    </p>
                    <Link :href="route('gallery.index')"
                        class="inline-flex items-center gap-2 bg-black hover:bg-[#E30613] text-white font-bold text-xs uppercase tracking-wider px-8 py-4 rounded-full shadow-md hover:shadow-lg transition-all hover:-translate-y-1">
                        Explorar Galerías
                        <ArrowRightIcon class="w-4 h-4" />
                    </Link>
                </div>


                <div v-else class="space-y-10">
                    <div v-for="purchase in purchases" :key="purchase.id"
                        class="bg-white border border-gray-200 shadow-sm rounded-3xl overflow-hidden hover:shadow-md transition-shadow duration-300">


                        <div class="bg-gray-50/80 px-6 md:px-8 py-5 border-b border-gray-200">
                            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">

                                <div>
                                    <div class="flex flex-wrap items-center gap-3 mb-2">
                                        <h3 class="text-2xl font-flux text-black leading-none">
                                            Orden #{{ purchase.id }}
                                        </h3>
                                        <span
                                            class="inline-flex items-center gap-1 px-2.5 py-1 bg-green-50 text-green-700 border border-green-200 rounded-md text-[9px] font-bold uppercase tracking-widest shadow-sm">
                                            <CheckBadgeIcon class="w-3 h-3" /> Completada
                                        </span>
                                    </div>

                                    <div
                                        class="flex items-center gap-4 text-[10px] font-bold uppercase tracking-wider text-gray-500">
                                        <span class="flex items-center gap-1.5">
                                            <CalendarIcon class="w-3.5 h-3.5 text-[#E30613]" /> {{ purchase.created_at
                                            }}
                                        </span>
                                        <span class="flex items-center gap-1.5">
                                            <PhotoIcon class="w-3.5 h-3.5 text-[#E30613]" /> {{ purchase.item_count }}
                                            {{ purchase.item_count === 1 ? 'Fotografía' : 'Fotografías' }}
                                        </span>
                                    </div>
                                </div>

                                <div
                                    class="sm:text-right border-t sm:border-t-0 sm:border-l border-gray-200 pt-4 sm:pt-0 sm:pl-6">
                                    <div class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">
                                        Total Abonado</div>
                                    <div class="text-3xl font-flux text-black leading-none">
                                        ${{ formatPrice(purchase.total_amount) }}
                                    </div>
                                </div>

                            </div>
                        </div>


                        <div class="p-6 md:p-8 bg-white">
                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">

                                <div v-for="item in purchase.items" :key="item.id"
                                    class="bg-white border border-gray-100 rounded overflow-hidden shadow-sm hover:shadow-lg hover:border-gray-300 transition-all duration-300 group flex flex-col">


                                    <div class="relative aspect-[4/3] bg-gray-50 overflow-hidden flex-shrink-0">
                                        <img v-if="item.photo.thumbnail_url" :src="item.photo.thumbnail_url"
                                            :alt="item.photo.title"
                                            class="w-full h-full object-cover transition-transform duration-700"
                                            @error="handleImageError" />

                                        <div
                                            class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-center justify-center">
                                            <a :href="route('purchases.download', [purchase.id, item.photo_id])"
                                                class="w-14 h-14 bg-white rounded-full flex items-center justify-center text-black hover:bg-[#E30613] hover:text-white shadow-xl transform scale-50 group-hover:scale-100 transition-all duration-300"
                                                title="Descargar Alta Resolución">
                                                <ArrowDownTrayIcon class="w-6 h-6" />
                                            </a>
                                        </div>

                                        <!-- Badge de Descargas -->
                                        <div v-if="item.download_count > 0"
                                            class="absolute top-3 right-3 bg-white/90 backdrop-blur-sm text-slate-700 border border-gray-100 px-2 py-1 rounded-md text-[9px] font-bold uppercase tracking-wider shadow-sm flex items-center gap-1">
                                            <ArrowDownTrayIcon class="w-3 h-3 text-[#E30613]" /> {{ item.download_count
                                            }} DL
                                        </div>
                                    </div>

                                    <!-- Detalles de la Foto -->
                                    <div class="p-5 flex flex-col flex-grow bg-white">
                                        <div class="mb-5">
                                            <h4 class="font-bold text-sm text-slate-800 line-clamp-1 mb-2">
                                                {{ item.photo.title || `REF-${item.photo.unique_id}` }}
                                            </h4>

                                            <div class="space-y-1.5">
                                                <div v-if="item.photo.event"
                                                    class="text-[10px] font-bold uppercase tracking-wider text-gray-500 flex gap-1">
                                                    <span class="text-gray-400">Evt:</span> <span class="truncate">{{
                                                        item.photo.event.name }}</span>
                                                </div>
                                                <div
                                                    class="text-[10px] font-bold uppercase tracking-wider text-gray-500 flex gap-1">
                                                    <span class="text-gray-400">Res:</span> <span>{{ item.photo.width }}
                                                        x {{ item.photo.height }} px</span>
                                                </div>
                                            </div>
                                        </div>

                                        <a :href="route('purchases.download', [purchase.id, item.photo_id])"
                                            class="mt-auto w-full inline-flex items-center justify-center gap-2 bg-gray-50 hover:bg-black text-gray-600 hover:text-white text-xs font-bold uppercase tracking-wider py-3 rounded-full border border-gray-100 transition-colors shadow-sm">
                                            <ArrowDownTrayIcon class="w-4 h-4" /> Descargar
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