<?php

namespace Hostinger\AiTheme\Builder;

use Hostinger\AiTheme\Admin\Hooks;
use Hostinger\AiTheme\Builder\Seo;
use Hostinger\AiTheme\Constants\PreviewImageConstant;
use Hostinger\WpHelper\Config;
use Hostinger\AiTheme\Requests\Client;
use Hostinger\WpHelper\Utils as Helper;
use stdClass;

defined( 'ABSPATH' ) || exit;

class BlogBuilder {
    /**
     * @var string
     */
    private string $brand_name;
    /**
     * @var string
     */
    private string $website_type;
    /**
     * @var string
     */
    private string $description;

    /**
     * @var Client
     */
    private Client $client;

    /**
     * @var ImageManager
     */
    private ImageManager $image_manager;

    private AffiliateBuilder $affiliate_builder;

    public function __construct( string $brand_name, string $website_type, string $description ) {
        $helper         = new Helper();
        $config_handler = new Config();

        $this->client = new Client( $config_handler->getConfigValue( 'base_rest_uri', HOSTINGER_AI_WEBSITES_REST_URI ), [
            Config::TOKEN_HEADER  => $helper::getApiToken(),
            Config::DOMAIN_HEADER => $helper->getHostInfo(),
            'Content-Type' => 'application/json'
        ] );
        $this->image_manager = new ImageManager();

        $this->brand_name = $brand_name;
        $this->website_type = $website_type;
        $this->description = $description;
        $this->affiliate_builder = new AffiliateBuilder();
    }

    public function generate_blog(): void {
        $blog_posts  = [];
        $used_titles = [];

        for ( $i = 0; $i < 2; $i++ ) {
            $blog_post = $this->generate_post( $used_titles );

            if ( ! empty( $blog_post->title ) && ! in_array( $blog_post->title, $used_titles, true ) ) {
                $used_titles[] = $blog_post->title;
                $blog_posts[]  = $blog_post;
            }
        }

        if ( $blog_posts ) {
            $created_blog_posts = array_map( [ $this, 'create_blog_post' ], $blog_posts );
            update_option( 'hostinger_ai_created_blog_posts', $created_blog_posts );
        }
    }

    public function generate_post( array $used_titles ): stdClass {
        $post_type   = 'blog_post';
        $length      = '150-300';
        $voice_tone  = 'neutral';

        $data = [
            'post_type'   => $post_type,
            'tone'        => $voice_tone,
            'length'      => $length,
            'description' => $this->description,
            'used_titles' => $used_titles,
        ];

        $response = $this->client->get( ImageManager::GENERATE_CONTENT_ACTION, $data );

        $response_code = wp_remote_retrieve_response_code( $response );

        if ( is_wp_error( $response ) || $response_code !== 200 ) {
            return new StdClass();
        }

        $generated_content = reset( json_decode( $response['body'] )->data );

        if ( isset( $generated_content->tags[0] ) && $generated_content->tags[0] !== '' ) {
            $this->image_manager->set_keyword( $generated_content->tags[0] );
            $image_data = $this->image_manager->get_unsplash_image_data( ! empty( $element_structure['default_content'] ) );

            if ( ! empty( get_object_vars( $image_data ) ) ) {
                $generated_content->image_data = $image_data;
            }

            if ( isset( $generated_content->content ) ) {
                $generated_content->content .= $this->affiliate_builder->generate_shortcode($generated_content->tags[0]);
            }
        }

        return $generated_content;
    }

    public function create_blog_post(StdClass $post): int {
        $post_status  = 'publish';
        $content = $post->content;
        $seo = new Seo();

        $post_data = array(
            'post_title'    => $post->title,
            'post_content'  => $content,
            'post_status'   => $post_status,
            'post_type'     => 'post'
        );

        $post_id = wp_insert_post( $post_data );

        if ( ! empty( get_object_vars( $post->image_data ) ) ) {
            update_post_meta( $post_id, PreviewImageConstant::META_SLUG, $post->image_data->image );

            $this->image_manager->create_blog_image_attachment( $post_id );
        }

        update_post_meta( $post_id, 'hostinger_ai_post_meta_title', $post->title );

        if ( ! empty( $post->meta_description ) ) {
            update_post_meta( $post_id, 'hostinger_ai_post_meta_description', $post->meta_description );
        }

        if ( ! empty( $post->seo_keywords ) ) {
            update_post_meta( $post_id, 'hostinger_ai_post_meta_seo_keywords', $post->seo_keywords );
        }

        $seo->add_seo_meta_tags( $post_id );

        return $post_id;
    }
}
