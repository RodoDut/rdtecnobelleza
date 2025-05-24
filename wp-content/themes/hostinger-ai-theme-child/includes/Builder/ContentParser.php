<?php

namespace Hostinger\AiTheme\Builder;

use DOMDocument;

defined( 'ABSPATH' ) || exit;

class ContentParser {
    /**
     * @var string
     */
    private array $section;

    /**
     * @param array $content_data
     */
    public function __construct( array $section ) {
        $this->section = $section;
    }

    /**
     * @return string
     */
    public function output() {
        if(empty($this->section['elements'])) {
            return $this->section['html'];
        }

        $dom = new DOMDocument();
        @$dom->loadHTML($this->section['html'], LIBXML_HTML_NODEFDTD | LIBXML_HTML_NOIMPLIED);

        $processor = new ElementProcessor( $this->section );
        $processor->setHelper( new Helper() );

        $html = $processor->process( $dom );

        $blocks = parse_blocks( $html );

        $serialized = serialize_blocks( $blocks );

        // URL fix.
        return str_replace( '\u0026', '&', $serialized );
    }
}
