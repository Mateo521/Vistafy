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
        //
    });
}, { immediate: true, deep: true });

const initMap = () => {
    if (!mapContainer.value) return;

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

    // CAPA BASE OSCURA (Vital para el tema cinemático)
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

            // Marcador personalizado (Naranja F33)
            const customIcon = L.divIcon({
                className: 'custom-event-marker',
                html: `<div style="
                    width: 14px; 
                    height: 14px; 
                    background-color: #FFB162; 
                    border-radius: 50%; 
                    border: 2px solid #1B2632; 
                    box-shadow: 0 0 15px rgba(255,177,98,0.6);
                    transition: all 0.3s cubic-bezier(0.25, 0.46, 0.45, 0.94);
                "></div>`,
                iconSize: [14, 14],
                iconAnchor: [7, 7]
            });

            try {
                // Popup HTML estilizado
                const marker = L.marker([lat, lng], { icon: customIcon })
                    .addTo(map)
                    .bindPopup(`
        <div style="text-align: center; font-family: 'Syne', sans-serif; min-width: 220px; padding-top:3px; max-width:260px;">
            <div style="margin-bottom: 12px; margin-top:5px; border-radius: 2px; overflow: hidden; border: 1px solid rgba(201,193,177,0.1);">
                <img 
                    src="${event.cover_image || 'https://via.placeholder.com/400x250/1B2632/EEE9DF?text=Sin+Imagen'}"
                    alt="${event.title}"
                    style="width: 100%; height: 140px; object-fit: cover; filter: saturate(0.8);"
                    onerror="this.src='https://via.placeholder.com/400x250/1B2632/EEE9DF?text=Sin+Imagen'"
                />
            </div>
            <strong style="display:block; font-family: 'Cormorant Garamond', serif; font-size: 24px; font-weight: 300; color: #EEE9DF; margin-bottom: 6px; line-height: 1.1;">
                ${event.title}
            </strong>
            <div style="font-size: 10px; font-weight: 700; color: #FFB162; text-transform: uppercase; letter-spacing: 0.2em; margin-bottom: 4px;">
                ${event.location}
            </div>
            <div style="font-size: 11px; color: #C9C1B1; margin-bottom: 12px; opacity: 0.8;">
                ${event.formatted_date ?? ''}
            </div>
            <a href="/eventos-futuros/${event.id}" 
               style="display:block; font-size:10px; font-weight:bold; color:#1B2632; background: #FFB162; padding: 10px 12px; text-decoration:none; border-radius: 2px; text-transform: uppercase; letter-spacing: 0.2em; transition: background 0.3s;">
               Ver Detalles
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
    <div class="relative w-full h-full bg-[#111920]">
        <div ref="mapContainer" class="w-full h-full z-0 outline-none map-future-events"></div>

        <transition enter-active-class="transition-opacity duration-300"
            leave-active-class="transition-opacity duration-500" enter-from-class="opacity-0"
            leave-to-class="opacity-0">
            <div v-if="!isMapReady" class="absolute inset-0 flex items-center justify-center bg-[#1B2632] z-20">
                <div class="flex flex-col items-center">
                    <svg class="animate-spin h-6 w-6 text-[#FFB162] mb-4" xmlns="http://www.w3.org/2000/svg" fill="none"
                        viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                        </circle>
                        <path class="opacity-75" fill="currentColor"
                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                        </path>
                    </svg>
                    <span class="text-[#C9C1B1] font-['Syne'] text-[10px] font-bold uppercase tracking-[0.3em]">Inicializando Mapa...</span>
                </div>
            </div>
        </transition>
    </div>
</template>

<style>
/* Controles de Zoom oscuros */
.map-future-events .leaflet-control-zoom a {
    background-color: #1B2632 !important;
    color: #C9C1B1 !important;
    border: 1px solid rgba(201, 193, 177, 0.1) !important;
    border-radius: 0 !important;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.5) !important;
    transition: all 0.3s;
}

.map-future-events .leaflet-control-zoom a:hover {
    background-color: #FFB162 !important;
    color: #1B2632 !important;
    border-color: #FFB162 !important;
}

/* Popups estilo cinemático */
.leaflet-popup-content-wrapper {
    background-color: #1B2632 !important;
    border-radius: 2px !important;
    padding: 0 !important;
    box-shadow: 0 20px 40px -10px rgba(0, 0, 0, 0.5) !important;
    border: 1px solid rgba(255, 177, 98, 0.15);
}

.leaflet-popup-content {
    margin: 16px 20px !important;
}

/* El triangulito del popup */
.leaflet-popup-tip {
    background: #1B2632 !important;
    border: 1px solid rgba(255, 177, 98, 0.15);
    border-top: none;
    border-left: none;
}

/* Animación hover del marcador */
.custom-event-marker:hover div {
    transform: scale(1.6);
    background-color: #EEE9DF !important;
    border-color: #A35139 !important;
    box-shadow: 0 0 20px rgba(238, 233, 223, 0.6) !important;
}

/* Ocultar el enlace de "Leaflet" por defecto si se desea más limpio (opcional) */
.leaflet-control-attribution {
    display: none !important;
}
</style>