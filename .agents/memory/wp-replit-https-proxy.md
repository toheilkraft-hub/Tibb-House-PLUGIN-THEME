---
name: WordPress HTTPS proxy fix for Replit
description: How to fix mixed-content CSS blocking and redirect loops for WordPress behind Replit's HTTPS proxy
---

## The Problem
Replit always serves the preview over HTTPS, but the PHP dev server and Vite proxy communicate over HTTP internally. Two failure modes:

1. **Mixed-content block**: WordPress generates `http://` asset URLs; browsers silently refuse to load CSS/JS from `http://` inside an `https://` page → completely unstyled page.
2. **Redirect loop**: If you set `WP_HOME = https://...` but `is_ssl()` returns false, WordPress's `redirect_canonical()` sees a scheme mismatch and redirects HTTP→HTTPS infinitely.

## The Fix (two-part)

### 1. Vite proxy — inject X-Forwarded-Proto
In `artifacts/api-server/vite.config.ts`, use a `configure` callback:

```ts
configure: (proxy) => {
  proxy.on('proxyReq', (proxyReq) => {
    proxyReq.setHeader('X-Forwarded-Proto', 'https');
  });
},
```

Keep `changeOrigin: false` so PHP sees the real `Host` header.

### 2. wp-config.php — backfill $_SERVER['HTTPS']
Critical: when `is_ssl()` returns true, `redirect_canonical()` sees current URL as `https://` matching `WP_HOME` → no redirect loop.

```php
if (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
    $_SERVER['HTTPS'] = 'on';  // Makes is_ssl() return true — PREVENTS redirect loop
}
$protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = preg_replace('/:\d+$/', '', $_SERVER['HTTP_HOST'] ?? 'localhost');
define('WP_HOME',    $protocol . '://' . $host);
define('WP_SITEURL', $protocol . '://' . $host);
```

## Why Screenshot Tool Shows 200 But User Sees Broken
The screenshot tool hits `http://127.0.0.1:80/` — page and CSS are both HTTP, no mixed-content. The user's browser hits `https://[domain].replit.dev/` — CSS must also be HTTPS. Always check HTML source for `http://` vs `https://` in asset hrefs.

**Why:** Replit terminates TLS at its edge; the internal chain is always HTTP. WordPress must be told it's behind HTTPS so it generates correct `https://` asset URLs AND doesn't redirect-loop.
