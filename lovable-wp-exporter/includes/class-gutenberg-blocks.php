<?php
/**
 * Gutenberg Blocks Class
 * 
 * Registra y gestiona bloques de Gutenberg para componentes convertidos
 */

if (!defined('ABSPATH')) {
    exit;
}

class Lovable_Gutenberg_Blocks {
    
    /**
     * Register blocks
     */
    public static function register_blocks() {
        $components = self::get_converted_components();
        
        foreach ($components as $component) {
            self::register_single_block($component);
        }
    }
    
    /**
     * Register single block
     */
    private static function register_single_block($component) {
        $component_name = $component['name'];
        $block_name = 'lovable/' . strtolower($component_name);
        
        $props = isset($component['props']) ? $component['props'] : array();
        
        // Build attributes
        $attributes = array();
        foreach ($props as $prop_name => $prop_data) {
            $attributes[$prop_name] = array(
                'type' => 'string',
                'default' => $prop_data['default'] ?: '',
            );
        }
        
        register_block_type($block_name, array(
            'attributes' => $attributes,
            'render_callback' => function($block_attributes, $content, $block) use ($component) {
                return self::render_block($block_attributes, $component);
            },
            'editor_script' => 'lovable-gutenberg-editor',
            'editor_style' => 'lovable-gutenberg-editor',
        ));
    }
    
    /**
     * Render block
     */
    private static function render_block($attributes, $component) {
        $converter = new Lovable_Converter();
        $html = $converter->convert_to_html($component);
        
        // Replace attribute placeholders
        foreach ($attributes as $prop_name => $prop_value) {
            $html = str_replace('{' . $prop_name . '}', esc_html($prop_value), $html);
        }
        
        return $html;
    }
    
    /**
     * Get converted components
     */
    private static function get_converted_components() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'lovable_exports';
        $results = $wpdb->get_results("SELECT * FROM $table_name WHERE status = 'completed'", ARRAY_A);
        
        $components = array();
        
        foreach ($results as $result) {
            $component_data = json_decode($result['components'], true);
            if ($component_data) {
                foreach ($component_data as $component) {
                    $components[] = $component;
                }
            }
        }
        
        return $components;
    }
    
    /**
     * Enqueue editor assets
     */
    public static function enqueue_editor_assets() {
        wp_enqueue_script(
            'lovable-gutenberg-editor',
            LOVABLE_WP_EXPORTER_PLUGIN_URL . 'build/gutenberg-editor.js',
            array('wp-blocks', 'wp-element', 'wp-editor', 'wp-components'),
            LOVABLE_WP_EXPORTER_VERSION
        );
        
        wp_enqueue_style(
            'lovable-gutenberg-editor',
            LOVABLE_WP_EXPORTER_PLUGIN_URL . 'build/gutenberg-editor.css',
            array(),
            LOVABLE_WP_EXPORTER_VERSION
        );
    }
}
