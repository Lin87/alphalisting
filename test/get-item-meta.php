<?php
declare(strict_types=1);

if ( ! defined('ABSPATH') ) {
    define('ABSPATH', __DIR__);
}

require_once __DIR__ . '/../src/Query.php';

use eslin87\AlphaListing\Query;

if ( ! class_exists('WP_Error') ) {
    class WP_Error {
        public function __construct( $code = '', $message = '' ) {
            $this->code = $code;
            $this->message = $message;
        }
    }
}

if ( ! function_exists('get_post_meta') ) {
    function get_post_meta( int $post_id, string $key = '', bool $single = false ) {
        return array(
            'type'   => 'post',
            'id'     => $post_id,
            'key'    => $key,
            'single' => $single,
        );
    }
}

if ( ! function_exists('get_term_meta') ) {
    function get_term_meta( int $term_id, string $key = '', bool $single = false ) {
        return array(
            'type'   => 'term',
            'id'     => $term_id,
            'key'    => $key,
            'single' => $single,
        );
    }
}

$reflection = new ReflectionClass( Query::class );
$query = $reflection->newInstanceWithoutConstructor();

$current_item_property = $reflection->getProperty( 'current_item' );
$current_item_property->setAccessible( true );

$current_item_property->setValue(
    $query,
    array(
        'item' => 'term:15',
    )
);

$term_meta = $query->get_item_meta( 'term_key', true );

if ( $term_meta['type'] !== 'term' || $term_meta['id'] !== 15 ) {
    throw new RuntimeException( 'Expected term meta to be returned for the "term" prefix.' );
}

$current_item_property->setValue(
    $query,
    array(
        'item' => 'terms:25',
    )
);

$terms_meta = $query->get_item_meta( 'term_key', false );

if ( $terms_meta['type'] !== 'term' || $terms_meta['id'] !== 25 ) {
    throw new RuntimeException( 'Expected term meta to be returned for the "terms" prefix.' );
}

$current_item_property->setValue(
    $query,
    array(
        'item' => 'post:35',
    )
);

$post_meta = $query->get_item_meta( 'post_key', true );

if ( $post_meta['type'] !== 'post' || $post_meta['id'] !== 35 ) {
    throw new RuntimeException( 'Expected post meta to be returned for the "post" prefix.' );
}

$current_item_property->setValue(
    $query,
    array(
        'item' => 'posts:45',
    )
);

$posts_meta = $query->get_item_meta( 'post_key', false );

if ( $posts_meta['type'] !== 'post' || $posts_meta['id'] !== 45 ) {
    throw new RuntimeException( 'Expected post meta to be returned for the "posts" prefix.' );
}

echo "get_item_meta() returns metadata for plural and singular prefixes.\n";
