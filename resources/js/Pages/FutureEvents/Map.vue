<script setup>
import { Head } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';
import MapLayout from '@/Layouts/MapLayout.vue';      // ✅ NUEVO LAYOUT
import FutureEventsMap from '@/Components/FutureEventsMap.vue';
import axios from 'axios';

const events = ref([]);
const loading = ref(true);
const error = ref(null);

const loadEvents = async () => {
    loading.value = true;
    error.value = null;
    try {
        const response = await axios.get('/eventos-futuros/api', {
            params: { mode: 'map' },
        });
        events.value = response.data.future_events || [];
        console.log(' Eventos para mapa:', events.value.length);
    } catch (e) {
        error.value = 'No se pudieron cargar los eventos.';
        console.error(e);
    } finally {
        loading.value = false;
    }
};

onMounted(loadEvents);
</script>

<template>
    <MapLayout>

        <Head title="Mapa de Eventos Futuros" />

        <!-- El mapa ocupa toda el área del layout -->
        <div class="w-full h-full">
            <div v-if="loading" class="w-full h-full flex items-center justify-center bg-slate-950">
                <div class="flex flex-col items-center gap-3 text-slate-300">
                    <div class="h-10 w-10 border-2 border-slate-700 border-t-blue-500 rounded-full animate-spin"></div>
                    <p class="text-xs uppercase tracking-[0.2em]">Cargando mapa...</p>
                </div>
            </div>

            <div v-else-if="error" class="w-full h-full flex items-center justify-center bg-slate-950">
                <p class="text-red-400 text-sm">{{ error }}</p>
            </div>

            <div v-else class="w-full h-full">
                <FutureEventsMap :events="events" />
            </div>
        </div>
    </MapLayout>
</template>
