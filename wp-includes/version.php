<?php
/**
 * AlphaListing version helper file for documentation site
 *
 * @package alphalisting
 */

$a_z_listing_path = dirname( __DIR__ );
$a_z_listing_data = get_plugin_data( trailingslashit( $a_z_listing_path ) . 'alphalisting.php', false, false );
$wp_version       = $a_z_listing_data['Version'];