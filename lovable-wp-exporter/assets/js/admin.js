/**
 * Lovable Elementor Pro - Admin JavaScript
 */

(function($) {
    'use strict';
    
    const dropZone = $('#lovable-drop-zone');
    const fileInput = $('#lovable-file-input');
    const progress = $('#lovable-progress');
    const results = $('#lovable-results');
    const error = $('#lovable-error');
    const componentsList = $('#lovable-components-list');
    const importBtn = $('#lovable-import-btn');
    
    let parsedData = null;
    
    // Drag and drop
    dropZone.on('dragover', function(e) {
        e.preventDefault();
        dropZone.addClass('dragover');
    });
    
    dropZone.on('dragleave', function(e) {
        e.preventDefault();
        dropZone.removeClass('dragover');
    });
    
    dropZone.on('drop', function(e) {
        e.preventDefault();
        dropZone.removeClass('dragover');
        
        const files = e.originalEvent.dataTransfer.files;
        if (files.length) {
            handleFile(files[0]);
        }
    });
    
    // File input change
    fileInput.on('change', function(e) {
        if (e.target.files.length) {
            handleFile(e.target.files[0]);
        }
    });
    
    // Handle file upload
    function handleFile(file) {
        if (!file.name.endsWith('.zip')) {
            showError('Por favor sube un archivo ZIP');
            return;
        }
        
        showProgress();
        
        const formData = new FormData();
        formData.append('action', 'lovable_parse_zip');
        formData.append('nonce', lovableAdmin.nonce);
        formData.append('design_file', file);
        
        $.ajax({
            url: lovableAdmin.ajaxUrl,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                hideProgress();
                
                if (response.success) {
                    parsedData = response.data;
                    showResults(parsedData);
                } else {
                    showError(response.data || 'Error al procesar el ZIP');
                }
            },
            error: function() {
                hideProgress();
                showError('Error de conexión. Por favor intenta de nuevo.');
            }
        });
    }
    
    // Show progress
    function showProgress() {
        progress.show();
        results.hide();
        error.hide();
        
        let width = 0;
        const interval = setInterval(function() {
            if (width >= 90) {
                clearInterval(interval);
            } else {
                width += 10;
                $('.lovable-progress-fill').css('width', width + '%');
            }
        }, 200);
    }
    
    // Hide progress
    function hideProgress() {
        $('.lovable-progress-fill').css('width', '100%');
        setTimeout(function() {
            progress.hide();
            $('.lovable-progress-fill').css('width', '0%');
        }, 500);
    }
    
    // Show results
    function showResults(data) {
        componentsList.empty();
        
        if (data.components) {
            $.each(data.components, function(name, component) {
                const card = $('<div class="lovable-component-card">')
                    .append('<h5>' + escapeHtml(name) + '</h5>')
                    .append('<p>' + (component.tailwind_classes?.length || 0) + ' clases Tailwind</p>')
                    .append('<p>' + (component.controls?.length || 0) + ' controles editables</p>');
                
                componentsList.append(card);
            });
        }
        
        results.fadeIn();
    }
    
    // Show error
    function showError(message) {
        error.find('p').text(message);
        error.fadeIn();
        results.hide();
    }
    
    // Import button
    importBtn.on('click', function() {
        if (!parsedData) return;
        
        importBtn.prop('disabled', true).text('Importando...');
        
        const formData = new FormData();
        formData.append('action', 'lovable_import_design');
        formData.append('nonce', lovableAdmin.nonce);
        formData.append('design_name', 'Lovable Import ' + new Date().toLocaleDateString());
        formData.append('design_data', JSON.stringify(parsedData.components));
        
        $.ajax({
            url: lovableAdmin.ajaxUrl,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                importBtn.prop('disabled', false).text('Importar a Elementor');
                
                if (response.success) {
                    alert(response.data.message);
                    window.location.href = 'admin.php?page=lovable-elementor-pro-designs';
                } else {
                    showError(response.data || 'Error al importar');
                }
            },
            error: function() {
                importBtn.prop('disabled', false).text('Importar a Elementor');
                showError('Error de conexión. Por favor intenta de nuevo.');
            }
        });
    });
    
    // Escape HTML
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
    
})(jQuery);
