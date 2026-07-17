import { defineConfig } from 'vite';

const rawPort = process.env.PORT;
if (!rawPort) throw new Error('PORT environment variable is required.');
const port = Number(rawPort);
if (Number.isNaN(port) || port <= 0) throw new Error(`Invalid PORT: "${rawPort}"`);

/**
 * Pure reverse-proxy Vite config.
 * Forwards every request to the WordPress PHP server on port 6000.
 * Injects X-Forwarded-Proto: https so WordPress rewrites asset
 * URLs to https:// in HTML output (fixes mixed-content blocking).
 */
export default defineConfig({
  server: {
    port,
    strictPort: true,
    host: '0.0.0.0',
    allowedHosts: true,
    proxy: {
      '/': {
        target: 'http://127.0.0.1:6000',
        changeOrigin: false,
        ws: true,
        configure: (proxy) => {
          proxy.on('proxyReq', (proxyReq) => {
            proxyReq.setHeader('X-Forwarded-Proto', 'https');
          });
        },
      },
    },
  },
  preview: {
    port,
    host: '0.0.0.0',
    allowedHosts: true,
  },
  build: { outDir: 'dist' },
});
