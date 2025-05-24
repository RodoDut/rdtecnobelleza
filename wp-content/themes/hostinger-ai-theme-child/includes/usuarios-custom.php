<?php
if (!defined('ABSPATH')) {
    exit;
}

// Función para crear un usuario si no existe
function crear_usuario_desde_reserva($nombre, $email, $telefono, $direccion, $ciudad, $provincia) {
    if (email_exists($email)) {
        return;
    }

    $password = wp_generate_password();
    $user_id = wp_create_user($email, $password, $email);

    if (!is_wp_error($user_id)) {
        wp_update_user(array(
            'ID' => $user_id,
            'first_name' => $nombre,
        ));

        update_user_meta($user_id, 'billing_phone', $telefono);
        update_user_meta($user_id, 'billing_address_1', $direccion);
        update_user_meta($user_id, 'billing_city', $ciudad);
        update_user_meta($user_id, 'billing_state', $provincia);
        wp_update_user(array('ID' => $user_id, 'role' => 'cliente'));
    }
}
