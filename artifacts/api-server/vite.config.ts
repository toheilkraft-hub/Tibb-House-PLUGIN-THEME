import { defineConfig } from 'vite';

const rawPort = process.env.PORT;
if (!rawPort) throw new Error('PORT environment variable is required.');
const port = Number(rawPort);
if (Number.isNaN(port) || port <= 0) throw new Error(`Invalid PORT: "${rawPort}"`);

/**
 * Pure reverse-proxy Vite config.
 * Forwards every request to the WordPress PHP server on port 6000.
 * Injects X-Forwarded-Proto: https so the mu-plugin can rewrite asset
 * URLs to https:// in HTML output (fixes mixed-content blocking).
 * WP_HOME stays http:// so WordPress never issues canonical redirects.
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
            // Belt-and-suspenders: Replit's own proxy already sets this,
            // but we set it explicitly so it works from any access path.
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
