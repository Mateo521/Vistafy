<script setup>
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div class="min-h-screen flex flex-col sm:justify-center items-center pt-10 sm:pt-0 bg-black font-sans relative overflow-hidden selection:bg-[#E30613] selection:text-white text-white">
        
        <div class="absolute inset-0 z-0 pointer-events-none">
            <div class="absolute inset-0 bg-[linear-gradient(rgba(255,255,255,.03)_1px,transparent_1px),linear-gradient(90deg,rgba(255,255,255,.03)_1px,transparent_1px)] bg-[size:32px_32px]"></div>
            <div class="absolute inset-0 bg-gradient-to-t from-black via-transparent to-black"></div>
        </div>

        <div class="w-full sm:max-w-md lg:max-w-7xl px-6 py-4 z-10 relative">
            
            <div class="flex justify-center mb-10">
                <Link :href="route('home')" class="text-center group flex flex-col items-center">
                    <span class="text-4xl font-black tracking-tighter text-white group-hover:text-[#E30613] transition-colors duration-300">
                        F33 <span class="text-[#E30613] group-hover:text-white transition-colors duration-300"></span>
                    </span>
                    <span class="font-mono text-[9px] uppercase tracking-[0.4em] text-gray-500 mt-2 group-hover:text-white transition-colors duration-300">
                        [ ACCESO ]
                    </span>
                </Link>
            </div>

            <div class="bg-zinc-950 border border-white/10 shadow-2xl relative overflow-hidden w-full mx-auto" style="max-width: 100%;">
                
                <div class="absolute top-0 left-0 w-full h-[2px] bg-[#E30613]"></div>
                
                <div class="px-6 py-10 sm:px-12 md:py-12">
                    <slot />
                </div>
            </div>

            <div v-if="$slots.footer" class="mt-8 text-center">
                <slot name="footer" />
            </div>
            
            <div class="mt-16 text-center">
                <p class="font-mono text-[9px] text-gray-600 uppercase  font-bold">
                    &copy; {{ new Date().getFullYear() }} F33. TODOS LOS DERECHOS RESERVADOS.
                </p>
            </div>
        </div>
    </div>
</template>

<style scoped>

</style>