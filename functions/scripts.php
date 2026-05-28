<?php
/**
 * JavaScript enqueueing functions.
 *
 * @package alphalisting
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
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
