<?php
/**
 * Fuerza la disponibilidad de Application Passwords
 */
add_filter( 'wp_is_application_passwords_available', '__return_true' );

?>