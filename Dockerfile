# Dockerfile multi-stage para EasyPanel (Frontend + Backend)

# ============================================
# STAGE 1: Build Frontend
# ============================================
FROM node:18-alpine AS frontend-builder

WORKDIR /app/frontend

# Copiar package files del frontend
COPY modern-market-makers-main/package*.json ./
COPY modern-market-makers-main/vite.config.ts ./
COPY modern-market-makers-main/tsconfig*.json ./
COPY modern-market-makers-main/tailwind.config.ts ./
COPY modern-market-makers-main/postcss.config.js ./
COPY modern-market-makers-main/index.html ./
COPY modern-market-makers-main/public ./public
COPY modern-market-makers-main/src ./src

# Instalar dependencias y construir
RUN npm install
RUN npm run build

# ============================================
# STAGE 2: Build Backend
# ============================================
FROM node:18-alpine AS backend-builder

# Install OpenSSL for Prisma
RUN apk add --no-cache openssl

WORKDIR /app/backend

# Copiar package files del backend
COPY backend/package*.json ./
COPY backend/prisma ./prisma

# Instalar dependencias (incluyendo dev para build)
RUN npm install && npm cache clean --force

COPY backend/. .

# Generar Prisma Client
RUN npx prisma generate

# Build TypeScript
RUN npm run build

# ============================================
# STAGE 3: Production Image
# ============================================
FROM node:18-alpine

# Install OpenSSL and Bash for Prisma runtime
RUN apk add --no-cache openssl bash

WORKDIR /app

ENV NODE_ENV=production

# Copiar backend
COPY --from=backend-builder /app/backend/dist ./backend/dist
COPY --from=backend-builder /app/backend/package.json ./backend/package.json
COPY --from=backend-builder /app/backend/prisma ./backend/prisma
COPY --from=backend-builder /app/backend/node_modules/.prisma ./backend/node_modules/.prisma
COPY --from=backend-builder /app/backend/node_modules/@prisma ./backend/node_modules/@prisma
COPY --from=backend-builder /app/backend/start.sh ./backend/start.sh

# Copiar frontend build
COPY --from=frontend-builder /app/frontend/dist ./backend/public

# Instalar backend dependencies (production only)
WORKDIR /app/backend
RUN npm install --omit=dev && npm cache clean --force

# Hacer ejecutable el script de inicio
RUN chmod +x ./start.sh

EXPOSE 3001

HEALTHCHECK --interval=30s --timeout=3s --start-period=40s --retries=3 \
  CMD node -e "require('http').get('http://localhost:3001/health', (r) => {process.exit(r.statusCode === 200 ? 0 : 1)})"

# Ejecutar script de inicio
CMD ["./start.sh"]
