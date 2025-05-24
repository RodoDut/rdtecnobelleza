<?php
/**
 * Builder Rest API
 *
 */

namespace Hostinger\AiTheme\Rest;

use Hostinger\AiTheme\Builder\Colors;
use Hostinger\AiTheme\Builder\WebsiteBuilder;
use Exception;

/**
 * Avoid possibility to get file accessed directly
 */
if ( ! defined( 'ABSPATH' ) ) {
    die;
}

/**
 * Class for handling Settings Rest API
 */
class BuilderRoutes {
    /**
     * @var WebsiteBuilder
     */
    private WebsiteBuilder $website_builder;

    /**
     * @param WebsiteBuilder $website_builder
     */
    public function __construct( WebsiteBuilder $website_builder ) {
        $this->website_builder = $website_builder;
    }

    /**
     * @param \WP_REST_Request $request
     *
     * @return \WP_REST_Response|\WP_Error
     */
    public function generate_colors( \WP_REST_Request $request ): \WP_REST_Response|\WP_Error {
        $parameters = $request->get_params();

        $fields = array(
            'brand_name',
            'website_type',
            'description'
        );

        $errors = array();

        foreach ( $fields as $field_key ) {
            if ( empty( $parameters[ $field_key ] ) ) {
                $errors[ $field_key ] = $field_key . ' is missing.';
            } else {
                $parameters[ $field_key ] = sanitize_text_field( $parameters[ $field_key ] );
            }
        }

        if ( ! empty( $errors ) ) {
            return new \WP_Error(
                'data_invalid',
                __( 'Sorry, something wrong with data.', 'hostinger-ai-theme' ),
                array(
                    'status' => \WP_Http::BAD_REQUEST,
                    'errors' => $errors,
                )
            );
        }

        $this->website_builder->clear_ai_content();
        $this->website_builder->clear_ai_data();

        if ( $parameters['website_type'] === 'affiliate-marketing' ) {
            $parameters['website_type'] = 'blog';
            update_option('hostinger_ai_affiliate', true );
        }

        // Purge LiteSpeed cache.
        if ( has_action( 'litespeed_purge_all' ) ) {
            do_action( 'litespeed_purge_all' );
        }

        update_option('blogname', $parameters['brand_name']);

        update_option('hostinger_ai_brand_name', $parameters['brand_name'] );
        update_option('hostinger_ai_website_type', $parameters['website_type'] );
        update_option('hostinger_ai_description', $parameters['description'] );

        $data = array(
            'colors_generated' => $this->website_builder->generate_colors( $parameters['description'] )
        );

        $response = new \WP_REST_Response( $data );

        $response->set_headers(array('Cache-Control' => 'no-cache'));

        $response->set_status( \WP_Http::OK );

        return $response;
    }

    /**
     * @param \WP_REST_Request $request
     *
     * @return \WP_REST_Response|\WP_Error
     */
    public function generate_structure( \WP_REST_Request $request ): \WP_REST_Response|\WP_Error {
        $colors = get_option( 'hostinger_ai_colors', false );

        if ( empty( $colors ) ) {
            return new \WP_Error(
                'data_invalid',
                __( 'Wrong sequence of step execution.', 'hostinger-ai-theme' ),
                array(
                    'status' => \WP_Http::BAD_REQUEST,
                )
            );
        }

        $brand_name = get_option('hostinger_ai_brand_name' );
        $website_type = get_option('hostinger_ai_website_type' );
        $description = get_option('hostinger_ai_description' );

        $data = array(
            'structure_generated' => $this->website_builder->generate_structure( $brand_name, $website_type, $description )
        );

        $response = new \WP_REST_Response( $data );

        $response->set_headers(array('Cache-Control' => 'no-cache'));

        $response->set_status( \WP_Http::OK );

        return $response;
    }

    /**
     * @param \WP_REST_Request $request
     *
     * @return \WP_REST_Response|\WP_Error
     */
    public function generate_content( \WP_REST_Request $request ): \WP_REST_Response|\WP_Error {
        $website_structure = get_option( 'hostinger_ai_website_structure', false );

        if ( empty( $website_structure ) ) {
            return new \WP_Error(
                'data_invalid',
                __( 'Wrong sequence of step execution.', 'hostinger-ai-theme' ),
                array(
                    'status' => \WP_Http::BAD_REQUEST,
                )
            );
        }

        $brand_name = get_option('hostinger_ai_brand_name' );
        $website_type = get_option('hostinger_ai_website_type' );
        $description = get_option('hostinger_ai_description' );

        try {

            $data = array(
                'content_generated' => $this->website_builder->generate_content( $brand_name, $website_type, $description )
            );

        } catch (Exception $e) {
            return new \WP_Error(
                'data_invalid',
                __( 'Problem generating content.', 'hostinger-ai-theme' ),
                array(
                    'status' => \WP_Http::BAD_REQUEST,
                    'error' => $e->getMessage()
                )
            );
        }

        $response = new \WP_REST_Response( $data );

        $response->set_headers(array('Cache-Control' => 'no-cache'));

        $response->set_status( \WP_Http::OK );

        return $response;
    }

    /**
     * @param \WP_REST_Request $request
     *
     * @return \WP_REST_Response|\WP_Error
     */
    public function build_content( \WP_REST_Request $request ): \WP_REST_Response|\WP_Error {
        $website_content = get_option( 'hostinger_ai_website_content', false );

        if ( empty( $website_content ) ) {
            return new \WP_Error(
                'data_invalid',
                __( 'Wrong sequence of step execution.', 'hostinger-ai-theme' ),
                array(
                    'status' => \WP_Http::BAD_REQUEST,
                )
            );
        }

        try {

            $data = array(
                'content_built' => $this->website_builder->build_content( $website_content )
            );

        } catch (Exception $e) {
            return new \WP_Error(
                'data_invalid',
                __( 'Problem building content.', 'hostinger-ai-theme' ),
                array(
                    'status' => \WP_Http::BAD_REQUEST,
                    'error' => $e->getMessage()
                )
            );
        }

        // Purge LiteSpeed cache.
        if ( has_action( 'litespeed_purge_all' ) ) {
            do_action( 'litespeed_purge_all' );
        }

        $response = new \WP_REST_Response( $data );

        $response->set_headers(array('Cache-Control' => 'no-cache'));

        $response->set_status( \WP_Http::OK );

        return $response;
    }
}
