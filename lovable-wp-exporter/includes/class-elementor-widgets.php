<?php
/**
 * Elementor Widgets Class
 * 
 * Registra y gestiona widgets de Elementor para componentes convertidos
 */

if (!defined('ABSPATH')) {
    exit;
}

class Lovable_Elementor_Widgets {
    
    /**
     * Register Elementor category
     */
    public static function register_category($elements_manager) {
        $elements_manager->add_category(
            'lovable-components',
            array(
                'title' => __('Lovable Components', 'lovable-wp-exporter'),
                'icon' => 'fa fa-plug',
            )
        );
    }
    
    /**
     * Register all converted widgets
     */
    public static function register_widgets($widgets_manager) {
        // Get registered components from database
        $components = self::get_converted_components();
        
        foreach ($components as $component) {
            self::register_single_widget($widgets_manager, $component);
        }
    }
    
    /**
     * Register single widget
     */
    private static function register_single_widget($widgets_manager, $component) {
        $component_name = $component['name'];
        $widget_class = 'Lovable_Elementor_Widget_' . strtolower($component_name);
        
        // Dynamic widget class
        eval(self::generate_widget_class($component));
        
        $widgets_manager->register(new $widget_class());
    }
    
    /**
     * Generate widget class code
     */
    private static function generate_widget_class($component) {
        $component_name = $component['name'];
        $widget_name = strtolower($component_name);
        $html = self::get_component_html($component);
        $props = isset($component['props']) ? $component['props'] : array();
        
        $controls = self::generate_controls_code($props);
        
        $class_code = "
class {$widget_name} extends \Elementor\Widget_Base {

    public function get_name() {
        return 'lovable_{$widget_name}';
    }

    public function get_title() {
        return __('{$component_name}', 'lovable-wp-exporter');
    }

    public function get_icon() {
        return 'eicon-code';
    }

    public function get_categories() {
        return array('lovable-components');
    }

    public function get_keywords() {
        return array('lovable', 'react', '{$widget_name}');
    }

    protected function register_controls() {
        \$this->start_controls_section(
            'content_section',
            [
                'label' => __('Content', 'lovable-wp-exporter'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );
        
        {$controls}
        
        \$this->end_controls_section();
    }

    protected function render() {
        \$settings = \$this->get_settings_for_display();
        ?>
        {$html}
        <?php
    }
}
";
        
        return $class_code;
    }
    
    /**
     * Generate controls code
     */
    private static function generate_controls_code($props) {
        $code = '';
        
        foreach ($props as $prop_name => $prop_data) {
            $default = $prop_data['default'] ?: '';
            
            $code .= "
        \$this->add_control(
            '{$prop_name}',
            [
                'label' => __('" . ucfirst($prop_name) . "', 'lovable-wp-exporter'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => '{$default}',
                'placeholder' => __('Enter {$prop_name}', 'lovable-wp-exporter'),
            ]
        );
        ";
        }
        
        return $code;
    }
    
    /**
     * Get component HTML
     */
    private static function get_component_html($component) {
        $converter = new Lovable_Converter();
        return $converter->convert_to_html($component);
    }
    
    /**
     * Get converted components from database
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
}
