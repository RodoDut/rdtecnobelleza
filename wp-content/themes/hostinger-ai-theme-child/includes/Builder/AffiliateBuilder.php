<?php

namespace Hostinger\AiTheme\Builder;

use Hostinger\AffiliatePlugin\Admin\Options\PluginOptions;
use Hostinger\AffiliatePlugin\Admin\PluginSettings;
use Plugin_Upgrader;
use WP_Ajax_Upgrader_Skin;
use WP_Error;

defined( 'ABSPATH' ) || exit;

class AffiliateBuilder {
    private const DEFAULT_PLUGIN_UPDATE_URI = 'https://wp-update.hostinger.io/';
    private const CANARY_PLUGIN_UPDATE_URI  = 'https://wp-update-canary.hostinger.io/';
    private const STAGING_PLUGIN_UPDATE_URI = 'https://wp-update-stage.hostinger.io/';

    public function boot(): void {
        if ( ! $this->is_enabled() ) {
            return;
        }

        $this->enable_plugin();

        if ( ! $this->is_plugin_active() ) {
            return;
        }

        $this->set_localization_settings();
    }

    public function generate_shortcode( string $keyword ): string {
        if ( ! $this->is_enabled() || ! $this->is_plugin_active() ) {
            return '';
        }

        $layouts = array(
            'list_with_description',
            'horizontal_cards',
            'simplified_list',
        );

        $selected_layout = array_rand($layouts);

        $data = array(
            'display_type' => 'multiple_product_list',
            'product_list_type' => 'bestsellers',
            'list_navigation' => 'bestsellers',
            'list_layout_selected' => true,
            'list_layout' => $layouts[$selected_layout],
            'keywords' => $keyword,
            'description_enabled' => true,
            'description_forced' => true,
            'ready' => true,
        );

        return '<!-- wp:hostinger-affiliate-plugin/block ' . json_encode( $data, JSON_UNESCAPED_UNICODE ) . ' /-->';
    }

    public function enable_plugin(): void {
        if ( ! function_exists( 'get_plugins' ) || ! function_exists( 'is_plugin_active' ) ) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }

        $installed_plugins = get_plugins();

        if ( !in_array( 'hostinger-affiliate-plugin/hostinger-affiliate-plugin.php', $installed_plugins, true ) ) {
            $this->install_plugin();
            return;
        }

        activate_plugin( 'hostinger-affiliate-plugin/hostinger-affiliate-plugin.php' );
    }

    public function set_localization_settings(): void {
        $locale = get_locale();

        $plugin_settings = new PluginSettings();

        $plugin_options = $plugin_settings->get_plugin_settings();

        if ( ! empty( $plugin_options->get_amazon_options()->get_country() ) ) {
            return;
        }

        $locale_map = [
            'af' => 'us',
            'sq' => 'us',
            'arq' => 'us',
            'ak' => 'us',
            'am' => 'us',
            'ar' => 'eg',
            'hy' => 'us',
            'rup_MK' => 'us',
            'frp' => 'us',
            'as' => 'in',
            'ast' => 'us',
            'az' => 'us',
            'az_TR' => 'tr',
            'bcc' => 'us',
            'ba' => 'us',
            'eu' => 'us',
            'bel' => 'us',
            'bn_BD' => 'us',
            'bn_IN' => 'in',
            'bho' => 'us',
            'brx' => 'us',
            'gax' => 'us',
            'bs_BA' => 'us',
            'bre' => 'us',
            'bg_BG' => 'us',
            'my_MM' => 'us',
            'ca' => 'us',
            'bal' => 'us',
            'ceb' => 'us',
            'zh_CN' => 'us',
            'zh_HK' => 'us',
            'zh_SG' => 'sg',
            'zh_TW' => 'us',
            'cor' => 'us',
            'co' => 'us',
            'hr' => 'us',
            'cs_CZ' => 'us',
            'da_DK' => 'us',
            'dv' => 'us',
            'nl_NL' => 'nl',
            'nl_BE' => 'be',
            'dzo' => 'us',
            'art-xemoji' => 'us',
            'en_US' => 'us',
            'en_AU' => 'au',
            'en_CA' => 'ca',
            'en_NZ' => 'us',
            'art_xpirate' => 'us',
            'en_SA' => 'sa',
            'en_GB' => 'uk',
            'eo' => 'us',
            'et' => 'us',
            'ewe' => 'us',
            'fo' => 'us',
            'fi' => 'us',
            'fon' => 'us',
            'fr_BE' => 'be',
            'fr_CA' => 'ca',
            'fr_FR' => 'fr',
            'fy' => 'us',
            'fur' => 'us',
            'fuc' => 'us',
            'gl_ES' => 'us',
            'ka_GE' => 'us',
            'de_DE' => 'de',
            'de_AT' => 'de',
            'de_CH' => 'de',
            'el' => 'us',
            'kal' => 'us',
            'gn' => 'us',
            'gu_IN' => 'in',
            'haw_US' => 'us',
            'hat' => 'us',
            'hau' => 'us',
            'haz' => 'us',
            'he_IL' => 'us',
            'hi_IN' => 'in',
            'hu_HU' => 'us',
            'is_IS' => 'us',
            'ido' => 'us',
            'ibo' => 'us',
            'id_ID' => 'us',
            'ga' => 'us',
            'it_IT' => 'it',
            'ja' => 'jp',
            'jv_ID' => 'us',
            'kab' => 'us',
            'kn' => 'in',
            'kaa' => 'us',
            'kk' => 'us',
            'km' => 'us',
            'kin' => 'us',
            'ky_KY' => 'us',
            'ko_KR' => 'us',
            'ckb' => 'us',
            'kmr' => 'us',
            'kir' => 'us',
            'lo' => 'us',
            'lv' => 'us',
            'la' => 'us',
            'lij' => 'us',
            'li' => 'us',
            'lin' => 'us',
            'lt_LT' => 'us',
            'lmo' => 'us',
            'dsb' => 'us',
            'lug' => 'us',
            'lb_LU' => 'us',
            'mk_MK' => 'us',
            'mai' => 'us',
            'mg_MG' => 'us',
            'mlt' => 'us',
            'ms_MY' => 'us',
            'ml_IN' => 'in',
            'mri' => 'us',
            'mfe' => 'us',
            'mr' => 'in',
            'xmf' => 'us',
            'mn' => 'us',
            'me_ME' => 'us',
            'ary' => 'us',
            'my_MM' => 'us',
            'ne_NP' => 'us',
            'pcm' => 'us',
            'nqo' => 'us',
            'nb_NO' => 'us',
            'nn_NO' => 'us',
            'oci' => 'us',
            'ory' => 'us',
            'os' => 'us',
            'ps' => 'us',
            'pa_IN' => 'in',
            'pap_AW' => 'us',
            'pap_CW' => 'us',
            'fa_IR' => 'us',
            'fa_AF' => 'us',
            'pl_PL' => 'pl',
            'pt_AO' => 'us',
            'pt_BR' => 'br',
            'pt_PT' => 'us',
            'pa' => 'us',
            'rhg' => 'us',
            'ro_RO' => 'us',
            'roh' => 'us',
            'ru_RU' => 'us',
            'ru_UA' => 'us',
            'rue' => 'us',
            'sah' => 'us',
            'sa_IN' => 'us',
            'skr' => 'us',
            'srd' => 'us',
            'gd' => 'us',
            'sr_RS' => 'us',
            'sna' => 'us',
            'sq_XK' => 'us',
            'scn' => 'us',
            'sd_PK' => 'us',
            'si_LK' => 'us',
            'szl' => 'us',
            'sk_SK' => 'us',
            'sl_SI' => 'us',
            'so_SO' => 'us',
            'azb' => 'us',
            'es_AR' => 'es',
            'es_CL' => 'es',
            'es_CR' => 'es',
            'es_CO' => 'es',
            'es_DO' => 'es',
            'es_EC' => 'es',
            'es_GT' => 'es',
            'es_HN' => 'es',
            'es_MX' => 'mx',
            'es_PE' => 'es',
            'es_PR' => 'es',
            'es_ES' => 'es',
            'es_UY' => 'es',
            'es_VE' => 'es',
            'su_ID' => 'us',
            'ssw' => 'us',
            'sw' => 'us',
            'sv_SE' => 'se',
            'gsw' => 'us',
            'syr' => 'us',
            'tl' => 'us',
            'tah' => 'us',
            'tg' => 'us',
            'tzm' => 'us',
            'zgh' => 'us',
            'ta_IN' => 'in',
            'ta_LK' => 'us',
            'tt_RU' => 'us',
            'te' => 'in',
            'th' => 'us',
            'bo' => 'us',
            'tir' => 'us',
            'tr_TR' => 'tr',
            'tuk' => 'us',
            'twd' => 'us',
            'ug_CN' => 'us',
            'uk' => 'us',
            'hsb' => 'us',
            'ur' => 'us',
            'uz_UZ' => 'us',
            'vec' => 'us',
            'vi' => 'us',
            'wa' => 'us',
            'cy' => 'us',
            'wol' => 'us',
            'xho' => 'us',
            'yor' => 'us',
            'zul' => 'us',
        ];

        if ( empty( $locale_map[$locale] ) ) {
            return;
        }

        $plugin_options->set_connection_status( PluginOptions::STATUS_CONNECTED );
        $plugin_options->get_amazon_options()->set_country( $locale_map[$locale] );
        $plugin_options->get_amazon_options()->set_tracking_id( 'hostinger084-20' );

        $plugin_settings->save_plugin_settings( $plugin_options );
    }

    public function install_plugin(): null|WP_Error {
        require_once ABSPATH . 'wp-admin/includes/file.php';
        include_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
        include_once ABSPATH . 'wp-admin/includes/plugin-install.php';
        include_once ABSPATH . 'wp-admin/includes/plugin.php';

        $skin     = new WP_Ajax_Upgrader_Skin();
        $upgrader = new Plugin_Upgrader( $skin );

        $install_plugin = $upgrader->install( $this->get_plugin_update_uri() . '?action=download&slug=hostinger-affiliate-plugin' );

        if ( is_wp_error( $install_plugin ) ) {
            error_log( 'Hostinger AI Theme: ' . print_r( $install_plugin, true ) );
            return null;
        }

        return activate_plugin( 'hostinger-affiliate-plugin/hostinger-affiliate-plugin.php' );
    }

    private function is_enabled(): bool {
        return get_option( 'hostinger_ai_affiliate', false );
    }

    private function is_plugin_active(): bool {
        return is_plugin_active( 'hostinger-affiliate-plugin/hostinger-affiliate-plugin.php' );
    }

    private function get_plugin_update_uri(): string {
        if ( isset( $_SERVER['H_STAGING'] ) && $_SERVER['H_STAGING'] === true ) {
            return self::STAGING_PLUGIN_UPDATE_URI;
        }

        if ( isset( $_SERVER['H_CANARY'] ) && $_SERVER['H_CANARY'] === true ) {
            return self::CANARY_PLUGIN_UPDATE_URI;
        }

        return self::DEFAULT_PLUGIN_UPDATE_URI;
    }
}
