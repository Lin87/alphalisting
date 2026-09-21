<?php
/**
 * Ignore Articles Query Part.
 *
 * @package alphalisting
 */

declare(strict_types=1);

namespace eslin87\AlphaListing\Shortcode\QueryParts;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use \eslin87\AlphaListing\Alphabet;
use \eslin87\AlphaListing\Shortcode\Extension;
use \eslin87\AlphaListing\Strings;

/**
 * File and sort posts by the first word after a leading article.
 *
 * The article is only ignored when choosing the index letter and when sorting -- the
 * complete title is still displayed, so "The Great Gatsby" appears under G but still
 * reads "The Great Gatsby".
 */
class IgnoreArticles extends Extension {
	/**
	 * The attribute for this Query Part.
	 *
	 * @var string
	 */
	public $attribute_name = 'ignore-articles';

	/**
	 * The listing types supported by this Query Part.
	 *
	 * @var array<string>
	 */
	public $display_types = array( 'posts' );

	/**
	 * The language whose articles are being ignored for the listing being rendered.
	 *
	 * @var string
	 */
	protected $language = '';

	/**
	 * Articles to ignore, keyed by language code. Values must be lower-case.
	 *
	 * Articles ending in an apostrophe are elided forms -- they attach directly to the
	 * following word, with no space, and are matched separately. See match_elision().
	 *
	 * @var array<string,array<string>>
	 */
	const ARTICLES = array(
		'en' => array( 'a', 'an', 'the' ),
		'fr' => array( 'le', 'la', 'les', 'un', 'une', 'des', "l'" ),
		'es' => array( 'el', 'la', 'los', 'las', 'un', 'una', 'unos', 'unas' ),
		'it' => array( 'il', 'lo', 'la', 'i', 'gli', 'le', 'un', 'uno', 'una', "l'", "un'" ),
	);

	/**
	 * Apostrophe characters an elided article may be written with.
	 *
	 * U+2019 is what wptexturize() stores in post titles, so it is the common case rather
	 * than the exception.
	 *
	 * @var string
	 */
	const APOSTROPHES = '\x{27}\x{2019}\x{02BC}';

	/**
	 * Values accepted as a shorthand for English.
	 *
	 * @var array<string>
	 */
	const ENGLISH_ALIASES = array( 'true', 'yes', '1', 'on', 'en' );

	/**
	 * Clear the language left over from a previous listing.
	 *
	 * Runs on alphalisting_shortcode_start, which fires before the attributes are
	 * sanitized and the query is built, so each listing starts from a clean slate.
	 *
	 * @return void
	 */
	public function handler() {
		$this->language = '';
	}

	/**
	 * Get the articles for a language.
	 *
	 * @param string $language The language code.
	 * @return array<string> The lower-case articles to ignore, empty for an unknown language.
	 */
	public static function get_articles( string $language ): array {
		$articles = self::ARTICLES[ $language ] ?? array();

		/**
		 * Filter the articles ignored for a language.
		 *
		 * Returning a non-empty array for an unrecognised code also makes that code a
		 * valid value for the `ignore-articles` attribute.
		 *
		 * @param array<string> $articles The lower-case articles to ignore.
		 * @param string        $language The language code.
		 */
		$articles = apply_filters( 'alphalisting_articles_for_language', $articles, $language );

		if ( ! is_array( $articles ) ) {
			return array();
		}

		return array_filter( array_map( 'strtolower', array_map( 'strval', $articles ) ) );
	}

	/**
	 * Sanitize the language code.
	 *
	 * @param mixed $value      The value of the shortcode attribute.
	 * @param array $attributes The complete set of shortcode attributes.
	 * @return string The language code, or an empty string to leave articles in place.
	 */
	public function sanitize_attribute( $value, array $attributes ): string {
		if ( is_bool( $value ) ) {
			$value = $value ? 'en' : '';
		}

		$value = is_scalar( $value ) ? strtolower( trim( strval( $value ) ) ) : '';

		if ( in_array( $value, self::ENGLISH_ALIASES, true ) ) {
			return 'en';
		}

		return array() !== self::get_articles( $value ) ? $value : '';
	}

	/**
	 * Ignore leading articles for post listings.
	 *
	 * @param mixed  $query      The query.
	 * @param string $display    The display/query type.
	 * @param string $key        The name of the attribute.
	 * @param mixed  $value      The shortcode attribute value.
	 * @param array  $attributes The complete set of shortcode attributes.
	 * @return mixed
	 */
	public function shortcode_query_for_display_and_attribute( $query, string $display, string $key, $value, array $attributes ) {
		$language = is_string( $value ) ? $value : '';

		if ( '' === $language || array() === self::get_articles( $language ) ) {
			return $query;
		}

		// GroupBy hooks the same two filters and this Query Part is registered after it,
		// so we would silently override last-word grouping. Stand down instead.
		if ( 'last-word' === ( $attributes['group-by'] ?? '' ) ) {
			return $query;
		}

		$this->language = $language;

		$this->add_hook( 'filter', 'alphalisting_item_index_letter', array( $this, 'index_without_article' ), 10, 4 );
		$this->add_hook( 'filter', 'alphalisting_item_sorting_comparator', array( $this, 'sort_without_articles' ), 10, 4 );

		if ( is_array( $query ) ) {
			// Distinguish this result from the default ordering in external cache implementations.
			$query['_alphalisting_ignore_articles'] = $language;
		}

		return $query;
	}

	/**
	 * Return the index letter from the title with its leading article removed.
	 *
	 * @param array  $letters Existing index letters.
	 * @param mixed  $item    The post object or ID.
	 * @param string $type    The listing type.
	 * @param string $title   The filtered title used to index the item.
	 * @return array
	 */
	public function index_without_article( array $letters, $item, string $type, string $title = '' ): array {
		if ( 'posts' !== $type || '' === $title || '' === $this->language ) {
			return $letters;
		}

		$stripped = self::strip_leading_article( $title, $this->language );

		if ( '' === $stripped || $stripped === $title ) {
			return $letters;
		}

		return array( Strings::maybe_mb_substr( $stripped, 0, 1 ) );
	}

	/**
	 * Sort titles as though they did not begin with an article.
	 *
	 * @param int      $default_sort The existing comparison result.
	 * @param string   $first_title  The first title.
	 * @param string   $second_title The second title.
	 * @param Alphabet $alphabet     The configured listing alphabet.
	 * @return int
	 */
	public function sort_without_articles( int $default_sort, string $first_title, string $second_title, Alphabet $alphabet ): int {
		if ( '' === $this->language ) {
			return $default_sort;
		}

		$first  = self::strip_leading_article( $first_title, $this->language );
		$second = self::strip_leading_article( $second_title, $this->language );

		// Neither title began with an article, so the default comparison already holds.
		if ( $first === $first_title && $second === $second_title ) {
			return $default_sort;
		}

		if ( '' === $first || '' === $second ) {
			return $default_sort;
		}

		$comparison = $alphabet->compare_strings( $first, $second );
		if ( 0 !== $comparison ) {
			return $comparison <=> 0;
		}

		return $default_sort;
	}

	/**
	 * Remove a leading article from a title.
	 *
	 * Returns the title unchanged when it does not begin with an article, or when removing
	 * the article would leave nothing behind.
	 *
	 * @param string $title    The title to strip.
	 * @param string $language The language code.
	 * @return string
	 */
	public static function strip_leading_article( string $title, string $language ): string {
		$articles = self::get_articles( $language );
		if ( array() === $articles ) {
			return $title;
		}

		$subject = trim( wp_strip_all_tags( $title ) );
		if ( '' === $subject ) {
			return $title;
		}

		// Leading quotes and brackets would otherwise hide the article behind them.
		$subject = (string) preg_replace( '/^[\p{P}\p{S}]+/u', '', $subject );
		if ( '' === $subject ) {
			return $title;
		}

		$remainder = self::strip_elided_article( $subject, $articles );

		if ( null === $remainder ) {
			$remainder = self::strip_spaced_article( $subject, $articles );
		}

		if ( null === $remainder || '' === $remainder ) {
			return $title;
		}

		return $remainder;
	}

	/**
	 * Remove an elided article -- one joined to the next word by an apostrophe, such as
	 * "L'Etranger" or "Un'Altra". These carry no space, so the word split below never
	 * sees them.
	 *
	 * @param string        $subject  The title, already trimmed of leading punctuation.
	 * @param array<string> $articles The articles to ignore.
	 * @return string|null The remainder, or null when no elided article matched.
	 */
	protected static function strip_elided_article( string $subject, array $articles ): ?string {
		$elisions = array();
		foreach ( $articles as $article ) {
			if ( "'" === substr( $article, -1 ) ) {
				$elisions[] = preg_quote( substr( $article, 0, -1 ), '/' );
			}
		}

		if ( array() === $elisions ) {
			return null;
		}

		// Longest first, so "un'" is tested before "l'" can partially match.
		usort(
			$elisions,
			static fn( string $a, string $b ): int => strlen( $b ) <=> strlen( $a )
		);

		// The apostrophe class covers the straight, curly, and modifier-letter forms; the
		// trailing \s* absorbs a stray space after it.
		$pattern = '/^(?:' . implode( '|', $elisions ) . ')[' . self::APOSTROPHES . ']\s*/iu';

		$remainder = preg_replace( $pattern, '', $subject, 1, $count );

		if ( ! is_string( $remainder ) || 0 === $count ) {
			return null;
		}

		return trim( $remainder );
	}

	/**
	 * Remove an article followed by a space, such as "The Great Gatsby".
	 *
	 * @param string        $subject  The title, already trimmed of leading punctuation.
	 * @param array<string> $articles The articles to ignore.
	 * @return string|null The remainder, or null when the first word is not an article.
	 */
	protected static function strip_spaced_article( string $subject, array $articles ): ?string {
		$parts = preg_split( '/\s+/u', $subject, 2 );

		if ( ! is_array( $parts ) || 2 !== count( $parts ) ) {
			return null;
		}

		$word = preg_replace( '/^[\p{P}\p{S}]+|[\p{P}\p{S}]+$/u', '', $parts[0] );

		if ( ! is_string( $word ) || '' === $word ) {
			return null;
		}

		if ( ! in_array( strtolower( $word ), $articles, true ) ) {
			return null;
		}

		return trim( $parts[1] );
	}
}
