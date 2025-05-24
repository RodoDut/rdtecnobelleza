 import { initCharts } from './finanzas-charts.js';
 // Esperar a que el DOM esté listo
    jQuery(document).ready(function() {
        console.log('DOM cargado, inicializando finanzas dashboard');
        
        // Buscar todos los contenedores de balance.
        jQuery('.balance-page').each(function() {
            const container = jQuery(this);
            const containerId = container.attr('id');
            
            if (!containerId) {
                console.error('Contenedor de balance sin ID');
                return;
            }
            
            console.log('Inicializando dashboard para:', containerId);
            
            // Inicializar el selector de fechas
//            const dateInput = container.find('.date-input');
            const dateInput = container.find('[id^="date-range-"]');
            if (dateInput.length && typeof window.rdInitializeDateRangePicker === 'function') {
                window.rdInitializeDateRangePicker(dateInput, {
                    locale: { format: 'DD/MM/YYYY' }
                }, true);

                dateInput.on('apply.daterangepicker', function(ev, picker) {
                    // handleFilter(container, dateInput); // Opcional: Filtrar automáticamente
                });
                
                dateInput.on('cancel.daterangepicker', function(ev, picker) {
                    // handleFilter(container, dateInput); // Opcional: Filtrar con fechas vacías
                });
            } else {
                if (!dateInput.length) {
                    console.warn('Finanzas Charts: Date input .date-input not found in container:', containerId);
                } else {
                    console.error('Finanzas Charts: rdInitializeDateRangePicker function not found.');
                }
            }
            
            // Inicializar los tabs
            const tabTriggers = container.find('.tab-trigger');
            const tabContents = container.find('.tab-content');
            
            tabTriggers.on('click', function() {
                const trigger = jQuery(this);
                const tabId = trigger.data('tab');
                
                console.log('Tab seleccionado:', tabId);
                
                // Remover clase activa de todos los tabs
                tabTriggers.removeClass('active');
                tabContents.removeClass('active');
                
                // Añadir clase activa al tab seleccionado
                trigger.addClass('active');
                jQuery('#' + tabId + '-tab').addClass('active');
                
                // Si es el tab de estadísticas, inicializar/actualizar gráficos
                if (tabId === 'estadisticas') {
                    initCharts(container, containerId);
                }
            });
            
            // Inicializar botón de filtro
            const filterButton = container.find('.filter-button');
            filterButton.on('click', function() {
                handleFilter(container, dateInput);
            });
            
            // Inicializar gráficos si el tab de estadísticas está activo
            if (container.find('.tab-trigger[data-tab="estadisticas"]').hasClass('active')) {
                initCharts(container, containerId);
            }
        });
    });


jQuery(document).ready(function($) {
    // Initialize date range picker
    $('.date-input').first().daterangepicker({
        autoUpdateInput: false,
        locale: {
            format: 'DD/MM/YYYY',
            cancelLabel: 'Limpiar',
            applyLabel: 'Aplicar'
        }
    });

    $('.date-input').first().on('apply.daterangepicker', function(ev, picker) {
        $(this).val(picker.startDate.format('DD/MM/YYYY') + ' - ' + picker.endDate.format('DD/MM/YYYY'));
        filterFinanzas(); // Call filter function when date range is selected
    });

    $('.date-input').first().on('cancel.daterangepicker', function(ev, picker) {
        $(this).val('');
        filterFinanzas(); // Call filter function when date range is cleared
    });

    // Handle filter button click
    $('.filter-button').on('click', function() {
        filterFinanzas();
    });

    // Handle type filter change
    $('#filter-finanzas-type').on('change', function() {
        filterFinanzas();
    });

    // Function to filter finanzas
    function filterFinanzas() {
        const container = $('.balance-page');
        const dateRange = $('.date-input').first().val();
        let fechaInicio = '';
        let fechaFin = '';
        let partes = [];
        
        if (dateRange && dateRange.includes(' - ')) {
                partes = dateRange.split(' - ');
            } else {
                partes[0] = '2025-01-01'; // Default start date
                partes[1] = new Date().toISOString().split('T')[0]; // Default end date
            }
        fechaInicio = partes[0];
        fechaFin = partes[1];

        const type = $('#filter-finanzas-type').val();
        const nonce = container.data('nonce');

        console.log('Filtrando desde: ' + fechaInicio + ' hasta: ' + fechaFin + ' tipo: ' + type);
        // Show loading state
        container.addClass('loading');
        
        // Make AJAX request
        $.ajax({
            url: rdFinanzasData.ajaxurl,
            type: 'POST',
            data: {
                action: 'rd_filter_finanzas',
                nonce: nonce,
                fechaInicio: fechaInicio,
                fechaFin: fechaFin
            },
            success: function(response) {
                if (response.success) {
                    // Update the table content
                    $('#finanzas-list').html(response.data.html);
                } else {
                    alert('Error al filtrar los registros');
                }
            },
            error: function() {
                alert('Error en la conexión');
            },
            complete: function() {
                // Remove loading state
                container.removeClass('loading');
            }
        });
    }
});