<?php
define( 'WP_CACHE', true );

/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the web site, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * Localized language
 * * ABSPATH
 *
 * @link https://wordpress.org/support/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'u938009968_ngYCS' );

/** Database username */
define( 'DB_USER', 'u938009968_nTH1T' );

/** Database password UPMWXBVX5X*/
define( 'DB_PASSWORD', '0312Rodo!' );

/** Database hostname */
define( 'DB_HOST', '127.0.0.1' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8' );

/** The database collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**#@+
 * Authentication unique keys and salts.
 *
 * Change these to different unique phrases! You can generate these using
 * the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}.
 *
 * You can change these at any point in time to invalidate all existing cookies.
 * This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',          'Yrl}6tlFc+QvKT(`,_[b+aSaEgx?/[F5Iw?]@~3w~P4:*vI;xrWamjC/lVXv9$5|' );
define( 'SECURE_AUTH_KEY',   '>&Q[Z3.y;h3(]LC=Z8)he!R:AYI.Sz556@$s7//Ayy~Qe2v{Vg-CMk_XhC8Mn}m ' );
define( 'LOGGED_IN_KEY',     ';@mHx1E.,Bp9|vvp;DLTa9;98+1zpj8rhT6Sh*~,t/l]?n, R9FEVf1yV3e=A}_:' );
define( 'NONCE_KEY',         '@hF8O!63;@trZarl9k8SLy|I^8Z9BG,tSuF>rgO=?)`3.=FC7n~rMoqXG#GH8Z|<' );
define( 'AUTH_SALT',         'fn5==q-]@5<wNYu4&x?q(tSeFPZWw,98kKswmu]`|f52=Hi}gboX900LP.evkM8W' );
define( 'SECURE_AUTH_SALT',  '!7keMpJ^b4 #CNk8i*5V}rTZb()0r%%Xl+w5*ix.!7D f]Lt-Vu&&~Z./y<#0TlF' );
define( 'LOGGED_IN_SALT',    'mk4Q9~T|nzTN~B&Kr}#!  L!oAYL<G|!pIk^9}JgGhn$x`ST$Cm-z:}r{7tgw C}' );
define( 'NONCE_SALT',        'MD!ov}l1XeZK0B C7csrql[oI{3K`VSb{fnU3l;b2XG&- 8L0pdKG7qYT!VKd9i*' );
define( 'WP_CACHE_KEY_SALT', '%G!POccl%,{._$Kb)&0GJI:|&S-AiPYAZTW:ti0V*5**WqNYgMu[T=EZRM0K{d:0' );
define( 'IMAP_USER',         'contacto@rdtecnobelleza.net');
define( 'IMAP_PASS',         '0312Rodo!');
define( 'PHONE_NUM',         '5493415795765');


/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wp_';


/* Add any custom values between this line and the "stop editing" line. */



/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the documentation.
 *
 * @link https://wordpress.org/support/article/debugging-in-wordpress/
 */
if ( ! defined( 'WP_DEBUG' ) ) {
	define( 'WP_DEBUG', false );
	define('WP_DEBUG_LOG', false);
    define('WP_DEBUG_DISPLAY', false);
    @ini_set('log_errors', 1);
    @ini_set('display_errors', 0);
}

define( 'FS_METHOD', 'direct' );
define( 'COOKIEHASH', '61338eb67f9aad7005df9ed4d52e781d' );
define( 'WP_AUTO_UPDATE_CORE', 'minor' );

// Forzar SSL en el administrador
define( 'FORCE_SSL_ADMIN', true );
// Si estás detrás de Cloudflare o similar:
if ( isset( $_SERVER['HTTP_X_FORWARDED_PROTO'] ) && 
     $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https' ) {
    $_SERVER['HTTPS'] = 'on';
}
/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
