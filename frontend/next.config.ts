import type { NextConfig } from "next";

const nextConfig: NextConfig = {
  async rewrites() {
    return [
      {
        source: '/api/:path*',
        destination: 'http://backend:8000/api/:path*',
      },
      {
        source: '/vendedor/:path*',
        destination: 'http://backend:8000/vendedor/:path*',
      },
      {
        source: '/gestor/:path*',
        destination: 'http://backend:8000/gestor/:path*',
      },
      {
        source: '/configuracoes/:path*',
        destination: 'http://backend:8000/configuracoes/:path*',
      },
      {
        source: '/login/:path*',
        destination: 'http://backend:8000/login/:path*',
      },
      {
        source: '/storage/:path*',
        destination: 'http://backend:8000/storage/:path*',
      },
    ];
  },
};

export default nextConfig;
