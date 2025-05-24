<?php

namespace Hostinger\AiTheme\Builder;

defined( 'ABSPATH' ) || exit;

class Colors {
    /**
     * @var string
     */
    private string $description;

    /**
     * @var RequestClient
     */
    private RequestClient $request_client;
    public function __construct( string $description ) {
        $this->description = $description;
    }

    /**
     * @param RequestClient $request_client
     *
     * @return void
     */
    public function setRequestClient( RequestClient $request_client ): void {
        $this->request_client = $request_client;
    }

    public function generate_colors( ): bool {
        $params = [
            'description' => $this->description,
            'gradients' => [
                'z48lj' => 1
            ]
        ];

        $colors = $this->request_client->post( '/v3/wordpress/plugin/builder/colors-v2', $params );

        if(!empty($colors['color_palette'])) {
            update_option( 'hostinger_ai_version', uniqid(), true );
            update_option( 'hostinger_ai_colors', $colors, true );

            return true;
        }

        return false;
    }
}
