import { defineConfig } from 'vite';
import path from 'path';

export default defineConfig({
    root: path.resolve(__dirname, 'js/openwire'),
    base: './',
    build: {
        outDir: path.resolve(__dirname, 'js/openwire/dist'),
        emptyOutDir: true,
        minify: 'terser',
        lib: {
            entry: path.resolve(__dirname, 'js/openwire/main.js'),
            name: 'OpenWire',
            formats: ['iife', 'es'],
            fileName: (format) => format === 'es' ? 'openwire.esm.js' : 'openwire.js'
        },
        rollupOptions: {
            output: {
                // IIFE global name
                globals: {
                    'openwire': 'OpenWire'
                },
                exports: 'named'
            }
        }
    }
});
