import { defineConfig } from 'vite';
import react from '@vitejs/plugin-react';
import fs from 'fs';
import path from 'path';

export default defineConfig({
    plugins: [
        react(),
        {
            name: 'copy-manifest',
            writeBundle() {
                const manifestPath = path.resolve('./public/build/.vite/manifest.json');
                const destPath = path.resolve('./public/build/manifest.json');
                
                if (fs.existsSync(manifestPath)) {
                    fs.copyFileSync(manifestPath, destPath);
                    console.log('✓ Manifest copiado a build/manifest.json');
                }
            }
        }
    ],

    build: {
        manifest: true,
        outDir: 'public/build',
        emptyOutDir: true,
        rollupOptions: {
            input: {
                main: 'resources/js/app.jsx'
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
    }
});