<?php
/**
 * Uninstall Handler
 * 
 * Se ejecuta cuando el plugin es desinstalado
 */

if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Clear database tables
global $wpdb;

$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}lovable_exports");
$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}lovable_asset_mapping");

// Clear options
delete_option('lovable_wp_exporter_version');
delete_option('lovable_wp_exporter_settings');

// Clear uploaded files
$upload_dir = wp_upload_dir();
$export_dir = $upload_dir['basedir'] . '/lovable-exports';
$assets_dir = $upload_dir['basedir'] . '/lovable-assets';

if (file_exists($export_dir)) {
    wp_delete_directory($export_dir);
}

if (file_exists($assets_dir)) {
    wp_delete_directory($assets_dir);
}

// Clear any transients
delete_transient('lovable_export_cache');
