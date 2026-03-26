<?php
/**
 * My Designs Admin Page
 */

if (!defined('ABSPATH')) exit;

global $wpdb;
$table_name = $wpdb->prefix . 'lovable_designs';
$designs = $wpdb->get_results("SELECT * FROM $table_name ORDER BY created_at DESC", ARRAY_A);
?>

<div class="wrap lovable-designs">
    <h1 class="wp-heading-inline">
        <?php _e('Mis Diseños Importados', 'lovable-elementor-pro'); ?>
    </h1>
    
    <a href="<?php echo admin_url('admin.php?page=lovable-elementor-pro'); ?>" class="page-title-action">
        <?php _e('Importar Nuevo', 'lovable-elementor-pro'); ?>
    </a>
    
    <hr class="wp-header-end">
    
    <?php if (empty($designs)) : ?>
        <div class="lovable-empty-state">
            <span class="dashicons dashicons-art"></span>
            <h2><?php _e('No hay diseños importados', 'lovable-elementor-pro'); ?></h2>
            <p><?php _e('Importa tu primer diseño desde Lovable para comenzar.', 'lovable-elementor-pro'); ?></p>
            <a href="<?php echo admin_url('admin.php?page=lovable-elementor-pro'); ?>" class="button button-primary">
                <?php _e('Importar Diseño', 'lovable-elementor-pro'); ?>
            </a>
        </div>
    <?php else : ?>
        <table class="wp-list-table widefat fixed striped table-view-list">
            <thead>
                <tr>
                    <th><?php _e('Nombre', 'lovable-elementor-pro'); ?></th>
                    <th><?php _e('Componentes', 'lovable-elementor-pro'); ?></th>
                    <th><?php _e('Estado', 'lovable-elementor-pro'); ?></th>
                    <th><?php _e('Fecha', 'lovable-elementor-pro'); ?></th>
                    <th><?php _e('Acciones', 'lovable-elementor-pro'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($designs as $design) : 
                    $components = json_decode($design['components'], true);
                    $component_count = is_array($components) ? count($components) : 0;
                ?>
                    <tr>
                        <td>
                            <strong><?php echo esc_html($design['design_name']); ?></strong>
                        </td>
                        <td>
                            <?php echo esc_html($component_count); ?> <?php _e('widgets', 'lovable-elementor-pro'); ?>
                        </td>
                        <td>
                            <span class="lovable-badge lovable-badge-<?php echo esc_attr($design['status']); ?>">
                                <?php echo esc_html(ucfirst($design['status'])); ?>
                            </span>
                        </td>
                        <td>
                            <?php echo esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($design['created_at']))); ?>
                        </td>
                        <td>
                            <div class="lovable-actions">
                                <a href="<?php echo admin_url('admin.php?page=lovable-elementor-pro-designs&action=activate&id=' . $design['id']); ?>" class="button button-small">
                                    <?php _e('Activar/Desactivar', 'lovable-elementor-pro'); ?>
                                </a>
                                <a href="<?php echo wp_nonce_url(admin_url('admin.php?page=lovable-elementor-pro-designs&action=delete&id=' . $design['id']), 'lovable_delete_design'); ?>" class="button button-small button-link-delete" onclick="return confirm('<?php esc_attr_e('¿Eliminar este diseño?', 'lovable-elementor-pro'); ?>')">
                                    <?php _e('Eliminar', 'lovable-elementor-pro'); ?>
                                </a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
    
    <div class="lovable-info-box">
        <h3><?php _e('ℹ️ Información', 'lovable-elementor-pro'); ?></h3>
        <p>
            <?php _e('Los diseños importados aparecen como widgets en Elementor bajo la categoría "Lovable Components".', 'lovable-elementor-pro'); ?>
        </p>
        <p>
            <?php _e('Después de importar un nuevo diseño, recarga la página de Elementor para ver los nuevos widgets.', 'lovable-elementor-pro'); ?>
        </p>
    </div>
</div>

<?php
// Handle delete action
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    check_admin_referer('lovable_delete_design');
    
    $id = intval($_GET['id']);
    $wpdb->delete($table_name, ['id' => $id]);
    
    echo '<div class="notice notice-success is-dismissible"><p>' . __('Design deleted.', 'lovable-elementor-pro') . '</p></div>';
}

// Handle activate/deactivate
if (isset($_GET['action']) && $_GET['action'] === 'activate' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $design = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $id), ARRAY_A);
    
    if ($design) {
        $new_status = $design['status'] === 'active' ? 'inactive' : 'active';
        $wpdb->update($table_name, ['status' => $new_status], ['id' => $id]);
        
        echo '<div class="notice notice-success is-dismissible"><p>' . sprintf(__('Design %s.', 'lovable-elementor-pro'), $new_status === 'active' ? __('activated', 'lovable-elementor-pro') : __('deactivated', 'lovable-elementor-pro')) . '</p></div>';
    }
}
?>
