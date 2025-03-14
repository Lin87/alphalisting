<?php
/**
 * Plugin Name:     	AlphaListing
 * Plugin URI:      	https://github.com/Lin87/alphalisting
 * Description:     	Display an A to Z listing of posts.
 * Author:          	Ethan Lin
 * Author URI:      	https://profiles.wordpress.org/eslin87/
 * Original Author: 	Lucy (formerly Dani) Llewellyn
 * Original Author URI: https://profiles.wordpress.org/diddledani/
 * Text Domain:     	alphalisting
 * Domain Path:     	/languages
 * Version:         	4.3.5
 * License:				GPLv2 or later
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

require_once __DIR__ . '/vendor/autoload.php';

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
	\eslin87\AlphaListing\Indices::instance()->activate( __FILE__ )->initialize();
	\eslin87\AlphaListing\GutenBlock::instance()->activate( __FILE__ )->initialize();

	// Shortcode handler.
	\eslin87\AlphaListing\Shortcode::instance()->activate( __FILE__ )->initialize();
	\eslin87\AlphaListing\Shortcode\PostsQuery::instance()->activate( __FILE__ )->initialize();
	\eslin87\AlphaListing\Shortcode\TermsQuery::instance()->activate( __FILE__ )->initialize();

	// Shortcode attribute handlers.
	\eslin87\AlphaListing\Shortcode\QueryParts\Alphabet::instance()->activate( __FILE__ )->initialize();
	\eslin87\AlphaListing\Shortcode\QueryParts\Columns::instance()->activate( __FILE__ )->initialize();
	\eslin87\AlphaListing\Shortcode\QueryParts\ColumnGap::instance()->activate( __FILE__ )->initialize();
	\eslin87\AlphaListing\Shortcode\QueryParts\ColumnWidth::instance()->activate( __FILE__ )->initialize();
	\eslin87\AlphaListing\Shortcode\QueryParts\ExcludePosts::instance()->activate( __FILE__ )->initialize();
	\eslin87\AlphaListing\Shortcode\QueryParts\ExcludeTerms::instance()->activate( __FILE__ )->initialize();
	\eslin87\AlphaListing\Shortcode\QueryParts\HideEmpty_Deprecated::instance()->activate( __FILE__ )->initialize();
	\eslin87\AlphaListing\Shortcode\QueryParts\HideEmptyTerms::instance()->activate( __FILE__ )->initialize();
	\eslin87\AlphaListing\Shortcode\QueryParts\InstanceId::instance()->activate( __FILE__ )->initialize();
	\eslin87\AlphaListing\Shortcode\QueryParts\ParentPost::instance()->activate( __FILE__ )->initialize();
	\eslin87\AlphaListing\Shortcode\QueryParts\ParentTermId::instance()->activate( __FILE__ )->initialize();
	\eslin87\AlphaListing\Shortcode\QueryParts\ParentTermSlugOrId::instance()->activate( __FILE__ )->initialize();
	\eslin87\AlphaListing\Shortcode\QueryParts\PostsTerms::instance()->activate( __FILE__ )->initialize();
	\eslin87\AlphaListing\Shortcode\QueryParts\PostType::instance()->activate( __FILE__ )->initialize();
	\eslin87\AlphaListing\Shortcode\QueryParts\SymbolsFirst::instance()->activate( __FILE__ )->initialize();
	\eslin87\AlphaListing\Shortcode\QueryParts\Taxonomy::instance()->activate( __FILE__ )->initialize();
	\eslin87\AlphaListing\Shortcode\QueryParts\TermsTerms::instance()->activate( __FILE__ )->initialize();
}
add_action( 'init', 'alphalisting_init', 5 );
