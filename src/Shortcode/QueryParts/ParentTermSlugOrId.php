<?php
/**
 * Parent Term Query Part.
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
 * Parent Term Slug Or ID implementation.
 */
class ParentTermSlugOrId extends ParentTermCommon {
	/**
	 * The attribute for this Query Part.
	 *
	 * @since 4.0.0
	 * @var string
	 */
	public $attribute_name = 'parent-term';

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
		if ( is_numeric( $value ) ) {
			$parent_id = intval( $value );
		} else {
			$parent_id  = -1;
			$taxonomies = array( 'category' );
			if ( isset( $attributes['taxonomy'] ) ) {
				$taxonomies = Strings::maybe_mb_split( ',', $attributes['taxonomy'] );
			}

			foreach ( $taxonomies as $taxonomy ) {
				// get_term_by() returns \WP_Term|false|null -- null for an unregistered
				// taxonomy, which slips past a `false !==` check and leaves $parent_id
				// null, fatalling on the int parameter of shortcode_query_with_parent_id().
				$parent_term = get_term_by( 'slug', $value, $taxonomy );
				if ( $parent_term instanceof \WP_Term ) {
					$parent_id = $parent_term->term_id;
					break;
				}
			}
		}

		return $this->shortcode_query_with_parent_id( $query, $parent_id, $attributes );
	}
}
