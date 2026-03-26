<?php
/**
 * History View
 */

if (!defined('ABSPATH')) {
    exit;
}

global $wpdb;
$table_name = $wpdb->prefix . 'lovable_exports';

// Handle actions
$action = isset($_GET['action']) ? sanitize_text_field($_GET['action']) : '';
$export_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Delete action
if ($action === 'delete' && $export_id && wp_verify_nonce($_GET['_wpnonce'], 'lovable_delete_export')) {
    $wpdb->delete($table_name, array('id' => $export_id));
    echo '<div class="notice notice-success is-dismissible"><p>' . __('Exportación eliminada correctamente.', 'lovable-wp-exporter') . '</p></div>';
}

// Get all exports
$paged = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
$per_page = 20;
$offset = ($paged - 1) * $per_page;

$total_items = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");

$exports = $wpdb->get_results($wpdb->prepare(
    "SELECT * FROM $table_name ORDER BY created_at DESC LIMIT %d OFFSET %d",
    $per_page,
    $offset
));

$total_pages = ceil($total_items / $per_page);
?>

<div class="wrap lovable-history">
    <h1 class="wp-heading-inline">
        <?php _e('Historial de Exportaciones', 'lovable-wp-exporter'); ?>
    </h1>
    
    <a href="<?php echo admin_url('admin.php?page=lovable-wp-exporter-new'); ?>" class="page-title-action">
        <?php _e('Nueva Exportación', 'lovable-wp-exporter'); ?>
    </a>
    
    <hr class="wp-header-end">
    
    <?php if (empty($exports)) : ?>
        <div class="lovable-empty-state">
            <span class="dashicons dashicons-archive"></span>
            <h2><?php _e('No hay exportaciones', 'lovable-wp-exporter'); ?></h2>
            <p><?php _e('Aún no has creado ninguna exportación. ¡Comienza ahora!', 'lovable-wp-exporter'); ?></p>
            <a href="<?php echo admin_url('admin.php?page=lovable-wp-exporter-new'); ?>" class="button button-primary">
                <?php _e('Crear Primera Exportación', 'lovable-wp-exporter'); ?>
            </a>
        </div>
    <?php else : ?>
        <form method="get" class="lovable-filters">
            <input type="hidden" name="page" value="lovable-wp-exporter-history" />
            
            <div class="lovable-filter-group">
                <label for="filter-status"><?php _e('Estado:', 'lovable-wp-exporter'); ?></label>
                <select name="status" id="filter-status">
                    <option value=""><?php _e('Todos', 'lovable-wp-exporter'); ?></option>
                    <option value="completed" <?php echo isset($_GET['status']) && $_GET['status'] === 'completed' ? 'selected' : ''; ?>>
                        <?php _e('Completadas', 'lovable-wp-exporter'); ?>
                    </option>
                    <option value="pending" <?php echo isset($_GET['status']) && $_GET['status'] === 'pending' ? 'selected' : ''; ?>>
                        <?php _e('Pendientes', 'lovable-wp-exporter'); ?>
                    </option>
                    <option value="failed" <?php echo isset($_GET['status']) && $_GET['status'] === 'failed' ? 'selected' : ''; ?>>
                        <?php _e('Fallidas', 'lovable-wp-exporter'); ?>
                    </option>
                </select>
            </div>
            
            <div class="lovable-filter-group">
                <label for="filter-type"><?php _e('Tipo:', 'lovable-wp-exporter'); ?></label>
                <select name="type" id="filter-type">
                    <option value=""><?php _e('Todos', 'lovable-wp-exporter'); ?></option>
                    <option value="lovable">Lovable</option>
                    <option value="bolt">Bolt.new</option>
                    <option value="base44">Base44</option>
                </select>
            </div>
            
            <button type="submit" class="button">
                <?php _e('Filtrar', 'lovable-wp-exporter'); ?>
            </button>
        </form>
        
        <table class="wp-list-table widefat fixed striped table-view-list">
            <thead>
                <tr>
                    <th class="column-id"><?php _e('ID', 'lovable-wp-exporter'); ?></th>
                    <th class="column-name"><?php _e('Nombre', 'lovable-wp-exporter'); ?></th>
                    <th class="column-type"><?php _e('Tipo', 'lovable-wp-exporter'); ?></th>
                    <th class="column-components"><?php _e('Componentes', 'lovable-wp-exporter'); ?></th>
                    <th class="column-status"><?php _e('Estado', 'lovable-wp-exporter'); ?></th>
                    <th class="column-date"><?php _e('Fecha', 'lovable-wp-exporter'); ?></th>
                    <th class="column-actions"><?php _e('Acciones', 'lovable-wp-exporter'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($exports as $export) : 
                    $components = json_decode($export->components, true);
                    $component_count = is_array($components) ? count($components) : 0;
                ?>
                    <tr>
                        <td><?php echo esc_html($export->id); ?></td>
                        <td>
                            <strong>
                                <a href="<?php echo admin_url('admin.php?page=lovable-wp-exporter-history&action=view&id=' . $export->id); ?>">
                                    <?php echo esc_html($export->export_name); ?>
                                </a>
                            </strong>
                        </td>
                        <td>
                            <span class="lovable-badge">
                                <?php echo esc_html($export->source_type); ?>
                            </span>
                        </td>
                        <td>
                            <?php echo esc_html($component_count); ?> <?php _e('componente(s)', 'lovable-wp-exporter'); ?>
                        </td>
                        <td>
                            <span class="lovable-status lovable-status-<?php echo esc_attr($export->status); ?>">
                                <?php echo esc_html(ucfirst($export->status)); ?>
                            </span>
                        </td>
                        <td>
                            <?php echo esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($export->created_at))); ?>
                        </td>
                        <td>
                            <div class="lovable-actions">
                                <a href="<?php echo admin_url('admin.php?page=lovable-wp-exporter-history&action=view&id=' . $export->id); ?>" class="button button-small">
                                    <?php _e('Ver', 'lovable-wp-exporter'); ?>
                                </a>
                                <a href="<?php echo admin_url('admin.php?page=lovable-wp-exporter-history&action=download&id=' . $export->id); ?>" class="button button-small">
                                    <?php _e('Descargar', 'lovable-wp-exporter'); ?>
                                </a>
                                <a href="<?php echo wp_nonce_url(admin_url('admin.php?page=lovable-wp-exporter-history&action=delete&id=' . $export->id), 'lovable_delete_export'); ?>" class="button button-small button-link-delete" onclick="return confirm('<?php esc_attr_e('¿Estás seguro de que quieres eliminar esta exportación?', 'lovable-wp-exporter'); ?>')">
                                    <?php _e('Eliminar', 'lovable-wp-exporter'); ?>
                                </a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        
        <?php
        // Pagination
        $pagination_args = array(
            'base' => add_query_arg('paged', '%#%'),
            'format' => '',
            'prev_text' => __('&laquo; Anterior'),
            'next_text' => __('Siguiente &raquo;'),
            'total' => $total_pages,
            'current' => $paged,
        );
        
        echo '<div class="tablenav"><div class="tablenav-pages">';
        echo paginate_links($pagination_args);
        echo '</div></div>';
        ?>
    <?php endif; ?>
</div>

<!-- View Export Modal -->
<?php if ($action === 'view' && $export_id) : 
    $export = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $export_id));
    if ($export) :
        $components = json_decode($export->components, true);
?>
    <div class="lovable-view-modal">
        <h2><?php echo esc_html($export->export_name); ?></h2>
        
        <div class="lovable-export-details">
            <div class="lovable-detail-row">
                <strong><?php _e('Tipo:', 'lovable-wp-exporter'); ?></strong>
                <span><?php echo esc_html($export->source_type); ?></span>
            </div>
            <div class="lovable-detail-row">
                <strong><?php _e('Estado:', 'lovable-wp-exporter'); ?></strong>
                <span><?php echo esc_html(ucfirst($export->status)); ?></span>
            </div>
            <div class="lovable-detail-row">
                <strong><?php _e('Fecha:', 'lovable-wp-exporter'); ?></strong>
                <span><?php echo esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($export->created_at))); ?></span>
            </div>
        </div>
        
        <h3><?php _e('Componentes Exportados', 'lovable-wp-exporter'); ?></h3>
        
        <?php if (is_array($components)) : ?>
            <div class="lovable-components-list">
                <?php foreach ($components as $component_name => $component) : ?>
                    <div class="lovable-component-card">
                        <h4><?php echo esc_html($component_name); ?></h4>
                        <p>
                            <strong><?php _e('Tipo:', 'lovable-wp-exporter'); ?></strong> 
                            <?php echo esc_html($component['type']); ?>
                        </p>
                        <p>
                            <strong><?php _e('Shortcode:', 'lovable-wp-exporter'); ?></strong> 
                            <code>[lovable_<?php echo esc_html(strtolower($component_name)); ?>]</code>
                        </p>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        
        <div class="lovable-modal-actions">
            <a href="<?php echo admin_url('admin.php?page=lovable-wp-exporter-history'); ?>" class="button">
                <?php _e('Cerrar', 'lovable-wp-exporter'); ?>
            </a>
            <a href="<?php echo admin_url('admin.php?page=lovable-wp-exporter-history&action=download&id=' . $export->id); ?>" class="button button-primary">
                <?php _e('Descargar JSON', 'lovable-wp-exporter'); ?>
            </a>
        </div>
    </div>
<?php 
    endif;
endif; 
?>
