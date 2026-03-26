# EasyPanel Configuration Guide

## Environment Variables to Configure in EasyPanel

Configure las siguientes variables de entorno en el panel de control de EasyPanel:

### Required Variables

```bash
# Environment
NODE_ENV=production
PORT=3001

# Database - PostgreSQL (Internal URL)
DATABASE_URL="postgresql://Cahaor:3CE479B79C97F2B8C6163ED041F0D7E9@automatizaciones_registrohorario:5432/lovable_wp_pro?schema=public&sslmode=disable"

# JWT Secret (generate a unique one for production)
JWT_SECRET="lovable-wp-pro-super-secret-jwt-key-2024"

# Email Configuration (SMTP)
SMTP_HOST=mail.sensei360.com
SMTP_PORT=465
SMTP_USER=hola@sensei360.com
SMTP_PASS=Malaga*2020Sensei360

# Frontend URL (CORS)
FRONTEND_URL=https://app.lovablewp.pro
```

### Optional Variables

```bash
# WordPress API
WP_API_TIMEOUT=30

# File Upload Limits (in bytes, default: 50MB)
MAX_FILE_SIZE=52428800

# Rate Limiting
RATE_LIMIT_WINDOW=900000
RATE_LIMIT_MAX=100

# Redis (for caching/queues)
REDIS_URL=redis://localhost:6379

# Stripe (for payments)
STRIPE_SECRET_KEY=sk_test_xxx
STRIPE_WEBHOOK_SECRET=whsec_xxx
```

## Deployment Steps

1. **Push to GitHub**: Asegúrate de que el código está subido a GitHub
2. **Connect to EasyPanel**: Conecta el repositorio en EasyPanel
3. **Configure Environment Variables**: Añade todas las variables anteriores en la sección de Environment Variables
4. **Deploy**: EasyPanel construirá y desplegará automáticamente

## Database Setup

The application uses Prisma ORM with PostgreSQL. On first deployment:

1. Prisma will automatically create the required tables
2. The migration runs automatically before the app starts
3. Tables created:
   - User
   - Project
   - Page
   - Component
   - Conversion
   - License
   - WordPressSite
   - Template

## Troubleshooting

### Build Fails
- Check that all dependencies are listed in package.json
- Verify TypeScript configuration (tsconfig.json)
- Ensure OpenSSL is installed for Prisma

### Database Connection Fails
- Verify DATABASE_URL is correct
- Check that the database is accessible from EasyPanel
- Ensure sslmode=disable for internal connections

### App Starts but Returns Errors
- Check application logs in EasyPanel
- Verify all required environment variables are set
- Check CORS settings match your frontend URL
