<?php

namespace Hostinger\AiTheme\Builder;

use Hostinger\AiTheme\Requests\Client;

defined( 'ABSPATH' ) || exit;

class RequestClient {
    /**
     * @var Client
     */
    private Client $client;

    /**
     * @param Client $client
     */
    public function __construct( Client $client ) {
        $this->client         = $client;
    }

    /**
     * @param string $endpoint
     * @param array  $params
     * @param array  $headers
     * @param int    $timeout
     * @param array  $args
     *
     * @return array
     */
    public function post( string $endpoint, array $params = array(), array $headers = array(), int $timeout = 120, array $args = array( 'data_format' => 'body' ) ): array
    {
        $response = $this->client->post( $endpoint, json_encode( $params ), $headers, $timeout, $args );

        if ( is_wp_error( $response ) ) {
            error_log('Something went wrong querying post ' . $endpoint . ' ' . $response->get_error_message() );
        }

        $decoded_response = $this->decode_response($response);

        $response_data    = $decoded_response['response_data']['data'] ?? null;

        if ( empty( $response_data ) ) {
            error_log('Response data: ');
            error_log(print_r($decoded_response, true));
            error_log(print_r($endpoint, true));
            error_log(print_r(json_encode( $params ), true));
            error_log(print_r($params, true));
            return array();
        }

        return $response_data;
    }

    /**
     * @param array|WP_Error $response
     *
     * @return array
     */
    public function decode_response( array|WP_Error $response ): array
    {
        $response_body = wp_remote_retrieve_body( $response );
        $response_code = wp_remote_retrieve_response_code( $response );
        $response_data = json_decode( $response_body, true);

        if ( !is_array( $response_data ) ) {
            $response_data = [ 'data' => null ];
        }

        return [
            'response_code' => $response_code,
            'response_data' => $response_data,
            'response_body' => $response_body,
        ];
    }
}
