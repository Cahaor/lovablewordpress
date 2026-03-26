#!/bin/sh
set -e

echo "🔄 Starting Prisma schema push..."
echo "📊 Database URL: $DATABASE_URL"

# Try to push schema, if fails, try again with more verbose output
if ! npx prisma db push --force-reset --accept-data-loss; then
  echo "❌ First attempt failed, retrying..."
  sleep 5
  npx prisma db push --force-reset --accept-data-loss --skip-generate
fi

echo "✅ Prisma schema pushed successfully"
echo "🚀 Starting application..."
node dist/index.js
