<?php
/**
 * Column Gap Query Part.
 *
 * @package alphalisting
 */

declare(strict_types=1);

namespace eslin87\AlphaListing\Shortcode\QueryParts;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Column Gap Query Part extension
 */
class ColumnGap extends CssLengthCommon {
	/**
	 * The default column gap.
	 *
	 * @since 4.3.2
	 * @var string
	 */
	public const DEFAULT_COLUMN_GAP = '0.6em';

	/**
	 * The attribute for this Query Part.
	 *
	 * @since 4.0.0
	 * @var string
	 */
	public $attribute_name = 'column-gap';

	/**
	 * The column gap.
	 *
	 * @var string
	 */
	public $column_gap = self::DEFAULT_COLUMN_GAP;

	/**
	 * The default length to fall back on when sanitization rejects a value.
	 *
	 * @since 4.5.0
	 * @return string The default column gap.
	 */
	protected function get_default_length(): string {
		return self::DEFAULT_COLUMN_GAP;
	}

	/**
	 * The CSS custom property this attribute is emitted as.
	 *
	 * @since 4.5.0
	 * @return string The custom property name.
	 */
	protected function get_css_property(): string {
		return '--alphalisting-column-gap';
	}

	/**
	 * Store the sanitized column gap.
	 *
	 * @since 4.5.0
	 * @param string $length The sanitized CSS length.
	 * @return void
	 */
	protected function set_length( string $length ) {
		$this->column_gap = $length;
	}

	/**
	 * Retrieve the configured column gap.
	 *
	 * @since 4.5.0
	 * @return string The column gap.
	 */
	protected function get_length(): string {
		return $this->column_gap;
	}
}
