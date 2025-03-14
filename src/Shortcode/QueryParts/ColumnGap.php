<?php
/**
 * Alphabet Query Part.
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
 * Column Gap Query Part extension
 */
class ColumnGap extends Extension {
	/**
	 * The attribute for this Query Part.
	 *
	 * @since 4.0.0
	 * @var string
	 */
	public $attribute_name = 'column-gap';

	/**
	 * The column gap.
	 *
	 * @var string
	 */
	public $column_gap = '0.6em';

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
		$this->column_gap = $value;
		$this->add_hook( 'filter', 'alphalisting_styles', array( $this, 'return_styles' ), 10, 3 );
		return $query;
	}

	/**
	 * Return the stylesheet for this instance.
	 *
	 * @param string             $styles      The stylesheet.
	 * @param \AlphaListing\Query $alphalisting The A-Z Listing Query object.
	 * @param string             $instance_id The instance ID.
	 * @return string
	 */
	public function return_styles( $styles, $alphalisting, $instance_id ): string {
		return "$styles --alphalisting-column-gap: $this->column_gap; ";
	}
}
