<?php
/**
 * Plugin Name: Lovable to WordPress Exporter
 * Plugin URI: https://github.com/yourusername/lovable-wp-exporter
 * Description: Exporta páginas web creadas en Lovable, Bolt.new o Base44 a WordPress con soporte para Elementor.
 * Version: 1.0.0
 * Author: Your Name
 * Author URI: https://yourwebsite.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: lovable-wp-exporter
 * Domain Path: /languages
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('LOVABLE_WP_EXPORTER_VERSION', '1.0.0');
define('LOVABLE_WP_EXPORTER_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('LOVABLE_WP_EXPORTER_PLUGIN_URL', plugin_dir_url(__FILE__));
define('LOVABLE_WP_EXPORTER_PLUGIN_BASENAME', plugin_basename(__FILE__));

/**
 * Main Plugin Class
 */
final class Lovable_WP_Exporter {
    
    /**
     * Single instance of the plugin
     */
    private static $instance = null;
    
    /**
     * Get instance
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Constructor
     */
    private function __construct() {
        $this->init_hooks();
        $this->load_dependencies();
    }
    
    /**
     * Initialize hooks
     */
    private function init_hooks() {
        // Activation/Deactivation
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
        
        // Load plugin textdomain
        add_action('init', array($this, 'load_textdomain'));
        
        // Admin menu
        add_action('admin_menu', array($this, 'add_admin_menu'));
        
        // Admin scripts and styles
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
        
        // AJAX handlers
        add_action('wp_ajax_lovable_export_parse', array($this, 'handle_export_parse'));
        add_action('wp_ajax_lovable_export_convert', array($this, 'handle_export_convert'));
        add_action('wp_ajax_lovable_export_import', array($this, 'handle_export_import'));
        
        // Register Elementor widgets (if Elementor is active)
        add_action('elementor/widgets/register', array($this, 'register_elementor_widgets'));
        
        // Register Gutenberg blocks
        add_action('init', array($this, 'register_gutenberg_blocks'));
        
        // Register shortcodes
        add_action('init', array($this, 'register_shortcodes'));
    }
    
    /**
     * Load plugin dependencies
     */
    private function load_dependencies() {
        // Core classes
        require_once LOVABLE_WP_EXPORTER_PLUGIN_DIR . 'includes/class-analyzer.php';
        require_once LOVABLE_WP_EXPORTER_PLUGIN_DIR . 'includes/class-converter.php';
        require_once LOVABLE_WP_EXPORTER_PLUGIN_DIR . 'includes/class-component-registry.php';
        require_once LOVABLE_WP_EXPORTER_PLUGIN_DIR . 'includes/class-asset-exporter.php';
        require_once LOVABLE_WP_EXPORTER_PLUGIN_DIR . 'includes/class-elementor-widgets.php';
        require_once LOVABLE_WP_EXPORTER_PLUGIN_DIR . 'includes/class-gutenberg-blocks.php';
        require_once LOVABLE_WP_EXPORTER_PLUGIN_DIR . 'includes/class-shortcodes.php';
        
        // Admin
        if (is_admin()) {
            require_once LOVABLE_WP_EXPORTER_PLUGIN_DIR . 'admin/class-admin.php';
        }
    }
    
    /**
     * Activation hook
     */
    public function activate() {
        // Create necessary database tables
        $this->create_tables();
        
        // Create upload directory for exports
        $upload_dir = wp_upload_dir();
        $export_dir = $upload_dir['basedir'] . '/lovable-exports';
        
        if (!file_exists($export_dir)) {
            wp_mkdir_p($export_dir);
        }
        
        // Set default options
        add_option('lovable_wp_exporter_version', LOVABLE_WP_EXPORTER_VERSION);
        add_option('lovable_wp_exporter_settings', array(
            'auto_enqueue_styles' => true,
            'convert_tailwind' => true,
            'preserve_react_state' => false,
            'elementor_integration' => true,
            'gutenberg_integration' => true,
        ));
        
        flush_rewrite_rules();
    }
    
    /**
     * Deactivation hook
     */
    public function deactivate() {
        flush_rewrite_rules();
    }
    
    /**
     * Create custom database tables
     */
    private function create_tables() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        $table_name = $wpdb->prefix . 'lovable_exports';
        
        $sql = "CREATE TABLE $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            export_name varchar(255) NOT NULL,
            source_type varchar(50) NOT NULL,
            components longtext NOT NULL,
            styles longtext NOT NULL,
            assets longtext NOT NULL,
            status varchar(20) NOT NULL DEFAULT 'pending',
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
    
    /**
     * Load plugin textdomain
     */
    public function load_textdomain() {
        load_plugin_textdomain(
            'lovable-wp-exporter',
            false,
            dirname(LOVABLE_WP_EXPORTER_PLUGIN_BASENAME) . '/languages'
        );
    }
    
    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_menu_page(
            __('Lovable Exporter', 'lovable-wp-exporter'),
            __('Lovable Exporter', 'lovable-wp-exporter'),
            'manage_options',
            'lovable-wp-exporter',
            array($this, 'render_admin_page'),
            'dashicons-code-standards',
            30
        );
        
        add_submenu_page(
            'lovable-wp-exporter',
            __('New Export', 'lovable-wp-exporter'),
            __('New Export', 'lovable-wp-exporter'),
            'manage_options',
            'lovable-wp-exporter-new',
            array($this, 'render_new_export_page')
        );
        
        add_submenu_page(
            'lovable-wp-exporter',
            __('Export History', 'lovable-wp-exporter'),
            __('Export History', 'lovable-wp-exporter'),
            'manage_options',
            'lovable-wp-exporter-history',
            array($this, 'render_history_page')
        );
        
        add_submenu_page(
            'lovable-wp-exporter',
            __('Settings', 'lovable-wp-exporter'),
            __('Settings', 'lovable-wp-exporter'),
            'manage_options',
            'lovable-wp-exporter-settings',
            array($this, 'render_settings_page')
        );
    }
    
    /**
     * Render admin page
     */
    public function render_admin_page() {
        include LOVABLE_WP_EXPORTER_PLUGIN_DIR . 'admin/views/dashboard.php';
    }
    
    /**
     * Render new export page
     */
    public function render_new_export_page() {
        include LOVABLE_WP_EXPORTER_PLUGIN_DIR . 'admin/views/new-export.php';
    }
    
    /**
     * Render history page
     */
    public function render_history_page() {
        include LOVABLE_WP_EXPORTER_PLUGIN_DIR . 'admin/views/history.php';
    }
    
    /**
     * Render settings page
     */
    public function render_settings_page() {
        include LOVABLE_WP_EXPORTER_PLUGIN_DIR . 'admin/views/settings.php';
    }
    
    /**
     * Enqueue admin assets
     */
    public function enqueue_admin_assets($hook) {
        if (strpos($hook, 'lovable-wp-exporter') === false) {
            return;
        }
        
        wp_enqueue_style(
            'lovable-wp-exporter-admin',
            LOVABLE_WP_EXPORTER_PLUGIN_URL . 'admin/css/admin.css',
            array(),
            LOVABLE_WP_EXPORTER_VERSION
        );
        
        wp_enqueue_script(
            'lovable-wp-exporter-admin',
            LOVABLE_WP_EXPORTER_PLUGIN_URL . 'admin/js/admin.js',
            array('jquery'),
            LOVABLE_WP_EXPORTER_VERSION,
            true
        );
        
        wp_localize_script('lovable-wp-exporter-admin', 'lovableExporter', array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('lovable_exporter_nonce'),
            'strings' => array(
                'confirmDelete' => __('Are you sure you want to delete this export?', 'lovable-wp-exporter'),
                'exportSuccess' => __('Export completed successfully!', 'lovable-wp-exporter'),
                'exportError' => __('Export failed. Please try again.', 'lovable-wp-exporter'),
            )
        ));
    }
    
    /**
     * Handle export parse AJAX
     */
    public function handle_export_parse() {
        check_ajax_referer('lovable_exporter_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized');
        }
        
        $source_code = isset($_POST['source_code']) ? sanitize_textarea_field($_POST['source_code']) : '';
        $source_type = isset($_POST['source_type']) ? sanitize_text_field($_POST['source_type']) : 'lovable';
        
        if (empty($source_code)) {
            wp_send_json_error('No source code provided');
        }
        
        $analyzer = new Lovable_Analyzer();
        $result = $analyzer->parse($source_code, $source_type);
        
        wp_send_json_success($result);
    }
    
    /**
     * Handle export convert AJAX
     */
    public function handle_export_convert() {
        check_ajax_referer('lovable_exporter_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized');
        }
        
        $parsed_data = isset($_POST['parsed_data']) ? json_decode(stripslashes($_POST['parsed_data']), true) : array();
        $output_format = isset($_POST['output_format']) ? sanitize_text_field($_POST['output_format']) : 'all';
        
        $converter = new Lovable_Converter();
        $result = $converter->convert($parsed_data, $output_format);
        
        wp_send_json_success($result);
    }
    
    /**
     * Handle export import AJAX
     */
    public function handle_export_import() {
        check_ajax_referer('lovable_exporter_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized');
        }
        
        $export_data = isset($_POST['export_data']) ? json_decode(stripslashes($_POST['export_data']), true) : array();
        $import_as = isset($_POST['import_as']) ? sanitize_text_field($_POST['import_as']) : 'shortcode';
        
        $registry = new Lovable_Component_Registry();
        $result = $registry->register($export_data, $import_as);
        
        wp_send_json_success($result);
    }
    
    /**
     * Register Elementor widgets
     */
    public function register_elementor_widgets($widgets_manager) {
        if (!did_action('elementor/loaded')) {
            return;
        }
        
        require_once LOVABLE_WP_EXPORTER_PLUGIN_DIR . 'includes/elementor/class-lovable-widget.php';
        
        $widgets_manager->register(new \Lovable_Elementor_Widget());
    }
    
    /**
     * Register Gutenberg blocks
     */
    public function register_gutenberg_blocks() {
        register_block_type(LOVABLE_WP_EXPORTER_PLUGIN_DIR . 'build/lovable-component');
    }
    
    /**
     * Register shortcodes
     */
    public function register_shortcodes() {
        add_shortcode('lovable_component', array('Lovable_Shortcodes', 'render_component'));
        add_shortcode('lovable_section', array('Lovable_Shortcodes', 'render_section'));
        add_shortcode('lovable_page', array('Lovable_Shortcodes', 'render_page'));
    }
}

// Initialize the plugin
function lovable_wp_exporter_init() {
    return Lovable_WP_Exporter::get_instance();
}
add_action('plugins_loaded', 'lovable_wp_exporter_init');
