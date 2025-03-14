<?php
/**
 * Javascripts enqueueing functions.
 *
 * @package alphalisting
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Enqueue Tabs script on pages where the shortcode is active
 *
 * @since 2.0.0
 * @param bool $force Set this to true if you want the script to always be enqueued.
 * @return void
 */
function alphalisting_enqueue_tabs( bool $force = false ) {
	global $post;
	if ( $force || ( is_singular() && has_shortcode( $post->post_content, 'alphalisting' ) ) ) {
		wp_enqueue_script( 'alphalisting-tabs' );
	}
}

/**
 * Forcibly enqueue Tabs script. This is a helper function which can be hooked in-place of the default hook added in `alphalisting_add_styling`
 *
 * @since 2.0.0
 * @return void
 */
function alphalisting_force_enqueue_tabs() {
	alphalisting_enqueue_tabs( true );
}

/**
 * Replace the default Tabs script enqueue function with `alphalisting_force_enqueue_tabs` to always add the Tabs script to pages
 *
 * @since 2.0.0
 * @return void
 */
function alphalisting_force_enable_tabs() {
	if ( false !== has_action( 'wp_enqueue_scripts', 'alphalisting_enqueue_tabs' ) ) {
		remove_action( 'wp_enqueue_scripts', 'alphalisting_enqueue_tabs' );
	}
	add_action( 'wp_enqueue_scripts', 'alphalisting_force_enqueue_tabs' );
}

/**
 * Enqueue the widget configuration support script
 *
 * @since 2.1.0
 * @return void
 */
function alphalisting_enqueue_widget_admin_script() {
	wp_enqueue_script( 'alphalisting-widget-admin' );
}

/**
 * Enqueue Scrollfix script
 *
 * @since 4.0.0
 * @return void
 */
function alphalisting_enqueue_scroll_fix() {
	wp_enqueue_script( 'alphalisting-scroll-fix' );
}
