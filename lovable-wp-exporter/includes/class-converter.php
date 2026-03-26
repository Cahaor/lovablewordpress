<?php
/**
 * Converter Class
 * 
 * Convierte componentes React analizados a formatos compatibles con WordPress
 */

if (!defined('ABSPATH')) {
    exit;
}

class Lovable_Converter {
    
    /**
     * Main conversion method
     */
    public function convert($parsed_data, $output_format = 'all') {
        $result = array(
            'html' => array(),
            'css' => array(),
            'php' => array(),
            'json' => array(),
            'wordpress' => array(),
            'elementor' => array(),
        );
        
        $components = isset($parsed_data['components']) ? $parsed_data['components'] : array();
        $styles = isset($parsed_data['styles']) ? $parsed_data['styles'] : array();
        
        foreach ($components as $component_name => $component_data) {
            // Convert to HTML
            $result['html'][$component_name] = $this->convert_to_html($component_data);
            
            // Convert to PHP shortcode
            $result['php'][$component_name] = $this->convert_to_php_shortcode($component_data);
            
            // Generate WordPress block JSON
            $result['json'][$component_name] = $this->convert_to_block_json($component_data);
            
            // Generate Elementor widget config
            $result['elementor'][$component_name] = $this->convert_to_elementor_widget($component_data);
        }
        
        // Convert Tailwind to WordPress-compatible CSS
        $result['css']['converted'] = $this->convert_tailwind_to_css($styles);
        
        // Generate WordPress template
        $result['wordpress']['template'] = $this->generate_wordpress_template($parsed_data);
        
        return $result;
    }
    
    /**
     * Convert React component to HTML
     */
    private function convert_to_html($component_data) {
        $body = $component_data['body'];
        $html = $body;
        
        // Remove JSX-specific syntax
        $html = $this->clean_jsx_syntax($html);
        
        // Convert className to class
        $html = preg_replace('/className\s*=/i', 'class=', $html);
        
        // Convert htmlFor to for
        $html = preg_replace('/htmlFor\s*=/i', 'for=', $html);
        
        // Remove event handlers
        $html = preg_replace('/\s*onClick\s*=\s*{[^}]*}/i', '', $html);
        $html = preg_replace('/\s*onChange\s*=\s*{[^}]*}/i', '', $html);
        $html = preg_replace('/\s*onSubmit\s*=\s*{[^}]*}/i', '', $html);
        $html = preg_replace('/\s*onMouseEnter\s*=\s*{[^}]*}/i', '', $html);
        $html = preg_replace('/\s*onMouseLeave\s*=\s*{[^}]*}/i', '', $html);
        
        // Remove JSX expressions
        $html = preg_replace('/{[^}]*}/', '', $html);
        
        // Self-close void elements
        $void_elements = array('img', 'input', 'br', 'hr', 'meta', 'link');
        foreach ($void_elements as $tag) {
            $html = preg_replace("/<{$tag}([^>]*)(?<!\/)>/i", "<{$tag}\\1 />", $html);
        }
        
        return $html;
    }
    
    /**
     * Clean JSX-specific syntax
     */
    private function clean_jsx_syntax($html) {
        // Remove React fragments
        $html = preg_replace('/<>\s*/', '', $html);
        $html = preg_replace('/\s*<\//?>/', '', $html);
        
        // Remove conditional rendering
        $html = preg_replace('/{\s*[^}]*\?\s*[^}]*:\s*[^}]*}/s', '', $html);
        
        // Remove map/forEach iterations
        $html = preg_replace('/{\s*[^}]*\.map\s*\([^)]*\)\s*}/s', '', $html);
        $html = preg_replace('/{\s*[^}]*\.forEach\s*\([^)]*\)\s*}/s', '', $html);
        
        // Remove spread operators
        $html = preg_replace('/{\s*\.\.\.[^}]*}/', '', $html);
        
        // Remove ternary operators
        $html = preg_replace('/{\s*[^}]*\?[^}]*:[^}]*}/s', '', $html);
        
        return $html;
    }
    
    /**
     * Convert to PHP shortcode
     */
    private function convert_to_php_shortcode($component_data) {
        $component_name = $component_data['name'];
        $component_type = $component_data['type'];
        $html = $this->convert_to_html($component_data);
        $props = $component_data['props'];
        
        $php_code = "<?php\n";
        $php_code .= "/**\n";
        $php_code .= " * Shortcode for {$component_name} component\n";
        $php_code .= " * Converted from React (Lovable/Bolt.new)\n";
        $php_code .= " */\n\n";
        
        // Register shortcode
        $php_code .= "add_shortcode('lovable_{$component_name}', 'render_lovable_{$component_name}');\n\n";
        
        // Render function
        $php_code .= "function render_lovable_{$component_name}(\$atts = array()) {\n";
        
        // Default attributes
        if (!empty($props)) {
            $php_code .= "    \$defaults = array(\n";
            foreach ($props as $prop_name => $prop_data) {
                $default_value = $prop_data['default'] ? $prop_data['default'] : "''";
                $php_code .= "        '{$prop_name}' => {$default_value},\n";
            }
            $php_code .= "    );\n";
            $php_code .= "    \$atts = shortcode_atts(\$defaults, \$atts, 'lovable_{$component_name}');\n\n";
        }
        
        // Extract variables
        if (!empty($props)) {
            foreach ($props as $prop_name => $prop_data) {
                $php_code .= "    \${$prop_name} = \$atts['{$prop_name}'];\n";
            }
            $php_code .= "\n";
        }
        
        // Output buffer
        $php_code .= "    ob_start();\n";
        $php_code .= "?>\n\n";
        $php_code .= $html . "\n\n";
        $php_code .= "<?php\n";
        $php_code .= "    return ob_get_clean();\n";
        $php_code .= "}\n";
        
        return $php_code;
    }
    
    /**
     * Convert to Gutenberg block JSON
     */
    private function convert_to_block_json($component_data) {
        $component_name = $component_data['name'];
        $component_type = $component_data['type'];
        $html = $this->convert_to_html($component_data);
        $props = $component_data['props'];
        
        $block_json = array(
            'name' => 'lovable/' . strtolower($component_name),
            'title' => $component_name,
            'category' => $this->get_block_category($component_type),
            'icon' => $this->get_block_icon($component_type),
            'description' => "Componente {$component_name} convertido desde Lovable/Bolt.new",
            'keywords' => array(strtolower($component_name), 'lovable', 'react'),
            'attributes' => array(),
            'supports' => array(
                'html' => false,
                'align' => array('full', 'wide'),
            ),
            'example' => array(
                'attributes' => array(),
            ),
        );
        
        // Convert props to block attributes
        foreach ($props as $prop_name => $prop_data) {
            $block_json['attributes'][$prop_name] = array(
                'type' => 'string',
                'default' => $prop_data['default'] ? $prop_data['default'] : '',
            );
        }
        
        return $block_json;
    }
    
    /**
     * Get block category based on component type
     */
    private function get_block_category($component_type) {
        $categories = array(
            'navigation' => 'formatting',
            'header' => 'formatting',
            'footer' => 'formatting',
            'hero' => 'layout',
            'card' => 'layout',
            'button' => 'formatting',
            'form' => 'formatting',
            'modal' => 'widgets',
        );
        
        return isset($categories[$component_type]) ? $categories[$component_type] : 'layout';
    }
    
    /**
     * Get block icon based on component type
     */
    private function get_block_icon($component_type) {
        $icons = array(
            'navigation' => 'menu',
            'header' => 'header',
            'footer' => 'footer',
            'hero' => 'cover-image',
            'card' => 'admin-post',
            'button' => 'button',
            'form' => 'feedback',
            'modal' => 'editor-expand',
        );
        
        return isset($icons[$component_type]) ? $icons[$component_type] : 'admin-generic';
    }
    
    /**
     * Convert to Elementor widget configuration
     */
    private function convert_to_elementor_widget($component_data) {
        $component_name = $component_data['name'];
        $component_type = $component_data['type'];
        $html = $this->convert_to_html($component_data);
        $props = $component_data['props'];
        $tailwind_classes = $component_data['tailwind_classes'];
        
        $widget_config = array(
            'name' => strtolower($component_name),
            'title' => $component_name,
            'category' => 'lovable-components',
            'icon' => 'fa fa-plug',
            'controls' => array(),
            'render_callback' => 'render_lovable_elementor_' . strtolower($component_name),
            'styles' => $tailwind_classes,
        );
        
        // Convert props to Elementor controls
        foreach ($props as $prop_name => $prop_data) {
            $control = array(
                'name' => $prop_name,
                'label' => ucfirst($prop_name),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => $prop_data['default'] ? $prop_data['default'] : '',
                'placeholder' => 'Enter ' . $prop_name,
            );
            
            // Detect control type based on default value
            if (is_numeric($prop_data['default'])) {
                $control['type'] = \Elementor\Controls_Manager::NUMBER;
            } elseif (filter_var($prop_data['default'], FILTER_VALIDATE_URL)) {
                $control['type'] = \Elementor\Controls_Manager::URL;
            } elseif (in_array($prop_name, array('color', 'bg_color', 'text_color'))) {
                $control['type'] = \Elementor\Controls_Manager::COLOR;
            }
            
            $widget_config['controls'][] = $control;
        }
        
        return $widget_config;
    }
    
    /**
     * Convert Tailwind classes to WordPress-compatible CSS
     */
    public function convert_tailwind_to_css($styles) {
        $css_output = array();
        $tailwind_classes = isset($styles['tailwind']) ? $styles['tailwind'] : array();
        
        // Common Tailwind to CSS mappings
        $tailwind_map = array(
            // Flexbox
            'flex' => 'display: flex;',
            'flex-col' => 'display: flex; flex-direction: column;',
            'flex-row' => 'display: flex; flex-direction: row;',
            'items-center' => 'align-items: center;',
            'items-start' => 'align-items: flex-start;',
            'items-end' => 'align-items: flex-end;',
            'justify-center' => 'justify-content: center;',
            'justify-start' => 'justify-content: flex-start;',
            'justify-end' => 'justify-content: flex-end;',
            'justify-between' => 'justify-content: space-between;',
            
            // Spacing
            'p-0' => 'padding: 0;',
            'p-1' => 'padding: 0.25rem;',
            'p-2' => 'padding: 0.5rem;',
            'p-3' => 'padding: 0.75rem;',
            'p-4' => 'padding: 1rem;',
            'p-6' => 'padding: 1.5rem;',
            'p-8' => 'padding: 2rem;',
            
            'm-0' => 'margin: 0;',
            'm-1' => 'margin: 0.25rem;',
            'm-2' => 'margin: 0.5rem;',
            'm-3' => 'margin: 0.75rem;',
            'm-4' => 'margin: 1rem;',
            'm-6' => 'margin: 1.5rem;',
            'm-8' => 'margin: 2rem;',
            
            // Typography
            'text-xs' => 'font-size: 0.75rem;',
            'text-sm' => 'font-size: 0.875rem;',
            'text-base' => 'font-size: 1rem;',
            'text-lg' => 'font-size: 1.125rem;',
            'text-xl' => 'font-size: 1.25rem;',
            'text-2xl' => 'font-size: 1.5rem;',
            'text-3xl' => 'font-size: 1.875rem;',
            'text-4xl' => 'font-size: 2.25rem;',
            
            'font-normal' => 'font-weight: 400;',
            'font-medium' => 'font-weight: 500;',
            'font-semibold' => 'font-weight: 600;',
            'font-bold' => 'font-weight: 700;',
            
            // Colors
            'text-white' => 'color: #ffffff;',
            'text-black' => 'color: #000000;',
            'text-gray-500' => 'color: #6b7280;',
            'text-blue-500' => 'color: #3b82f6;',
            
            'bg-white' => 'background-color: #ffffff;',
            'bg-black' => 'background-color: #000000;',
            'bg-gray-100' => 'background-color: #f3f4f6;',
            'bg-blue-500' => 'background-color: #3b82f6;',
            
            // Layout
            'w-full' => 'width: 100%;',
            'w-screen' => 'width: 100vw;',
            'h-full' => 'height: 100%;',
            'h-screen' => 'height: 100vh;',
            
            'container' => 'max-width: 1280px; margin-left: auto; margin-right: auto;',
            
            // Border
            'rounded' => 'border-radius: 0.25rem;',
            'rounded-lg' => 'border-radius: 0.5rem;',
            'rounded-xl' => 'border-radius: 0.75rem;',
            'rounded-full' => 'border-radius: 9999px;',
            
            // Shadow
            'shadow' => 'box-shadow: 0 1px 3px rgba(0,0,0,0.1);',
            'shadow-md' => 'box-shadow: 0 4px 6px rgba(0,0,0,0.1);',
            'shadow-lg' => 'box-shadow: 0 10px 15px rgba(0,0,0,0.1);',
            'shadow-xl' => 'box-shadow: 0 20px 25px rgba(0,0,0,0.1);',
        );
        
        // Generate CSS for each Tailwind class
        foreach ($tailwind_classes as $class) {
            if (isset($tailwind_map[$class])) {
                $css_output[$class] = $tailwind_map[$class];
            } else {
                // Try to parse dynamic Tailwind classes
                $parsed = $this->parse_dynamic_tailwind($class);
                if ($parsed) {
                    $css_output[$class] = $parsed;
                }
            }
        }
        
        return $css_output;
    }
    
    /**
     * Parse dynamic Tailwind classes
     */
    private function parse_dynamic_tailwind($class) {
        // Pattern: p-{number}, m-{number}, etc.
        $patterns = array(
            '/^p-(\d+)$/' => function($value) { return "padding: " . ($value * 0.25) . "rem;"; },
            '/^m-(\d+)$/' => function($value) { return "margin: " . ($value * 0.25) . "rem;"; },
            '/^px-(\d+)$/' => function($value) { return "padding-left: " . ($value * 0.25) . "rem; padding-right: " . ($value * 0.25) . "rem;"; },
            '/^py-(\d+)$/' => function($value) { return "padding-top: " . ($value * 0.25) . "rem; padding-bottom: " . ($value * 0.25) . "rem;"; },
            '/^mt-(\d+)$/' => function($value) { return "margin-top: " . ($value * 0.25) . "rem;"; },
            '/^mb-(\d+)$/' => function($value) { return "margin-bottom: " . ($value * 0.25) . "rem;"; },
            '/^ml-(\d+)$/' => function($value) { return "margin-left: " . ($value * 0.25) . "rem;"; },
            '/^mr-(\d+)$/' => function($value) { return "margin-right: " . ($value * 0.25) . "rem;"; },
            '/^gap-(\d+)$/' => function($value) { return "gap: " . ($value * 0.25) . "rem;"; },
            '/^w-(\d+)$/' => function($value) { return "width: " . ($value * 0.25) . "rem;"; },
            '/^h-(\d+)$/' => function($value) { return "height: " . ($value * 0.25) . "rem;"; },
        );
        
        foreach ($patterns as $pattern => $callback) {
            if (preg_match($pattern, $class, $matches)) {
                return $callback(intval($matches[1]));
            }
        }
        
        return null;
    }
    
    /**
     * Generate WordPress template
     */
    private function generate_wordpress_template($parsed_data) {
        $components = $parsed_data['components'];
        
        $template = "<?php\n";
        $template .= "/**\n";
        $template .= " * Template Name: Lovable Export Template\n";
        $template .= " * Auto-generated from Lovable/Bolt.new export\n";
        $template .= " */\n\n";
        
        $template .= "get_header();\n?>\n\n";
        $template .= "<div class=\"lovable-template\">\n";
        
        foreach ($components as $component_name => $component_data) {
            $template .= "    <?php echo do_shortcode('[lovable_{$component_name}]'); ?>\n";
        }
        
        $template .= "</div>\n\n";
        $template .= "<?php\n";
        $template .= "get_footer();\n";
        
        return $template;
    }
}
