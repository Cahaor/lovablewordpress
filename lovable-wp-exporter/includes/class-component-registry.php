<?php
/**
 * Component Registry Class
 * 
 * Registra y gestiona componentes convertidos en WordPress
 */

if (!defined('ABSPATH')) {
    exit;
}

class Lovable_Component_Registry {
    
    /**
     * Registered components
     */
    private static $registered_components = array();
    
    /**
     * Register a component
     */
    public function register($export_data, $register_as = 'shortcode') {
        $result = array(
            'success' => false,
            'registered' => array(),
            'errors' => array(),
        );
        
        $components = isset($export_data['components']) ? $export_data['components'] : array();
        
        foreach ($components as $component_name => $component_data) {
            switch ($register_as) {
                case 'shortcode':
                    $registered = $this->register_as_shortcode($component_data);
                    break;
                case 'block':
                    $registered = $this->register_as_block($component_data);
                    break;
                case 'both':
                    $registered_shortcode = $this->register_as_shortcode($component_data);
                    $registered_block = $this->register_as_block($component_data);
                    $registered = array_merge($registered_shortcode, $registered_block);
                    break;
                default:
                    $registered = $this->register_as_shortcode($component_data);
            }
            
            if ($registered['success']) {
                $result['registered'][] = $component_name;
                self::$registered_components[$component_name] = $registered;
            } else {
                $result['errors'][] = $registered['error'];
            }
        }
        
        $result['success'] = count($result['registered']) > 0;
        
        // Save to database
        if ($result['success']) {
            $this->save_to_database($export_data, $register_as);
        }
        
        return $result;
    }
    
    /**
     * Register component as shortcode
     */
    private function register_as_shortcode($component_data) {
        $component_name = $component_data['name'];
        $shortcode_tag = 'lovable_' . strtolower($component_name);
        
        // Check if shortcode already exists
        if (shortcode_exists($shortcode_tag)) {
            return array(
                'success' => false,
                'error' => "Shortcode {$shortcode_tag} already exists",
            );
        }
        
        // Generate HTML
        $converter = new Lovable_Converter();
        $html = $converter->convert_to_html($component_data);
        
        // Register shortcode
        add_shortcode($shortcode_tag, function($atts) use ($html, $component_data) {
            return $this->render_shortcode($html, $atts, $component_data);
        });
        
        return array(
            'success' => true,
            'type' => 'shortcode',
            'tag' => $shortcode_tag,
        );
    }
    
    /**
     * Render shortcode
     */
    private function render_shortcode($html, $atts, $component_data) {
        // Process props
        $props = isset($component_data['props']) ? $component_data['props'] : array();
        $defaults = array();
        
        foreach ($props as $prop_name => $prop_data) {
            $defaults[$prop_name] = $prop_data['default'] ?: '';
        }
        
        $atts = shortcode_atts($defaults, $atts, 'lovable_' . strtolower($component_data['name']));
        
        // Replace prop placeholders in HTML
        foreach ($props as $prop_name => $prop_data) {
            $html = str_replace('{' . $prop_name . '}', $atts[$prop_name], $html);
        }
        
        // Apply Tailwind styles
        $html = $this->apply_tailwind_styles($html, $component_data);
        
        return $html;
    }
    
    /**
     * Register component as Gutenberg block
     */
    private function register_as_block($component_data) {
        $component_name = $component_data['name'];
        $block_name = 'lovable/' . strtolower($component_name);
        
        // Generate block attributes
        $attributes = array();
        $props = isset($component_data['props']) ? $component_data['props'] : array();
        
        foreach ($props as $prop_name => $prop_data) {
            $attributes[$prop_name] = array(
                'type' => 'string',
                'default' => $prop_data['default'] ?: '',
            );
        }
        
        // Register block
        register_block_type($block_name, array(
            'attributes' => $attributes,
            'render_callback' => function($block_attributes) use ($component_data) {
                return $this->render_block($block_attributes, $component_data);
            },
        ));
        
        return array(
            'success' => true,
            'type' => 'block',
            'name' => $block_name,
        );
    }
    
    /**
     * Render Gutenberg block
     */
    private function render_block($attributes, $component_data) {
        $converter = new Lovable_Converter();
        $html = $converter->convert_to_html($component_data);
        
        // Replace prop placeholders
        foreach ($attributes as $prop_name => $prop_value) {
            $html = str_replace('{' . $prop_name . '}', esc_html($prop_value), $html);
        }
        
        // Apply Tailwind styles
        $html = $this->apply_tailwind_styles($html, $component_data);
        
        return $html;
    }
    
    /**
     * Apply Tailwind styles to HTML
     */
    private function apply_tailwind_styles($html, $component_data) {
        $tailwind_classes = isset($component_data['tailwind_classes']) ? $component_data['tailwind_classes'] : array();
        
        // Check if we should convert Tailwind to CSS
        $settings = get_option('lovable_wp_exporter_settings', array());
        $convert_tailwind = isset($settings['convert_tailwind']) ? $settings['convert_tailwind'] : true;
        
        if (!$convert_tailwind) {
            return $html;
        }
        
        // Generate inline styles or enqueue Tailwind CDN
        if (isset($settings['auto_enqueue_styles']) && $settings['auto_enqueue_styles']) {
            $this->enqueue_tailwind_styles();
        }
        
        return $html;
    }
    
    /**
     * Enqueue Tailwind CSS
     */
    private function enqueue_tailwind_styles() {
        if (!wp_script_is('tailwindcss', 'enqueued')) {
            // Option 1: Use Tailwind CDN (for development)
            // wp_enqueue_script('tailwindcss', 'https://cdn.tailwindcss.com', array(), null, false);
            
            // Option 2: Use compiled CSS (for production)
            wp_enqueue_style(
                'lovable-tailwind',
                LOVABLE_WP_EXPORTER_PLUGIN_URL . 'assets/css/tailwind-compiled.css',
                array(),
                LOVABLE_WP_EXPORTER_VERSION
            );
        }
    }
    
    /**
     * Save export to database
     */
    private function save_to_database($export_data, $register_as) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'lovable_exports';
        
        $data = array(
            'export_name' => isset($export_data['metadata']['export_name']) ? $export_data['metadata']['export_name'] : 'Export ' . current_time('mysql'),
            'source_type' => isset($export_data['metadata']['source_type']) ? $export_data['metadata']['source_type'] : 'lovable',
            'components' => json_encode($export_data['components']),
            'styles' => json_encode($export_data['styles']),
            'assets' => json_encode(isset($export_data['assets']) ? $export_data['assets'] : array()),
            'register_as' => $register_as,
            'status' => 'completed',
        );
        
        $wpdb->insert($table_name, $data);
        
        return $wpdb->insert_id;
    }
    
    /**
     * Get registered components
     */
    public static function get_registered_components() {
        return self::$registered_components;
    }
    
    /**
     * Unregister a component
     */
    public function unregister($component_name, $type = 'shortcode') {
        if ($type === 'shortcode') {
            $shortcode_tag = 'lovable_' . strtolower($component_name);
            remove_shortcode($shortcode_tag);
        } elseif ($type === 'block') {
            $block_name = 'lovable/' . strtolower($component_name);
            unregister_block_type($block_name);
        }
        
        unset(self::$registered_components[$component_name]);
        
        return true;
    }
    
    /**
     * Import components from JSON file
     */
    public function import_from_json($json_file) {
        if (!file_exists($json_file)) {
            return array(
                'success' => false,
                'error' => 'File not found',
            );
        }
        
        $json_content = file_get_contents($json_file);
        $export_data = json_decode($json_content, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            return array(
                'success' => false,
                'error' => 'Invalid JSON file',
            );
        }
        
        return $this->register($export_data, 'both');
    }
    
    /**
     * Export registered components to JSON
     */
    public function export_to_json($component_names = array()) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'lovable_exports';
        
        if (empty($component_names)) {
            // Export all
            $exports = $wpdb->get_results("SELECT * FROM $table_name WHERE status = 'completed'", ARRAY_A);
        } else {
            // Export specific components
            $placeholders = implode(',', array_fill(0, count($component_names), '%s'));
            $exports = $wpdb->get_results($wpdb->prepare(
                "SELECT * FROM $table_name WHERE status = 'completed' AND export_name IN ($placeholders)",
                $component_names
            ), ARRAY_A);
        }
        
        return json_encode($exports, JSON_PRETTY_PRINT);
    }
}
