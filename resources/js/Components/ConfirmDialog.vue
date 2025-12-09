<script setup>
import { XMarkIcon, ExclamationTriangleIcon } from '@heroicons/vue/24/outline';
import { computed } from 'vue';
import DOMPurify from 'dompurify';
const props = defineProps({
    show: {
        type: Boolean,
        default: false
    },
    title: {
        type: String,
        default: '¿Estás seguro?'
    },
    message: {
        type: String,
        required: true
    },
    confirmText: {
        type: String,
        default: 'Confirmar'
    },
    cancelText: {
        type: String,
        default: 'Cancelar'
    },
    type: {
        type: String,
        default: 'danger', // danger, warning, info
        validator: (value) => ['danger', 'warning', 'info'].includes(value)
    }
});
const sanitizedMessage = computed(() => {
    return DOMPurify.sanitize(props.message, {
        ALLOWED_TAGS: ['strong', 'em', 'br', 'p', 'ul', 'ol', 'li'],
        ALLOWED_ATTR: []
    });
});
const emit = defineEmits(['confirm', 'cancel', 'close']);

const handleConfirm = () => {
    emit('confirm');
    emit('close');
};

const handleCancel = () => {
    emit('cancel');
    emit('close');
};

const typeColors = {
    danger: 'text-red-600 bg-red-50',
    warning: 'text-amber-600 bg-amber-50',
    info: 'text-blue-600 bg-blue-50',
};

const buttonColors = {
    danger: 'bg-red-600 hover:bg-red-700',
    warning: 'bg-amber-600 hover:bg-amber-700',
    info: 'bg-blue-600 hover:bg-blue-700',
};
</script>

<template>
    <Teleport to="body">
        <Transition enter-active-class="transition ease-out duration-200" enter-from-class="opacity-0"
            enter-to-class="opacity-100" leave-active-class="transition ease-in duration-150"
            leave-from-class="opacity-100" leave-to-class="opacity-0">
            <div v-if="show"
                class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm"
                @click.self="handleCancel">
                <Transition enter-active-class="transition ease-out duration-200 transform"
                    enter-from-class="opacity-0 scale-95" enter-to-class="opacity-100 scale-100"
                    leave-active-class="transition ease-in duration-150 transform"
                    leave-from-class="opacity-100 scale-100" leave-to-class="opacity-0 scale-95">
                    <div v-if="show"
                        class="relative bg-white rounded-sm shadow-2xl w-full max-w-md overflow-hidden border border-gray-200"
                        @click.stop>
                        <!-- Header con icono -->
                        <div class="p-6 pb-4">
                            <div class="flex items-start gap-4">
                                <div
                                    :class="['flex-shrink-0 w-12 h-12 rounded-full flex items-center justify-center', typeColors[type]]">
                                    <ExclamationTriangleIcon class="w-6 h-6" />
                                </div>

                                <div class="flex-1 pt-1">
                                    <h3 class="text-lg font-serif font-bold text-slate-900 mb-2">
                                        {{ title }}
                                    </h3>
                                    <p v-html="sanitizedMessage" class="text-sm text-gray-600"></p>
                                </div>

                                <button @click="handleCancel"
                                    class="flex-shrink-0 text-slate-400 hover:text-slate-600 transition-colors">
                                    <XMarkIcon class="w-5 h-5" />
                                </button>
                            </div>
                        </div>

                        <!-- Footer con botones -->
                        <div class="bg-gray-50 px-6 py-4 flex gap-3 justify-end border-t border-gray-100">
                            <button @click="handleCancel"
                                class="px-6 py-2.5 text-sm font-bold uppercase tracking-wider text-slate-600 hover:text-slate-900 border border-gray-300 hover:border-slate-400 rounded-sm transition-colors">
                                {{ cancelText }}
                            </button>

                            <button @click="handleConfirm" :class="[
                                'px-6 py-2.5 text-sm font-bold bg-black uppercase tracking-wider text-white rounded-sm transition-all shadow-md hover:shadow-lg',
                                buttonColors[type]
                            ]">
                                {{ confirmText }}
                            </button>
                        </div>
                    </div>
                </Transition>
            </div>
        </Transition>
    </Teleport>
</template>
