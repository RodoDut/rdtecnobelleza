<?php
/**
 * Shortcode para mostrar el panel de balances
 * 
 * Para incluir este archivo en WordPress, añade esta línea a tu functions.php:
 * require_once get_template_directory() . '/ruta/a/finanzas-shortcode.php';
 */

// Evitar acceso directo al archivo
if (!defined('ABSPATH')) {
    exit;
}

//Funciones generales
require_once  __DIR__ . '/finanzas-helpers.php';
/**
 * Registrar y cargar los estilos y scripts necesarios
 */
function rd_balances_enqueue_assets() {
    $css_file_path = get_stylesheet_directory() . '/finanzas-style.css';
    $css_url = get_stylesheet_directory_uri() . '/finanzas-style.css';
    $css_version = file_exists($css_file_path) ? filemtime($css_file_path) : '1.0.0';
    
    // Registrar estilos
    wp_register_style('rd-balances-styles', $css_url, array(), $css_version);
    
    // Primero registrar y cargar jQuery
    wp_enqueue_script('jquery');
    
    // Registrar y cargar Moment.js (dependencia de daterangepicker)
    wp_register_script('moment-js', 'https://cdn.jsdelivr.net/momentjs/latest/moment.min.js', array('jquery'), '2.29.1', true);
    wp_enqueue_script('moment-js');
    
    // Registrar y cargar daterangepicker después de moment.js
    wp_register_style('daterangepicker-css', 'https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css', array(), '3.1');
    wp_register_script('daterangepicker-js', 'https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js', array('jquery', 'moment-js'), '3.1', true);
    wp_enqueue_style('daterangepicker-css');
    wp_enqueue_script('daterangepicker-js');
    
    // Registrar el script principal de finanzas.js
    $finanzas_js_path = get_stylesheet_directory() . '/includes/js/finanzas.js';
    $finanzas_js_url = get_stylesheet_directory_uri() . '/includes/js/finanzas.js';
    $finanzas_js_version = file_exists($finanzas_js_path) ? filemtime($finanzas_js_path) : '1.0.0';
    
    wp_register_script(
        'rd-finanzas-js',
        $finanzas_js_url,
        array('jquery', 'moment-js', 'daterangepicker-js'), // Depends on daterangepicker
        $finanzas_js_version,
        true
    );

   // Asegurarse de que los scripts del array se carguen como un módulos ES6
    // Esto es necesario para que funcione con la sintaxis de import/export de ES6 
    
    add_filter('script_loader_tag', function($tag, $handle, $src) {
        $module_handles = array('rd-finanzas-js', 'rd-finanzas-charts-js');
        if (in_array($handle, $module_handles)) {
            return '<script type="module" src="' . esc_url($src) . '"></script>';
        }
        return $tag;
    }, 10, 3);

    // Registrar el script de inicialización de daterangepicker
    $daterangepicker_init_js_path = get_stylesheet_directory() . '/includes/js/daterangepicker-init.js';
    $daterangepicker_init_js_url = get_stylesheet_directory_uri() . '/includes/js/daterangepicker-init.js';
    $daterangepicker_init_js_version = file_exists($daterangepicker_init_js_path) ? filemtime($daterangepicker_init_js_path) : '1.0.0';

    wp_register_script(
        'rd-daterangepicker-init-js',
        $daterangepicker_init_js_url,
        array('jquery', 'daterangepicker-js'), // Depends on jQuery and daterangepicker
        $daterangepicker_init_js_version,
        true
    );
    
    // Registrar el script de gráficos
    $charts_js_path = get_stylesheet_directory() . '/includes/js/finanzas-charts.js';
    $charts_js_url = get_stylesheet_directory_uri() . '/includes/js/finanzas-charts.js';
    $charts_js_version = file_exists($charts_js_path) ? filemtime($charts_js_path) : '1.0.0';

    wp_register_script('chartjs', 'https://cdn.jsdelivr.net/npm/chart.js', array(), '4.4.0', true);

    wp_register_script(
        'rd-finanzas-charts-js',
        $charts_js_url,
        array('jquery', 'chartjs', 'rd-daterangepicker-init-js'), // Depends on the init script
        $charts_js_version,
        true
    );

    
    // Pasar variables a JavaScript
    wp_localize_script('rd-finanzas-js', 'rdFinanzasData', array(
        'ajaxurl' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('rd_balances_nonce'),
        'debug' => current_user_can('administrator')
    ));
    
    // Registrar el script del formulario
    $form_js_file_path = get_stylesheet_directory() . '/includes/js/finanzas-form.js';
    $form_js_url = get_stylesheet_directory_uri() . '/includes/js/finanzas-form.js';
    $form_js_version = file_exists($form_js_file_path) ? filemtime($form_js_file_path) : '1.0.0';

    wp_register_script(
        'rd-finanzas-form-js',
        $form_js_url,
        array('jquery', 'rd-finanzas-js', 'rd-daterangepicker-init-js'), // Depends on the init script
        $form_js_version,
        true
    );
    
    // Cargar Dashicons
    wp_enqueue_style('dashicons');
}
add_action('wp_enqueue_scripts', 'rd_balances_enqueue_assets');


//----------------------------

function verificar_meta_fecha() {
    global $wpdb;
    $count = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->postmeta} WHERE meta_key = 'fecha'");
    error_log("Total de registros con 'fecha': " . $count);
}
add_action('init', 'verificar_meta_fecha');


//----------------------------


// =================================================================
// 2. LÓGICA PRINCIPAL DEL SHORTCODE
// =================================================================

function rd_balances_shortcode($atts) {
    // 2.1 Configuración inicial y cargar scripts en orden correcto
    wp_enqueue_style('rd-balances-styles');
    wp_enqueue_script('jquery');
    wp_enqueue_script('moment-js');
    wp_enqueue_style('daterangepicker-css');
    wp_enqueue_script('daterangepicker-js');
    wp_enqueue_script('rd-daterangepicker-init-js'); // Enqueue the init script
    wp_enqueue_script('chartjs');
    wp_enqueue_script('rd-finanzas-js');
    wp_enqueue_script('rd-finanzas-charts-js');
    wp_enqueue_script('rd-finanzas-form-js');
    
    $atts = shortcode_atts([
        'mostrar_transacciones' => 'si',
        'mostrar_pagos' => 'si',
        'limite' => 5
    ], $atts);
    
    // 2.2 Obtener datos
    agregar_categorias_finanza();
    $stats = rd_obtener_datos_balance($atts);
    $unique_id = uniqid('rd-balance-');
    $nonce = wp_create_nonce('rd_balances_nonce');

    // 2.3 Renderizar vista
    ob_start();
    include __DIR__ . '/templates/balance-container.php';
    return ob_get_clean();
}
add_shortcode('rd_balances', 'rd_balances_shortcode');


// =================================================================
// 3. FUNCIONES DE DATOS REFACTORIZADAS
// =================================================================
function rd_obtener_datos_balance($atts = [], $filtros = []) {
    $query = rd_get_finanzas_data($filtros);
    
    if (!$query->have_posts()) {
        rd_finanzas_log('No se encontraron transacciones', 'info');
        return [];
    }

    $meses = rd_agrupar_por_mes($query->posts);
    
    return [
        'totales' => rd_calcular_totales($query->posts),
        'meses' => $meses,
        'transacciones' => rd_obtener_transacciones_recientes($query->posts, $atts['limite']),
        'graficos' => rd_preparar_datos_graficos($meses),
        'paginacion' => rd_generar_paginacion($query)
    ];
}

function rd_get_finanzas_data($args = []) {
    $defaults = [
        'post_type' => 'finanzas',
        'posts_per_page' => -1,
        'paged' => rd_get_pagina_actual(),
        'orderby' => 'date',
        'meta_key' => 'fecha',
        'order' => 'DESC',
        'meta_query' => []
    ];

    // Add date filters if provided
    if (!empty($args['fecha_inicio']) || !empty($args['fecha_fin'])) {
        $date_query = [];

        if (!empty($args['fecha_inicio'])) {
            $date_query['after'] = $args['fecha_inicio'];
        }

        if (!empty($args['fecha_fin'])) {
            $date_query['before'] = $args['fecha_fin'];
        }

        $date_query['inclusive'] = true;
        $defaults['date_query'] = [$date_query];
    }

    $query = new WP_Query(wp_parse_args($args, $defaults));

    // Debug
  
    return $query;
}    
    

// =================================================================
// 4. FUNCIONALIDADES ADICIONALES REFACTORIZADAS
// =================================================================
// 4.1 AJAX
function rd_ajax_filter_finanzas() {
    try {
        check_ajax_referer('rd_balances_nonce', 'nonce'); 

        $filtros = [
            'search' => sanitize_text_field($_POST['searchTerm'] ?? ''),
            'tipo' => sanitize_text_field($_POST['filterType'] ?? ''),
            'fecha_inicio' => isset($_POST['fechaInicio']) ? sanitize_text_field($_POST['fechaInicio']) : '',
            'fecha_fin' => isset($_POST['fechaFin']) ? sanitize_text_field($_POST['fechaFin']) : ''
        ];
        $page = isset($_POST['page']) ? max(1, intval($_POST['page'])) : 1;
        $posts_per_page = 10;
        $args = [
            'post_type' => 'finanzas',
            'posts_per_page' => $posts_per_page,
            'paged' => $page,
            'orderby' => 'meta_value',
            'meta_key' => 'fecha',
            'order' => 'DESC',
            'meta_query' => [
                'relation' => 'AND',
            ]
        ];
        if (!empty($filtros['search'])) {
            $args['s'] = $filtros['search'];
        }
        if (!empty($filtros['tipo'])) {
            $args['meta_query'][] = [
                'key' => 'tipo',
                'value' => $filtros['tipo'],
                'compare' => '='
            ];
        }
        if (!empty($filtros['fecha_inicio']) && !empty($filtros['fecha_fin'])) {
            $args['meta_query'][] = [
                'key' => 'fecha',
                'value' => [$filtros['fecha_inicio'], $filtros['fecha_fin']],
                'compare' => 'BETWEEN',
                'type' => 'DATE'
            ];
        } elseif (!empty($filtros['fecha_inicio'])) {
            $args['meta_query'][] = [
                'key' => 'fecha',
                'value' => $filtros['fecha_inicio'],
                'compare' => '>=',
                'type' => 'DATE'
            ];
        } elseif (!empty($filtros['fecha_fin'])) {
            $args['meta_query'][] = [
                'key' => 'fecha',
                'value' => $filtros['fecha_fin'],
                'compare' => '<=',
                'type' => 'DATE'
            ];
        }
        if (count($args['meta_query']) <= 1) {
            unset($args['meta_query']['relation']);
        }
        $query = new WP_Query($args);
        $posts = $query->posts;
        $current_page = $page;
        $total_pages = $query->max_num_pages;
        ob_start();
        include __DIR__ . '/template-parts/finanzas-table.php'; 
        $html = ob_get_clean();
        wp_send_json_success([
            'transacciones' => $html,
            'current_page' => $current_page,
            'total_pages' => $total_pages
        ]);
    } catch (Exception $e) {
        error_log('Error in rd_ajax_filter_finanzas: ' . $e->getMessage());
        wp_send_json_error(['message' => $e->getMessage()]);
    }
}
add_action('wp_ajax_rd_filter_finanzas', 'rd_ajax_filter_finanzas');
add_action('wp_ajax_nopriv_rd_filter_finanzas', 'rd_ajax_filter_finanzas');

// 4.2 Exportación CSV
function rd_export_finanzas_csv() {
    try {
        check_admin_referer('rd_export_csv', 'nonce');
        
        $query = rd_get_finanzas_data([
            'posts_per_page' => -1,
            'paged' => 1
        ]);
        
        // Generar CSV usando template
        include __DIR__ . '/templates/csv-export.php';
        
    } catch (Exception $e) {
        wp_die('Error en exportación: ' . $e->getMessage());
    }
}
add_action('admin_post_rd_export_csv', 'rd_export_finanzas_csv');
add_action('admin_post_nopriv_rd_export_csv', 'rd_export_finanzas_csv');


/**
 * AJAX: Crear o actualizar post de finanzas
 */
function rd_ajax_save_finanza() {
    try {
        // Verificar nonce
        check_ajax_referer('rd_finanzas_crud_nonce', 'nonce');
        
        // Verificar permisos
        if (!current_user_can('edit_posts')) {
            throw new Exception(__('No tienes permisos para realizar esta acción.', 'rd-finanzas'));
        }
        
        // Sanitizar datos
        $data = rd_sanitize_finanzas_data($_POST);
        
        $categoria = get_terms($data['categoria']);
        //error_log('data - categoria : ' . $categoria);
        
        // Validar datos requeridos
        if (empty($data['concepto']) || empty($data['fecha']) || empty($data['tipo']) || !isset($data['monto']) || empty($categoria) || empty($data['metodo_pago'])) {
            throw new Exception(__('Faltan campos requeridos.', 'rd-finanzas'));
        }
        
        // Preparar datos del post
        $post_data = [
            'post_title' => $data['concepto'],
            'post_type' => 'finanzas',
            'post_status' => 'publish',
        ];
        
        // Si es una actualización, añadir el ID
        if (!empty($_POST['post_id'])) {
            $post_data['ID'] = absint($_POST['post_id']);
            
            // Verificar que el post existe y es del tipo correcto
            $post = get_post($post_data['ID']);
            if (!$post || $post->post_type !== 'finanzas') {
                throw new Exception(__('El registro no existe.', 'rd-finanzas'));
            }
        }
        
        // Insertar o actualizar post
        $post_id = wp_insert_post($post_data, true);
        
        if (is_wp_error($post_id)) {
            throw new Exception($post_id->get_error_message());
        }
        
        // Guardar metadatos
        update_post_meta($post_id, 'tipo', $data['tipo']);
        update_post_meta($post_id, 'monto', $data['monto']);
        update_post_meta($post_id, 'fecha', $data['fecha']);
        update_post_meta($post_id, 'concepto', $data['concepto']);
        if (isset($data['metodo_pago'])) {
            update_post_meta($post_id, 'metodo_pago', $data['metodo_pago']);
        }
        
        if (isset($data['descripcion'])) {
            update_post_meta($post_id, 'descripcion', $data['descripcion']);
        }
        
        // Asignar categoría si existe
        if (!empty($data['categoria'])) {
            wp_set_object_terms($post_id, [$data['categoria']], 'categoria_finanza');
            update_post_meta($post_id, 'categoria', $data['categoria']);
        }
        if(!empty($data['disparos'])){
            error_log('Campo disparos actualizado!: ' . $data['disparos']);
        update_post_meta($post_id, 'cantidad_de_disparos', $data['disparos']);
        }else{
            error_log('Campo disparos está vacío. finanzas-shortcode línea 292');
        }
        
        // Obtener datos actualizados para devolver
        $post = get_post($post_id);
        $meta = rd_get_finanzas_meta($post_id);
        
        wp_send_json_success([
            'message' => __('Registro guardado correctamente.', 'rd-finanzas'),
            'post' => [
                'ID' => $post_id,
                'title' => $post->post_title,
                'meta' => $meta,
            ],
        ]);
        
    } catch (Exception $e) {
        wp_send_json_error([
            'message' => $e->getMessage(),
        ]);
    }
}
add_action('wp_ajax_rd_save_finanza', 'rd_ajax_save_finanza');

/**
 * AJAX: Eliminar post de finanzas
 */
function rd_ajax_delete_finanza() {
    try {
        // Verificar nonce
        check_ajax_referer('rd_finanzas_crud_nonce', 'nonce');
        
        // Verificar permisos
        if (!current_user_can('delete_posts')) {
            throw new Exception(__('No tienes permisos para realizar esta acción.', 'rd-finanzas'));
        }
        
        // Obtener ID del post
        $post_id = isset($_POST['post_id']) ? absint($_POST['post_id']) : 0;
        
        if (!$post_id) {
            throw new Exception(__('ID de registro inválido.', 'rd-finanzas'));
        }
        
        // Verificar que el post existe y es del tipo correcto
        $post = get_post($post_id);
        if (!$post || $post->post_type !== 'finanzas') {
            throw new Exception(__('El registro no existe.', 'rd-finanzas'));
        }
        
        // Eliminar post
        $result = wp_delete_post($post_id, true);
        
        if (!$result) {
            throw new Exception(__('Error al eliminar el registro.', 'rd-finanzas'));
        }
        
        wp_send_json_success([
            'message' => __('Registro eliminado correctamente.', 'rd-finanzas'),
            'post_id' => $post_id,
        ]);
        
    } catch (Exception $e) {
        wp_send_json_error([
            'message' => $e->getMessage(),
        ]);
    }
}
add_action('wp_ajax_rd_delete_finanza', 'rd_ajax_delete_finanza');

/**
 * AJAX: Obtener post de finanzas
 */
function rd_ajax_get_finanza() {
    try {
        // Verificar nonce
        check_ajax_referer('rd_finanzas_crud_nonce', 'nonce');
        
        // Obtener ID del post
        $post_id = isset($_GET['post_id']) ? absint($_GET['post_id']) : 0;
        
        if (!$post_id) {
            throw new Exception(__('ID de registro inválido.', 'rd-finanzas'));
        }
        
        // Verificar que el post existe y es del tipo correcto
        $post = get_post($post_id);
        if (!$post || $post->post_type !== 'finanzas') {
            throw new Exception(__('El registro no existe.', 'rd-finanzas'));
        }
        
        // Obtener metadatos
        $meta = rd_get_finanzas_meta($post_id);
        
        // Obtener categoría
        $terms = wp_get_object_terms($post_id, 'tipo_finanza');
        //$terms = wp_get_object_terms($post_id, 'finanza');
        $categoria = !empty($terms) ? $terms[0]->term_id : '';
        
        wp_send_json_success([
            'post' => [
                'ID' => $post_id,
                'title' => $post->post_title,
                'meta' => $meta,
                'categoria' => $categoria,
            ],
        ]);
        
    } catch (Exception $e) {
        wp_send_json_error([
            'message' => $e->getMessage(),
        ]);
    }
}
add_action('wp_ajax_rd_get_finanza', 'rd_ajax_get_finanza');

// =================================================================
// 5. FUNCIONES DE VISTA
// =================================================================
function rd_generar_paginacion($query) {
    if ($query->max_num_pages <= 1) return '';
    
    ob_start();
    include __DIR__ . '/templates/paginacion.php';
    return ob_get_clean();
}

function rd_render_tabla_transacciones($query) {
    $transacciones = $query;
    ob_start();
    include __DIR__ . '/templates/tabla-transacciones.php';
    return ob_get_clean();
}
?>
<?php
if (current_user_can('administrator')) {
    ob_start();
    $unique_id = uniqid('rd-balance-');
    ?>
    <script>
    jQuery(document).ready(function($) {
        console.log('Depuración de balances:', {
            'jQuery': typeof $ === 'function',
            'jQuery.fn': typeof $.fn === 'object',
            'jQuery.fn.daterangepicker': typeof $.fn.daterangepicker === 'function',
            'Chart': typeof Chart === 'function',
            'rdFinanzasData': typeof rdFinanzasData === 'object',
            'Contenedor': $('#<?php echo esc_js($unique_id); ?>').length > 0,
            'Tabs': $('#<?php echo esc_js($unique_id); ?> .tab-trigger').length,
            'Canvas': {
                'income': $('#income-chart-<?php echo esc_js($unique_id); ?>').length > 0,
                'expenses': $('#expenses-chart-<?php echo esc_js($unique_id); ?>').length > 0,
                'comparison': $('#comparison-chart-<?php echo esc_js($unique_id); ?>').length > 0
            }
        });
    });
    </script>
    <?php
    $debug_output = ob_get_clean();
    return ob_get_clean() . $debug_output;
}
?>