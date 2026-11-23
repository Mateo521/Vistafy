<script>
export default {
    inheritAttrs: false
}
</script>

<script setup>
import { ref, computed, watch } from 'vue';

const props = defineProps({
    src: String,
    alt: String,
    class: String,
    width: [Number, String],
    height: [Number, String],
    loading: {
        type: String,
        default: 'lazy',
    },
});

const isLoaded = ref(false);
const hasError = ref(false);

watch(() => props.src, () => {
    isLoaded.value = false;
    hasError.value = false;
});

const computedSrc = computed(() => {
    if (!props.src) return '';
    const separator = props.src.includes('?') ? '&' : '?';
    return `${props.src}${separator}v=f33`;
});
</script>

<template>
    <div v-if="hasError || !props.src"
        :class="['bg-gray-950 flex items-center justify-center border border-red-600/30', props.class]"
        v-bind="$attrs">
        <span class="font-mono text-[9px] text-red-600 uppercase tracking-widest">[ ERROR ]</span>
    </div>

    <img v-else
        :src="computedSrc"
        :alt="props.alt || ''"
        :width="props.width"
        :height="props.height"
        :loading="props.loading"
        decoding="async"
        :class="[
            props.class,
            !isLoaded ? 'bg-gray-900/40' : 'bg-transparent transition-opacity duration-200'
        ]"
        v-bind="$attrs"
        @load="isLoaded = true"
        @error="hasError = true"
        @contextmenu.prevent
        draggable="false" />
</template>

<style scoped>
img {
    user-select: none;
    -webkit-user-drag: none;
    -webkit-touch-callout: none;
}
</style>