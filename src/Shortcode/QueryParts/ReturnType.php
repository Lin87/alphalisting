<?php
/**
 * Return Type Query Part.
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
 * Return Type Query Part extension.
 *
 * Named ReturnType because `return` is a reserved word in PHP.
 */
class ReturnType extends Extension {
	/**
	 * The attribute for this Query Part.
	 *
	 * @since 4.5.1
	 * @var string
	 */
	public $attribute_name = 'return';

	/**
	 * The default value for this Query Part.
	 *
	 * @since 4.5.1
	 * @var string
	 */
	public $default_value = 'listing';
}
