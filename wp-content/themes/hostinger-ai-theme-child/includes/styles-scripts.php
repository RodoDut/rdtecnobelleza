<?php
if (!defined('ABSPATH')) {
    exit;
}

function cargar_estilos_personalizados() {
   // wp_enqueue_style('custom-style', get_stylesheet_directory_uri() . '/assets/css/custom.css');
}
add_action('wp_enqueue_scripts', 'cargar_estilos_personalizados');
