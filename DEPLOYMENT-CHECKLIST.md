# Deployment Checklist - Lovable WP Pro

## Pre-Deployment

### Code Review
- [ ] All tests passing (`npm test`)
- [ ] Linter clean (`npm run lint`)
- [ ] No console.log in production code
- [ ] Environment variables documented
- [ ] README updated

### Security
- [ ] JWT_SECRET is strong (64+ chars)
- [ ] Database credentials secured
- [ ] API keys in environment variables
- [ ] CORS configured correctly
- [ ] Rate limiting enabled
- [ ] SQL injection prevention (Prisma ORM)
- [ ] XSS prevention (React escaping)

### Database
- [ ] PostgreSQL installed
- [ ] Database created
- [ ] Migrations run (`npx prisma migrate deploy`)
- [ ] Backup strategy configured
- [ ] Connection pooling configured

### Infrastructure
- [ ] VPS provisioned (4GB RAM, 2 CPU, 20GB SSD)
- [ ] Docker & Docker Compose installed
- [ ] Domain DNS configured
- [ ] SSL certificate (Let's Encrypt)
- [ ] Firewall rules (UFW)
- [ ] Monitoring setup (UptimeRobot)

---

## Deployment Steps

### 1. Environment Setup

```bash
# SSH to server
ssh user@your-server.com

# Update system
sudo apt update && sudo apt upgrade -y

# Install Docker
curl -fsSL https://get.docker.com -o get-docker.sh
sudo sh get-docker.sh

# Install Docker Compose
sudo curl -L "https://github.com/docker/compose/releases/latest/download/docker-compose-$(uname -s)-$(uname -m)" -o /usr/local/bin/docker-compose
sudo chmod +x /usr/local/bin/docker-compose
```

### 2. Clone & Configure

```bash
# Clone repository
git clone https://github.com/yourusername/lovable-wp-pro.git /opt/lovable-wp-pro
cd /opt/lovable-wp-pro

# Copy environment files
cp backend/.env.example backend/.env
nano backend/.env

# Generate JWT secret
openssl rand -hex 32
# Copy to backend/.env
```

### 3. Start Services

```bash
# Build and start
docker-compose up -d

# Check status
docker-compose ps

# View logs
docker-compose logs -f
```

### 4. Database Migration

```bash
docker-compose exec backend npx prisma migrate deploy
docker-compose exec backend npx prisma db seed
```

### 5. SSL Setup

```bash
# Install Certbot
sudo apt install certbot python3-certbot-nginx -y

# Get certificate
sudo certbot --nginx -d api.lovablewp.pro -d app.lovablewp.pro

# Test auto-renewal
sudo certbot renew --dry-run
```

---

## Post-Deployment

### Verify Services

```bash
# Backend health
curl https://api.lovablewp.pro/health

# Frontend
curl https://app.lovablewp.pro

# Database connection
docker-compose exec postgres pg_isready

# Redis connection
docker-compose exec redis redis-cli ping
```

### Setup Monitoring

```bash
# Install PM2 (optional, for process management)
sudo npm i -g pm2

# Start backend with PM2
cd /opt/lovable-wp-pro/backend
pm2 start dist/index.js --name lovable-backend
pm2 startup
pm2 save

# Setup log rotation
pm2 install pm2-logrotate
```

### Configure Backups

```bash
# Create backup script
nano /opt/backup-lovable.sh

#!/bin/bash
DATE=$(date +%Y%m%d_%H%M%S)
docker-compose exec -T postgres pg_dump -U lovable_user lovable_wp_pro > /opt/backups/backup_$DATE.sql
find /opt/backups -name "*.sql" -mtime +7 -delete

# Make executable
chmod +x /opt/backup-lovable.sh

# Add to crontab (daily at 3 AM)
crontab -e
0 3 * * * /opt/backup-lovable.sh
```

### Setup Error Tracking

1. Create Sentry account: https://sentry.io
2. Create new project
3. Add DSN to backend/.env:
   ```
   SENTRY_DSN=https://xxx@xxx.ingest.sentry.io/xxx
   ```

### Configure Email

1. Setup SMTP (Gmail, SendGrid, etc.)
2. Add to backend/.env:
   ```
   SMTP_HOST=smtp.sendgrid.net
   SMTP_PORT=587
   SMTP_USER=apikey
   SMTP_PASS=your-sendgrid-api-key
   ```

---

## WordPress Plugin Deployment

### Option 1: Manual Upload

1. Create ZIP:
   ```bash
   cd lovable-wp-exporter
   zip -r lovable-wp-pro.zip . -x "*.git*" "node_modules/*"
   ```

2. Upload to WordPress:
   - WP Admin → Plugins → Add New → Upload Plugin
   - Select `lovable-wp-pro.zip`
   - Activate

### Option 2: FTP/SFTP

```bash
# Using lftp
lftp -u username,password sftp://your-wordpress.com

# Upload plugin
put -r lovable-wp-exporter /wp-content/plugins/lovable-wp-pro
```

### Option 3: WP-CLI

```bash
# SSH to WordPress server
wp plugin install ./lovable-wp-pro.zip --activate
```

---

## Testing Checklist

### Backend API
- [ ] POST /api/auth/register - User registration
- [ ] POST /api/auth/login - User login
- [ ] POST /api/converter/convert - ZIP conversion
- [ ] GET /api/projects - List projects
- [ ] POST /api/wordpress/sync - WordPress sync

### Frontend
- [ ] User registration flow
- [ ] User login flow
- [ ] ZIP upload and conversion
- [ ] Project dashboard
- [ ] Settings page

### WordPress Plugin
- [ ] Plugin activation
- [ ] License validation
- [ ] Widget sync
- [ ] Widgets appear in Elementor
- [ ] Widgets render correctly

### Payment Flow (if enabled)
- [ ] Stripe checkout
- [ ] Webhook processing
- [ ] Plan upgrade/downgrade
- [ ] Cancellation

---

## Performance Optimization

### Backend
- [ ] Enable Redis caching
- [ ] Configure connection pooling
- [ ] Enable gzip compression
- [ ] Setup CDN for static assets

### Frontend
- [ ] Enable code splitting
- [ ] Configure lazy loading
- [ ] Optimize images
- [ ] Enable service worker (PWA)

### Database
- [ ] Add indexes on frequently queried columns
- [ ] Configure query logging
- [ ] Setup read replicas (if needed)

---

## Security Hardening

### Server
```bash
# Setup firewall
sudo ufw allow 22/tcp
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp
sudo ufw enable

# Disable root login
sudo nano /etc/ssh/sshd_config
# PermitRootLogin no

# Setup fail2ban
sudo apt install fail2ban -y
sudo systemctl enable fail2ban
```

### Docker
- [ ] Run containers as non-root user
- [ ] Scan images for vulnerabilities
- [ ] Limit container resources
- [ ] Use secrets for sensitive data

### Application
- [ ] Enable HTTPS only
- [ ] Set secure cookie flags
- [ ] Implement rate limiting
- [ ] Add CSRF protection

---

## Rollback Plan

### If deployment fails:

```bash
# Stop services
docker-compose down

# Checkout previous version
git checkout <previous-commit>

# Restart services
docker-compose up -d

# Restore database (if needed)
docker-compose exec -T postgres psql -U lovable_user lovable_wp_pro < /opt/backups/backup_20240101.sql
```

---

## Support Contacts

- **DevOps**: your-devops@email.com
- **Backend Lead**: your-backend@email.com
- **Frontend Lead**: your-frontend@email.com

---

## Emergency Procedures

### Database Down
1. Check logs: `docker-compose logs postgres`
2. Restart: `docker-compose restart postgres`
3. Restore from backup if needed

### Backend Crashes
1. Check logs: `docker-compose logs backend`
2. Check resources: `docker stats`
3. Restart: `docker-compose restart backend`

### High Traffic
1. Scale backend: `docker-compose up -d --scale backend=3`
2. Enable CDN
3. Increase rate limits temporarily

---

**Deployment Complete! 🚀**

Monitor for 24 hours and check:
- Error rates (Sentry)
- Response times
- User registrations
- Conversion success rate
