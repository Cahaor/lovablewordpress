<?php
/**
 * API Client for Lovable WP Pro Cloud
 */

if (!defined('ABSPATH')) exit;

class Lovable_WP_Pro_API_Client {
    
    private $api_url;
    private $license_key;
    
    public function __construct() {
        $this->api_url = get_option('lovable_wp_pro_api_url', 'https://api.lovablewp.pro');
        $this->license_key = get_option('lovable_wp_pro_license_key', '');
    }
    
    /**
     * Make API request
     */
    private function request($endpoint, $method = 'GET', $data = []) {
        $url = trailingslashit($this->api_url) . ltrim($endpoint, '/');
        
        $args = [
            'method' => strtoupper($method),
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $this->license_key,
            ],
            'timeout' => 30,
        ];
        
        if (!empty($data) && in_array(strtoupper($method), ['POST', 'PUT', 'PATCH'])) {
            $args['body'] = json_encode($data);
        }
        
        $response = wp_remote_request($url, $args);
        
        if (is_wp_error($response)) {
            return ['success' => false, 'error' => $response->get_error_message()];
        }
        
        $body = wp_remote_retrieve_body($response);
        $code = wp_remote_retrieve_response_code($response);
        
        if ($code >= 200 && $code < 300) {
            return ['success' => true, 'data' => json_decode($body, true)];
        }
        
        return ['success' => false, 'error' => 'API Error: ' . $code, 'data' => json_decode($body, true)];
    }
    
    /**
     * Validate license
     */
    public function validate_license() {
        $result = $this->request('/api/license/validate', 'POST', [
            'license_key' => $this->license_key,
            'site_url' => home_url(),
        ]);
        
        if ($result['success']) {
            update_option('lovable_wp_pro_connected', true);
            update_option('lovable_wp_pro_license_valid', true);
        }
        
        return $result;
    }
    
    /**
     * Sync widgets from cloud
     */
    public function sync_widgets() {
        $result = $this->request('/api/wordpress/sync?site_url=' . urlencode(home_url()));
        
        if (!$result['success']) {
            return $result;
        }
        
        $widgets = $result['data']['widgets'] ?? [];
        
        if (empty($widgets)) {
            return ['success' => true, 'message' => 'No widgets to sync'];
        }
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'lovable_widgets';
        
        foreach ($widgets as $widget) {
            // Check if widget exists
            $existing = $wpdb->get_row($wpdb->prepare(
                "SELECT id FROM $table_name WHERE widget_name = %s",
                $widget['name']
            ));
            
            if ($existing) {
                // Update
                $wpdb->update(
                    $table_name,
                    [
                        'html' => $widget['html'],
                        'css' => $widget['css'],
                        'controls' => json_encode($widget['controls'] ?? []),
                        'updated_at' => current_time('mysql'),
                    ],
                    ['id' => $existing->id]
                );
            } else {
                // Insert
                $wpdb->insert(
                    $table_name,
                    [
                        'widget_name' => $widget['name'],
                        'widget_type' => $widget['type'] ?? 'custom',
                        'html' => $widget['html'],
                        'css' => $widget['css'],
                        'controls' => json_encode($widget['controls'] ?? []),
                        'status' => 'active',
                        'created_at' => current_time('mysql'),
                    ]
                );
            }
        }
        
        // Clear Elementor cache
        delete_transient('elementor-widgets');
        
        return ['success' => true, 'message' => 'Synced ' . count($widgets) . ' widgets'];
    }
    
    /**
     * Push widget to cloud
     */
    public function push_widget($widget_data) {
        return $this->request('/api/wordpress/widgets', 'POST', $widget_data);
    }
    
    /**
     * Get project from cloud
     */
    public function get_project($project_id) {
        return $this->request('/api/projects/' . $project_id);
    }
    
    /**
     * Convert ZIP (send to backend for processing)
     */
    public function convert_zip($file_path) {
        if (!file_exists($file_path)) {
            return ['success' => false, 'error' => 'File not found'];
        }
        
        $file_data = base64_encode(file_get_contents($file_path));
        
        $result = $this->request('/api/converter/convert', 'POST', [
            'file' => $file_data,
            'site_url' => home_url(),
        ]);
        
        return $result;
    }
    
    /**
     * Disconnect site
     */
    public function disconnect() {
        $result = $this->request('/api/wordpress/disconnect', 'POST', [
            'site_url' => home_url(),
        ]);
        
        delete_option('lovable_wp_pro_connected');
        delete_option('lovable_wp_pro_license_valid');
        
        return $result;
    }
}
