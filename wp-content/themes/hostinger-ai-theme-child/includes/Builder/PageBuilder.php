<?php

namespace Hostinger\AiTheme\Builder;

defined( 'ABSPATH' ) || exit;

class PageBuilder {
    /**
     * @var string
     */
    private array $content_data;

    /**
     * @param array $content_data
     */
    public function __construct( array $content_data ) {
        $this->content_data = $content_data;
    }

    /**
     * @return array
     */
    public function build_pages(): array {
        $pages = array();

        foreach($this->content_data['pages'] as $page => $page_data) {

            $page_content = '';

            if(!empty($page_data['sections'])) {
                foreach ($page_data['sections'] as $section) {

                    $content_parser = new ContentParser( $section );

                    $page_content .= $content_parser->output();
                }
            }

            $page_clean = trim( $page );

            $page_title = mb_convert_case( $page_clean, MB_CASE_TITLE, "UTF-8" );

            $new_page = array(
                'post_title'    => $page_title,
                'post_content'  => $page_content,
                'post_status'   => 'publish',
                'post_type'     => 'page',
            );

            $page_id = wp_insert_post($new_page);

            if(!empty($page_id)) {
                $pages[$page] = array(
                    'title' => $page_title,
                    'page_id' => $page_id,
                );
            }
        }

        update_option( 'hostinger_ai_created_pages', $pages );

        return $pages;
    }
}
