<?php
/**
 * Plugin Name:     	AlphaListing
 * Plugin URI:      	https://github.com/Lin87/alphalisting
 * Description:     	Display an A to Z listing of posts (based on work by Dani Llewellyn)
 * Author:          	Ethan Lin
 * Author URI:      	https://github.com/Lin87
 * Original Author: 	Dani Llewellyn
 * Original Author URI: https://profiles.wordpress.org/diddledani/
 * Text Domain:     	a-z-listing
 * Domain Path:     	/languages
 * Version:         	4.3.1
 *
 * @package         alphalisting
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( '\get_plugin_data' ) ) {
	require_once ABSPATH . 'wp-admin/includes/plugin.php';
}
define( 'ALPHALISTING_VERSION', \get_plugin_data( __FILE__, false, false )['Version'] );

if ( ! defined( 'ALPHALISTING_LOG' ) ) {
	define( 'ALPHALISTING_LOG', false );
}

define( 'ALPHALISTING_PLUGIN_FILE', __FILE__ );
define( 'ALPHALISTING_DEFAULT_TEMPLATE', __DIR__ . '/templates/a-z-listing.php' );

if ( file_exists( __DIR__ . '/build/vendor/scoper-autoload.php' ) ) {
	require_once __DIR__ . '/build/vendor/scoper-autoload.php';
} else {
	require_once __DIR__ . '/vendor/autoload.php';
}

require_once __DIR__ . '/functions/i18n.php';
require_once __DIR__ . '/functions/health-check.php';
require_once __DIR__ . '/functions/helpers.php';
require_once __DIR__ . '/functions/styles.php';
require_once __DIR__ . '/functions/scripts.php';
require_once __DIR__ . '/functions/enqueues.php';
require_once __DIR__ . '/widgets/class-alphalisting-widget.php';

/**
 * Initialize the plugin.
 *
 * @return void
 */
function alphalisting_init() {
	\AlphaListing\Indices::instance()->activate( __FILE__ )->initialize();
	\AlphaListing\GutenBlock::instance()->activate( __FILE__ )->initialize();

	// Shortcode handler.
	\AlphaListing\Shortcode::instance()->activate( __FILE__ )->initialize();
	\AlphaListing\Shortcode\PostsQuery::instance()->activate( __FILE__ )->initialize();
	\AlphaListing\Shortcode\TermsQuery::instance()->activate( __FILE__ )->initialize();

	// Shortcode attribute handlers.
	\AlphaListing\Shortcode\QueryParts\Alphabet::instance()->activate( __FILE__ )->initialize();
	\AlphaListing\Shortcode\QueryParts\Columns::instance()->activate( __FILE__ )->initialize();
	\AlphaListing\Shortcode\QueryParts\ColumnGap::instance()->activate( __FILE__ )->initialize();
	\AlphaListing\Shortcode\QueryParts\ColumnWidth::instance()->activate( __FILE__ )->initialize();
	\AlphaListing\Shortcode\QueryParts\ExcludePosts::instance()->activate( __FILE__ )->initialize();
	\AlphaListing\Shortcode\QueryParts\ExcludeTerms::instance()->activate( __FILE__ )->initialize();
	\AlphaListing\Shortcode\QueryParts\HideEmpty_Deprecated::instance()->activate( __FILE__ )->initialize();
	\AlphaListing\Shortcode\QueryParts\HideEmptyTerms::instance()->activate( __FILE__ )->initialize();
	\AlphaListing\Shortcode\QueryParts\InstanceId::instance()->activate( __FILE__ )->initialize();
	\AlphaListing\Shortcode\QueryParts\ParentPost::instance()->activate( __FILE__ )->initialize();
	\AlphaListing\Shortcode\QueryParts\ParentTermId::instance()->activate( __FILE__ )->initialize();
	\AlphaListing\Shortcode\QueryParts\ParentTermSlugOrId::instance()->activate( __FILE__ )->initialize();
	\AlphaListing\Shortcode\QueryParts\PostsTerms::instance()->activate( __FILE__ )->initialize();
	\AlphaListing\Shortcode\QueryParts\PostType::instance()->activate( __FILE__ )->initialize();
	\AlphaListing\Shortcode\QueryParts\SymbolsFirst::instance()->activate( __FILE__ )->initialize();
	\AlphaListing\Shortcode\QueryParts\Taxonomy::instance()->activate( __FILE__ )->initialize();
	\AlphaListing\Shortcode\QueryParts\TermsTerms::instance()->activate( __FILE__ )->initialize();
}
add_action( 'init', 'alphalisting_init', 5 );
