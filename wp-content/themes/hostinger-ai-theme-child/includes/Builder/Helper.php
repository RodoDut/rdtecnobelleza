<?php

namespace Hostinger\AiTheme\Builder;

defined( 'ABSPATH' ) || exit;

class Helper {
    /**
     * @param array $structure
     * @param array $element_data
     *
     * @return false|mixed
     */
    public function find_structure( array $structure, array $element_data ): mixed
    {
        foreach($structure as $index => $data) {
            if($data['class'] == $element_data['class'] && $data['index'] == $element_data['index']) {
                return $data;
            }
        }

        return array();
    }

    /**
     * @param string $string
     * @param string $pattern
     *
     * @return array
     */
    public function extract_class_names( string $string, string $pattern ): array {
        preg_match_all( $pattern, $string, $matches );
        return $matches[0];
    }

    /**
     * @param string $string
     *
     * @return int
     */
    public function extract_index_number( string $string ): int
    {
        $pattern = '/hostinger-index-(\d+)/';

        if (preg_match($pattern, $string, $matches)) {
            return (int)$matches[1];
        }

        return 0;
    }
}
