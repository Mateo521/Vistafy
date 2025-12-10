<script setup>
import { onMounted, ref, watch, nextTick } from 'vue';
import 'leaflet/dist/leaflet.css';
import L from 'leaflet';

const props = defineProps({
    events: {
        type: Array,
        default: () => []
    }
});

const mapContainer = ref(null);
const isMapReady = ref(false);
let map = null;
let markers = [];

// 🔍 DEBUG 1: Ver qué eventos llegan
watch(() => props.events, (newEvents) => {


    newEvents.forEach((event, index) => {
        //
    });
}, { immediate: true, deep: true });

const initMap = () => {


    if (!mapContainer.value) {

        return;
    }

    if (map) {

        map.remove();
    }

    // Inicializar mapa centrado en Argentina
    map = L.map(mapContainer.value, {
        scrollWheelZoom: true,
        zoomControl: false,
        attributionControl: false
    }).setView([-38.4161, -63.6167], 4);


    // Control de Zoom (bottom right)
    L.control.zoom({ position: 'bottomright' }).addTo(map);

    // Capa base
    L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png', {
        maxZoom: 19
    }).addTo(map);



    addMarkers();

    setTimeout(() => {
        isMapReady.value = true;
        map.invalidateSize();

    }, 200);
};

const addMarkers = () => {


    markers.forEach(marker => marker.remove());
    markers = [];

    let validEvents = 0;
    let invalidEvents = 0;

    props.events.forEach((event, index) => {

        if (event.latitude && event.longitude) {
            validEvents++;

            const lat = parseFloat(event.latitude);
            const lng = parseFloat(event.longitude);

            if (isNaN(lat) || isNaN(lng)) {
                console.error(` Coordenadas inválidas para "${event.title}":`, { lat, lng });
                invalidEvents++;
                return;
            }


            const customIcon = L.divIcon({
                className: 'custom-event-marker',
                html: `<div style="
                    width: 16px; 
                    height: 16px; 
                    background-color: #141414; 
                    border-radius: 50%; 
                    border: 3px solid white; 
                    box-shadow: 0 4px 8px rgba(0,0,0,0.3);
                    transition: all 0.2s ease;
                "></div>`,
                iconSize: [16, 16],
                iconAnchor: [8, 8]
            });

            try {
                const marker = L.marker([lat, lng], { icon: customIcon })
                    .addTo(map)
                    .bindPopup(`
        <div style="text-align: center; font-family: ui-serif, Georgia, serif; min-width: 220px; padding-top:3px; max-width:260px;">
            <div style="margin-bottom: 8px; margin-top:5px; border-radius: 4px; overflow: hidden;">
                <img 
                    src="${event.cover_image || 'https://via.placeholder.com/400x250?text=Sin+Imagen'}"
                    alt="${event.title}"
                    style="width: 100%; height: 140px; object-fit: cover;"
                    onerror="this.src='https://via.placeholder.com/400x250?text=Sin+Imagen'"
                />
            </div>
            <strong style="display:block; font-size: 14px; color: #0f172a; margin-bottom: 4px; line-height: 1.3;">
                ${event.title}
            </strong>
            <div style="font-size: 11px; color: #64748b; margin-bottom: 4px;">
                ${event.location}
            </div>
            <div style="font-size: 10px; color: #64748b; margin-bottom: 6px;">
                ${event.formatted_date ?? ''}
            </div>
            <div style="font-size: 9px; color: #94a3b8; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 8px;">
                por ${event.photographer?.business_name ?? 'Fotógrafo'}
            </div>
            <a href="/eventos-futuros/${event.id}" 
               style="display:block; font-size:10px; font-weight:bold; color:white; background: #141414; padding: 6px 12px; text-decoration:none; border-radius: 2px; transition: background 0.2s;">
               VER DETALLES
            </a>
        </div>
    `);

                markers.push(marker);
            } catch (error) {
            }
        } else {
            invalidEvents++;
        }
    });


    // Ajustar zoom para mostrar todos los marcadores
    if (markers.length > 0) {
        const group = new L.featureGroup(markers);
        const bounds = group.getBounds();

        map.fitBounds(bounds.pad(0.1), {
            maxZoom: 10,
            padding: [50, 50]
        });

    } else {
    }
};

onMounted(async () => {
    await nextTick();
    setTimeout(initMap, 100);
});

watch(() => props.events, () => {
    if (map) addMarkers();
}, { deep: true });
</script>

<template>
    <div class="relative w-full h-full bg-slate-100">
        <div ref="mapContainer" class="w-full h-full z-0 outline-none map-future-events"></div>

        <transition enter-active-class="transition-opacity duration-300"
            leave-active-class="transition-opacity duration-500" enter-from-class="opacity-0"
            leave-to-class="opacity-0">
            <div v-if="!isMapReady" class="absolute inset-0 flex items-center justify-center bg-gray-50 z-20">
                <div class="flex flex-col items-center">
                    <svg class="animate-spin h-5 w-5 text-slate-400 mb-2" xmlns="http://www.w3.org/2000/svg" fill="none"
                        viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                        </circle>
                        <path class="opacity-75" fill="currentColor"
                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                        </path>
                    </svg>
                    <span class="text-slate-400 text-xs font-bold uppercase tracking-widest">Cargando Eventos...</span>
                </div>
            </div>
        </transition>
    </div>
</template>

<style>
/* Controles de zoom */
.map-future-events .leaflet-control-zoom a {
    background-color: #ffffff !important;
    color: #0f172a !important;
    border: 1px solid #e2e8f0 !important;
    border-radius: 0 !important;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1) !important;
    transition: all 0.2s;
}

.map-future-events .leaflet-control-zoom a:hover {
    background-color: #0f172a !important;
    color: #ffffff !important;
    border-color: #0f172a !important;
}

/* Popups */
.leaflet-popup-content-wrapper {
    border-radius: 2px !important;
    padding: 0 !important;
    box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1) !important;
    border: 1px solid #e2e8f0;
}

.leaflet-popup-content {
    margin: 16px 20px !important;
}

.leaflet-popup-tip {
    background: white !important;
    border: 1px solid #e2e8f0;
    border-top: none;
    border-left: none;
}

/* Marcadores de eventos */
.custom-event-marker:hover div {
    transform: scale(1.5);
    background-color: #dc2626 !important;
    border-color: #fef2f2 !important;
}
</style>
