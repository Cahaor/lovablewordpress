# 🚀 Lovable to WordPress Pro

**Sistema profesional SaaS para convertir diseños de Lovable/Bolt.new a WordPress/Elementor con integración nativa y sync automático.**

![Version](https://img.shields.io/badge/version-1.0.0-blue.svg)
![License](https://img.shields.io/badge/license-GPLv2-green)

---

## 📋 Índice

- [Características](#-características)
- [Arquitectura](#-arquitectura)
- [Instalación](#-instalación)
- [Uso](#-uso)
- [API](#-api)
- [WordPress Plugin](#-wordpress-plugin)
- [Pricing](#-pricing)
- [Roadmap](#-roadmap)

---

## ✨ Características

### 🔄 Conversión Automática
- **React → HTML**: Conversión inteligente de componentes JSX
- **Tailwind → CSS**: 500+ clases mapeadas automáticamente
- **Iconos Lucide**: 200+ iconos convertidos a SVG
- **Animaciones**: Framer Motion → CSS @keyframes
- **Imágenes**: Base64 embed o Media Library

### ☁️ Cloud Sync
- **Automático**: Sync hourly de cambios
- **Multi-site**: Gestiona múltiples WordPress
- **Versiones**: Historial y rollback
- **Colaboración**: Equipos y permisos

### 🎨 Elementor Nativo
- **Widgets**: Registrados automáticamente
- **Controles**: Editables desde Elementor
- **Estilos**: Global colors/fonts
- **Responsive**: Breakpoints configurables

### 🔐 Sistema de Licencias
- **Validación**: License key verification
- **Planes**: Free, Pro, Agency
- **Créditos**: Sistema de conversión mensual
- **White-label**: Opción para agencies

---

## 🏗️ Arquitectura

```
┌─────────────────────────────────────────┐
│         Frontend (React + Vite)         │
│  - Converter UI                         │
│  - Dashboard                            │
│  - Project Manager                      │
└──────────────┬──────────────────────────┘
               │ REST API
┌──────────────▼──────────────────────────┐
│         Backend (Node.js + Express)     │
│  - ZIP Processor                        │
│  - Component Converter                  │
│  - Auth & Licensing                     │
│  - WordPress API Client                 │
└──────────────┬──────────────────────────┘
               │
┌──────────────▼──────────────────────────┐
│         Database (PostgreSQL)           │
│  - Users & Plans                        │
│  - Projects & Pages                     │
│  - Conversions History                  │
│  - Licenses                             │
└──────────────┬──────────────────────────┘
               │ WordPress REST API
┌──────────────▼──────────────────────────┐
│      WordPress Plugin (PHP)             │
│  - Native Widgets                       │
│  - Elementor Integration                │
│  - Cloud Sync                           │
│  - Template Importer                    │
└─────────────────────────────────────────┘
```

---

## 📦 Instalación

### Backend

```bash
cd backend
npm install
cp .env.example .env
# Edit .env with your database credentials
npx prisma migrate dev
npm run dev
```

### Frontend

```bash
cd frontend
npm install
npm run dev
```

### WordPress Plugin

```bash
# Upload to WordPress
cd wp-content/plugins/
# Upload lovable-wp-pro folder
# Activate in WordPress Admin
```

---

## 🚀 Uso

### 1. Registrar cuenta

Ve a `https://lovablewp.pro` y crea tu cuenta.

### 2. Obtener License Key

En tu dashboard, genera una license key.

### 3. Conectar WordPress

En WordPress Admin → Lovable Pro → Settings:
- Introduce tu license key
- Click en "Connect Site"

### 4. Importar diseño

**Opción A: Desde Lovable Cloud**
- Tus diseños aparecen automáticamente
- Click en "Sync Now"

**Opción B: Subir ZIP**
- Exporta desde Lovable (Download ZIP)
- Sube el ZIP en WordPress
- Conversión automática

### 5. Usar en Elementor

- Abre una página con Elementor
- Busca categoría "Lovable Pro"
- Arrastra los widgets
- ¡Listo!

---

## 🔌 API

### Authentication

```bash
POST /api/auth/register
{
  "email": "user@example.com",
  "name": "John Doe",
  "password": "securepassword"
}

POST /api/auth/login
{
  "email": "user@example.com",
  "password": "securepassword"
}
```

### Convert ZIP

```bash
POST /api/converter/convert
Authorization: Bearer {token}
Content-Type: multipart/form-data

file: [ZIP file]
```

### Sync WordPress

```bash
POST /api/wordpress/sync
Authorization: Bearer {token}

{
  "site_url": "https://yoursite.com"
}
```

---

## 🎨 WordPress Plugin

### Widgets Disponibles

| Widget | Descripción | Controles |
|--------|-------------|-----------|
| **Hero** | Hero section | Title, subtitle, CTA, image |
| **Features** | Features grid | Items, icons, colors |
| **CTA** | Call-to-action | Text, button, link |
| **Footer** | Footer section | Links, social, copyright |
| **Navigation** | Navbar | Logo, menu items |
| **Card** | Content card | Image, title, text |

### Hooks

```php
// After widget sync
do_action('lovable_wp_pro/widgets_synced', $widgets);

// Before widget render
apply_filters('lovable_wp_pro/widget_html', $html, $widget);

// Custom styles
apply_filters('lovable_wp_pro/widget_css', $css, $widget);
```

---

## 💳 Pricing

| Plan | Precio | Características |
|------|--------|-----------------|
| **Free** | $0/mes | 3 conversiones/mes, 1 sitio, widgets básicos |
| **Pro** | $29/mes | 50 conversiones/mes, 5 sitios, todos los widgets, priority support |
| **Agency** | $99/mes | Ilimitado, sitios ilimitados, white-label, API access |

---

## 📊 Roadmap

### Q1 2024
- ✅ Backend API
- ✅ WordPress Plugin
- ✅ Elementor Integration
- ⏳ Payment System (Stripe)

### Q2 2024
- ⏳ Multi-page Projects
- ⏳ Visual Editor
- ⏳ Template Library
- ⏳ Team Collaboration

### Q3 2024
- ⏳ AI-powered Conversion
- ⏳ Figma Import
- ⏳ Webflow Export
- ⏳ Analytics Dashboard

---

## 🤝 Contribuir

1. Fork el repositorio
2. Crea una rama (`git checkout -b feature/AmazingFeature`)
3. Commit (`git commit -m 'Add AmazingFeature'`)
4. Push (`git push origin feature/AmazingFeature`)
5. Pull Request

---

## 📄 Licencia

GPL v2 o posterior.

---

## 📞 Soporte

- **Documentación**: https://docs.lovablewp.pro
- **Email**: support@lovablewp.pro
- **Discord**: https://discord.gg/lovablewp

---

**Hecho con ❤️ para la comunidad de WordPress**
