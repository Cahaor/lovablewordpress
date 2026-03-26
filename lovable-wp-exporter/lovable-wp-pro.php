<?php
/**
 * Plugin Name: Lovable WP Pro
 * Plugin URI: https://lovablewp.pro
 * Description: Importa diseños de Lovable directamente como widgets nativos de Elementor con sync automático.
 * Version: 1.0.0
 * Author: Lovable WP Pro
 * Author URI: https://lovablewp.pro
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: lovable-wp-pro
 * Domain Path: /languages
 * Requires at least: 5.8
 * Requires PHP: 8.0
 */

if (!defined('ABSPATH')) exit;

// Define constants
define('LOVABLE_WP_PRO_VERSION', '1.0.0');
define('LOVABLE_WP_PRO_PATH', plugin_dir_path(__FILE__));
define('LOVABLE_WP_PRO_URL', plugin_dir_url(__FILE__));
define('LOVABLE_WP_PRO_BASENAME', plugin_basename(__FILE__));

// Autoloader
spl_autoload_register(function ($class) {
    $prefix = 'Lovable_WP_Pro\\';
    $base_dir = LOVABLE_WP_PRO_PATH . 'includes/';
    
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }
    
    $relative_class = substr($class, $len);
    $file = $base_dir . 'class-' . strtolower(str_replace('_', '-', $relative_class)) . '.php';
    
    if (file_exists($file)) {
        require $file;
    }
});

/**
 * Main Plugin Class
 */
final class Lovable_WP_Pro_Plugin {
    
    private static $instance = null;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        $this->init_hooks();
        $this->load_dependencies();
    }
    
    private function init_hooks() {
        register_activation_hook(__FILE__, [$this, 'activate']);
        register_deactivation_hook(__FILE__, [$this, 'deactivate']);
        
        add_action('plugins_loaded', [$this, 'init']);
    }
    
    private function load_dependencies() {
        // Core classes
        require_once LOVABLE_WP_PRO_PATH . 'includes/class-api-client.php';
        require_once LOVABLE_WP_PRO_PATH . 'includes/class-widget-registry.php';
        require_once LOVABLE_WP_PRO_PATH . 'includes/class-template-importer.php';
        require_once LOVABLE_WP_PRO_PATH . 'includes/class-cloud-sync.php';
        
        // Admin
        if (is_admin()) {
            require_once LOVABLE_WP_PRO_PATH . 'admin/class-admin.php';
            new Lovable_WP_Pro_Admin();
        }
        
        // Elementor integration
        add_action('elementor/init', [$this, 'init_elementor']);
    }
    
    public function init() {
        load_plugin_textdomain('lovable-wp-pro', false, dirname(LOVABLE_WP_PRO_BASENAME) . '/languages');
        
        // Schedule sync job
        if (!wp_next_scheduled('lovable_wp_pro_sync')) {
            wp_schedule_event(time(), 'hourly', 'lovable_wp_pro_sync');
        }
        
        add_action('lovable_wp_pro_sync', [$this, 'run_scheduled_sync']);
    }
    
    public function activate() {
        // Create options
        add_option('lovable_wp_pro_license_key', '');
        add_option('lovable_wp_pro_api_url', 'https://api.lovablewp.pro');
        add_option('lovable_wp_pro_connected', false);
        add_option('lovable_wp_pro_widgets', []);
        
        // Create custom table for widgets
        global $wpdb;
        $table_name = $wpdb->prefix . 'lovable_widgets';
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            widget_name varchar(255) NOT NULL,
            widget_type varchar(100) NOT NULL,
            html longtext NOT NULL,
            css longtext NOT NULL,
            controls longtext,
            status varchar(20) DEFAULT 'active',
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY widget_type (widget_type),
            KEY status (status)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
        
        flush_rewrite_rules();
    }
    
    public function deactivate() {
        wp_clear_scheduled_hook('lovable_wp_pro_sync');
        flush_rewrite_rules();
    }
    
    public function init_elementor() {
        // Check if Elementor is active
        if (!did_action('elementor/loaded')) {
            add_action('admin_notices', [$this, 'admin_notice_missing_elementor']);
            return;
        }
        
        // Register widget category
        add_action('elementor/elements/categories_registered', [$this, 'register_category']);
        
        // Register dynamic widgets
        add_action('elementor/widgets/register', [$this, 'register_widgets']);
        
        // Register controls
        add_action('elementor/controls/register', [$this, 'register_controls']);
    }
    
    public function register_category($elements_manager) {
        $elements_manager->add_category(
            'lovable-pro',
            [
                'title' => __('Lovable Pro', 'lovable-wp-pro'),
                'icon' => 'fa fa-plug',
            ]
        );
    }
    
    public function register_widgets($widgets_manager) {
        // Get registered widgets from database
        global $wpdb;
        $table_name = $wpdb->prefix . 'lovable_widgets';
        $widgets = $wpdb->get_results("SELECT * FROM $table_name WHERE status = 'active'", ARRAY_A);
        
        if (empty($widgets)) {
            return;
        }
        
        foreach ($widgets as $widget_data) {
            $widget_class = $this->create_widget_class($widget_data);
            if ($widget_class) {
                $widgets_manager->register(new $widget_class());
            }
        }
    }
    
    private function create_widget_class($data) {
        $widget_name = sanitize_title($data['widget_name']);
        $class_name = 'Lovable_Widget_' . str_replace('-', '_', $widget_name);
        
        if (class_exists($class_name)) {
            return $class_name;
        }
        
        $title = ucwords(str_replace('-', ' ', $widget_name));
        $html = $data['html'];
        $css = $data['css'];
        $controls = !empty($data['controls']) ? json_decode($data['controls'], true) : [];
        
        // Generate controls code
        $controls_code = $this->generate_controls_code($controls);
        
        $php_code = <<<PHP
class {$class_name} extends \Elementor\Widget_Base {

    public function get_name() {
        return 'lovable_pro_{$widget_name}';
    }

    public function get_title() {
        return esc_html__('{$title}', 'lovable-wp-pro');
    }

    public function get_icon() {
        return 'eicon-code';
    }

    public function get_categories() {
        return ['lovable-pro'];
    }

    public function get_keywords() {
        return ['lovable', 'react', '{$widget_name}'];
    }

    protected function register_controls() {
        \$this->start_controls_section(
            'content_section',
            [
                'label' => esc_html__('Content', 'lovable-wp-pro'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        {$controls_code}

        \$this->end_controls_section();
        
        // Style controls
        \$this->register_style_controls();
    }
    
    private function register_style_controls() {
        \$this->start_controls_section(
            'style_section',
            [
                'label' => esc_html__('Style', 'lovable-wp-pro'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );
        
        \$this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'typography',
                'selector' => '{{WRAPPER}} .lovable-widget',
            ]
        );
        
        \$this->add_control(
            'text_color',
            [
                'label' => esc_html__('Text Color', 'lovable-wp-pro'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .lovable-widget' => 'color: {{VALUE}};',
                ],
            ]
        );
        
        \$this->end_controls_section();
    }

    protected function render() {
        \$settings = \$this->get_settings_for_display();
        ?>
        <style>
        .lovable-widget.lovable-{$widget_name} {
            /* Base styles from Lovable */
        }
        {$css}
        </style>
        <div class="lovable-widget lovable-{$widget_name}">
        {$html}
        </div>
        <?php
    }
}
PHP;
        
        eval($php_code);
        
        return $class_name;
    }
    
    private function generate_controls_code($controls) {
        if (empty($controls)) {
            return "\$this->add_control(
                'info',
                [
                    'type' => \Elementor\Controls_Manager::RAW_HTML,
                    'raw' => __('This widget is read-only. Edit in Lovable and sync.', 'lovable-wp-pro'),
                    'content_classes' => 'elementor-panel-alert elementor-panel-alert-info',
                ]
            );";
        }
        
        $code = '';
        foreach ($controls as $control) {
            $name = $control['name'] ?? 'field';
            $label = $control['label'] ?? ucwords($name);
            $type = $control['type'] ?? 'text';
            $default = $control['default'] ?? '';
            
            $control_type_map = [
                'text' => '\Elementor\Controls_Manager::TEXT',
                'textarea' => '\Elementor\Controls_Manager::TEXTAREA',
                'wysiwyg' => '\Elementor\Controls_Manager::WYSIWYG',
                'number' => '\Elementor\Controls_Manager::NUMBER',
                'url' => '\Elementor\Controls_Manager::URL',
                'image' => '\Elementor\Controls_Manager::MEDIA',
                'color' => '\Elementor\Controls_Manager::COLOR',
                'select' => '\Elementor\Controls_Manager::SELECT',
                'checkbox' => '\Elementor\Controls_Manager::YESNO',
            ];
            
            $elementor_type = $control_type_map[$type] ?? '\Elementor\Controls_Manager::TEXT';
            
            $code .= <<<PHP
\$this->add_control(
    '{$name}',
    [
        'label' => esc_html__('{$label}', 'lovable-wp-pro'),
        'type' => {$elementor_type},
        'default' => '{$default}',
    ]
);

PHP;
        }
        
        return $code;
    }
    
    public function register_controls($controls_manager) {
        // Add custom controls if needed
    }
    
    public function admin_notice_missing_elementor() {
        $message = sprintf(
            esc_html__('"%1$s" requires %2$sElementor%3$s to be installed and active.', 'lovable-wp-pro'),
            '<strong>Lovable WP Pro</strong>',
            '<a href="' . esc_url(admin_url('plugin-install.php?tab=plugin-information&plugin=elementor')) . '">',
            '</a>'
        );
        
        printf('<div class="notice notice-warning is-dismissible"><p>%1$s</p></div>', $message);
    }
    
    public function run_scheduled_sync() {
        $api_client = new Lovable_WP_Pro_API_Client();
        $api_client->sync_widgets();
    }
}

// Initialize plugin
function lovable_wp_pro_init() {
    return Lovable_WP_Pro_Plugin::get_instance();
}
add_action('plugins_loaded', 'lovable_wp_pro_init');
