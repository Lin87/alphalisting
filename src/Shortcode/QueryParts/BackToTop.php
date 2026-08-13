<?php
/**
 * Back To Top Query Part.
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
 * Back To Top Query Part extension.
 */
class BackToTop extends Extension {
	/**
	 * The attribute for this Query Part.
	 *
	 * @since 4.3.9
	 * @var string
	 */
	public $attribute_name = 'back-to-top';

	/**
	 * The default value for this Query Part.
	 *
	 * @since 4.3.9
	 * @var string
	 */
	public $default_value = 'true';

	/**
	 * Resolved "back to top" visibility for this listing.
	 *
	 * @var bool
	 */
	protected $show_back_to_top = true;

	/**
	 * Sanitize the shortcode attribute.
	 *
	 * @param mixed $value      The value of the shortcode attribute.
	 * @param array $attributes The complete set of shortcode attributes.
	 * @return string
	 */
	public function sanitize_attribute( $value, array $attributes ) {
		return alphalisting_is_truthy( $value ) ? 'true' : 'false';
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
		$this->show_back_to_top = alphalisting_is_truthy( $value );
		$this->add_hook( 'filter', 'alphalisting_show_back_to_top', array( $this, 'return_show_back_to_top' ), 10, 2 );
		return $query;
	}

	/**
	 * Return whether "Back to top" should be rendered.
	 *
	 * @param bool $show Whether to show the link.
	 * @return bool
	 */
	public function return_show_back_to_top( bool $show ): bool {
		return $this->show_back_to_top;
	}
}
