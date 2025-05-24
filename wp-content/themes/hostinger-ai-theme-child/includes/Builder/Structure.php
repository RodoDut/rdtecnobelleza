<?php

namespace Hostinger\AiTheme\Builder;

defined( 'ABSPATH' ) || exit;

class Structure {
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
     * @var RequestClient
     */
    private RequestClient $request_client;
    public function __construct( string $brand_name, string $website_type, string $description ) {
        $this->brand_name = $brand_name;
        $this->website_type = $website_type;
        $this->description = $description;
    }

    /**
     * @param RequestClient $request_client
     *
     * @return void
     */
    public function setRequestClient( RequestClient $request_client ) {
        $this->request_client = $request_client;
    }

    /**
     * @return array
     */
    public function generate_structure( ): array
    {
        $params = [
            'brand_name' => $this->brand_name,
            'website_type' => $this->website_type,
            'description' => $this->description,
            'language' => $this->get_site_locale(),
            'sections' => [
                'hero' => 'Title, subtitle and cta buttons',
                'about-us' => 'Title, subtitle, image',
                'services' => 'Title, subtitle, cards about services',
                'contact' => 'Title, subtitle, contact information, form',
                'location' => 'Title, subtitle, address, map',
                'projects' => 'Title, subtitle, project cards',
                'customer-reviews' => 'Title, subtitle, single customer review',
                'call-to-action' => 'Title, description, cta and illustration',
                'my-background' => 'My Background section used for personal or portfolio sites, showing details about education, work, skills, and achievements.',
                'gallery' => 'Gallery section displays images.',
                'blog-posts' => 'Contains the content of the blog post.',
            ],
            'pages' => [
                ''
            ]
        ];

        $structure = $this->request_client->post( '/v3/wordpress/plugin/builder/structure', $params );

        return $this->generate_unique_identifiers( $structure );
    }

    /**
     * @param array $page_data
     *
     * @return array
     */
    public function generate_content( array $page_data ): array
    {
        $params = [
            'brand_name' => $this->brand_name,
            'website_type' => $this->website_type,
            'description' => $this->description,
            'image_with_prompt' => false,
            'language' => $this->get_site_locale(),
            'pages' => $this->format_page_data( $page_data )
        ];

        return $this->request_client->post( '/v3/wordpress/plugin/builder/ai-builder-v3', $params );
    }

    /**
     * @param $structure
     *
     * @return array
     * @throws \Exception
     */
    public function generate_builder_data( $structure ): array
    {
        foreach ($structure as &$data) {
            foreach ($data['sections'] as &$section_data) {
                $section_builder = new SectionBuilder($section_data['section']);
                $section_builder->setHelper(new Helper());

                $generate = $section_builder->generate();

                if ( ! empty($generate)) {
                    $section_data['content'] = $section_builder->get_block_content();

                    $section_data['structure'] = $section_builder->get_block_used_elements();
                }
            }
        }

        return $structure;
    }

    /**
     * @param array $structure
     * @param array $content
     *
     * @return array
     */
    public function merge_content( array $structure, array $content ): array {
        foreach( $content['pages'] as $page => &$content_data ) {
            foreach($content_data['sections'] as $section_index => &$section_data) {
                $section_data['html'] = $this->find_section_content( $page, $structure, $section_index );
            }
        }

        return $content;
    }

    /**
     * @param string $page
     * @param array  $structure
     * @param string $section_index
     *
     * @return string
     */
    private function find_section_content( string $page, array $structure, string $section_index ): string {

        foreach($structure as $structure_data) {
            if($structure_data['page'] == $page) {
                foreach($structure_data['sections'] as $section_data) {
                    if($section_data['id'] == $section_index) {
                        return $section_data['content'];
                    }
                }
            }
        }

        return '';
    }

    /**
     * @param array $page_data
     *
     * @return array
     */
    private function format_page_data( array $page_data ): array {
        $formatted_data = [];

        foreach( $page_data as $data ) {
            $sections = array();

            foreach( $data['sections'] as $section_data ) {

                if(empty($section_data['structure'])) {
                    continue;
                }

                $sections[ $section_data['id'] ] = array(
                    'type' => $section_data['section'],
                    'elements' => $section_data['structure']
                );
            }

            $formatted_data[ $data['page'] ] = array(
                'sections' => $sections
            );
        }

        return $formatted_data;
    }

    /**
     * @param array $structure
     *
     * @return array
     */
    private function generate_unique_identifiers( array $structure ): array {
        $result = [];

        foreach($structure as $page => $sections) {
            $page = [
                'page' => $page,
                'sections' => []
            ];

            foreach($sections as $section) {
                $page['sections'][] = [
                    'id' => uniqid(),
                    'section' => $section
                ];
            }

            $result[] = $page;
        }

        return $result;
    }

    /**
     * @return string
     */
    private function get_site_locale(): string {
        $language_code = 'en';
        $locale = get_locale();

        if ( !empty( $locale ) ) {
            $language_code = substr($locale, 0, 2);
        }

        return $language_code;
    }
}
