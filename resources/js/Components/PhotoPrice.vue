<script setup>
import { computed } from 'vue';

const props = defineProps({
    price: {
        type: [Number, String],
        required: true,
    },
    eventId: {
        type: [Number, String],
        default: null,
    },
    variant: {
        type: String,
        default: 'inline',
    },
});


const PROMO_EVENT_ID = 18;
const ORIGINAL_PRICE = 5000;

const numericPrice = computed(() => Number(props.price));


const hasDiscount = computed(() => {
    return (
        PROMO_EVENT_ID !== null &&
        Number(props.eventId) === PROMO_EVENT_ID &&
        numericPrice.value > 0 &&
        numericPrice.value < ORIGINAL_PRICE
    );
});
</script>

<template>

    <span
        v-if="variant === 'badge'"
        class="bg-[#E30613] text-white font-bold text-[10px] px-2 py-1 rounded-md shadow-sm inline-flex items-center gap-1.5"
    >
        <span v-if="hasDiscount" class="line-through opacity-75 font-normal">
            ${{ ORIGINAL_PRICE }}
        </span>
        <span>${{ price }}</span>
    </span>


    <span v-else-if="variant === 'large'" class="inline-flex items-baseline gap-2 mr-2">
        <span v-if="hasDiscount" class="text-gray-400 line-through text-xl font-bold leading-none">
            ${{ ORIGINAL_PRICE }}
        </span>
        <span class="text-[#E30613] font-flux text-4xl leading-none">
            ${{ price }}
        </span>
    </span>

    <span v-else class="inline-flex items-baseline gap-1.5">
        <span v-if="hasDiscount" class="line-through opacity-60 text-[0.85em]">
            ${{ ORIGINAL_PRICE }}
        </span>
        <span>${{ price }}</span>
    </span>
</template>