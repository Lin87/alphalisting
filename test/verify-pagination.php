<?php
error_reporting(E_ALL & ~E_DEPRECATED);
\define('ABSPATH', true);
$autoload = __DIR__ . '/../vendor/autoload.php';
if (file_exists($autoload)) {
    require $autoload;
}

require_once __DIR__ . '/../src/Strings.php';
require_once __DIR__ . '/../src/Alphabet.php';
require_once __DIR__ . '/../src/Query.php';

if (!function_exists('apply_filters')) {
    function apply_filters(string $hook_name, $value, ...$args) {
        global $alphalisting_processed_posts;
        global $alphabet;
        if ('alphalisting_extract_item_indices' === $hook_name) {
            $item = $args[0];
            if (null === $item) {
                return array();
            }
            $alphalisting_processed_posts[] = $item->ID;
            $letter = 0 === $item->ID % 2 ? $alphabet->get_unknown_letter() : 'A';
            return array(
                $letter => array(
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
    private $upper_bound = 0;
    private static $instantiation_count = 0;

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
        $paged                = isset($this->query['paged']) ? max(1, (int) $this->query['paged']) : 1;
        $offset               = (int) $this->query['offset'];

        ++self::$instantiation_count;
        if (1 === self::$instantiation_count) {
            $start = $offset;
        } else {
            $start = $offset + ($paged - 1) * $this->posts_per_page;
        }

        if ($start < 0) {
            $start = 0;
        }

        $this->pointer      = $start;
        $this->upper_bound  = $this->found_posts;
    }

    public static function reset_test_state(): void {
        self::$instantiation_count = 0;
    }

    public function the_post() {
        global $post;
        if ($this->pointer >= $this->upper_bound) {
            $post = null;
            return null;
        }
        $post = $this->dataset[$this->pointer];
        ++$this->pointer;
        return $post;
    }
}

function create_dataset(int $count): array {
    $dataset = array();
    for ($i = 1; $i <= $count; ++$i) {
        $post             = new stdClass();
        $post->ID         = $i;
        $post->post_title = 'Post ' . $i;
        $dataset[]        = $post;
    }
    return $dataset;
}

function run_pagination_verification(array $dataset, array $query_args, array $expected_ids, string $failure_message): void {
    global $alphabet;
    global $alphalisting_processed_posts;
    global $alphalisting_test_dataset;

    $alphalisting_test_dataset = $dataset;
    WP_Query::reset_test_state();
    $initial_query             = new WP_Query($query_args);

    $alphabet = new TestAlphabet();

    $ref    = new ReflectionClass(\eslin87\AlphaListing\Query::class);
    $query  = $ref->newInstanceWithoutConstructor();
    $method = $ref->getMethod('get_all_indices');
    $method->setAccessible(true);

    $setup = \Closure::bind(function ($instance) use ($alphabet, $initial_query) {
        $instance->alphabet = $alphabet;
        $instance->query    = $initial_query;
        $instance->items    = array();
        $instance->unknown_letters = '#';
    }, null, \eslin87\AlphaListing\Query::class);
    $setup($query);

    $alphalisting_processed_posts = array();
    $result = $method->invoke($query, array());

    sort($alphalisting_processed_posts);

    if ($alphalisting_processed_posts !== $expected_ids) {
        fwrite(STDERR, $failure_message . "\n");
        exit(1);
    }

    $unknown_letter      = $alphabet->get_unknown_letter();
    $expected_unknown    = array_values(array_filter($expected_ids, function ($id) {
        return 0 === $id % 2;
    }));
    $expected_known = count($expected_ids) - count($expected_unknown);

    if (!isset($result['A']) || count($result['A']) !== $expected_known) {
        fwrite(STDERR, "Unexpected index results for letter A.\n");
        exit(1);
    }

    if (!isset($result[$unknown_letter]) || count($result[$unknown_letter]) !== count($expected_unknown)) {
        fwrite(STDERR, "Unexpected index results for the unknown letter.\n");
        exit(1);
    }

    $total_indexed = array_sum(array_map('count', $result));
    if ($total_indexed !== count($expected_ids)) {
        fwrite(STDERR, "Unexpected total indexed item count.\n");
        exit(1);
    }
}

// Baseline scenario where the initial query starts on the first page.
run_pagination_verification(
    create_dataset(25),
    array(
        'posts_per_page' => 10,
    ),
    range(1, 25),
    'Not all posts were indexed for the baseline dataset.'
);

// Scenario where the original query begins on a later page.
run_pagination_verification(
    create_dataset(15),
    array(
        'posts_per_page' => 5,
        'offset'         => 5,
        'paged'          => 2,
    ),
    range(6, 15),
    'Posts were skipped when the original query started on a later page.'
);

echo "All posts indexed successfully.\n";
