<?php

namespace Hostinger\AiTheme\Builder\ElementHandlers;

use DOMElement;

defined( 'ABSPATH' ) || exit;

class ButtonHandler implements ElementHandler {
    public function handle(DOMElement &$node, array $element_structure): void
    {
        $links = $node->getElementsByTagName('a');

        if ($links->length > 0) {
            $link = $links->item(0);
            $link->nodeValue = $element_structure['content'];
        }
    }
}