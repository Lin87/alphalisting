<?php
/**
 * Display Query Part.
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
 * Display Query Part extension.
 */
class Display extends Extension {
	/**
	 * The attribute for this Query Part.
	 *
	 * @since 4.5.1
	 * @var string
	 */
	public $attribute_name = 'display';

	/**
	 * The default value for this Query Part.
	 *
	 * @since 4.5.1
	 * @var string
	 */
	public $default_value = 'posts';
}
