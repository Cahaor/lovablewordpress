# Guía Rápida de Uso - Lovable to WordPress Exporter

## 🚀 Inicio Rápido (5 minutos)

### Paso 1: Instalar el Plugin

1. Descarga la carpeta `lovable-wp-exporter`
2. Súbelala a `/wp-content/plugins/` de tu WordPress
3. Activa el plugin desde **Plugins**

### Paso 2: Exportar desde Lovable

1. En tu proyecto de Lovable, abre un componente (ej: `Hero.tsx`)
2. Copia TODO el contenido del archivo
3. Ve a tu WordPress → **Lovable Exporter → Nueva Exportación**
4. Pega el código y haz clic en **"Analizar Código"**

### Paso 3: Convertir

1. Revisa los componentes detectados
2. Haz clic en **"Convertir Componentes"**
3. Selecciona:
   - ✅ Shortcodes (recomendado)
   - ✅ Bloques Gutenberg (opcional)
   - ✅ Widgets Elementor (si usas Elementor)
4. Haz clic en **"Exportar"**

### Paso 4: Usar en tu Web

**Opción A - Shortcode:**
```
[lovable_hero title="Bienvenido" subtitle="Mi sitio web"]
```

**Opción B - Gutenberg:**
- Busca el bloque "Lovable: Hero"

**Opción C - Elementor:**
- Busca el widget "Hero" en "Lovable Components"

---

## 📋 Shortcodes Disponibles

### Componente Individual
```
[lovable_nombre_componente atributo1="valor1" atributo2="valor2"]
```

### Sección Completa
```
[lovable_section name="hero" class="mi-clase" style="padding: 40px;"]
```

### Página Completa
```
[lovable_page name="landing-page"]
```

---

## 🎨 Ejemplos Prácticos

### Ejemplo 1: Hero Section

**Código original en Lovable:**
```tsx
export const Hero = ({ title, subtitle, ctaText }) => {
  return (
    <section className="bg-blue-500 text-white p-8">
      <div className="container">
        <h1 className="text-4xl font-bold">{title}</h1>
        <p className="text-xl mt-4">{subtitle}</p>
        <button className="btn btn-primary mt-6">{ctaText}</button>
      </div>
    </section>
  );
};
```

**Uso en WordPress:**
```
[lovable_hero title="Bienvenido a Mi Sitio" subtitle="Creamos cosas increíbles" ctaText="Empezar"]
```

### Ejemplo 2: Card Component

**Código original:**
```tsx
export const Card = ({ title, description, image }) => {
  return (
    <div className="card shadow-lg rounded-lg overflow-hidden">
      <img src={image} alt={title} className="w-full" />
      <div className="p-6">
        <h3 className="text-xl font-bold">{title}</h3>
        <p className="text-gray-600 mt-2">{description}</p>
      </div>
    </div>
  );
};
```

**Uso en WordPress:**
```
[lovable_card title="Producto 1" description="Descripción del producto" image="/images/producto.jpg"]
```

### Ejemplo 3: Navigation

**Código original:**
```tsx
export const Navigation = ({ logo, items }) => {
  return (
    <nav className="flex justify-between items-center p-4 bg-white shadow">
      <img src={logo} alt="Logo" className="h-10" />
      <ul className="flex gap-4">
        {items.map(item => (
          <li key={item.id}><a href={item.url}>{item.label}</a></li>
        ))}
      </ul>
    </nav>
  );
};
```

**Uso en WordPress:**
```
[lovable_navigation logo="/logo.png" items='[{"id":1,"url":"/","label":"Inicio"}]']
```

---

## 🔧 Configuración Recomendada

### Para la mayoría de usuarios:

✅ **Auto Cargar Estilos** - Para que los estilos funcionen automáticamente
✅ **Convertir Tailwind CSS** - Convierte las clases a CSS estándar
❌ **Preservar Estado React** - Solo si necesitas interactividad avanzada
✅ **Integración con Elementor** - Si usas Elementor
✅ **Integración con Gutenberg** - Para usar el editor de bloques

---

## 🐛 Problemas Comunes

### "El shortcode no funciona"
- Verifica que escribiste bien el shortcode
- Asegúrate de que el componente se exportó correctamente
- Revisa en **Lovable Exporter → Historial**

### "Los estilos no se ven"
- Activa "Auto Cargar Estilos" en Configuración
- O añade esto a tu `functions.php`:
  ```php
  add_action('wp_enqueue_scripts', function() {
      wp_enqueue_style('tailwindcss', 'https://cdn.tailwindcss.com');
  });
  ```

### "Elementor no muestra los widgets"
- Asegúrate de que Elementor está instalado y activo
- Ve a Elementor → Herramientas → Regenerar CSS
- Limpia la caché del navegador

---

## 💡 Trucos Pro

### 1. Exportar Múltiples Componentes

Copia el archivo principal que importa todos los componentes:
```tsx
import { Hero } from '@/components/Hero';
import { Card } from '@/components/Card';
import { Footer } from '@/components/Footer';
```

El plugin detectará automáticamente todas las dependencias.

### 2. Personalizar Estilos

Los shortcodes aceptan el atributo `class`:
```
[lovable_hero title="Hola" class="mi-clase-personalizada"]
```

Luego en tu CSS:
```css
.mi-clase-personalized h1 {
    color: purple;
}
```

### 3. Usar en Templates

Crea un template de página (`page-landing.php`):
```php
<?php
/* Template Name: Landing Page */
get_header();
?>

<?php echo do_shortcode('[lovable_hero title="Landing"]'); ?>
<?php echo do_shortcode('[lovable_features]'); ?>
<?php echo do_shortcode('[lovable_cta]'); ?>

<?php get_footer(); ?>
```

### 4. Exportar/Importar entre Sites

1. Ve a **Configuración → Herramientas**
2. Haz clic en **"Exportar Todo"**
3. Importa el JSON en otro WordPress

---

## 📞 Soporte

¿Necesitas ayuda?

1. Revisa la documentación completa en `README.md`
2. Busca tu problema en la sección "Solución de Problemas"
3. Abre un issue en GitHub

---

## 🎯 Próximos Pasos

1. **Explora todos los componentes** exportados en tu historial
2. **Personaliza los estilos** desde el Personalizador de WordPress
3. **Combina componentes** para crear páginas únicas
4. **Experimenta** con diferentes configuraciones

---

**¡Disfruta creando webs increíbles con Lovable + WordPress! 🚀**
