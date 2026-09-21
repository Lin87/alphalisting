<?php
/**
 * AlphaListing Styles
 *
 * @package alphalisting
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Enqueue A-Z default styling
 *
 * @since 0.7
 * @since 4.0.0 Don't conditionally load to alleviate issues with not loading.
 * @return void
 */
function alphalisting_enqueue_styles() {
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
