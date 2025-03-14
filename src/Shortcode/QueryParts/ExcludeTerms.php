<?php
/**
 * Exclude Terms Query Part.
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
 * Exclude Terms Query Part extension
 */
class ExcludeTerms extends Extension {
	/**
	 * The attribute for this Query Part.
	 *
	 * @since 4.0.0
	 * @var string
	 */
	public $attribute_name = 'exclude-terms';

	/**
	 * The types of listing this shortcode extension may be used with.
	 *
	 * @since 4.0.0
	 * @var array<string>
	 */
	public $display_types = array( 'posts' );

	/**
	 * Update the query with this extension's additional configuration.
	 *
	 * @param \AlphaListing\Query $query      The query.
	 * @param string             $display    The display/query type.
	 * @param string             $key        The name of the attribute.
	 * @param mixed              $value      The shortcode attribute value.
	 * @param array              $attributes The complete set of shortcode attributes.
	 * @return mixed The updated query.
	 */
	public function shortcode_query_for_display_and_attribute( $query, string $display, string $key, $value, array $attributes ) {
		$exclude_terms = Strings::maybe_mb_split( ',', $value );
		$exclude_terms = array_map( 'trim', $exclude_terms );
		$exclude_terms = array_map( 'intval', $exclude_terms );
		$exclude_terms = array_filter(
			$exclude_terms,
			function( int $value ): bool {
				return 0 < $value;
			}
		);
		$exclude_terms = array_unique( $exclude_terms );

		if ( empty( $exclude_terms ) ) {
			return $query;
		}

		$taxonomy = isset( $attributes['taxonomy'] ) ? $attributes['taxonomy'] : 'category';

		$tax_query = array(
			array(
				'taxonomy' => $taxonomy,
				'field'    => 'slug',
				'terms'    => $exclude_terms,
				'operator' => 'NOT IN',
			),
		);

		if ( isset( $query['tax_query'] ) ) {
			$query['tax_query'] = wp_parse_args( $query['tax_query'], $tax_query ); // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
		} else {
			$query['tax_query'] = $tax_query; // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
		}
		return $query;
	}
}
