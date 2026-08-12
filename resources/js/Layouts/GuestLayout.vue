<script setup>
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div class="min-h-screen flex flex-col sm:justify-center items-center pt-10 sm:pt-0 bg-[#F8F9FA] font-sans relative overflow-hidden selection:bg-[#E30613] selection:text-white text-slate-800 antialiased">
        
    
        <div class="absolute inset-0 z-0 pointer-events-none overflow-hidden">
            
            <div class="absolute top-0 right-0 w-[500px] h-[500px] bg-red-50 rounded-full blur-[100px] -translate-y-1/2 translate-x-1/3"></div>
            
            <div class="absolute bottom-0 left-0 w-[600px] h-[600px] bg-gray-100 rounded-full blur-[120px] translate-y-1/3 -translate-x-1/4"></div>
        </div>

        <div class="w-full sm:max-w-md lg:max-w-lg px-4 py-8 z-10 relative flex flex-col items-center">
            
           
            <div class="flex justify-center mb-10">
                <Link :href="route('home')" class="text-center group flex flex-col items-center gap-2">
                    <div class="font-flux text-5xl md:text-6xl text-black flex items-center gap-1  transition-transform duration-300">
                        f33<span class="w-3 h-3 rounded-full bg-[#E30613] block mb-2 shadow-sm shadow-red-500/30"></span>
                    </div>
                    <span class="text-[10px] font-bold uppercase tracking-widest text-gray-400 group-hover:text-black transition-colors duration-300">
                        Acceso
                    </span>
                </Link>
            </div>

            
            <div class="bg-white border border-gray-100 shadow-[0_8px_30px_rgb(0,0,0,0.04)] rounded relative overflow-hidden w-full transition-all duration-300 hover:shadow-[0_8px_40px_rgb(0,0,0,0.08)]">
                <div class="px-6 py-10 sm:px-10 md:py-12 relative z-10">
                    <slot />
                </div>
            </div>

          
            <div v-if="$slots.footer" class="mt-8 text-center w-full">
                <slot name="footer" />
            </div>
            
        
            <div class="mt-14 text-center">
                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">
                    &copy; {{ new Date().getFullYear() }} F33. Todos los derechos reservados.
                </p>
            </div>
        </div>
    </div>
</template>

<style scoped>

:deep(input) {
    transition: all 0.3s ease;
}
:deep(button) {
    transition: all 0.3s ease;
}
</style>