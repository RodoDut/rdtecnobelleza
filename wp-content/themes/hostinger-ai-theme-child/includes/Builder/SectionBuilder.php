<?php

namespace Hostinger\AiTheme\Builder;

use DOMDocument;
use DOMXPath;
use Exception as ExceptionAlias;

defined( 'ABSPATH' ) || exit;

class SectionBuilder {
    /**
     * @var string
     */
    private string $section;

    /**
     * @var string
     */
    private string $block_content;

    /**
     * @var array
     */
    private array $block_structure = array();

    /**
     * @var array
     */
    private array $block_used_elements = array();

    /**
     * @var Helper
     */
    private Helper $helper;

    /**
     * @param string $section
     */
    public function __construct( string $section ) {

        // Use same CTA block for reservation.
        if ( $section == 'reservation' ) {
            $section = 'call-to-action';
        }

        $this->section = strtolower($section);
    }

    /**
     * @param Helper $helper
     *
     * @return void
     */
    public function setHelper( Helper $helper ) {
        $this->helper = $helper;
    }

    /**
     * @return bool
     * @throws ExceptionAlias
     */
    public function generate(): bool {
        // Random section variation is picked.
        $section_picked = $this->pick_section();

        if(!empty($section_picked)) {
            // Main block structure is loaded.
            $this->load_block_structure();

            // Picked section is scanned, only used elements from block structure are loaded.
            $this->scan_block_elements();

            return true;
        }

        return false;
    }

    /**
     * @return void
     */
    public function pick_section(): bool {
        $block_variations = glob(get_stylesheet_directory() . DIRECTORY_SEPARATOR . 'blocks' . DIRECTORY_SEPARATOR  . $this->section . DIRECTORY_SEPARATOR . $this->section . '_*.html');

        if(empty($block_variations)) {
            return false;
        }

        shuffle($block_variations);

        $block_file = array_shift($block_variations);

        $this->block_content = file_get_contents($block_file);

        return true;
    }

    /**
     * @throws ExceptionAlias
     */
    public function load_block_structure(): void {
        $structure_file = get_stylesheet_directory() . DIRECTORY_SEPARATOR . 'blocks' . DIRECTORY_SEPARATOR . $this->section . DIRECTORY_SEPARATOR . $this->section . '.json';

        if( !file_exists( $structure_file ) ) {
            throw new ExceptionAlias('Section ' . $this->section . ' structure file does not exists.');
        }

        $structure = file_get_contents( $structure_file );

        if( !empty( $structure) ) {
            $this->block_structure = json_decode($structure, true);
        }
    }

    /**
     * @return void
     */
    public function scan_block_elements(): void {
        if(empty($this->block_structure)) {
            return;
        }

        $elements = $this->extract_elements();

        foreach ($elements as $element_data) {
            $structure = $this->helper->find_structure($this->block_structure, $element_data);

            if(!empty($structure)) {
                $this->block_used_elements[uniqid()] = $structure;
            }
        }
    }

    /**
     * @return string
     */
    public function extract_elements(): array {
        $result = [];

        $dom = new DOMDocument();
        @$dom->loadHTML($this->block_content, LIBXML_HTML_NODEFDTD | LIBXML_HTML_NOIMPLIED);

        $xpath = new DOMXPath($dom);

        $pattern = '/hostinger-ai-[^\s]+/';

        $text_nodes = $xpath->query('//*[contains(@class,"hostinger-ai-")]');

        foreach ($text_nodes as $node) {
            if ($node->nodeType === XML_ELEMENT_NODE) {
                $classes = $node->getAttribute('class');

                if(empty($classes)) {
                    continue;
                }

                $ai_elements = $this->helper->extract_class_names($classes, $pattern);

                if(!empty($ai_elements)) {
                    foreach($ai_elements as $ai_element) {
                        $result[] = [
                            'class' => $ai_element,
                            'index' => $this->helper->extract_index_number($classes),
                        ];
                    }
                }
            }
        }

        return $result;
    }

    /**
     * @return array
     */
    public function get_block_used_elements(): array {
        return $this->block_used_elements;
    }

    /**
     * @return string
     */
    public function get_block_content(): string {
        return $this->block_content;
    }
}
