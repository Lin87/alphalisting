/**
 * Registers a new block provided a unique name and an object defining its behavior.
 *
 * @see https://developer.wordpress.org/block-editor/developers/block-api/#registering-a-block
 */
import { __ } from '@wordpress/i18n';

import { createBlock, registerBlockType } from '@wordpress/blocks';
import { createReduxStore, register } from '@wordpress/data';
import domReady from '@wordpress/dom-ready';
import { applyFilters } from '@wordpress/hooks';
import { postList as icon } from '@wordpress/icons';
import { attrs } from '@wordpress/shortcode';

import edit from './edit';
import globalAttributes from './attributes.json';
import DisplayOptions from '../components/DisplayOptions';
import ItemSelection from '../components/ItemSelection';
import Extensions from '../components/Extensions';
import AZInspectorControls from '../components/AZInspectorControls';

import shortcodeUpgrader from './shortcode-upgrader';

const store = createReduxStore( 'alphalisting/slotfills', {
	reducer( state = {} ) {
		return state;
	},
	selectors: {
		getDisplayOptions() {
			return DisplayOptions;
		},
		getItemSelection() {
			return ItemSelection;
		},
		getExtensions() {
			return Extensions;
		},
		getInspectorControls() {
			return AZInspectorControls;
		},
	},
} );
register( store );

domReady( () => {
	const attributes = applyFilters( 'alphalisting_attributes', globalAttributes );

	/**
	 * Every block starts by registering a new block type definition.
	 *
	 * @see https://developer.wordpress.org/block-editor/developers/block-api/#registering-a-block
	 */
	registerBlockType( 'alphalisting/block', {
		/**
		 * This is the display title for your block, which can be translated with `i18n` functions.
		 * The block inserter will show this name.
		 */
		title: __( 'AlphaListing', 'alphalisting' ),

		/**
		 * This is a short description for your block, can be translated with `i18n` functions.
		 * It will be shown in the Block Tab in the Settings Sidebar.
		 */
		description: __(
			'Show your posts in an alphabetically-ordered rolodex-style list',
			'alphalisting'
		),

		/**
		 * Blocks are grouped into categories to help users browse and discover them.
		 * The categories provided by core are `common`, `embed`, `formatting`, `layout` and `widgets`.
		 */
		category: 'widgets',

		/**
		 * An icon property should be specified to make it easier to identify a block.
		 * These can be any of WordPress’ Dashicons, or a custom svg element.
		 */
		icon,

		/**
		 * Optional block extended support features.
		 */
		supports: {
			align: true,
			html: false,
		},

		attributes,

		edit,

		save: () => null,

		transforms: {
			from: [
				{
					type: 'prefix',
					prefix: '[alphalisting',
					transform() {
						return createBlock( 'alphalisting/block' );
					},
				},
				{
					type: 'prefix',
					prefix: '[alphalisting]',
					transform() {
						return createBlock( 'alphalisting/block' );
					},
				},
				{
					type: 'raw',
					isMatch: ( node ) => node.nodeName === 'P' && /^\s*\[alphalisting.*\]\s*$/.test( node.textContent ),
					transform( node ) {
						const atts = attrs( node.textContent.trim() );
						return createBlock( 'alphalisting/block', atts );
					},
				},
			],
		},
	} );

	shortcodeUpgrader();
} );
