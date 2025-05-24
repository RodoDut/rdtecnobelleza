<?php
/**
 * Shortcode para reconocimiento de voz
 * 
 * Este archivo maneja toda la funcionalidad relacionada con el shortcode
 * de reconocimiento de voz, incluyendo scripts, estilos y la lógica del shortcode.
 */

// Evitar acceso directo al archivo
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Clase para manejar el shortcode de reconocimiento de voz
 */
class Voz_Recognition_Shortcode {
    
    /**
     * Constructor
     */
    public function __construct() {
          
        // Añadir estilos en el head
        add_action('wp_head', array($this, 'add_inline_styles'));
        
        // Incluir script global para los botones de control
        add_action('wp_footer', array($this, 'include_inline_script'));
        
        // Asegurarse de que jQuery esté disponible
        add_action('wp_enqueue_script', array($this, 'enqueue_jquery'));
        
        // Registrar los scripts necesarios
        add_action('wp_enqueue_scripts', array($this, 'register_scripts'));

        // Agregar filtro para cargar scripts como módulos
        add_filter('script_loader_tag', array($this, 'add_module_to_scripts'), 10, 3);

         // Registrar el shortcode
         add_shortcode('reconocimiento_voz', array($this, 'render_shortcode'));
         add_shortcode('boton_voz', array($this, 'render_button_shortcode'));
    }
    
    /**
     * Asegurarse de que jQuery esté disponible
     */
    public function enqueue_jquery() {
        wp_enqueue_script('jquery');
    }
 
    
 /**
 * Renderiza el shortcode
 */
 
public function render_shortcode($atts) {
    // Procesar atributos
    $atts = shortcode_atts(array(
        'idioma' => 'es-AR',
        'texto_inicial' => 'Presiona el botón para comenzar...',
        'texto_boton' => 'Iniciar reconocimiento de voz',
        'id' => 'voz_' . uniqid(),
        'visible' => 'true'
    ), $atts, 'reconocimiento_voz');
    
    // Generar un ID único si no se proporciona
    $id_unico = $atts['id'];
    
    // Encolar el script solo cuando se usa el shortcode
    wp_enqueue_script('reconocimiento-voz-script');
    

    wp_enqueue_script('spetial-voice-script');
    
    // Determinar si debe estar visible inicialmente
    $display_style = ($atts['visible'] === 'true') ? 'block' : 'none';
    
    // HTML para el shortcode
    $html = '<div id="container_' . $id_unico . '" class="reconocimiento-voz-container" style="display: ' . $display_style . ';">';
    $html .= '<button id="btn_' . $id_unico . '" class="boton-reconocimiento" data-idioma="' . esc_attr($atts['idioma']) . '">' . esc_html($atts['texto_boton']) . '</button>';
    $html .= '<div id="' . $id_unico . '" class="resultado-reconocimiento">' . esc_html($atts['texto_inicial']) . '</div>';
    $html .= '</div>';
    
    return $html;
}
    
    /**
     * Registra los scripts necesarios
     */
    public function register_scripts() {
        $js_func_file = get_stylesheet_directory() . '/includes/Voz-Recognition/js/spetial-voice-functions.js';
        $version_func = file_exists($js_func_file) ? filemtime($js_func_file) : '1.0.0'; // Versión por defecto si el archivo no existe
        // Registrar el script de funciones de voz.
        wp_register_script(
            'spetial-voice-script', 
            $this->get_js_url() . '/spetial-voice-functions.js', 
            array(), 
            $version_func, 
            array()
        );
        
        $js_rec_file = get_stylesheet_directory() . '/includes/Voz-Recognition/js/reconocimiento-voz.js';
        $version_rec = file_exists($js_rec_file) ? filemtime($js_rec_file) : '1.0.0'; // Versión por defecto si el archivo no existe
        // Registrar el script de reconocimiento de voz
        wp_register_script(
            'reconocimiento-voz-script', 
            $this->get_js_url() . '/reconocimiento-voz.js', 
            array(), 
            $version_rec, 
            array()
        );
        }
    
    
    /**
 * Obtiene la URL correcta para los archivos JS
 */
private function get_js_url() {
    // Obtener la URL base del tema
    $theme_url = get_template_directory_uri();
    
    // Para temas hijos, usar la URL del tema hijo
    if (is_child_theme()) {
        $theme_url = get_stylesheet_directory_uri();
    }
    
    // Ruta completa a la carpeta js
    $js_url = $theme_url . '/includes/Voz-Recognition/js';
    
    // Depurar la ruta (opcional, quitar en producción)
    error_log('URL de JavaScript: ' . $js_url);
    
    return $js_url;
}

/**
 * Añade type="module" a los scripts de voz
 */
public function add_module_to_scripts($tag, $handle, $src) {
    if (in_array($handle, array('reconocimiento-voz-script', 'spetial-voice-script'))) {
        return '<script type="module" src="' . esc_url($src) . '"></script>';
    }
    return $tag;
}

/**
 * Incluir script global para los botones de control
 */
 
public function include_inline_script() {
    ?>
    <script>
    (function($) {
        // Función global para mostrar/ocultar el reconocimiento de voz
        window.toggleVozRecognition = function(id) {
            const container = $('#container_' + id);
            
            if (container.length) {
                if (container.is(':visible')) {
                    container.hide();
                } else {
                    container.show();
                }
                return true;
            }
            return false;
        };
        
        // Función global para mostrar el reconocimiento de voz
        window.showVozRecognition = function(id) {
            const container = $('#container_' + id);
            
            if (container.length) {
                container.show();
                return true;
            }
            return false;
        };
        
        // Función global para ocultar el reconocimiento de voz
        window.hideVozRecognition = function(id) {
            const container = $('#container_' + id);
            
            if (container.length) {
                container.hide();
                return true;
            }
            return false;
        };
    })(jQuery);
    </script>
    <?php
}
 
    /**
     * Añade estilos inline en el head
     */
    public function add_inline_styles() {
        echo '<style>
            .reconocimiento-voz-container {
                margin: 20px 0;
                padding: 15px;
                border: 1px solid #ddd;
                border-radius: 5px;
                background-color: #f9f9f9;
            }
            .boton-reconocimiento {
                background-color: #0073aa;
                color: white;
                border: none;
                padding: 10px 15px;
                border-radius: 4px;
                cursor: pointer;
                font-size: 16px;
            }
            .boton-reconocimiento:hover {
                background-color: #005177;
            }
            .resultado-reconocimiento {
                margin-top: 15px;
                padding: 10px;
                min-height: 50px;
                border: 1px solid #eee;
                background-color: white;
            }
        </style>';
    }
}

// Inicializar la clase
new Voz_Recognition_Shortcode();