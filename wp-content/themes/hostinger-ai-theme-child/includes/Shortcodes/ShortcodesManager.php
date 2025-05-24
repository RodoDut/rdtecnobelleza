<?php

namespace Hostinger\AiTheme\Shortcodes;

use Hostinger\AiTheme\Shortcodes\ContactForm;

/**
 * Shortcodes Manager
 */
class ShortcodesManager {
    /**
     * Initialize shortcodes
     */
    public function init(): void {
        $this->load_shortcodes();
    }

    /**
     * Load all shortcode classes
     */
    private function load_shortcodes(): void {
        new ContactForm();
    }
}