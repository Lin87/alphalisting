<?php
// Simple verification script to ensure REST arguments are merged correctly.

define('ABSPATH', __DIR__);

if (!function_exists('__')) {
        function __($text, $domain = null) {
                return $text;
        }
}

if (!function_exists('add_action')) {
        function add_action($hook, $callback) {
                // No-op for testing.
        }
}

$GLOBALS['alphalisting_routes'] = array();

if (!function_exists('register_rest_route')) {
        function register_rest_route($namespace, $route, $config) {
                $GLOBALS['alphalisting_routes']["{$namespace}{$route}"] = $config;
        }
}

require_once __DIR__ . '/../wp-api/api.php';

\eslin87\AlphaListing\alphalisting_register_rest_api();

function assert_has_arg($route, $arg) {
        if (!isset($GLOBALS['alphalisting_routes'][$route])) {
                throw new RuntimeException("Route {$route} was not registered");
        }

        $args = $GLOBALS['alphalisting_routes'][$route]['args'] ?? array();
        if (!array_key_exists($arg, $args)) {
                throw new RuntimeException("Missing expected argument '{$arg}' for route {$route}");
        }

        return $args[$arg];
}

$posts_route = 'alphalisting/v1/posts/(?P<post_type>[a-z0-9-]+)';
$terms_route = 'alphalisting/v1/terms/(?P<taxonomy>[a-z0-9-]+)';

$captured_definitions = array(
        'grouping'       => assert_has_arg($posts_route, 'grouping'),
        'alphabet'       => assert_has_arg($posts_route, 'alphabet'),
        'include-styles' => assert_has_arg($posts_route, 'include-styles'),
);

assert_has_arg($terms_route, 'grouping');
assert_has_arg($terms_route, 'alphabet');
assert_has_arg($terms_route, 'include-styles');

$expected_validations = array(
        'grouping'       => 'integer',
        'alphabet'       => 'string',
        'include-styles' => 'boolean',
);

foreach ($expected_validations as $key => $type) {
        $definition = $captured_definitions[$key];
        if (!isset($definition['type']) || $definition['type'] !== $type) {
                throw new RuntimeException("Argument {$key} is not validated as {$type}");
        }
}

echo "REST arguments merged and validated successfully.\n";
