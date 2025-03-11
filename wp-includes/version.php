<?php
/**
 * AlphaListing version helper file for documentation site
 *
 * @package alphalisting
 */

 namespace eslin87\AlphaListing;

 if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

$alphalisting_path = dirname( __DIR__ );
$alphalisting_data = get_plugin_data( trailingslashit( $alphalisting_path ) . 'alphalisting.php', false, false );
$wp_version       = $alphalisting_data['Version'];