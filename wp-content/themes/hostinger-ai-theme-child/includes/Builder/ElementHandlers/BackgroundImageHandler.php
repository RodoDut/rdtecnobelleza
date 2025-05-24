<?php

namespace Hostinger\AiTheme\Builder\ElementHandlers;

use Hostinger\AiTheme\Builder\ImageManager;
use Hostinger\WpHelper\Utils;
use Hostinger\WpHelper\Utils as Helper;
use DOMElement;
use DOMDocument;
use Hostinger\WpHelper\Config;
use Hostinger\AiTheme\Requests\Client;

defined( 'ABSPATH' ) || exit;

class BackgroundImageHandler implements ElementHandler {
    private const GET_UNSPLASH_IMAGE_ACTION = '/v3/wordpress/plugin/download-image';
    /**
     * @var Helper
     */
    private Utils $helper;
    /**
     * @var Client
     */
    private Client $client;

    public function __construct() {
        $this->helper         = new Helper();
        $config_handler = new Config();
        $this->client         = new Client( $config_handler->getConfigValue( 'base_rest_uri', HOSTINGER_AI_WEBSITES_REST_URI ), [
            Config::TOKEN_HEADER  => $this->helper::getApiToken(),
            Config::DOMAIN_HEADER => $this->helper->getHostInfo(),
            'Content-Type' => 'application/json'
        ] );
    }

    /**
     * @param DOMElement $node
     * @param array      $element_structure
     *
     * @return void
     */
    public function handle(DOMElement &$node, array $element_structure): void
    {
        if ( empty( $element_structure['content'] ) ) {
            return;
        }

        $previousElement = $node->previousSibling;

        $value = str_replace(' wp:group ', '', $previousElement->nodeValue);

        $block = json_decode($value, true);

        $image_manager = new ImageManager( $element_structure['content'] );
        $image_data = $image_manager->get_unsplash_image_data();

        if ( ! empty( get_object_vars( $image_data ) ) && ! empty( $block ) ) {
            $image_url = $image_manager->modify_image_url( $image_data->image, $element_structure );

            if ( ! empty($block['className'])
                 && str_contains(
                     $block['className'],
                     'hostinger-ai-background-image'
                 )) {
                $block['style']['background']['backgroundImage']['url'] = $image_url;
                $block['style']['background']['backgroundImage']['id'] = 0;
                $block['style']['background']['backgroundImage']['title'] = '';
            }

            $previousElement->nodeValue = ' wp:group ' . json_encode( $block ) .' ';
        }
    }
}