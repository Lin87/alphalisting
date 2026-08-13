<?php
/**
 * Validate last-word extraction, indexing, and sorting.
 *
 * Run with: php test/group-by-last-word.php
 */

declare(strict_types=1);

define( 'ABSPATH', __DIR__ . '/' );

class WP_Post {
    /**
     * Post title used by the test stubs.
     *
     * @var string
     */
    public $post_title;

    /**
     * Build a test post.
     *
     * @param string $post_title Post title.
     */
    public function __construct( string $post_title ) {
        $this->post_title = $post_title;
    }
}

function __( string $value ): string {
    return $value;
}

function apply_filters( string $hook, $value ) {
    return $value;
}

function wp_strip_all_tags( string $value ): string {
    return strip_tags( $value );
}

function get_the_title( $post ): string {
    return $post instanceof WP_Post ? $post->post_title : '';
}

function get_post( $post ) {
    return $post;
}

require_once dirname( __DIR__ ) . '/vendor/autoload.php';

use eslin87\AlphaListing\Alphabet;
use eslin87\AlphaListing\Shortcode\QueryParts\GroupBy;

$examples = array(
    'Yassmin Abdel-Magied'       => 'Abdel-Magied',
    'Francesco Barberis Canonico' => 'Canonico',
    'Raffaele La Capria'         => 'Capria',
    '  Marina Abramovic  '       => 'Abramovic',
    'David Adjaye, '             => 'Adjaye',
    'John Smith —'               => 'Smith',
    'John Smith 🎉'              => 'Smith',
    'John Smith — 🎉'            => 'Smith',
    '— 🎉'                       => '',
    '<em>André Aciman</em>'      => 'Aciman',
    ''                           => '',
);

foreach ( $examples as $title => $expected ) {
    $actual = GroupBy::get_last_word( $title );
    if ( $expected !== $actual ) {
        throw new RuntimeException( "Expected '$expected' for '$title'; received '$actual'." );
    }
}

$group_by = GroupBy::instance();
if ( 'last-word' !== $group_by->sanitize_attribute( 'last-word', array() ) ) {
    throw new RuntimeException( 'Expected the supported shortcode attribute value to be retained.' );
}
if ( '' !== $group_by->sanitize_attribute( 'unsupported', array() ) ) {
    throw new RuntimeException( 'Expected an unsupported shortcode attribute value to be disabled.' );
}

$block_attributes = json_decode( (string) file_get_contents( dirname( __DIR__ ) . '/scripts/blocks/attributes.json' ), true );
if ( 'last-word' !== $block_attributes['group-by']['enum'][1] || '' !== $block_attributes['group-by']['default'] ) {
    throw new RuntimeException( 'Expected the block to expose last-word grouping as an opt-in attribute.' );
}

$block_editor = (string) file_get_contents( dirname( __DIR__ ) . '/scripts/blocks/edit.js' );
if ( ! str_contains( $block_editor, "label={ __( 'Group by last word', 'alphalisting' ) }" )
    || ! str_contains( $block_editor, "'group-by': enabled ? 'last-word' : ''" ) ) {
    throw new RuntimeException( 'Expected the block to expose last-word grouping as a toggle.' );
}

$letters = $group_by->index_by_last_word(
    array( 'S' ),
    new WP_Post( 'John Smith' ),
    'posts',
    'Smith, John'
);
if ( array( 'J' ) !== $letters ) {
    throw new RuntimeException( 'Expected the post to be indexed by the filtered title.' );
}

$term_letters = $group_by->index_by_last_word( array( 'Y' ), new WP_Post( 'Yassmin Abdel-Magied' ), 'terms', 'Yassmin Abdel-Magied' );
if ( array( 'Y' ) !== $term_letters ) {
    throw new RuntimeException( 'Expected non-post listings to remain unchanged.' );
}

$alphabet = new Alphabet();
$titles   = array( 'David Adjaye', 'Marina Abramovic', 'Cristina Acidini' );
usort(
    $titles,
    function( string $first, string $second ) use ( $group_by, $alphabet ): int {
        return $group_by->sort_by_last_word( $alphabet->compare_strings( $first, $second ), $first, $second, $alphabet );
    }
);

$expected_titles = array( 'Marina Abramovic', 'Cristina Acidini', 'David Adjaye' );
if ( $expected_titles !== $titles ) {
    throw new RuntimeException( 'Expected titles to be sorted by last word.' );
}

$accented_titles = array( 'Anna Ézard', 'Bea éclair' );
usort(
    $accented_titles,
    function( string $first, string $second ) use ( $group_by, $alphabet ): int {
        return $group_by->sort_by_last_word( $alphabet->compare_strings( $first, $second ), $first, $second, $alphabet );
    }
);
if ( array( 'Bea éclair', 'Anna Ézard' ) !== $accented_titles ) {
    throw new RuntimeException( 'Expected equivalent accented letters to be sorted by their remaining characters.' );
}

echo "Group-by-last-word behavior is correct.\n";
