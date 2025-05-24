<?php

namespace Hostinger\AiTheme\Builder;

use Exception;
use Hostinger\AiTheme\Constants\PreviewImageConstant;

defined( 'ABSPATH' ) || exit;

class WebsiteBuilder {
    /**
     * @var RequestClient
     */
    private RequestClient $request_client;

    private ImageManager $image_manager;

    private AffiliateBuilder $affiliate_builder;

    public function __construct( RequestClient $request_client, ImageManager $image_manager, AffiliateBuilder $affiliate_builder ) {
        $this->request_client = $request_client;
        $this->image_manager = $image_manager;
        $this->affiliate_builder = $affiliate_builder;
    }

    public function init() {
        add_filter( 'wp_theme_json_data_theme', [ $this, 'update_new_colors' ], 999 );
    }

    public function update_new_colors( $theme_json ) {

        $hostinger_ai_version = get_option( 'hostinger_ai_version', false );

        if(empty($hostinger_ai_version)) {
            return $theme_json;
        }

        $hostinger_ai_colors = get_option( 'hostinger_ai_colors', false );

        $gradient_key = array_key_first($hostinger_ai_colors['color_palette']['gradients']);

        $new_data = array(
            'version'  => $hostinger_ai_version,
            'settings' => array(
                'color' => array(
                    'palette' => [
                        [
                            'slug' => 'color1',
                            'color' => !empty($hostinger_ai_colors['color_palette']['color1']) ? $hostinger_ai_colors['color_palette']['color1'] : '',
                            'name' => 'Color 1 (Section backgrounds)'
                        ],
                        [
                            'slug' => 'color2',
                            'color' => !empty($hostinger_ai_colors['color_palette']['color2']) ? $hostinger_ai_colors['color_palette']['color2'] : '',
                            'name' => 'Color 2 (Section backgrounds)'
                        ],
                        [
                            'slug' => 'color3',
                            'color' => !empty($hostinger_ai_colors['color_palette']['color3']) ? $hostinger_ai_colors['color_palette']['color3'] : '',
                            'name' => 'Color 3 (Button background)'
                        ],
                        [
                            'slug' => 'light',
                            'color' => !empty($hostinger_ai_colors['color_palette']['light']) ? $hostinger_ai_colors['color_palette']['light'] : '',
                            'name' => 'Light (Text on Color 2 and Gradient)'
                        ],
                        [
                            'slug' => 'dark',
                            'color' => !empty($hostinger_ai_colors['color_palette']['dark']) ? $hostinger_ai_colors['color_palette']['dark'] : '',
                            'name' => 'Dark (Text on Light and Color 1)'
                        ],
                        [
                            'slug' => 'grey',
                            'color' => !empty($hostinger_ai_colors['color_palette']['grey']) ? $hostinger_ai_colors['color_palette']['grey'] : '',
                            'name' => 'Grey (Form borders)'
                        ]
                    ],
                    'gradients' => [
                        [
                            'slug' => 'gradient-one',
                            'gradient' => 'linear-gradient(135deg, '.(!empty($hostinger_ai_colors['color_palette']['color3']) ? $hostinger_ai_colors['color_palette']['color3'] : '').' 0%, '.(!empty($hostinger_ai_colors['color_palette']['gradients']) ? $hostinger_ai_colors['color_palette']['gradients'][$gradient_key]['gradient'] : '').' 100%)',
                            'name' => 'Section background gradient'
                        ]
                    ]
                ),
            ),
        );

        return $theme_json->update_with( $new_data );

    }

    /**
     * @param string $description
     *
     * @return bool
     */
    public function generate_colors( string $description ): bool {
        $colors = new Colors( $description );
        $colors->setRequestClient( $this->request_client );

        return $colors->generate_colors();
    }

    /**
     * @param string $brand_name
     * @param string $website_type
     * @param string $description
     *
     * @return bool
     */
    public function generate_structure( string $brand_name, string $website_type, string $description ): bool {
        $structure = new Structure( $brand_name, $website_type, $description );
        $structure->setRequestClient( $this->request_client );

        $website_structure = $structure->generate_structure();

        if ( empty( $website_structure ) ) {
            return false;
        }

        $this->mark_blog_section($website_structure);

        update_option( 'hostinger_ai_website_structure', $website_structure );

        return true;
    }

    /**
     * @throws Exception
     */
    public function generate_content( string $brand_name, string $website_type, string $description ): bool {
        $structure = new Structure( $brand_name, $website_type, $description );
        $structure->setRequestClient( $this->request_client );

        $website_structure = get_option( 'hostinger_ai_website_structure' );

        $page_data = $structure->generate_builder_data( $website_structure );

        $content = $structure->generate_content( $page_data );

        if(empty($content)) {
            throw new Exception('There was an error generating a content');
        }

        $content = $structure->merge_content( $page_data, $content );

        update_option( 'hostinger_ai_website_content', $content );

        $blog_content_needed = get_option( 'hostinger_ai_blog_needed', false );

        if(!empty($blog_content_needed)) {
            $this->affiliate_builder->boot();

            $blog_builder = new BlogBuilder( $brand_name, $website_type, $description );
            $blog_builder->generate_blog();
        }

        return true;
    }

    /**
     * @param array $content
     *
     * @return bool
     */
    public function build_content( array $content ): bool {
        $page_builder = new PageBuilder( $content );

        $pages = $page_builder->build_pages();

        $home_key = array_key_first( $pages );

        if(!empty($pages[$home_key])) {
            update_option('show_on_front', 'page');
            update_option('page_on_front', $pages[$home_key]['page_id']);
        }

        $navigation_builder = new NavigationBuilder( $pages );

        $navigation_builder->updateMenus();

        return true;
    }

    /**
     * @return void
     */
    public function clear_ai_data(): void {
        // Prompt.
        delete_option('hostinger_ai_brand_name');
        delete_option('hostinger_ai_website_type');
        delete_option('hostinger_ai_description');

        // Affiliate flag.
        delete_option('hostinger_ai_affiliate');

        // Colors.
        delete_option('hostinger_ai_version');
        delete_option('hostinger_ai_colors');

        // Images.
        delete_option('hostinger_ai_used_images');

        // Structure
        delete_option('hostinger_ai_website_structure');

        // Content
        delete_option('hostinger_ai_website_content');
    }

    /**
     * @return bool
     */
    public function clear_ai_content(): bool {
        $pages = get_option('hostinger_ai_created_pages', array());

        if ( empty( $pages ) ) {
            return false;
        }

        foreach ( $pages as $page ) {
            wp_delete_post( $page['page_id'], true );
        }

        delete_option('hostinger_ai_created_pages');

        // Blog flag
        delete_option('hostinger_ai_blog_needed');

        // Blog posts
        $created_blog_posts = get_option( 'hostinger_ai_created_blog_posts', array() );

        if ( empty( $created_blog_posts ) ) {
            return true;
        }

        foreach($created_blog_posts as $post_id) {
            wp_delete_post( $post_id, true );

            $this->image_manager->delete_attachments_by_meta_value( PreviewImageConstant::POST_ID, $post_id );
        }

        delete_option( 'hostinger_ai_created_blog_posts' );

        return true;
    }

    public function mark_blog_section( array $structure ): void {
        if (!empty($structure)) {
            foreach ($structure as $page_data) {
                if (!empty($page_data['sections'])) {
                    $sections = array_column($page_data['sections'], 'section');
                    if (array_search('blog-posts', $sections) !== false) {
                        update_option('hostinger_ai_blog_needed', 1);
                        break;
                    }
                }
            }
        }
    }
}
