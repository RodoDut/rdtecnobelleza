<?php

namespace Hostinger\AiTheme\Rest;

/**
 * Avoid possibility to get file accessed directly
 */
if ( ! defined( 'ABSPATH' ) ) {
    die;
}

/**
 * Class for handling Rest Api Routes
 */
class Routes {
    /**
     * @var Settings
     */
    private BuilderRoutes $builder_routes;

    /**
     * @param BuilderRoutes $builder_routes
     */
    public function __construct( BuilderRoutes $builder_routes ) {
        $this->builder_routes = $builder_routes;
    }

    /**
     * Init rest routes
     *
     * @return void
     */
    public function init(): void {
        add_action( 'rest_api_init', array( $this, 'register_routes' ) );
    }

    /**
     * @return void
     */
    public function register_routes() {
        // Register Builder Rest API Routes.
        $this->register_build_routes();
    }

    /**
     *
     * @return void
     */
    private function register_build_routes(): void {
        // Generate colors.
        register_rest_route(
            HOSTINGER_AI_WEBSITES_REST_API_BASE,
            'generate-colors',
            array(
                'methods'             => 'GET',
                'callback'            => array( $this->builder_routes, 'generate_colors' ),
                'permission_callback' => array( $this, 'permission_check' ),
            )
        );

        // Generate structure.
        register_rest_route(
            HOSTINGER_AI_WEBSITES_REST_API_BASE,
            'generate-structure',
            array(
                'methods'             => 'GET',
                'callback'            => array( $this->builder_routes, 'generate_structure' ),
                'permission_callback' => array( $this, 'permission_check' ),
            )
        );

        // Generate content.
        register_rest_route(
            HOSTINGER_AI_WEBSITES_REST_API_BASE,
            'generate-content',
            array(
                'methods'             => 'GET',
                'callback'            => array( $this->builder_routes, 'generate_content' ),
                'permission_callback' => array( $this, 'permission_check' ),
            )
        );

        // Build content.
        register_rest_route(
            HOSTINGER_AI_WEBSITES_REST_API_BASE,
            'build-content',
            array(
                'methods'             => 'GET',
                'callback'            => array( $this->builder_routes, 'build_content' ),
                'permission_callback' => array( $this, 'permission_check' ),
            )
        );
    }

    /**
     * @param WP_REST_Request $request WordPress rest request.
     *
     * @return bool
     */
    public function permission_check( $request ): bool {
        // Workaround if Rest Api endpoint cache is enabled.
        // We don't want to cache these requests.
        if( has_action('litespeed_control_set_nocache') ) {
            do_action(
                'litespeed_control_set_nocache',
                'Custom Rest API endpoint, not cacheable.'
            );
        }

        if ( empty( is_user_logged_in() ) ) {
            return false;
        }

        // Implement custom capabilities when needed.
        return current_user_can( 'manage_options' );
    }
}