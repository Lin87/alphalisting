<?php
declare(strict_types=1);

if ( ! defined('ABSPATH') ) {
    define('ABSPATH', __DIR__);
}

require_once __DIR__ . '/../src/Extension.php';
require_once __DIR__ . '/../src/Singleton.php';
require_once __DIR__ . '/../src/Shortcode/Extension.php';

use eslin87\AlphaListing\Shortcode\Extension as BaseExtension;

$GLOBALS['wp_hooks'] = array(
    'filter' => array(),
    'action' => array(),
);

if ( ! function_exists('add_filter') ) {
    function add_filter( string $tag, callable $function_to_add, int $priority = 10, int $accepted_args = 1 ): void {
        $GLOBALS['wp_hooks']['filter'][] = array( $tag, $function_to_add, $priority, $accepted_args );
    }
}

if ( ! function_exists('remove_filter') ) {
    function remove_filter( string $tag, callable $function_to_remove, int $priority = 10, int $accepted_args = 1 ): void {
        $GLOBALS['wp_hooks']['filter'] = array_values(
            array_filter(
                $GLOBALS['wp_hooks']['filter'],
                static function ( array $hook ) use ( $tag, $function_to_remove, $priority, $accepted_args ): bool {
                    return $hook !== array( $tag, $function_to_remove, $priority, $accepted_args );
                }
            )
        );
    }
}

if ( ! function_exists('add_action') ) {
    function add_action( string $tag, callable $function_to_add, int $priority = 10, int $accepted_args = 1 ): void {
        $GLOBALS['wp_hooks']['action'][] = array( $tag, $function_to_add, $priority, $accepted_args );
    }
}

if ( ! function_exists('remove_action') ) {
    function remove_action( string $tag, callable $function_to_remove, int $priority = 10, int $accepted_args = 1 ): void {
        $GLOBALS['wp_hooks']['action'] = array_values(
            array_filter(
                $GLOBALS['wp_hooks']['action'],
                static function ( array $hook ) use ( $tag, $function_to_remove, $priority, $accepted_args ): bool {
                    return $hook !== array( $tag, $function_to_remove, $priority, $accepted_args );
                }
            )
        );
    }
}

class DummyExtension extends BaseExtension {
    public function registerFilter( callable $callable ): void {
        $this->add_hook( 'filter', 'dummy_filter', $callable, 10, 1 );
    }

    public function unregisterFilter( callable $callable ): void {
        $this->remove_hook( 'filter', 'dummy_filter', $callable, 10, 1 );
    }

    public function getHooks(): array {
        return $this->hooks;
    }
}

$extension = new DummyExtension();

$callback = static function ( $value ) {
    return $value;
};

$extension->registerFilter( $callback );

$hooks = $extension->getHooks();

if ( count( $hooks['filter'] ) !== 1 ) {
    throw new RuntimeException( 'Expected one registered filter in the extension.' );
}

if ( count( $GLOBALS['wp_hooks']['filter'] ) !== 1 ) {
    throw new RuntimeException( 'Expected one registered filter in WordPress hooks.' );
}

$extension->unregisterFilter( $callback );

$hooks = $extension->getHooks();

if ( ! empty( $hooks['filter'] ) ) {
    throw new RuntimeException( 'Expected no registered filters in the extension after removal.' );
}

if ( ! empty( $GLOBALS['wp_hooks']['filter'] ) ) {
    throw new RuntimeException( 'Expected no registered filters in WordPress hooks after removal.' );
}

fwrite( STDOUT, "remove_hook() removed the hook and updated internal state.\n" );
