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

watch(() => props.events, (newEvents) => {
    newEvents.forEach((event, index) => {

    });
}, { immediate: true, deep: true });

const initMap = () => {
    if (!mapContainer.value) return;

    if (map) {
        map.remove();
    }


    map = L.map(mapContainer.value, {
        scrollWheelZoom: true,
        zoomControl: false,
        attributionControl: false
    }).setView([-38.4161, -63.6167], 4);


    L.control.zoom({ position: 'bottomright' }).addTo(map);


    L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', {
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
                    background-color: #dc2626; 
                    border-radius: 50%; 
                    border: 2px solid #fff; 
                    box-shadow: 0 0 10px rgba(220, 38, 38, 0.8);
                    transition: all 0.3s cubic-bezier(0.25, 0.46, 0.45, 0.94);
                "></div>`,
                iconSize: [16, 16],
                iconAnchor: [8, 8]
            });

            try {

                const marker = L.marker([lat, lng], { icon: customIcon })
                    .addTo(map)
                    .bindPopup(`
        <div style="font-family: 'Space Mono', monospace; min-width: 200px; max-width: 260px; padding: 0;">
            <div style="margin-bottom: 12px; border: 1px solid rgba(255,255,255,0.2); overflow: hidden;">
                <img 
                    src="${event.cover_image || event.cover_image_url || 'https://images.unsplash.com/photo-1511795409834-ef04bbd61622?auto=format&fit=crop&w=400&q=80'}"
                    alt="${event.title || event.name}"
                    style="width: 100%; height: 120px; object-fit: cover; filter: grayscale(100%) contrast(120%);"
                    onerror="this.src='https://images.unsplash.com/photo-1511795409834-ef04bbd61622?auto=format&fit=crop&w=400&q=80'"
                />
            </div>
            <strong style="display:block; font-family: 'Inter', sans-serif; font-size: 18px; font-weight: 900; color: #ffffff; text-transform: uppercase; margin-bottom: 4px; line-height: 1.1; letter-spacing: -0.05em;">
                ${event.title || event.name}
            </strong>
            <div style="font-size: 10px; font-weight: 700; color: #dc2626; text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 8px;">
                ${event.location || 'Ubicación a confirmar'}
            </div>
            <div style="font-size: 10px; color: #9ca3af; margin-bottom: 16px; opacity: 0.8; text-transform: uppercase; letter-spacing: 0.1em;">
                ${event.formatted_date || (event.event_date ? new Date(event.event_date).toLocaleDateString('es-ES') : '')}
            </div>
            <a href="/eventos-futuros/${event.id || event.slug}" 
               style="display:block; font-size:10px; font-weight:700; color:#000000; background: #ffffff; padding: 10px 12px; text-decoration:none; text-align: center; text-transform: uppercase; letter-spacing: 0.2em; transition: all 0.3s; border: 1px solid #ffffff;">
               [ RASTREAR OBJETIVO ]
            </a>
        </div>
    `);

                markers.push(marker);
            } catch (error) {
                console.error("Error al crear marcador", error);
            }
        } else {
            invalidEvents++;
        }
    });

    if (markers.length > 0) {
        const group = new L.featureGroup(markers);
        const bounds = group.getBounds();

        map.fitBounds(bounds.pad(0.1), {
            maxZoom: 10,
            padding: [50, 50]
        });
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
    <div class="relative w-full h-[100vh] min-h-[400px] bg-black">
        <div ref="mapContainer" class="w-full h-full z-0 outline-none map-future-events"></div>

        <transition enter-active-class="transition-opacity duration-300"
            leave-active-class="transition-opacity duration-500" enter-from-class="opacity-0"
            leave-to-class="opacity-0">
            <div v-if="!isMapReady" class="absolute inset-0 flex items-center justify-center bg-black z-20">
                <div class="flex flex-col items-center">
                    <div class="h-10 w-10 border-2 border-white/20 border-t-red-600 rounded-full animate-spin mb-4"></div>
                    <span class="text-white font-mono text-[10px] font-bold uppercase tracking-widest">Estableciendo enlace satelital...</span>
                </div>
            </div>
        </transition>
        
        <div class="absolute left-[4vw] top-[8vh] z-10 h-16 w-16  border-t-2 border-white pointer-events-none opacity-50"></div>
        <div class="absolute right-[4vw] top-[8vh] z-10 h-16 w-16 border-r-2 border-t-2 border-red-600 pointer-events-none opacity-50"></div>
        <div class="absolute bottom-[8vh] left-[4vw] z-10 h-16 w-16 border-b-2  border-red-600 pointer-events-none opacity-50"></div>
        <div class="absolute bottom-[8vh] right-[4vw] z-10 h-16 w-16 border-b-2 border-r-2 border-white pointer-events-none opacity-50"></div>
    </div>
</template>

<style>

.map-future-events .leaflet-tile-pane {
    filter: brightness(1.3) contrast(1.1) grayscale(0.5);
}

.map-future-events {
    font-family: 'Space Mono', monospace;
    background: #000000;
}


.map-future-events .leaflet-control-zoom a {
    background-color: #000000 !important;
    color: #ffffff !important;
    border: 1px solid rgba(255, 255, 255, 0.2) !important;
    border-radius: 0 !important;
    box-shadow: none !important;
    transition: all 0.3s;
}

.map-future-events .leaflet-control-zoom a:hover {
    background-color: #dc2626 !important;
    color: #ffffff !important;
    border-color: #dc2626 !important;
}


.leaflet-popup-content-wrapper {
    background-color: #000000 !important;
    border: 1px solid #dc2626 !important;
    border-radius: 0 !important;
    padding: 0 !important;
    color: #ffffff !important;
    box-shadow: 0 0 20px rgba(220, 38, 38, 0.2) !important;
}

.leaflet-popup-content {
    margin: 16px !important;
}


.leaflet-popup-tip {
    background-color: #000000 !important;
    border: 1px solid #dc2626 !important;
    border-top: none !important;
    border-left: none !important;
}


.leaflet-popup-close-button {
    color: #dc2626 !important;
    font-family: 'Space Mono', monospace;
    font-size: 16px !important;
    padding: 4px !important;
}

.leaflet-popup-close-button:hover {
    color: #ffffff !important;
    background-color: transparent !important;
}


.custom-event-marker:hover div {
    transform: scale(1.5);
    background-color: #ffffff !important;
    border-color: #dc2626 !important;
    box-shadow: 0 0 15px rgba(255, 255, 255, 0.8) !important;
}


.leaflet-popup-content a:hover {
    background-color: #dc2626 !important;
    color: #ffffff !important;
    border-color: #dc2626 !important;
}


.leaflet-control-attribution {
    display: none !important;
}
</style>