<?php
/**
 * Shortcodes Class
 * 
 * Renderiza shortcodes para componentes convertidos
 */

if (!defined('ABSPATH')) {
    exit;
}

class Lovable_Shortcodes {
    
    /**
     * Render component shortcode
     */
    public static function render_component($atts, $content = '', $tag = '') {
        $component_name = str_replace('lovable_', '', $tag);
        
        // Get component from registry
        $component = self::get_component_by_name($component_name);
        
        if (!$component) {
            return '<!-- Lovable component not found: ' . esc_html($component_name) . ' -->';
        }
        
        // Convert to HTML
        $converter = new Lovable_Converter();
        $html = $converter->convert_to_html($component);
        
        // Replace attributes
        foreach ($atts as $key => $value) {
            $html = str_replace('{' . $key . '}', esc_html($value), $html);
        }
        
        // Apply styles
        $html = self::apply_styles($html, $component);
        
        return $html;
    }
    
    /**
     * Render section shortcode
     */
    public static function render_section($atts, $content = '', $tag = '') {
        $atts = shortcode_atts(array(
            'name' => '',
            'class' => '',
            'style' => '',
        ), $atts, $tag);
        
        $component = self::get_component_by_name($atts['name']);
        
        if (!$component) {
            return '<!-- Lovable section not found: ' . esc_html($atts['name']) . ' -->';
        }
        
        $converter = new Lovable_Converter();
        $html = $converter->convert_to_html($component);
        
        // Wrap in section
        $output = '<section class="lovable-section ' . esc_attr($atts['class']) . '"';
        
        if (!empty($atts['style'])) {
            $output .= ' style="' . esc_attr($atts['style']) . '"';
        }
        
        $output .= '>' . $html . '</section>';
        
        return $output;
    }
    
    /**
     * Render full page shortcode
     */
    public static function render_page($atts, $content = '', $tag = '') {
        $atts = shortcode_atts(array(
            'name' => '',
            'template' => 'default',
        ), $atts, $tag);
        
        // Get all components for this page
        $components = self::get_components_for_page($atts['name']);
        
        if (empty($components)) {
            return '<!-- Lovable page not found: ' . esc_html($atts['name']) . ' -->';
        }
        
        $converter = new Lovable_Converter();
        $output = '<div class="lovable-page lovable-page-' . esc_attr($atts['name']) . '">';
        
        foreach ($components as $component) {
            $html = $converter->convert_to_html($component);
            $output .= $html;
        }
        
        $output .= '</div>';
        
        return $output;
    }
    
    /**
     * Get component by name
     */
    private static function get_component_by_name($name) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'lovable_exports';
        
        $result = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE export_name = %s AND status = 'completed'",
            $name
        ), ARRAY_A);
        
        if ($result) {
            $components = json_decode($result['components'], true);
            return isset($components[$name]) ? $components[$name] : null;
        }
        
        return null;
    }
    
    /**
     * Get components for a page
     */
    private static function get_components_for_page($page_name) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'lovable_exports';
        
        $result = $wpdb->get_row($wpdb->prepare(
            "SELECT components FROM $table_name WHERE export_name = %s AND status = 'completed'",
            $page_name
        ), ARRAY_A);
        
        if ($result) {
            return json_decode($result['components'], true);
        }
        
        return array();
    }
    
    /**
     * Apply styles to HTML
     */
    private static function apply_styles($html, $component) {
        $settings = get_option('lovable_wp_exporter_settings', array());
        
        // Enqueue Tailwind if enabled
        if (isset($settings['auto_enqueue_styles']) && $settings['auto_enqueue_styles']) {
            add_action('wp_enqueue_scripts', array(__CLASS__, 'enqueue_styles'));
        }
        
        return $html;
    }
    
    /**
     * Enqueue styles
     */
    public static function enqueue_styles() {
        // Enqueue converted Tailwind CSS
        wp_enqueue_style(
            'lovable-converted-styles',
            LOVABLE_WP_EXPORTER_PLUGIN_URL . 'assets/css/converted-styles.css',
            array(),
            LOVABLE_WP_EXPORTER_VERSION
        );
    }
}
