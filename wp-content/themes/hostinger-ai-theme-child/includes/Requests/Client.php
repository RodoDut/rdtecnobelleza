<?php

namespace Hostinger\AiTheme\Requests;

defined( 'ABSPATH' ) || exit;

class Client {
    private string $api_url;
    private array $default_headers;

    public function __construct( $api_url, $default_headers = array() ) {
        $this->api_url         = $api_url;
        $this->default_headers = $default_headers;
    }

    public function get( $endpoint, $params = array(), $headers = array(), $timeout = 120 ) {
        $url          = $this->api_url . $endpoint;
        $request_args = array(
            'method'  => 'GET',
            'headers' => array_merge( $this->default_headers, $headers ),
            'timeout' => $timeout,
        );

        if ( ! empty( $params ) ) {
            $url = add_query_arg( $params, $url );
        }

        return wp_remote_get( $url, $request_args );
    }

    public function post( $endpoint, $params = array(), $headers = array(), $timeout = 120, $args = array() ) {
        $url          = $this->api_url . $endpoint;

        $default_args = array(
            'method'  => 'POST',
            'timeout' => $timeout,
            'headers' => array_merge( $this->default_headers, $headers ),
            'body'    => $params,
        );

        $request_args = array_merge( $default_args, $args );

        return wp_remote_post( $url, $request_args );
    }
}