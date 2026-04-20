# Estágio de Build
FROM node:20-alpine AS builder

WORKDIR /app

# Copiar arquivos de dependências
COPY package.json package-lock.json* ./
RUN npm install

# Copiar código fonte
COPY . .

# Build do Next.js
RUN npm run build

# Estágio de Produção
FROM node:20-alpine AS runner

WORKDIR /app

ENV NODE_ENV=production

# Copiar arquivos necessários do build
COPY --from=builder /app/public ./public
COPY --from=builder /app/.next ./.next
COPY --from=builder /app/node_modules ./node_modules
COPY --from=builder /app/package.json ./package.json

EXPOSE 3000

CMD ["npm", "start"]
