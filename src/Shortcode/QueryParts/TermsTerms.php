<?php
/**
 * Taxonomy Terms for taxonomies Query Part.
 *
 * @package alphalisting
 */

declare(strict_types=1);

namespace eslin87\AlphaListing\Shortcode\QueryParts;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use \eslin87\AlphaListing\Strings;

/**
 * Taxonomy Terms Query Part extension for taxonomies
 */
class TermsTerms extends TermsCommon {
	/**
	 * The types of listing this shortcode extension may be used with.
	 *
	 * @since 4.0.0
	 * @var array<string>
	 */
	public $display_types = array( 'terms' );

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
		$taxonomies = isset( $attributes['taxonomy'] ) ? Strings::maybe_mb_split( ',', $attributes['taxonomy'] ) : array();

		$terms = $this->get_terms( $value, $taxonomies );

		$exclude_terms = $this->get_exclude_terms( $terms );
		$include_terms = $this->get_include_terms( $terms );
		$include_terms = array_diff( $include_terms, $exclude_terms );

		if ( ! empty( $include_terms ) ) {
			$query = wp_parse_args(
				$query,
				array(
					'include' => $include_terms,
				)
			);
		} else {
			$query = wp_parse_args(
				$query,
				array(
					'exclude' => $exclude_terms,
				)
			);
		}

		return $query;
	}
}
