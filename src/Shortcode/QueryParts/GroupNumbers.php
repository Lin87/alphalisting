<?php
/**
 * Group Numbers Query Part.
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
 * Group Numbers Query Part extension.
 */
class GroupNumbers extends Extension {
	/**
	 * The attribute for this Query Part.
	 *
	 * @since 4.5.1
	 * @var string
	 */
	public $attribute_name = 'group-numbers';
}
