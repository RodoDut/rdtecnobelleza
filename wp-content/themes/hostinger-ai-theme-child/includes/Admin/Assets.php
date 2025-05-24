<?php

namespace Hostinger\AiTheme\Admin;

defined( 'ABSPATH' ) || exit;

class Assets {

	public function __construct() {
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_styles' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ) );
	}

	/**
	 * Enqueues styles for the Hostinger admin pages.
	 */
	public function admin_styles(): void {
		wp_enqueue_style( 'hostinger_ai_websites_main_styles', HOSTINGER_AI_WEBSITES_ASSETS_URL . '/css/main.min.css', array(), wp_get_theme()->get( 'Version' ) );

        $hide_menu_item = '.hsr-list__item a.' . Menu::AI_BUILDER_MENU_SLUG . ', .toplevel_page_hostinger a[href="admin.php?page=' . Menu::AI_BUILDER_MENU_SLUG . '"] { display: none !important; }';
        wp_add_inline_style( 'hostinger_ai_websites_main_styles', $hide_menu_item );
    }

	/**
	 * Enqueues scripts for the Hostinger admin pages.
	 */
	public function admin_scripts(): void {
        wp_enqueue_script(
            'hostinger_ai_websites_main_scripts',
            HOSTINGER_AI_WEBSITES_ASSETS_URL . '/js/main.min.js',
            array(
                'jquery',
                'wp-i18n',
            ),
            wp_get_theme()->get( 'Version' ),
            false
        );

        $site_url = add_query_arg( 'LSCWP_CTRL', 'before_optm', get_site_url() . '/' );

        $localize_data = array(
            'site_url'     => $site_url,
            'plugin_url'   => get_stylesheet_directory_uri() . '/',
            'admin_url' => admin_url('admin-ajax.php'),
            'website_type' => get_option( 'hostinger_website_type', 'other' ),
            'translations' => AdminTranslations::getValues(),
            'content_generated' => (int)!empty( get_option( 'hostinger_ai_version' ) ),
            'rest_base_url' => esc_url_raw( rest_url() ),
            'nonce'         => wp_create_nonce( 'wp_rest' ),
            'ajax_nonce'         => wp_create_nonce( 'updates' ),
        );

        wp_localize_script(
            'hostinger_ai_websites_main_scripts',
            'hostinger_ai_websites',
            $localize_data
        );

        wp_enqueue_script(
            'hostinger_ai_websites_admin_scripts',
            HOSTINGER_AI_WEBSITES_ASSETS_URL . '/js/admin.min.js',
            array(
                'jquery',
                'wp-i18n',
            ),
            wp_get_theme()->get( 'Version' ),
            false
        );
	}
}
