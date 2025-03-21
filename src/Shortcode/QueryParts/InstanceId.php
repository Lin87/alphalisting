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
	 * Update the query with this extension's additional configuration.
	 *
	 * @param \AlphaListing\Query $query      The query.
	 * @param string             $display    The display/query type.
	 * @param string             $key        The name of the attribute.
	 * @param mixed              $value      The shortcode attribute value.
	 * @param array              $attributes The complete set of shortcode attributes.
	 * @return mixed The updated query.
	 */
	public function shortcode_query( $query, string $display, string $key, $value, array $attributes ) {
		$this->instance_id = $value;
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
