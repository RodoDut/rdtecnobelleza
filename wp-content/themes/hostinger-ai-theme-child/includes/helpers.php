<?php
//Funciones personalizadas para incluir en distintas partes del código.

if (!defined('ABSPATH')) {
    exit;
}

// Función para obtener el mayor gasto registrado
function obtener_mayor_gasto() {
    $args = array(
        'post_type'      => 'finanzas',
        'posts_per_page' => 1,
        'meta_key'       => 'monto',
        'orderby'        => 'meta_value_num',
        'order'          => 'DESC',
        'meta_query'     => array(
            array(
                'key'     => 'tipo',
                'value'   => 'gasto',
                'compare' => '='
            )
        )
    );

    $query = new WP_Query($args);
    if ($query->have_posts()) {
        $query->the_post();
        return get_the_title() . ' - $' . number_format(get_field('monto'), 2, ',', '.');
    }
    wp_reset_postdata();
    return 'No hay gastos registrados.';
}

//Devuelve los mayores ingresos/egresos de un mes en cuestión.
function obtener_mayores_transacciones($tipo, $mes) {
    $args = array(
        'post_type'      => 'finanzas',
        'posts_per_page' => -1,
        'meta_query'     => array(
            'relation' => 'AND',
            array(
                'key'     => 'tipo',
                'value'   => $tipo,
                'compare' => '='
            ),
            array(
                'key'     => 'fecha',
                'value'   => $mes,
                'compare' => 'LIKE'
            )
        ),
        'orderby'        => 'meta_value_num',
        'order'          => 'DESC'
    );
    
    $query = new WP_Query($args);
    $resultados = [];
    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            $resultados[] = [
                'label' => get_field('descripcion'),
                'value' => get_field('monto')
            ];
        }
    }
    wp_reset_postdata();
    return $resultados;
}

