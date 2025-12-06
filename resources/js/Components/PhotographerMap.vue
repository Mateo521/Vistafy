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
    return parseFloat(coord) + (Math.random() - 0.5) * 0.005; // Reducido para mayor precisión
};

const initMap = () => {
    if (!mapContainer.value) return;

    if (map) map.remove();

    // 1. Configuración: Habilitamos Zoom
    map = L.map(mapContainer.value, {
        scrollWheelZoom: true, // ¡Habilitado!
        zoomControl: false,    // Lo agregamos manualmente abajo para posicionarlo
        attributionControl: false
    }).setView([-38.4161, -63.6167], 4);

    // 2. Control de Zoom (Abajo a la derecha)
    L.control.zoom({ position: 'bottomright' }).addTo(map);

    // 3. Capa Base (CartoDB Positron)
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
                const radius = 0.05; // Ajustado para que no se separen tanto visualmente
                const angle = (index / count) * (2 * Math.PI);
                lat += Math.cos(angle) * radius;
                lng += Math.sin(angle) * radius;
            }

            const customIcon = L.divIcon({
                className: 'custom-map-marker',
                html: `<div style="
                    width: 12px; 
                    height: 12px; 
                    background-color: #0f172a; 
                    border-radius: 50%; 
                    border: 2px solid white; 
                    box-shadow: 0 4px 6px rgba(0,0,0,0.3);
                    transition: all 0.2s ease;
                "></div>`,
                iconSize: [12, 12],
                iconAnchor: [6, 6]
            });

            const marker = L.marker([lat, lng], { icon: customIcon })
                .addTo(map)
                .bindPopup(`
                    <div style="text-align: center; font-family: ui-serif, Georgia, serif; min-width: 140px;">
                        <strong style="display:block; font-size: 14px; color: #0f172a; margin-bottom: 4px; line-height: 1.2;">
                            ${photographer.business_name}
                        </strong>
                        <div style="font-size: 9px; color: #64748b; text-transform: uppercase; letter-spacing: 1px; font-family: sans-serif; margin-bottom: 8px;">
                            ${photographer.region}
                        </div>
                        <a href="/fotografos/${photographer.slug}" 
                           style="display:block; font-size:10px; font-weight:bold; color:white; background: #0f172a; padding: 6px 10px; text-decoration:none; border-radius: 2px; font-family: sans-serif; transition: background 0.2s;">
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
            maxZoom: 11, // <--- ESTO EVITA QUE EL ZOOM SE ACERQUE DEMASIADO
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
    <div class="relative w-full h-full bg-slate-100">
        <div ref="mapContainer" class="w-full h-full z-0 outline-none map-editorial"></div>
        
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
/* Estilos base Leaflet */
.leaflet-pane { z-index: 10 !important; }
.leaflet-top, .leaflet-bottom { z-index: 20 !important; }

/* Personalización de Controles de Zoom (Estilo Editorial) */
.map-editorial .leaflet-control-zoom a {
    background-color: #ffffff !important;
    color: #0f172a !important; /* Texto negro */
    border: 1px solid #e2e8f0 !important;
    border-radius: 0 !important; /* Cuadrados */
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1) !important;
    transition: all 0.2s;
}
.map-editorial .leaflet-control-zoom a:hover {
    background-color: #0f172a !important; /* Fondo negro al hover */
    color: #ffffff !important; /* Texto blanco */
    border-color: #0f172a !important;
}

/* Popups */
.leaflet-popup-content-wrapper { 
    border-radius: 2px !important; 
    padding: 0 !important; 
    box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1) !important;
    border: 1px solid #e2e8f0;
}
.leaflet-popup-content { margin: 16px 20px !important; }
.leaflet-popup-tip { background: white !important; border: 1px solid #e2e8f0; border-top: none; border-left: none; }

/* Marcadores */
.custom-map-marker:hover div { 
    transform: scale(1.5); 
    background-color: #3b82f6 !important; 
    border-color: #eff6ff !important;
}
</style>