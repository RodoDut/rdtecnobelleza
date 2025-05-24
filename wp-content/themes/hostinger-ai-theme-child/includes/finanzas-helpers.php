<?php
/**
 * Helpers para el módulo de Finanzas
 * Funciones reutilizables y de propósito general
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Obtiene y cachea la página actual desde la query string
 */
function rd_get_pagina_actual() {
    static $pagina = null;

    if ($pagina === null) {
        $pagina = isset($_GET['pagina']) ? max(1, intval($_GET['pagina'])) : 1;
    }

    return $pagina;
}

/**
 * Normaliza diferentes formatos monetarios a float
 * @param mixed $monto - Valor a normalizar
 * @return float - Valor numérico estandarizado
 */
function rd_limpiar_monto($monto) {
    if ($monto === null || $monto === '') return 0.0;
    if (is_numeric($monto)) return floatval($monto);

    $limpio = preg_replace('/[^0-9.,]/', '', (string)$monto);
    $es_europeo = (substr_count($limpio, ',') === 1 && strpos($limpio, '.') !== false);

    if ($es_europeo) {
        $limpio = str_replace('.', '', $limpio);
        $limpio = str_replace(',', '.', $limpio);
    } else {
        $limpio = str_replace(',', '', $limpio);
    }

    return round(floatval($limpio), 2);
}

/**
 * Valida y sanitiza fechas en formato YYYY-MM-DD
 * @param string $date - Fecha a validar
 * @return string - Fecha válida o string vacío
 */
function rd_sanitize_fecha($date) {
    if (empty($date)) return '';
    
    $date_obj = DateTime::createFromFormat('Y-m-d', $date);
    return $date_obj && $date_obj->format('Y-m-d') === $date ? $date : '';
}

/**
 * Calcula totales de ingresos y egresos
 * @param array $posts - Array de objetos WP_Post
 * @return array - Totales calculados
 */
function rd_calcular_totales($posts) {
    $totales = [
        'ingresos' => 0.0,
        'egresos' => 0.0,
        'disparos' => 0
    ];

    foreach ($posts as $post) {
      if(class_exists('ACF') && function_exists('get_field')){ 
        $tipo = (get_field('tipo', $post->ID)) ? strtolower(get_field('tipo', $post->ID)) : 'desconocido';
        if (!function_exists('get_field')) {
            error_log('Error: The function get_field is not available. Ensure the ACF plugin is installed and active.');
        }
        $monto = (class_exists(class:'ACF') && function_exists('get_field') && get_field('monto', $post->ID) !== false) 
            ? rd_limpiar_monto(get_field('monto', $post->ID)) 
            : 0.0;

        if (!function_exists('get_field')) {
            error_log('Error: The function get_field is not available. Ensure the ACF plugin is installed and active.');
        }
        $disparos = function_exists('get_field') ? intval(get_field('cantidad_de_disparos', $post->ID)) : 0;

        if ($tipo === 'ingreso') {
            $totales['ingresos'] += $monto;
        } elseif ($tipo === 'egreso') {
            $totales['egresos'] += $monto;
        }
        
        $totales['disparos'] += $disparos;
     }  
    }

    $totales['balance'] = $totales['ingresos'] - $totales['egresos'];
    
    return $totales;
}

/**
 * Agrupa transacciones por mes
 * @param array $posts - Array de objetos WP_Post
 * @return array - Datos organizados por mes
 */
function rd_agrupar_por_mes($posts) {
    $meses = [];

    foreach ($posts as $post) {
        $fecha = get_field('fecha', $post->ID);
        if (empty($fecha)) continue;

        $mes_key = date('Y-m', strtotime($fecha));
        $mes_nombre = date_i18n('M Y', strtotime($fecha));

        if (!isset($meses[$mes_key])) {
            $meses[$mes_key] = [
                'etiqueta' => $mes_nombre,
                'ingresos' => 0.0,
                'egresos' => 0.0,
                'disparos' => 0
            ];
        }

        $tipo = strtolower(get_field('tipo', $post->ID));
        $monto = rd_limpiar_monto(get_field('monto', $post->ID));
        $disparos = intval(get_field('cantidad_de_disparos', $post->ID));

        if ($tipo === 'ingreso') {
            $meses[$mes_key]['ingresos'] += $monto;
        } elseif ($tipo === 'egreso') {
            $meses[$mes_key]['egresos'] += $monto;
        }
        
        $meses[$mes_key]['disparos'] += $disparos;
    }

    ksort($meses);
    return $meses;
}

/**
 * Obtiene las últimas transacciones para el panel
 * @param array $posts - Array de objetos WP_Post
 * @param int $limite - Cantidad máxima de transacciones
 * @return array - Transacciones formateadas
 */
function rd_obtener_transacciones_recientes($posts, $limite = 5) {
    $transacciones = [];
    
    foreach ($posts as $post) {
        if (count($transacciones) >= $limite) break;

        $transacciones[] = [
            'id' => $post->ID,
            'titulo' => get_the_title($post->ID),
            'fecha' => get_field('fecha', $post->ID),
            'tipo' => strtolower(get_field('tipo', $post->ID)),
            'monto' => rd_limpiar_monto(get_field('monto', $post->ID)),
            'descripcion' => sanitize_text_field(get_field('descripcion', $post->ID)),
            'concepto' => sanitize_text_field(get_field('concepto', $post->ID)),
            'disparos' => intval(get_field('cantidad_de_disparos', $post->ID)),
            'categoria'=> sanitize_text_field(get_field('categoria', $post->ID))
        ];
    }

    return $transacciones;
}

/**
 * Sistema de logging para depuración
 * @param mixed $mensaje - Datos a registrar
 * @param string $nivel - Nivel de error (debug, info, error)
 */
function rd_finanzas_log($mensaje, $nivel = 'debug') {
    if (!defined('WP_DEBUG') || !WP_DEBUG) return;

    $log_entry = sprintf(
        "[%s] RD Finanzas (%s): %s\n",
        date('Y-m-d H:i:s'),
        strtoupper($nivel),
        print_r($mensaje, true)
    );

    error_log($log_entry);
}

/**
 * Prepara los datos para los gráficos
 * @param array $meses - Datos agrupados por mes
 * @return array - Estructura lista para Chart.js
 */
function rd_preparar_datos_graficos($meses) {
    return [
        'labels' => array_column($meses, 'etiqueta'),
        'datasets' => [
            'ingresos' => array_column($meses, 'ingresos'),
            'egresos' => array_column($meses, 'egresos'),
            'balance' => array_map(function($mes) {
                return $mes['ingresos'] - $mes['egresos'];
            }, $meses),
            'disparos' => array_column($meses, 'disparos')
        ]
    ];
}

//FUNCIONES PARA PESTAÑA DE GESTIONAR FINANZAS----------
/**
 * Obtener listado de posts de finanzas
 */
function rd_get_finanzas_posts($args = []) {
    $default_args = [
        'post_type' => 'finanzas',
        'posts_per_page' => -1,
        'orderby' => 'meta_value',
        'meta_key' => 'fecha',
        'order' => 'DESC',
    ];
    
    $query_args = wp_parse_args($args, $default_args);
    $query = new WP_Query($query_args);
    
    return $query->posts;
}

/**
 * Obtener metadatos de un post de finanzas
 */
function rd_get_finanzas_meta($post_id) {
    $meta = [];
    
    $meta['tipo'] = get_post_meta($post_id, 'tipo', true);
    if(isset($meta['tipo'])){
        $meta['tipo'] = strtolower($meta['tipo']);
    }
    $meta['monto'] = get_post_meta($post_id, 'monto', true);
    $meta['fecha'] = get_post_meta($post_id, 'fecha', true);
    $meta['metodo_pago'] = get_post_meta($post_id, 'metodo_pago', true);
    $meta['descripcion'] = get_post_meta($post_id, 'descripcion', true);
    $meta['concepto'] = get_post_meta($post_id, 'concepto', true);
    $meta['disparos'] = get_post_meta($post_id, 'cantidad_de_disparos', true);
    $meta['categoria'] = get_post_meta($post_id, 'categoria', true);
    
    return $meta;
}

/**
 * Obtener categorías de finanzas
 */ 
function rd_get_finanzas_categories() {
   $terms = get_terms([
    'taxonomy' => 'categoria_finanza',
    'hide_empty' => false
]);

    return is_wp_error($terms) ? [] : $terms;
}

/**
 * Sanitizar datos de finanzas
 */
function rd_sanitize_finanzas_data($data) {
    $sanitized = [];
    
    if (isset($data['concepto'])) {
        $sanitized['concepto'] = sanitize_text_field($data['concepto']);
    }
    
    if (isset($data['fecha'])) {
        $sanitized['fecha'] = sanitize_text_field($data['fecha']);
    }
    
    if (isset($data['tipo'])) {
        $sanitized['tipo'] = in_array($data['tipo'], ['ingreso', 'egreso', 'Ingreso', 'Egreso']) ? $data['tipo'] : '';
    }
    
    if (isset($data['monto'])) {
        $sanitized['monto'] = (float) $data['monto'];
    }
    
    if (isset($data['categoria'])) {
        $sanitized['categoria'] = sanitize_text_field($data['categoria']);
    }
    
    if (isset($data['metodo_pago'])) {
        $sanitized['metodo_pago'] = sanitize_text_field($data['metodo_pago']);
    }
    
    if (isset($data['descripcion'])) {
        $sanitized['descripcion'] = sanitize_textarea_field($data['descripcion']);
    }
    
    if (isset($data['disparos'])) {
        $sanitized['disparos'] = (int) $data['disparos'];
    }
    
    return $sanitized;
}
// ------- FIN  FUNCIONES PARA PESTAÑA DE GESTIONAR FINANZAS----------


function agregar_categorias_finanza() {
    $categorias = [
        'Alquiler Jornada Completa',
        'Alquiler Media Jornada',
        'Combustible',
        'Peaje',
        'Mantenimiento Vehiculo',
        'Reparación Vehiculo',
        'Mantenimiento Soprano',
        'Reparación Soprano',
        'Accesorio Soprano',
        'Herramienta',
        'Servicio',
        'Viático',
        'Otros'
    ];

    // Obtener todos los términos actuales en la taxonomía
    $terms_actuales = get_terms([
        'taxonomy' => 'categoria_finanza',
        'hide_empty' => false,
    ]);

    // Lista simple con los nombres actuales
    $nombres_actuales = [];

    foreach ($terms_actuales as $term) {
        $nombres_actuales[] = $term->name;

        // Si el término actual no está en la lista deseada, eliminarlo
        if (!in_array($term->name, $categorias)) {
            wp_delete_term($term->term_id, 'categoria_finanza');
            error_log("Término '{$term->name}' eliminado.");
        }
    }

    // Agregar los términos que faltan
    foreach ($categorias as $categoria) {
        if (!term_exists($categoria, 'categoria_finanza')) {
            $resultado = wp_insert_term($categoria, 'categoria_finanza');

            if (is_wp_error($resultado)) {
                error_log("Error al insertar término '{$categoria}': " . $resultado->get_error_message());
            } else {
                error_log("Término '{$categoria}' insertado correctamente.");
            }
        } else {
            error_log("Término '{$categoria}' ya existe. No se inserta.");
        }
    }
}

?>