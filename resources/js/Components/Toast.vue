<script setup>
import { ref, watch, onMounted } from 'vue';
import {
    CheckCircleIcon,
    XCircleIcon,
    InformationCircleIcon,
    XMarkIcon
} from '@heroicons/vue/24/outline';

const props = defineProps({
    show: {
        type: Boolean,
        default: false
    },
    type: {
        type: String,
        default: 'success', // success, error, info
        validator: (value) => ['success', 'error', 'info'].includes(value)
    },
    message: {
        type: String,
        required: true
    },
    duration: {
        type: Number,
        default: 3000
    }
});

const emit = defineEmits(['close']);

const visible = ref(props.show);
let timeout = null;

const icons = {
    success: CheckCircleIcon,
    error: XCircleIcon,
    info: InformationCircleIcon,
};

const colors = {
    success: 'bg-green-50 border-green-200 text-green-800',
    error: 'bg-red-50 border-red-200 text-red-800',
    info: 'bg-blue-50 border-blue-200 text-blue-800',
};

const iconColors = {
    success: 'text-green-600',
    error: 'text-red-600',
    info: 'text-blue-600',
};

watch(() => props.show, (newVal) => {
    visible.value = newVal;

    if (newVal && props.duration > 0) {
        clearTimeout(timeout);
        timeout = setTimeout(() => {
            close();
        }, props.duration);
    }
});

const close = () => {
    visible.value = false;
    emit('close');
};

onMounted(() => {
    if (props.show && props.duration > 0) {
        timeout = setTimeout(() => {
            close();
        }, props.duration);
    }
});
</script>

<template>
    <Transition enter-active-class="transform transition ease-out duration-300"
        enter-from-class="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2"
        enter-to-class="translate-y-0 opacity-100 sm:translate-x-0" leave-active-class="transition ease-in duration-200"
        leave-from-class="opacity-100" leave-to-class="opacity-0">
        <div v-if="visible" :class="[
            'fixed top-4 right-4 z-50 max-w-sm w-full shadow-lg rounded-sm border pointer-events-auto',
            colors[type]
        ]">
            <div class="p-4 flex items-start gap-3">
                <component :is="icons[type]" :class="['w-6 h-6 flex-shrink-0', iconColors[type]]" />

                <div class="flex-1 pt-0.5">
                    <p class="text-sm font-medium">
                        {{ message }}
                    </p>
                </div>

                <button @click="close" class="flex-shrink-0 text-slate-400 hover:text-slate-600 transition-colors">
                    <XMarkIcon class="w-5 h-5" />
                </button>
            </div>

            <!-- Progress bar -->
            <div v-if="duration > 0" class="h-1 bg-black/10 overflow-hidden">
                <div class="h-full bg-current animate-progress" :style="{ animationDuration: `${duration}ms` }" />
            </div>
        </div>
    </Transition>
</template>

<style scoped>
@keyframes progress {
    from {
        width: 100%;
    }

    to {
        width: 0%;
    }
}

.animate-progress {
    animation: progress linear forwards;
}
</style>
