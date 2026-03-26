#!/bin/sh
set -e

echo "🔄 Starting Prisma schema push..."
npx prisma db push --force-reset --accept-data-loss

echo "✅ Prisma schema pushed successfully"
echo "🚀 Starting application..."
node dist/index.js
