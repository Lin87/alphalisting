<?php
/**
 * Shortcode Extension class
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
 * Shortcode Extension
 */
abstract class Extension extends Singleton implements \eslin87\AlphaListing\Extension {
	/**
	 * The attribute for this shortcode extension.
	 *
	 * @since 4.0.0
	 * @var string
	 */
	public $attribute_name = '';

	/**
	 * The default value for the attribute.
	 *
	 * @since 4.0.0
	 * @var string
	 */
	public $default_value = '';

	/**
	 * The types of listing this shortcode extension may be used with.
	 *
	 * @since 4.0.0
	 * @var array
	 */
	public $display_types = array();

	/**
	 * Our hooks
	 *
	 * @since 4.0.0
	 * @var array
	 */
	protected $hooks = array(
		'action' => array(),
		'filter' => array(),
	);

	/**
	 * Initialize the extension.
	 *
	 * @since 4.0.0
	 * @return void
	 */
	final public function initialize() {
		add_action( 'alphalisting_shortcode_start', array( $this, 'handler' ), 10, 1 );
		add_action( 'alphalisting_shortcode_end', array( $this, 'cleanup' ), 10, 1 );

		if ( ! empty( $this->attribute_name ) ) {
			add_filter( 'alphalisting_get_shortcode_attributes', array( $this, 'add_attribute' ) );
			add_filter( "alphalisting_sanitize_shortcode_attribute__{$this->attribute_name}", array( $this, 'sanitize_attribute' ), 10, 2 );
			add_filter( "alphalisting_shortcode_query_for_attribute__{$this->attribute_name}", array( $this, 'shortcode_query' ), 10, 5 );
			foreach ( $this->display_types as $display ) {
				add_filter( "alphalisting_shortcode_query_for_display__{$display}__and_attribute__{$this->attribute_name}", array( $this, 'shortcode_query_for_display_and_attribute' ), 10, 5 );
			}
		}
	}

	/**
	 * Add a hook.
	 *
	 * @since 1.0.0
	 * @param string   $type The hook type (filter or action).
	 * @param string   $name The hook name.
	 * @param callable $function The function to call.
	 * @param int      $order The order to call this function.
	 * @param int      $arguments The number of arguments the function expects.
	 */
	final protected function add_hook( string $type, string $name, callable $function, int $order = 10, int $arguments = 1 ) {
		$hook = array( $name, $function, $order, $arguments );
		call_user_func_array( "add_$type", $hook );
		$this->hooks[ $type ][] = $hook;
	}

	/**
	 * Remove a hook.
	 *
	 * @since 1.0.0
	 * @param string   $type The hook type (filter or action).
	 * @param string   $name The hook name.
	 * @param callable $function The function to call.
	 * @param int      $order The order to call this function.
	 * @param int      $arguments The number of arguments the function expects.
	 */
	final protected function remove_hook( string $type, string $name, callable $function, int $order = 10, int $arguments = 1 ) {
		$hook = array( $name, $function, $order, $arguments );
		call_user_func_array( "remove_$type", $hook );
		array_filter(
			$this->filters[ $type ],
			function( $item ) use ( $hook ) {
				return $item === $hook;
			}
		);
	}

	/**
	 * Unhook all our filters and actions.
	 *
	 * @since 1.0.0
	 */
	final public function cleanup() {
		foreach ( array_keys( $this->hooks ) as $type ) {
			while ( $hook = array_shift( $this->hooks[ $type ] ) ) { // phpcs:ignore WordPress.CodeAnalysis.AssignmentInCondition.FoundInWhileCondition
				call_user_func_array( "remove_$type", $hook );
			}
		}
	}

	/**
	 * Handle the shortcode.
	 *
	 * @since 4.0.0
	 * @return void
	 */
	public function handler() {}

	/**
	 * Add the attribute.
	 *
	 * @since 4.0.0
	 * @param array $attributes The attributes.
	 * @return array The attributes.
	 */
	public function add_attribute( $attributes ) {
		$attributes[ $this->attribute_name ] = $this->default_value;
		return $attributes;
	}

	/**
	 * Sanitize the shortcode attribute.
	 *
	 * @param mixed $value      The value of the shortcode attribute.
	 * @param array $attributes The complete set of shortcode attributes.
	 * @return mixed The sanitized value.
	 */
	public function sanitize_attribute( $value, array $attributes ) {
		return $value;
	}

	/**
	 * Update the query with this extension's additional configuration.
	 *
	 * @param mixed  $query      The query.
	 * @param string $display    The display/query type.
	 * @param string $key        The name of the attribute.
	 * @param mixed  $value      The shortcode attribute value.
	 * @param array  $attributes The complete set of shortcode attributes.
	 * @return mixed The updated query.
	 */
	public function shortcode_query( $query, string $display, string $key, $value, array $attributes ) {
		return $query;
	}

	/**
	 * Update the query with this extension's additional configuration.
	 *
	 * @param mixed  $query      The query.
	 * @param string $display    The display/query type.
	 * @param string $key        The name of the attribute.
	 * @param mixed  $value      The shortcode attribute value.
	 * @param array  $attributes The complete set of shortcode attributes.
	 * @return mixed The updated query.
	 */
	public function shortcode_query_for_display_and_attribute( $query, string $display, string $key, $value, array $attributes ) {
		return $query;
	}
}
