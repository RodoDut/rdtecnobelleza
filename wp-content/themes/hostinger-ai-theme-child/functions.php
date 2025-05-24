<?php

require_once get_stylesheet_directory() . '/includes/finanzas-shortcode.php';
require_once get_stylesheet_directory() . '/includes/usuarios-custom.php';
require_once get_stylesheet_directory() . '/includes/reservas-ssa.php';
require_once get_stylesheet_directory() . '/includes/seguridad.php';
require_once get_stylesheet_directory() . '/includes/styles-scripts.php';
require_once get_stylesheet_directory() . '/includes/helpers.php';
/**
 * Incluir el shortcode de reconocimiento de voz
 */
require_once get_stylesheet_directory() . '/includes/Voz-Recognition/voz-recognition-shortcode.php';
                        

// Copiar estilos del tema padre
function hostinger_ai_child_enqueue_styles() {
    wp_enqueue_style( 'parent-style', get_template_directory_uri() . '/style.css' );
}
add_action( 'wp_enqueue_scripts', 'hostinger_ai_child_enqueue_styles' );

//Se agrega el hook para pedir que los términos sean aceptados.
function agregar_hidden_terms_accepted() {
    if ( isset( $_GET['terms_accepted'] ) && $_GET['terms_accepted'] == '1' ) {
        echo '<input type="hidden" name="terms_accepted" value="1">';
    }
}
add_action( 'woocommerce_register_form', 'agregar_hidden_terms_accepted' );



//Función para validar la creación de un nuevo usuario sólo si aceptó los términos.
function validar_aceptacion_terminos( $username, $email, $errors ) {
    // Verifica que se haya enviado el campo hidden con valor "1"
    if ( ! isset( $_POST['terms_accepted'] ) || $_POST['terms_accepted'] != '1' ) {
        $errors->add( 'terms_error', __( 'Debes aceptar los términos y condiciones para registrarte.', 'woocommerce' ) );
    }
}
add_action( 'woocommerce_register_post', 'validar_aceptacion_terminos', 10, 3 );


//Función para asignar o crear usuarios que realicen reservas mediante el plugin SSA

// Asegurar que el evento se programa solo una vez
if (!wp_next_scheduled('verificar_correos_ssa_event')) {
    wp_schedule_event(time(), 'hourly', 'verificar_correos_ssa_event');
}

add_action('verificar_correos_ssa_event', 'leer_correos_ssa_y_crear_reservas');

// -----------------------------------------------------

function mostrar_debug_log_en_admin() {
    if (current_user_can('manage_options')) {
        echo '<h3>Errores de debug.log</h3><pre>';
        echo file_get_contents(WP_CONTENT_DIR . '/debug.log');
        echo '</pre>';
    }
}
add_action('admin_notices', 'mostrar_debug_log_en_admin');

//Shortcode para botón Whatsapp
function boton_whatsapp_shortcode($atts) {
    $atts = shortcode_atts([
        'numero' => PHONE_NUM,
        'mensaje' => 'Hola RD-Tecno Belleza, quisiera saber más acerca de sus servicios de alquiler de equipos Soprano Titanium.',
        'texto' => '💬 Enviar mensaje por WhatsApp',
    ], $atts);

    $link = "https://wa.me/{$atts['numero']}?text=" . urlencode($atts['mensaje']);

    return '<a href="' . esc_url($link) . '" target="_blank" style="display:inline-block;padding:10px 20px;background-color:#25D366;color:white;border-radius:5px;text-decoration:none;">' . esc_html($atts['texto']) . '</a>';
}
add_shortcode('boton_whatsapp', 'boton_whatsapp_shortcode');

//-----------------------------------------------------

//Para ventana emergente de botón de reserva.
/**
 * Cargar shortcode de popup de citas
 */
require_once get_stylesheet_directory() . '/includes/Shortcodes/class-rd-cita-popup.php';

//Para usar el Webhook de finanzas con n8n y cargar los datos por voz.
function mis_scripts_finanzas() {
    wp_enqueue_script(
      'finanzas-webhook',
      get_stylesheet_directory_uri() . '/includes/js/finanzas-webhook.js',
      array(), // sin dependencias
      '1.0',
      true   // cargar en footer
    );
  }
  add_action('wp_enqueue_scripts', 'mis_scripts_finanzas');
  
?>