import { defineConfig } from 'vite'
import laravel from 'laravel-vite-plugin'
import react from '@vitejs/plugin-react'

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/js/app.jsx',
                'resources/css/app.css',  // ← Faltaba esto
                'resources/css/CRUD.css',
               'resources/css/sidebar.css'
            ],
            refresh: true,
        }),
        react(),
    ],
})

