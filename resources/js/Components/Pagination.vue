<template>
    <div class="flex justify-center items-center space-x-2">
        <template v-for="(link, index) in links" :key="index">
            <Link
                v-if="link.url"
                :href="link.url"
                :class="[
                    'px-4 py-2 rounded-lg border transition',
                    link.active 
                        ? 'bg-indigo-600 text-white border-indigo-600' 
                        : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50'
                ]"
                v-html="link.label"
            />
            <span
                v-else
                :class="[
                    'px-4 py-2 rounded-lg border',
                    'bg-gray-100 text-gray-400 border-gray-200 cursor-not-allowed'
                ]"
                v-html="link.label"
            />
        </template>
    </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    links: Array,
});
</script>
