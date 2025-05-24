<?php

namespace Hostinger\AiTheme\Builder;

defined( 'ABSPATH' ) || exit;

class NavigationBuilder {
    /**
     * @var array
     */
    private array $pages;

    /**
     * @param array $pages
     */
    public function __construct( array $pages )
    {
        $this->pages = $pages;
    }

    public function updateMenus() {
        $menu_id = $this->createMenu();

        $this->updateTemplatePart( 'header', $menu_id );
        $this->updateTemplatePart( 'footer', $menu_id );
    }

    /**
     * @return int
     */
    private function createMenu(): int {
        $content = $this->generateMenuItems();

        $menu_id = wp_insert_post(
            array(
                'post_title'   => 'AI menu',
                'post_type'    => 'wp_navigation',
                'post_status'  => 'publish',
                'post_content' => $content
            ),
            false,
            false);

        if ( is_wp_error( $menu_id ) ) {
            throw new Exception( 'Error creating menu: ' . $menu_id->get_error_message() );
        }

        return $menu_id;
    }

    /**
     * @return string
     */
    private function generateMenuItems(): string {
        $html = '';

        if ( !empty( $this->pages ) ) {
            foreach( $this->pages as $page_data ) {
                $link_meta = array(
                    'label' => $page_data['title'],
                    'type' => 'page',
                    'id' => $page_data['page_id'],
                    'url' => get_permalink( $page_data['page_id'] ),
                    'kind' => 'post-type',
                    'isTopLevelLink' => true,
                );

                $html .= '<!-- wp:navigation-link ' .json_encode( $link_meta, JSON_UNESCAPED_UNICODE ). ' /-->';
            }
        }

        return $html;
    }

    /**
     * @param string $post_name
     *
     * @return bool
     */
    private function updateTemplatePart( string $post_name, int $menu_id ) : bool {
        $template_part_id = $this->findTemplatePart( $post_name );

        if( empty($template_part_id) ) {
            $template_part_id = wp_insert_post([
                'post_title' => ucfirst($post_name),
                'post_status' => 'publish',
                'post_type' => 'wp_template_part',
            ]);

            $this->prepareTaxonomyAndTerms( $post_name, $template_part_id );
        }

        $new_template_part = $this->loadTemplatePart($post_name);

        if(empty($new_template_part)) {
            error_log( 'Cant load template part: ' . $post_name );
            return false;
        }

        $replaced_template_part = $this->replaceTemplatePart( $new_template_part, $menu_id );

        if(empty($replaced_template_part)) {
            error_log( 'Cant replace template part: ' . $post_name );
            return false;
        }

        return wp_update_post([
            'ID' => $template_part_id,
            'post_content' => $replaced_template_part
        ]);
    }

    /**
     * @param string $new_template_part
     * @param int    $menu_id
     *
     * @return string
     */
    private function replaceTemplatePart( string $new_template_part, int $menu_id ): string {
        $blocks = parse_blocks( $new_template_part );

        foreach ($blocks as &$block) {
            $this->updateMenuRef($block, $menu_id);
        }

        return serialize_blocks($blocks);
    }

    /**
     * @param array $block
     * @param int   $menu_id
     *
     * @return void
     */
    private function updateMenuRef(array &$block, int $menu_id): void {
        if ($block['blockName'] === 'core/navigation') {
            $block['attrs']['ref'] = $menu_id;
        }

        if ( !empty( $block['innerBlocks'] ) ) {
            foreach ( $block['innerBlocks'] as &$innerBlock ) {
                $this->updateMenuRef( $innerBlock, $menu_id );
            }
        }
    }

    /**
     * @param string $template_part
     *
     * @return string
     */
    private function loadTemplatePart( string $template_part ) : string {
        $file = get_template_directory() . DIRECTORY_SEPARATOR . 'parts' . DIRECTORY_SEPARATOR . $template_part . '.html';

        if(!file_exists($file)) {
            error_log('file does not exists' . $file);
            return '';
        }

        $file_content = file_get_contents( $file );

        return $this->translate($file_content);
    }

    /**
     * @param string $post_name
     *
     * @return int
     */
    private function findTemplatePart( string $post_name ): int {
        $post_ids = get_posts(array
        (
            's'   => $post_name,
            'post_type'   => 'wp_template_part',
            'numberposts' => 1,
            'fields' => 'ids'
        ));

        if(empty($post_ids)) {
            return 0;
        }

        return array_shift( $post_ids );
    }

    /**
     * @param string $post_name
     * @param int    $template_part_id
     *
     * @return void
     */
    private function prepareTaxonomyAndTerms( string $post_name, int $template_part_id ){
        global $wpdb;

        // TODO: refactor this

        $template_part_options = get_option('hostinger_ai_template_part_options', array());

        if(empty($template_part_options)) {

            $term_name = 'hostinger-ai-theme';

            $wpdb->insert(
                $wpdb->terms,
                [
                    'name' => $term_name,
                    'slug' => sanitize_title($term_name)
                ]
            );

            $theme_term_id = $wpdb->insert_id;

            $term_name = 'footer';

            $wpdb->insert(
                $wpdb->terms,
                [
                    'name' => $term_name,
                    'slug' => sanitize_title($term_name)
                ]
            );

            $footer_term_id = $wpdb->insert_id;

            $wpdb->insert(
                $wpdb->term_taxonomy,
                [
                    'term_id'  => $theme_term_id,
                    'taxonomy' => 'wp_theme'
                ]
            );

            $theme_taxonomy_id = $wpdb->insert_id;

            $wpdb->insert(
                $wpdb->term_taxonomy,
                [
                    'term_id'  => $footer_term_id,
                    'taxonomy' => 'wp_template_part_area'
                ]
            );

            $part_taxonomy_id = $wpdb->insert_id;

            update_option( 'hostinger_ai_template_part_options', array(
                'theme_taxonomy_id' => $theme_taxonomy_id,
                'part_taxonomy_id' => $part_taxonomy_id
            ) );
        }

        $template_part_options = get_option('hostinger_ai_template_part_options', array());

        $wpdb->insert(
            $wpdb->term_relationships,
            [
                'object_id'  => $template_part_id,
                'term_taxonomy_id' => $template_part_options['theme_taxonomy_id']
            ]
        );

        if($post_name == 'footer') {
            $wpdb->insert(
                $wpdb->term_relationships,
                [
                    'object_id'  => $template_part_id,
                    'term_taxonomy_id' => $template_part_options['part_taxonomy_id']
                ]
            );
        }
    }

    /**
     * @param string $content
     *
     * @return string
     */
    private function translate( string $content ): string {
        if ( ! empty( $this->translations() ) ) {
            foreach( $this->translations() as $key => $translation ) {
                $content = str_replace( 'trans-' . $key, $translation, $content );
            }
        }

        return $content;
    }

    /**
     * @return array
     */
    private function translations(): array {
        return array(
            'menu' => __( 'Menu', 'hostinger-ai-theme' ),
            'contacts' => __( 'Contacts', 'hostinger-ai-theme' ),
            'socials' => __( 'Socials', 'hostinger-ai-theme' ),
        );
    }
}
