<?php

namespace Hostinger\AiTheme\Admin;

use Hostinger\AiTheme\Builder\ImageManager;
use Hostinger\AiTheme\Constants\PreviewImageConstant;

defined( 'ABSPATH' ) || exit;

class Hooks {
    private ImageManager $image_manager;
    public function __construct( ImageManager $image_manager ) {
        $this->image_manager = $image_manager;

        add_action( 'add_meta_boxes', array( $this, 'add_preview_image_metabox' ) );
        add_action( 'wp_insert_post_data', array( $this, 'save_preview_image_url' ), 0, 2 );
    }

    /**
     * @param $post_type
     *
     * @return void
     */
    public function add_preview_image_metabox( $post_type ): void {
        if ( $post_type != 'post' ) {
            return;
        }

        $post_types = post_type_supports( 'post', 'thumbnail' ) ? array( 'post' ) : array();

        add_meta_box(
            'hostinger_metabox',
            __( 'Featured Image with URL', 'hostinger-ai-theme' ),
            array( $this, 'render_metabox' ),
            $post_types,
            'normal',
            'low'
        );
    }

    /**
     * @param $post
     *
     * @return void
     */
    public function render_metabox( $post ): void {
        $image_url = get_post_meta( $post->ID, PreviewImageConstant::META_SLUG, true );

        include get_stylesheet_directory() . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'Admin' . DIRECTORY_SEPARATOR . 'Templates' . DIRECTORY_SEPARATOR . 'featured-image-metabox.php';
    }

    public function save_preview_image_url( array $data, array $post_data ): array {
        $post_id = !empty( $post_data['post_ID'] ) ? $post_data['post_ID'] : 0;

        if ( empty( $post_id ) ) {
            return $data;
        }

        if ( ! current_user_can( 'edit_post', $post_id ) || ! post_type_supports( $post_data['post_type'], 'thumbnail' ) || defined( 'DOING_AUTOSAVE' ) ) {
            return $data;
        }


        if ( isset( $_POST[ PreviewImageConstant::META_SLUG ] ) ) {
            $image_url = isset( $_POST[ PreviewImageConstant::META_SLUG ] ) ? esc_url_raw( wp_unslash( $_POST[ PreviewImageConstant::META_SLUG ] ) ) : '';

            $this->handle_external_url_field( $image_url, $post_id );
        }

        $featured_image_id = get_post_meta( $post_id, '_thumbnail_id', true );

        if ( ! empty( $featured_image_id ) ) {
            $this->image_manager->clean_external_image_data( $post_id );
        }

        return $data;
    }

    private function handle_external_url_field( string $image_url, int $post_id ) : void {
        update_post_meta( $post_id, PreviewImageConstant::META_SLUG, $image_url );

        if ( empty( $image_url ) ) {
            $this->image_manager->clean_external_image_data( $post_id );
            return;
        }

        $attachments = $this->image_manager->get_attachments_by_meta_value( PreviewImageConstant::POST_ID, $post_id );

        if ( empty( $attachments ) ) {
            $this->image_manager->create_blog_image_attachment( $post_id );
        }
    }
}

