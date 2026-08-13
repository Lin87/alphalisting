<?php
/**
 * Terms Query class
 *
 * @package alphalisting
 */

declare(strict_types=1);

namespace eslin87\AlphaListing\Shortcode;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * TermsQuery
 */
class TermsQuery extends Query {
	/**
	 * The display/query type name.
	 *
	 * @var string
	 */
	public $display = 'terms';

	/**
	 * Execute this query extension.
	 *
	 * @param mixed $query      The query.
	 * @param array $attributes The complete set of shortcode attributes.
	 * @return mixed The query.
	 */
	public function apply_query_to_shortcode( $query, array $attributes ) {
		$query = wp_parse_args(
			(array) $query,
			array(
				'hide_empty' => 0,
				'taxonomy'   => 'category',
			)
		);

		return parent::apply_query_to_shortcode( $query, $attributes );
	}

	/**
	 * Get the items for the query.
	 *
	 * @since 4.0.0
	 * @param array $items The items.
	 * @param mixed $query The query.
	 * @return array<\WP_Term> The items.
	 */
	public function get_items( array $items, $query ): array {
		if ( is_array( $items ) && 0 < count( $items ) ) {
			return $items;
		}

		$terms = get_terms( $query ); // @phan-suppress-current-line PhanAccessMethodInternal

		// get_terms() returns array|int|\WP_Error. An unregistered taxonomy yields a
		// \WP_Error, which would violate this method's `array` return type under
		// strict_types and fatal the page instead of rendering an empty listing.
		if ( ! is_array( $terms ) ) {
			return array();
		}

		return $terms;
	}

	/**
	 * Get the item.
	 *
	 * @param mixed $previous The previous item object.
	 * @param mixed $item     The item object or ID.
	 * @return \WP_Term The item object.
	 */
	public function get_item( $previous, $item ) {
		if ( $previous instanceof \WP_Term ) {
			return $previous;
		}

		if ( $item instanceof \WP_Term ) {
			return $item;
		}

		return get_term( $item );
	}

	/**
	 * Get the item ID.
	 *
	 * @param int      $item_id The item ID.
	 * @param \WP_Term $item    The item object.
	 * @return int The item ID.
	 */
	public function get_item_id( int $item_id, $item ) {
		if ( ! $item instanceof \WP_Term ) {
			$item = get_term( $item );
		}

		if ( $item instanceof \WP_Term ) {
			$item_id = $item->term_id;
		}

		return $item_id;
	}

	/**
	 * Get the item title.
	 *
	 * @param string   $title The item title.
	 * @param \WP_Term $item  The item object.
	 * @return string The item title.
	 */
	public function get_item_title( string $title, $item ) {
		if ( ! $item instanceof \WP_Term ) {
			$item = get_term( $item );
		}

		if ( $item instanceof \WP_Term ) {
			$title = $item->name;
		}

		return $title;
	}

	/**
	 * Get the item permalink.
	 *
	 * @param string   $permalink The item permalink.
	 * @param \WP_Term $item      The item object.
	 * @return string The item permalink
	 */
	public function get_item_permalink( string $permalink, $item ) {
		if ( ! $item instanceof \WP_Term ) {
			$item = get_term( $item );
		}

		if ( $item instanceof \WP_Term ) {
			$link = get_term_link( $item );
			// get_term_link() returns string|\WP_Error. Storing a \WP_Error here would
			// only surface much later, as a return-type fatal in Query::get_the_permalink().
			if ( is_string( $link ) ) {
				$permalink = $link;
			}
		}

		return $permalink;
	}
}
