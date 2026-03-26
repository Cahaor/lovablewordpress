# 🚀 Lovable to WordPress Pro

**Sistema profesional para convertir diseños de Lovable/Bolt.new a WordPress/Elementor con integración nativa.**

[![Version](https://img.shields.io/badge/version-1.0.0-blue.svg)](https://github.com/Cahaor/pluginconvertwordpress)
[![License](https://img.shields.io/badge/license-GPLv2-green)](LICENSE)

---

## ✨ Características

- 🔄 **Conversión Automática** - React → HTML, Tailwind → CSS, Iconos SVG
- ☁️ **Cloud Sync** - Sync automático con WordPress
- 🎨 **Elementor Nativo** - Widgets registrados automáticamente
- 🔐 **License System** - Validación de licencias
- 📦 **Multi-site** - Gestiona múltiples WordPress

---

## 🚀 Quick Start

### Docker (Recomendado)

```bash
# Clonar repositorio
git clone https://github.com/Cahaor/pluginconvertwordpress.git
cd pluginconvertwordpress

# Configurar environment
cp backend/.env.example backend/.env
nano backend/.env

# Iniciar servicios
docker-compose up -d

# Migrar database
docker-compose exec backend npx prisma migrate deploy
```

Accede a:
- **Frontend**: http://localhost
- **Backend API**: http://localhost:3001
- **API Health**: http://localhost:3001/health

---

## 📁 Estructura

```
├── backend/                    # Node.js API
│   ├── src/
│   │   ├── routes/
│   │   ├── middleware/
│   │   └── index.ts
│   ├── prisma/
│   │   └── schema.prisma
│   ├── Dockerfile
│   └── package.json
├── modern-market-makers-main/  # React Frontend
│   ├── src/
│   │   └── pages/
│   │       └── LovableToElementor.tsx
│   ├── Dockerfile
│   └── nginx.conf
├── lovable-wp-exporter/        # WordPress Plugin
│   ├── lovable-wp-pro.php
│   ├── includes/
│   │   ├── class-api-client.php
│   │   └── class-widget-registry.php
│   ├── admin/
│   │   ├── class-admin.php
│   │   ├── views/
│   │   ├── css/
│   │   └── js/
│   └── README-PRO.md
├── docker-compose.yml
├── DEPLOYMENT.md
└── README.md
```

---

## 🛠️ Instalación

### Backend

```bash
cd backend
npm install
cp .env.example .env
# Edit .env
npx prisma migrate dev
npm run dev
```

### Frontend

```bash
cd modern-market-makers-main/modern-market-makers-main
npm install
npm run dev
```

### WordPress Plugin

1. Copia `lovable-wp-exporter` a `wp-content/plugins/`
2. Activa en WordPress Admin
3. Configura License Key

---

## 🐳 Docker Deploy

### Variables de Entorno

Crea `backend/.env`:

```env
NODE_ENV=production
PORT=3001
DATABASE_URL="postgresql://user:password@postgres:5432/lovable_wp_pro"
JWT_SECRET="your-secret-key-change-this"
REDIS_URL="redis://redis:6379"
FRONTEND_URL="http://localhost"
```

### Iniciar Servicios

```bash
docker-compose up -d
```

### Ver Logs

```bash
docker-compose logs -f backend
docker-compose logs -f frontend
```

---

## 📖 Documentación

- [DEPLOYMENT.md](DEPLOYMENT.md) - Guía completa de deploy
- [DEPLOYMENT-CHECKLIST.md](DEPLOYMENT-CHECKLIST.md) - Checklist paso a paso
- [lovable-wp-exporter/README-PRO.md](lovable-wp-exporter/README-PRO.md) - WordPress plugin docs

---

## 💳 Pricing Plans

| Plan | Price | Features |
|------|-------|----------|
| **Free** | $0/mo | 3 conversions/mo, 1 site |
| **Pro** | $29/mo | 50 conversions/mo, 5 sites |
| **Agency** | $99/mo | Unlimited, white-label |

---

## 🔧 Tech Stack

- **Frontend**: React 18, Vite, Tailwind CSS, TypeScript
- **Backend**: Node.js 18, Express, Prisma, PostgreSQL
- **WordPress**: PHP 8.0+, Elementor API
- **Infrastructure**: Docker, Redis, Nginx

---

## 🤝 Contributing

1. Fork the repo
2. Create feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit changes (`git commit -m 'Add AmazingFeature'`)
4. Push to branch (`git push origin feature/AmazingFeature`)
5. Open Pull Request

---

## 📄 License

GPL v2 or later. See [LICENSE](LICENSE) for details.

---

## 📞 Support

- **Issues**: https://github.com/Cahaor/pluginconvertwordpress/issues
- **Email**: support@example.com
- **Docs**: https://github.com/Cahaor/pluginconvertwordpress/wiki

---

**Made with ❤️ for the WordPress community**
