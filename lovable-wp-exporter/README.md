# Lovable to WordPress Exporter

**Plugin de WordPress para exportar páginas web creadas en Lovable, Bolt.new o Base44 a WordPress con soporte completo para Elementor y Gutenberg.**

![Version](https://img.shields.io/badge/version-1.0.0-blue.svg)
![WordPress](https://img.shields.io/badge/WordPress-5.0%2B-blue)
![PHP](https://img.shields.io/badge/PHP-7.4%2B-purple)
![License](https://img.shields.io/badge/license-GPLv2-green)

---

## 📋 Índice

- [Características](#-características)
- [Requisitos](#-requisitos)
- [Instalación](#-instalación)
- [Uso](#-uso)
- [Shortcodes Disponibles](#-shortcodes-disponibles)
- [Bloques Gutenberg](#-bloques-gutenberg)
- [Widgets Elementor](#-widgets-elementor)
- [API para Desarrolladores](#-api-para-desarrolladores)
- [Solución de Problemas](#-solución-de-problemas)
- [Contribuir](#-contribuir)
- [Licencia](#-licencia)

---

## ✨ Características

### 🔄 Conversión Automática
- **React → HTML**: Convierte automáticamente componentes JSX a HTML estándar
- **Tailwind → CSS**: Transforma clases de Tailwind a CSS compatible con WordPress
- **Assets**: Importa imágenes y fuentes a la biblioteca de medios

### 🎯 Múltiples Formatos de Exportación
- **Shortcodes**: Usa componentes en cualquier página o entrada
- **Bloques Gutenberg**: Integración nativa con el editor de WordPress
- **Widgets Elementor**: Compatible con Elementor Page Builder

### 🛠️ Características Avanzadas
- Análisis automático de componentes React
- Detección de dependencias (Lucide, shadcn/ui)
- Exportación/importación de configuraciones
- Historial de exportaciones
- Panel de administración intuitivo

---

## 📦 Requisitos

- **WordPress**: 5.0 o superior
- **PHP**: 7.4 o superior
- **Opcional**: Elementor 3.0+ (para widgets de Elementor)

---

## 🚀 Instalación

### Método 1: Subir ZIP

1. Descarga el plugin desde el repositorio
2. Ve a **Plugins → Añadir nuevo → Subir plugin**
3. Selecciona el archivo ZIP del plugin
4. Haz clic en **Instalar ahora** y luego en **Activar**

### Método 2: FTP

1. Sube la carpeta `lovable-wp-exporter` a `/wp-content/plugins/`
2. Ve a **Plugins** en el admin de WordPress
3. Activa el plugin "Lovable to WordPress Exporter"

### Método 3: Composer (para desarrolladores)

```bash
composer require lovable/wp-exporter
```

---

## 📖 Uso

### Paso 1: Preparar tu Código

En tu proyecto de **Lovable**, **Bolt.new** o **Base44**:

1. Abre el archivo del componente que quieres exportar (`.tsx` o `.jsx`)
2. Copia todo el contenido del archivo

### Paso 2: Importar en WordPress

1. Ve a **Lovable Exporter → Nueva Exportación**
2. Selecciona el tipo de proyecto (Lovable, Bolt.new, Base44)
3. Pega el código en el área de texto
4. Haz clic en **"Analizar Código"**

### Paso 3: Configurar Exportación

El sistema mostrará los componentes detectados:

1. Revisa los componentes encontrados
2. Haz clic en **"Convertir Componentes"**
3. Selecciona los formatos de exportación:
   - ✅ Shortcodes
   - ✅ Bloques Gutenberg
   - ✅ Widgets Elementor
4. Configura opciones adicionales
5. Haz clic en **"Exportar"**

### Paso 4: Usar en tu Sitio

#### Usando Shortcodes

```
[lovable_header title="Bienvenido" subtitle="Mi sitio web"]
```

#### Usando Gutenberg

1. Abre el editor de WordPress
2. Busca el bloque **"Lovable: [Nombre del Componente]"**
3. Arrástralo a tu página
4. Configura los atributos en la barra lateral

#### Usando Elementor

1. Abre una página con Elementor
2. Busca la categoría **"Lovable Components"**
3. Arrastra el widget deseado
4. Configura los controles del widget

---

## 🔤 Shortcodes Disponibles

### Shortcode Genérico

```
[lovable_component nombre="valor" otro_valor="texto"]
```

### Shortcode de Sección

```
[lovable_section name="hero" class="custom-class" style="padding: 20px;"]
```

### Shortcode de Página Completa

```
[lovable_page name="landing-page" template="default"]
```

---

## 🧩 Bloques Gutenberg

Los bloques se registran automáticamente con el prefijo `lovable/`:

- `lovable/header`
- `lovable/hero`
- `lovable/card`
- `lovable/button`
- `lovable/navigation`
- `lovable/footer`
- `lovable/form`

**Uso programático:**

```php
// Registrar un bloque manualmente
register_block_type('lovable/header', array(
    'attributes' => array(
        'title' => array('type' => 'string'),
        'subtitle' => array('type' => 'string'),
    ),
    'render_callback' => 'render_lovable_header',
));
```

---

## 🎨 Widgets Elementor

Los widgets se registran en la categoría **"Lovable Components"**.

**Uso programático:**

```php
// En tu functions.php o plugin custom
add_action('elementor/widgets/register', function($widgets_manager) {
    require_once 'path/to/widget.php';
    $widgets_manager->register(new \Lovable_Elementor_Widget_Header());
});
```

---

## 👨‍💻 API para Desarrolladores

### Clase: `Lovable_Analyzer`

Analiza código fuente React/JSX.

```php
$analyzer = new Lovable_Analyzer();
$result = $analyzer->parse($source_code, 'lovable');

// Resultado:
// - components: Array de componentes detectados
// - styles: Clases Tailwind extraídas
// - assets: Imágenes y fuentes detectadas
// - metadata: Información del análisis
```

### Clase: `Lovable_Converter`

Convierte componentes analizados a formatos WordPress.

```php
$converter = new Lovable_Converter();
$converted = $converter->convert($parsed_data, 'all');

// Formatos disponibles:
// - html: HTML estándar
// - php: Shortcodes PHP
// - json: Configuración de bloques Gutenberg
// - elementor: Widgets de Elementor
// - wordpress: Template de WordPress
```

### Clase: `Lovable_Component_Registry`

Registra componentes en WordPress.

```php
$registry = new Lovable_Component_Registry();

// Registrar como shortcode
$registry->register($export_data, 'shortcode');

// Registrar como bloque Gutenberg
$registry->register($export_data, 'block');

// Registrar en ambos formatos
$registry->register($export_data, 'both');
```

### Clase: `Lovable_Asset_Exporter`

Gestiona importación de assets.

```php
$exporter = new Lovable_Asset_Exporter();

// Extraer assets del código
$assets = $exporter->extract_assets($source_code);

// Importar a la biblioteca de medios
$imported = $exporter->import_to_media_library($assets);

// Exportar a ZIP
$zip = $exporter->export_to_zip($assets);
```

### Hooks Disponibles

```php
// Después de exportar un componente
add_action('lovable_exported_component', function($component_name, $component_data) {
    // Tu código aquí
}, 10, 2);

// Antes de convertir Tailwind
add_filter('lovable_tailwind_classes', function($classes, $component) {
    // Modificar clases
    return $classes;
}, 10, 2);

// Personalizar HTML convertido
add_filter('lovable_converted_html', function($html, $component) {
    // Modificar HTML
    return $html;
}, 10, 2);
```

---

## 🔧 Solución de Problemas

### Los componentes no se muestran

1. Verifica que el shortcode esté bien escrito
2. Revisa la consola del navegador para errores JavaScript
3. Asegúrate de que los estilos estén cargando

### Error al analizar el código

1. Verifica que el código sea JSX/TSX válido
2. Asegúrate de copiar el archivo completo
3. Intenta con un componente más simple

### Los estilos Tailwind no funcionan

1. Activa "Convertir Tailwind CSS" en Configuración
2. O enqueue Tailwind manualmente:
   ```php
   add_action('wp_enqueue_scripts', function() {
       wp_enqueue_script('tailwindcss', 'https://cdn.tailwindcss.com', array(), null, false);
   });
   ```

### Elementor no muestra los widgets

1. Asegúrate de que Elementor esté activado
2. Ve a Elementor → Herramientas → Regenerar CSS
3. Limpia la caché del navegador

---

## 🤝 Contribuir

¡Las contribuciones son bienvenidas!

1. Haz un fork del repositorio
2. Crea una rama para tu feature (`git checkout -b feature/AmazingFeature`)
3. Commit tus cambios (`git commit -m 'Add some AmazingFeature'`)
4. Push a la rama (`git push origin feature/AmazingFeature`)
5. Abre un Pull Request

---

## 📄 Licencia

Este plugin está licenciado bajo GPL v2 o posterior.

```
Copyright (C) 2024

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.
```

---

## 📞 Soporte

- **Documentación**: [Ver docs completos](docs/)
- **Issues**: [Reportar bug](https://github.com/yourusername/lovable-wp-exporter/issues)
- **Discusiones**: [GitHub Discussions](https://github.com/yourusername/lovable-wp-exporter/discussions)

---

## 🙏 Agradecimientos

- [Lovable](https://lovable.dev) - Por la increíble plataforma de generación de código
- [Elementor](https://elementor.com) - Por el mejor page builder para WordPress
- [WordPress](https://wordpress.org) - Por el CMS más popular del mundo
- [Tailwind CSS](https://tailwindcss.com) - Por el framework de CSS utilitario

---

**Hecho con ❤️ para la comunidad de WordPress**
