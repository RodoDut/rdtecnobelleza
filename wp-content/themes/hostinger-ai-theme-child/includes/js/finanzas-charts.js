/**
 * RD Tecnobelleza - Finanzas Dashboard
 * 
 * Este script maneja la funcionalidad del panel de balances
 */

import { FinanzasChartsController } from './finanzas-charts-controller.js';

// Exportar la función initCharts para uso como módulo ES6
export function initCharts(container, containerId) {
    const controller = new FinanzasChartsController(window.rdFinanzasGraficos, Chart);
    controller.initCharts(containerId);
}

(function(jQuery) {
    // Exponer la función initCharts globalmente para que pueda ser llamada desde finanzas.js
    window.initCharts = initCharts;

    // Función para manejar el filtrado
    function handleFilter(container, dateInput) {
        console.log('Aplicando filtro en Charts');
        
        if (!rdFinanzasData || !rdFinanzasData.ajaxurl) {
            console.error('No se encontró la URL de AJAX');
            return;
        }
        
        const dateRange = dateInput.val();
        if (!dateRange) {
            console.warn('No se seleccionó un rango de fechas');
            return;
        }
        
        const dates = dateRange.split(' - ');
        const startDate = dates[0];
        const endDate = dates.length > 1 ? dates[1] : dates[0];
        
        const startDateFormatted = moment(startDate, 'DD/MM/YYYY').format('YYYY-MM-DD');
        const endDateFormatted = moment(endDate, 'DD/MM/YYYY').format('YYYY-MM-DD');
        
        console.log('Filtrando desde', startDateFormatted, 'hasta', endDateFormatted);
        
        container.addClass('loading');
        
        jQuery.ajax({
            url: rdFinanzasData.ajaxurl,
            type: 'POST',
            data: {
                action: 'rd_filter_finanzas',
                nonce: rdFinanzasData.nonce,
                fecha_inicio: startDateFormatted,
                fecha_fin: endDateFormatted
            },
            success: function(response) {
                console.log('Respuesta del servidor:', response);
                
                if (response.success && response.data) {
                    updateDashboard(container, response.data);
                } else {
                    console.error('Error en la respuesta del servidor:', response);
                    alert('Error al filtrar los datos. Por favor, intenta de nuevo.');
                }
            },
            error: function(xhr, status, error) {
                console.error('Error AJAX:', error);
                alert('Error de conexión. Por favor, verifica tu conexión a internet e intenta de nuevo.');
            },
            complete: function() {
                container.removeClass('loading');
            }
        });
    }
    
    // Función para actualizar el dashboard con nuevos datos
    function updateDashboard(container, data) {
        console.log('Actualizando dashboard con nuevos datos');
        
        if (data.totales) {
            container.find('.stat-value').each(function() {
                const stat = jQuery(this);
                const statType = stat.data('stat-type');
                
                if (data.totales[statType]) {
                    stat.text(data.totales[statType]);
                }
            });
        }
        
        if (data.transacciones) {
            const transactionsList = container.find('.transactions-list');
            if (transactionsList.length) {
                transactionsList.empty();
                
                data.transacciones.forEach(function(transaction) {
                    const item = jQuery('<div class="transaction-item"></div>');
                    item.html(`
                        <div class="transaction-info">
                            <div class="transaction-icon">
                                <i class="icon-user"></i>
                            </div>
                            <div class="transaction-details">
                                <p class="transaction-name">${transaction.name}</p>
                                <p class="transaction-time">${transaction.time}</p>
                            </div>
                        </div>
                        <div class="transaction-amount">${transaction.amount}</div>
                    `);
                    
                    transactionsList.append(item);
                });
            }
        }
        
        if (container.find('.tab-trigger[data-tab="estadisticas"]').hasClass('active') && data.graficos) {
            if (Chart.instances) {
                Object.values(Chart.instances).forEach(function(instance) {
                    const chartId = instance.canvas.id;
                    
                    if (chartId.includes('income-chart') && data.graficos.ingresos) {
                        instance.data.labels = data.graficos.ingresos.labels;
                        instance.data.datasets[0].data = data.graficos.ingresos.data;
                        instance.update();
                    } else if (chartId.includes('expenses-chart') && data.graficos.gastos) {
                        instance.data.labels = data.graficos.gastos.labels;
                        instance.data.datasets[0].data = data.graficos.gastos.data;
                        instance.update();
                    } else if (chartId.includes('comparison-chart') && data.graficos.comparacion) {
                        instance.data.labels = data.graficos.comparacion.labels;
                        instance.data.datasets[0].data = data.graficos.comparacion.actual;
                        instance.data.datasets[1].data = data.graficos.comparacion.anterior;
                        instance.update();
                    }
                });
            }
        }
    }
})(jQuery); // Pasamos jQuery como parámetro})(jQuery); // Pasamos jQuery como parámetro