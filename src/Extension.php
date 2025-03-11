<?php
/**
 * AlphaListing Extension interface
 *
 * @package alphalisting
 */

declare(strict_types=1);

namespace eslin87\AlphaListing;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * AlphaListing Extension interface
 */
interface Extension {
	/**
	 * Singleton
	 *
	 * @since 4.0.0
	 * @return Extension extension object.
	 */
	public static function instance(): Extension;

	/**
	 * Activate
	 *
	 * @since 4.0.0
	 * @param string              $file   the plugin file.
	 * @param array<string,mixed> $plugin the plugin details.
	 * @return Extension extension object.
	 */
	public function activate( string $file = '', array $plugin = array() ): Extension;

	/**
	 * Initialize
	 *
	 * @since 4.0.0
	 * @return void
	 */
	public function initialize();
}
