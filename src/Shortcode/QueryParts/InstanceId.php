<?php
/**
 * Instance ID Query Part.
 *
 * @package alphalisting
 */

declare(strict_types=1);

namespace eslin87\AlphaListing\Shortcode\QueryParts;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use \eslin87\AlphaListing\Shortcode\Extension;

/**
 * Instance ID Query Part extension
 */
class InstanceId extends Extension {
	/**
	 * The attribute for this Query Part.
	 *
	 * @since 4.0.0
	 * @var string
	 */
	public $attribute_name = 'instance-id';

	/**
	 * The instance ID.
	 *
	 * @var string
	 */
	public $instance_id = '';

	/**
	 * Sanitize the shortcode attribute.
	 *
	 * The value ends up in an HTML `id` and in a URL fragment, so restrict it to
	 * characters valid in both. Spaces or a `#` would otherwise produce a broken id
	 * and a dead "back to top" anchor.
	 *
	 * @since 4.5.0
	 * @param mixed $value      The value of the shortcode attribute.
	 * @param array $attributes The complete set of shortcode attributes.
	 * @return string The sanitized instance ID.
	 */
	public function sanitize_attribute( $value, array $attributes ) {
		return sanitize_html_class( (string) $value );
	}

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
	public function shortcode_query( $query, string $display, string $key, $value, array $attributes ) {
		$this->instance_id = sanitize_html_class( (string) $value );
		$this->add_hook( 'filter', 'alphalisting_instance_id', array( $this, 'return_instance_id' ), 10, 1 );
		return $query;
	}

	/**
	 * Return the ID for this instance.
	 *
	 * @return string
	 */
	public function return_instance_id(): string {
		return $this->instance_id;
	}
}
