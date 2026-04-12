<script setup>
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div class="min-h-screen flex flex-col sm:justify-center items-center pt-10 sm:pt-0 bg-[#111920] font-['Syne'] relative overflow-hidden selection:bg-[#FFB162] selection:text-[#1B2632]">
        
        <div class="absolute inset-0 z-0 pointer-events-none">
            <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_top,_var(--tw-gradient-stops))] from-[#1B2632]/80 via-[#111920] to-[#111920]"></div>
            
            <div class="absolute inset-0 opacity-[0.04]" 
                 style="background-image: url('data:image/svg+xml,%3Csvg viewBox=%220 0 200 200%22 xmlns=%22http://www.w3.org/2000/svg%22%3E%3Cfilter id=%22noiseFilter%22%3E%3CfeTurbulence type=%22fractalNoise%22 baseFrequency=%220.85%22 numOctaves=%223%22 stitchTiles=%22stitch%22/%3E%3C/filter%3E%3Crect width=%22100%25%22 height=%22100%25%22 filter=%22url(%23noiseFilter)%22/%3E%3C/svg%3E');">
            </div>
        </div>

        <div class="w-full sm:max-w-7xl px-6 py-4 z-10 relative">
            
            <div class="flex justify-center mb-12">
                <Link :href="route('home')" class="text-center group flex flex-col items-center">
                    <span class="text-5xl font-['Syne'] font-extrabold tracking-tighter text-[#EEE9DF] transition-transform duration-500 group-hover:scale-105">
                        f<span class="text-[#FFB162]">3</span>3
                    </span>
                    <span class="text-[0.6rem] uppercase tracking-[0.4em] text-[#C9C1B1]/40 mt-1 group-hover:text-[#C9C1B1] transition-colors duration-500">
                        Authentication
                    </span>
                </Link>
            </div>

            <div class="bg-[#1B2632]/80 backdrop-blur-md border border-[#C9C1B1]/10 shadow-2xl sm:rounded-sm relative overflow-hidden">
                <div class="absolute top-0 left-0 w-full h-[2px] bg-gradient-to-r from-transparent via-[#FFB162]/60 to-transparent"></div>
                
                <div class="px-6 py-10 sm:px-12 md:py-16">
                    <slot />
                </div>
            </div>

            <div v-if="$slots.footer" class="mt-8 text-center">
                <slot name="footer" />
            </div>
            
            <div class="mt-16 text-center">
                <p class="text-[9px] text-[#C9C1B1]/30 uppercase tracking-[0.3em] font-bold">
                    &copy; {{ new Date().getFullYear() }} f33 Photography.
                </p>
            </div>
        </div>
    </div>
</template>

<style scoped>
/* Sin animaciones exageradas, mantenemos un estilo serio y editorial */
</style>