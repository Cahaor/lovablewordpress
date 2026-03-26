<?php
/**
 * Dashboard View
 */

if (!defined('ABSPATH')) exit;
?>

<div class="wrap lovable-dashboard">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    
    <?php if (!$connected) : ?>
        <div class="lovable-notice lovable-notice-warning">
            <h3><?php _e('⚠️ Connect your site to Lovable WP Pro', 'lovable-wp-pro'); ?></h3>
            <p><?php _e('Enter your license key to start importing widgets from Lovable.', 'lovable-wp-pro'); ?></p>
            <a href="<?php echo admin_url('admin.php?page=lovable-wp-pro-settings'); ?>" class="button button-primary">
                <?php _e('Go to Settings', 'lovable-wp-pro'); ?>
            </a>
        </div>
    <?php else : ?>
        <div class="lovable-notice lovable-notice-success">
            <h3><?php _e('✅ Site Connected', 'lovable-wp-pro'); ?></h3>
            <p><?php _e('Your site is connected to Lovable WP Pro cloud.', 'lovable-wp-pro'); ?></p>
        </div>
    <?php endif; ?>
    
    <div class="lovable-stats-grid">
        <div class="lovable-stat-card">
            <div class="stat-icon dashicons dashicons-admin-multisite"></div>
            <div class="stat-content">
                <h3><?php echo number_format($widget_count); ?></h3>
                <p><?php _e('Active Widgets', 'lovable-wp-pro'); ?></p>
            </div>
        </div>
        
        <div class="lovable-stat-card">
            <div class="stat-icon dashicons dashicons-cloud"></div>
            <div class="stat-content">
                <h3><?php echo $connected ? __('Connected', 'lovable-wp-pro') : __('Not Connected', 'lovable-wp-pro'); ?></h3>
                <p><?php _e('Cloud Status', 'lovable-wp-pro'); ?></p>
            </div>
        </div>
        
        <div class="lovable-stat-card">
            <div class="stat-icon dashicons dashicons-update"></div>
            <div class="stat-content">
                <h3><?php _e('Auto-Sync', 'lovable-wp-pro'); ?></h3>
                <p><?php _e('Hourly', 'lovable-wp-pro'); ?></p>
            </div>
        </div>
    </div>
    
    <div class="lovable-actions-grid">
        <div class="lovable-action-card">
            <h3><?php _e('🔄 Sync Widgets', 'lovable-wp-pro'); ?></h3>
            <p><?php _e('Fetch latest widgets from Lovable cloud', 'lovable-wp-pro'); ?></p>
            <button id="lovable-sync-btn" class="button button-primary button-large">
                <?php _e('Sync Now', 'lovable-wp-pro'); ?>
            </button>
        </div>
        
        <div class="lovable-action-card">
            <h3><?php _e('📦 Import ZIP', 'lovable-wp-pro'); ?></h3>
            <p><?php _e('Convert Lovable ZIP to widgets', 'lovable-wp-pro'); ?></p>
            <button id="lovable-import-btn" class="button button-secondary button-large">
                <?php _e('Upload ZIP', 'lovable-wp-pro'); ?>
            </button>
        </div>
        
        <div class="lovable-action-card">
            <h3><?php _e('🎨 Manage Widgets', 'lovable-wp-pro'); ?></h3>
            <p><?php _e('View and manage all widgets', 'lovable-wp-pro'); ?></p>
            <a href="<?php echo admin_url('admin.php?page=lovable-wp-pro-widgets'); ?>" class="button button-secondary button-large">
                <?php _e('View Widgets', 'lovable-wp-pro'); ?>
            </a>
        </div>
    </div>
    
    <div class="lovable-section">
        <h2><?php _e('📖 Quick Start Guide', 'lovable-wp-pro'); ?></h2>
        <div class="lovable-steps">
            <div class="lovable-step">
                <div class="step-number">1</div>
                <h4><?php _e('Get License', 'lovable-wp-pro'); ?></h4>
                <p><?php _e('Sign up at Lovable WP Pro and get your license key', 'lovable-wp-pro'); ?></p>
            </div>
            <div class="lovable-step">
                <div class="step-number">2</div>
                <h4><?php _e('Connect Site', 'lovable-wp-pro'); ?></h4>
                <p><?php _e('Enter license in Settings to connect your site', 'lovable-wp-pro'); ?></p>
            </div>
            <div class="lovable-step">
                <div class="step-number">3</div>
                <h4><?php _e('Design in Lovable', 'lovable-wp-pro'); ?></h4>
                <p><?php _e('Create your design in Lovable and export as ZIP', 'lovable-wp-pro'); ?></p>
            </div>
            <div class="lovable-step">
                <div class="step-number">4</div>
                <h4><?php _e('Import & Use', 'lovable-wp-pro'); ?></h4>
                <p><?php _e('Import ZIP or sync from cloud, then use in Elementor', 'lovable-wp-pro'); ?></p>
            </div>
        </div>
    </div>
</div>

<!-- Import Modal -->
<div id="lovable-import-modal" style="display:none;">
    <div class="lovable-modal-content">
        <h3><?php _e('Import Lovable ZIP', 'lovable-wp-pro'); ?></h3>
        <div id="lovable-drop-zone">
            <p><?php _e('Drag & drop ZIP file here or click to browse', 'lovable-wp-pro'); ?></p>
            <input type="file" id="lovable-zip-input" accept=".zip" />
        </div>
        <div id="lovable-import-progress" style="display:none;">
            <div class="lovable-progress-bar">
                <div class="lovable-progress-fill"></div>
            </div>
            <p><?php _e('Converting...', 'lovable-wp-pro'); ?></p>
        </div>
        <div id="lovable-import-result"></div>
    </div>
</div>
