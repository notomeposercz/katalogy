/**
 * Enhanced drag & drop handling for Katalogy admin
 * Umístit do /modules/katalogy/views/js/
 */

$(document).ready(function() {
    // Override default PrestaShop drag & drop behavior
    if (typeof updatePosition !== 'undefined') {
        console.log('Katalogy: Enhancing drag & drop functionality');
        
        // Store original updatePosition function
        var originalUpdatePosition = updatePosition;
        
        // Override with our enhanced version
        window.updatePosition = function(way, id, token, up_query_string, down_query_string, table) {
            console.log('Katalogy updatePosition called:', {way, id, token, table});
            
            if (table === 'katalogy') {
                return enhancedUpdatePosition(way, id, token, up_query_string, down_query_string, table);
            } else {
                return originalUpdatePosition(way, id, token, up_query_string, down_query_string, table);
            }
        };
    }
    
    // Enhanced position update specifically for katalogy
    function enhancedUpdatePosition(way, id, token, up_query_string, down_query_string, table) {
        console.log('Enhanced katalogy position update');
        
        var ajax_params = {
            ajax: '1',
            token: token,
            action: 'Move',
            id: id,
            way: way
        };
        
        $.ajax({
            type: 'POST',
            url: 'index.php?controller=AdminKatalogy',
            data: ajax_params,
            dataType: 'json',
            success: function(data) {
                console.log('Position update success:', data);
                if (data.success) {
                    showSuccessMessage('Pozice byla úspěšně aktualizována');
                    // Refresh the list
                    setTimeout(function() {
                        location.reload();
                    }, 1000);
                } else {
                    showErrorMessage('Chyba při aktualizaci pozice');
                }
            },
            error: function(xhr, status, error) {
                console.error('Position update error:', {xhr, status, error});
                showErrorMessage('AJAX chyba při aktualizaci pozice');
            }
        });
        
        return false;
    }
    
    // Enhanced sortable for drag & drop
    if ($('#table-katalogy tbody').length > 0) {
        console.log('Katalogy: Initializing enhanced sortable');
        
        $('#table-katalogy tbody').sortable({
            helper: 'clone',
            cursor: 'move',
            axis: 'y',
            opacity: 0.8,
            placeholder: 'ui-sortable-placeholder',
            start: function(event, ui) {
                ui.placeholder.height(ui.item.height());
                console.log('Drag started');
            },
            stop: function(event, ui) {
                console.log('Drag stopped');
                
                var positions = {};
                var token = $('input[name="token"]').val();
                
                $('#table-katalogy tbody tr').each(function(index, element) {
                    var id_match = $(element).attr('id');
                    if (id_match) {
                        positions[index] = id_match;
                    }
                });
                
                console.log('New positions:', positions);
                
                // Send AJAX request
                $.ajax({
                    type: 'POST',
                    url: 'index.php?controller=AdminKatalogy',
                    data: {
                        ajax: '1',
                        token: token,
                        action: 'updatePositions',
                        katalogy: positions
                    },
                    dataType: 'json',
                    success: function(data) {
                        console.log('Drag & drop success:', data);
                        if (data.success) {
                            showSuccessMessage('Pořadí bylo úspěšně aktualizováno');
                        } else {
                            showErrorMessage('Chyba při aktualizaci pořadí');
                            location.reload(); // Reload on error
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Drag & drop error:', {xhr, status, error});
                        console.log('Response text:', xhr.responseText);
                        showErrorMessage('AJAX chyba při aktualizaci pořadí');
                        location.reload(); // Reload on error
                    }
                });
            }
        });
    }
    
    // Helper functions for messages
    function showSuccessMessage(message) {
        if (typeof showSuccessMessage !== 'undefined') {
            showSuccessMessage(message);
        } else {
            console.log('SUCCESS: ' + message);
            // Fallback notification
            $('body').prepend('<div class="alert alert-success">' + message + '</div>');
            setTimeout(function() {
                $('.alert-success').fadeOut();
            }, 3000);
        }
    }
    
    function showErrorMessage(message) {
        if (typeof showErrorMessage !== 'undefined') {
            showErrorMessage(message);
        } else {
            console.log('ERROR: ' + message);
            // Fallback notification
            $('body').prepend('<div class="alert alert-danger">' + message + '</div>');
            setTimeout(function() {
                $('.alert-danger').fadeOut();
            }, 3000);
        }
    }
    
    // Debug information
    console.log('Katalogy admin drag & drop initialized');
    console.log('jQuery version:', $.fn.jquery);
    console.log('Table exists:', $('#table-katalogy').length > 0);
    console.log('Sortable available:', typeof $.fn.sortable !== 'undefined');
});