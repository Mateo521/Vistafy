<script setup>
import { ref, onMounted, watch } from 'vue';
import 'leaflet/dist/leaflet.css';
import L from 'leaflet';

const props = defineProps({
    modelValue: {
        type: Object,
        default: () => ({ lat: -38.4161, lng: -63.6167 })
    },
    initialCenter: {
        type: Object,
        default: () => ({ lat: -38.4161, lng: -63.6167 })
    },
    zoom: {
        type: Number,
        default: 10
    }
});

const emit = defineEmits(['update:modelValue', 'update:location']);

const mapContainer = ref(null);
let map = null;
let marker = null;

const initMap = () => {
    if (!mapContainer.value || map) return;

    // Inicializar mapa centrado en coordenadas del fotógrafo
    map = L.map(mapContainer.value, {
        scrollWheelZoom: true,
        zoomControl: true,
    }).setView([props.initialCenter.lat, props.initialCenter.lng], props.zoom);

    // Capa base
    L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png', {
        maxZoom: 19,
        attribution: '© OpenStreetMap contributors'
    }).addTo(map);

    // Marcador inicial
    const customIcon = L.divIcon({
        className: 'custom-marker',
        html: `<div style="
            width: 24px; 
            height: 24px; 
            background-color: #ef4444; 
            border-radius: 50%; 
            border: 3px solid white; 
            box-shadow: 0 4px 12px rgba(0,0,0,0.3);
            cursor: pointer;
        "></div>`,
        iconSize: [24, 24],
        iconAnchor: [12, 12]
    });

    marker = L.marker([props.modelValue.lat, props.modelValue.lng], {
        icon: customIcon,
        draggable: true
    }).addTo(map);

    // Evento: Click en el mapa
    map.on('click', (e) => {
        updateMarker(e.latlng.lat, e.latlng.lng);
    });

    // Evento: Arrastrar marcador
    marker.on('dragend', (e) => {
        const pos = e.target.getLatLng();
        updateMarker(pos.lat, pos.lng);
    });

    setTimeout(() => map.invalidateSize(), 200);
};

const updateMarker = async (lat, lng) => {
    marker.setLatLng([lat, lng]);
    
    emit('update:modelValue', { lat, lng });

    // Geocodificación inversa (Nominatim)
    try {
        const response = await fetch(
            `https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&zoom=18&addressdetails=1`
        );
        const data = await response.json();
        
        if (data.display_name) {
            emit('update:location', data.display_name);
        }
    } catch (error) {
        console.error('Error en geocodificación:', error);
    }
};

onMounted(() => {
    setTimeout(initMap, 100);
});

watch(() => props.modelValue, (newVal) => {
    if (marker && newVal) {
        marker.setLatLng([newVal.lat, newVal.lng]);
        map.setView([newVal.lat, newVal.lng]);
    }
}, { deep: true });
</script>

<template>
    <div class="relative">
        <div ref="mapContainer" class="w-full h-96 rounded-sm border-2 border-gray-300 overflow-hidden"></div>
        
        <div class="absolute top-4 left-4 bg-white px-4 py-2 rounded-sm shadow-lg border border-gray-200 text-xs text-slate-600 z-[1000]">
            <strong class="block mb-1 text-slate-900"> Seleccioná la ubicación</strong>
            Click en el mapa o arrastrá el marcador
        </div>
    </div>
</template>

<style scoped>
.custom-marker {
    transition: transform 0.2s;
}
.custom-marker:hover {
    transform: scale(1.2);
}
</style>
