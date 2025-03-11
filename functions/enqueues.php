<?php
/**
 * AlphaListing Styles and Scripts enqueue functions
 *
 * @package alphalisting
 */

declare(strict_types=1);

namespace eslin87\AlphaListing;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register default A-Z stylesheet, jQuery-UI Tabs script and add our enqueue
 * functions to the `wp_enqueue_scripts` action
 *
 * @since 2.0.0 Renamed from alphalisting_add_styling. Added jQuery-UI Tabs support.
 * @return void
 */
function alphalisting_do_enqueue() {
	wp_register_style(
		'alphalisting',
		plugins_url( 'css/alphalisting-default.css', dirname( __FILE__ ) ),
		array( 'dashicons' ),
		ALPHALISTING_VERSION
	);

	wp_register_style(
		'alphalisting-admin',
		plugins_url( 'css/alphalisting-customize.css', dirname( __FILE__ ) ),
		array(),
		ALPHALISTING_VERSION
	);

	wp_register_script(
		'alphalisting-tabs',
		plugins_url( 'scripts/alphalisting-tabs.js', dirname( __FILE__ ) ),
		array( 'jquery', 'jquery-ui-tabs' ),
		ALPHALISTING_VERSION,
		true
	);

	wp_register_script(
		'alphalisting-widget-admin',
		plugins_url( 'scripts/alphalisting-widget-admin.js', dirname( __FILE__ ) ),
		array( 'jquery', 'jquery-ui-autocomplete' ),
		ALPHALISTING_VERSION,
		true
	);
	
	wp_localize_script(
		'alphalisting-widget-admin',
		'alphalisting_widget_admin',
		array( 'ajax_url' => admin_url( 'admin-ajax.php' ) )
	);

	wp_register_script(
		'alphalisting-scroll-fix',
		plugins_url( 'scripts/alphalisting-scroll-fix.js', dirname( __FILE__ ) ),
		array(),
		ALPHALISTING_VERSION,
		true
	);
	wp_localize_script(
		'alphalisting-scroll-fix',
		'alphalisting_scroll_fix',
		array( 'offset' => -120 )
	);

	$add_styles = get_option( 'alphalisting-add-styling', true );
	/**
	 * Determine whether to add default listing styling
	 *
	 * @param bool True to add default styling, False to disable.
	 * @since 1.7.1
	 */
	$add_styles = apply_filters( 'alphalisting_add_styling', $add_styles );
	/**
	 * Determine whether to add default listing styling
	 *
	 * @param bool True to add default styling, False to disable.
	 * @since 1.7.1
	 */
	$add_styles = apply_filters( 'alphalisting-add-styling', $add_styles ); //phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound

	if ( defined( 'ALPHALISTING_LOG' ) && ALPHALISTING_LOG ) {
		do_action( 'alphalisting_log', 'AlphaListing: Add Styles', $add_styles );
	}
	if ( true === $add_styles && ! has_action( 'wp_enqueue_scripts', __NAMESPACE__ . '\\alphalisting_enqueue_styles' ) ) {
		add_action( 'wp_enqueue_scripts', __NAMESPACE__ . '\\alphalisting_enqueue_styles' );
	}

	add_action( 'customize_controls_enqueue_scripts', __NAMESPACE__ . '\\alphalisting_customize_enqueue_styles' );

	$tabify = get_option( 'alphalisting-add-tabs', false );
	/**
	 * Determine whether to add jQuery-UI Tabs
	 *
	 * @param bool True to add jQuery-UI Tabs, False to disable.
	 * @since 2.0.0
	 */
	$tabify = apply_filters( 'alphalisting_tabify', $tabify );
	/**
	 * Determine whether to add jQuery-UI Tabs
	 *
	 * @param bool True to add jQuery-UI Tabs, False to disable.
	 * @since 2.0.0
	 */
	$tabify = apply_filters( 'alphalisting-tabify', $tabify ); //phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound

	if ( defined( 'ALPHALISTING_LOG' ) && ALPHALISTING_LOG ) {
		do_action( 'alphalisting_log', 'AlphaListing: Tabify', $tabify );
	}
	if ( true === $tabify && ! has_action( 'wp_enqueue_scripts', __NAMESPACE__ . '\\alphalisting_enqueue_tabs' ) ) {
		add_action( 'wp_enqueue_scripts', __NAMESPACE__ . '\\alphalisting_enqueue_tabs' );
	}
}
add_action( 'init', __NAMESPACE__ . '\\alphalisting_do_enqueue' );
