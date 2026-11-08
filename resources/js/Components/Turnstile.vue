<script setup>
import { ref, onMounted, onUnmounted } from 'vue';

const props = defineProps({
    siteKey: {
        type: String,
        required: true,
    },
});

const emit = defineEmits(['update:modelValue']);
const container = ref(null);
let widgetId = null;

onMounted(() => {
    const renderWidget = () => {
        if (window.turnstile && container.value) {
            widgetId = window.turnstile.render(container.value, {
                sitekey: props.siteKey,
                callback: (token) => emit('update:modelValue', token),
                'error-callback': () => emit('update:modelValue', null),
                'expired-callback': () => emit('update:modelValue', null),
            });
        }
    };


    if (window.turnstile) {
        renderWidget();
    } else {

        const script = document.createElement('script');
        script.src = 'https://challenges.cloudflare.com/turnstile/v0/api.js?render=explicit';
        script.async = true;
        script.defer = true;
        script.onload = renderWidget;
        document.head.appendChild(script);
    }
});


onUnmounted(() => {
    if (window.turnstile && widgetId !== null) {
        window.turnstile.remove(widgetId);
    }
});
</script>

<template>
    <div ref="container" class="min-h-[65px] flex justify-center"></div>
</template>