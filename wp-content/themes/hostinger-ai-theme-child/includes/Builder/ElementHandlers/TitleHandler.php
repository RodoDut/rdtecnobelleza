<?php

namespace Hostinger\AiTheme\Builder\ElementHandlers;

use DOMElement;

defined( 'ABSPATH' ) || exit;

class TitleHandler implements ElementHandler {
    public function handle(DOMElement &$node, array $element_structure): void
    {
        $prefix = ! empty( $element_structure['prefix'] ) ? $element_structure['prefix'] : '';
        $suffix = ! empty( $element_structure['suffix'] ) ? $element_structure['suffix'] : '';

        $node->nodeValue = $prefix . $element_structure['content'] . $suffix;
    }
}