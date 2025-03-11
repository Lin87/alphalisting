<?php
/**
 * Health Check functionality
 *
 * @package alphalisting
 * @since 2.3.0
 */

declare(strict_types=1);

namespace AlphaListing;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Add AlphaListing Health Checks
 *
 * @since 2.3.0
 *
 * @param array<string,mixed> $tests The health checks.
 * @return array<string,mixed> The health checks.
 */
function alphalisting_add_health_check( array $tests ): array {
	$tests['direct']['alphalisting_mbstring'] = array(
		'label' => __( 'A to Z Listing plugin', 'alphalisting' ),
		'test'  => 'alphalisting_mbstring_health_check',
	);
	return $tests;
}
add_filter( 'site_status_tests', 'alphalisting_add_health_check' );

/**
 * The mbstring health check
 *
 * @since 2.3.0
 *
 * @return array<string,mixed> The health check results.
 */
function alphalisting_mbstring_health_check(): array {
	$result = array(
		'label'       => __( 'AlphaListing: PHP mbstring module is enabled', 'alphalisting' ),
		'status'      => 'good',
		'badge'       => array(
			'label' => __( 'Compatibility', 'alphalisting' ),
			'color' => 'green',
		),
		'description' => sprintf(
			'<p>%s</p>',
			__( 'The mbstring PHP module improves support for non-latin languages in the AlphaListing plugin.', 'alphalisting' )
		),
		'actions'     => '',
		'test'        => 'alphalisting_mbstring_health_check',
	);

	if ( ! extension_loaded( 'mbstring' ) ) {
		$result['status']         = 'recommended';
		$result['label']          = __( 'AlphaListing: PHP mbstring module is not enabled', 'alphalisting' );
		$result['badge']['color'] = 'orange';
		$result['description']    = sprintf(
			'<p>%s</p>',
			__( 'The mbstring PHP module is not enabled on your server. This module improves support for non-latin languages in the AlphaListing plugin.', 'alphalisting' )
		);
		$result['actions']        = __( 'Contact your web host to request that the mbstring PHP module is enabled for your site.', 'alphalisting' );
	}

	return $result;
}

/**
 * Add mbstring to the recommended modules section of the health-check feature
 *
 * @since 2.3.0
 *
 * @param array<string,mixed> $modules An associated array of module properties used during testing.
 * @return array<string,mixed> The `$modules` array with `mbstring` added.
 */
function alphalisting_php_modules_health_check( array $modules ): array {
	$modules['mbstring']['extension'] = 'mbstring';
	if ( ! isset( $modules['mbstring']['required'] ) ) {
		$modules['mbstring']['required'] = false;
	}
	return $modules;
}
add_filter( 'site_status_test_php_modules', 'alphalisting_php_modules_health_check' );
