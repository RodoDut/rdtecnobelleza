<?php

namespace Hostinger\AiTheme\Builder\ElementHandlers;

defined( 'ABSPATH' ) || exit;

use DOMElement;

interface ElementHandler {
    public function handle(DOMElement &$node, array $element_structure);
}