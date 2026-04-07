<?php
/**
 * Alphabet Query Part.
 *
 * @package alphalisting
 */

declare(strict_types=1);

namespace eslin87\AlphaListing\Shortcode\QueryParts;

if (!defined("ABSPATH")) {
    exit();
}

use eslin87\AlphaListing\Shortcode\Extension;

/**
 * Column Width Query Part extension
 */
class ColumnWidth extends Extension {
    private const ALLOWED_UNITS = ['px', 'em', 'rem', '%', 'ch'];
    private const MAX_PIXEL_VALUE = 1200.0;
    private const MAX_PERCENT_VALUE = 100.0;
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
     * Sanitize the shortcode attribute.
     *
     * @param mixed $value      The value of the shortcode attribute.
     * @param array $attributes The complete set of shortcode attributes.
     * @return string
     */
    public function sanitize_attribute($value, array $attributes) {
        return $this->sanitize_css_length($value, self::DEFAULT_COLUMN_WIDTH);
    }

    /**
     * Update the query with this extension's additional configuration.
     *
     * @param \AlphaListing\Query $query      The query.
     * @param string             $display    The display/query type.
     * @param string             $key        The name of the attribute.
     * @param mixed              $value      The shortcode attribute value.
     * @param array              $attributes The complete set of shortcode attributes.
     * @return mixed The updated query.
     */
    public function shortcode_query($query, string $display, string $key, $value, array $attributes) {
        $this->column_width = $this->sanitize_css_length($value, self::DEFAULT_COLUMN_WIDTH);
        $this->add_hook('filter', 'alphalisting_styles', [$this, 'return_styles'], 10, 3);
        return $query;
    }

    /**
     * Return the stylesheet for this instance.
     *
     * @param string             $styles      The stylesheet.
     * @param mixed              $query       The listing query instance passed by the filter.
     * @param mixed              $instance_id The listing instance id passed by the filter.
     * @return string
     */
    public function return_styles($styles, $query = null, $instance_id = null): string {
        return sprintf('%s --alphalisting-column-width: %s; ', $styles, $this->column_width);
    }

    /**
     * Ensure the provided column width is a safe CSS length value.
     *
     * @param mixed  $value   Potential CSS length value.
     * @param string $default Default value to use when sanitization fails.
     * @return string
     */
    private function sanitize_css_length($value, string $default): string {
        if (is_string($value)) {
            $value = trim($value);
        } elseif (is_numeric($value)) {
            $value = (string) $value;
        } else {
            return $default;
        }

        if ('' === $value) {
            return $default;
        }

        if (preg_match('/^0+(?:\.0+)?$/', $value)) {
            return '0';
        }

        if (!preg_match('/^([0-9]+(?:\.[0-9]+)?)\s*(px|em|rem|%|ch)$/i', $value, $matches)) {
            return $default;
        }

        $number = (float) $matches[1];
        $unit = strtolower($matches[2]);

        if (!in_array($unit, self::ALLOWED_UNITS, true)) {
            return $default;
        }

        if ($number < 0) {
            return $default;
        }

        if ('px' === $unit) {
            $number = min($number, self::MAX_PIXEL_VALUE);
        }

        if (in_array($unit, ['%', 'ch'], true)) {
            $number = min($number, self::MAX_PERCENT_VALUE);
        }

        $number_string = $this->format_numeric_value($number);

        return $number_string . $unit;
    }

    /**
     * Normalize numeric values before concatenating with units.
     *
     * @param float $number The numeric value to format.
     * @return string
     */
    private function format_numeric_value(float $number): string {
        if (floor($number) === $number) {
            return (string) (int) $number;
        }

        return rtrim(rtrim(sprintf('%.4f', $number), '0'), '.');
    }
}
