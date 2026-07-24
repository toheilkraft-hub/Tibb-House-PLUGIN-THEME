---
name: WordPress preview proxy origins
description: Local artifact-panel rendering constraints for WordPress sites behind a Vite proxy.
---

When WordPress is proxied through a local artifact-panel Vite server, do not let the HTML point at the internal Vite port. Browsers viewing the panel cannot reach that loopback origin; same-origin relative asset and script URLs are required locally. Public requests should continue to use the forwarded HTTPS host.

**Why:** WordPress derives absolute URLs from forwarded host/protocol headers, while the panel screenshot browser uses a separate browser-facing proxy rather than the container's loopback port. Internal absolute URLs can make CSS, images, and the preloader script fail even when the server returns 200.

**How to apply:** Preserve `X-Forwarded-Proto` and `X-Forwarded-Host` for real Replit requests. For local proxy requests, rewrite HTML-only internal absolute origins to relative URLs without altering public responses.