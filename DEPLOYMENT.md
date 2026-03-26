# 🚀 Deployment Guide - Lovable WP Pro

## Prerequisites

- Docker & Docker Compose installed
- Domain name configured (optional)
- SSL certificate (Let's Encrypt recommended)
- Database backup strategy

---

## Quick Deploy (Docker)

### 1. Clone & Configure

```bash
cd modern-market-makers-main

# Copy environment files
cp backend/.env.example backend/.env
cp .env.example .env

# Edit environment variables
nano backend/.env
# - Set DATABASE_URL
# - Set JWT_SECRET (openssl rand -hex 32)
# - Set FRONTEND_URL
```

### 2. Start Services

```bash
# Build and start all services
docker-compose up -d

# Check status
docker-compose ps

# View logs
docker-compose logs -f backend
```

### 3. Run Database Migrations

```bash
docker-compose exec backend npx prisma migrate deploy
docker-compose exec backend npx prisma db seed
```

### 4. Verify Deployment

```bash
# Backend health check
curl http://localhost:3001/health

# Frontend
curl http://localhost
```

---

## Production Deploy (VPS)

### Server Requirements

- **CPU**: 2 cores minimum
- **RAM**: 4GB minimum
- **Storage**: 20GB SSD
- **OS**: Ubuntu 22.04 LTS

### 1. Server Setup

```bash
# Update system
sudo apt update && sudo apt upgrade -y

# Install Docker
curl -fsSL https://get.docker.com -o get-docker.sh
sudo sh get-docker.sh

# Install Docker Compose
sudo curl -L "https://github.com/docker/compose/releases/latest/download/docker-compose-$(uname -s)-$(uname -m)" -o /usr/local/bin/docker-compose
sudo chmod +x /usr/local/bin/docker-compose

# Add user to docker group
sudo usermod -aG docker $USER
```

### 2. Clone Repository

```bash
git clone https://github.com/yourusername/lovable-wp-pro.git
cd lovable-wp-pro

# Configure environment
cp backend/.env.example backend/.env
nano backend/.env
```

### 3. Setup SSL (Let's Encrypt)

```bash
# Install Certbot
sudo apt install certbot python3-certbot-nginx -y

# Get certificate
sudo certbot --nginx -d api.lovablewp.pro -d app.lovablewp.pro

# Auto-renewal
sudo certbot renew --dry-run
```

### 4. Deploy with Docker Compose

```bash
# Start services
docker-compose up -d

# Check logs
docker-compose logs -f
```

### 5. Setup Nginx Reverse Proxy

```nginx
# /etc/nginx/sites-available/lovable-wp-pro
server {
    listen 80;
    server_name api.lovablewp.pro;
    
    location / {
        proxy_pass http://localhost:3001;
        proxy_http_version 1.1;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection 'upgrade';
        proxy_set_header Host $host;
        proxy_cache_bypass $http_upgrade;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
    }
}
```

```bash
# Enable site
sudo ln -s /etc/nginx/sites-available/lovable-wp-pro /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

---

## Deploy Frontend (Vercel/Netlify)

### Vercel

```bash
# Install Vercel CLI
npm i -g vercel

# Deploy
cd modern-market-makers-main/modern-market-makers-main
vercel --prod
```

**Environment Variables in Vercel:**
- `VITE_API_URL`: https://api.lovablewp.pro

### Netlify

```bash
# Build command
npm run build

# Publish directory
dist

# Environment variables
VITE_API_URL=https://api.lovablewp.pro
```

---

## Deploy WordPress Plugin

### Option 1: WordPress.org

1. Prepare plugin according to WordPress.org standards
2. Submit to WordPress plugin repository
3. Users can install from WordPress Admin

### Option 2: Self-hosted

```bash
# Create ZIP package
cd lovable-wp-exporter
zip -r lovable-wp-pro.zip . -x "*.git*" "*.env*" "node_modules/*"

# Upload to WordPress site
# WP Admin → Plugins → Add New → Upload Plugin
```

### Option 3: Composer (Premium)

```json
// In WordPress site composer.json
{
  "repositories": [
    {
      "type": "composer",
      "url": "https://composer.lovablewp.pro"
    }
  ],
  "require": {
    "lovable/wp-pro": "^1.0"
  }
}
```

---

## Database Backup

```bash
# Backup
docker-compose exec postgres pg_dump -U lovable_user lovable_wp_pro > backup_$(date +%Y%m%d).sql

# Restore
docker-compose exec -T postgres psql -U lovable_user lovable_wp_pro < backup_20240101.sql
```

---

## Monitoring

### Setup PM2 (Alternative to Docker)

```bash
# Install PM2
npm i -g pm2

# Start backend
cd backend
pm2 start dist/index.js --name lovable-backend

# Setup startup
pm2 startup
pm2 save
```

### Health Checks

```bash
# Backend
curl http://localhost:3001/health

# Database
docker-compose exec postgres pg_isready

# Redis
docker-compose exec redis redis-cli ping
```

---

## Environment Variables Checklist

### Backend (.env)

- [ ] `NODE_ENV=production`
- [ ] `DATABASE_URL` (PostgreSQL connection string)
- [ ] `JWT_SECRET` (random 64 char string)
- [ ] `FRONTEND_URL` (your frontend domain)
- [ ] `SMTP_*` (email configuration)
- [ ] `STRIPE_*` (payment processing)
- [ ] `REDIS_URL` (cache/queues)

### Frontend (.env)

- [ ] `VITE_API_URL` (backend API URL)
- [ ] `VITE_STRIPE_PUBLIC_KEY` (if using payments)

---

## Troubleshooting

### Backend won't start

```bash
# Check logs
docker-compose logs backend

# Common issues:
# - Database not ready: wait for postgres healthcheck
# - Port in use: change PORT in .env
# - Missing env vars: check backend/.env
```

### Database connection error

```bash
# Restart postgres
docker-compose restart postgres

# Check connection
docker-compose exec postgres psql -U lovable_user -d lovable_wp_pro
```

### Frontend can't connect to backend

```bash
# Check CORS settings in backend/.env
FRONTEND_URL=http://your-frontend-domain.com

# Check API URL in frontend
VITE_API_URL=http://your-backend-domain.com
```

---

## Post-Deploy Checklist

- [ ] SSL certificate installed
- [ ] Database backups configured (cron job)
- [ ] Monitoring setup (UptimeRobot, etc.)
- [ ] Error tracking (Sentry)
- [ ] Analytics configured
- [ ] Email notifications tested
- [ ] Payment flow tested
- [ ] WordPress plugin sync tested
- [ ] Rate limiting configured
- [ ] CDN configured (Cloudflare)

---

## Support

- **Issues**: https://github.com/yourusername/lovable-wp-pro/issues
- **Docs**: https://docs.lovablewp.pro
- **Email**: support@lovablewp.pro

---

**Ready for production! 🚀**
