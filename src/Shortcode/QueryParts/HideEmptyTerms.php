<?php
/**
 * Hide Empty Terms Query Part.
 *
 * @package alphalisting
 */

declare(strict_types=1);

namespace eslin87\AlphaListing\Shortcode\QueryParts;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use \eslin87\AlphaListing\Shortcode\Extension;

/**
 * Hide Empty Terms Query Part extension
 */
class HideEmptyTerms extends Extension {
	/**
	 * The attribute for this Query Part.
	 *
	 * @since 4.0.0
	 * @var string
	 */
	public $attribute_name = 'hide-empty-terms';

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
		if ( ! isset( $query['hide_empty'] ) || empty( $query['hide_empty'] ) ) {
			$query['hide_empty'] = alphalisting_is_truthy( $value );
		}
		return $query;
	}
}
