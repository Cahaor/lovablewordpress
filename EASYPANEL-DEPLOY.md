# 🚀 Guía de Deploy para EasyPanel/VPS

## EasyPanel Deploy (Recomendado)

EasyPanel es un panel de control Docker basado en CloudPanel que facilita el deploy de aplicaciones.

---

## 📋 Prerequisites

- VPS con Ubuntu 20.04+ (2GB RAM, 1 CPU mínimo)
- EasyPanel instalado: https://easypanel.io
- Dominio configurado (opcional)
- Git instalado localmente

---

## 🔧 Paso 1: Subir a GitHub

```bash
# Inicializar repositorio
cd "c:\Users\chaza\OneDrive - Carlos Hazañas Ortiz\360VM\Web\modern-market-makers-main"
git init
git add .
git commit -m "Initial commit - Lovable WP Pro v1.0"
git branch -M main
git remote add origin https://github.com/Cahaor/pluginconvertwordpress.git
git push -u origin main
```

---

## 🐳 Paso 2: Deploy en EasyPanel

### 2.1 Crear Proyecto en EasyPanel

1. Login a EasyPanel
2. Click en **"New Project"**
3. Nombre: `lovable-wp-pro`
4. Click en **"Create"**

### 2.2 Crear Servicio Backend

1. Click en **"New Service"**
2. Selecciona **"Git Repository"**
3. URL: `https://github.com/Cahaor/pluginconvertwordpress.git`
4. Branch: `main`
5. Root directory: `backend`
6. Dockerfile: `Dockerfile.easypanel`

### 2.3 Configurar Variables de Entorno

En EasyPanel → Settings → Environment Variables:

```env
NODE_ENV=production
PORT=3001
DATABASE_URL=postgresql://user:pass@host:5432/lovable_wp_pro
JWT_SECRET=tu-secreto-seguro-aqui-generado-aleatorio
REDIS_URL=redis://redis:6379
FRONTEND_URL=https://tu-dominio.com
```

**Generar JWT_SECRET:**
```bash
# En tu terminal local
openssl rand -hex 32
# Copia el resultado a JWT_SECRET
```

### 2.4 Crear Base de Datos PostgreSQL

1. En EasyPanel, click en **"New Service"**
2. Selecciona **"PostgreSQL"**
3. Nombre: `lovable-db`
4. Click en **"Create"**
5. Copia el **DATABASE_URL** que genera EasyPanel
6. Pégalo en las variables de entorno del backend

### 2.5 Crear Servicio Redis (Opcional pero recomendado)

1. Click en **"New Service"**
2. Selecciona **"Redis"**
3. Nombre: `lovable-redis`
4. Click en **"Create"**
5. Copia el **REDIS_URL**
6. Pégalo en las variables de entorno del backend

### 2.6 Crear Servicio Frontend

1. Click en **"New Service"**
2. Git Repository: `https://github.com/Cahaor/pluginconvertwordpress.git`
3. Root directory: `modern-market-makers-main/modern-market-makers-main`
4. Build command: `npm run build`
5. Publish directory: `dist`
6. Port: `80`

### 2.7 Configurar Dominio

1. En cada servicio, ve a **"Domains"**
2. Click en **"Add Domain"**
3. Backend: `api.tudominio.com`
4. Frontend: `app.tudominio.com`
5. EasyPanel genera SSL automáticamente

---

## 🔄 Paso 3: Migrar Database

Después de deployar el backend:

### Desde EasyPanel Console:

1. Ve a Backend → Console
2. Ejecuta:
```bash
npx prisma migrate deploy
npx prisma db seed
```

### O desde tu terminal local:

```bash
# Conectar al contenedor
docker exec -it backend-container npx prisma migrate deploy
```

---

## 📊 Paso 4: Verificar Deploy

### Health Check

```bash
curl https://api.tudominio.com/health
```

Deberías ver:
```json
{
  "status": "ok",
  "timestamp": "2024-03-26T..."
}
```

### Probar Frontend

Abre en navegador: `https://app.tudominio.com`

---

## 🔐 Paso 5: Configurar WordPress Plugin

### 5.1 Instalar Plugin

1. Descarga el plugin desde GitHub:
   - Ve a: https://github.com/Cahaor/pluginconvertwordpress
   - Download ZIP de `lovable-wp-exporter`

2. En WordPress Admin:
   - Plugins → Add New → Upload Plugin
   - Sube el ZIP
   - Activa

### 5.2 Conectar con API

En WordPress → Lovable Pro → Settings:

- **API URL**: `https://api.tudominio.com`
- **License Key**: (genera una en tu sistema de licensing)

---

## ⚙️ Configuración Avanzada

### Auto-Deploy desde GitHub

EasyPanel configura webhooks automáticamente:

1. Cada push a `main` → Auto deploy
2. Puedes desactivar en Settings → Auto Deploy

### Escalar Servicios

1. Ve a Servicio → Settings
2. **Replicas**: Aumenta para más tráfico
3. **Resources**: Ajusta CPU/RAM

### Backups Automáticos

EasyPanel incluye backups:

1. Project → Settings → Backups
2. Enable automatic backups
3. Frequency: Daily
4. Retention: 7 days

---

## 🔧 Troubleshooting

### Backend no inicia

```bash
# Ver logs en EasyPanel
Backend → Logs

# Errores comunes:
# - DATABASE_URL incorrecto
# - Puerto ya en uso
# - Variables de entorno faltantes
```

### Database connection error

```bash
# Reiniciar PostgreSQL
EasyPanel → lovable-db → Restart

# Verificar conexión
EasyPanel → Backend → Console
npx prisma db pull
```

### Frontend no conecta al backend

```bash
# Verificar FRONTEND_URL en backend/.env
# Debe ser: https://app.tudominio.com

# Verificar CORS en backend
# Asegúrate que frontend está en allowed origins
```

---

## 📈 Monitoreo

### EasyPanel Dashboard

- CPU usage
- Memory usage
- Network traffic
- Disk usage

### Configurar Alertas

1. Project → Settings → Alerts
2. Email notifications
3. Thresholds:
   - CPU > 80%
   - Memory > 85%
   - Disk > 90%

---

## 💰 Costos Estimados (VPS)

| Provider | Plan | RAM | CPU | Storage | Price/mo |
|----------|------|-----|-----|---------|----------|
| **DigitalOcean** | Basic | 2GB | 1 | 50GB | $12 |
| **Linode** | Nanode | 1GB | 1 | 25GB | $5 |
| **Vultr** | Cloud | 2GB | 1 | 55GB | $12 |
| **Hetzner** | CPX11 | 2GB | 2 | 40GB | €5 |

**EasyPanel**: Gratis (self-hosted) o $12/mo (managed)

---

## 🔐 Seguridad

### Firewall

EasyPanel configura automáticamente:
- Puerto 80 (HTTP)
- Puerto 443 (HTTPS)
- Puerto 22 (SSH)

### SSL/TLS

- SSL automático con Let's Encrypt
- Renovación automática
- A+ rating en SSL Labs

### Backups

```bash
# Backup manual de database
EasyPanel → lovable-db → Backup → Create

# Restaurar
EasyPanel → lovable-db → Backup → Restore
```

---

## 📝 Comandos Útiles

### Ver logs
```bash
# EasyPanel UI
Servicio → Logs
```

### Reiniciar servicios
```bash
# EasyPanel UI
Servicio → Restart
```

### Acceder a consola
```bash
# EasyPanel UI
Servicio → Console
```

### Ejecutar migraciones
```bash
npx prisma migrate deploy
npx prisma db seed
```

### Ver database
```bash
npx prisma studio
```

---

## ✅ Checklist Post-Deploy

- [ ] Backend health check OK
- [ ] Frontend carga correctamente
- [ ] Database migrada
- [ ] SSL activo en ambos dominios
- [ ] WordPress plugin instalado
- [ ] API URL configurada en plugin
- [ ] License key válida
- [ ] Backups automáticos activos
- [ ] Monitoreo configurado
- [ ] Emails transaccionales probados

---

## 🆘 Soporte

Si tienes problemas:

1. Revisa logs en EasyPanel
2. Verifica variables de entorno
3. Checa health check del backend
4. Revisa conexión a database

**GitHub Issues**: https://github.com/Cahaor/pluginconvertwordpress/issues

---

**¡Deploy completado! 🎉**

Tu aplicación está corriendo en:
- Frontend: https://app.tudominio.com
- Backend API: https://api.tudominio.com
- WordPress: Tu plugin conectado
