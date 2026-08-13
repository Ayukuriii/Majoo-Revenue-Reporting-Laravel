import path from 'node:path';
import { fileURLToPath } from 'node:url';
import tailwindcss from '@tailwindcss/vite';
import react from '@vitejs/plugin-react';
import { defineConfig } from 'vite';

const projectRoot = path.dirname(fileURLToPath(import.meta.url));

export default defineConfig({
    root: 'spa',
    envDir: projectRoot,
    plugins: [react(), tailwindcss()],
    server: {
        host: '127.0.0.1',
        port: 5173,
        strictPort: true,
        allowedHosts: ['majoo-revenue-reporting.test'],
        fs: {
            allow: [projectRoot],
        },
        hmr: {
            host: 'majoo-revenue-reporting.test',
            clientPort: 80,
        },
    },
    build: {
        outDir: path.resolve(projectRoot, 'public/spa'),
        emptyOutDir: true,
    },
});
