<?php
/**
 * Query Type extension parent class
 *
 * @package alphalisting
 */

declare(strict_types=1);

namespace eslin87\AlphaListing\Shortcode;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use \eslin87\AlphaListing\Singleton;

/**
 * Query Type extension parent class
 */
abstract class Query extends Singleton {
	/**
	 * The display/query type name.
	 *
	 * @var string
	 */
	public $display;

	/**
	 * Items already fetched during this request, keyed by get_request_items_key().
	 *
	 * Lets identical listings on one page (such as the widget and the listing it links to)
	 * share a single query. Never persisted, so it cannot serve stale results.
	 *
	 * @since 4.6.2
	 * @var array<string,array>
	 */
	protected static $request_items = array();

	/**
	 * Initialize the extension.
	 *
	 * @since 4.0.0
	 * @return void
	 */
	final public function initialize() {
		add_filter( 'alphalisting_shortcode_query_types', array( $this, 'add_query_type' ) );
		add_filter( "alphalisting_shortcode_query_for_display__{$this->display}", array( $this, 'apply_query_to_shortcode' ), 5, 2 );
		add_filter( "alphalisting_get_items_for_display__{$this->display}", array( $this, 'get_items' ), 5, 2 );
		add_filter( "alphalisting_get_item_for_display__{$this->display}", array( $this, 'get_item' ), 5, 2 );
		add_filter( "alphalisting_get_item_id_for_display__{$this->display}", array( $this, 'get_item_id' ), 5, 2 );
		add_filter( "alphalisting_get_item_title_for_display__{$this->display}", array( $this, 'get_item_title' ), 5, 2 );
		add_filter( "alphalisting_get_item_permalink_for_display__{$this->display}", array( $this, 'get_item_permalink' ), 5, 2 );
	}

	/**
	 * Add this query type to the list of supported query types.
	 *
	 * @param array<string> $query_types The supported query types.
	 * @return array<string> The updated query types.
	 */
	public function add_query_type( array $query_types ): array {
		if ( ! in_array( $this->display, $query_types, true ) ) {
			$query_types[] = $this->display;
		}
		return $query_types;
	}

	/**
	 * Execute this query extension.
	 *
	 * @param mixed $query      The query.
	 * @param array $attributes The complete set of shortcode attributes.
	 * @return mixed The query.
	 */
	public function apply_query_to_shortcode( $query, array $attributes ) {
		foreach ( $attributes as $key => $value ) {
			if ( is_string( $value ) ) {
				$value = trim( $value );
			}
			if ( ! empty( $value ) ) {
				$query = apply_filters( "alphalisting_shortcode_query_for_attribute__{$key}", $query, $this->display, $key, $value, $attributes );
				$query = apply_filters( "alphalisting_shortcode_query_for_display__{$this->display}__and_attribute__{$key}", $query, $this->display, $key, $value, $attributes );
			}
		}
		return $query;
	}

	/**
	 * Build the key under which this request's items for a query are remembered.
	 *
	 * Query parts change which items are fetched only through the query array, so the
	 * array identifies the result. The user and locale are included because they also
	 * decide which items WordPress returns (private posts, multilingual plugins).
	 *
	 * @since 4.6.2
	 * @param mixed $query The query.
	 * @return string The key, or '' when the result must not be reused.
	 */
	protected function get_request_items_key( $query ): string {
		if ( ! is_array( $query ) ) {
			return '';
		}

		/**
		 * Whether identical listing queries in one request may share their results.
		 *
		 * @since 4.6.2
		 * @param bool   $reuse   Whether to reuse the results. Default true.
		 * @param array  $query   The query.
		 * @param string $display The display/query type.
		 */
		if ( ! apply_filters( 'alphalisting_reuse_query_results', true, $query, $this->display ) ) {
			return '';
		}

		try {
			$serialized = serialize( array( $this->display, $query, get_current_user_id(), get_locale() ) ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.serialize_serialize
		} catch ( \Throwable $e ) {
			// A third-party query arg such as a closure cannot be serialized; just don't reuse.
			return '';
		}

		return md5( $serialized );
	}
}
