<?php
/**
 * Parent Term Query Part.
 *
 * @package alphalisting
 */

declare(strict_types=1);

namespace AlphaListing\Shortcode\QueryParts;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Parent Term ID implementation.
 */
class ParentTermId extends ParentTermCommon {
	/**
	 * The attribute for this Query Part.
	 *
	 * @since 4.0.0
	 * @var string
	 */
	public $attribute_name = 'parent-term-id';

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
		if ( is_numeric( $value ) ) {
			$parent_id = intval( $value );
		} else {
			$parent_id = -1;
		}

		return $this->shortcode_query_with_parent_id( $query, $parent_id, $attributes );
	}
}
