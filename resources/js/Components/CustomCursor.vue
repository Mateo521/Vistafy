<script setup>
import { ref, onMounted, onUnmounted } from 'vue';

const x = ref(-100); 
const y = ref(-100);
const isClicking = ref(false);
const isHovering = ref(false);

const updatePosition = (e) => {
    x.value = e.clientX;
    y.value = e.clientY;
};

const handleMouseDown = () => isClicking.value = true;
const handleMouseUp = () => isClicking.value = false;

 
const handleHoverStart = (e) => {
    if (e.target.closest('a, button, input, textarea, .cursor-pointer')) {
        isHovering.value = true;
    }
};

const handleHoverEnd = () => {
    isHovering.value = false;
};

onMounted(() => {
    window.addEventListener('mousemove', updatePosition);
    window.addEventListener('mousedown', handleMouseDown);
    window.addEventListener('mouseup', handleMouseUp);
   
    window.addEventListener('mouseover', handleHoverStart);
    window.addEventListener('mouseout', handleHoverEnd);
});

onUnmounted(() => {
    window.removeEventListener('mousemove', updatePosition);
    window.removeEventListener('mousedown', handleMouseDown);
    window.removeEventListener('mouseup', handleMouseUp);
    window.removeEventListener('mouseover', handleHoverStart);
    window.removeEventListener('mouseout', handleHoverEnd);
});
</script>

<template>
    <div 
        class="fixed top-0 left-0 pointer-events-none z-[9999] mix-blend-difference hidden md:block"
        :style="{ transform: `translate3d(${x}px, ${y}px, 0)` }"
    >
        <div class="absolute -top-1 -left-1 w-2 h-2 bg-white rounded-full"></div>

        <div 
            class="absolute -top-4 -left-4 w-8 h-8 border border-white rounded-full transition-all duration-300 ease-out"
            :class="[
                isClicking ? 'scale-50 bg-white/30' : 'scale-100',  
                isHovering ? 'scale-150 border-0 bg-white/20' : ''  
            ]"
        ></div>
    </div>
</template>

<style scoped>
 
</style>