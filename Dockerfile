# Dockerfile raíz para EasyPanel
# Este Dockerfile redirige al backend

FROM node:18-alpine AS builder

WORKDIR /app

# Copiar backend
COPY backend/package*.json ./backend/
WORKDIR /app/backend
RUN npm ci --only=production && npm cache clean --force

COPY backend/. .

# Generar Prisma Client
RUN npx prisma generate

# Build TypeScript
RUN npm run build

# Production image
FROM node:18-alpine

WORKDIR /app/backend

ENV NODE_ENV=production

# Copiar archivos construidos
COPY --from=builder /app/backend/dist ./dist
COPY --from=builder /app/backend/node_modules ./node_modules
COPY --from=builder /app/backend/prisma ./prisma

EXPOSE 3001

HEALTHCHECK --interval=30s --timeout=3s --start-period=40s --retries=3 \
  CMD node -e "require('http').get('http://localhost:3001/health', (r) => {process.exit(r.statusCode === 200 ? 0 : 1)})"

ENTRYPOINT ["node", "dist/index.js"]
