<?php
/**
 * Server-side rendering of the `alphalisting` block.
 *
 * @package WordPress
 */

declare(strict_types=1);

namespace eslin87\AlphaListing;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Server-side rendering of the `alphalisting` block implementation class.
 *
 * @package WordPress
 */
class GutenBlock extends Singleton implements Extension {
	/**
	 * Render the block.
	 *
	 * @since 4.0.0
	 * @param array $attributes The block configured attributes.
	 * @return string The block content.
	 */
	public function render( $attributes ) {
		global $shortcode_tags;
		if ( empty( $shortcode_tags ) || ! is_array( $shortcode_tags ) || ! array_key_exists( 'alphalisting', $shortcode_tags ) ) {
			return 'The AlphaListing plugin has been disabled.';
		}

		return call_user_func( $shortcode_tags['alphalisting'], $attributes );
	}

	/**
	 * Register and initialize the block.
	 *
	 * @since 4.0.0
	 * @return void
	 * @throws \Error When the plugin has not been correctly built.
	 */
	final public function initialize() {
		$script_asset_path = dirname( ALPHALISTING_PLUGIN_FILE ) . '/build/index.asset.php';
		if ( ! file_exists( $script_asset_path ) ) {
			throw new \Error(
				'You need to run `npm start` or `npm run build` for the "alphalisting/block" block first.'
			);
		}
		$index_js     = 'build/index.js';
		$script_asset = require $script_asset_path;
		wp_register_script(
			'alphalisting-block-editor',
			plugins_url( $index_js, ALPHALISTING_PLUGIN_FILE ),
			$script_asset['dependencies'],
			ALPHALISTING_VERSION,
			true
		);

		$editor_css = 'css/editor.css';
		wp_register_style(
			'alphalisting-block-editor',
			plugins_url( $editor_css, ALPHALISTING_PLUGIN_FILE ),
			array(),
			ALPHALISTING_VERSION
		);

		$style_css = 'css/alphalisting-default.css';
		wp_register_style(
			'alphalisting-block',
			plugins_url( $style_css, ALPHALISTING_PLUGIN_FILE ),
			array(),
			ALPHALISTING_VERSION
		);

		$attributes = json_decode( file_get_contents( dirname( ALPHALISTING_PLUGIN_FILE ) . '/scripts/blocks/attributes.json' ), true );  //phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
		$attributes = apply_filters( 'alphalisting_get_gutenberg_attributes', $attributes );

		register_block_type(
			'alphalisting/block',
			array(
				'editor_script'   => 'alphalisting-block-editor',
				'editor_style'    => 'alphalisting-block-editor',
				'style'           => 'alphalisting-block',
				'render_callback' => array( $this, 'render' ),
				'attributes'      => $attributes,
			)
		);
	}
}
