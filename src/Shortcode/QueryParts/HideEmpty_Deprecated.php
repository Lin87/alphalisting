<?php
/**
 * Deprecated Hide Empty Terms Query Part.
 *
 * @package alphalisting
 */

declare(strict_types=1);

namespace eslin87\AlphaListing\Shortcode\QueryParts;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Deprecated Hide Empty Terms Query Part extension.
 *
 * Handles the legacy `hide-empty` attribute. The behavior is identical to
 * `hide-empty-terms`, so only the attribute name differs.
 *
 * @since 4.0.0 deprecated in favor of the `hide-empty-terms` attribute.
 */
class HideEmpty_Deprecated extends HideEmptyTerms {
	/**
	 * The attribute for this Query Part.
	 *
	 * @since 4.0.0
	 * @var string
	 */
	public $attribute_name = 'hide-empty';

	/**
	 * Update the query with this extension's additional configuration.
	 *
	 * @param \eslin87\AlphaListing\Query $query      The query.
	 * @param string                      $display    The display/query type.
	 * @param string                      $key        The name of the attribute.
	 * @param mixed                       $value      The shortcode attribute value.
	 * @param array                       $attributes The complete set of shortcode attributes.
	 * @return mixed The updated query.
	 */
	public function shortcode_query_for_display_and_attribute( $query, string $display, string $key, $value, array $attributes ) {
		_deprecated_argument( 'hide-empty', '4.0.0', 'Use the hide-empty-terms attribute instead.' );

		return parent::shortcode_query_for_display_and_attribute( $query, $display, $key, $value, $attributes );
	}
}
