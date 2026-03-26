# Dockerfile raíz para EasyPanel
FROM node:18-alpine AS builder

# Install OpenSSL for Prisma
RUN apk add --no-cache openssl

WORKDIR /app

# Copiar backend
COPY backend/package*.json ./backend/
WORKDIR /app/backend

# Instalar TODAS las dependencias (incluyendo dev para build)
RUN npm install && npm cache clean --force

COPY backend/. .

# Generar Prisma Client
RUN npx prisma generate

# Build TypeScript
RUN npm run build

# Production image
FROM node:18-alpine

# Install OpenSSL and Bash for Prisma runtime
RUN apk add --no-cache openssl bash

WORKDIR /app/backend

ENV NODE_ENV=production

# Copiar solo archivos construidos y production deps
COPY --from=builder /app/backend/dist ./dist
COPY --from=builder /app/backend/package.json ./package.json

# Instalar solo production dependencies
RUN npm install --omit=dev && npm cache clean --force

# Copiar Prisma client generado y schema
COPY --from=builder /app/backend/node_modules/.prisma ./node_modules/.prisma
COPY --from=builder /app/backend/node_modules/@prisma ./node_modules/@prisma
COPY --from=builder /app/backend/prisma ./prisma
COPY --from=builder /app/backend/start.sh ./start.sh

# Hacer ejecutable el script de inicio
RUN chmod +x ./start.sh

EXPOSE 3001

HEALTHCHECK --interval=30s --timeout=3s --start-period=40s --retries=3 \
  CMD node -e "require('http').get('http://localhost:3001/health', (r) => {process.exit(r.statusCode === 200 ? 0 : 1)})"

# Ejecutar script de inicio
CMD ["./start.sh"]
