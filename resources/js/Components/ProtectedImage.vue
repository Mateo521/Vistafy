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

const loadImage = () => {
    if (!props.src) {
        error.value = true;
        loading.value = false;
        return;
    }

    loading.value = true;
    error.value = false;

     
    const img = new Image();
    
    img.onload = () => {
       
        imageSrc.value = props.src;
        loading.value = false;
    };
    
    img.onerror = () => {
        console.error('Error cargando imagen desde la nube:', props.src);
        error.value = true;
        loading.value = false;
    };

   
    img.src = props.src;
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
        <div v-if="loading" 
            :class="['animate-pulse bg-gray-200', props.class]"
            class="w-full h-full"
        />

        <div v-else-if="error" 
            :class="['bg-gray-100 flex items-center justify-center', props.class]"
        >
            <svg class="w-12 h-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" 
                    d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" 
                />
            </svg>
        </div>

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