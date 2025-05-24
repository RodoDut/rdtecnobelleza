<?php

namespace Hostinger\AiTheme;

use Hostinger\AiTheme\Builder\ImageManager;
use Hostinger\AiTheme\Constants\PreviewImageConstant;
use WP_Admin_Bar;
use stdClass;
use WP_REST_Request;
use WP_REST_Response;
use WP_Post;

defined( 'ABSPATH' ) || exit;

class Hooks {
    private ImageManager $image_manager;
    public function __construct( ImageManager $image_manager ) {
        $this->image_manager = $image_manager;

        if ( isset( $_GET['ai_preview'] ) || isset($_SERVER['HTTP_SEC_FETCH_DEST']) && $_SERVER['HTTP_SEC_FETCH_DEST'] == 'iframe' ) {
            add_filter( 'show_admin_bar', '__return_false' );
            add_action( 'wp_head', array( $this, 'hide_preview_domain_topbar' ), 999 );
        }

        add_filter( 'render_block', array( $this, 'replace_scroll_fade_class' ), 10, 2 );

        $this->rehook_edit_site_menu();

        $this->register_image_sizes();

        if ( ! is_admin() || (( defined( 'DOING_AJAX' ) && DOING_AJAX )) ) {
            add_filter( 'get_post_metadata', array( $this, 'set_thumbnail_id_true' ), 10, 3 );
            add_filter( 'wp_get_attachment_image_src', array( $this, 'replace_image_src' ), 10, 3 );
        }

        add_filter( 'rest_prepare_attachment', array( $this, 'replace_attachment_url' ), 10, 2 );
        add_filter( 'rest_pre_insert_post', array( $this, 'catch_featured_image_change' ), 10, 2 );
    }

    /**
     * @return void
     */
    public function rehook_edit_site_menu() {
        add_action( 'admin_bar_menu', [ $this, 'add_edit_site_menu' ], 41 );
    }

    /**
     * @param WP_Admin_Bar $wp_admin_bar The WP_Admin_Bar instance.
     *
     * @return void
     */
    public function add_edit_site_menu( WP_Admin_Bar $wp_admin_bar ) {

        if ( ! wp_is_block_theme() || ! current_user_can( 'edit_theme_options' ) ) {
            return;
        }

        $front_page_id = get_option( 'page_on_front' );

        if( is_front_page() || get_the_id() == $front_page_id ) {
            $wp_admin_bar->remove_menu('site-editor');
            return;
        }

        if ( ! $front_page_id ) {
            return;
        }

        $query_args = array(
            'post'   => $front_page_id,
            'action' => 'edit',
        );

        $edit_url = add_query_arg( $query_args, admin_url( 'post.php' ) );

        $wp_admin_bar->add_node(
            array(
                'id'    => 'site-editor',
                'title' => __( 'Edit site', 'hostinger-ai-theme' ),
                'href'  => $edit_url,
            )
        );

    }

    /**
     * @return void
     */
    public function hide_preview_domain_topbar() {
        ?>
        <style>
            #hostinger-preview-banner {
                display: none!important;
                box-sizing: border-box!important;
            }
        </style>
        <?php
    }

    /**
     * @return void
     */
    public function replace_scroll_fade_class( $block_content, $block ) {
        // Check if the block content contains the class wp-block-group
        if ( strpos( $block_content, 'hostinger-ai-fade-up' ) !== false ) {
            // Add the data-aos attribute to the block content
            $block_content = preg_replace(
                '/(<\w+\s+[^>]*class="[^"]*hostinger-ai-fade-up[^"]*")/',
                '$1 data-aos="fade-up"',
                $block_content,
                1
            );
        }

        return $block_content;
    }

    public function register_image_sizes(): void {
        add_image_size( 'blog-thumb', 530, 250, array( 'center', 'center' ) );
        add_image_size( 'blog-full', 1100, 450, array( 'center', 'center' ) );
    }

    /**
     * @param $value
     * @param $object_id
     * @param $meta_key
     *
     * @return mixed
     */
    public function set_thumbnail_id_true( $value, $object_id, $meta_key ) {
        $post_type = get_post_type( $object_id );

        if ( $post_type != 'post' || isset( $_POST['_wp_http_referer'] ) ) {
            return $value;
        }

        if ( $meta_key === '_thumbnail_id' ) {
            $attach_id = get_post_meta( $object_id, PreviewImageConstant::ATTACHMENT_ID, true );

            if ( ! empty( $attach_id ) ) {
                return $attach_id;
            }
        }

        return $value;
    }

    public function replace_image_src( mixed $image, int $attachment_id, mixed $size ): mixed {
        if ( ! is_numeric( $attachment_id ) || $attachment_id <= 0 ) {
            return $image;
        }

        $post_id = get_post_meta( $attachment_id, PreviewImageConstant::POST_ID, true );

        if ( empty( $post_id ) ) {
            return $image;
        }

        $image_url = get_post_meta( $post_id, PreviewImageConstant::META_SLUG, true );

        if ( empty( $image_url ) ) {
            return $image;
        }

        $cropped_image_url = $this->crop_external_image( $image_url, $size );

        return ! empty( $cropped_image_url ) ? $cropped_image_url : $image;
    }

    public function crop_external_image( string $image_url, string $size ): array {
        global $_wp_additional_image_sizes;

        $image_size = !empty($_wp_additional_image_sizes[ $size ]) ? $_wp_additional_image_sizes[ $size ] : '';

        if(!empty($image_size)) {
            $structure = array(
                'image_size' => $image_size
            );

            $image_manager = new ImageManager();

            $image_url = $image_manager->modify_image_url( $image_url, $structure );

            return array(
                $image_url,
                $image_size['width'],
                $image_size['height'],
                $image_size['crop'],
            );
        }

        return array();
    }

    public function replace_attachment_url( WP_REST_Response $response, WP_Post $post ): WP_REST_Response {
        $post_id = get_post_meta( $post->ID, PreviewImageConstant::POST_ID, true );

        if ( ! empty( $post_id ) ) {
            $external_url = get_post_meta( $post_id, PreviewImageConstant::META_SLUG, true );

            if( ! empty( $external_url ) ) {
                $cropped_image_url = $this->crop_external_image( $external_url, 'blog-thumb' );

                $response->data['guid']['rendered'] = $cropped_image_url;
                $response->data['source_url'] = $cropped_image_url;
                $response->data['media_details'] = array(
                        'blog-thumb' => array(
                                'source_url' => $cropped_image_url
                        )
                );
            }
        }

        return $response;
    }

    public function catch_featured_image_change( stdClass $prepared_post, WP_REST_Request $request ): stdClass {
        $params = $request->get_params();

        $attachment_id = (int)get_post_meta( $prepared_post->ID, PreviewImageConstant::ATTACHMENT_ID, true );
        $featured_image_id = ! empty( $params['featured_media'] ) ? (int)$params['featured_media'] : 0;

        if ( ! empty( $featured_image_id ) && $attachment_id !== $featured_image_id ) {
            $this->image_manager->clean_external_image_data( $prepared_post->ID );
        }

        return $prepared_post;
    }
}
