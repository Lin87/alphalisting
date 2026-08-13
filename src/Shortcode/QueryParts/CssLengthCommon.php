<?php
/**
 * CSS length Query Part common implementation.
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
 * Shared implementation for Query Parts whose attribute is a CSS length that is
 * emitted as a custom property by the `alphalisting_styles` filter.
 *
 * Subclasses supply the attribute name, the default length, the CSS custom
 * property, and where to store the sanitized value.
 *
 * @since 4.5.0
 */
abstract class CssLengthCommon extends Extension {
	/**
	 * The CSS units this attribute accepts.
	 *
	 * @since 4.5.0
	 * @var array<int,string>
	 */
	protected const ALLOWED_UNITS = array( 'px', 'em', 'rem', '%', 'ch' );

	/**
	 * Upper bound applied to values expressed in pixels.
	 *
	 * @since 4.5.0
	 * @var float
	 */
	protected const MAX_PIXEL_VALUE = 1200.0;

	/**
	 * Upper bound applied to values expressed in percent or ch units.
	 *
	 * @since 4.5.0
	 * @var float
	 */
	protected const MAX_PERCENT_VALUE = 100.0;

	/**
	 * The default length to fall back on when sanitization rejects a value.
	 *
	 * @since 4.5.0
	 * @return string The default CSS length.
	 */
	abstract protected function get_default_length(): string;

	/**
	 * The CSS custom property this attribute is emitted as.
	 *
	 * @since 4.5.0
	 * @return string The custom property name, including the leading `--`.
	 */
	abstract protected function get_css_property(): string;

	/**
	 * Store the sanitized length on the subclass.
	 *
	 * @since 4.5.0
	 * @param string $length The sanitized CSS length.
	 * @return void
	 */
	abstract protected function set_length( string $length );

	/**
	 * Retrieve the currently configured length.
	 *
	 * @since 4.5.0
	 * @return string The CSS length.
	 */
	abstract protected function get_length(): string;

	/**
	 * Sanitize the shortcode attribute.
	 *
	 * @param mixed $value      The value of the shortcode attribute.
	 * @param array $attributes The complete set of shortcode attributes.
	 * @return string The sanitized CSS length.
	 */
	public function sanitize_attribute( $value, array $attributes ) {
		return $this->sanitize_css_length( $value, $this->get_default_length() );
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
		$this->set_length( $this->sanitize_css_length( $value, $this->get_default_length() ) );
		$this->add_hook( 'filter', 'alphalisting_styles', array( $this, 'return_styles' ), 10, 3 );
		return $query;
	}

	/**
	 * Return the stylesheet for this instance.
	 *
	 * @param string $styles      The stylesheet.
	 * @param mixed  $query       The listing query instance passed by the filter.
	 * @param mixed  $instance_id The listing instance id passed by the filter.
	 * @return string The stylesheet with this attribute's custom property appended.
	 */
	public function return_styles( $styles, $query = null, $instance_id = null ): string {
		return sprintf( '%s %s: %s; ', $styles, $this->get_css_property(), $this->get_length() );
	}

	/**
	 * Ensure the provided value is a safe CSS length.
	 *
	 * @since 4.5.0
	 * @param mixed  $value   Potential CSS length value.
	 * @param string $default Default value to use when sanitization fails.
	 * @return string The sanitized CSS length.
	 */
	protected function sanitize_css_length( $value, string $default ): string {
		if ( is_string( $value ) ) {
			$value = trim( $value );
		} elseif ( is_numeric( $value ) ) {
			$value = (string) $value;
		} else {
			return $default;
		}

		if ( '' === $value ) {
			return $default;
		}

		if ( preg_match( '/^0+(?:\.0+)?$/', $value ) ) {
			return '0';
		}

		// The alternation is built from ALLOWED_UNITS so the constant stays the single
		// source of truth for which units are accepted.
		$units = implode(
			'|',
			array_map(
				function ( string $unit ): string {
					return preg_quote( $unit, '/' );
				},
				self::ALLOWED_UNITS
			)
		);

		if ( ! preg_match( '/^([0-9]+(?:\.[0-9]+)?)\s*(' . $units . ')$/i', $value, $matches ) ) {
			return $default;
		}

		$number = (float) $matches[1];
		$unit   = strtolower( $matches[2] );

		if ( $number < 0 ) {
			return $default;
		}

		if ( 'px' === $unit ) {
			$number = min( $number, self::MAX_PIXEL_VALUE );
		}

		if ( in_array( $unit, array( '%', 'ch' ), true ) ) {
			$number = min( $number, self::MAX_PERCENT_VALUE );
		}

		return $this->format_numeric_value( $number ) . $unit;
	}

	/**
	 * Normalize numeric values before concatenating with units.
	 *
	 * @since 4.5.0
	 * @param float $number The numeric value to format.
	 * @return string The formatted number.
	 */
	private function format_numeric_value( float $number ): string {
		if ( floor( $number ) === $number ) {
			return (string) (int) $number;
		}

		return rtrim( rtrim( sprintf( '%.4f', $number ), '0' ), '.' );
	}
}
