<?php
declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
        define( 'ABSPATH', __DIR__ );
}

if ( ! class_exists( 'WP_Widget' ) ) {
        /**
         * Minimal WP_Widget stub for isolated tests.
         */
        class WP_Widget { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedClassFound
                public function __construct( $id_base = '', $name = '', $widget_options = array(), $control_options = array() ) {
                }

                public function get_field_id( $field_name ) {
                        return $field_name;
                }

                public function get_field_name( $field_name ) {
                        return $field_name;
                }
        }
}

if ( ! function_exists( 'add_action' ) ) {
        function add_action( $hook, $callback, $priority = 10, $accepted_args = 1 ) { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedFunctionFound
                return true;
        }
}

if ( ! function_exists( '__' ) ) {
        function __( $text, $domain = null ) { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedFunctionFound
                return $text;
        }
}

if ( ! function_exists( 'wp_strip_all_tags' ) ) {
        function wp_strip_all_tags( $text ) { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedFunctionFound
                return trim( strip_tags( (string) $text ) );
        }
}

require_once __DIR__ . '/../widgets/class-alphalisting-widget.php';

use eslin87\AlphaListing\AlphaListing_Widget;

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
 * Assert that a value is empty.
 *
 * @param mixed  $actual  Actual value.
 * @param string $message Error message on failure.
 * @return void
 */
function assert_empty( $actual, string $message ): void {
        if ( ! empty( $actual ) ) {
                throw new RuntimeException( $message . sprintf( ' Got %s.', var_export( $actual, true ) ) );
        }
}

$widget = new AlphaListing_Widget();

$old_instance = array(
        'all_children'     => 'true',
        'hide_empty_terms' => 'true',
        'parent_term'      => 'should reset',
        'terms'            => '1,2,3',
        'exclude_terms'    => '4,5',
);

$new_instance = array(
        'title'     => '  <strong>Updated Listing</strong> ',
        'type'      => 'terms',
        'post'      => '17',
        'post_type' => 'page',
        'taxonomy'  => 'category',
        // Intentionally omit optional text fields and checkboxes to confirm defaults.
);

$errors = array();
set_error_handler(
        function ( $errno, $errstr ) use ( &$errors ) {
                if ( in_array( $errno, array( E_WARNING, E_NOTICE, E_USER_WARNING, E_USER_NOTICE ), true ) ) {
                        $errors[] = $errstr;
                }

                return true;
        }
);

$updated = $widget->update( $new_instance, $old_instance );

restore_error_handler();

assert_empty( $errors, 'Widget update should not trigger notices or warnings when optional values are omitted.' );
assert_same( 'false', $updated['all_children'], 'Unchecked all_children checkbox should default to string false.' );
assert_same( 'false', $updated['hide_empty_terms'], 'Unchecked hide_empty_terms checkbox should default to string false.' );
assert_same( '', $updated['parent_term'], 'Missing parent term should sanitize to an empty string.' );
assert_same( '', $updated['terms'], 'Missing include terms should sanitize to an empty string.' );
assert_same( '', $updated['exclude_terms'], 'Missing exclude terms should sanitize to an empty string.' );

echo "AlphaListing widget update handles unchecked inputs without notices.\n";
