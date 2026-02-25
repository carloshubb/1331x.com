import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';
import vue from '@vitejs/plugin-vue'; 
import path from 'path';
export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        vue(),              
        tailwindcss(),
        
    ],
    resolve: {
        alias: {
        '@ckeditor': path.resolve(__dirname, 'node_modules/@ckeditor'),
        },
    },
    base: '/build/',
    server: {
        host: '127.0.0.1',
        // https: true,
        // host: '0.0.0.0',
        // hmr: {
        //     host: '1331x.com',
        //     protocol: 'wss',
        // },
    },
});
