import { defineConfig } from 'vite';
import vue from '@vitejs/plugin-vue';
import laravel from 'laravel-vite-plugin';

export default defineConfig(({ mode }) => ({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        vue(),
    ],
    resolve: {
        alias: {
            vue: 'vue/dist/vue.esm-bundler.js',
        },
    },
    server: {
        host: 'localhost',  // Use localhost instead of 0.0.0.0
        port: 5173,
        cors: true,
    },
    build: {
        outDir: 'public/build',
        emptyOutDir: true,
    },
    base: mode === 'production' ? '/build/' : '/',
}));
