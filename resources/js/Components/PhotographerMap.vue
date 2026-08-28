<script setup>
import { onMounted, ref, watch, nextTick } from 'vue';
import 'leaflet/dist/leaflet.css';
import L from 'leaflet';
const cartoApiKey = import.meta.env.VITE_CARTO_API_KEY;
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

const initMap = () => {
    if (!mapContainer.value) return;

    if (map) map.remove();


    const boundsArgentina = L.latLngBounds(
        [-55.051258, -73.576081],  
        [-21.781134, -53.637568]   
    );

    map = L.map(mapContainer.value, {
        scrollWheelZoom: true,
        zoomControl: false, 
        attributionControl: false,
        maxBounds: boundsArgentina,
        maxBoundsViscosity: 1.0,
        minZoom: 4,
        maxZoom: 19
    }).setView([-38.4161, -63.6167], 5);


    L.control.zoom({ position: 'bottomright' }).addTo(map);


    L.tileLayer(`https://basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}.png?key=${cartoApiKey}`, {
        maxZoom: 19,
        minZoom: 4,
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>, &copy; <a href="https://carto.com/attributions">CARTO</a>',

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


            if (count > 1) {
                const radius = 0.03; 
                const angle = (index / count) * (2 * Math.PI);
                lat += Math.cos(angle) * radius;
                lng += Math.sin(angle) * radius;
            }


            const customIcon = L.divIcon({
                className: 'custom-photographer-marker',
                html: `<div style="
                    width: 16px; 
                    height: 16px; 
                    background-color: #E30613; 
                    border-radius: 50%; 
                    border: 3px solid #ffffff; 
                    box-shadow: 0 4px 10px rgba(227, 6, 19, 0.4);
                    transition: transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
                " class="marker-dot"></div>`,
                iconSize: [16, 16],
                iconAnchor: [8, 8],
                popupAnchor: [0, -10]
            });


            const marker = L.marker([lat, lng], { icon: customIcon })
                .addTo(map)
                .bindPopup(`
                    <div style="text-align: center; font-family: 'Inter', system-ui, sans-serif; min-width: 180px; padding: 8px 0 4px 0;">
                        
                        <div style="display: flex; align-items: center; justify-content: center; width: 40px; height: 40px; background-color: #f1f5f9; border-radius: 50%; margin: 0 auto 12px auto; color: #94a3b8;">
                            <svg style="width: 20px; height: 20px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                        </div>
                        
                        <strong style="display:block; font-size: 15px; font-weight: 800; color: #0f172a; margin-bottom: 4px; line-height: 1.2;">
                            ${photographer.business_name}
                        </strong>
                        
                        <div style="font-size: 10px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 16px; display: flex; align-items: center; justify-content: center; gap: 4px;">
                            <svg style="width: 12px; height: 12px; color: #E30613;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                            ${photographer.region}
                        </div>
                        
                        <a href="/fotografos/${photographer.slug}" 
                        style="display:block; font-size:10px; font-weight:700; color:#ffffff; background: #0f172a; padding: 10px 16px; text-decoration:none; border-radius: 99px; text-transform: uppercase; letter-spacing: 0.1em; transition: background 0.2s;">
                        Ver Perfil
                        </a>
                    </div>
                `);
            
            markers.push(marker);
        });
    });

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
    <div class="relative w-full h-full bg-[#F8F9FA] rounded-xl overflow-hidden shadow-sm border border-gray-100">
        

        <div ref="mapContainer" class="w-full h-full z-0 outline-none map-sleek-directory"></div>
        

        <transition 
            enter-active-class="transition-opacity duration-300"
            leave-active-class="transition-opacity duration-500"
            enter-from-class="opacity-0"
            leave-to-class="opacity-0"
        >
            <div v-if="!isMapReady" class="absolute inset-0 flex items-center justify-center bg-white/80 backdrop-blur-md z-20 rounded-xl">
                <div class="flex flex-col items-center gap-4">
                    <div class="h-10 w-10 border-4 border-gray-100 border-t-[#E30613] rounded-full animate-spin shadow-sm"></div>
                    <span class="text-slate-600 font-bold text-xs uppercase tracking-wider">Cargando directorio...</span>
                </div>
            </div>
        </transition>
    </div>
</template>

<style>

.leaflet-pane { z-index: 10 !important; }
.leaflet-top, .leaflet-bottom { z-index: 20 !important; }


.map-sleek-directory .leaflet-control-zoom {
    border: none !important;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08) !important;
    border-radius: 12px !important;
    overflow: hidden;
    margin-right: 20px !important;
    margin-bottom: 20px !important;
}

.map-sleek-directory .leaflet-control-zoom a {
    background-color: #ffffff !important;
    color: #334155 !important;
    border: none !important;
    border-bottom: 1px solid #f1f5f9 !important;
    width: 36px !important;
    height: 36px !important;
    line-height: 36px !important;
    transition: all 0.2s ease;
}

.map-sleek-directory .leaflet-control-zoom a:last-child {
    border-bottom: none !important;
}

.map-sleek-directory .leaflet-control-zoom a:hover {
    background-color: #f8fafc !important;
    color: #E30613 !important;
}


.leaflet-popup-content-wrapper {
    background-color: #ffffff !important;
    border-radius: 20px !important;
    padding: 0 !important;
    color: #334155 !important;
    box-shadow: 0 10px 40px -10px rgba(0,0,0,0.15), 0 0 20px rgba(0,0,0,0.05) !important;
    border: 1px solid rgba(0,0,0,0.05) !important;
}

.leaflet-popup-content {
    margin: 16px !important;
    line-height: 1.5 !important;
}


.leaflet-popup-tip-container {
    margin-top: -1px !important;
}

.leaflet-popup-tip {
    background-color: #ffffff !important;
    box-shadow: 0 10px 40px -10px rgba(0,0,0,0.15) !important;
}


.leaflet-popup-close-button {
    color: #94a3b8 !important;
    font-size: 20px !important;
    padding: 8px 8px 0 0 !important;
    font-weight: 300 !important;
    transition: color 0.2s;
    z-index: 10;
}

.leaflet-popup-close-button:hover {
    color: #E30613 !important;
    background-color: transparent !important;
}


.custom-photographer-marker .marker-dot {
    transform: scale(1);
}

.custom-photographer-marker:hover .marker-dot { 
    transform: scale(1.4) !important; 
    background-color: #E30613 !important; 
    border-color: #ffffff !important;
    box-shadow: 0 6px 15px rgba(227, 6, 19, 0.5) !important;
}


.leaflet-popup-content a:hover {
    background-color: #E30613 !important;
}

.leaflet-control-attribution {
    display: none !important;
}
</style>