<?php
declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
        define( 'ABSPATH', __DIR__ );
}

require_once __DIR__ . '/../src/Extension.php';
require_once __DIR__ . '/../src/Singleton.php';
require_once __DIR__ . '/../src/Strings.php';
require_once __DIR__ . '/../src/Shortcode/Extension.php';
require_once __DIR__ . '/../src/Shortcode/Query.php';
require_once __DIR__ . '/../src/Shortcode/TermsQuery.php';
require_once __DIR__ . '/../src/Shortcode/QueryParts/ExcludeTerms.php';

use eslin87\AlphaListing\Shortcode\QueryParts\ExcludeTerms;
use eslin87\AlphaListing\Shortcode\TermsQuery;

if ( ! class_exists( 'WP_Term' ) ) {
        class WP_Term {
                /** @var int */
                public $term_id;

                /** @var string */
                public $name;

                public function __construct( int $term_id, string $name ) {
                        $this->term_id = $term_id;
                        $this->name    = $name;
                }
        }
}

$all_terms = array(
        new WP_Term( 201, 'Manual Keep' ),
        new WP_Term( 202, 'Manual Drop' ),
        new WP_Term( 203, 'Manual Keep Too' ),
);

if ( ! function_exists( 'get_terms' ) ) {
        /**
         * Mimic WordPress get_terms by filtering out excluded IDs.
         *
         * @param array $query The query arguments.
         * @return array<int,WP_Term>
         */
        function get_terms( $query ) {
                global $all_terms;

                $exclude = array();
                if ( isset( $query['exclude'] ) ) {
                        $exclude = is_array( $query['exclude'] ) ? $query['exclude'] : array( $query['exclude'] );
                }
                $exclude = array_map( 'intval', $exclude );

                return array_values(
                        array_filter(
                                $all_terms,
                                static function ( WP_Term $term ) use ( $exclude ): bool {
                                        return ! in_array( $term->term_id, $exclude, true );
                                }
                        )
                );
        }
}

$attributes = array(
        'taxonomy'       => 'category',
        'exclude-terms'  => '202',
);

$query = array(
        'taxonomy' => 'category',
        'exclude'  => array( 999 ),
);

$extension = new ExcludeTerms();

$query = $extension->shortcode_query_for_display_and_attribute(
        $query,
        'terms',
        'exclude-terms',
        $attributes['exclude-terms'],
        $attributes
);

if ( empty( $query['exclude'] ) || ! in_array( 202, $query['exclude'], true ) ) {
        throw new RuntimeException( 'Expected the term ID to be added to the exclude list.' );
}

$terms_query = new TermsQuery();
$items       = $terms_query->get_items( array(), $query );
$ids         = array_map(
        static function ( WP_Term $term ): int {
                return $term->term_id;
        },
        $items
);

if ( in_array( 202, $ids, true ) ) {
        throw new RuntimeException( 'The excluded term ID should not appear in the TermsQuery results.' );
}

if ( count( $ids ) !== 2 ) {
        throw new RuntimeException( 'Expected only non-excluded terms to remain in the query results.' );
}

echo "TermsQuery respected exclude-terms when display=\"terms\".\n";
