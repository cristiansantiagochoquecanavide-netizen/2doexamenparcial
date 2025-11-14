import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import react from '@vitejs/plugin-react';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.jsx'
            ],
            refresh: true,
        }),
        react(),
    ],

    build: {
        outDir: 'public', // 👈 Compila React dentro de /public
        emptyOutDir: true, // Limpia carpeta antes de compilar
        manifest: true,
        rollupOptions: {
            input: {
                main: 'resources/js/app.jsx',
                index: 'resources/views/index.html', // 👈 Generar index.html
            },
        },
    },
});