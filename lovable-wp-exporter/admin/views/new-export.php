<?php
/**
 * New Export View
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap lovable-new-export">
    <h1><?php _e('Nueva Exportación', 'lovable-wp-exporter'); ?></h1>
    
    <div class="lovable-export-wizard">
        <!-- Step 1: Source Input -->
        <div class="lovable-wizard-step" id="step-1">
            <h2><?php _e('Paso 1: Código Fuente', 'lovable-wp-exporter'); ?></h2>
            <p class="description">
                <?php _e('Copia y pega el código fuente de tu proyecto de Lovable, Bolt.new o Base44.', 'lovable-wp-exporter'); ?>
            </p>
            
            <div class="lovable-input-group">
                <label for="source-type"><?php _e('Tipo de Proyecto:', 'lovable-wp-exporter'); ?></label>
                <select id="source-type" name="source_type">
                    <option value="lovable">Lovable</option>
                    <option value="bolt">Bolt.new</option>
                    <option value="base44">Base44</option>
                    <option value="file">Subir Archivo ZIP</option>
                </select>
            </div>
            
            <div id="source-code-container">
                <label for="source-code"><?php _e('Código Fuente:', 'lovable-wp-exporter'); ?></label>
                <textarea 
                    id="source-code" 
                    name="source_code" 
                    rows="20" 
                    placeholder="// Pega aquí tu código React/JSX..."
                    class="large-text code-editor"
                ></textarea>
            </div>
            
            <div id="file-upload-container" style="display: none;">
                <label for="source-file"><?php _e('Subir ZIP:', 'lovable-wp-exporter'); ?></label>
                <input 
                    type="file" 
                    id="source-file" 
                    name="source_file" 
                    accept=".zip"
                    class="regular-text"
                />
                <p class="description">
                    <?php _e('Sube un archivo ZIP con tu proyecto completo.', 'lovable-wp-exporter'); ?>
                </p>
            </div>
            
            <div class="lovable-step-actions">
                <button type="button" class="button button-primary" id="btn-analyze">
                    <span class="dashicons dashicons-visibility"></span>
                    <?php _e('Analizar Código', 'lovable-wp-exporter'); ?>
                </button>
            </div>
        </div>
        
        <!-- Step 2: Analysis Results -->
        <div class="lovable-wizard-step" id="step-2" style="display: none;">
            <h2><?php _e('Paso 2: Resultados del Análisis', 'lovable-wp-exporter'); ?></h2>
            <p class="description">
                <?php _e('El sistema ha detectado los siguientes componentes:', 'lovable-wp-exporter'); ?>
            </p>
            
            <div id="analysis-results">
                <!-- Populated by JavaScript -->
            </div>
            
            <div class="lovable-step-actions">
                <button type="button" class="button" id="btn-back-1">
                    <?php _e('Atrás', 'lovable-wp-exporter'); ?>
                </button>
                <button type="button" class="button button-primary" id="btn-convert">
                    <span class="dashicons dashicons-admin-tools"></span>
                    <?php _e('Convertir Componentes', 'lovable-wp-exporter'); ?>
                </button>
            </div>
        </div>
        
        <!-- Step 3: Conversion Options -->
        <div class="lovable-wizard-step" id="step-3" style="display: none;">
            <h2><?php _e('Paso 3: Opciones de Exportación', 'lovable-wp-exporter'); ?></h2>
            <p class="description">
                <?php _e('Configura cómo quieres exportar los componentes:', 'lovable-wp-exporter'); ?>
            </p>
            
            <div class="lovable-export-options">
                <div class="lovable-option-card">
                    <label>
                        <input type="checkbox" name="export_as[]" value="shortcode" checked />
                        <span class="dashicons dashicons-shortcode"></span>
                        <h3><?php _e('Shortcodes', 'lovable-wp-exporter'); ?></h3>
                        <p><?php _e('Usa shortcodes como [lovable_component] en cualquier página.', 'lovable-wp-exporter'); ?></p>
                    </label>
                </div>
                
                <div class="lovable-option-card">
                    <label>
                        <input type="checkbox" name="export_as[]" value="gutenberg" />
                        <span class="dashicons dashicons-editor-kitchensink"></span>
                        <h3><?php _e('Bloques Gutenberg', 'lovable-wp-exporter'); ?></h3>
                        <p><?php _e('Crea bloques nativos para el editor de WordPress.', 'lovable-wp-exporter'); ?></p>
                    </label>
                </div>
                
                <div class="lovable-option-card">
                    <label>
                        <input type="checkbox" name="export_as[]" value="elementor" />
                        <span class="dashicons dashicons-admin-multisite"></span>
                        <h3><?php _e('Widgets Elementor', 'lovable-wp-exporter'); ?></h3>
                        <p><?php _e('Integra los componentes como widgets en Elementor.', 'lovable-wp-exporter'); ?></p>
                    </label>
                </div>
            </div>
            
            <div class="lovable-export-settings">
                <h3><?php _e('Configuración Adicional', 'lovable-wp-exporter'); ?></h3>
                
                <div class="lovable-setting-field">
                    <label for="export-name"><?php _e('Nombre de la Exportación:', 'lovable-wp-exporter'); ?></label>
                    <input 
                        type="text" 
                        id="export-name" 
                        name="export_name" 
                        class="regular-text"
                        placeholder="Mi Exportación"
                    />
                </div>
                
                <div class="lovable-setting-field">
                    <label>
                        <input type="checkbox" name="convert_tailwind" checked />
                        <?php _e('Convertir clases Tailwind a CSS', 'lovable-wp-exporter'); ?>
                    </label>
                    <p class="description">
                        <?php _e('Convierte automáticamente las clases de Tailwind a CSS estándar.', 'lovable-wp-exporter'); ?>
                    </p>
                </div>
                
                <div class="lovable-setting-field">
                    <label>
                        <input type="checkbox" name="include_assets" checked />
                        <?php _e('Incluir Assets (imágenes, fuentes)', 'lovable-wp-exporter'); ?>
                    </label>
                    <p class="description">
                        <?php _e('Importa automáticamente las imágenes y fuentes a la biblioteca de medios.', 'lovable-wp-exporter'); ?>
                    </p>
                </div>
            </div>
            
            <div class="lovable-step-actions">
                <button type="button" class="button" id="btn-back-2">
                    <?php _e('Atrás', 'lovable-wp-exporter'); ?>
                </button>
                <button type="button" class="button button-primary" id="btn-export">
                    <span class="dashicons dashicons-download"></span>
                    <?php _e('Exportar', 'lovable-wp-exporter'); ?>
                </button>
            </div>
        </div>
        
        <!-- Step 4: Complete -->
        <div class="lovable-wizard-step" id="step-4" style="display: none;">
            <h2><?php _e('¡Exportación Completada!', 'lovable-wp-exporter'); ?></h2>
            
            <div class="lovable-success-message">
                <span class="dashicons dashicons-yes-alt"></span>
                <p><?php _e('Los componentes han sido exportados correctamente.', 'lovable-wp-exporter'); ?></p>
            </div>
            
            <div id="export-summary">
                <!-- Populated by JavaScript -->
            </div>
            
            <div class="lovable-usage-instructions">
                <h3><?php _e('¿Cómo usar?', 'lovable-wp-exporter'); ?></h3>
                
                <div class="lovable-usage-tab">
                    <h4><?php _e('Shortcodes', 'lovable-wp-exporter'); ?></h4>
                    <pre><code>[lovable_componente nombre="valor"]</code></pre>
                </div>
                
                <div class="lovable-usage-tab">
                    <h4><?php _e('Gutenberg', 'lovable-wp-exporter'); ?></h4>
                    <p><?php _e('Busca el bloque "Lovable: [Nombre del Componente]" en el editor.', 'lovable-wp-exporter'); ?></p>
                </div>
                
                <div class="lovable-usage-tab">
                    <h4><?php _e('Elementor', 'lovable-wp-exporter'); ?></h4>
                    <p><?php _e('Encuentra los widgets en la categoría "Lovable Components".', 'lovable-wp-exporter'); ?></p>
                </div>
            </div>
            
            <div class="lovable-step-actions">
                <a href="<?php echo admin_url('admin.php?page=lovable-wp-exporter'); ?>" class="button">
                    <?php _e('Volver al Dashboard', 'lovable-wp-exporter'); ?>
                </a>
                <a href="<?php echo admin_url('admin.php?page=lovable-wp-exporter-history'); ?>" class="button button-primary">
                    <?php _e('Ver Historial', 'lovable-wp-exporter'); ?>
                </a>
            </div>
        </div>
    </div>
    
    <!-- Loading Overlay -->
    <div id="lovable-loading" style="display: none;">
        <div class="lovable-loading-spinner">
            <span class="dashicons dashicons-admin-tools"></span>
            <p><?php _e('Procesando...', 'lovable-wp-exporter'); ?></p>
        </div>
    </div>
</div>
