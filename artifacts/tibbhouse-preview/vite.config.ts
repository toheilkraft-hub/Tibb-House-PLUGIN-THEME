import path from 'path';
import react from '@vitejs/plugin-react';
import tailwindcss from '@tailwindcss/vite';
import { defineConfig } from 'vite';

import runtimeErrorOverlay from '@replit/vite-plugin-runtime-error-modal';

const rawPort = process.env.PORT;

if (!rawPort) {
  throw new Error(
    'PORT environment variable is required but was not provided.',
  );
}

const port = Number(rawPort);

if (Number.isNaN(port) || port <= 0) {
  throw new Error(`Invalid PORT value: "${rawPort}"`);
}

const basePath = process.env.BASE_PATH;

if (!basePath) {
  throw new Error(
    'BASE_PATH environment variable is required but was not provided.',
  );
}

export default defineConfig({
  base: basePath,
  plugins: [
    react(),
    tailwindcss(),
    runtimeErrorOverlay(),
    ...(process.env.NODE_ENV !== 'production' &&
    process.env.REPL_ID !== undefined
      ? [
          await import('@replit/vite-plugin-cartographer').then((m) =>
            m.cartographer({
              root: path.resolve(import.meta.dirname, '..'),
            }),
          ),
          await import('@replit/vite-plugin-dev-banner').then((m) =>
            m.devBanner(),
          ),
        ]
      : []),
  ],
  resolve: {
    alias: {
      '@': path.resolve(import.meta.dirname, 'src'),
      '@assets': path.resolve(
        import.meta.dirname,
        '..',
        '..',
        'attached_assets',
      ),
    },
    dedupe: ['react', 'react-dom'],
  },
  root: path.resolve(import.meta.dirname),
  build: {
    outDir: path.resolve(import.meta.dirname, 'dist/public'),
    emptyOutDir: true,
  },
  server: {
    port,
    strictPort: true,
    host: '0.0.0.0',
    allowedHosts: true,
    fs: {
      strict: true,
    },
    proxy: {
      '^/.*': {
        target: 'http://127.0.0.1:6000',
        changeOrigin: false,
        configure: (proxy) => {
          proxy.on('proxyReq', (proxyReq, req) => {
            const forwardedProto = String(req.headers['x-forwarded-proto'] ?? '')
              .split(',')[0]
              .trim();
            const forwardedHost = String(req.headers['x-forwarded-host'] ?? '')
              .split(',')[0]
              .trim();
            const requestHost = forwardedHost || req.headers.host || '';
            const isLocalHost = /^(127\.0\.0\.1|localhost)(:\d+)?$/.test(requestHost);
            const protocol =
              forwardedProto || (isLocalHost ? 'http' : 'https');

            proxyReq.setHeader('X-Forwarded-Proto', protocol);
            if (requestHost) {
              proxyReq.setHeader('X-Forwarded-Host', requestHost);
            }
          });
          proxy.on('proxyRes', (proxyRes, req, res) => {
            const forwardedHost = String(req.headers['x-forwarded-host'] ?? '')
              .split(',')[0]
              .trim();
            const requestHost = forwardedHost || req.headers.host || '';
            const isLocalHost = /^(127\.0\.0\.1|localhost)(:\d+)?$/.test(requestHost);
            const contentType = String(proxyRes.headers['content-type'] ?? '');

            if (!isLocalHost || !contentType.includes('text/html')) {
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
