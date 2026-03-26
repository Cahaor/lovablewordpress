# Dockerfile raíz para EasyPanel
FROM node:18-alpine AS builder

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

WORKDIR /app/backend

ENV NODE_ENV=production

# Copiar solo archivos construidos y production deps
COPY --from=builder /app/backend/dist ./dist
COPY --from=builder /app/backend/package.json ./package.json

# Instalar solo production dependencies
RUN npm install --omit=dev && npm cache clean --force

# Copiar Prisma client generado
COPY --from=builder /app/backend/node_modules/.prisma ./node_modules/.prisma
COPY --from=builder /app/backend/prisma ./prisma

EXPOSE 3001

HEALTHCHECK --interval=30s --timeout=3s --start-period=40s --retries=3 \
  CMD node -e "require('http').get('http://localhost:3001/health', (r) => {process.exit(r.statusCode === 200 ? 0 : 1)})"

ENTRYPOINT ["node", "dist/index.js"]
