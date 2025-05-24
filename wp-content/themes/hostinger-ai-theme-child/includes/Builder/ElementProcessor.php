<?php

namespace Hostinger\AiTheme\Builder;

use Hostinger\AiTheme\Builder\ElementHandlers\ButtonHandler;
use Hostinger\AiTheme\Builder\ElementHandlers\ImageHandler;
use Hostinger\AiTheme\Builder\ElementHandlers\TitleHandler;
use Hostinger\AiTheme\Builder\ElementHandlers\BackgroundImageHandler;
use Hostinger\AiTheme\Builder\Helper;
use DOMDocument;
use DOMXPath;

defined( 'ABSPATH' ) || exit;

class ElementProcessor {
    /**
     * @var array
     */
    protected array $handlers = [];

    /**
     * @var string
     */
    private array $section;

    /**
     * @var Helper
     */
    private Helper $helper;

    /**
     * @param array $section
     */
    public function __construct( array $section ) {
        $this->handlers = [
            'hostinger-ai-title' => new TitleHandler(),
            'hostinger-ai-subtitle' => new TitleHandler(),
            'hostinger-ai-cta-button' => new ButtonHandler(),
            'hostinger-ai-project-title' => new TitleHandler(),
            'hostinger-ai-service-title' => new TitleHandler(),
            'hostinger-ai-testimonial-text' => new TitleHandler(),
            'hostinger-ai-service-description' => new TitleHandler(),
            'hostinger-ai-project-description' => new TitleHandler(),
            'hostinger-ai-description' => new TitleHandler(),
            'hostinger-ai-testimonial-image' => new ImageHandler(),
            'hostinger-ai-image' => new ImageHandler(),
            'hostinger-ai-service-image' => new ImageHandler(),
            'hostinger-ai-project-image' => new ImageHandler(),
            'hostinger-ai-background-image' => new BackgroundImageHandler(),
            'hostinger-ai-card-title' => new TitleHandler(),
            'hostinger-ai-card-description' => new TitleHandler(),
            'hostinger-ai-card-price' => new TitleHandler(),
            'hostinger-ai-workplace' => new TitleHandler(),
            'hostinger-ai-date' => new TitleHandler(),
        ];

        $this->section = $section;
    }

    /**
     * @param Helper $helper
     *
     * @return void
     */
    public function setHelper( Helper $helper ): void {
        $this->helper = $helper;
    }

    /**
     * @param DOMDocument $dom
     *
     * @return mixed
     */
    public function process( DOMDocument $dom ): string {
        $xpath = new DOMXPath($dom);
        $text_nodes = $xpath->query('//*[contains(@class,"hostinger-ai-")]');

        foreach ($text_nodes as $node) {
            if ($node->nodeType === XML_ELEMENT_NODE) {
                $classes = $node->getAttribute('class');

                if (empty($classes)) {
                    continue;
                }

                preg_match_all('/hostinger-ai-[^\s]+/', $classes, $matches);
                $ai_elements = $matches[0];

                $index = $this->helper->extract_index_number($classes);

                foreach ($ai_elements as $ai_element) {
                    if (isset($this->handlers[$ai_element])) {
                        $element_data = [
                            'class' => $ai_element,
                            'index' => $index
                        ];

                        $element_structure = $this->helper->find_structure($this->section['elements'], $element_data);

                        if (!empty($element_structure)) {
                            $this->handlers[$ai_element]->handle($node, $element_structure);
                        }
                    }
                }
            }
        }

        $html = $dom->saveHTML();

        $html = preg_replace('/<\/html>$/', '', $html);
        $html = preg_replace('/<\/body>$/', '', $html);

        return $html;
    }
}
