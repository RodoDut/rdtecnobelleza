<?php

namespace Hostinger\AiTheme\Builder;

use Hostinger\AiTheme\Admin\Hooks;
use Hostinger\AiTheme\Constants\PreviewImageConstant;
use stdClass;
use Hostinger\WpHelper\Config;
use Hostinger\AiTheme\Requests\Client;
use Hostinger\WpHelper\Utils;
use Hostinger\WpHelper\Utils as Helper;

defined( 'ABSPATH' ) || exit;

class ImageManager {
    public const GENERATE_CONTENT_IMAGES_ACTION = '/v3/wordpress/plugin/search-images';
    public const GENERATE_CONTENT_ACTION = '/v3/wordpress/plugin/generate-content';
    public const GET_UNSPLASH_IMAGE_ACTION = '/v3/wordpress/plugin/download-image';
    /**
     * @var string
     */
    public string $keyword;
    /**
     * @var string
     */
    public string $keyword_slug;
    /**
     * @var Helper
     */
    private Utils $helper;
    /**
     * @var Client
     */
    private Client $client;

    /**
     * @param string $keyword
     */
    public function __construct( string $keyword = '' )
    {
        $this->keyword        = $keyword;
        if(!empty($this->keyword)) {
            $this->keyword_slug = sanitize_title($this->keyword);
        }
        $this->helper         = new Helper();
        $config_handler       = new Config();
        $this->client         = new Client( $config_handler->getConfigValue( 'base_rest_uri', HOSTINGER_AI_WEBSITES_REST_URI ), [
            Config::TOKEN_HEADER  => $this->helper::getApiToken(),
            Config::DOMAIN_HEADER => $this->helper->getHostInfo(),
            'Content-Type' => 'application/json'
        ] );
    }

    public function set_keyword(string $keyword): void
    {
        $this->keyword = $keyword;
        $this->keyword_slug = sanitize_title($this->keyword);
    }

    /**
     * @return object
     */
    public function get_unsplash_image_data( bool $random = false ): object {
        $transient_key = 'image_data_' . $this->keyword_slug;

        if ( get_transient( $transient_key ) ) {
            $image_list = get_transient( $transient_key );
        } else {
            $image_list = $this->fetch_image_list();

            if ( !empty( $image_list ) ) {
                set_transient( $transient_key, $image_list, DAY_IN_SECONDS );
            }
        }

        return $this->pick_image_from_list( $image_list, $random );
    }

    /**
     * @param array $image_list
     * @param bool  $random
     *
     * @return mixed|stdClass|void
     */
    public function pick_image_from_list( array $image_list, bool $random = false ) {
        $used_images = get_option('hostinger_ai_used_images', []);

        if( empty( $image_list ) ) {
            return new stdClass();
        }

        if ( ! empty( $random ) ) {
            shuffle( $image_list );
        }

        foreach( $image_list as $image ) {
            if ( empty( $used_images[$this->keyword_slug][$image->image] ) ) {
                $used_images[$this->keyword_slug][$image->image] = $image;

                update_option( 'hostinger_ai_used_images', $used_images, false );

                return $image;
            }
        }

        update_option( 'hostinger_ai_used_images', [], false );

        return end($image_list);
    }

    /**
     * @return array
     */
    public function fetch_image_list(): array {
        try {
            $site_url = get_option( 'siteurl' );
            $host     = parse_url( $site_url, PHP_URL_HOST );

            $response = $this->client->post( self::GENERATE_CONTENT_IMAGES_ACTION, json_encode( [
                'domain'      => $host,
                'description' => $this->keyword,
                'limit'       => 20,
            ] ) );

            if ( is_wp_error( $response ) ) {
                error_log( 'Hostinger AI Theme - HTTP request error: ' . print_r( $response, true ) );

                return [];
            }

            $response_code      = wp_remote_retrieve_response_code( $response );
            $response_body      = wp_remote_retrieve_body( $response );
            $response_data_body = json_decode( $response_body, true );
            $response_data      = $response_data_body['data']['list'] ?? [];

            if ( empty( $response_data ) ) {
                error_log( 'Hostinger AI Theme - Empty image list response: ' . print_r( $response, true ) );

                return [];
            }

            if ( $response_code !== 200 ) {
                error_log( 'Hostinger AI Theme: Request error' );
                error_log( 'Hostinger AI Theme: ' . print_r( $response_data, true ) );

                return [];
            }

            return array_map( static function ( $item ) {
                return (object)[
                    'image'           => $item['photo_image_url'] ?? '',
                    'alt_description' => $item['description'] ?? '',
                ];
            }, $response_data );
        } catch ( Exception $exception ) {
            error_log( 'Hostinger AI Theme - Error fetching image list: ' . $exception->getMessage() );
        }

        return [];
    }

    /**
     * @param $url
     * @param $image_size_data
     *
     * @return string
     */
    public function modify_image_url( $url, $element_structure = null ): string {
        $url = $url . '?q=85';
        $parsed_url = parse_url( $url );

        parse_str( $parsed_url['query'], $query_params );

        if ( ! empty( $element_structure['image_size'] ) ) {

            if ( ! empty( $element_structure['image_size']['width'] ) ) {
                $query_params['w'] = $element_structure['image_size']['width'];
            }

            if ( ! empty( $element_structure['image_size']['height'] ) ) {
                $query_params['h'] = $element_structure['image_size']['height'];
            }

            if ( ! empty( $element_structure['image_size']['crop'] ) ) {
                $query_params['fit'] = 'crop';
            }

        }

        $new_query = http_build_query( $query_params );

        return $parsed_url['scheme'] . '://' . $parsed_url['host'] . $parsed_url['path'] . '?' . $new_query;
    }

    public function create_blog_image_attachment( int $post_id ): bool {
        $attachment = array(
            'post_title' => 'External Image For post: ' . $post_id,
            'post_content' => '',
            'post_status' => 'inherit'
        );

        $attach_id = wp_insert_attachment( $attachment, false, $post_id );

        if ( is_wp_error( $attach_id ) ) {
            return false;
        }

        update_post_meta( $post_id, PreviewImageConstant::ATTACHMENT_ID, $attach_id );
        update_post_meta( $attach_id, PreviewImageConstant::POST_ID, $post_id );

        return true;
    }

    public function get_attachments_by_meta_value( string $meta_key, string $meta_value ): array {
        $args = [
            'post_type'      => 'attachment',
            'post_status'    => 'any',
            'meta_key'       => $meta_key,
            'meta_value'     => $meta_value,
            'posts_per_page' => -1,
            'fields'         => 'ids',
        ];

        return get_posts($args);
    }

    public function delete_attachments_by_meta_value( string $meta_key, string $meta_value ): bool {
        $attachments = $this->get_attachments_by_meta_value( $meta_key, $meta_value );

        if ( empty($attachments) ) {
            return false;
        }

        foreach ( $attachments as $attachment_id ) {
            wp_delete_attachment( $attachment_id, true );
        }

        return true;
    }

    public function clean_external_image_data( int $post_id ) : void {
        delete_post_meta( $post_id, PreviewImageConstant::META_SLUG );
        delete_post_meta( $post_id, PreviewImageConstant::ATTACHMENT_ID );

        $this->delete_attachments_by_meta_value( PreviewImageConstant::POST_ID, $post_id );
    }
}
