<script setup>
import { ref, onMounted, watch } from 'vue';

const props = defineProps({
    src: String,
    alt: String,
    class: String,
});

const imageSrc = ref('');
const loading = ref(true);
const error = ref(false);

const loadImage = async () => {
    if (!props.src) {
        error.value = true;
        loading.value = false;
        return;
    }

    loading.value = true;
    error.value = false;

    try {
        //  NUEVO: Permitir rutas del visor sin fetch
        if (props.src.startsWith('/foto/')) {
            imageSrc.value = props.src;
            loading.value = false;
            return;
        }

        // Para URLs de storage tradicionales
        if (props.src.startsWith('/storage/')) {
            imageSrc.value = props.src;
            loading.value = false;
            return;
        }

        // Para URLs externas o con marca de agua
        const response = await fetch(props.src, {
            method: 'GET',
            credentials: 'same-origin',
        });

        if (!response.ok) {
            throw new Error('Error al cargar la imagen');
        }

        const blob = await response.blob();
        imageSrc.value = URL.createObjectURL(blob);
    } catch (err) {
        console.error('Error cargando imagen:', err);
        error.value = true;
    } finally {
        loading.value = false;
    }
};

onMounted(() => {
    loadImage();
});

watch(() => props.src, () => {
    loadImage();
});
</script>

<template>
    <div class="relative inline-block w-full h-full">
        <!-- Loading skeleton -->
        <div v-if="loading" 
            :class="['animate-pulse bg-gray-200', props.class]"
            class="w-full h-full"
        />

        <!-- Error placeholder -->
        <div v-else-if="error" 
            :class="['bg-gray-100 flex items-center justify-center', props.class]"
        >
            <svg class="w-12 h-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" 
                    d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" 
                />
            </svg>
        </div>

        <!-- Imagen cargada -->
        <img 
            v-else
            :src="imageSrc" 
            :alt="props.alt"
            :class="props.class"
            @error="error = true"
            @contextmenu.prevent
            draggable="false"
        />
    </div>
</template>

<style scoped>
img {
    user-select: none;
    -webkit-user-drag: none;
    -webkit-touch-callout: none;
}
</style>
