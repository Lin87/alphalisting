<?php
/**
 * Group By Query Part.
 *
 * @package alphalisting
 */

declare(strict_types=1);

namespace eslin87\AlphaListing\Shortcode\QueryParts;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

use \eslin87\AlphaListing\Shortcode\Extension;
use \eslin87\AlphaListing\Strings;

/**
 * Group posts by a selected part of their title.
 */
class GroupBy extends Extension {
    /**
     * The attribute for this Query Part.
     *
     * @var string
     */
    public $attribute_name = 'group-by';

    /**
     * The listing types supported by this Query Part.
     *
     * @var array<string>
     */
    public $display_types = array( 'posts' );

    /**
     * Sanitize the grouping mode.
     *
     * @param mixed $value      The value of the shortcode attribute.
     * @param array $attributes The complete set of shortcode attributes.
     * @return string
     */
    public function sanitize_attribute( $value, array $attributes ): string {
        $value = is_string( $value ) ? strtolower( trim( $value ) ) : '';
        return 'last-word' === $value ? 'last-word' : '';
    }

    /**
     * Enable last-word grouping for post listings.
     *
     * @param mixed  $query      The query.
     * @param string $display    The display/query type.
     * @param string $key        The name of the attribute.
     * @param mixed  $value      The shortcode attribute value.
     * @param array  $attributes The complete set of shortcode attributes.
     * @return mixed
     */
    public function shortcode_query_for_display_and_attribute( $query, string $display, string $key, $value, array $attributes ) {
        if ( 'last-word' !== $value ) {
            return $query;
        }

        $this->add_hook( 'filter', 'alphalisting_item_index_letter', array( $this, 'index_by_last_word' ), 10, 3 );
        $this->add_hook( 'filter', 'alphalisting_item_sorting_comparator', array( $this, 'sort_by_last_word' ), 10, 3 );

        if ( is_array( $query ) ) {
            // Distinguish this result from the default grouping in external cache implementations.
            $query['_alphalisting_group_by'] = 'last-word';
        }

        return $query;
    }

    /**
     * Return the index letter from the last word of a post title.
     *
     * @param array  $letters Existing index letters.
     * @param mixed  $item    The post object or ID.
     * @param string $type    The listing type.
     * @return array
     */
    public function index_by_last_word( array $letters, $item, string $type ): array {
        if ( 'posts' !== $type ) {
            return $letters;
        }

        $post = $item instanceof \WP_Post ? $item : get_post( $item );
        if ( ! $post instanceof \WP_Post ) {
            return $letters;
        }

        $title = get_the_title( $post );
        $word  = self::get_last_word( (string) $title );

        if ( '' === $word ) {
            return $letters;
        }

        return array( Strings::maybe_mb_substr( $word, 0, 1 ) );
    }

    /**
     * Sort titles by their last word, then by their complete title.
     *
     * @param int    $default_sort The existing comparison result.
     * @param string $first_title  The first title.
     * @param string $second_title The second title.
     * @return int
     */
    public function sort_by_last_word( int $default_sort, string $first_title, string $second_title ): int {
        $first_word  = self::get_last_word( $first_title );
        $second_word = self::get_last_word( $second_title );

        if ( '' === $first_word || '' === $second_word ) {
            return $default_sort;
        }

        $comparison = strcasecmp( $first_word, $second_word );
        if ( 0 !== $comparison ) {
            return $comparison <=> 0;
        }

        return strcasecmp( $first_title, $second_title ) <=> 0;
    }

    /**
     * Extract the final word from a title.
     *
     * @param string $title The title to parse.
     * @return string
     */
    public static function get_last_word( string $title ): string {
        $title = trim( wp_strip_all_tags( $title ) );
        if ( '' === $title ) {
            return '';
        }

        $words = preg_split( '/\s+/u', $title );
        if ( ! is_array( $words ) || empty( $words ) ) {
            return '';
        }

        $word = (string) end( $words );
        $word = preg_replace( '/^[\p{P}\p{S}]+|[\p{P}\p{S}]+$/u', '', $word );

        return is_string( $word ) ? $word : '';
    }
}
