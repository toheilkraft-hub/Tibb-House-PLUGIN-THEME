import express, { type Express } from "express";
import cors from "cors";
import pinoHttp from "pino-http";
import router from "./routes";
import { logger } from "./lib/logger";
import { wordpressProxy } from "./middlewares/wordpressProxy";

const app: Express = express();

app.use(
  pinoHttp({
    logger,
    serializers: {
      req(req) {
        return {
          id: req.id,
          method: req.method,
          url: req.url?.split("?")[0],
        };
      },
      res(res) {
        return {
          statusCode: res.statusCode,
        };
      },
    },
  }),
);

// Body-parsing/CORS only apply to our own JSON API. Everything else falls
// through to the WordPress reverse proxy below, which needs the raw,
// unparsed request stream (uploads, wp-login form posts, etc.).
app.use("/api", cors(), express.json(), express.urlencoded({ extended: true }), router);

// Everything that isn't our API is the WordPress site (see
// wordpress/ + wp-content/plugins/tibbhouse-core, wp-content/themes/tibbhouse-theme).
app.use(wordpressProxy);

export default app;
