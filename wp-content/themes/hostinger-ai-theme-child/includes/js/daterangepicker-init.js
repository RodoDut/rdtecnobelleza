//javascript
/**
 * Date Range Picker Initialization Utility
 * 
 * Provides a reusable function to initialize daterangepicker elements.
 */
(function($) {
    /**
     * Initializes a daterangepicker instance on the provided element.
     * 
     * @param {jQuery} $element The jQuery object representing the input element.
     * @param {object} options Custom options to override daterangepicker defaults.
     * @param {boolean} autoUpdate Whether to automatically update the input value on selection. Defaults to false.
     */
    window.rdInitializeDateRangePicker = function($element, options = {}, autoUpdate = false) {
        if (!$element || !$element.length) {
            console.warn('DateRangePicker Init: Element not provided or not found.');
            return;
        }

        if (typeof $.fn.daterangepicker !== 'function') {
            console.error('DateRangePicker Init: daterangepicker library is not loaded.');
            return;
        }

        const defaultOptions = {
            autoUpdateInput: autoUpdate,
            opens: 'left', // Default opening direction
            locale: {
                cancelLabel: 'Limpiar',
                applyLabel: 'Aplicar',
                fromLabel: 'Desde',
                toLabel: 'Hasta',
                format: 'YYYY-MM-DD', // Default format, consistent with PHP
                daysOfWeek: ['Do', 'Lu', 'Ma', 'Mi', 'Ju', 'Vi', 'Sa'],
                monthNames: ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'],
                firstDay: 1
            }
        };

        const finalOptions = $.extend(true, {}, defaultOptions, options); // Deep merge options

        try {
            $element.daterangepicker(finalOptions);
            console.log('DateRangePicker initialized for element:', $element.attr('class'));

            // Basic event handlers to manage autoUpdateInput behavior
            if (!autoUpdate) {
                $element.on('apply.daterangepicker', function(ev, picker) {
                    $(this).val(picker.startDate.format(finalOptions.locale.format) + ' - ' + picker.endDate.format(finalOptions.locale.format));
                });

                $element.on('cancel.daterangepicker', function(ev, picker) {
                    $(this).val('');
                });
            }

        } catch (error) {
            console.error('Error initializing daterangepicker:', error, 'on element:', $element);
        }
    };

})(jQuery);
