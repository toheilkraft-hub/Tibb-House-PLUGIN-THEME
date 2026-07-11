import http from "node:http";
import type { Request, Response, NextFunction } from "express";
import { logger } from "../lib/logger";

/**
 * Reverse-proxies everything that isn't handled by our own Express routes
 * to the WordPress site running on the internal PHP server. Must be
 * mounted BEFORE any body-parsing middleware (express.json/urlencoded),
 * since those consume the request stream and would break uploads/forms.
 *
 * The original Host header is forwarded unchanged so WordPress (see
 * wordpress/wp-config.php) derives the correct site URL for whichever
 * public domain the request came in on.
 */
const WORDPRESS_PORT = 6000;

export function wordpressProxy(
  req: Request,
  res: Response,
  _next: NextFunction,
): void {
  const proxyReq = http.request(
    {
      host: "127.0.0.1",
      port: WORDPRESS_PORT,
      path: req.url,
      method: req.method,
      headers: req.headers,
    },
    (proxyRes) => {
      res.writeHead(proxyRes.statusCode ?? 502, proxyRes.headers);
      proxyRes.pipe(res, { end: true });
    },
  );

  proxyReq.on("error", (err) => {
    logger.error({ err }, "WordPress proxy request failed");
    if (!res.headersSent) {
      res.writeHead(502, { "content-type": "text/plain" });
    }
    res.end("Bad gateway: WordPress backend is not responding.");
  });

  req.pipe(proxyReq, { end: true });
}
