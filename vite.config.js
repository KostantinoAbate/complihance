import { defineConfig } from 'vite';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        tailwindcss(),
    ],

    server: {
        host: '0.0.0.0',
        port: 5174,
        strictPort: true,
        cors: true,
    },

    build: {
        manifest: true,
        outDir: 'resources/dist',
        emptyOutDir: true,
        rollupOptions: {
            input: {
                complihance: 'resources/js/complihance.js',
            },
        },
    },
});
