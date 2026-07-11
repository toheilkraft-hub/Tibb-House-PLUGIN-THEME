import { defineConfig } from 'vite';

const rawPort = process.env.PORT;
if (!rawPort) throw new Error('PORT environment variable is required.');
const port = Number(rawPort);
if (Number.isNaN(port) || port <= 0) throw new Error(`Invalid PORT: "${rawPort}"`);

/**
 * Pure reverse-proxy Vite config.
 * Forwards every request to the WordPress PHP server on port 6000.
 * Injects X-Forwarded-Proto: https so WordPress generates correct https://
 * asset URLs (prevents mixed-content CSS/JS blocking in the browser).
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
            // Tell PHP it's behind HTTPS — Replit always serves HTTPS.
            // wp-config.php reads this to set WP_HOME/WP_SITEURL and
            // backfills $_SERVER['HTTPS'] so WordPress's is_ssl() = true,
            // preventing redirect loops.
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
  build: {
    outDir: 'dist',
  },
});
