import { defineConfig } from 'vite';

const rawPort = process.env.PORT;
if (!rawPort) throw new Error('PORT environment variable is required.');
const port = Number(rawPort);
if (Number.isNaN(port) || port <= 0) throw new Error(`Invalid PORT: "${rawPort}"`);

const basePath = process.env.BASE_PATH ?? '/';

export default defineConfig({
  base: basePath,

  // 'custom' tells Vite not to serve its own index.html for any route.
  // Every request falls through to the proxy → WordPress on port 6000.
  appType: 'custom',

  server: {
    port,
    strictPort: true,
    host: '0.0.0.0',
    allowedHosts: true,
    proxy: {
      '^/.*': {
        target: 'http://127.0.0.1:6000',
        changeOrigin: false,
        selfHandleResponse: true,
        configure: (proxy) => {
          // Forward the original protocol and host so WordPress builds
          // correct absolute URLs (CSS, images, links).
          proxy.on('proxyReq', (proxyReq, req) => {
            const forwardedProto = String(req.headers['x-forwarded-proto'] ?? '')
              .split(',')[0]
              .trim();
            const forwardedHost = String(req.headers['x-forwarded-host'] ?? '')
              .split(',')[0]
              .trim();
            const requestHost = forwardedHost || req.headers.host || '';
            const isLocal = /^(127\.0\.0\.1|localhost)(:\d+)?$/.test(requestHost);
            const protocol = forwardedProto || (isLocal ? 'http' : 'https');

            proxyReq.setHeader('X-Forwarded-Proto', protocol);
            if (requestHost) {
              proxyReq.setHeader('X-Forwarded-Host', requestHost);
            }
          });

          // Strip absolute origin prefixes from HTML responses so that
          // asset URLs are root-relative and work through the Replit proxy.
          proxy.on('proxyRes', (proxyRes, req, res) => {
            const forwardedHost = String(req.headers['x-forwarded-host'] ?? '')
              .split(',')[0]
              .trim();
            const requestHost = forwardedHost || req.headers.host || '';
            const isLocal = /^(127\.0\.0\.1|localhost)(:\d+)?$/.test(requestHost);
            const contentType = String(proxyRes.headers['content-type'] ?? '');

            // Only rewrite HTML served to a local (dev) request.
            if (!isLocal || !contentType.includes('text/html')) {
              proxyRes.pipe(res);
              return;
            }

            const chunks: Buffer[] = [];
            proxyRes.on('data', (chunk: Buffer) => chunks.push(chunk));
            proxyRes.on('end', () => {
              const originPattern = new RegExp(
                `https?:\\/\\/${requestHost.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')}`,
                'g',
              );
              const body = Buffer.concat(chunks)
                .toString('utf8')
                .replace(originPattern, '');
              delete proxyRes.headers['content-length'];
              res.end(body);
            });
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
});
