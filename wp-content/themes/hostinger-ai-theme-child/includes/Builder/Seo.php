<?php

namespace Hostinger\AiTheme\Builder;

defined( 'ABSPATH' ) || exit;

class Seo {
    const YOAST_SEO_PLUGIN_SLUG = 'yoast';
    const RANK_MATH_SEO_PLUGIN_SLUG = 'rank-math';
    const ALL_IN_ONE_SEO_PLUGIN_SLUG = 'all-in-one-seo-pack';

    public function __construct() {
        add_action( 'wp_head', array( $this, 'print_seo_meta_tags' ) );
    }

    public function add_seo_meta_tags( $post_id ): void {
        $seo_meta              = $this->get_seo_meta( $post_id );
        $yoast_seo_active      = $this->is_yoast_seo_active();
        $rank_math_seo_active  = $this->is_rank_math_seo_active();
        $all_in_one_seo_active = $this->is_all_in_one_seo_active();

        if ( $yoast_seo_active && ! $seo_meta['yoast_seo_tags_created'] ) {
            $this->add_seo_tags_for_plugin( $post_id, $seo_meta, self::YOAST_SEO_PLUGIN_SLUG );
        } elseif ( $rank_math_seo_active && ! $seo_meta['rank_seo_tags_created'] ) {
            $this->add_seo_tags_for_plugin( $post_id, $seo_meta, self::RANK_MATH_SEO_PLUGIN_SLUG );
        } elseif ( $all_in_one_seo_active && ! $seo_meta['all_in_one_seo_tags_created'] ) {
            $this->add_seo_tags_for_plugin( $post_id, $seo_meta, self::ALL_IN_ONE_SEO_PLUGIN_SLUG );
        }
    }

    public function print_seo_meta_tags(): void {
        $post_id  = get_the_ID();
        $seo_meta = $this->get_seo_meta( $post_id );

        if ( ! $this->is_seo_plugin_active() ) {
            $this->output_meta_tags( $this->format_keywords($seo_meta['keywords']), $seo_meta['description'], $seo_meta['title'] );
        }
    }

    private function get_seo_meta( int $post_id ): array {
        return [
            'keywords'                    => get_post_meta( $post_id, 'hostinger_ai_post_meta_seo_keywords', true ),
            'description'                 => get_post_meta( $post_id, 'hostinger_ai_post_meta_description', true ),
            'title'                       => get_post_meta( $post_id, 'hostinger_ai_post_meta_title', true ),
            'yoast_seo_tags_created'      => get_post_meta( $post_id, 'hts_yoast_seo_tags_created', true ),
            'rank_seo_tags_created'       => get_post_meta( $post_id, 'hts_rank_seo_tags_created', true ),
            'all_in_one_seo_tags_created' => get_post_meta( $post_id, 'hts_all_in_one_seo_tags_created', true ),
        ];
    }

    private function add_seo_tags_for_plugin( int $post_id, array $seo_meta, string $plugin ): void {
        if ( $plugin === self::YOAST_SEO_PLUGIN_SLUG ) {
            $this->add_yoast_meta_tags( $post_id, $seo_meta['description'], $this->get_single_keyword( $seo_meta['keywords'] ), $seo_meta['title'] );
            update_post_meta( $post_id, 'hts_yoast_seo_tags_created', true );
        } elseif ( $plugin === self::RANK_MATH_SEO_PLUGIN_SLUG ) {
            $this->add_rank_math_meta_tags( $post_id, $seo_meta['description'], $this->get_keywords( $seo_meta['keywords'], 4 ), $seo_meta['title'] );
            update_post_meta( $post_id, 'hts_rank_seo_tags_created', true );
        } elseif ( $plugin === self::ALL_IN_ONE_SEO_PLUGIN_SLUG ) {
            $this->all_in_one_meta_tags( $post_id, $seo_meta['description'], $this->get_single_keyword( $seo_meta['keywords'] ), $seo_meta['title'] );
            update_post_meta( $post_id, 'hts_all_in_one_seo_tags_created', true );
        }
    }

    private function is_seo_plugin_active(): bool {
        return $this->is_yoast_seo_active() || $this->is_rank_math_seo_active() || $this->is_all_in_one_seo_active();
    }

    public function add_yoast_meta_tags( int $post_id, string $meta_description, string $keyword, string $meta_title ): void {
        if ( $meta_title ) {
            update_post_meta( $post_id, '_yoast_wpseo_title', $meta_title );
        }

        if ( $meta_description ) {
            update_post_meta( $post_id, '_yoast_wpseo_metadesc', $meta_description );
        }
        if ( $keyword ) {
            update_post_meta( $post_id, '_yoast_wpseo_focuskw', $keyword );
        }
    }

    public function add_rank_math_meta_tags( int $post_id, string $meta_description, string $keyword, string $meta_title ): void {
        if ( $meta_description ) {
            update_post_meta( $post_id, 'rank_math_description', $meta_description );
        }
        if ( $keyword ) {
            update_post_meta( $post_id, 'rank_math_focus_keyword', $keyword );
        }
        if ( $meta_title ) {
            update_post_meta( $post_id, 'rank_math_title', $meta_title );
        }
    }

    public function all_in_one_meta_tags( int $post_id, string $meta_description, string $keyword, string $meta_title ): void {
        $data = [];

        if ( $meta_description ) {
            $data['description'] = '#post_excerpt ' . $meta_description;
        }

        if ( $meta_title ) {
            $data['title'] = $meta_title;
        }

        if ( class_exists( \AIOSEO\Plugin\Common\Models\Post::class ) ) {
            \AIOSEO\Plugin\Common\Models\Post::savePost( $post_id, $data );
        }
    }

    private function output_meta_tags( string $seo_keywords, string $seo_description, string $seo_title = '' ): void {
        if ( ! empty( $seo_keywords ) ) {
            echo '<meta name="keywords" content="' . esc_attr( $seo_keywords ) . '" />' . "\n";
        }
        if ( ! empty( $seo_description ) ) {
            echo '<meta name="description" content="' . esc_attr( $seo_description ) . '" />' . "\n";
        }

        if ( ! empty( $seo_title ) ) {
            echo '<meta name="title" content="' . esc_attr( $seo_title ) . '" />' . "\n";
        }
    }

    private function get_single_keyword( mixed $keywords ): string {
        if ( is_string( $keywords ) ) {
            return trim( $keywords );
        }

        return reset( $keywords );
    }

    public function format_keywords( mixed $keywords ): string {
        if ( is_string( $keywords ) ) {
            return $keywords;
        }

        if ( ! is_array( $keywords ) ) {
            return '';
        }

        $keywords = array_map( 'trim', $keywords );
        $keywords = array_filter( $keywords );

        return implode( ', ', $keywords );
    }

    private function get_keywords( mixed $keywords, int $max_count = 1 ): string {
        if ( is_string( $keywords ) ) {
            return $keywords;
        }

        if ( is_array( $keywords ) ) {
            $keywords = array_slice( $keywords, 0, $max_count );
            $keywords = array_map( 'trim', $keywords );
            $keywords = array_filter( $keywords );

            return implode( ', ', $keywords );
        }

        return '';
    }

    private function is_yoast_seo_active(): bool {
        return is_plugin_active( 'wordpress-seo/wp-seo.php' );
    }

    private function is_rank_math_seo_active(): bool {
        return is_plugin_active('seo-by-rank-math/rank-math.php');
    }

    private function is_all_in_one_seo_active(): bool {
        return is_plugin_active('all-in-one-seo-pack/all_in_one_seo_pack.php');
    }
}
