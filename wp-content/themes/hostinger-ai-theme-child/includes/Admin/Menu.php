<?php

namespace Hostinger\AiTheme\Admin;

defined( 'ABSPATH' ) || exit;

class Menu {
    public const AI_BUILDER_MENU_SLUG = 'hostinger-ai-website-creation';

	public function __construct() {
		add_filter( 'hostinger_menu_subpages', array( $this, 'add_menu_sub_pages' ), 999 );
	}

	/**
	 * @param array $submenus
	 *
	 * @return array
	 */
	public function add_menu_sub_pages( array $submenus ): array {
		$submenus[] = array(
			'page_title' => __( 'AI Website Creation', 'hostinger-ai-theme' ),
			'menu_title' => __( 'AI Website Creation', 'hostinger-ai-theme' ),
			'capability' => 'manage_options',
			'menu_slug'  => self::AI_BUILDER_MENU_SLUG,
			'callback'   => array( $this, 'renderWebsiteCreation' ),
			'menu_identifier' => 'generic',
			'menu_order' => 100
		);

		return $submenus;
	}

	/**
	 * @return void
	 */
	public function renderWebsiteCreation(): void {
		include_once __DIR__ . DIRECTORY_SEPARATOR . 'Views' . DIRECTORY_SEPARATOR . 'WebsiteCreationOnboarding.php';
	}
}
