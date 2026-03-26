<?php
/**
 * Import Design Admin Page
 */

if (!defined('ABSPATH')) exit;
?>

<div class="wrap lovable-importer">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    
    <div class="lovable-welcome-panel">
        <h2><?php _e('Importar Diseño desde Lovable', 'lovable-elementor-pro'); ?></h2>
        <p class="description">
            <?php _e('Sube el archivo ZIP exportado desde Lovable y se crearán widgets nativos de Elementor automáticamente.', 'lovable-elementor-pro'); ?>
        </p>
    </div>
    
    <div class="lovable-upload-section">
        <div id="lovable-drop-zone" class="lovable-drop-zone">
            <input type="file" id="lovable-file-input" accept=".zip" />
            <div class="lovable-drop-zone-content">
                <div class="lovable-icon">📦</div>
                <h3><?php _e('Arrastra tu ZIP aquí', 'lovable-elementor-pro'); ?></h3>
                <p><?php _e('o haz clic para seleccionar', 'lovable-elementor-pro'); ?></p>
            </div>
        </div>
        
        <div id="lovable-progress" class="lovable-progress" style="display: none;">
            <div class="lovable-progress-bar">
                <div class="lovable-progress-fill"></div>
            </div>
            <p class="lovable-progress-text"><?php _e('Procesando...', 'lovable-elementor-pro'); ?></p>
        </div>
    </div>
    
    <div id="lovable-results" class="lovable-results" style="display: none;">
        <div class="lovable-success-message">
            <span class="dashicons dashicons-yes-alt"></span>
            <h3><?php _e('¡Diseño Procesado!', 'lovable-elementor-pro'); ?></h3>
        </div>
        
        <div class="lovable-components-grid">
            <h4><?php _e('Componentes Detectados:', 'lovable-elementor-pro'); ?></h4>
            <div id="lovable-components-list"></div>
        </div>
        
        <div class="lovable-actions">
            <button id="lovable-import-btn" class="button button-primary button-hero">
                <span class="dashicons dashicons-download"></span>
                <?php _e('Importar a Elementor', 'lovable-elementor-pro'); ?>
            </button>
        </div>
    </div>
    
    <div id="lovable-error" class="notice notice-error" style="display: none;">
        <p></p>
    </div>
    
    <div class="lovable-instructions">
        <h3><?php _e('¿Cómo funciona?', 'lovable-elementor-pro'); ?></h3>
        <div class="lovable-steps">
            <div class="lovable-step">
                <div class="lovable-step-number">1</div>
                <h4><?php _e('Exporta desde Lovable', 'lovable-elementor-pro'); ?></h4>
                <p><?php _e('En tu proyecto de Lovable: Export → Download ZIP', 'lovable-elementor-pro'); ?></p>
            </div>
            <div class="lovable-step">
                <div class="lovable-step-number">2</div>
                <h4><?php _e('Sube el ZIP', 'lovable-elementor-pro'); ?></h4>
                <p><?php _e('Arrastra el archivo aquí y lo procesaremos', 'lovable-elementor-pro'); ?></p>
            </div>
            <div class="lovable-step">
                <div class="lovable-step-number">3</div>
                <h4><?php _e('Importa', 'lovable-elementor-pro'); ?></h4>
                <p><?php _e('Haz clic en "Importar" y se crearán los widgets', 'lovable-elementor-pro'); ?></p>
            </div>
            <div class="lovable-step">
                <div class="lovable-step-number">4</div>
                <h4><?php _e('Usa en Elementor', 'lovable-elementor-pro'); ?></h4>
                <p><?php _e('Edita una página y busca los widgets en "Lovable Components"', 'lovable-elementor-pro'); ?></p>
            </div>
        </div>
    </div>
</div>
