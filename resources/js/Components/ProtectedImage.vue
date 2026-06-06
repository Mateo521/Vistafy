<script setup>
import { ref, onMounted, watch } from 'vue';

defineOptions({
    inheritAttrs: false
});

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
        console.error('Error cargando imagen:', props.src);
        error.value = true;
        loading.value = false;
    };

    img.src = props.src;
};

onMounted(() => loadImage());
watch(() => props.src, () => loadImage());
</script>

<template>
    <div v-if="loading" :class="['animate-pulse bg-gray-900', props.class]" v-bind="$attrs"></div>

    <div v-else-if="error"
        :class="['bg-gray-950 flex items-center justify-center border border-red-600/30', props.class]" v-bind="$attrs">
        <span class="font-mono text-[9px] text-red-600 uppercase tracking-widest">[ ERROR ]</span>
    </div>

    <img v-else :src="imageSrc" :alt="props.alt" :class="props.class" v-bind="$attrs" @error="error = true"
        @contextmenu.prevent draggable="false" />
</template>

<style scoped>
img {
    user-select: none;
    -webkit-user-drag: none;
    -webkit-touch-callout: none;
}
</style>