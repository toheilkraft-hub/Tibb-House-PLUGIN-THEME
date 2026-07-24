import { defineConfig } from 'vite';
import path from 'path';

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
  resolve: {
    alias: {
      '@': path.resolve(__dirname, './src'),
    },
  },
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
          proxy.on('proxyReq', (proxyReq, req) => {
            proxyReq.setHeader('X-Forwarded-Proto', 'https');
            // Forward the real public domain so WordPress builds correct URLs.
            const fwdHost =
              (req.headers['x-forwarded-host'] as string) ||
              (req.headers['host'] as string) ||
              '';
            if (fwdHost) {
              proxyReq.setHeader('X-Forwarded-Host', fwdHost.split(',')[0].trim());
            }
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
