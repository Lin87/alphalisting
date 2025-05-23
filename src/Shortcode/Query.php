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

use \eslin87\AlphaListing\Extension;
use \eslin87\AlphaListing\Singleton;

/**
 * Query Type extension parent class
 */
abstract class Query extends Singleton implements Extension {
	/**
	 * The display/query type name.
	 *
	 * @var string
	 */
	public $display;

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
}
