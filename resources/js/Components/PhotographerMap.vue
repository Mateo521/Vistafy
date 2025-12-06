<script setup>
import { onMounted, ref, watch, nextTick } from 'vue';
import 'leaflet/dist/leaflet.css';
import L from 'leaflet';

const props = defineProps({
    photographers: {
        type: Array,
        default: () => []
    }
});

const mapContainer = ref(null);
const isMapReady = ref(false); // <--- NUEVA VARIABLE REACTIVA
let map = null; // Mantenemos el mapa como variable normal (mejor rendimiento)
let markers = [];

const regionCoords = {
    'Buenos Aires': [-34.6037, -58.3816],
    'CABA': [-34.6037, -58.3816],
    'Córdoba': [-31.4201, -64.1888],
    'Santa Fe': [-31.6107, -60.6973],
    'Mendoza': [-32.8895, -68.8458],
    'Tucumán': [-26.8083, -65.2176],
    'Rosario': [-32.9442, -60.6505],
    'Salta': [-24.7821, -65.4232],
    'Neuquén': [-38.9516, -68.0591],
    'Entre Ríos': [-31.7413, -60.5115],
};

const jitter = (coord) => coord + (Math.random() - 0.5) * 0.1;

const initMap = () => {
    if (!mapContainer.value) return;

    // Evitar reinicializar si ya existe
    if (map) map.remove();

    // 1. Crear Mapa
    map = L.map(mapContainer.value, {
        scrollWheelZoom: false,
        zoomControl: false,
        attributionControl: false
    }).setView([-38.4161, -63.6167], 4);

    // 2. Cargar Tiles
    L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png', {
        maxZoom: 19
    }).addTo(map);

    // 3. Agregar pines
    addMarkers();

    // 4. AVISAR A VUE QUE EL MAPA ESTÁ LISTO
    // Usamos un pequeño timeout para asegurar que el render visual terminó
    setTimeout(() => {
        isMapReady.value = true; 
        // Forzar un recálculo de tamaño por si el contenedor cambió
        map.invalidateSize();
    }, 200);
};

const addMarkers = () => {
    markers.forEach(marker => marker.remove());
    markers = [];

    props.photographers.forEach(photographer => {
        const regionName = photographer.region ? photographer.region.trim() : null;
        const baseCoords = regionCoords[regionName];
        
        if (baseCoords) {
            const lat = jitter(baseCoords[0]);
            const lng = jitter(baseCoords[1]);

            const customIcon = L.divIcon({
                className: 'custom-map-marker',
                html: `<div style="width: 12px; height: 12px; background-color: #0f172a; border-radius: 50%; border: 2px solid white; box-shadow: 0 2px 4px rgba(0,0,0,0.3);"></div>`,
                iconSize: [12, 12],
                iconAnchor: [6, 6]
            });

            const marker = L.marker([lat, lng], { icon: customIcon })
                .addTo(map)
                .bindPopup(`
                    <div style="text-align: center; font-family: sans-serif;">
                        <strong style="display:block; font-size: 12px; color: #0f172a;">${photographer.business_name}</strong>
                        <span style="font-size: 10px; color: #64748b; text-transform: uppercase;">${photographer.region}</span>
                        <a href="/fotografos/${photographer.slug}" style="display:block; margin-top:4px; font-size:10px; font-weight:bold; color:#0f172a; text-decoration:none;">Ver Perfil</a>
                    </div>
                `);
            
            markers.push(marker);
        }
    });
};

onMounted(async () => {
    await nextTick();
    setTimeout(initMap, 100);
});

watch(() => props.photographers, () => {
    if (map) addMarkers();
}, { deep: true });
</script>

<template>
    <div class="relative w-full h-full bg-slate-100">
        <div ref="mapContainer" class="w-full h-full z-0"></div>
        
        <transition 
            enter-active-class="transition-opacity duration-300"
            leave-active-class="transition-opacity duration-500"
            enter-from-class="opacity-0"
            leave-to-class="opacity-0"
        >
            <div v-if="!isMapReady" class="absolute inset-0 flex items-center justify-center bg-gray-50 z-20">
                <div class="flex flex-col items-center">
                    <svg class="animate-spin h-5 w-5 text-slate-400 mb-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span class="text-slate-400 text-xs font-bold uppercase tracking-widest">Cargando Mapa...</span>
                </div>
            </div>
        </transition>
    </div>
</template>

<style>
.leaflet-pane { z-index: 10 !important; }
.leaflet-top, .leaflet-bottom { z-index: 20 !important; }
/* Arreglo visual para popups */
.leaflet-popup-content-wrapper { border-radius: 2px !important; padding: 0 !important; }
.leaflet-popup-content { margin: 12px 16px !important; }
</style>