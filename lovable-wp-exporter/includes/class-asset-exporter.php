<?php
/**
 * Asset Exporter Class
 * 
 * Gestiona la exportación e importación de assets (imágenes, fuentes, iconos)
 */

if (!defined('ABSPATH')) {
    exit;
}

class Lovable_Asset_Exporter {
    
    /**
     * Extract and import assets from source
     */
    public function extract_assets($source_code, $source_dir = null) {
        $assets = array(
            'images' => array(),
            'fonts' => array(),
            'icons' => array(),
            'other' => array(),
        );
        
        // Extract image URLs
        $image_pattern = '/(?:src|href|background-image)\s*=\s*["\']([^"\']+\.(?:png|jpg|jpeg|gif|svg|webp|ico))["\']/i';
        
        if (preg_match_all($image_pattern, $source_code, $matches)) {
            foreach ($matches[1] as $url) {
                $asset_info = $this->process_asset_url($url, $source_dir);
                if ($asset_info) {
                    $assets['images'][] = $asset_info;
                }
            }
        }
        
        // Extract import statements for assets
        $import_pattern = '/import\s+.*\s+from\s+["\']([^"\']+\.(?:png|jpg|jpeg|gif|svg|webp))["\']/i';
        
        if (preg_match_all($import_pattern, $source_code, $matches)) {
            foreach ($matches[1] as $import_path) {
                $asset_info = $this->process_asset_url($import_path, $source_dir);
                if ($asset_info) {
                    $assets['images'][] = $asset_info;
                }
            }
        }
        
        // Extract font URLs
        $font_pattern = '/(?:src|url)\s*[\(]?\s*["\']?([^"\')\s]+\.(?:woff|woff2|ttf|otf|eot))["\']?\s*\)?/i';
        
        if (preg_match_all($font_pattern, $source_code, $matches)) {
            foreach ($matches[1] as $url) {
                $assets['fonts'][] = array(
                    'url' => $url,
                    'type' => 'font',
                );
            }
        }
        
        // Extract Lucide icons
        $icon_pattern = '/import\s+{\s*([^}]+)\s*}\s+from\s+[\'"]lucide-react[\'"]/';
        
        if (preg_match($icon_pattern, $source_code, $match)) {
            $icons = array_map('trim', explode(',', $match[1]));
            foreach ($icons as $icon) {
                $assets['icons'][] = array(
                    'name' => $icon,
                    'library' => 'lucide',
                );
            }
        }
        
        return $assets;
    }
    
    /**
     * Process asset URL
     */
    private function process_asset_url($url, $source_dir = null) {
        $asset = array(
            'url' => $url,
            'path' => null,
            'filename' => basename($url),
            'type' => $this->get_asset_type($url),
            'size' => null,
        );
        
        // Check if it's a local path
        if (strpos($url, 'http') !== 0 && $source_dir) {
            $local_path = $source_dir . '/' . ltrim($url, './');
            
            if (file_exists($local_path)) {
                $asset['path'] = $local_path;
                $asset['size'] = filesize($local_path);
            }
        }
        
        return $asset;
    }
    
    /**
     * Get asset type from filename
     */
    private function get_asset_type($filename) {
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        $image_extensions = array('png', 'jpg', 'jpeg', 'gif', 'svg', 'webp', 'ico');
        $font_extensions = array('woff', 'woff2', 'ttf', 'otf', 'eot');
        
        if (in_array($extension, $image_extensions)) {
            return 'image';
        } elseif (in_array($extension, $font_extensions)) {
            return 'font';
        }
        
        return 'other';
    }
    
    /**
     * Import assets to WordPress media library
     */
    public function import_to_media_library($assets) {
        $imported = array(
            'success' => array(),
            'failed' => array(),
        );
        
        require_once(ABSPATH . 'wp-admin/includes/media.php');
        require_once(ABSPATH . 'wp-admin/includes/file.php');
        require_once(ABSPATH . 'wp-admin/includes/image.php');
        
        foreach ($assets as $asset) {
            $result = $this->import_single_asset($asset);
            
            if ($result['success']) {
                $imported['success'][] = array_merge($asset, $result['data']);
            } else {
                $imported['failed'][] = array_merge($asset, array('error' => $result['error']));
            }
        }
        
        return $imported;
    }
    
    /**
     * Import single asset
     */
    private function import_single_asset($asset) {
        $upload_dir = wp_upload_dir();
        $export_dir = $upload_dir['basedir'] . '/lovable-assets';
        
        // Create export directory
        if (!file_exists($export_dir)) {
            wp_mkdir_p($export_dir);
        }
        
        // Download or copy asset
        $file_url = $asset['url'];
        $file_name = sanitize_file_name($asset['filename']);
        $file_path = $export_dir . '/' . $file_name;
        
        // Check if it's a local file
        if ($asset['path'] && file_exists($asset['path'])) {
            copy($asset['path'], $file_path);
        } else {
            // Download from URL
            $downloaded = download_url($file_url);
            
            if (is_wp_error($downloaded)) {
                return array(
                    'success' => false,
                    'error' => $downloaded->get_error_message(),
                );
            }
            
            copy($downloaded, $file_path);
            @unlink($downloaded);
        }
        
        // Add to media library
        $file_type = wp_check_filetype($file_name);
        $attachment = array(
            'post_mime_type' => $file_type['type'],
            'post_title' => preg_replace('/\.[^.]+$/', '', $file_name),
            'post_content' => '',
            'post_status' => 'inherit',
        );
        
        $attach_id = wp_insert_attachment($attachment, $file_path);
        
        if (is_wp_error($attach_id)) {
            return array(
                'success' => false,
                'error' => $attach_id->get_error_message(),
            );
        }
        
        // Generate attachment metadata
        $attach_data = wp_generate_attachment_metadata($attach_id, $file_path);
        wp_update_attachment_metadata($attach_id, $attach_data);
        
        return array(
            'success' => true,
            'data' => array(
                'id' => $attach_id,
                'url' => wp_get_attachment_url($attach_id),
                'path' => $file_path,
            ),
        );
    }
    
    /**
     * Replace asset URLs in content
     */
    public function replace_asset_urls($content, $asset_mapping) {
        foreach ($asset_mapping as $original_url => $new_url) {
            $content = str_replace($original_url, $new_url, $content);
        }
        
        return $content;
    }
    
    /**
     * Export assets to ZIP
     */
    public function export_to_zip($assets, $destination = null) {
        if (!class_exists('ZipArchive')) {
            return array(
                'success' => false,
                'error' => 'ZipArchive not available',
            );
        }
        
        $upload_dir = wp_upload_dir();
        $export_dir = $upload_dir['basedir'] . '/lovable-exports';
        
        if (!$destination) {
            $destination = $export_dir . '/assets-' . time() . '.zip';
        }
        
        $zip = new ZipArchive();
        
        if ($zip->open($destination, ZipArchive::CREATE) !== TRUE) {
            return array(
                'success' => false,
                'error' => 'Cannot create ZIP file',
            );
        }
        
        foreach ($assets as $asset) {
            if ($asset['path'] && file_exists($asset['path'])) {
                $zip->addFile($asset['path'], 'assets/' . $asset['filename']);
            }
        }
        
        $zip->close();
        
        return array(
            'success' => true,
            'path' => $destination,
            'url' => str_replace($upload_dir['basedir'], $upload_dir['baseurl'], $destination),
        );
    }
    
    /**
     * Get asset from WordPress media library by original URL
     */
    public function get_media_by_original_url($original_url) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'lovable_asset_mapping';
        
        $result = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE original_url = %s",
            $original_url
        ));
        
        if ($result) {
            return array(
                'id' => $result->attachment_id,
                'url' => wp_get_attachment_url($result->attachment_id),
                'original_url' => $result->original_url,
            );
        }
        
        return null;
    }
    
    /**
     * Save asset mapping to database
     */
    public function save_asset_mapping($original_url, $attachment_id) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'lovable_asset_mapping';
        
        $wpdb->insert(
            $table_name,
            array(
                'original_url' => $original_url,
                'attachment_id' => $attachment_id,
                'created_at' => current_time('mysql'),
            )
        );
        
        return $wpdb->insert_id;
    }
}
