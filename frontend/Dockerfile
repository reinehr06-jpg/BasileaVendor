# ============================================================
# Estágio de Build — roda no GitHub Actions (RAM de sobra),
# NÃO no servidor de 2 GB. Usa pnpm com lockfile congelado.
# ============================================================
FROM node:20-alpine AS builder

WORKDIR /app

# pnpm via corepack (já vem no Node 20)
RUN corepack enable

# Instala dependências com o lockfile do pnpm (determinístico)
COPY package.json pnpm-lock.yaml ./
RUN pnpm install --frozen-lockfile

# Código-fonte
COPY . .

# NEXT_PUBLIC_* é embutido no bundle em tempo de BUILD.
# Default /api (o Next faz proxy pro backend em runtime).
ARG NEXT_PUBLIC_API_URL=/api
ENV NEXT_PUBLIC_API_URL=$NEXT_PUBLIC_API_URL
ENV NEXT_TELEMETRY_DISABLED=1

RUN pnpm run build

# ============================================================
# Estágio de Produção — imagem leve só com o necessário
# ============================================================
FROM node:20-alpine AS runner

WORKDIR /app

ENV NODE_ENV=production
ENV NEXT_TELEMETRY_DISABLED=1

RUN corepack enable

COPY --from=builder /app/public ./public
COPY --from=builder /app/.next ./.next
COPY --from=builder /app/node_modules ./node_modules
COPY --from=builder /app/package.json ./package.json
COPY --from=builder /app/pnpm-lock.yaml ./pnpm-lock.yaml

EXPOSE 3000

CMD ["pnpm", "start"]
