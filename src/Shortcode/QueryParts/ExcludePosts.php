<?php
/**
 * Exclude Posts Query Part.
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
 * Exclude Posts Query Part extension
 */
class ExcludePosts extends Extension {
	/**
	 * The attribute for this Query Part.
	 *
	 * @since 4.0.0
	 * @var string
	 */
	public $attribute_name = 'exclude-posts';

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
		$exclude_posts = Strings::maybe_mb_split( ',', $value );
		$exclude_posts = array_map( 'trim', $exclude_posts );
		$exclude_posts = array_map( 'intval', $exclude_posts );
		$exclude_posts = array_filter(
			$exclude_posts,
			function( int $value ): bool {
				return 0 < $value;
			}
		);
		$exclude_posts = array_unique( $exclude_posts );

		if ( ! empty( $exclude_posts ) ) {
			$query = wp_parse_args( $query, array( 'post__not_in' => $exclude_posts ) );
		}
		return $query;
	}
}
