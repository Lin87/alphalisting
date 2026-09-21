<?php
/**
 * Columns Query Part.
 *
 * @package alphalisting
 */

declare(strict_types=1);

namespace eslin87\AlphaListing\Shortcode\QueryParts;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use \eslin87\AlphaListing\Shortcode\Extension;

/**
 * Columns Query Part extension
 */
class Columns extends Extension {
	/**
	 * The default number of columns.
	 *
	 * @since 4.3.2
	 * @var int
	 */
	public const DEFAULT_COLUMN_COUNT = 3;

	/**
	 * The lowest number of columns that may be configured.
	 *
	 * @since 4.3.2
	 * @var int
	 */
	public const MIN_COLUMN_COUNT = 1;

	/**
	 * The highest number of columns that may be configured.
	 *
	 * Matches MAX_POSTS_COLUMNS in scripts/blocks/constants.js. This is a different
	 * axis to the template's `.max-N-columns` class, which is derived from the item
	 * count -- the stylesheet combines the two with min().
	 *
	 * @since 4.3.2
	 * @var int
	 */
	public const MAX_COLUMN_COUNT = 15;

	/**
	 * The attribute for this Query Part.
	 *
	 * @since 4.0.0
	 * @var string
	 */
	public $attribute_name = 'columns';

	/**
	 * The number of columns.
	 *
	 * @var int
	 */
	public $columns = self::DEFAULT_COLUMN_COUNT;

	/**
	 * Sanitize the shortcode attribute.
	 *
	 * @param mixed $value      The value of the shortcode attribute.
	 * @param array $attributes The complete set of shortcode attributes.
	 * @return int The sanitized column count.
	 */
	public function sanitize_attribute( $value, array $attributes ) {
		return $this->sanitize_column_count( $value );
	}

	/**
	 * Update the query with this extension's additional configuration.
	 *
	 * @param \eslin87\AlphaListing\Query $query      The query.
	 * @param string                      $display    The display/query type.
	 * @param string                      $key        The name of the attribute.
	 * @param mixed                       $value      The shortcode attribute value.
	 * @param array                       $attributes The complete set of shortcode attributes.
	 * @return mixed The updated query.
	 */
	public function shortcode_query( $query, string $display, string $key, $value, array $attributes ) {
		$this->columns = $this->sanitize_column_count( $value );
		$this->add_hook( 'filter', 'alphalisting_styles', array( $this, 'return_styles' ), 10, 3 );
		return $query;
	}

	/**
	 * Return the stylesheet for this instance.
	 *
	 * @param string $styles      The stylesheet.
	 * @param mixed  $query       The listing query instance passed by the filter.
	 * @param mixed  $instance_id The listing instance id passed by the filter.
	 * @return string The stylesheet with the column count appended.
	 */
	public function return_styles( $styles, $query = null, $instance_id = null ): string {
		return sprintf( '%s --alphalisting-column-count: %d; ', $styles, $this->columns );
	}

	/**
	 * Ensure the provided column count is a safe integer.
	 *
	 * @param mixed $value Potential column count.
	 * @return int The sanitized column count.
	 */
	protected function sanitize_column_count( $value ): int {
		if ( is_string( $value ) ) {
			$value = trim( $value );
		}

		if ( '' === $value || null === $value ) {
			return self::DEFAULT_COLUMN_COUNT;
		}

		if ( is_numeric( $value ) ) {
			$value = (int) floor( (float) $value );
		} else {
			return self::DEFAULT_COLUMN_COUNT;
		}

		if ( $value < self::MIN_COLUMN_COUNT ) {
			return self::MIN_COLUMN_COUNT;
		}

		if ( $value > self::MAX_COLUMN_COUNT ) {
			return self::MAX_COLUMN_COUNT;
		}

		return $value;
	}
}
