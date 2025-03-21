<?php
/**
 * Alphabet Query Part.
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
 * Alphabet Query Part extension
 */
class Alphabet extends Extension {
	/**
	 * The attribute for this Query Part.
	 *
	 * @since 4.0.0
	 * @var string
	 */
	public $attribute_name = 'alphabet';

	/**
	 * The alphabet.
	 *
	 * @var string
	 */
	public $alphabet = '';

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
	public function shortcode_query( $query, string $display, string $key, $value, array $attributes ) {
		$this->alphabet = $value;
		$this->add_hook( 'filter', 'alphalisting-alphabet', array( $this, 'return_alphabet' ), 1, 1 );
		return $query;
	}

	/**
	 * Return the Alphabet for this instance.
	 *
	 * @return string
	 */
	public function return_alphabet(): string {
		return $this->alphabet;
	}
}
