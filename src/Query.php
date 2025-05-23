<?php
/**
 * AlphaListing main process
 *
 * @package  alphalisting
 */

declare(strict_types=1);

namespace eslin87\AlphaListing;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The main A-Z Query class
 *
 * @since 0.1
 */
class Query {
	/**
	 * The taxonomy
	 *
	 * @var string|array<int,string>
	 */
	private $taxonomy;

	/**
	 * Listing type, posts or terms
	 *
	 * @var string
	 */
	private $type = 'posts';

	/**
	 * All available characters in a single string for translation support
	 *
	 * @var Alphabet
	 */
	private $alphabet;

	/**
	 * All items returned by the query
	 *
	 * @var array<int,mixed>
	 */
	private $items;

	/**
	 * Indices for only the items returned by the query - filtered version of $alphabet_chars
	 *
	 * @var array<string,mixed>
	 */
	private $matched_item_indices;

	/**
	 * The current item for use in the a-z items loop. internal use only
	 *
	 * @var array<string,\WP_Post>|array<string,\WP_Term>|array<string,string>
	 */
	private $current_item = null;

	/**
	 * The current item array-index in $items. internal use only
	 *
	 * @var int
	 */
	private $current_item_offset = 0;

	/**
	 * The items for the current letter. Used internally for the items loop.
	 *
	 * @var array<int,mixed>
	 */
	private $current_letter_items = array();

	/**
	 * The current letter array-index in $matched_item_indices. internal use only
	 *
	 * @var int
	 */
	private $current_letter_offset = 0;

	/**
	 * The query for this instance of the AlphaListing
	 *
	 * @var \WP_Query|array
	 */
	private $query;

	/**
	 * Number of instances on the page
	 *
	 * @var integer
	 */
	private static $num_instances = 0;

	/**
	 * Current instance ID
	 *
	 * @var integer
	 */
	private $instance_id;

	/**
	 * AlphaListing constructor
	 *
	 * @since 0.1
	 * @since 1.9.2 Instantiate the \WP_Query object here instead of in `AlphaListing::construct_query()`
	 * @since 2.0.0 add $type and $use_cache parameters
	 * @param null|\WP_Query|array|string $query      A \WP_Query-compatible query definition or a taxonomy name.
	 * @param string                      $type       Specify the listing type; either 'posts' or 'terms'.
	 * @param bool                        $use_cache  Cache the Listing via WordPress transients.
	 * @param array                       $attributes The shortcode attributes or null.
	 */
	public function __construct( $query = null, string $type = 'posts', bool $use_cache = true, $attributes = array() ) {
		global $post;

		if ( is_string( $query ) && ! empty( $query ) ) {
			$this->type = 'terms';
			if ( empty( $attributes ) ) {
				$attributes = array( 'taxonomy' => $query );
				$query      = apply_filters( 'alphalisting_shortcode_query_for_display__terms', array(), $attributes );
			}
		} elseif ( 'terms' === $type && ! empty( $query ) ) {
			$this->type = 'terms';
			if ( ! isset( $query['taxonomy'] ) ) {
				$taxonomy = 'category';
			} elseif ( is_array( $query['taxonomy'] ) ) {
				$taxonomy = implode( ',', $query['taxonomy'] );
			} else {
				$taxonomy = $query['taxonomy'];
			}
			if ( empty( $attributes ) ) {
				$attributes = array( 'taxonomy' => $taxonomy );
			}
			$query = apply_filters( 'alphalisting_shortcode_query_for_display__terms', $query, $attributes );
		} else {
			if ( empty( $type ) ) {
				if ( isset( $attributes['display'] ) ) {
					$type = $attributes['display'];
				} else {
					$type = 'posts';
				}
			}

			/**
			 * Filter the available display/query types.
			 *
			 * @param array<string> $types The supported display/query types.
			 * @return array<string> The supported display/query types.
			 */
			$types      = apply_filters( 'alphalisting_shortcode_query_types', array() );
			$this->type = in_array( $type, $types, true ) ? $type : 'posts';

			if ( empty( $attributes ) ) {
				$attributes = array();
			}
			if ( empty( $query ) ) {
				$query = array();
			}
			$query = apply_filters( "alphalisting_shortcode_query_for_display__{$this->type}", $query, $attributes );
		}

		// Must be after filter 'alphalisting_shortcode_query_for_display__$display'
		// to correctly wire-up the query-part filters.
		if ( ! defined( 'PHPUNIT_TEST_SUITE' ) || ! PHPUNIT_TEST_SUITE ) {
			$this->instance_id = apply_filters( 'alphalisting_instance_id', ++self::$num_instances );
		} else {
			$this->instance_id = 'testid';
		}
		$this->alphabet = new Alphabet();

		/**
		 * Modify or replace the query
		 *
		 * @since 1.0.0
		 * @since 2.0.0 apply to taxonomy queries. Add type parameter indicating type of query.
		 * @param array|Object|\WP_Query  $query  The query object
		 * @param string  $type  The type of the query. Either 'posts' or 'terms'.
		 */
		$query = apply_filters( 'alphalisting_query', $query, $this->type );

		/**
		 * Modify or replace the query
		 *
		 * @since 1.7.1
		 * @since 2.0.0 apply to taxonomy queries. Add type parameter indicating type of query.
		 * @param array|Object|\WP_Query  $query  The query object
		 * @param string  $type  The type of the query. Either 'posts' or 'terms'.
		 */
		$query = apply_filters( 'alphalisting-query', $query, $this->type ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound

		if ( is_array( $query ) && isset( $query['taxonomy'] ) ) {
			$this->taxonomy = $query['taxonomy'];
		} elseif ( $query instanceof \WP_Query ) {
			$this->taxonomy = $query->taxonomy;
		} elseif ( is_string( $query ) ) {
			$this->taxonomy = $query;
		}

		$this->query = $query;

		/**
		 * Get the cached data
		 *
		 * @since 1.0.0
		 * @since 2.0.0 apply to taxonomy queries. Add type parameter indicating type of query.
		 * @since 4.0.0 apply to all queries.
		 * @param array  $items  The items from previous cache modules.
		 * @param array  $query  The query.
		 * @param string  $type  The type of the query. e.g. posts, terms, etc.
		 */
		$items = apply_filters( 'alphalisting_get_cached_query', array(), (array) $query, $this->type );

		if ( ! is_array( $items ) || 0 === count( $items ) ) {
			/**
			 * Run the query to fetch the current items
			 *
			 * @since 4.0.0
			 * @param array $items The items.
			 * @param array|\WP_Query $query The query.
			 */
			$items = apply_filters( "alphalisting_get_items_for_display__{$this->type}", array(), $query );
		}

		if ( defined( 'ALPHALISTING_LOG' ) && ALPHALISTING_LOG ) {
			do_action( 'alphalisting_log', "AlphaListing: {$this->type}", '!ID', $items );
		}

		/**
		 * Filter items from the query results
		 *
		 * @since 2.0.0
		 * @param array  $items The query results.
		 * @param string $type  The query type - e.g. terms, posts, etc.
		 * @param array  $query The query as an array.
		 */
		$items = apply_filters( 'alphalisting-filter-items', $items, $this->type, (array) $query ); //phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound

		$this->matched_item_indices = $this->get_all_indices( $items );

		if ( $use_cache ) {
			/**
			 * Save the data to cache
			 *
			 * @since 2.0.0
			 * @param array  $query  The query.
			 * @param string  $type  The type of the query. e.g. posts, terms, etc.
			 * @param array  $items  The items from query.
			 */
			do_action( 'alphalisting_save_cache', (array) $query, $this->type, $this->matched_item_indices );
		}
	}

	/**
	 * Tell \WP_Query to split the query.
	 *
	 * @since 4.0.0
	 * @param bool      $split_the_query Whether or not to split the query.
	 * @param \WP_Query $query           The \WP_Query instance.
	 */
	public static function split_the_query( bool $split_the_query, \WP_Query $query ): bool {
		return true;
	}

	/**
	 * Find a post's parent post. Will return the original post if the post-type is not hierarchical or the post does not have a parent.
	 *
	 * @since 1.4.0
	 * @param \WP_Post|int $page The post whose parent we want to find.
	 * @return \WP_Post|null The parent post or the original post if no parents were found. Will be false if the function is called with incorrect arguments.
	 */
	public static function find_post_parent( $page ) {
		if ( empty( $page ) ) {
			return null;
		}
		if ( ! $page instanceof \WP_Post ) {
			$page = get_post( $page );
		}
		if ( empty( $page->post_parent ) ) {
			return $page;
		}
		return self::find_post_parent( $page->post_parent );
	}

	/**
	 * Calculate the top-level section of the requested page
	 *
	 * @since 0.1
	 * @param \WP_Post|int $page Optional: The post object, or post-ID, of the page whose section we want to find.
	 * @return \WP_Post|null The post object of the current section's top-level page.
	 */
	protected static function get_section( $page = 0 ) {
		global $post;

		$pages = get_pages( array( 'parent' => 0 ) );

		$sections = array_map(
			function( \WP_Post $item ): string {
					return $item->post_name;
			},
			$pages
		);
		/**
		 * Override the detected top-level sections for the site. Defaults to contain each page with no post-parent.
		 *
		 * @deprecated Use alphalisting_sections
		 * @see alphalisting_sections
		 */
		$sections = apply_filters_deprecated( 'az_sections', array( $sections ), '1.0.0', 'alphalisting_sections' );
		/**
		 * Override the detected top-level sections for the site. Defaults to contain each page with no post-parent.
		 *
		 * @param array $sections The sections for the site.
		 */
		$sections = apply_filters( 'alphalisting_sections', $sections );
		/**
		 * Override the detected top-level sections for the site. Defaults to contain each page with no post-parent.
		 *
		 * @since 1.7.1
		 * @param array $sections The sections for the site.
		 */
		$sections = apply_filters( 'alphalisting-sections', $sections ); //phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound

		if ( ! $page instanceof \WP_Post ) {
			$page = get_post( $page );
		}
		if ( ! $page instanceof \WP_Post ) {
			$page = $post;
		}
		if ( ! $page instanceof \WP_Post ) {
			return null;
		}

		$section_object = self::find_post_parent( $page );
		$section_name   = null;
		if ( $section_object === $page ) {
			$section_object = null;
		} elseif ( $section_object instanceof \WP_Post ) {
			$section_name = $section_object->post_name;
		} else {
			$section_name   = null;
			$section_object = null;
		}

		if ( defined( 'ALPHALISTING_LOG' ) ) {
			do_action( 'alphalisting_log', 'AlphaListing: Section selection', $section_name, $sections );
		}

		if ( null !== $section_name && ! in_array( $section_name, $sections, true ) ) {
			$section_name   = null;
			$section_object = null;
		}

		if ( defined( 'ALPHALISTING_LOG' ) ) {
			do_action( 'alphalisting_log', 'AlphaListing: Proceeding with section', $section_name );
		}
		return $section_object;
	}

	/**
	 * Fetch the query we are currently using
	 *
	 * @since 1.0.0
	 * @return \WP_Query The query object
	 */
	public function get_the_query(): \WP_Query {
		return $this->query;
	}

	/**
	 * Reducer used by get_the_item_indices() to filter the indices for each post to unique array_values (see: https://secure.php.net/array_reduce)
	 *
	 * @param array<int,mixed> $carry Holds the return value of the previous iteration.
	 * @param array<int,mixed> $value  Holds the value of the current iteration.
	 * @return array<int,mixed> The previous iteration return value with the current iteration added after running through array_unique()
	 */
	public static function index_reduce( array $carry, array $value ): array {
		$v = array_unique( $value );
		if ( ! empty( $v ) ) {
			$carry[] = $v;
		}
		return $carry;
	}

	/**
	 * Extract an item's indices
	 *
	 * @param mixed $item The item.
	 * @return array<string,mixed> The indices. This is an associative array of `[ 'index-char' => $item_array ]`.
	 */
	protected function get_all_indices_for_item( $item ): array {
		$indexed_items = array();
		$item_indices  = apply_filters( 'alphalisting_extract_item_indices', array(), $item, $this->type, $this->alphabet );

		if ( ! empty( $item_indices ) ) {
			foreach ( $item_indices as $key => $entries ) {
				if ( ! empty( $entries ) ) {
					if ( ! isset( $indexed_items[ $key ] ) || ! is_array( $indexed_items[ $key ] ) ) {
						$indexed_items[ $key ] = array();
					}
					$indexed_items[ $key ] = array_merge_recursive( $indexed_items[ $key ], $entries );
				}
			}
		}

		if ( defined( 'ALPHALISTING_LOG' ) && ALPHALISTING_LOG > 2 ) {
			do_action( 'alphalisting_log', 'AlphaListing: Complete item indices', $indexed_items );
		}
		return $indexed_items;
	}

	/**
	 * Sort the letters to be used as indices and return as an Array
	 *
	 * @since 0.1
	 * @param array<int,mixed> $items The items to index.
	 * @return array<string,mixed> The index letters
	 */
	protected function get_all_indices( array $items = array() ): array {
		global $post;
		$indexed_items = array();

		if ( ! is_array( $items ) || empty( $items ) ) {
			$items = $this->items;
		}

		if ( is_array( $items ) && ! empty( $items ) ) {
			foreach ( $items as $item ) {
				foreach ( $this->get_all_indices_for_item( $item ) as $key => $value ) {
					foreach ( $value as $index_entry ) {
						$indexed_items[ $key ][] = $index_entry;
					}
				}
			}
		} elseif ( $this->query instanceof \WP_Query ) {
			$offset         = 0;
			$posts_per_page = $this->query->posts_per_page;
			$found_posts    = $this->query->found_posts;
			while ( $offset < $found_posts ) {
				$this->query->the_post();

				foreach ( $this->get_all_indices_for_item( $post ) as $key => $value ) {
					foreach ( $value as $index_entry ) {
						$indexed_items[ $key ][] = $index_entry;
					}
				}

				++$offset;
				if ( 0 === $offset % $posts_per_page ) {
					$q           = $this->query->query;
					$q['offset'] = $offset * $posts_per_page;
					$this->query = new \WP_Query( $q );
				}
				$this->alphabet[ $this->unknown_letters ] = $this->unknown_letters;
			}
			wp_reset_postdata();
		}

		$alphabet = $this->alphabet;
		$alphabet->loop(
			function( string $character ) use ( &$indexed_items, $alphabet ) {
				if ( ! empty( $indexed_items[ $character ] ) ) {
					usort(
						$indexed_items[ $character ],
						/**
						 * Closure to sort the indexed items based on their titles
						 *
						 * @param array<string,string> $a
						 * @param array<string,string> $b
						 */
						function ( array $a, array $b ) use ( $alphabet ): int {
							$atitle = strtolower( $a['title'] );
							$btitle = strtolower( $b['title'] );

							$atitle_array = Strings::mb_string_to_array( $atitle );
							$btitle_array = Strings::mb_string_to_array( $btitle );

							$atitle_array = array_map(
								function( $letter ) use ( $alphabet ) {
									$normalised_letter = $alphabet->get_letter_for_key( $letter );
									if ( $normalised_letter === $alphabet->unknown_letter ) {
										$normalised_letter = $letter;
									}
									return $normalised_letter;
								},
								$atitle_array
							);
							$btitle_array = array_map(
								function( $letter ) use ( $alphabet ) {
									$normalised_letter = $alphabet->get_letter_for_key( $letter );
									if ( $normalised_letter === $alphabet->unknown_letter ) {
										$normalised_letter = $letter;
									}
									return $normalised_letter;
								},
								$btitle_array
							);

							$default_sort = 0;
							if ( implode( '', $atitle_array ) != implode( '', $btitle_array ) ) {
								$min_length = min( count( $atitle_array ), count( $btitle_array ) );
								for ( $idx = 0; $idx < $min_length; ++$idx ) {
									$a_has_symbol = false;
									$b_has_symbol = false;

									$aletter = array_search( $atitle_array[ $idx ], $alphabet->alphabet_keys );
									$bletter = array_search( $btitle_array[ $idx ], $alphabet->alphabet_keys );

									if ( ! is_int( $aletter ) ) {
										$aletter = $atitle_array[ $idx ];
										$a_has_symbol = true;
									}
									if ( ! is_int( $bletter ) ) {
										$bletter = $btitle_array[ $idx ];
										$b_has_symbol = true;
									}

									if ( $a_has_symbol && ! $b_has_symbol ) {
										$default_sort = $alphabet->unknown_letter_is_first ? -1 : 1;
									} elseif ( ! $a_has_symbol && $b_has_symbol ) {
										$default_sort = $alphabet->unknown_letter_is_first ? 1 : -1;
									} elseif ( $a_has_symbol && $b_has_symbol ) {
										$default_sort = $aletter <=> $bletter;
									} else {
										$default_sort = $aletter <=> $bletter;
									}

									if ( 0 !== $default_sort ) {
										break;
									}
								}

								if ( 0 === $default_sort ) {
									$default_sort = count( $atitle_array ) <=> count( $btitle_array );
								}
							}

							/**
							 * Compare two titles to determine sorting order.
							 *
							 * @since 3.1.0
							 * @param int The previous order preference: -1 if $a is less than $b. 1 if $a is greater than $b. 0 if they are identical.
							 * @param string $a The first title. Converted to lower case.
							 * @param string $b The second title. Converted to lower case.
							 * @return int The new order preference: -1 if $a is less than $b. 1 if $a is greater than $b. 0 if they are identical.
							 */
							$sort = apply_filters(
								'alphalisting_item_sorting_comparator',
								$default_sort,
								$atitle,
								$btitle
							);

							if ( is_int( $sort ) ) {
								// normalise the returned value to -1, 0, or 1.
								return $sort <=> 0;
							}

							if ( defined( 'AZLISTINGLOG' ) && AZLISTINGLOG ) {
								do_action( 'alphalisting_log', 'AlphaListing: value returned from `alphalisting_item_sorting_comparator` filter sorting was not an integer', $sort, $atitle, $btitle );
							}
							return $default_sort;
						}
					);
				}
			},
			array_key_exists( $this->alphabet->get_unknown_letter(), $indexed_items )
		);

		return $indexed_items;
	}

	/**
	 * Print the letter links HTML
	 *
	 * @since 1.0.0
	 * @param string       $target The page to point links toward.
	 * @param string|array $style CSS classes to apply to the output.
	 * @return void
	 */
	public function the_letters( string $target = '', $style = null ) {
		echo $this->get_the_letters( $target, $style ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * Print the letter links HTML
	 *
	 * @since 0.1
	 * @since 1.0.0 deprecated.
	 * @see AlphaListing::get_the_letters()
	 * @deprecated use AlphaListing::get_the_letters().
	 * @param string       $target The page to point links toward.
	 * @param string|array $style CSS classes to apply to the output.
	 * @return string The letter links HTML
	 */
	public function get_letter_display( string $target = '', $style = '' ): string {
		_deprecated_function( __METHOD__, '1.0.0', 'AlphaListing::get_the_letters' );
		return $this->get_the_letters( $target, $style );
	}

	/**
	 * Retrieve the letter links HTML
	 *
	 * @since 1.0.0
	 * @param string       $target The page to point links toward.
	 * @param string|array $style CSS classes to apply to the output.
	 * @return string The letter links HTML
	 */
	public function get_the_letters( string $target = '', $style = '' ): string {
		$classes = array( 'az-links' );
		if ( is_array( $style ) ) {
			$classes = array_merge( $classes, $style );
		} elseif ( is_string( $style ) ) {
			$c       = preg_split( '[,\s]', $style );
			$classes = array_merge( $classes, $c );
		}
		$classes = array_unique( array_filter( $classes ) );

		$that     = $this;
		$alphabet = $this->alphabet;
		$indices  = &$this->matched_item_indices;
		$ret      = '<ul class="' . esc_attr( implode( ' ', $classes ) ) . '">';

		$alphabet->loop(
			/**
			 * Closure to build each letter in the letter view
			 *
			 * @param string     $character
			 * @param int|string $i
			 * @param int        $count
			 * @return void
			 */
			function( string $character, $i, int $count ) use ( $that, $target, $alphabet, $indices, &$ret ) {
				$id = $character;
				if ( $alphabet->get_unknown_letter() === $id ) {
					$id = '_';
				}

				$classes = array();
				if ( 0 === $i ) {
					$classes[] = 'first';
				} elseif ( $count - 1 === $i ) {
					$classes[] = 'last';
				}
				if ( 1 === $i % 2 ) {
					$classes[] = 'even';
				} else {
					$classes[] = 'odd';
				}
				if ( ! empty( $indices[ $character ] ) ) {
					$classes[] = 'has-posts';
				} else {
					$classes[] = 'no-posts';
				}

				$ret .= '<li class="' . esc_attr( implode( ' ', $classes ) ) . '">';
				if ( ! empty( $indices[ $character ] ) ) {
					$ret .= '<a href="' . esc_url( "$target#alphalisting-letter-$id-{$this->instance_id}" ) . '">';
				}
				$ret .= '<span>' . esc_html( $that->get_the_letter_title( $character ) ) . '</span>';
				if ( ! empty( $indices[ $character ] ) ) {
					$ret .= '</a>';
				}
				$ret .= '</li>';
			},
			array_key_exists( $alphabet->get_unknown_letter(), $this->matched_item_indices )
		);

		$ret .= '</ul>';
		return $ret;
	}

	/**
	 * Print the index list HTML created by a theme template
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function the_listing() {
		global $post;
		if ( 'terms' === $this->type ) {
			if ( is_array( $this->taxonomy ) ) {
				$section = join( '_', $this->taxonomy );
			} else {
				$section = $this->taxonomy;
			}
		} else {
			$section = self::get_section();
			if ( $section instanceof \WP_Post ) {
				$section = $section->post_name;
			}
		}

		$templates = array(
			'a-z-listing-' . $section . '.php',
			'a-z-listing.php',
		);

		if ( $post ) {
			array_unshift(
				$templates,
				'a-z-listing-' . $post->post_type . '.php'
			);
			array_unshift(
				$templates,
				'a-z-listing-' . $post->post_type . '.php'
			);
			array_unshift(
				$templates,
				'a-z-listing-' . $post->post_name . '.php'
			);
		}
		
		_do_template( $this, locate_template( $templates ) );
		
		wp_reset_postdata();
	}

	/**
	 * Retrieve column number, gap, and column width
	 *
	 * @since 4.3.2
	 * @return string The inline style properities for column.
	 */
	public function get_customized_column_styles() {
		$styles = apply_filters( 'alphalisting_styles', '', $this, $this->instance_id );
		echo esc_attr( $styles );
	}

	/**
	 * Retrieve the index list HTML created by a theme template
	 *
	 * @since 0.7
	 * @return string The index list HTML.
	 */
	public function get_the_listing(): string {
		ob_start();
		$this->the_listing();
		$r = ob_get_clean();

		return $r;
	}

	/**
	 * Retrieve the listing instance ID. This is not escaped!
	 *
	 * @since 4.0.0
	 * @return string The instance number
	 */
	public function get_the_instance_id() {
		return "alphalisting-{$this->instance_id}";
	}

	/**
	 * Print the listing instance ID.
	 *
	 * @since 4.0.0
	 */
	public function the_instance_id() {
		echo esc_attr( $this->get_the_instance_id() );
	}

	/**
	 * Used by theme templates. Returns true when we still have letters to iterate.
	 *
	 * @since 0.7
	 * @see AlphaListing::have_letters()
	 * @deprecated use AlphaListing::have_letters()
	 * @return bool True if we have more letters to iterate, otherwise false
	 */
	public function have_a_z_letters(): bool {
		_deprecated_function( __METHOD__, '1.0.0', 'AlphaListing::have_letters' );
		return $this->have_letters();
	}

	/**
	 * Used by theme templates. Returns true when we still have letters to iterate.
	 *
	 * @since 1.0.0
	 * @return bool True if we have more letters to iterate, otherwise false
	 */
	public function have_letters(): bool {
		return ( $this->num_letters() > $this->current_letter_offset );
	}

	/**
	 * Used by theme templates. Returns true when we still have posts to iterate.
	 *
	 * @since 0.7
	 * @since 1.0.0 deprecated.
	 * @see AlphaListing::have_items()
	 * @deprecated use AlphaListing::have_items()
	 * @return bool True if there are posts left to iterate within the current letter, otherwise false.
	 */
	public function have_a_z_posts(): bool {
		_deprecated_function( __METHOD__, '1.0.0', 'have_items' );
		return $this->have_items();
	}

	/**
	 * Used by theme templates. Returns true when we still have items/posts within the current letter.
	 *
	 * To advance the letter use AlphaListing::the_letter()
	 * To advance the item/post use AlphaListing::the_item()
	 *
	 * @since 1.0.0
	 * @return bool True if there are posts left to iterate within the current letter, otherwise false.
	 */
	public function have_items(): bool {
		return is_array( $this->current_letter_items ) &&
			$this->get_the_letter_items_count() > $this->current_item_offset;
	}

	/**
	 * Advance the Letter Loop onto the next letter
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function the_letter() {
		$this->current_item_offset  = 0;
		$this->current_letter_items = array();
		$key                        = $this->alphabet->get_key_for_offset( $this->current_letter_offset );
		if ( isset( $this->matched_item_indices[ $key ] ) ) {
			$this->current_letter_items = &$this->matched_item_indices[ $key ];
			usort($this->current_letter_items, function ($a, $b) {
				return strcasecmp($a['title'], $b['title']);
			});
		}
		++$this->current_letter_offset;
	}

	/**
	 * Advance the Post loop within the Letter Loop onto the next post
	 *
	 * @since 0.7
	 * @since 1.0.0 deprecated.
	 * @see AlphaListing::the_item()
	 * @deprecated use AlphaListing::the_item()
	 * @return void
	 */
	public function the_a_z_post() {
		_deprecated_function( __METHOD__, '1.0.0', 'AlphaListing::the_item' );
		$this->the_item();
	}

	/**
	 * Advance the Post loop within the Letter Loop onto the next post
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function the_item() {
		$this->current_item = $this->current_letter_items[ $this->current_item_offset ];
		++$this->current_item_offset;
	}

	/**
	 * Retrieve the item object for the current post
	 *
	 * @since 2.0.0
	 * @param string $force Set this to 'I understand the issues!' to acknowledge that this function will cause slowness on large sites.
	 * @return array|\WP_Error|\WP_Post|\WP_Term
	 */
	public function get_the_item_object( string $force = '' ) {
		global $post;
		if ( 'I understand the issues!' === $force ) {
			$current_item = $this->current_item['item'];

			if ( is_string( $current_item ) ) {
				$item = explode( ':', $current_item, 2 );

				if ( isset( $item[1] ) ) {
					if ( 'terms' === $item[0] ) {
						return get_term( intval( $item[1] ) );
					}
					if ( 'posts' === $item[0] ) {
						$post = get_post( intval( $item[1] ) ); //phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
						\setup_postdata( $post );
						return $post;
					}
				}
			} elseif ( $current_item instanceof \WP_Post ) {
				$post = $current_item; //phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
				setup_postdata( $post );
				return $post;
			} elseif ( $current_item instanceof \WP_Term ) {
				return get_term( $current_item );
			} else {
				return $current_item;
			}
		}

		return new \WP_Error( 'understanding', 'You must tell the plugin "I understand the issues!" when calling get_the_item_object().' );
	}

	/**
	 * Retrieve meta field for an item.
	 *
	 * @since 2.1.0
	 * @param string $key The meta key to retrieve. By default returns data for all keys.
	 * @param bool   $single Whether to return a single value.
	 * @return mixed|\WP_Error Will be an array if $single is false. Will be value of meta data field if $single is true.
	 */
	public function get_item_meta( string $key = '', bool $single = false ) {
		if ( is_string( $this->current_item['item'] ) ) {
			$item = explode( ':', $this->current_item['item'], 2 );

			if ( 'term' === $item[0] ) {
				return get_term_meta( intval( $item[1] ), $key, $single );
			} elseif ( 'post' === $item[0] ) {
				return get_post_meta( intval( $item[1] ), $key, $single );
			}
		} elseif ( $this->current_item['item'] instanceof \WP_Term ) {
			return get_term_meta( $this->current_item['item']->term_id, $key, $single );
		} elseif ( $this->current_item['item'] instanceof \WP_Post ) {
			return get_post_meta( $this->current_item['item']->ID, $key, $single );
		}

		return new \WP_Error( 'no-type', 'Unknown item type.' );
	}

	/**
	 * Print the number of posts assigned to the current term
	 *
	 * @since 2.2.0
	 * @return void
	 */
	public function the_item_post_count() {
		echo \esc_html( strval( $this->get_the_item_post_count() ) );
	}

	/**
	 * Retrieve the number of posts assigned to the current term
	 *
	 * @since 2.2.0
	 * @return int The number of posts
	 */
	public function get_the_item_post_count(): int {
		if ( is_string( $this->current_item['item'] ) ) {
			if ( 'term' === $this->get_the_item_type() ) {
				$term = get_term( intval( $this->get_the_item_id() ) );
				if ( $term ) {
					return $term->count;
				}
			}
		} elseif ( $this->current_item['item'] instanceof \WP_Term ) {
			return $this->current_item['item']->count;
		}
		return 0;
	}

	/**
	 * Retrieve the number of letters in the loaded alphabet
	 *
	 * @since 1.0.0
	 * @return int The number of letters
	 */
	public function num_letters() {
		return $this->alphabet->count( array_key_exists( $this->alphabet->get_unknown_letter(), $this->matched_item_indices ) );
	}

	/**
	 * Retrieve the number of items within the current letter
	 *
	 * @since 0.7
	 * @since 1.0.0 deprecated.
	 * @see AlphaListing::get_the_letter_items_count()
	 * @deprecated use AlphaListing::get_the_letter_items_count()
	 * @return int The number of items.
	 */
	public function num_a_z_posts(): int {
		_deprecated_function( __METHOD__, '1.0.0', 'AlphaListing::get_the_letter_items_count' );
		return $this->get_the_letter_items_count();
	}

	/**
	 * Retrieve the number of items within the current letter
	 *
	 * @since 0.7
	 * @since 1.0.0 deprecated.
	 * @see AlphaListing::get_the_letter_items_count()
	 * @deprecated use AlphaListing::get_the_letter_items_count()
	 * @return int The number of posts.
	 */
	public function num_a_z_items(): int {
		_deprecated_function( __METHOD__, '1.0.0', 'AlphaListing::get_the_letter_items_count' );
		return $this->get_the_letter_items_count();
	}

	/**
	 * Print the number of items within the current letter
	 *
	 * @since 1.0.0
	 * @since 4.0.0 deprecated.
	 * @return void
	 */
	public function the_letter_count() {
		_deprecated_function( __METHOD__, '4.0.0', 'AlphaListing::the_letter_items_count' );
		$this->the_letter_items_count();
	}

	/**
	 * Retrieve the number of items within the current letter
	 *
	 * @since 1.0.0
	 * @since 4.0.0 deprecated.
	 * @return int The number of items.
	 */
	public function get_the_letter_count(): int {
		_deprecated_function( __METHOD__, '4.0.0', 'AlphaListing::get_the_letter_items_count' );
		return $this->get_the_letter_items_count();
	}

	/**
	 * Print the number of items within the current letter
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function the_letter_items_count() {
		echo esc_html( strval( $this->get_the_letter_items_count() ) );
	}

	/**
	 * Retrieve the number of items within the current letter
	 *
	 * @since 1.0.0
	 * @return int The number of items
	 */
	public function get_the_letter_items_count(): int {
		return count( $this->current_letter_items );
	}

	/**
	 * Print the escaped ID of the current letter.
	 *
	 * @since 0.7
	 * @return void
	 */
	public function the_letter_id() {
		echo esc_attr( $this->get_the_letter_id() );
	}

	/**
	 * Retrieve the ID of the current letter. This is not escaped!
	 *
	 * @since 0.7
	 * @return string The letter ID
	 */
	public function get_the_letter_id(): string {
		$key = $this->alphabet->get_key_for_offset( $this->current_letter_offset - 1 );
		$id  = $this->alphabet->get_letter_for_key( $key );
		if ( $this->alphabet->get_unknown_letter() === $id ) {
			$id = '_';
		}
		return "alphalisting-letter-$id-{$this->instance_id}";
	}

	/**
	 * Print the escaped ID of the current item.
	 *
	 * @since 2.4.0
	 * @return void
	 */
	public function the_item_id() {
		echo esc_attr( $this->get_the_item_id() );
	}

	/**
	 * Retreive the ID of the current item. This is not escaped!
	 *
	 * @since 2.4.0
	 * @return int The item ID.
	 */
	public function get_the_item_id(): int {
		$current_item = $this->current_item['item'];
		if ( is_string( $current_item ) ) {
			$item = explode( ':', $current_item, 2 );

			return intval( $item[1] ?? -1 );
		} elseif ( $current_item instanceof \WP_Post ) {
			return $current_item->ID;
		} elseif ( $current_item instanceof \WP_Term ) {
			return $current_item->term_id;
		} else {
			return $current_item;
		}
	}

	/**
	 * Retreive the type of the current item.
	 *
	 * @since 2.4.0
	 * @return string|\WP_Error The type of the current item. Either `post` or `term`. Will return a \WP_Error object if the type of the current item cannot be determined.
	 */
	public function get_the_item_type() {
		$current_item = $this->current_item['item'];
		if ( $current_item instanceof \WP_Post ) {
			return 'post';
		} elseif ( $current_item instanceof \WP_Term ) {
			return 'term';
		} elseif ( is_string( $current_item ) ) {
			$item = explode( ':', $current_item, 2 );
			if ( isset( $item[0] ) && in_array( $item[0], array( 'post', 'term' ), true ) ) {
				return $item[0];
			}
		}
		if ( in_array( $this->type, array( 'terms', 'posts' ), true ) ) {
			return 'terms' === $this->type ? 'term' : 'post';
		}

		return new \WP_Error( 'no-type', 'Unknown item type.' );
	}

	/**
	 * Print the escaped title of the current letter. For example, upper-case A or B or C etc.
	 *
	 * @since 0.7
	 * @param string $index The index for which to print the title.
	 * @return void
	 */
	public function the_letter_title( string $index = '' ) {
		echo esc_html( $this->get_the_letter_title( $index ) );
	}

	/**
	 * Retrieve the title of the current letter. For example, upper-case A or B or C etc. This is not escaped!
	 *
	 * @since 0.7
	 * @since 1.8.0 Add filters to modify the title of the letter.
	 * @param string $index The index for which to return the title.
	 * @return string The letter title
	 */
	public function get_the_letter_title( string $index = '' ): string {
		if ( '' !== $index ) {
			$letter = $this->alphabet->get_letter_for_key( $index ) ?? $index;
		} else {
			$key    = $this->alphabet->get_key_for_offset( $this->current_letter_offset - 1 );
			$letter = $this->alphabet->get_letter_for_key( $key );
		}

		/**
		 * Modify the letter title or heading
		 *
		 * @since 1.8.0
		 * @param string $letter The title of the letter.
		 */
		$letter = apply_filters( 'the_a_z_letter_title', $letter ); //phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound
		/**
		 * Modify the letter title or heading
		 *
		 * @since 1.8.0
		 * @param string $letter The title of the letter.
		 */
		$letter = apply_filters( 'the-a-z-letter-title', $letter ); //phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound

		return $letter;
	}

	/**
	 * Print the escaped title of the current post
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function the_title() {
		// to match core we do NOT escape the output!
		echo $this->get_the_title(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * Retrieve the title of the current post. This is not escaped!
	 *
	 * @since 1.0.0
	 * @return string The post title
	 */
	public function get_the_title(): string {
		$title = $this->current_item['title'];
		if ( is_string( $this->current_item['item'] ) ) {
			$item = explode( ':', $this->current_item['item'], 2 );
		} else {
			$item = $this->current_item['item'];
		}

		if ( is_array( $item ) ) {
			if ( 'post' === $item[0] ) {
				return apply_filters( 'the_title', $title, $item[1] ); //phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound
			} elseif ( 'term' === $item[0] ) {
				return apply_filters( 'term_name', $title, $item[1] ); //phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound
			}
		} else {
			if ( $item instanceof \WP_Post ) {
				return apply_filters( 'the_title', $title, $item->ID ); //phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound
			} elseif ( $item instanceof \WP_Term ) {
				return apply_filters( 'term_name', $title, $item->term_id ); //phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound
			}
		}

		return $title;
	}

	/**
	 * Print the escaped permalink of the current post.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function the_permalink() {
		echo esc_url( $this->get_the_permalink() );
	}

	/**
	 * Retrieve the permalink of the current post. This is not escaped!
	 *
	 * @since 1.0.0
	 * @return string The permalink
	 */
	public function get_the_permalink(): string {
		return $this->current_item['link'];
	}
}

/**
 * Load and execute a theme template
 *
 * @since 2.1.0
 * @param Query $a_z_query The Query object.
 * @return void
 */
function _do_template( Query $a_z_query ) {
	if ( func_get_arg( 1 ) ) {
		require func_get_arg( 1 );
	} else {
		require ALPHALISTING_DEFAULT_TEMPLATE;
	}
}
