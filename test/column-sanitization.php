<?php
declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
        define( 'ABSPATH', __DIR__ );
}

require_once __DIR__ . '/../src/Extension.php';
require_once __DIR__ . '/../src/Singleton.php';
require_once __DIR__ . '/../src/Shortcode/Extension.php';
require_once __DIR__ . '/../src/Shortcode/QueryParts/Columns.php';
require_once __DIR__ . '/../src/Shortcode/QueryParts/ColumnGap.php';
require_once __DIR__ . '/../src/Shortcode/QueryParts/ColumnWidth.php';

use eslin87\AlphaListing\Shortcode\QueryParts\ColumnGap;
use eslin87\AlphaListing\Shortcode\QueryParts\Columns;
use eslin87\AlphaListing\Shortcode\QueryParts\ColumnWidth;

if ( ! function_exists( 'add_filter' ) ) {
        function add_filter( $hook, $callback, $priority = 10, $accepted_args = 1 ) { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedFunctionFound
                return true;
        }
}

if ( ! function_exists( 'remove_filter' ) ) {
        function remove_filter( $hook, $callback, $priority = 10, $accepted_args = 1 ) { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedFunctionFound
                return true;
        }
}

/**
 * Simple assertion helper.
 *
 * @param mixed  $expected Expected value.
 * @param mixed  $actual   Actual value.
 * @param string $message  Error message on failure.
 * @return void
 */
function assert_same( $expected, $actual, string $message ): void {
        if ( $expected !== $actual ) {
                throw new RuntimeException( $message . sprintf( ' Expected %s, got %s.', var_export( $expected, true ), var_export( $actual, true ) ) );
        }
}

/**
 * Assert that a string contains the expected substring.
 *
 * @param string $needle   Expected substring.
 * @param string $haystack Full string to inspect.
 * @param string $message  Error message on failure.
 * @return void
 */
function assert_contains( string $needle, string $haystack, string $message ): void {
        if ( strpos( $haystack, $needle ) === false ) {
                throw new RuntimeException( $message . sprintf( ' Expected %s within %s.', var_export( $needle, true ), var_export( $haystack, true ) ) );
        }
}

$columns = new Columns();
assert_same( 12, $columns->sanitize_attribute( '12', array() ), 'Column count should sanitize numeric strings.' );
assert_same( Columns::MAX_COLUMN_COUNT, $columns->sanitize_attribute( '999', array() ), 'Column count should clamp to the maximum.' );
assert_same( Columns::DEFAULT_COLUMN_COUNT, $columns->sanitize_attribute( 'calc(2*3)', array() ), 'Column count should fall back for invalid input.' );

$columns->shortcode_query( array(), 'posts', 'columns', '4', array() );
$column_count_styles = $columns->return_styles( ' ', null, 'test' );
assert_contains( '--alphalisting-column-count: 4;', $column_count_styles, 'Styles should include sanitized column count.' );

$columns_invalid = new Columns();
$columns_invalid->shortcode_query( array(), 'posts', 'columns', 'calc(2*3)', array() );
$column_count_invalid_styles = $columns_invalid->return_styles( ' ', null, 'test' );
assert_contains( '--alphalisting-column-count: ' . Columns::DEFAULT_COLUMN_COUNT . ';', $column_count_invalid_styles, 'Invalid column count should revert to default in styles.' );

$column_width = new ColumnWidth();
assert_same( '1200px', $column_width->sanitize_attribute( '1500px', array() ), 'Column width should clamp pixel values.' );
assert_same( ColumnWidth::DEFAULT_COLUMN_WIDTH, $column_width->sanitize_attribute( 'calc(10px)', array() ), 'Column width should reject unsafe tokens.' );
assert_same( '0', $column_width->sanitize_attribute( '0', array() ), 'Column width should allow unitless zero.' );

$column_width->shortcode_query( array(), 'posts', 'column-width', '1500px', array() );
$column_width_styles = $column_width->return_styles( ' ', null, 'test' );
assert_contains( '--alphalisting-column-width: 1200px;', $column_width_styles, 'Styles should reflect clamped column width.' );

$column_width_zero = new ColumnWidth();
$column_width_zero->shortcode_query( array(), 'posts', 'column-width', '0', array() );
$column_width_zero_styles = $column_width_zero->return_styles( ' ', null, 'test' );
assert_contains( '--alphalisting-column-width: 0;', $column_width_zero_styles, 'Unitless zero column width should be preserved.' );

$column_gap = new ColumnGap();
assert_same( '100%', $column_gap->sanitize_attribute( '500%', array() ), 'Column gap should clamp percentage values.' );
assert_same( ColumnGap::DEFAULT_COLUMN_GAP, $column_gap->sanitize_attribute( 'url(javascript:alert(1))', array() ), 'Column gap should reject unsafe tokens.' );
assert_same( '0', $column_gap->sanitize_attribute( '0', array() ), 'Column gap should allow unitless zero.' );

$column_gap->shortcode_query( array(), 'posts', 'column-gap', '12%', array() );
$column_gap_styles = $column_gap->return_styles( ' ', null, 'test' );
assert_contains( '--alphalisting-column-gap: 12%;', $column_gap_styles, 'Styles should use sanitized column gap.' );

$column_gap_zero = new ColumnGap();
$column_gap_zero->shortcode_query( array(), 'posts', 'column-gap', '0', array() );
$column_gap_zero_styles = $column_gap_zero->return_styles( ' ', null, 'test' );
assert_contains( '--alphalisting-column-gap: 0;', $column_gap_zero_styles, 'Unitless zero column gap should be preserved.' );

$column_gap_invalid = new ColumnGap();
$column_gap_invalid->shortcode_query( array(), 'posts', 'column-gap', 'calc(1+1)', array() );
$column_gap_invalid_styles = $column_gap_invalid->return_styles( ' ', null, 'test' );
assert_contains( '--alphalisting-column-gap: ' . ColumnGap::DEFAULT_COLUMN_GAP . ';', $column_gap_invalid_styles, 'Invalid column gap should revert to default in styles.' );

// Extra coverage for rem/em rounding.
$column_gap_rounding = new ColumnGap();
assert_same( '0.125em', $column_gap_rounding->sanitize_attribute( '0.1250em', array() ), 'Column gap should normalize fractional em values.' );

echo "Column-related shortcode attributes are sanitized and safe.\n";
