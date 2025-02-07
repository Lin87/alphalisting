<?php
/**
 * Default index parsing functions.
 *
 * @package alphalisting
 */

declare(strict_types=1);

namespace AlphaListing;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Indices
 */
class Indices extends Singleton implements Extension {
	/**
	 * Bind the index parsing functions to their respective filters.
	 *
	 * @return void
	 */
	final public function initialize() {
		add_filter( 'alphalisting_extract_item_indices', array( $this, 'get_item_indices' ), 1, 4 );
	}

	/**
	 * Find and return the index letter for a post
	 *
	 * @since 2.0.0
	 * @param array<string,mixed>   $indices  Previously discovered indices.
	 * @param int|\WP_Post|\WP_Term $item     ID of the item whose index letters we want to find.
	 * @param string                $type     Type of listing - terms or posts.
	 * @param Alphabet              $alphabet The alphabet object.
	 * @return array<string,mixed> The post's index letters (usually matching the first character of the post title)
	 */
	public static function get_item_indices( array $indices, $item, string $type, Alphabet $alphabet ): array {
		/**
		 * Get the item object.
		 *
		 * @since 4.0.0
		 * @param mixed The item object or ID.
		 * @return string The item object.
		 */
		$item = apply_filters( "alphalisting_get_item_for_display__{$type}", null, $item );

		/**
		 * Get the item permalink.
		 *
		 * @since 4.0.0
		 * @param string The permalink.
		 * @param mixed  The item object or ID.
		 * @return string The permalink.
		 */
		$permalink = apply_filters( "alphalisting_get_item_permalink_for_display__{$type}", '', $item );

		/**
		 * Get the item title.
		 *
		 * @since 4.0.0
		 * @param string The title.
		 * @param mixed  The item object or ID.
		 * @return string The title.
		 */
		$title = apply_filters( "alphalisting_get_item_title_for_display__{$type}", '', $item );

		/**
		 * Get the item title.
		 *
		 * @since 4.0.0
		 * @param string The title.
		 * @param mixed  The item object or ID.
		 * @return string The title.
		 */
		$item_id = apply_filters( "alphalisting_get_item_id_for_display__{$type}", -1, $item );

		/**
		 * Modify the title for this item before indexing
		 *
		 * @since 2.1.0
		 * @since 4.0.0 Remove `int` from passed parameter types for `$item`.
		 * @param string            $title The current title
		 * @param \WP_Post|\WP_Term $item The item
		 * @param string            $item_type The type of the item. Either 'posts' or 'terms'.
		 */
		$title = apply_filters( 'alphalisting-pre-index-item-title', $title, $item, $type ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound

		/**
		 * Modify the title for this item before indexing
		 *
		 * @since 2.1.0
		 * @since 4.0.0 Set parameter type for `$item` to `mixed`.
		 * @param string $title The current title
		 * @param mixed  $item The item
		 * @param string $item_type The type of the item. Either 'posts' or 'terms'.
		 */
		$title = apply_filters( 'alphalisting_pre_index_item_title', $title, $item, $type );

		$index = Strings::maybe_mb_substr( trim( $title ), 0, 1 );

		/**
		 * Modify the indice(s) to group this item under
		 *
		 * @since 2.1.0
		 * @since 4.0.0 Set parameter type for `$item` to `mixed`.
		 * @param array  $indices The current indices
		 * @param mixed  $item The item
		 * @param string $item_type The type of the listing.
		 */
		$index_letters = apply_filters( 'alphalisting-item-index-letter', array( $index ), $item, $type ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound

		/**
		 * Modify the indice(s) to group this item under
		 *
		 * @since 2.1.0
		 * @since 4.0.0 Set parameter type for `$item` to `mixed`.
		 * @param array  $indices The current indices
		 * @param mixed  $item The item
		 * @param string $item_type The type of the listing.
		 */
		$index_letters = apply_filters( 'alphalisting_item_index_letter', $index_letters, $item, $type );
		$index_letters = array_unique( array_filter( $index_letters ) );

		foreach ( $index_letters as $letter ) {
			$indices[ $alphabet->get_letter_for_key( $letter ) ][] = array(
				'title' => $title,
				'item'  => "$type:$item_id",
				'link'  => $permalink,
			);
		}

		$filter_params = array( $indices, $item );
		if ( $item instanceof \WP_Term ) {
			/**
			 * Modify the indice(s) to group this term under
			 *
			 * @deprecated Use alphalisting_item_index_letter and/or alphalisting_item_title
			 * @see alphalisting_item_index_letter, alphalisting_item_title
			 */
			$indices = apply_filters_deprecated( 'alphalisting_term_indices', $filter_params, '1.0.0', 'alphalisting_item_index_letter' );
		} elseif ( $item instanceof \WP_Post ) {
			/**
			 * Modify the indice(s) to group this post under
			 *
			 * @deprecated Use alphalisting_item_index_letter and/or alphalisting_item_title
			 * @see alphalisting_item_index_letter, alphalisting_item_title
			 */
			$indices = apply_filters_deprecated( 'alphalisting_post_indices', $filter_params, '1.5.0', 'alphalisting_item_index_letter' );
		} // End if.

		$filter_params = array( $indices, $item, $type );

		/**
		 * Modify the indice(s) to group this item under
		 *
		 * @since 1.7.0
		 * @since 2.1.0 Deprecated
		 * @since 4.0.0 Remove `int` from passed parameter types for `$item`.
		 * @deprecated Use alphalisting_item_index_letter and/or alphalisting_item_title
		 * @see alphalisting_item_index_letter, alphalisting_item_title
		 * @param array  $indices The current indices
		 * @param mixed  $item The item
		 * @param string $item_type The type of the listing.
		 */
		$indices = apply_filters_deprecated( 'alphalisting_item_indices', $filter_params, '2.1.0', 'alphalisting_item_index_letter' );

		/**
		 * Modify the indice(s) to group this item under
		 *
		 * @since 1.7.1
		 * @since 2.1.0 Deprecated
		 * @since 4.0.0 Set parameter type for `$item` to `mixed`.
		 * @deprecated Use alphalisting_item_index_letter and/or alphalisting_item_title
		 * @see alphalisting_item_index_letter, alphalisting_item_title
		 * @param array  $indices The current indices
		 * @param mixed  $item The item
		 * @param string $item_type The type of the listing.
		 */
		$indices = apply_filters_deprecated( 'alphalisting-item-indices', $filter_params, '2.1.0', 'alphalisting_item_index_letter' );  // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound

		if ( defined( 'ALPHALISTING_LOG' ) && ALPHALISTING_LOG > 2 ) {
			do_action( 'alphalisting_log', 'AlphaListing: Item indices', $indices );
		}

		return $indices;
	}
}
