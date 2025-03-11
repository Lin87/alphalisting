<?php
/**
 * Support functions for the A-Z Index page
 *
 * @package  alphalisting
 */

declare(strict_types=1);

namespace AlphaListing;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'alphalisting_log', 'alphalisting_log' );

/**
 * AlphaListing Logging wrapper function.
 *
 * @since 4.0.0
 * @return void
 */
function alphalisting_log() {
	do_action_ref_array( 'log', func_get_args() ); //phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound
}

/**
 * Retrieve whether the query has any letters left
 *
 * @since 0.7
 * @param  array|string|WP_Query|\AlphaListing\Query $query a valid WordPress query or an AlphaListing\Query instance.
 * @return bool whether there are letters still to be iterated-over
 */
function have_a_z_letters( $query = null ): bool {
	return alphalisting_cache( $query )->have_letters();
}

/**
 * Whether the query has any posts left for the current letter
 *
 * @since 0.7
 * @since 0.8.0 deprecated
 * @see have_a_z_items()
 * @deprecated use have_a_z_items()
 */
function have_a_z_posts(): bool {
	_deprecated_function( __FUNCTION__, '0.8.0', 'have_a_z_items' );
	return have_a_z_items();
}

/**
 * Whether the query has any posts left for the current letter
 *
 * @since 0.8.0
 * @param  array|string|WP_Query|\AlphaListing\Query $query a valid WordPress query or an AlphaListing\Query instance.
 * @return bool whether there are still posts available
 */
function have_a_z_items( $query = null ): bool {
	return alphalisting_cache( $query )->have_items();
}

/**
 * Proceed to the next letter
 *
 * @since 0.7
 * @param array|string|WP_Query|\AlphaListing\Query $query a valid WordPress query or an AlphaListing\Query instance.
 * @return void
 */
function the_a_z_letter( $query = null ) {
	alphalisting_cache( $query )->the_letter();
}

/**
 * Proceed to the next letter
 *
 * @since 0.7
 * @since 0.8.0 deprecated
 * @see the_a_z_item()
 * @deprecated use the_a_z_item()
 * @return void
 */
function the_a_z_post() {
	_deprecated_function( __FUNCTION__, '0.8.0', 'the_a_z_item' );
	the_a_z_item();
}

/**
 * Proceed to the next post
 *
 * @since 0.8.0
 * @param array|string|WP_Query|\AlphaListing\Query $query a valid WordPress query or an AlphaListing\Query instance.
 * @return void
 */
function the_a_z_item( $query = null ) {
	alphalisting_cache( $query )->the_item();
}

/**
 * Retrieve the number of posts for the letter
 *
 * @since 0.7
 * @since 1.0.0 deprecated
 * @see get_the_a_z_letter_count()
 * @deprecated use get_the_a_z_letter_count()
 */
function num_a_z_letters(): int {
	_deprecated_function( __FUNCTION__, '1.0.0', 'get_the_a_z_letter_count' );
	return get_the_a_z_letter_count();
}

/**
 * Retrieve the number of posts for the letter
 *
 * @since 0.7
 * @since 1.0.0 deprecated
 * @see get_the_a_z_letter_count()
 * @deprecated use get_the_a_z_letter_count()
 */
function num_a_z_posts(): int {
	_deprecated_function( __FUNCTION__, '1.0.0', 'get_the_a_z_letter_count' );
	return get_the_a_z_letter_count();
}

/**
 * Retrieve the number of posts for the letter
 *
 * @since 0.7
 * @since 1.0.0 deprecated
 * @see get_the_a_z_letter_count()
 * @deprecated use get_the_a_z_letter_count()
 */
function num_a_z_items(): int {
	_deprecated_function( __FUNCTION__, '1.0.0', 'get_the_a_z_letter_count' );
	return get_the_a_z_letter_count();
}

/**
 * Print the number of letters for the query
 *
 * @since 1.0.0
 * @param array|string|WP_Query|\AlphaListing\Query $query a valid WordPress query or an AlphaListing\Query instance.
 */
function the_a_z_letter_count( $query = null ) {
	echo esc_html( alphalisting_cache( $query )->num_letters() );
}

/**
 * Retrieve the number of items for the current letter
 *
 * @since 1.0.0
 * @param  array|string|WP_Query|\AlphaListing\Query $query a valid WordPress query or an AlphaListing\Query instance.
 * @return int the number of letters
 */
function get_the_a_z_letter_count( $query = null ): int {
	return alphalisting_cache( $query )->num_letters();
}

/**
 * Print the current letter ID
 *
 * @since 0.7
 * @param array|string|WP_Query|\AlphaListing\Query $query either a valid WordPress query or an AlphaListing\Query instance.
 * @return void
 */
function the_a_z_letter_id( $query = null ) {
	alphalisting_cache( $query )->the_letter_id();
}

/**
 * Retrieve the current letter ID
 *
 * @since 0.7
 * @param  array|string|WP_Query|\AlphaListing\Query $query either a valid WordPress query or an AlphaListing\Query instance.
 * @return string the current letter ID
 */
function get_the_a_z_letter_id( $query = null ): string {
	return alphalisting_cache( $query )->get_the_letter_id();
}

/**
 * Print the current letter title
 *
 * @since 0.7
 * @param array|string|WP_Query|\AlphaListing\Query $query either a valid WordPress query or an AlphaListing\Query instance.
 * @return void
 */
function the_a_z_letter_title( $query = null ) {
	alphalisting_cache( $query )->the_letter_title();
}

/**
 * Retrieve the current letter title
 *
 * @since 0.7
 * @param  array|string|WP_Query|\AlphaListing\Query $query either a valid WordPress query or an AlphaListing\Query instance.
 * @return string the letter title
 */
function get_the_a_z_letter_title( $query = null ): string {
	return alphalisting_cache( $query )->get_the_letter_title();
}

/**
 * Print the current item title
 *
 * @since 0.8.0
 * @param array|string|WP_Query|\AlphaListing\Query $query either a valid WordPress query or an AlphaListing\Query instance.
 * @return void
 */
function the_a_z_item_title( $query = null ) {
	alphalisting_cache( $query )->the_title();
}

/**
 * Retrieve the current item title
 *
 * @since 0.8.0
 * @param  array|string|WP_Query|\AlphaListing\Query $query either a valid WordPress query or an AlphaListing\Query instance.
 * @return string the post or taxonomy-term title
 */
function get_the_a_z_item_title( $query = null ): string {
	return alphalisting_cache( $query )->get_the_title();
}

/**
 * Print the current item permalink
 *
 * @since 0.8.0
 * @param array|string|WP_Query|\AlphaListing\Query $query either a valid WordPress query or an AlphaListing\Query instance.
 * @return void
 */
function the_a_z_item_permalink( $query = null ) {
	alphalisting_cache( $query )->the_permalink();
}

/**
 * Retrieve the current item permalink
 *
 * @since 0.8.0
 * @param  array|string|WP_Query|\AlphaListing\Query $query either a valid WordPress query or an AlphaListing\Query instance.
 * @return string the permalink
 */
function get_the_a_z_item_permalink( $query = null ): string {
	return alphalisting_cache( $query )->get_the_permalink();
}

/**
 * Print the A-Z Index page content
 *
 * @since 0.1
 * @since 0.8.0 deprecated
 * @see the_alphalisting()
 * @deprecated use the_alphalisting()
 * @param array|string|WP_Query|\AlphaListing\Query $query either a valid WordPress query or an AlphaListing\Query instance.
 * @return void
 */
function the_az_listing( $query = null ) {
	_deprecated_function( __FUNCTION__, '0.8.0', 'the_alphalisting' );
	the_alphalisting( $query );
}

/**
 * Print the A-Z Index page content
 *
 * @since 0.8.0
 * @param array|string|WP_Query|\AlphaListing\Query $query     a valid WordPress query or an AlphaListing\Query instance.
 * @param bool                                     $use_cache use the plugin's in-built query cache.
 * @return void
 */
function the_alphalisting( $query = null, $use_cache = true ) {
	alphalisting_cache( $query, '', $use_cache )->the_listing();
}

/**
 * Retrieve the A-Z Index page content
 *
 * @since 0.1
 * @since 0.8.0 deprecated
 * @see get_the_alphalisting()
 * @deprecated use get_the_alphalisting()
 * @param array|string|WP_Query|\AlphaListing\Query $query either a valid WordPress query or an AlphaListing\Query instance.
 * @return string The listing html content ready for echoing to the page.
 */
function get_the_az_listing( $query = null ): string {
	_deprecated_function( __FUNCTION__, '0.8.0', 'get_the_alphalisting' );
	return get_the_alphalisting( $query );
}

/**
 * Retrieve the index of posts ordered and segmented alphabetically
 *
 * @since 0.8.0
 * @param  array|string|WP_Query|\AlphaListing\Query $query     a valid WordPress query or an AlphaListing\Query instance.
 * @param  bool                                     $use_cache use the plugin's in-built query cache.
 * @return string The listing html content ready for echoing to the page.
 */
function get_the_alphalisting( $query = null, $use_cache = true ): string {
	return alphalisting_cache( $query, '', $use_cache )->get_the_listing();
}

/**
 * Print the A-Z Letter list
 *
 * @since 0.7
 * @since 0.8.0 deprecated
 * @see the_a_z_letters()
 * @deprecated use the_a_z_letters()
 * @param array|string|WP_Query|\AlphaListing\Query $query either a valid WordPress query or an AlphaListing\Query instance.
 * @param bool|string                              $target URL of the page to send the browser when a letter is clicked.
 * @param string                                   $styling unused.
 * @return void
 */
function the_az_letters( $query = null, $target = false, string $styling = '' ) {
	_deprecated_function( __FUNCTION__, '0.8.0', 'the_a_z_letters' );
	the_a_z_letters( $query, $target, $styling );
}

/**
 * Print the A-Z Letter list
 *
 * @since 0.8.0
 * @param array|string|WP_Query|\AlphaListing\Query $query a valid WordPress query or an AlphaListing\Query instance.
 * @param bool|string                              $target URL of the page to send the browser when a letter is clicked.
 * @param string                                   $styling unused.
 * @param bool                                     $use_cache use the plugin's in-built query cache.
 * @return void
 */
function the_a_z_letters( $query = null, string $target = '', string $styling = '', bool $use_cache = true ) {
	echo get_the_a_z_letters( $query, $target, $styling, $use_cache ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}

/**
 * Retrieve the A-Z Letter list
 *
 * @since 0.7
 * @since 0.8.0 deprecated
 * @see get_the_a_z_letters()
 * @deprecated use get_the_a_z_letters()
 * @param array|string|WP_Query|\AlphaListing\Query $query a valid WordPress query or an AlphaListing\Query instance.
 * @param bool|string                              $target URL of the page to send the browser when a letter is clicked.
 * @param string                                   $styling unused.
 * @return string HTML ready for echoing containing the list of A-Z letters with anchor links to the A-Z Index page.
 */
function get_the_az_letters( $query = null, string $target = '', string $styling = '' ): string {
	_deprecated_function( __FUNCTION__, '0.8.0', 'get_the_a_z_letters' );
	return get_the_a_z_letters( $query, $target, $styling );
}

/**
 * Retrieve the A-Z Letter list
 *
 * @since 0.8.0
 * @param array|string|WP_Query|\AlphaListing\Query $query a valid WordPress query or an AlphaListing\Query instance.
 * @param bool|string                              $target URL of the page to send the browser when a letter is clicked.
 * @param string                                   $styling unused.
 * @param bool                                     $use_cache use the plugin's in-built query cache.
 * @return string HTML ready for echoing containing the list of A-Z letters with anchor links to the A-Z Index page.
 */
function get_the_a_z_letters( $query = null, string $target = '', string $styling = '', bool $use_cache = true ): string {
	return alphalisting_cache( $query, '', $use_cache )->get_the_letters( $target, $styling );
}

/**
 * Get a saved copy of the AlphaListing instance if we have one, or make a new one and save it for later
 *
 * @param array|string|WP_Query|\AlphaListing\Query $query     A valid WordPress query or an AlphaListing instance.
 * @param string                                   $type      The type of items displayed in the listing: 'terms' or 'posts'.
 * @param bool                                     $use_cache Try to use a caching plugin. See https://alphalisting.com/ for the caching plugin we created to work with this feature.
 * @return \AlphaListing\Query A new or previously-saved instance of AlphaListing using the provided construct_query
 */
function alphalisting_cache( $query = null, string $type = '', bool $use_cache = true ) {
	return new \AlphaListing\Query( $query, $type, $use_cache );
}

/**
 * Check value for truthiness
 *
 * @since 2.1.0
 * @param string|int|bool $value The value to check for thruthiness.
 * @return bool The truthiness of the value.
 */
function alphalisting_is_truthy( $value ): bool {
	if ( '1' === $value ||
		'on' === $value ||
		'yes' === $value ||
		'true' === $value ||
		1 === $value ||
		true === $value
	) {
		return true;
	} else {
		return false;
	}
}
