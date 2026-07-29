import type { NextConfig } from "next";
import { withSentryConfig } from "@sentry/nextjs";

// Origem do backend para o proxy /api.
// - Rodando direto (npm run dev): padrão http://127.0.0.1:8000 (php artisan serve).
// - Docker Compose: a variável BACKEND_ORIGIN=http://backend:8000 é injetada no compose.
const BACKEND_ORIGIN = process.env.BACKEND_ORIGIN || "http://backend";

const nextConfig: NextConfig = {
  async rewrites() {
    return [
      {
        source: "/api/:path*",
        destination: `${BACKEND_ORIGIN}/api/:path*`,
      },
      {
        source: "/storage/:path*",
        destination: `${BACKEND_ORIGIN}/storage/:path*`,
      },
    ];
  },
  typescript: {
    ignoreBuildErrors: true,
  },
  eslint: {
    ignoreDuringBuilds: true,
  },
};

export default withSentryConfig(nextConfig, {
  org: "basileia",
  project: "vendor-os",
  silent: !process.env.CI,
  widenClientFileUpload: true,
});
