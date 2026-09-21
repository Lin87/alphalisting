<?php
/**
 * AlphaListing Alphabet grouping system
 *
 * @package  alphalisting
 */

declare(strict_types=1);

namespace eslin87\AlphaListing;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * AlphaListing Alphabet grouping system class
 *
 * @since 2.0.0
 */
class Grouping {
	/**
	 * The configured grouping count
	 *
	 * @since 2.0.0
	 * @var int
	 */
	private $grouping;

	/**
	 * The populated headings for the listing
	 *
	 * @since 2.0.0
	 * @var array<string,array>
	 */
	private $headings;

	/**
	 * Each group we created, mapped back to the groups it was built from.
	 *
	 * @since 4.5.1
	 * @var array<string,array<int,string>>
	 */
	private $ungrouped = array();

	/**
	 * Add filters to group the alphabet letters
	 *
	 * @since 2.0.0
	 * @param int $grouping The number of letters in each group.
	 */
	public function __construct( int $grouping ) {
		$this->grouping = $grouping;

		if ( 1 < $grouping ) {
			add_filter( 'alphalisting-alphabet', array( $this, 'alphabet_filter' ), 2 );
			add_filter( 'alphalisting_sorting_alphabet', array( $this, 'sorting_alphabet_filter' ), 2 );
			add_filter( 'the-a-z-letter-title', array( $this, 'heading' ), 5 );
		}
	}

	/**
	 * Remove the filters grouping the alphabet letters
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function teardown() {
		remove_filter( 'alphalisting-alphabet', array( $this, 'alphabet_filter' ), 2 );
		remove_filter( 'alphalisting_sorting_alphabet', array( $this, 'sorting_alphabet_filter' ), 2 );
		remove_filter( 'the-a-z-letter-title', array( $this, 'heading' ), 5 );
	}

	/**
	 * Override the alphabet with grouped letters
	 *
	 * @since 2.0.0
	 * @param string $alphabet The alphabet to override.
	 * @return string the new grouped alphabet.
	 */
	public function alphabet_filter( string $alphabet ): string {
		$headings = array();
		$letters  = explode( ',', $alphabet );
		$letters  = array_map( 'trim', $letters );

		$i = 0;
		$j = 0;

		$grouping  = $this->grouping;
		$ungrouped = array();

		$groups = array_reduce(
			$letters,
			/**
			 * Closure to reduce the groups array and populate the headings array
			 *
			 * @param array<int,string> $carry
			 * @param string $letter
			 * @return array<int,string>
			 */
			function( array $carry, string $letter ) use ( $grouping, &$headings, &$ungrouped, &$i, &$j ) {
				if ( isset( $carry[ $j ] ) ) {
					$carry[ $j ] = $carry[ $j ] . $letter;
				} else {
					$carry[ $j ] = $letter;
				}
				$headings[ $j ][]  = Strings::maybe_mb_substr( $letter, 0, 1 );
				$ungrouped[ $j ][] = $letter;

				if ( $i + 1 === $grouping ) {
					$i = 0;
					$j++;
				} else {
					$i++;
				}

				return $carry;
			},
			array()
		);

		$this->headings = array_reduce(
			$headings,
			/**
			 * Closure to reduce the headings array
			 *
			 * @param array<string,string> $carry
			 * @param string $heading
			 * @return array<string,string>
			 */
			function( array $carry, array $heading ): array {
				$carry[ Strings::maybe_mb_substr( $heading[0], 0, 1 ) ] = $heading;
				return $carry;
			},
			array()
		);

		$this->ungrouped = array();
		foreach ( $groups as $offset => $group ) {
			$this->ungrouped[ "__$group" ] = $ungrouped[ $offset ];
		}

		return join( ',', $groups );
	}

	/**
	 * Expand our own groups again so items under a shared heading still order
	 * by their real letter. Groups we did not create are passed through, so
	 * this does not depend on which filters ran before us.
	 *
	 * @since 4.5.1
	 * @param string $alphabet The grouped alphabet.
	 * @return string The alphabet with our own groups expanded again.
	 */
	public function sorting_alphabet_filter( string $alphabet ): string {
		if ( empty( $this->ungrouped ) ) {
			return $alphabet;
		}

		$parts = array_map(
			function( string $part ): string {
				$group = trim( $part );
				if ( ! isset( $this->ungrouped[ "__$group" ] ) ) {
					return $part;
				}
				return join( ',', $this->ungrouped[ "__$group" ] );
			},
			explode( ',', $alphabet )
		);

		return join( ',', $parts );
	}

	/**
	 * Override the title of each group
	 *
	 * @since 2.0.0
	 * @param string $title The original title of the group.
	 * @return string The new title for the group.
	 */
	public function heading( string $title ): string {
		if ( isset( $this->headings[ $title ] ) && is_array( $this->headings[ $title ] ) ) {
			$first = $this->headings[ $title ][0];
			$last  = $this->headings[ $title ][ count( $this->headings[ $title ] ) - 1 ];
			return $first . '-' . $last;
		}

		return $title;
	}
}
