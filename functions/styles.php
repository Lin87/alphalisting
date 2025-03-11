<?php
/**
 * AlphaListing Styles
 *
 * @package alphalisting
 */

declare(strict_types=1);

namespace AlphaListing;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Enqueue A-Z default styling
 *
 * @since 0.7
 * @since 4.0.0 Don't conditionally load to alleviate issues with not loading.
 * @param bool $unused Not used.
 * @return void
 */
function alphalisting_enqueue_styles( bool $unused = false ) {
	wp_enqueue_style( 'alphalisting' );
}

/**
 * Enqueue A-Z customizer styles.
 *
 * @since 2.1.0
 * @return void
 */
function alphalisting_customize_enqueue_styles() {
	wp_enqueue_style( 'alphalisting-admin' );
}

/**
 * Forcibly enqueue styling. This is a helper function which can be hooked in-place of the default hook added in `alphalisting_add_styling`
 *
 * @since 1.3.0
 * @since 4.0.0 deprecated
 * @deprecated
 * @return void
 */
function alphalisting_force_enqueue_styles() {
	// no-op.
}

/**
 * Replace the default styling enqueue function with `alphalisting_force_enqueue_styles` to always add the styling to pages
 *
 * @since 1.3.0
 * @since 4.0.0 deprecated
 * @deprecated
 * @return void
 */
function alphalisting_force_enable_styles() {
	// no-op.
}
