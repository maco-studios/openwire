import { defineConfig } from 'vitest/config';

export default defineConfig({
    test: {
        globals: true,
        environment: 'jsdom',
        include: ['js/openwire/tests/**/*.test.js'],
        setupFiles: ['./js/openwire/tests/setup.js']
    }
});
