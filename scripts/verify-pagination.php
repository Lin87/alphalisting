<?php
error_reporting(E_ALL & ~E_DEPRECATED);
\define('ABSPATH', true);
require __DIR__ . '/../vendor/autoload.php';

if (!function_exists('apply_filters')) {
    function apply_filters(string $hook_name, $value, ...$args) {
        global $alphalisting_processed_posts;
        if ('alphalisting_extract_item_indices' === $hook_name) {
            $item = $args[0];
            if (null === $item) {
                return array();
            }
            $alphalisting_processed_posts[] = $item->ID;
            return array(
                'A' => array(
                    array(
                        'title'     => $item->post_title,
                        'permalink' => 'post-' . $item->ID,
                    ),
                ),
            );
        }
        return $value;
    }
}

if (!function_exists('do_action')) {
    function do_action(string $hook_name, ...$args) {
        // no-op for testing.
    }
}

if (!function_exists('wp_reset_postdata')) {
    function wp_reset_postdata() {
        // no-op for testing.
    }
}

class TestAlphabet implements \ArrayAccess {
    public $unknown_letter = '#';
    public $unknown_letter_is_first = false;
    public $alphabet_keys = array('A');

    private $storage = array();

    public function offsetExists($offset): bool {
        return isset($this->storage[$offset]);
    }

    public function offsetGet($offset): mixed {
        return $this->storage[$offset] ?? null;
    }

    public function offsetSet($offset, $value): void {
        if (null === $offset) {
            $this->storage[] = $value;
            return;
        }
        $this->storage[$offset] = $value;
    }

    public function offsetUnset($offset): void {
        unset($this->storage[$offset]);
    }

    public function get_letter_for_key(string $key): string {
        return 'a' === strtolower($key) ? 'A' : $this->unknown_letter;
    }

    public function get_unknown_letter(): string {
        return $this->unknown_letter;
    }

    public function chars(bool $include_unknown = false): array {
        $chars = array('A');
        if ($include_unknown) {
            $chars[] = $this->unknown_letter;
        }
        return $chars;
    }

    public function count(bool $include_unknown = false): int {
        return count($this->chars($include_unknown));
    }

    public function loop(callable $callback, bool $include_unknown = false): void {
        foreach ($this->chars($include_unknown) as $letter) {
            $callback($letter);
        }
    }
}

class WP_Query {
    public $posts_per_page;
    public $found_posts;
    public $query;

    private $dataset;
    private $pointer = 0;

    public function __construct(array $args) {
        global $alphalisting_test_dataset;
        $defaults = array(
            'posts_per_page' => 10,
            'offset'         => 0,
        );
        $this->query          = array_merge($defaults, $args);
        $this->posts_per_page = $this->query['posts_per_page'];
        $this->dataset        = $alphalisting_test_dataset;
        $this->found_posts    = count($this->dataset);
        $this->pointer        = $this->query['offset'];
    }

    public function the_post() {
        global $post;
        if ($this->pointer >= $this->found_posts) {
            $post = null;
            return null;
        }
        $post = $this->dataset[$this->pointer];
        ++$this->pointer;
        return $post;
    }
}

$alphalisting_test_dataset = array();
for ($i = 1; $i <= 25; ++$i) {
    $post            = new stdClass();
    $post->ID        = $i;
    $post->post_title = 'Post ' . $i;
    $alphalisting_test_dataset[] = $post;
}

$initial_query = new WP_Query(array(
    'posts_per_page' => 10,
));

$alphabet = new TestAlphabet();

$ref   = new ReflectionClass(\eslin87\AlphaListing\Query::class);
$query = $ref->newInstanceWithoutConstructor();

$setup = \Closure::bind(function ($instance) use ($alphabet, $initial_query) {
    $instance->alphabet = $alphabet;
    $instance->query    = $initial_query;
    $instance->items    = array();
    $instance->unknown_letters = '#';
}, null, \eslin87\AlphaListing\Query::class);
$setup($query);

$method = $ref->getMethod('get_all_indices');
$method->setAccessible(true);

$alphalisting_processed_posts = array();
$result = $method->invoke($query, array());

sort($alphalisting_processed_posts);
$all_posts_present = $alphalisting_processed_posts === range(1, 25);

if (!$all_posts_present) {
    fwrite(STDERR, "Not all posts were indexed.\n");
    exit(1);
}

if (!isset($result['A']) || count($result['A']) !== 25) {
    fwrite(STDERR, "Unexpected index results.\n");
    exit(1);
}

echo "All posts indexed successfully.\n";
