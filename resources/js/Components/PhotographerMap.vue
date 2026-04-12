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
const isMapReady = ref(false);
let map = null;
let markers = [];

// Función "Jitter" para evitar superposición exacta
const jitter = (coord) => {
    return parseFloat(coord) + (Math.random() - 0.5) * 0.005;
};

const initMap = () => {
    if (!mapContainer.value) return;

    if (map) map.remove();

    // 1. Configuración: Habilitamos Zoom
    map = L.map(mapContainer.value, {
        scrollWheelZoom: true,
        zoomControl: false, 
        attributionControl: false
    }).setView([-38.4161, -63.6167], 4);

    // 2. Control de Zoom (Abajo a la derecha)
    L.control.zoom({ position: 'bottomright' }).addTo(map);

    // 3. Capa Base (CartoDB Dark Matter - Esencial para el tema)
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

    // Agrupar por ubicación exacta
    const grouped = {};
    
    props.photographers.forEach(p => {
        if(p.latitude && p.longitude) {
            const key = `${p.latitude},${p.longitude}`;
            if (!grouped[key]) grouped[key] = [];
            grouped[key].push(p);
        }
    });

    Object.values(grouped).forEach(group => {
        const count = group.length;
        
        group.forEach((photographer, index) => {
            let lat = parseFloat(photographer.latitude);
            let lng = parseFloat(photographer.longitude);

            // Dispersión circular si hay múltiples en el mismo punto
            if (count > 1) {
                const radius = 0.05; 
                const angle = (index / count) * (2 * Math.PI);
                lat += Math.cos(angle) * radius;
                lng += Math.sin(angle) * radius;
            }

            // Marcador personalizado (Naranja f33)
            const customIcon = L.divIcon({
                className: 'custom-map-marker',
                html: `<div style="
                    width: 12px; 
                    height: 12px; 
                    background-color: #FFB162; 
                    border-radius: 50%; 
                    border: 2px solid #1B2632; 
                    box-shadow: 0 0 12px rgba(255,177,98,0.6);
                    transition: all 0.3s cubic-bezier(0.25, 0.46, 0.45, 0.94);
                "></div>`,
                iconSize: [12, 12],
                iconAnchor: [6, 6]
            });

            // Popup HTML Estilizado al Dark Theme
            const marker = L.marker([lat, lng], { icon: customIcon })
                .addTo(map)
                .bindPopup(`
                    <div style="text-align: center; font-family: 'Syne', sans-serif; min-width: 150px; padding-top: 4px;">
                        <strong style="display:block; font-family: 'Cormorant Garamond', serif; font-size: 22px; font-weight: 300; color: #EEE9DF; margin-bottom: 4px; line-height: 1.1;">
                            ${photographer.business_name}
                        </strong>
                        <div style="font-size: 9px; font-weight: 700; color: #C9C1B1; text-transform: uppercase; letter-spacing: 0.2em; margin-bottom: 12px;">
                            ${photographer.region}
                        </div>
                        <a href="/fotografos/${photographer.slug}" 
                           style="display:block; font-size:9px; font-weight:bold; color:#1B2632; background: #FFB162; padding: 8px 12px; text-decoration:none; border-radius: 2px; text-transform: uppercase; letter-spacing: 0.2em; transition: background 0.3s;">
                           VER PERFIL
                        </a>
                    </div>
                `);
            
            markers.push(marker);
        });
    });

    // AJUSTE DE ZOOM AUTOMÁTICO
    if (markers.length > 0) {
        const group = new L.featureGroup(markers);
        map.fitBounds(group.getBounds().pad(0.1), {
            maxZoom: 11, 
            padding: [50, 50]
        });
    }
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
    <div class="relative w-full h-full bg-[#111920]">
        <div ref="mapContainer" class="w-full h-full z-0 outline-none map-editorial"></div>
        
        <transition 
            enter-active-class="transition-opacity duration-300"
            leave-active-class="transition-opacity duration-500"
            enter-from-class="opacity-0"
            leave-to-class="opacity-0"
        >
            <div v-if="!isMapReady" class="absolute inset-0 flex items-center justify-center bg-[#1B2632] z-20">
                <div class="flex flex-col items-center">
                    <svg class="animate-spin h-6 w-6 text-[#FFB162] mb-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span class="text-[#C9C1B1] font-['Syne'] text-[10px] font-bold uppercase tracking-[0.3em]">Inicializando Mapa...</span>
                </div>
            </div>
        </transition>
    </div>
</template>

<style>
/* Estilos base Leaflet */
.leaflet-pane { z-index: 10 !important; }
.leaflet-top, .leaflet-bottom { z-index: 20 !important; }

/* Personalización de Controles de Zoom (Estilo Editorial Oscuro) */
.map-editorial .leaflet-control-zoom a {
    background-color: #1B2632 !important;
    color: #C9C1B1 !important;
    border: 1px solid rgba(201, 193, 177, 0.1) !important;
    border-radius: 0 !important;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.5) !important;
    transition: all 0.3s;
}
.map-editorial .leaflet-control-zoom a:hover {
    background-color: #FFB162 !important; 
    color: #1B2632 !important; 
    border-color: #FFB162 !important;
}

/* Popups Estilo Cinemático */
.leaflet-popup-content-wrapper { 
    background-color: #1B2632 !important;
    border-radius: 2px !important; 
    padding: 0 !important; 
    box-shadow: 0 20px 40px -10px rgba(0, 0, 0, 0.5) !important;
    border: 1px solid rgba(255, 177, 98, 0.15);
}
.leaflet-popup-content { margin: 16px 20px !important; }

/* Triángulo del Popup */
.leaflet-popup-tip { 
    background: #1B2632 !important; 
    border: 1px solid rgba(255, 177, 98, 0.15); 
    border-top: none; 
    border-left: none; 
}

/* Efecto Hover del Marcador */
.custom-map-marker:hover div { 
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