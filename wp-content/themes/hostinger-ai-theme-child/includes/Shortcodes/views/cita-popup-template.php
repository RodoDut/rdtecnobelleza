<?php
/**
 * Template para el popup de citas
 *
 * @package RDTecnobelleza
 * @subpackage Shortcodes
 */

// Evitar acceso directo
if (!defined('ABSPATH')) {
    exit;
}

// Generar IDs y clases únicas
$button_id = 'open-' . $atts['id'];
$popup_id = $atts['id'];
$close_class = 'close-' . $atts['id'];
$content_class = 'content-' . $atts['id'];
?>

<!-- Botón para abrir el popup -->
<button id="<?php echo esc_attr($button_id); ?>" class="rd-appointment-button">
    <?php echo esc_html($atts['texto']); ?>
</button>

<!-- Estructura del popup -->
<div id="<?php echo esc_attr($popup_id); ?>" class="rd-appointment-popup">
    <div class="rd-appointment-popup-content <?php echo esc_attr($content_class); ?>">
        <span class="rd-close-popup <?php echo esc_attr($close_class); ?>">&times;</span>
        <div class="rd-appointment-popup-body">
            <!-- Aquí se carga el formulario de citas -->
            <?php echo do_shortcode('[ssa_booking]'); ?>
        </div>
    </div>
</div>