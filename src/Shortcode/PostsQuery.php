<?php
/**
 * Posts Query class
 *
 * @package alphalisting
 */

declare(strict_types=1);

namespace eslin87\AlphaListing\Shortcode;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * PostsQuery
 */
class PostsQuery extends Query {
	/**
	 * The display/query type name.
	 *
	 * @var string
	 */
	public $display = 'posts';

	/**
	 * Get the items for the query.
	 *
	 * @since 4.0.0
	 * @param array $items The items.
	 * @param mixed $query The query.
	 * @return array<\WP_Post> The items.
	 */
	public function get_items( array $items, $query ): array {
		if ( is_array( $items ) && 0 < count( $items ) ) {
			return $items;
		}

		add_filter( 'posts_fields', array( $this, 'wp_query_fields' ), 10, 2 );
		if ( $query instanceof \WP_Query ) {
			$items = $query->posts;
		} else {
			if ( isset( $query['child_of'] ) ) {
				if ( is_array( $query['post_type'] ) ) {
					// We set post_type as an array of types even with a single value. `get_posts` does
					// not work with an array of post types, so we fetch each type's posts separately.
					$items = array();
					foreach ( $query['post_type'] as $post_type ) {
						$partial_query = $query;
						$partial_query['post_type'] = $post_type;
						$partial_items = get_pages( $partial_query );
						if ( false != $partial_items ) {
							$items = array_merge( $items, $partial_items );
						}
					}
				} else {
					$items = get_pages( $query );
				}
			} else {
				$query = wp_parse_args(
					$query,
					array(
						'posts_per_page' => -1,
						'nopaging'       => true,
					)
				);
				$items = ( new \WP_Query( $query ) )->posts;
			}
		}
		remove_filter( 'posts_fields', array( $this, 'wp_query_fields' ) );

		return empty( $items ) ? array() : $items;
	}

	/**
	 * Set the fields we require on \WP_Query.
	 *
	 * @since 3.0.0 Introduced.
	 * @since 4.0.0 Converted to static function, and moved to \AlphaListing\Shortcode\PostsQuery.
	 * @param string    $fields The current fields in SQL format.
	 * @param \WP_Query $query  The \WP_Query instance.
	 * @return string The new fields in SQL format.
	 */
	public static function wp_query_fields( string $fields, \WP_Query $query ): string {
		global $wpdb;
		return "{$wpdb->posts}.ID, {$wpdb->posts}.post_title, {$wpdb->posts}.post_type, {$wpdb->posts}.post_name, {$wpdb->posts}.post_parent, {$wpdb->posts}.post_date";
	}

	/**
	 * Get the item.
	 *
	 * @param mixed $previous The previous item object or ID.
	 * @param mixed $item     The item object or ID.
	 * @return \WP_Post The item object.
	 */
	public function get_item( $previous, $item ) {
		if ( $previous instanceof \WP_Post ) {
			return $previous;
		}

		if ( $item instanceof \WP_Post ) {
			return $item;
		}

		return get_post( $item );
	}

	/**
	 * Get the item ID.
	 *
	 * @param int      $item_id The item ID.
	 * @param \WP_Post $item    The item object.
	 * @return int The item ID.
	 */
	public function get_item_id( int $item_id, $item ) {
		if ( ! $item instanceof \WP_Post ) {
			$item = get_post( $item );
		}

		if ( $item instanceof \WP_Post ) {
			$item_id = $item->ID;
		}

		return $item_id;
	}

	/**
	 * Get the item title.
	 *
	 * @param string   $title The item title.
	 * @param \WP_Post $item  The item object.
	 * @return string The item title.
	 */
	public function get_item_title( string $title, $item ) {
		if ( ! $item instanceof \WP_Post ) {
			$item = get_post( $item );
		}

		if ( $item instanceof \WP_Post ) {
			$title = get_the_title( $item );
		}

		return $title;
	}

	/**
	 * Get the item permalink.
	 *
	 * @param string   $permalink The item permalink.
	 * @param \WP_Post $item      The item object.
	 * @return string The item permalink
	 */
	public function get_item_permalink( string $permalink, $item ) {
		if ( ! $item instanceof \WP_Post ) {
			$item = get_post( $item );
		}

		if ( $item instanceof \WP_Post ) {
			$permalink = get_the_permalink( $item );
		}

		return $permalink;
	}
}
