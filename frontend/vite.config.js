import { defineConfig } from 'vite';
import react from '@vitejs/plugin-react';

export default defineConfig({
    plugins: [
        react({
            fastRefresh: true,
        })
    ],

    build: {
        manifest: true,
        outDir: 'dist',
        emptyOutDir: true,
        minify: 'esbuild',
        chunkSizeWarningLimit: 1000,
        rollupOptions: {
            input: './index.html',
            output: {
                manualChunks: {
                    'react-vendor': ['react', 'react-dom', 'react-router-dom'],
                    'axios-vendor': ['axios']
                }
            }
        },
        // Asegurar que las variables de entorno se incluyan en el build
        define: {
            'import.meta.env.VITE_API_URL': JSON.stringify(
                process.env.VITE_API_URL || 'http://127.0.0.1:8000/api'
            ),
        }
    },
    
    server: {
        host: '127.0.0.1',
        port: 5173,
        strictPort: false,
        cors: true,
        hmr: {
            host: 'localhost',
            port: 5173,
            protocol: 'ws'
        }
    },
    
    optimizeDeps: {
        include: ['react', 'react-dom', 'react-router-dom', 'axios']
    }
});