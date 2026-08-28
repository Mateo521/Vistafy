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
    if (map) addMarkers();
}, { deep: true });

const initMap = () => {
    if (!mapContainer.value) return;

    if (map) {
        map.remove();
    }

  
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

   
    L.tileLayer('https://basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}.png?key=cb1_2gqk_1_77e379e8d2f7f215be887004', {
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

    let validEvents = 0;
    let invalidEvents = 0;

    props.events.forEach((event) => {
        if (event.latitude && event.longitude) {
            validEvents++;

            const lat = parseFloat(event.latitude);
            const lng = parseFloat(event.longitude);

            if (isNaN(lat) || isNaN(lng)) {
                console.error(`Coordenadas inválidas para "${event.title}":`, { lat, lng });
                invalidEvents++;
                return;
            }

        
            const customIcon = L.divIcon({
                className: 'custom-event-marker',
                html: `<div style="
                    width: 20px; 
                    height: 20px; 
                    background-color: #E30613; 
                    border-radius: 50%; 
                    border: 3px solid #ffffff; 
                    box-shadow: 0 4px 10px rgba(227, 6, 19, 0.4);
                    transition: transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
                " class="marker-dot"></div>`,
                iconSize: [20, 20],
                iconAnchor: [10, 10],
                popupAnchor: [0, -12]
            });

            try {
                const marker = L.marker([lat, lng], { icon: customIcon })
                    .addTo(map)
                    .bindPopup(`
                        <div style="font-family: 'Inter', system-ui, sans-serif; min-width: 240px; max-width: 280px; padding: 0;">
                            <div style="margin-bottom: 12px; border-radius: 4px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.05); background: #f8fafc;">
                                <img 
                                    src="${event.cover_image || event.cover_image_url || 'https://images.unsplash.com/photo-1511795409834-ef04bbd61622?auto=format&fit=crop&w=400&q=80'}"
                                    alt="${event.title || event.name}"
                                    style="width: 100%; height: 140px; object-fit: cover;"
                                    onerror="this.src='https://images.unsplash.com/photo-1511795409834-ef04bbd61622?auto=format&fit=crop&w=400&q=80'"
                                />
                            </div>
                            <h3 style="font-size: 16px; font-weight: 800; color: #0f172a; margin: 0 0 4px 0; line-height: 1.2;">
                                ${event.title || event.name}
                            </h3>
                            <div style="font-size: 12px; font-weight: 600; color: #64748b; margin-bottom: 4px; display: flex; align-items: center; gap: 4px;">
                                <svg style="width: 14px; height: 14px; color: #E30613;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                ${event.location || 'Ubicación a confirmar'}
                            </div>
                            <div style="font-size: 12px; color: #E30613; font-weight: 700; margin-bottom: 16px; padding-left: 18px;">
                                ${event.formatted_date || (event.event_date ? new Date(event.event_date).toLocaleDateString('es-ES') : '')}
                            </div>
                            <a href="/eventos-futuros/${event.id || event.slug}" 
                               style="display:block; font-size: 12px; font-weight: 700; color: #ffffff; background: #0f172a; padding: 10px 16px; text-decoration:none; text-align: center; border-radius: 99px; transition: background 0.2s;">
                               Ver detalles
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
</script>

<template>
    <div class="relative w-full h-[100vh] min-h-[500px] bg-[#F8F9FA] overflow-hidden rounded shadow-sm border border-gray-100">
        
    
        <div ref="mapContainer" class="w-full h-full z-0 outline-none map-sleek-events"></div>

       
        <transition enter-active-class="transition-opacity duration-300"
            leave-active-class="transition-opacity duration-500" enter-from-class="opacity-0"
            leave-to-class="opacity-0">
            <div v-if="!isMapReady" class="absolute inset-0 flex items-center justify-center bg-white/80 backdrop-blur-md z-20">
                <div class="flex flex-col items-center gap-4">
                    <div class="h-10 w-10 border-4 border-gray-100 border-t-[#E30613] rounded-full animate-spin shadow-sm"></div>
                    <span class="text-slate-600 font-bold text-xs uppercase tracking-wider">Cargando mapa...</span>
                </div>
            </div>
        </transition>
        
    </div>
</template>

<style>


.map-sleek-events {
    font-family: 'Inter', system-ui, -apple-system, sans-serif;
    background: #F8F9FA;
}


.map-sleek-events .leaflet-control-zoom {
    border: none !important;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08) !important;
    border-radius: 4px !important;
    overflow: hidden;
    margin-right: 20px !important;
    margin-bottom: 20px !important;
}

.map-sleek-events .leaflet-control-zoom a {
    background-color: #ffffff !important;
    color: #334155 !important;
    border: none !important;
    border-bottom: 1px solid #f1f5f9 !important;
    width: 36px !important;
    height: 36px !important;
    line-height: 36px !important;
    transition: all 0.2s ease;
}

.map-sleek-events .leaflet-control-zoom a:last-child {
    border-bottom: none !important;
}

.map-sleek-events .leaflet-control-zoom a:hover {
    background-color: #f8fafc !important;
    color: #E30613 !important;
}


.leaflet-popup-content-wrapper {
    background-color: #ffffff !important;
    border-radius: 4px !important;
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


.custom-event-marker .marker-dot {
    transform: scale(1);
}

.custom-event-marker:hover .marker-dot {
    transform: scale(1.3) !important;
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