<?php
/**
 * Plugin Name: Lovable to Elementor Pro
 * Plugin URI: https://yoursite.com/lovable-elementor
 * Description: Importa diseños de Lovable directamente como widgets nativos de Elementor.
 * Version: 1.0.0
 * Author: Your Name
 * Author URI: https://yoursite.com
 * License: GPL v2 or later
 * Text Domain: lovable-elementor-pro
 * Domain Path: /languages
 */

if (!defined('ABSPATH')) exit;

define('LOVABLE_ELEMENTOR_PRO_VERSION', '1.0.0');
define('LOVABLE_ELEMENTOR_PRO_PATH', plugin_dir_path(__FILE__));
define('LOVABLE_ELEMENTOR_PRO_URL', plugin_dir_url(__FILE__));

/**
 * Main Plugin Class
 */
class Lovable_Elementor_Pro {
    
    private static $instance = null;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        add_action('plugins_loaded', [$this, 'init']);
        register_activation_hook(__FILE__, [$this, 'activate']);
    }
    
    public function init() {
        load_plugin_textdomain('lovable-elementor-pro', false, dirname(LOVABLE_ELEMENTOR_PRO_BASENAME) . '/languages');
        
        // Check if Elementor is active
        if (!did_action('elementor/loaded')) {
            add_action('admin_notices', [$this, 'admin_notice_missing_elementor']);
            return;
        }
        
        // Register widgets
        add_action('elementor/widgets/register', [$this, 'register_widgets']);
        
        // Register widget categories
        add_action('elementor/elements/categories_registered', [$this, 'register_categories']);
        
        // Admin menu
        add_action('admin_menu', [$this, 'add_admin_menu']);
        
        // AJAX handlers
        add_action('wp_ajax_lovable_import_design', [$this, 'handle_import']);
        add_action('wp_ajax_lovable_parse_zip', [$this, 'handle_parse']);
        
        // Enqueue admin scripts
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_assets']);
    }
    
    public function activate() {
        // Create uploads directory for imports
        $upload_dir = wp_upload_dir();
        $import_dir = $upload_dir['basedir'] . '/lovable-imports';
        
        if (!file_exists($import_dir)) {
            wp_mkdir_p($import_dir);
        }
        
        // Create database table for imported designs
        global $wpdb;
        $table_name = $wpdb->prefix . 'lovable_designs';
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            design_name varchar(255) NOT NULL,
            design_data longtext NOT NULL,
            components longtext NOT NULL,
            status varchar(20) DEFAULT 'active',
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
        
        add_option('lovable_elementor_pro_version', LOVABLE_ELEMENTOR_PRO_VERSION);
    }
    
    public function admin_notice_missing_elementor() {
        $message = sprintf(
            esc_html__('"%1$s" requires %2$sElementor%3$s to be installed and active.', 'lovable-elementor-pro'),
            '<strong>Lovable to Elementor Pro</strong>',
            '<a href="' . esc_url(admin_url('plugin-install.php?tab=plugin-information&plugin=elementor')) . '">',
            '</a>'
        );
        
        printf('<div class="notice notice-warning is-dismissible"><p>%1$s</p></div>', $message);
    }
    
    public function register_categories($elements_manager) {
        $elements_manager->add_category(
            'lovable-components',
            [
                'title' => __('Lovable Components', 'lovable-elementor-pro'),
                'icon' => 'fa fa-plug',
            ]
        );
    }
    
    public function register_widgets($widgets_manager) {
        // Get imported designs from database
        global $wpdb;
        $table_name = $wpdb->prefix . 'lovable_designs';
        $designs = $wpdb->get_results("SELECT * FROM $table_name WHERE status = 'active'", ARRAY_A);
        
        foreach ($designs as $design) {
            $components = json_decode($design['design_data'], true);
            
            if (is_array($components)) {
                foreach ($components as $component_name => $component_data) {
                    $widget_class = $this->create_widget_class($component_name, $component_data);
                    if ($widget_class) {
                        $widgets_manager->register(new $widget_class());
                    }
                }
            }
        }
    }
    
    private function create_widget_class($name, $data) {
        $widget_name = sanitize_title($name);
        $class_name = 'Lovable_Widget_' . str_replace('-', '_', $widget_name);
        
        // Check if class already exists
        if (class_exists($class_name)) {
            return $class_name;
        }
        
        $title = ucwords(str_replace('-', ' ', $widget_name));
        $html = isset($data['html']) ? $data['html'] : '';
        $css = isset($data['css']) ? $data['css'] : '';
        $controls = isset($data['controls']) ? $data['controls'] : [];
        
        // Generate controls array
        $controls_code = '';
        foreach ($controls as $control) {
            $controls_code .= $this->generate_control_code($control);
        }
        
        $php_code = <<<PHP
class {$class_name} extends \Elementor\Widget_Base {

    public function get_name() {
        return 'lovable_{$widget_name}';
    }

    public function get_title() {
        return esc_html__('{$title}', 'lovable-elementor-pro');
    }

    public function get_icon() {
        return 'eicon-code';
    }

    public function get_categories() {
        return ['lovable-components'];
    }

    public function get_keywords() {
        return ['lovable', 'react', '{$widget_name}'];
    }

    protected function register_controls() {
        \$this->start_controls_section(
            'content_section',
            [
                'label' => esc_html__('Content', 'lovable-elementor-pro'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        {$controls_code}

        \$this->end_controls_section();
    }

    protected function render() {
        \$settings = \$this->get_settings_for_display();
        ?>
        <style>
        {$css}
        </style>
        <div class="lovable-component lovable-{$widget_name}">
        {$html}
        </div>
        <?php
    }
}
PHP;
        
        // Evaluate the class
        eval($php_code);
        
        return $class_name;
    }
    
    private function generate_control_code($control) {
        $type = $control['type'] ?? 'text';
        $name = $control['name'] ?? 'field';
        $label = $control['label'] ?? ucwords($name);
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
        
        return <<<PHP
\$this->add_control(
    '{$name}',
    [
        'label' => esc_html__('{$label}', 'lovable-elementor-pro'),
        'type' => {$elementor_type},
        'default' => '{$default}',
    ]
);

PHP;
    }
    
    public function add_admin_menu() {
        add_menu_page(
            __('Lovable Importer', 'lovable-elementor-pro'),
            __('Lovable Importer', 'lovable-elementor-pro'),
            'manage_options',
            'lovable-elementor-pro',
            [$this, 'render_admin_page'],
            'dashicons-code-standards',
            30
        );
        
        add_submenu_page(
            'lovable-elementor-pro',
            __('Import Design', 'lovable-elementor-pro'),
            __('Import Design', 'lovable-elementor-pro'),
            'manage_options',
            'lovable-elementor-pro',
            [$this, 'render_admin_page']
        );
        
        add_submenu_page(
            'lovable-elementor-pro',
            __('My Designs', 'lovable-elementor-pro'),
            __('My Designs', 'lovable-elementor-pro'),
            'manage_options',
            'lovable-elementor-pro-designs',
            [$this, 'render_designs_page']
        );
    }
    
    public function render_admin_page() {
        include LOVABLE_ELEMENTOR_PRO_PATH . 'includes/admin/import.php';
    }
    
    public function render_designs_page() {
        include LOVABLE_ELEMENTOR_PRO_PATH . 'includes/admin/designs.php';
    }
    
    public function enqueue_admin_assets($hook) {
        if (strpos($hook, 'lovable-elementor') === false) {
            return;
        }
        
        wp_enqueue_style(
            'lovable-admin',
            LOVABLE_ELEMENTOR_PRO_URL . 'assets/css/admin.css',
            [],
            LOVABLE_ELEMENTOR_PRO_VERSION
        );
        
        wp_enqueue_script(
            'lovable-admin',
            LOVABLE_ELEMENTOR_PRO_URL . 'assets/js/admin.js',
            ['jquery'],
            LOVABLE_ELEMENTOR_PRO_VERSION,
            true
        );
        
        wp_localize_script('lovable-admin', 'lovableAdmin', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('lovable_nonce'),
            'strings' => [
                'importSuccess' => __('Design imported successfully!', 'lovable-elementor-pro'),
                'importError' => __('Import failed. Please try again.', 'lovable-elementor-pro'),
            ]
        ]);
    }
    
    public function handle_parse() {
        check_ajax_referer('lovable_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized');
        }
        
        // Handle file upload
        if (!isset($_FILES['design_file'])) {
            wp_send_json_error('No file uploaded');
        }
        
        $file = $_FILES['design_file'];
        
        if ($file['error'] !== UPLOAD_ERR_OK) {
            wp_send_json_error('Upload error: ' . $file['error']);
        }
        
        // Check if it's a ZIP
        $file_type = wp_check_filetype($file['name']);
        if ($file_type['ext'] !== 'zip') {
            wp_send_json_error('Please upload a ZIP file');
        }
        
        // Process ZIP with JSZip equivalent (using PHP ZipArchive)
        $zip = new ZipArchive();
        if ($zip->open($file['tmp_name']) !== TRUE) {
            wp_send_json_error('Cannot open ZIP file');
        }
        
        $components = [];
        
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $file_info = $zip->statIndex($i);
            $file_path = $file_info['name'];
            
            // Look for React components
            if (preg_match('/(src\/)?(components|pages)\/.*\.(tsx|jsx)$/i', $file_path)) {
                $content = $zip->getFromIndex($i);
                $file_name = basename($file_path);
                $component_name = preg_replace('/\.(tsx|jsx)$/i', '', $file_name);
                
                // Parse component
                $parsed = $this->parse_react_component($content, $component_name);
                $components[$component_name] = $parsed;
            }
        }
        
        $zip->close();
        
        wp_send_json_success([
            'components' => $components,
            'count' => count($components)
        ]);
    }
    
    private function parse_react_component($content, $name) {
        // Extract JSX from return statement
        $html = $content;
        
        // Remove motion.* tags
        $html = preg_replace('/<motion\./', '<', $html);
        $html = preg_replace('/<\/motion\./', '</', $html);
        
        // Remove animation props
        $html = preg_replace('/\s*(initial|animate|transition|whileHover|whileTap)\s*=\s*\{[^}]*\}/', '', $html);
        
        // Remove imports/exports
        $html = preg_replace('/import\s+.*?from\s+["\'].*?["\'];?/', '', $html);
        $html = preg_replace('/export\s+default\s+\w+;?/', '', $html);
        
        // Remove JSX expressions
        $html = preg_replace('/\{[^{}]*\}/', '', $html);
        
        // Convert className to class
        $html = str_replace('className=', 'class=', $html);
        
        // Extract return statement
        if (preg_match('/return\s*\(([\s\S]*)\)\s*;?\s*$/m', $html, $matches)) {
            $html = $matches[1];
        }
        
        // Extract Tailwind classes
        preg_match_all('/className\s*=\s*["\']([^"\']+)["\']/', $content, $class_matches);
        $tailwind_classes = [];
        if (!empty($class_matches[1])) {
            foreach ($class_matches[1] as $class_string) {
                $classes = explode(' ', $class_string);
                $tailwind_classes = array_merge($tailwind_classes, $classes);
            }
        }
        $tailwind_classes = array_unique(array_filter($tailwind_classes));
        
        // Generate controls from props
        $controls = [];
        preg_match_all('/\{\s*(\w+)\s*\}/', $content, $prop_matches);
        if (!empty($prop_matches[1])) {
            foreach (array_unique($prop_matches[1]) as $prop) {
                $controls[] = [
                    'name' => $prop,
                    'label' => ucwords($prop),
                    'type' => 'text',
                    'default' => ''
                ];
            }
        }
        
        return [
            'name' => $name,
            'html' => trim($html),
            'tailwind_classes' => $tailwind_classes,
            'controls' => $controls,
            'css' => $this->generate_css_from_tailwind($tailwind_classes)
        ];
    }
    
    private function generate_css_from_tailwind($classes) {
        $map = [
            'container' => 'max-width: 1280px; margin: 0 auto; padding: 0 1rem;',
            'flex' => 'display: flex;',
            'flex-col' => 'flex-direction: column;',
            'items-center' => 'align-items: center;',
            'justify-center' => 'justify-content: center;',
            'text-center' => 'text-align: center;',
            'text-4xl' => 'font-size: 2.25rem;',
            'text-3xl' => 'font-size: 1.875rem;',
            'text-2xl' => 'font-size: 1.5rem;',
            'text-xl' => 'font-size: 1.25rem;',
            'text-lg' => 'font-size: 1.125rem;',
            'font-bold' => 'font-weight: 700;',
            'font-semibold' => 'font-weight: 600;',
            'p-8' => 'padding: 2rem;',
            'p-6' => 'padding: 1.5rem;',
            'p-4' => 'padding: 1rem;',
            'gap-4' => 'gap: 1rem;',
            'gap-6' => 'gap: 1.5rem;',
            'rounded' => 'border-radius: 0.25rem;',
            'rounded-lg' => 'border-radius: 0.5rem;',
            'shadow' => 'box-shadow: 0 1px 3px rgba(0,0,0,0.1);',
            'shadow-lg' => 'box-shadow: 0 10px 15px rgba(0,0,0,0.1);',
        ];
        
        $css = "/* Tailwind conversions */\n";
        foreach ($classes as $class) {
            if (isset($map[$class])) {
                $css .= ".{$class} { {$map[$class]} }\n";
            }
        }
        
        return $css;
    }
    
    public function handle_import() {
        check_ajax_referer('lovable_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized');
        }
        
        $design_data = isset($_POST['design_data']) ? json_decode(stripslashes($_POST['design_data']), true) : null;
        $design_name = isset($_POST['design_name']) ? sanitize_text_field($_POST['design_name']) : 'Imported Design';
        
        if (!$design_data) {
            wp_send_json_error('Invalid design data');
        }
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'lovable_designs';
        
        $result = $wpdb->insert($table_name, [
            'design_name' => $design_name,
            'design_data' => json_encode($design_data),
            'components' => json_encode(array_keys($design_data)),
            'status' => 'active'
        ]);
        
        if ($result) {
            // Clear Elementor cache
            delete_transient('elementor-widgets');
            
            wp_send_json_success([
                'id' => $wpdb->insert_id,
                'message' => __('Design imported successfully! Refresh Elementor to see the new widgets.', 'lovable-elementor-pro')
            ]);
        } else {
            wp_send_json_error('Database error');
        }
    }
}

// Initialize plugin
function lovable_elementor_pro_init() {
    return Lovable_Elementor_Pro::get_instance();
}
add_action('plugins_loaded', 'lovable_elementor_pro_init');
