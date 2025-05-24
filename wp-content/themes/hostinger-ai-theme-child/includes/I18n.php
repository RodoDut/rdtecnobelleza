<?php

namespace Hostinger\AiTheme;

defined( 'ABSPATH' ) || exit;

class I18n {
    public function __construct() {
        add_action( 'after_setup_theme', array( $this, 'load_plugin_textdomain' ) );
    }

	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.2.0
	 */
	public function load_plugin_textdomain(): void {
        $domain = 'hostinger-ai-theme';

        $locale = apply_filters( 'theme_locale', determine_locale(), $domain );

        $mo_file = $domain . '-' . $locale . '.mo';

        $path = get_template_directory() . '/languages/' . $mo_file;

        load_textdomain( $domain, $path, $locale );
	}
}
