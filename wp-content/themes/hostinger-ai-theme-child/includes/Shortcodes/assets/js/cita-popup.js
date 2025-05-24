/**
 * JavaScript para el popup de citas
 *
 * @package RDTecnobelleza
 * @subpackage Shortcodes
 */

jQuery(document).ready(function($) {
    'use strict';
    
    console.log('Script de cita-popup cargado');
    
    // Usar delegación de eventos para los botones de cita
    $(document).on('click', '.rd-appointment-button', function(e) {
        e.preventDefault();
        
        // Obtener el ID del popup a partir del ID del botón
        var buttonId = $(this).attr('id');      //Se obtiene el id del botón que fue clickeado.
        var popupId = buttonId.replace('open-', '');    //Se elimina prefijo "open-" del id del botón.
        
        console.log('Botón clickeado:', buttonId, 'Intentando abrir popup:', popupId);
        
        // Abrir el popup
        $('#' + popupId).addClass('active');
        $('body').css('overflow', 'hidden');
    });
    
    // Delegación de eventos para botones de cierre
    $(document).on('click', '.rd-close-popup', function() {
        var popup = $(this).closest('.rd-appointment-popup');
        popup.removeClass('active');
        $('body').css('overflow', '');
    });
    
    // Cerrar al hacer clic fuera del contenido
    $(document).on('click', '.rd-appointment-popup', function(e) {
        if (e.target === this) {
            $(this).removeClass('active');
            $('body').css('overflow', '');
        }
    });
    
    // Cerrar con la tecla ESC
    $(document).on('keydown', function(e) {
        if (e.key === 'Escape') {
            $('.rd-appointment-popup.active').removeClass('active');
            $('body').css('overflow', '');
        }
    });
    
    console.log('Eventos de popup inicializados');
});