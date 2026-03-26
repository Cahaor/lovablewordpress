<?php
/**
 * Admin Dashboard for Lovable WP Pro
 */

if (!defined('ABSPATH')) exit;

class Lovable_WP_Pro_Admin {
    
    public function __construct() {
        add_action('admin_menu', [$this, 'add_menu']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_assets']);
        add_action('wp_ajax_lovable_validate_license', [$this, 'ajax_validate_license']);
        add_action('wp_ajax_lovable_sync_widgets', [$this, 'ajax_sync_widgets']);
        add_action('wp_ajax_lovable_convert_zip', [$this, 'ajax_convert_zip']);
    }
    
    public function add_menu() {
        add_menu_page(
            __('Lovable Pro', 'lovable-wp-pro'),
            __('Lovable Pro', 'lovable-wp-pro'),
            'manage_options',
            'lovable-wp-pro',
            [$this, 'render_dashboard'],
            'dashicons-code-standards',
            30
        );
        
        add_submenu_page(
            'lovable-wp-pro',
            __('Dashboard', 'lovable-wp-pro'),
            __('Dashboard', 'lovable-wp-pro'),
            'manage_options',
            'lovable-wp-pro',
            [$this, 'render_dashboard']
        );
        
        add_submenu_page(
            'lovable-wp-pro',
            __('Widgets', 'lovable-wp-pro'),
            __('Widgets', 'lovable-wp-pro'),
            'manage_options',
            'lovable-wp-pro-widgets',
            [$this, 'render_widgets_page']
        );
        
        add_submenu_page(
            'lovable-wp-pro',
            __('Settings', 'lovable-wp-pro'),
            __('Settings', 'lovable-wp-pro'),
            'manage_options',
            'lovable-wp-pro-settings',
            [$this, 'render_settings_page']
        );
    }
    
    public function enqueue_assets($hook) {
        if (strpos($hook, 'lovable-wp-pro') === false) {
            return;
        }
        
        wp_enqueue_style(
            'lovable-admin',
            LOVABLE_WP_PRO_URL . 'admin/css/admin.css',
            [],
            LOVABLE_WP_PRO_VERSION
        );
        
        wp_enqueue_script(
            'lovable-admin',
            LOVABLE_WP_PRO_URL . 'admin/js/admin.js',
            ['jquery'],
            LOVABLE_WP_PRO_VERSION,
            true
        );
        
        wp_localize_script('lovable-admin', 'lovableAdmin', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('lovable_admin_nonce'),
            'strings' => [
                'confirmDelete' => __('Are you sure?', 'lovable-wp-pro'),
                'syncSuccess' => __('Widgets synced successfully!', 'lovable-wp-pro'),
                'syncError' => __('Sync failed.', 'lovable-wp-pro'),
            ]
        ]);
    }
    
    public function render_dashboard() {
        $connected = get_option('lovable_wp_pro_connected', false);
        $license = get_option('lovable_wp_pro_license_key', '');
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'lovable_widgets';
        $widget_count = $wpdb->get_var("SELECT COUNT(*) FROM $table_name WHERE status = 'active'");
        
        include LOVABLE_WP_PRO_PATH . 'admin/views/dashboard.php';
    }
    
    public function render_widgets_page() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'lovable_widgets';
        $widgets = $wpdb->get_results("SELECT * FROM $table_name ORDER BY created_at DESC", ARRAY_A);
        
        include LOVABLE_WP_PRO_PATH . 'admin/views/widgets.php';
    }
    
    public function render_settings_page() {
        $license = get_option('lovable_wp_pro_license_key', '');
        $api_url = get_option('lovable_wp_pro_api_url', 'https://api.lovablewp.pro');
        $connected = get_option('lovable_wp_pro_connected', false);
        
        include LOVABLE_WP_PRO_PATH . 'admin/views/settings.php';
    }
    
    public function ajax_validate_license() {
        check_ajax_referer('lovable_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized');
        }
        
        $license_key = sanitize_text_field($_POST['license_key'] ?? '');
        
        if (empty($license_key)) {
            wp_send_json_error('License key required');
        }
        
        update_option('lovable_wp_pro_license_key', $license_key);
        
        $api_client = new Lovable_WP_Pro_API_Client();
        $result = $api_client->validate_license();
        
        if ($result['success']) {
            wp_send_json_success($result);
        } else {
            wp_send_json_error($result['error'] ?? 'Validation failed');
        }
    }
    
    public function ajax_sync_widgets() {
        check_ajax_referer('lovable_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized');
        }
        
        $api_client = new Lovable_WP_Pro_API_Client();
        $result = $api_client->sync_widgets();
        
        if ($result['success']) {
            wp_send_json_success($result);
        } else {
            wp_send_json_error($result['error'] ?? 'Sync failed');
        }
    }
    
    public function ajax_convert_zip() {
        check_ajax_referer('lovable_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized');
        }
        
        if (!isset($_FILES['zip_file'])) {
            wp_send_json_error('No file uploaded');
        }
        
        $file = $_FILES['zip_file'];
        $upload_dir = wp_upload_dir();
        $temp_path = $upload_dir['tmp_path'] ?? sys_get_temp_dir();
        $file_path = $temp_path . '/' . uniqid() . '.zip';
        
        if (!move_uploaded_file($file['tmp_name'], $file_path)) {
            wp_send_json_error('Upload failed');
        }
        
        $api_client = new Lovable_WP_Pro_API_Client();
        $result = $api_client->convert_zip($file_path);
        
        unlink($file_path);
        
        if ($result['success']) {
            wp_send_json_success($result['data']);
        } else {
            wp_send_json_error($result['error'] ?? 'Conversion failed');
        }
    }
}
