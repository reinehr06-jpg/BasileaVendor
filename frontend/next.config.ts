import type { NextConfig } from "next";
import { withSentryConfig } from "@sentry/nextjs";

// Origem do backend para o proxy /api.
// - Rodando direto (npm run dev): padrão http://127.0.0.1:8000 (php artisan serve).
// - Docker Compose: a variável BACKEND_ORIGIN=http://backend:8000 é injetada no compose.
const BACKEND_ORIGIN = process.env.BACKEND_ORIGIN || "http://backend";

const nextConfig: NextConfig = {
  // Limita o build a 1 worker: em servidor com pouca RAM, gerar 135 páginas
  // em paralelo estoura a memória (OOM / exit 137). Serializar reduz o pico.
  experimental: {
    cpus: 1,
    workerThreads: false,
    // Evita "barrel imports": o compilador só processa o que é usado desses
    // pacotes grandes, cortando bastante uso de memória e tempo de build.
    optimizePackageImports: ["lucide-react", "date-fns", "recharts"],
  },

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

// O plugin do Sentry processa source maps de TODOS os bundles no build, o que
// pesa muito em RAM. Só o aplicamos quando há token de upload (CI). No servidor
// de 2 GB o build sai "limpo"; o rastreamento em runtime continua funcionando
// pelos arquivos sentry.*.config.ts.
const enableSentryBuild = Boolean(process.env.SENTRY_AUTH_TOKEN);

export default enableSentryBuild
  ? withSentryConfig(nextConfig, {
      org: "basileia",
      project: "vendor-os",
      silent: !process.env.CI,
      widenClientFileUpload: false,
    })
  : nextConfig;
