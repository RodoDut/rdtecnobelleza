/**
 * RD Tecnobelleza - Finanzas Form
 * 
 * Este script maneja la funcionalidad del formulario de finanzas
 */
//Función anónima para evitar conflictos con otras bibliotecas de JavaScript
// y para asegurar que el código se ejecute una vez que el DOM esté completamente cargado.
(function(jQuery) {
    // Esperar a que el DOM esté listo
    jQuery(document).ready(function() {
        console.log('Inicializando formulario de finanzas');
        
        // Elementos del DOM
        const $container = jQuery('.finanzas-manager');
        if (!$container.length) {
            console.warn('No se encontró el contenedor del formulario de finanzas');
            return;
        }
        
        const $listContainer = $container.find('.finanzas-list-container');
        const $formContainer = $container.find('.finanzas-form-container');
        const $form = $container.find('#finanzas-form');
        const $formTitle = $container.find('#form-title');
        const $postIdInput = $container.find('#post-id');
        const $addNewButton = $container.find('#add-new-finanza');
        const $closeFormButton = $container.find('#close-form');
        const $cancelFormButton = $container.find('#cancel-form');
        const $searchInput = $container.find('#search-finanzas');
        const $filterType = $container.find('#filter-finanzas-type');
        const $dateInput = $container.find('#date-input-form-finanzas'); // Select the date input
        
        // Inicializar fecha con la fecha actual
        const today = new Date();
        const formattedDate = today.toISOString().split('T')[0];
        $form.find('#fecha').val(formattedDate);
        
        // Mostrar formulario para añadir nuevo
        $addNewButton.on('click', function() {
            resetForm();
            showForm();
            $formTitle.text('Añadir Nuevo Registro');
        });
        
        // Cerrar formulario
        $closeFormButton.on('click', hideForm);
        $cancelFormButton.on('click', hideForm);
        
        // Editar registro
        $container.on('click', '.edit-finanza', function() {
            const postId = jQuery(this).data('id');
            loadFinanza(postId);
        });
        
        // Eliminar registro
        $container.on('click', '.delete-finanza', function() {
            const postId = jQuery(this).data('id');
            const row = jQuery(this).closest('tr');
            
            if (confirm('¿Estás seguro de que deseas eliminar este registro?')) {
                deleteFinanza(postId, row);
            }
        });
        
        // Enviar formulario
        $form.on('submit', function(e) {
            e.preventDefault();
            saveFinanza();
        });
        
        // Filtrar registros
        $container.find('.filter-button').on('click', filterFinanzas);

        $filterType.on('change', filterFinanzas); 

        // Initialize Date Range Picker using the shared function
        if ($dateInput.length && typeof window.rdInitializeDateRangePicker === 'function') {
            window.rdInitializeDateRangePicker($dateInput, {}, false); // autoUpdate = false

            // Keep specific event listeners for this form's filtering
            $dateInput.on('apply.daterangepicker', function(ev, picker) {
                // The init function already updates the input value on apply
                filterFinanzas(); // Trigger filter
            });

            $dateInput.on('cancel.daterangepicker', function(ev, picker) {
                // The init function already clears the input value on cancel
                filterFinanzas(); // Trigger filter (with empty dates)
            });
        } else {
            if (!$dateInput.length) {
                 console.warn('Finanzas Form: Date input .date-input not found.');
            } else {
                 console.error('Finanzas Form: rdInitializeDateRangePicker function not found.');
            }
        }
        
        // Paginación AJAX
        $container.on('click', '.pagination-btn', function(e) {
            e.preventDefault();
            const page = parseInt(jQuery(this).data('page'));
            if (!page || jQuery(this).is(':disabled')) return;

            // Obtener filtros actuales
            const searchTerm = $searchInput.val().toLowerCase();
            const filterType = $filterType.val();
            const datePickerData = $dateInput.data('daterangepicker');
            var fechaInicio = datePickerData && datePickerData.startDate ? datePickerData.startDate.format('YYYY-MM-DD') : '2025-01-01';
            var fechaFin = datePickerData && datePickerData.endDate ? datePickerData.endDate.format('YYYY-MM-DD') : new Date().toISOString().split('T')[0];
            if(fechaInicio >= fechaFin) {
                fechaInicio = '2024-01-01';
            }
            $container.addClass('loading');
            jQuery.ajax({
                url: rdFinanzasData.ajaxurl,
                type: 'POST',
                data: {
                    action: 'rd_filter_finanzas',
                    nonce: rdFinanzasData.nonce,
                    searchTerm: searchTerm,
                    filterType: filterType,
                    fechaInicio: fechaInicio,
                    fechaFin: fechaFin,
                    page: page
                },
                success: function(response) {
                    if (response.success) {
                        const $finanzasList = $container.find('#finanzas-list');
                        if (response.data && response.data.transacciones) {
                            $finanzasList.html(response.data.transacciones);
                        } else {
                            $finanzasList.html('<div class="empty-state"><p>No se encontraron registros.</p></div>');
                        }
                    } else {
                        console.error('Error al paginar:', response.data);
                    }
                },
                error: function() {
                    alert('Error de conexión al paginar los datos.');
                },
                complete: function() {
                    $container.removeClass('loading');
                }
            });
        });
        
        // Funciones
        
        function showForm() {
            $listContainer.hide();
            $formContainer.show();
        }
        
        function hideForm() {
            $formContainer.hide();
            $listContainer.show();
        }
        
        function resetForm() {
            $form[0].reset();
            $postIdInput.val('0');
            
            // Establecer fecha actual
            $form.find('#fecha').val(formattedDate);
        }
        
        function loadFinanza(postId) {
            // Mostrar indicador de carga
            $form.addClass('loading');

            jQuery.ajax({
                url: rdFinanzasData.ajaxurl,
                type: 'GET',
                data: {
                    action: 'rd_get_finanza',
                    nonce: $form.find('input[name="nonce"]').val(),
                    post_id: postId
                },


                success: function(response) {
                    if (response.success && response.data.post) {
                        const post = response.data.post;
                        
                        // Llenar formulario
                        $postIdInput.val(post.ID);
                        $form.find('#concepto').val(post.meta.concepto);
                        $form.find('#fecha').val(post.meta.fecha);
                        $form.find('#tipo').val(post.meta.tipo);
                        $form.find('#monto').val(post.meta.monto);
                        $form.find('#metodo_pago').val(post.meta.metodo_pago);
                        $form.find('#descripcion').val(post.meta.descripcion);
                        $form.find('#categoria').val(post.meta.categoria);
                        $form.find('#diparos').val(post.meta.disparos);
                        
                        // Mostrar formulario
                        $formTitle.text('Editar Registro');
                        showForm();
                    } else {
                        alert(response.data.message || 'Error al cargar el registro');
                    }
                },
                error: function(xhr, status, error) {
                    console.error("Error AJAX:", status, error);
                    alert('Error de conexión. Por favor, intenta de nuevo.', status, error);
                },
                complete: function() {
                    $form.removeClass('loading');
                }
            });
        }
        
        function saveFinanza() {
            // Validar formulario
            if (!$form[0].checkValidity()) {
                $form[0].reportValidity();
                return;
            }
            
            // Mostrar indicador de carga
            $form.addClass('loading');
            
            // Obtener datos del formulario
            const formData = new FormData($form[0]);
            formData.append('action', 'rd_save_finanza');
            
            jQuery.ajax({
                url: rdFinanzasData.ajaxurl,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.success) {
                        alert(response.data.message);
                        
                        // Recargar la página para mostrar los cambios
                        window.location.reload();
                    } else {
                        alert(response.data.message || 'Error al guardar el registro');
                    }
                },
                error: function() {
                    alert('Error de conexión. Por favor, intenta de nuevo.');
                },
                complete: function() {
                    $form.removeClass('loading');
                }
            });
        }
        
        function deleteFinanza(postId, row) {
            // Mostrar indicador de carga
            row.addClass('deleting');
            
            jQuery.ajax({
                url: rdFinanzasData.ajaxurl,
                type: 'POST',
                data: {
                    action: 'rd_delete_finanza',
                    nonce: $form.find('input[name="nonce"]').val(),
                    post_id: postId
                },
                success: function(response) {
                    if (response.success) {
                        // Eliminar fila de la tabla
                        row.fadeOut(300, function() {
                            row.remove();
                            
                            // Si no quedan registros, mostrar mensaje
                            if ($container.find('.finanzas-table tbody tr').length === 0) {
                                $container.find('.finanzas-table').replaceWith(
                                    '<div class="empty-state"><p>No hay registros de finanzas. ¡Añade uno nuevo!</p></div>'
                                );
                            }
                        });
                    } else {
                        alert(response.data.message || 'Error al eliminar el registro');
                        row.removeClass('deleting');
                    }
                },
                error: function() {
                    alert('Error de conexión. Por favor, intenta de nuevo.');
                    row.removeClass('deleting');
                }
            });
        }
        
        function filterFinanzas() {
            console.log('Filtrando finanzas...');
            const searchTerm = $searchInput.val().toLowerCase();
            const filterType = $filterType.val();
            console.log('Tipo de filtro:', filterType);
            
            // Get dates from picker data
            const datePickerData = $dateInput.data('daterangepicker');
            var fechaInicio = datePickerData && datePickerData.startDate ? datePickerData.startDate.format('YYYY-MM-DD') : '2025-01-01';
            var fechaFin = datePickerData && datePickerData.endDate ? datePickerData.endDate.format('YYYY-MM-DD') : new Date().toISOString().split('T')[0];
            if(fechaInicio >= fechaFin) {
                fechaInicio = '2024-01-01'; // Default start date
            }
            console.log('Fechas:', fechaInicio, fechaFin);
            // Mostrar indicador de carga
            $container.addClass('loading');
            
            jQuery.ajax({
                url: rdFinanzasData.ajaxurl,
                type: 'POST',
                data: {
                    action: 'rd_filter_finanzas',
                    nonce: rdFinanzasData.nonce,
                    searchTerm: searchTerm,
                    filterType: filterType,
                    fechaInicio: fechaInicio, // Add start date
                    fechaFin: fechaFin       // Add end date
                },
                success: function(response) {
                    if (response.success) {
                        // Actualizar la lista de finanzas
                        const $finanzasList = $container.find('#finanzas-list');
                        if (response.data && response.data.transacciones) {
                            $finanzasList.html(response.data.transacciones);
                        } else {
                            $finanzasList.html('<div class="empty-state"><p>No se encontraron registros.</p></div>');
                        }
                    } else {
                        console.error('Error al filtrar:', response.data);
                    }
                },
                error: function() {
                    alert('Error de conexión al filtrar los datos.');
                },
                complete: function() {
                    $container.removeClass('loading');
                }
            });
        }

        // Cargar la tabla paginada por AJAX al cargar la página
        filterFinanzas();
    });
})(jQuery);