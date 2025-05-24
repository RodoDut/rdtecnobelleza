<?php
namespace Hostinger\AiTheme;

use Hostinger\AiTheme\Admin\Assets as AdminAssets;
use Hostinger\AiTheme\Admin\Menu as AdminMenu;
use Hostinger\AiTheme\Admin\Hooks as AdminHooks;
use Hostinger\AiTheme\Builder\AffiliateBuilder;
use Hostinger\AiTheme\Builder\ImageManager;
use Hostinger\AiTheme\Builder\RequestClient;
use Hostinger\AiTheme\Builder\Seo;
use Hostinger\AiTheme\Requests\Client as AiClient;
use Hostinger\AiTheme\Builder\WebsiteBuilder;
use Hostinger\AiTheme\Rest\BuilderRoutes;
use Hostinger\AiTheme\Rest\Routes;
use Hostinger\Surveys\Rest as SurveysRest;
use Hostinger\Surveys\SurveyManager;
use Hostinger\WpHelper\Config;
use Hostinger\WpHelper\Constants;
use Hostinger\WpHelper\Requests\Client;
use Hostinger\WpHelper\Utils;
use Hostinger\WpHelper\Utils as Helper;
use Hostinger\WpMenuManager\Manager;
use Hostinger\Amplitude\AmplitudeLoader;
use Hostinger\AiTheme\Shortcodes\ShortcodesManager;

defined( 'ABSPATH' ) || exit;

class Boot {
	public function run(): void {
        $this->load_hostinger_packages();
		$this->load_dependencies();
		$this->set_locale();
	}

    /**
     * @return void
     */
    public function hostinger_load_menus(): void {
        $manager = Manager::getInstance();
        $manager->boot();
    }

    /**
     * @return void
     */
    public function hostinger_load_amplitude(): void {
        $amplitude = AmplitudeLoader::getInstance();
        $amplitude->boot();
    }

    /**
     * @return void
     */
    private function load_hostinger_packages(): void {
        if ( ! has_action( 'plugins_loaded', 'hostinger_load_menus' ) ) {
            add_action( 'after_setup_theme', array( $this, 'hostinger_load_menus' ) );
        }

        if ( ! has_action( 'plugins_loaded', 'hostinger_load_amplitude' ) ) {
            add_action( 'after_setup_theme', array( $this, 'hostinger_load_amplitude' ) );
        }
    }

	private function load_dependencies(): void {
        $assets = new Assets();

        $updates = new Updates();

        $helper         = new Helper();
        $config_handler = new Config();
        $client         = new AiClient( $config_handler->getConfigValue( 'base_rest_uri', HOSTINGER_AI_WEBSITES_REST_URI ), [
            Config::TOKEN_HEADER  => $helper::getApiToken(),
            Config::DOMAIN_HEADER => $helper->getHostInfo(),
            'Content-Type' => 'application/json'
        ] );

        $request_client = new RequestClient( $client );
        $image_manager = new ImageManager();
        $affiliate_builder = new AffiliateBuilder();

        $website_builder = new WebsiteBuilder( $request_client, $image_manager, $affiliate_builder );
        $website_builder->init();

        $builder_routes = new BuilderRoutes( $website_builder );

        $routes = new Routes( $builder_routes );
        $routes->init();

        $utils = new Utils();
        $redirects = new Redirects( $utils );
        $seo = new Seo();
        $hooks = new Hooks( $image_manager );

        $shortcodes_manager = new ShortcodesManager();
        $shortcodes_manager->init();

		if ( is_admin() ) {
			$this->load_admin_dependencies();
		}
	}

	private function set_locale() {
		$plugin_i18n = new I18n();
	}


	private function load_admin_dependencies(): void
    {
        $image_manager = new ImageManager();

        new AdminAssets();
        new AdminMenu();
        new AdminHooks( $image_manager );

        $helper = new Helper();
        $config = new Config();
        $client = new Client(
            $config->getConfigValue( 'base_rest_uri', Constants::HOSTINGER_REST_URI ),
            [
                Config::TOKEN_HEADER  => $helper->getApiToken(),
                Config::DOMAIN_HEADER => $helper->getHostInfo(),
            ]
        );

        if ( class_exists( SurveyManager::class ) ) {
            $surveysRest   = new SurveysRest( $client );
            $surveyManager = new SurveyManager( $helper, $config, $surveysRest );
            $surveys       = new \Hostinger\AiTheme\Admin\Surveys( $surveyManager );
            $surveys->init();
        }
    }
}
