/**
 * Lovable WP Pro Admin JavaScript
 */

(function($) {
    'use strict';
    
    $(document).ready(function() {
        // Sync widgets
        $('#lovable-sync-btn').on('click', function() {
            const $btn = $(this);
            $btn.prop('disabled', true).text('Syncing...');
            
            $.ajax({
                url: lovableAdmin.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'lovable_sync_widgets',
                    nonce: lovableAdmin.nonce
                },
                success: function(response) {
                    if (response.success) {
                        alert(lovableAdmin.strings.syncSuccess);
                        location.reload();
                    } else {
                        alert(lovableAdmin.strings.syncError + ' ' + (response.data || ''));
                    }
                },
                error: function() {
                    alert(lovableAdmin.strings.syncError);
                },
                complete: function() {
                    $btn.prop('disabled', false).text('Sync Now');
                }
            });
        });
        
        // Import ZIP modal
        $('#lovable-import-btn').on('click', function() {
            $('#lovable-import-modal').show();
        });
        
        // Close modal
        $(document).on('click', function(e) {
            if ($(e.target).closest('.lovable-modal-content').length === 0) {
                $('#lovable-import-modal').hide();
            }
        });
        
        // ZIP upload
        const dropZone = $('#lovable-drop-zone');
        const fileInput = $('#lovable-zip-input');
        
        dropZone.on('dragover', function(e) {
            e.preventDefault();
            $(this).addClass('dragover');
        });
        
        dropZone.on('dragleave', function(e) {
            e.preventDefault();
            $(this).removeClass('dragover');
        });
        
        dropZone.on('drop', function(e) {
            e.preventDefault();
            $(this).removeClass('dragover');
            
            const files = e.originalEvent.dataTransfer.files;
            if (files.length) {
                handleFile(files[0]);
            }
        });
        
        fileInput.on('change', function() {
            if (this.files.length) {
                handleFile(this.files[0]);
            }
        });
        
        function handleFile(file) {
            if (!file.name.endsWith('.zip')) {
                alert('Please upload a ZIP file');
                return;
            }
            
            $('#lovable-import-progress').show();
            
            const formData = new FormData();
            formData.append('action', 'lovable_convert_zip');
            formData.append('nonce', lovableAdmin.nonce);
            formData.append('zip_file', file);
            
            $.ajax({
                url: lovableAdmin.ajaxUrl,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    $('#lovable-import-progress').hide();
                    
                    if (response.success) {
                        $('#lovable-import-result').html(
                            '<div class="notice notice-success"><p>' + 
                            'Converted ' + (response.data.components?.length || 0) + ' components!</p></div>'
                        );
                        setTimeout(function() {
                            location.reload();
                        }, 2000);
                    } else {
                        $('#lovable-import-result').html(
                            '<div class="notice notice-error"><p>' + 
                            (response.data || 'Conversion failed') + '</p></div>'
                        );
                    }
                },
                error: function() {
                    $('#lovable-import-progress').hide();
                    $('#lovable-import-result').html(
                        '<div class="notice notice-error"><p>Upload failed</p></div>'
                    );
                }
            });
        }
    });
    
})(jQuery);
