<?php
/**
 * Column Width Query Part.
 *
 * @package alphalisting
 */

declare(strict_types=1);

namespace eslin87\AlphaListing\Shortcode\QueryParts;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Column Width Query Part extension
 */
class ColumnWidth extends CssLengthCommon {
	/**
	 * The default column width.
	 *
	 * @since 4.3.2
	 * @var string
	 */
	public const DEFAULT_COLUMN_WIDTH = '15em';

	/**
	 * The attribute for this Query Part.
	 *
	 * @since 4.0.0
	 * @var string
	 */
	public $attribute_name = 'column-width';

	/**
	 * The column width.
	 *
	 * @var string
	 */
	public $column_width = self::DEFAULT_COLUMN_WIDTH;

	/**
	 * The default length to fall back on when sanitization rejects a value.
	 *
	 * @since 4.5.0
	 * @return string The default column width.
	 */
	protected function get_default_length(): string {
		return self::DEFAULT_COLUMN_WIDTH;
	}

	/**
	 * The CSS custom property this attribute is emitted as.
	 *
	 * @since 4.5.0
	 * @return string The custom property name.
	 */
	protected function get_css_property(): string {
		return '--alphalisting-column-width';
	}

	/**
	 * Store the sanitized column width.
	 *
	 * @since 4.5.0
	 * @param string $length The sanitized CSS length.
	 * @return void
	 */
	protected function set_length( string $length ) {
		$this->column_width = $length;
	}

	/**
	 * Retrieve the configured column width.
	 *
	 * @since 4.5.0
	 * @return string The column width.
	 */
	protected function get_length(): string {
		return $this->column_width;
	}
}
