<?php

namespace Hostinger\AiTheme;

use Hostinger\WpHelper\Config;
use YahnisElsts\PluginUpdateChecker\v5\PucFactory;

defined( 'ABSPATH' ) || exit;

class Updates {
    private const DEFAULT_PLUGIN_UPDATE_URI = 'https://wp-update.hostinger.io/?action=get_metadata&slug=hostinger-ai-theme';
    private const CANARY_PLUGIN_UPDATE_URI  = 'https://wp-update-canary.hostinger.io/?action=get_metadata&slug=hostinger-ai-theme';
    private const STAGING_PLUGIN_UPDATE_URI = 'https://wp-update-stage.hostinger.io/?action=get_metadata&slug=hostinger-ai-theme';

    /**
     * @return string
     */
    private function get_plugin_update_uri(): string {
        if ( isset( $_SERVER['H_STAGING'] ) && $_SERVER['H_STAGING'] === true ) {
            return self::STAGING_PLUGIN_UPDATE_URI;
        }

        if ( isset( $_SERVER['H_CANARY'] ) && $_SERVER['H_CANARY'] === true ) {
            return self::CANARY_PLUGIN_UPDATE_URI;
        }

        return self::DEFAULT_PLUGIN_UPDATE_URI;
    }

    /**
     * @return void
     */
    public function updates(): void {
        $plugin_updater_uri = $this->get_plugin_update_uri();

        if ( class_exists( PucFactory::class ) ) {
            $hts_update_checker = PucFactory::buildUpdateChecker(
                $plugin_updater_uri,
                get_template_directory() . DIRECTORY_SEPARATOR . 'functions.php',
                'hostinger-ai-theme'
            );
        }
    }
}
