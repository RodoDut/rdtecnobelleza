<?php
if (!defined('ABSPATH')) {
    exit;
}

// Bloquear acceso a la página Balances para no administradores o socias
function restringir_acceso_balances() {
    if (is_page('balances') && !current_user_can('manage_options')) {
        wp_redirect(home_url());
        exit;
    }
}
add_action('template_redirect', 'restringir_acceso_balances');
