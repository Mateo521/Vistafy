import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
    ],
    server: {
        // 🆕 AGREGAR ESTAS LÍNEAS
        host: '0.0.0.0', // Permitir conexiones externas
        port: 5173,
        strictPort: true,
        hmr: {
            host: 'localhost', // Usar localhost para HMR
        },
        cors: true, // Habilitar CORS
    },
});
