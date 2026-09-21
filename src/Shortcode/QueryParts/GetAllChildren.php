<?php
/**
 * Get All Children Query Part.
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
 * Get All Children Query Part extension.
 *
 * Read from the attribute array by ParentPost and ParentTermCommon.
 */
class GetAllChildren extends Extension {
	/**
	 * The attribute for this Query Part.
	 *
	 * @since 4.5.1
	 * @var string
	 */
	public $attribute_name = 'get-all-children';

	/**
	 * The default value for this Query Part.
	 *
	 * @since 4.5.1
	 * @var string
	 */
	public $default_value = 'false';
}
