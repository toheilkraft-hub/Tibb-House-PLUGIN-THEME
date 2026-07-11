import { defineConfig } from 'vite';

const rawPort = process.env.PORT;
if (!rawPort) throw new Error('PORT environment variable is required.');
const port = Number(rawPort);
if (Number.isNaN(port) || port <= 0) throw new Error(`Invalid PORT: "${rawPort}"`);

/**
 * Pure reverse-proxy Vite config.
 * Every request is forwarded to the WordPress PHP server on port 6000.
 * No React/HTML — the preview panel shows the live WordPress site directly.
 */
export default defineConfig({
  server: {
    port,
    strictPort: true,
    host: '0.0.0.0',
    allowedHosts: true,
    proxy: {
      // Proxy everything to WordPress
      '/': {
        target: 'http://127.0.0.1:6000',
        changeOrigin: false,
        ws: true,
      },
    },
  },
  preview: {
    port,
    host: '0.0.0.0',
    allowedHosts: true,
  },
  // No build needed — this config is purely for the dev proxy
  build: {
    outDir: 'dist',
  },
});
