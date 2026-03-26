<?php
/**
 * Settings View
 */

if (!defined('ABSPATH')) {
    exit;
}

$settings = get_option('lovable_wp_exporter_settings', array(
    'auto_enqueue_styles' => true,
    'convert_tailwind' => true,
    'preserve_react_state' => false,
    'elementor_integration' => true,
    'gutenberg_integration' => true,
));
?>

<div class="wrap lovable-settings">
    <h1><?php _e('Configuración de Lovable Exporter', 'lovable-wp-exporter'); ?></h1>
    
    <form method="post" action="options.php">
        <?php settings_fields('lovable_wp_exporter_settings_group'); ?>
        <?php do_settings_sections('lovable_wp_exporter_settings'); ?>
        
        <table class="form-table">
            <tr>
                <th scope="row">
                    <label for="auto_enqueue_styles">
                        <?php _e('Auto Cargar Estilos', 'lovable-wp-exporter'); ?>
                    </label>
                </th>
                <td>
                    <input 
                        type="checkbox" 
                        id="auto_enqueue_styles" 
                        name="lovable_wp_exporter_settings[auto_enqueue_styles]" 
                        value="1"
                        <?php checked(isset($settings['auto_enqueue_styles']) && $settings['auto_enqueue_styles']); ?>
                    />
                    <label for="auto_enqueue_styles">
                        <?php _e('Cargar automáticamente los estilos CSS en todas las páginas', 'lovable-wp-exporter'); ?>
                    </label>
                    <p class="description">
                        <?php _e('Si está marcado, los estilos convertidos se cargarán automáticamente en todo el sitio.', 'lovable-wp-exporter'); ?>
                    </p>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="convert_tailwind">
                        <?php _e('Convertir Tailwind CSS', 'lovable-wp-exporter'); ?>
                    </label>
                </th>
                <td>
                    <input 
                        type="checkbox" 
                        id="convert_tailwind" 
                        name="lovable_wp_exporter_settings[convert_tailwind]" 
                        value="1"
                        <?php checked(isset($settings['convert_tailwind']) && $settings['convert_tailwind']); ?>
                    />
                    <label for="convert_tailwind">
                        <?php _e('Convertir clases de Tailwind a CSS estándar', 'lovable-wp-exporter'); ?>
                    </label>
                    <p class="description">
                        <?php _e('Convierte automáticamente las clases de utilidad de Tailwind a CSS tradicional.', 'lovable-wp-exporter'); ?>
                    </p>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="preserve_react_state">
                        <?php _e('Preservar Estado React', 'lovable-wp-exporter'); ?>
                    </label>
                </th>
                <td>
                    <input 
                        type="checkbox" 
                        id="preserve_react_state" 
                        name="lovable_wp_exporter_settings[preserve_react_state]" 
                        value="1"
                        <?php checked(isset($settings['preserve_react_state']) && $settings['preserve_react_state']); ?>
                    />
                    <label for="preserve_react_state">
                        <?php _e('Intentar preservar la funcionalidad interactiva de React', 'lovable-wp-exporter'); ?>
                    </label>
                    <p class="description">
                        <?php _e('Experimental: Intenta mantener algo de la interactividad original usando JavaScript.', 'lovable-wp-exporter'); ?>
                    </p>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="elementor_integration">
                        <?php _e('Integración con Elementor', 'lovable-wp-exporter'); ?>
                    </label>
                </th>
                <td>
                    <input 
                        type="checkbox" 
                        id="elementor_integration" 
                        name="lovable_wp_exporter_settings[elementor_integration]" 
                        value="1"
                        <?php checked(isset($settings['elementor_integration']) && $settings['elementor_integration']); ?>
                    />
                    <label for="elementor_integration">
                        <?php _e('Habilitar widgets de Elementor', 'lovable-wp-exporter'); ?>
                    </label>
                    <p class="description">
                        <?php _e('Registra los componentes convertidos como widgets en Elementor.', 'lovable-wp-exporter'); ?>
                    </p>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="gutenberg_integration">
                        <?php _e('Integración con Gutenberg', 'lovable-wp-exporter'); ?>
                    </label>
                </th>
                <td>
                    <input 
                        type="checkbox" 
                        id="gutenberg_integration" 
                        name="lovable_wp_exporter_settings[gutenberg_integration]" 
                        value="1"
                        <?php checked(isset($settings['gutenberg_integration']) && $settings['gutenberg_integration']); ?>
                    />
                    <label for="gutenberg_integration">
                        <?php _e('Habilitar bloques de Gutenberg', 'lovable-wp-exporter'); ?>
                    </label>
                    <p class="description">
                        <?php _e('Registra los componentes convertidos como bloques en Gutenberg.', 'lovable-wp-exporter'); ?>
                    </p>
                </td>
            </tr>
        </table>
        
        <?php submit_button(); ?>
    </form>
    
    <hr />
    
    <h2><?php _e('Herramientas', 'lovable-wp-exporter'); ?></h2>
    
    <table class="form-table">
        <tr>
            <th scope="row"><?php _e('Exportar Datos', 'lovable-wp-exporter'); ?></th>
            <td>
                <form method="post" action="">
                    <p class="description">
                        <?php _e('Descarga todas tus exportaciones en formato JSON.', 'lovable-wp-exporter'); ?>
                    </p>
                    <button type="submit" name="lovable_export_all" class="button">
                        <?php _e('Exportar Todo', 'lovable-wp-exporter'); ?>
                    </button>
                </form>
            </td>
        </tr>
        
        <tr>
            <th scope="row"><?php _e('Importar Datos', 'lovable-wp-exporter'); ?></th>
            <td>
                <form method="post" enctype="multipart/form-data" action="">
                    <p class="description">
                        <?php _e('Importa exportaciones desde un archivo JSON.', 'lovable-wp-exporter'); ?>
                    </p>
                    <input type="file" name="lovable_import_file" accept=".json" />
                    <button type="submit" name="lovable_import" class="button">
                        <?php _e('Importar', 'lovable-wp-exporter'); ?>
                    </button>
                </form>
            </td>
        </tr>
        
        <tr>
            <th scope="row"><?php _e('Limpiar Datos', 'lovable-wp-exporter'); ?></th>
            <td>
                <form method="post" action="">
                    <p class="description">
                        <?php _e('Elimina todas las exportaciones guardadas. Esta acción no se puede deshacer.', 'lovable-wp-exporter'); ?>
                    </p>
                    <button type="submit" name="lovable_clear_all" class="button button-link-delete" onclick="return confirm('<?php esc_attr_e('¿Estás seguro? Esta acción eliminará todas las exportaciones.', 'lovable-wp-exporter'); ?>')">
                        <?php _e('Eliminar Todo', 'lovable-wp-exporter'); ?>
                    </button>
                </form>
            </td>
        </tr>
    </table>
    
    <hr />
    
    <h2><?php _e('Información del Plugin', 'lovable-wp-exporter'); ?></h2>
    
    <table class="form-table">
        <tr>
            <th scope="row"><?php _e('Versión', 'lovable-wp-exporter'); ?></th>
            <td><?php echo LOVABLE_WP_EXPORTER_VERSION; ?></td>
        </tr>
        <tr>
            <th scope="row"><?php _e('Documentación', 'lovable-wp-exporter'); ?></th>
            <td>
                <a href="#" class="button">
                    <?php _e('Ver Documentación', 'lovable-wp-exporter'); ?>
                </a>
            </td>
        </tr>
        <tr>
            <th scope="row"><?php _e('Soporte', 'lovable-wp-exporter'); ?></th>
            <td>
                <a href="#" class="button">
                    <?php _e('Obtener Soporte', 'lovable-wp-exporter'); ?>
                </a>
            </td>
        </tr>
    </table>
</div>

<?php
// Handle tool actions
if (isset($_POST['lovable_clear_all'])) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'lovable_exports';
    $wpdb->query("TRUNCATE TABLE $table_name");
    echo '<div class="notice notice-success"><p>' . __('Todos los datos han sido eliminados.', 'lovable-wp-exporter') . '</p></div>';
}

if (isset($_POST['lovable_export_all'])) {
    // Export functionality
    $registry = new Lovable_Component_Registry();
    $json = $registry->export_to_json();
    
    header('Content-Type: application/json');
    header('Content-Disposition: attachment; filename="lovable-exports-' . date('Y-m-d') . '.json"');
    echo $json;
    exit;
}

if (isset($_POST['lovable_import'])) {
    // Import functionality
    if (isset($_FILES['lovable_import_file']) && $_FILES['lovable_import_file']['error'] === UPLOAD_ERR_OK) {
        $upload_file = $_FILES['lovable_import_file']['tmp_name'];
        $json_content = file_get_contents($upload_file);
        $import_data = json_decode($json_content, true);
        
        if ($import_data) {
            $registry = new Lovable_Component_Registry();
            $result = $registry->register($import_data, 'both');
            
            if ($result['success']) {
                echo '<div class="notice notice-success"><p>' . __('Importación completada exitosamente.', 'lovable-wp-exporter') . '</p></div>';
            } else {
                echo '<div class="notice notice-error"><p>' . __('Error en la importación.', 'lovable-wp-exporter') . '</p></div>';
            }
        }
    }
}
?>
