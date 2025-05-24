<?php
/**
 * Clase para el shortcode de popup de citas
 *
 * @package RDTecnobelleza
 * @subpackage Shortcodes
 */

// Evitar acceso directo
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Clase que implementa el shortcode para el popup de citas
 */
class RD_Cita_Popup {
    /**
     * Instancia única (patrón Singleton)
     *
     * @var RD_Cita_Popup
     */
    private static $instance = null;
    
    /**
     * Contador para IDs únicos
     *
     * @var int
     */
    private $counter = 0;
    
    /**
     * Constructor
     */
    private function __construct() {
        // Registrar shortcode
        add_shortcode('cita_popup', array($this, 'render_shortcode'));
        
        // Registrar assets
        add_action('wp_enqueue_scripts', array($this, 'register_assets'));
    }
    
    /**
     * Obtener instancia única (Singleton)
     *
     * @return RD_Cita_Popup
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Registrar estilos y scripts
     */
    public function register_assets() {
        // Ruta a los assets
        $assets_dir = get_stylesheet_directory_uri() . '/includes/Shortcodes/assets';
        
        // Registrar CSS
        wp_register_style(
            'rd-cita-popup-style',
            $assets_dir . '/css/cita-popup.css',
            array(),
            filemtime( get_stylesheet_directory() . '/includes/Shortcodes/assets/css/cita-popup.css')
        );
        
        // Registrar JS
        wp_register_script(
            'rd-cita-popup-script',
            $assets_dir . '/js/cita-popup.js',
            array('jquery'),
            filemtime(get_stylesheet_directory() . '/includes/Shortcodes/assets/js/cita-popup.js'),
            true
        );
        
         // Inicializar el objeto global
        wp_localize_script('rd-cita-popup-script', 'rdCitaPopupConfig', array(
            'popups' => array() // Será llenado por cada instancia del shortcode
        ));
    }
    
    /**
     * Renderizar shortcode
     *
     * @param array $atts Atributos del shortcode
     * @return string HTML del shortcode
     */
    public function render_shortcode($atts) {
        // Incrementar contador para ID único
        $this->counter++;
        
        // Atributos por defecto
        $atts = shortcode_atts(array(
            'texto' => 'Reservar Soprano Titanium',
            'color' => '#038D71',
            'hover' => '#07DBB1',
            'class' => 'rd-appointment-button',      //Atributo de clase personalizado para estilos.
            'id' => 'cita-popup-' . $this->counter,
        ), $atts, 'cita_popup');
        
        // Encolar assets
        wp_enqueue_style('rd-cita-popup-style');
        wp_enqueue_script('rd-cita-popup-script');
        
        
        
        // Importante: Modificar esta parte para que el script funcione correctamente
      // Configuración para este popup específico
    $popup_config = array(
        'id' => $atts['id'],
        'buttonId' => 'open-' . $atts['id'],
        'closeClass' => 'close-' . $atts['id'],
        'contentClass' => 'content-' . $atts['id'],
    );
    
    // Añadir script inline para este popup específico
    wp_add_inline_script('rd-cita-popup-script', 
        'if (typeof rdCitaPopupConfig === "object") { 
            if (!rdCitaPopupConfig.popups) rdCitaPopupConfig.popups = [];
            rdCitaPopupConfig.popups.push(' . json_encode($popup_config) . ');
         }', 
        'before'
    );
        
        // Iniciar buffer de salida
        ob_start();
        
        // Incluir template
        include get_stylesheet_directory() . '/includes/Shortcodes/views/cita-popup-template.php';
        
        // Generar estilos inline específicos para esta instancia
        $this->render_inline_styles($atts);
        
        // Devolver contenido
        return ob_get_clean();
    }
    
    /**
     * Renderizar estilos inline específicos para esta instancia
     *
     * @param array $atts Atributos del shortcode
     */
    private function render_inline_styles($atts) {
 // Si ya se definió la clase con los estilos en el CSS (por ejemplo, 'rd-appointment-button'),
    // no imprime estilos inline.
    if ( !empty($atts['class']) && $atts['class'] === 'rd-appointment-button' ) {
        return;
    }
    // Si se requiere imprimir estilos inline, se haría aquí (aunque en este caso no lo queremos)        
        ?>
        <style>
            #open-<?php echo esc_attr($atts['id']); ?> {
                background-color: <?php echo esc_attr($atts['color']); ?>;
            }
            #open-<?php echo esc_attr($atts['id']); ?>:hover {
                background-color: <?php echo esc_attr($atts['hover']); ?>;
            }
        </style>
        <?php
    }
}

// Inicializar
RD_Cita_Popup::get_instance();