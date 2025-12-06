<script setup>
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-slate-950 relative overflow-hidden selection:bg-white selection:text-slate-900">
        
        <div class="absolute inset-0 z-0">
            <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_top,_var(--tw-gradient-stops))] from-slate-900 via-slate-950 to-black"></div>
            
            <div class="absolute inset-0 opacity-[0.03]" 
                 style="background-image: url('data:image/svg+xml,%3Csvg viewBox=%220 0 200 200%22 xmlns=%22http://www.w3.org/2000/svg%22%3E%3Cfilter id=%22noiseFilter%22%3E%3CfeTurbulence type=%22fractalNoise%22 baseFrequency=%220.65%22 numOctaves=%223%22 stitchTiles=%22stitch%22/%3E%3C/filter%3E%3Crect width=%22100%25%22 height=%22100%25%22 filter=%22url(%23noiseFilter)%22/%3E%3C/svg%3E');">
            </div>
        </div>

        <div class="w-full sm:max-w-md px-6 py-4 z-10 relative">
            
            <div class="flex justify-center mb-10">
                <Link :href="route('home')" class="text-center group">
                    <div class="inline-flex justify-center items-center w-16 h-16 bg-white/5 border border-white/10 rounded-sm mb-4 group-hover:border-white/30 transition-all duration-500">
                        <ApplicationLogo class="h-8 w-8 text-white fill-current" />
                    </div>
                    <div class="flex flex-col">
                        <span class="text-xl font-serif font-bold text-white tracking-widest">EMPRESA</span>
                        <span class="text-[0.6rem] uppercase tracking-[0.4em] text-white/40 group-hover:text-white/60 transition-colors">Photography</span>
                    </div>
                </Link>
            </div>

            <div class="bg-slate-900/50 backdrop-blur-sm border border-white/10 shadow-2xl sm:rounded-sm relative overflow-hidden">
                <div class="absolute top-0 left-0 w-full h-[1px] bg-gradient-to-r from-transparent via-white/50 to-transparent"></div>
                
                <div class="px-6 py-8 sm:px-10">
                    <slot />
                </div>
            </div>

            <div class="mt-8 text-center text-sm">
                <slot name="footer" />
            </div>
            
            <div class="mt-12 text-center">
                <p class="text-[10px] text-white/20 uppercase tracking-widest">
                    &copy; {{ new Date().getFullYear() }} Empresa S.A.
                </p>
            </div>
        </div>
    </div>
</template>

<style scoped>
/* Elimina animaciones previas para un look estático y serio */
</style>