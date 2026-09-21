<?php
/**
 * Grouping Query Part.
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
 * Grouping Query Part extension.
 *
 * Not the listing-building `eslin87\AlphaListing\Grouping` in src/Grouping.php.
 */
class Grouping extends Extension {
	/**
	 * The attribute for this Query Part.
	 *
	 * @since 4.5.1
	 * @var string
	 */
	public $attribute_name = 'grouping';
}
