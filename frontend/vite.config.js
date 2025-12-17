import { defineConfig, loadEnv } from 'vite';
import react from '@vitejs/plugin-react';

export default defineConfig(({ command, mode }) => {
    // Cargar variables de entorno según el modo
    const env = loadEnv(mode, process.cwd(), '');
    
    return {
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
    };
});