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

use \AlphaListing\Shortcode\Extension;

/**
 * Parent Term Common implementation.
 */
abstract class ParentTermCommon extends Extension {
	/**
	 * The types of listing this shortcode extension may be used with.
	 *
	 * @since 4.0.0
	 * @var array<string|int>
	 */
	public $display_types = array( 'terms' );

	/**
	 * Update the query with this extension's additional configuration.
	 *
	 * @param \AlphaListing\Query $query      The query.
	 * @param int                $parent_id  The shortcode attribute value.
	 * @param array              $attributes The complete set of shortcode attributes.
	 * @return mixed The updated query.
	 */
	public function shortcode_query_with_parent_id( $query, int $parent_id, array $attributes ) {
		if ( isset( $attributes['get-all-children'] ) && alphalisting_is_truthy( $attributes['get-all-children'] ) ) {
			$parent_selector = 'child_of';
		} else {
			$parent_selector = 'parent';
		}

		if ( -1 < $parent_id ) {
			$query = wp_parse_args(
				$query,
				array( $parent_selector => $parent_id )
			);
		}

		return $query;
	}
}



