<?php
/**
 * AlphaListing Internationalisation
 *
 * @package alphalisting
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Initialize the translations for the plugin
 *
 * @since 2.0.0
 * @return void
 */
function alphalisting_init_translations() {
	load_plugin_textdomain( 'alphalisting' );
}
add_action( 'init', 'alphalisting_init_translations' );
