<?php
/**
 * Numbers Query Part.
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
 * Numbers Query Part extension.
 *
 * Not the listing-building `eslin87\AlphaListing\Numbers` in src/Numbers.php.
 */
class Numbers extends Extension {
	/**
	 * The attribute for this Query Part.
	 *
	 * @since 4.5.1
	 * @var string
	 */
	public $attribute_name = 'numbers';

	/**
	 * The default value for this Query Part.
	 *
	 * @since 4.5.1
	 * @var string
	 */
	public $default_value = 'hide';
}
