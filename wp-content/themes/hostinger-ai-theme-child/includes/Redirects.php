<?php

namespace Hostinger\AiTheme;

use Hostinger\AiTheme\Admin\Menu;
use Hostinger\WpHelper\Utils;

defined( 'ABSPATH' ) || exit;

class Redirects {
    /**
     * @var string
     */
    private string $platform;
    /**
     * @var Utils
     */
    private Utils $utils;
    public const PLATFORM_CONTENT_CREATOR = 'ai-website-generation';

    public const REDIRECT_URL = 'admin.php?page=' . Menu::AI_BUILDER_MENU_SLUG;

    /**
     * @param Utils $utils
     */
    public function __construct( Utils $utils ) {
        $this->utils = $utils;

        add_action( 'admin_init', array( $this, 'redirect_to_builder' ) );

        if ( ! isset( $_GET['platform'] ) ) {
            return;
        }

        $this->platform = sanitize_text_field( $_GET['platform'] );
        $this->login_redirect();
    }

    /**
     * @return void
     */
    public function redirect_to_builder(): void {
        if ( wp_doing_ajax() || defined('REST_REQUEST') ) {
            return;
        }

        if ( $this->utils->isThisPage( Menu::AI_BUILDER_MENU_SLUG ) ) {
            return;
        }

        $hostinger_ai_version = get_option( 'hostinger_ai_version', false );

        if ( ! empty( $hostinger_ai_version ) ) {
            return;
        }

        $redirect_url = admin_url( self::REDIRECT_URL );
        wp_safe_redirect( $redirect_url );
        exit;
    }

    /**
     * @return void
     */
    private function login_redirect(): void {
        if ( $this->platform === self::PLATFORM_CONTENT_CREATOR ) {
            add_action(
                'init',
                function () {
                    $redirect_url = admin_url( self::REDIRECT_URL );
                    wp_safe_redirect( $redirect_url );
                    exit;
                }
            );
        }
    }
}