<?php

namespace Hostinger\AiTheme\Builder\ElementHandlers;

use Hostinger\AiTheme\Builder\ImageManager;
use stdClass;
use DOMElement;

defined( 'ABSPATH' ) || exit;

class ImageHandler implements ElementHandler {
    /**
     * @param DOMElement $node
     * @param array      $element_structure
     *
     * @return void
     */
    public function handle(DOMElement &$node, array $element_structure): void
    {

        $content = !empty( $element_structure['content'] ) ? $element_structure['content'] : '';

        if ( !empty( $element_structure['default_content'] ) ) {
            $content = $element_structure['default_content'];
        }

        if ( empty( $content ) ) {
            return;
        }

        $image_manager = new ImageManager( $content );

        $image_data = $image_manager->get_unsplash_image_data( !empty( $element_structure['default_content'] ) );

        if ( ! empty( get_object_vars( $image_data ) ) ) {
            $image_url = $image_manager->modify_image_url( $image_data->image, $element_structure );

            $imgs = $node->getElementsByTagName('img');

            if ($imgs->length > 0) {
                $img = $imgs->item(0);
                $img->setAttribute('src', $image_url);
            }
        }
    }
}