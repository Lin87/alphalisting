/**
 * Retrieves the translation of text.
 *
 * @see https://developer.wordpress.org/block-editor/packages/packages-i18n/
 */

import { useEffect, useMemo } from '@wordpress/element';
import { FormTokenField, PanelBody, Placeholder, RangeControl, SelectControl, Spinner, ToggleControl, TextControl, __experimentalUnitControl as UnitControl, __experimentalSpacer as Spacer } from '@wordpress/components';
import * as ServerSideRenderModule from '@wordpress/server-side-render';
import { __ } from '@wordpress/i18n';
import { InspectorControls } from '@wordpress/block-editor';
import { useSelect } from '@wordpress/data';
import { store as coreStore } from '@wordpress/core-data';
import { pin } from '@wordpress/icons';
import { addFilter, applyFilters } from '@wordpress/hooks';
import {v4 as uuid} from 'uuid';

/**
 * Internal dependencies
 */
import { MAX_COLUMN_WIDTH, MAX_POSTS_COLUMNS } from './constants';

import defaults from './attributes.json';
import ItemSelection from '../components/ItemSelection';
import DisplayOptions from '../components/DisplayOptions';
import Extensions from '../components/Extensions';
import AZInspectorControls from '../components/AZInspectorControls';
import PostParent from '../components/PostParent';

/**
 * Filters to kill any stale state when selections are changed in the editor
 */
addFilter(
	'alphalisting_selection_changed_for__display',
	'alphalisting',
	( attributes ) => ( {
		...attributes,
		'post-type': defaults['post-type'].default,
		taxonomy: defaults.taxonomy.default,
		terms: [ ...defaults.terms.default ],
		'exclude-posts': [ ...defaults['exclude-posts'].default ],
		'exclude-terms': [ ...defaults['exclude-terms'].default ],
		'parent-post': defaults['parent-post'].default,
		'parent-term': defaults['parent-term'].default,
		'hide-empty-terms': defaults['hide-empty-terms'].default,
		'get-all-children': defaults['get-all-children'].default,
	} ),
	5
);
addFilter(
	'alphalisting_selection_changed_for__post-type',
	'alphalisting',
	( attributes ) => ( {
		...attributes,
		taxonomy: defaults.taxonomy.default,
		terms: [ ...defaults.terms.default ],
		'exclude-posts': [ ...defaults['exclude-posts'].default ],
		'parent-post': defaults['parent-post'].default,
	} ),
	5
);
addFilter(
	'alphalisting_selection_changed_for__taxonomy',
	'alphalisting',
	( attributes ) => ( {
		...attributes,
		terms: [ ...defaults.terms.default ],
		'exclude-terms': [ ...defaults['exclude-terms'].default ],
		'parent-term': defaults['parent-term'].default,
		'get-all-children': defaults['get-all-children'].default,
	} ),
	5
);

const ServerSideRender = ServerSideRenderModule.ServerSideRender || ServerSideRenderModule.default;
const displayTypes = applyFilters(
	'alphalisting_display_types',
	[
		{ value: 'posts', label: __( 'Posts', 'alphalisting' ) },
		{ value: 'terms', label: __( 'Taxonomy Terms', 'alphalisting' ) },
	]
);
const LENGTH_VALUE_PATTERN = /^([0-9]+(?:\.[0-9]+)?)\s*(px|em|rem|%|ch)$/i;
const UNIT_LESS_ZERO_PATTERN = /^0+(?:\.0+)?$/;
const LENGTH_UNITS = [
	{ value: 'px', label: 'px', step: 1, min: 0, max: MAX_COLUMN_WIDTH },
	{ value: 'em', label: 'em', step: 0.1, min: 0 },
	{ value: 'rem', label: 'rem', step: 0.1, min: 0 },
	{ value: '%', label: '%', step: 1, min: 0, max: 100 },
	{ value: 'ch', label: 'ch', step: 1, min: 0, max: 100 },
];

/**
 * Convert an arbitrary value into a bounded integer column count.
 *
 * @param {unknown} value Raw attribute value.
 * @return {number} Sanitized column count.
 */
const sanitizeColumnCount = ( value ) => {
	if ( typeof value === 'number' && Number.isFinite( value ) ) {
		value = Math.trunc( value );
	} else if ( typeof value === 'string' && value.trim() !== '' && ! Number.isNaN( Number( value ) ) ) {
		value = Math.trunc( Number( value ) );
	} else {
		value = defaults.columns.default;
	}

	if ( value < 1 ) {
		return 1;
	}

	if ( value > MAX_POSTS_COLUMNS ) {
		return MAX_POSTS_COLUMNS;
	}

	return value;
};

/**
 * Ensure a CSS length string uses an allowed unit and within safe bounds.
 *
 * @param {unknown} value Raw attribute value.
 * @param {string} fallback Default value when validation fails.
 * @return {string} Sanitized CSS length string.
 */
const sanitizeLengthValue = ( value, fallback ) => {
	if ( typeof value !== 'string' ) {
		if ( typeof value === 'number' && Number.isFinite( value ) ) {
			value = String( value );
		} else {
			return fallback;
		}
	}

	const trimmed = value.trim();

	if ( trimmed === '' ) {
		return fallback;
	}

	if ( UNIT_LESS_ZERO_PATTERN.test( trimmed ) ) {
		return '0';
	}

	const match = trimmed.match( LENGTH_VALUE_PATTERN );

	if ( ! match ) {
		return fallback;
	}

	const numeric = Number.parseFloat( match[ 1 ] );
	const unit = match[ 2 ].toLowerCase();

	if ( ! Number.isFinite( numeric ) || numeric < 0 ) {
		return fallback;
	}

	let bounded = numeric;

	if ( unit === 'px' ) {
		bounded = Math.min( bounded, MAX_COLUMN_WIDTH );
	}

	if ( unit === '%' || unit === 'ch' ) {
		bounded = Math.min( bounded, 100 );
	}

	if ( Number.isInteger( bounded ) ) {
		return `${ bounded }${ unit }`;
	}

	return `${ parseFloat( bounded.toFixed( 4 ) ) }${ unit }`;
};

/**
 * Convert a value into a positive integer or null when invalid.
 *
 * @param {unknown} value Raw attribute value.
 * @return {?number} Sanitized integer or null when invalid.
 */
const sanitizePositiveInteger = ( value ) => {
	if ( typeof value === 'number' && Number.isFinite( value ) ) {
		value = Math.trunc( value );
	} else if ( typeof value === 'string' ) {
		const trimmed = value.trim();

		if ( trimmed === '' ) {
				return null;
		}

		const numeric = Number( trimmed );

		if ( Number.isNaN( numeric ) ) {
				return null;
		}

		value = Math.trunc( numeric );
	} else if ( Array.isArray( value ) ) {
		// When the attribute is serialized as a list of tokens already.
		return null;
	} else {
		return null;
	}

	if ( value <= 0 ) {
		return null;
	}

	return value;
};

/**
 * Ensure a list of identifiers only contains unique positive integers.
 *
 * @param {unknown} values Raw attribute value.
 * @return {number[]} Sanitized list of identifiers.
 */
const sanitizeNumericTokenList = ( values ) => {
	if ( typeof values === 'string' ) {
		values = values.split( ',' );
	}

	if ( ! Array.isArray( values ) ) {
		return [];
	}

	const sanitized = values
		.map( sanitizePositiveInteger )
		.filter( ( value ) => value !== null );

	return Array.from( new Set( sanitized ) );
};

/**
 * Ensure a list of tokens is stored as unique, trimmed strings.
 *
 * @param {unknown} values Raw attribute value.
 * @return {string[]} Sanitized list of string tokens.
 */
const sanitizeStringTokenList = ( values ) => {
	if ( typeof values === 'string' ) {
		values = values.split( ',' );
	}

	if ( ! Array.isArray( values ) ) {
		return [];
	}

	const sanitized = values
		.map( ( value ) => {
			if ( typeof value === 'string' ) {
				return value.trim();
			}

			if ( typeof value === 'number' && Number.isFinite( value ) ) {
				return String( value );
			}

			return '';
		} )
		.filter( ( value ) => value !== '' );

	return Array.from( new Set( sanitized ) );
};

/**
 * Normalize selected post types into unique string slugs.
 *
 * @param {unknown} values Raw attribute value.
 * @return {string[]} Sanitized post type slugs.
 */
const sanitizePostTypeList = ( values ) => {
	if ( typeof values === 'string' ) {
		values = values.split( ',' );
	}

	return sanitizeStringTokenList( values );
};

const A_Z_Listing_Edit = ( { attributes, setAttributes } ) => {
	const { postTypes, allTaxonomies } = useSelect( ( select ) => {
		const { getPostTypes, getTaxonomies } = select( coreStore );
		const excludedPostTypes = [ 'attachment' ];
		const filteredPostTypes = getPostTypes( { per_page: -1 } )?.filter(
			( { viewable, slug } ) =>
				viewable && ! excludedPostTypes.includes( slug )
		);
		const taxonomies = getTaxonomies() ?? [];
		return { postTypes: filteredPostTypes, allTaxonomies: taxonomies };
	} );

	const postTypesMap = useMemo( () => {
		if ( ! postTypes?.length ) return;
		return postTypes.reduce( ( accumulator, type ) => {
			accumulator[ type.slug ] = type;
			return accumulator;
		}, {} );
	}, [ postTypes ] );

	const postTypesSelectOptions = useMemo( () => {
		if ( ! postTypes?.length ) return;
		return ( postTypes ).map( ( { labels, slug } ) => ( {
			label: labels.name,
			value: slug,
		} ) );
	}, [ postTypes ] );

	const postTypesTaxonomiesMap = useMemo( () => {
		if ( ! postTypes?.length ) return;
		return postTypes.reduce( ( accumulator, type ) => {
			accumulator[ type.slug ] = type.taxonomies;
			return accumulator;
		}, {} );
	}, [ postTypes ] );

	const postTypesTaxonomiesSelectOptions = useMemo( () => {
		let postTaxonomies = [];
		const selectedPostTypes = sanitizePostTypeList( attributes['post-type'] ?? defaults['post-type'].default );
		if ( attributes['display'] === 'posts' && selectedPostTypes.length > 0 && postTypesTaxonomiesMap ) {
			postTaxonomies = selectedPostTypes.flatMap(
				( postType ) => postTypesTaxonomiesMap[ postType ] || []
			);
			postTaxonomies = Array.from( new Set( postTaxonomies ) );
		}
		return [ { label: '', slug: '' } ].concat(
			allTaxonomies
				.filter( ( tax ) => postTaxonomies.includes( tax.slug ) )
				.map( ( tax ) => ( {
					label: tax.name,
					value: tax.slug,
				} ) )
		);
	}, [ attributes.display, attributes['post-type'], postTypesTaxonomiesMap, allTaxonomies ]);

	const taxonomiesSelectOptions = useMemo( () => {
		return [ { label: '', slug: '' } ].concat(
			allTaxonomies.map( ( tax ) => ( {
				label: tax.name,
				value: tax.slug,
			} ) )
		);
	}, [ allTaxonomies ] );

	const validationErrors = useMemo( () => {
		const errors = [];
			if ( 'terms' === attributes.display && ! attributes.taxonomy ) {
				errors.push(
					__(
						`You must set a taxonomy when display mode is set to 'terms'.`,
						'alphalisting'
					)
				);
			}
			return errors;
	}, [ attributes.display, attributes.taxonomy ] );

	const sanitizedColumns = useMemo(
		() => sanitizeColumnCount( attributes.columns ),
		[ attributes.columns ]
	);

	const sanitizedColumnWidth = useMemo(
		() => sanitizeLengthValue(
			attributes['column-width'] ?? defaults['column-width'].default,
			defaults['column-width'].default
		),
		[ attributes['column-width'] ]
	);
	
	const sanitizedColumnGap = useMemo(
		() => sanitizeLengthValue(
			attributes['column-gap'] ?? defaults['column-gap'].default,
			defaults['column-gap'].default
		),
		[ attributes['column-gap'] ]
	);

	useEffect( () => {
		if ( typeof attributes.columns !== 'undefined' && attributes.columns !== sanitizedColumns ) {
			setAttributes( { columns: sanitizedColumns } );
		}
	}, [ attributes.columns, sanitizedColumns, setAttributes ] );

	useEffect( () => {
		if ( typeof attributes['column-width'] !== 'undefined' && attributes['column-width'] !== sanitizedColumnWidth ) {
			setAttributes( { 'column-width': sanitizedColumnWidth } );
		}
	}, [ attributes['column-width'], sanitizedColumnWidth, setAttributes ] );

	useEffect( () => {
		if ( typeof attributes['column-gap'] !== 'undefined' && attributes['column-gap'] !== sanitizedColumnGap ) {
			setAttributes( { 'column-gap': sanitizedColumnGap } );
		}
	}, [ attributes['column-gap'], sanitizedColumnGap, setAttributes ] );

	useEffect( () => {
		if ( typeof attributes['parent-term'] === 'number' ) {
			setAttributes( { 'parent-term': String( attributes['parent-term'] ) } );
		}
	}, [ attributes['parent-term'], setAttributes ] );

	useEffect( () => {
		if ( typeof attributes['exclude-posts'] === 'undefined' ) {
			return;
		}

		const sanitized = sanitizeNumericTokenList( attributes['exclude-posts'] );

		if ( JSON.stringify( sanitized ) !== JSON.stringify( attributes['exclude-posts'] ) ) {
			setAttributes( { 'exclude-posts': sanitized } );
		}
	}, [ attributes['exclude-posts'], setAttributes ] );

	useEffect( () => {
		if ( typeof attributes['exclude-terms'] === 'undefined' ) {
			return;
		}

		const sanitized = sanitizeNumericTokenList( attributes['exclude-terms'] )
			.map( ( token ) => token.toString() );

		if ( JSON.stringify( sanitized ) !== JSON.stringify( attributes['exclude-terms'] ) ) {
			setAttributes( { 'exclude-terms': sanitized } );
		}
	}, [ attributes['exclude-terms'], setAttributes ] );

	useEffect( () => {
		if ( typeof attributes.terms === 'undefined' ) {
			return;
		}

		let sanitized;

		if ( attributes.display === 'terms' ) {
			sanitized = sanitizeNumericTokenList( attributes.terms )
				.map( ( token ) => token.toString() );
		} else {
			sanitized = sanitizeStringTokenList( attributes.terms );
		}

		if ( JSON.stringify( sanitized ) !== JSON.stringify( attributes.terms ) ) {
			setAttributes( { terms: sanitized } );
		}
	}, [ attributes.terms, attributes.display, setAttributes ] );

	useEffect( () => {
		if ( typeof attributes['post-type'] === 'undefined' ) {
			return;
		}

		const sanitized = sanitizePostTypeList( attributes['post-type'] );
		const normalized = sanitized.join( ',' );
		const current = Array.isArray( attributes['post-type'] )
			? attributes['post-type'].join( ',' )
			: String( attributes['post-type'] ?? '' ).trim();

		if ( normalized !== current ) {
			setAttributes( { 'post-type': normalized } );
		}
	}, [ attributes['post-type'], setAttributes ] );

	const excludePostsTokens = useMemo(
		() => sanitizeNumericTokenList( attributes['exclude-posts'] ).map( ( token ) => token.toString() ),
		[ attributes['exclude-posts'] ]
	);

	const excludeTermsTokens = useMemo(
		() => sanitizeNumericTokenList( attributes['exclude-terms'] ).map( ( token ) => token.toString() ),
		[ attributes['exclude-terms'] ]
	);
	const selectedPostTypes = useMemo(
		() => sanitizePostTypeList( attributes['post-type'] ?? defaults['post-type'].default ),
		[ attributes['post-type'] ]
	);
	const selectedPostTypeForParent = selectedPostTypes.length === 1
		? selectedPostTypes[ 0 ]
		: '';

	const parentTermValue = typeof attributes['parent-term'] === 'undefined'
		? defaults['parent-term'].default
		: String( attributes['parent-term'] ?? '' );
	const hasTermParentSelection = attributes.display === 'terms'
		&& ( parentTermValue.trim() !== '' );
	const showDescendantsToggle = hasTermParentSelection;

    const inspectorControls = (
        <InspectorControls>
			<AZInspectorControls.Slot>
				{ ( fills ) => (
					<>
						<PanelBody title={ __( 'Listing selection', 'alphalisting' ) }>
							<ItemSelection.Slot>
								{ ( subFills ) => (
									<>
										<SelectControl
											label={ __( 'Display mode', 'alphalisting' ) }
											value={ attributes.display ?? defaults['display'].default }
											options={ displayTypes }
											onChange={ ( value ) =>
												setAttributes(
													applyFilters(
														'alphalisting_selection_changed_for__display',
														{ display: value }
													)
												)
											}
											__next40pxDefaultSize
											__nextHasNoMarginBottom
										/>

										{ 'posts' === attributes.display && (
											<SelectControl
												label={ __( 'Post Type', 'alphalisting' ) }
												help={ __( 'Select the post types to display. Hold Ctrl to select multiple.', 'alphalisting' ) }
												value={ selectedPostTypes }
												options={ postTypesSelectOptions }
												onChange={ ( value ) =>
													setAttributes(
														applyFilters(
															'alphalisting_selection_changed_for__post-type',
															{ 'post-type': sanitizePostTypeList( value ).join( ',' ) }
														)
													)
												}
												multiple
												__next40pxDefaultSize
												__nextHasNoMarginBottom
											/>
										) }

										{ (
											'posts' === attributes.display &&
											selectedPostTypeForParent &&
											postTypesMap && postTypesMap[ selectedPostTypeForParent ]?.hierarchical
										) && (
											<PostParent
												pageId={ attributes['parent-post'] ?? defaults['parent-post'].default }
												postTypeSlug={ selectedPostTypeForParent }
												onChange={ ( parentId ) => setAttributes( { 'parent-post': parentId } ) }
											/>
										) }

										{ (
											( 'posts' === attributes.display && postTypesTaxonomiesSelectOptions.length > 1 ) ||
											'terms' === attributes.display
										) && (
											<SelectControl
												label={ __( 'Taxonomy', 'alphalisting' ) }
												value={ attributes.taxonomy ?? '' }
												options={
													'posts' === attributes.display
													? postTypesTaxonomiesSelectOptions
													: taxonomiesSelectOptions
												}
												onChange={ ( value ) =>
													setAttributes(
														applyFilters(
															'alphalisting_selection_changed_for__taxonomy',
															{ taxonomy: value }
														)
													)
												}
												__next40pxDefaultSize
												__nextHasNoMarginBottom
											/>
										) }

										{ 'posts' === attributes.display && !! attributes.taxonomy && (
											<FormTokenField
												label={ __( 'Taxonomy terms', 'alphalisting' ) }
												value={ attributes.terms ?? [] }
												onChange={ ( value ) =>
													setAttributes( {
														terms: sanitizeStringTokenList( value ),
													} )
												}
												__next40pxDefaultSize
												__nextHasNoMarginBottom
											/>
										) }

										{ 'posts' === attributes.display && (
											<>
											<Spacer marginBottom={'16px'}  />
											<FormTokenField
												label={ __( 'Exclude post IDs', 'alphalisting' ) }
												value={ excludePostsTokens }
												onChange={ ( value ) =>
													setAttributes( {
														'exclude-posts': sanitizeNumericTokenList( value ),
													} )
												}
												help={ __( 'Provide numeric post IDs to omit from the listing.', 'alphalisting' ) }
												__next40pxDefaultSize
												__nextHasNoMarginBottom
											/>
											</>
										) }

										{ 'terms' === attributes.display && (
											<TextControl
												label={ __( 'Parent term (slug or ID)', 'alphalisting' ) }
												value={ parentTermValue }
												onChange={ ( value ) =>
													setAttributes( {
														'parent-term': value.trim(),
													} )
												}
												__next40pxDefaultSize
												__nextHasNoMarginBottom
											/>
										) }

										{ ( 'terms' === attributes.display ) && (
											<>
											<Spacer marginBottom={'16px'} />
											<FormTokenField
												label={ __( 'Exclude term IDs', 'alphalisting' ) }
												value={ excludeTermsTokens }
												onChange={ ( value ) =>
													setAttributes( {
														'exclude-terms': sanitizeNumericTokenList( value )
															.map( ( token ) => token.toString() ),
													} )
												}
												help={ __( 'Provide numeric term IDs to omit from the listing.', 'alphalisting' ) }
												__next40pxDefaultSize
												__nextHasNoMarginBottom
											/>
											<Spacer marginBottom={'16px'}  />
											</>
										) }

										{ showDescendantsToggle && (
											<ToggleControl
												label={ __( 'Include all descendants', 'alphalisting' ) }
												help={ __( 'Include items from all levels below the selected parent.', 'alphalisting' ) }
												checked={ !! attributes['get-all-children'] }
												onChange={ ( value ) =>
													setAttributes( { 'get-all-children': value } )
												}
												__next40pxDefaultSize
												__nextHasNoMarginBottom
											/>
										) }

										{ 'terms' === attributes.display && (
											<ToggleControl
												label={ __( 'Hide empty terms', 'alphalisting' ) }
												checked={ !! attributes['hide-empty-terms'] }
												onChange={ ( value ) =>
														setAttributes( { 'hide-empty-terms': value } )
												}
												__next40pxDefaultSize
												__nextHasNoMarginBottom
											/>
										) }

										{ subFills }
									</>
								) }
							</ItemSelection.Slot>
						</PanelBody>

						<PanelBody title={ __( 'Display options', 'alphalisting' ) }>
							<DisplayOptions.Slot>
								{ ( subFills ) => (
									<>
										<TextControl
											label={ __( 'Listing ID', 'alphalisting' ) }
											value={ attributes['instance-id'] ?? uuid() }
											onChange={ (value) =>
												setAttributes( { 'instance-id': value } )
											}
											__next40pxDefaultSize
											__nextHasNoMarginBottom
										/>
										<TextControl
											label={ __( 'CSS class names', 'alphalisting' ) }
											value={ attributes.className ?? '' }
											onChange={ ( value ) =>
													setAttributes( { className: value } )
											}
											__next40pxDefaultSize
											__nextHasNoMarginBottom
										/>
										<TextControl
											label={ __( 'Alphabet', 'alphalisting' ) }
											value={ attributes.alphabet ?? defaults['alphabet'].default }
											onChange={ ( value ) =>
													setAttributes( { alphabet: value } )
											}
											__next40pxDefaultSize
											__nextHasNoMarginBottom
										/>
										<SelectControl
											label={ __( 'Numbers', 'alphalisting' ) }
											value={ attributes.numbers ?? defaults['numbers'].default }
											options={ [
												{
													value: 'hide',
													label: __(
														'Hide numbers',
														'alphalisting'
													),
												},
												{
													value: 'before',
													label: __(
														'Prepend before alphabet',
														'alphalisting'
													),
												},
												{
													value: 'after',
													label: __(
														'Append after alphabet',
														'alphalisting'
													),
												},
											] }
											onChange={ ( value ) =>
												setAttributes(
													applyFilters(
														'alphalisting_selection_changed_for__numbers',
														{ numbers: value }
													)
												)
											}
											__next40pxDefaultSize
											__nextHasNoMarginBottom
										/>

										<RangeControl
											label={ __( 'Group letters', 'alphalisting' ) }
											help={ __(
												'The number of letters to include in a single group',
												'alphalisting'
											) }
											value={ attributes.grouping ?? defaults['grouping'].default }
											min={ 1 }
											max={ 10 }
											onChange={ ( value ) =>
												setAttributes(
													applyFilters(
														'alphalisting_selection_changed_for__grouping',
														{ grouping: value }
													)
												)
											}
											withInputField
											__next40pxDefaultSize
											__nextHasNoMarginBottom
										/>

										{ 'hide' !== attributes.numbers &&
											! (
												1 < attributes.grouping
											) && (
												<ToggleControl
													label={ __(
														'Group numbers',
														'alphalisting'
													) }
													help={ __(
														'Group 0-9 as a single letter',
														'alphalisting'
													) }
													checked={ !! attributes['group-numbers'] }
													onChange={ ( value ) =>
														setAttributes(
															applyFilters(
																'alphalisting_selection_changed_for__group-numbers',
																{
																	'group-numbers': !! value,
																}
															)
														)
													}
													__next40pxDefaultSize
													__nextHasNoMarginBottom
												/>
											) }

										<ToggleControl
											label={ __( 'Display symbols entry first', 'alphalisting' ) }
											checked={ !!attributes['symbols-first'] }
											onChange={ ( value ) =>
												setAttributes( { 'symbols-first': value } )
											}
											__next40pxDefaultSize
											__nextHasNoMarginBottom
										/>
										<ToggleControl
											label={ __( 'Show back to top link', 'alphalisting' ) }
											checked={ !! attributes['back-to-top'] }
											onChange={ ( value ) =>
												setAttributes( { 'back-to-top': value } )
											}
											__next40pxDefaultSize
											__nextHasNoMarginBottom
										/>

										<RangeControl
											label={ __( 'Columns', 'alphalisting' ) }
											value={ sanitizedColumns }
											onChange={ ( value ) =>
												setAttributes( { columns: sanitizeColumnCount( value ) } )
											}
											min={ 1 }
											max={ MAX_POSTS_COLUMNS }
											withInputField
											required
											__next40pxDefaultSize
											__nextHasNoMarginBottom
										/>
										
										<UnitControl
											label={ __( 'Column width', 'alphalisting' ) }
											value={ sanitizedColumnWidth }
											units={ LENGTH_UNITS }
											onChange={ ( nextValue ) =>
												setAttributes( {
													'column-width': sanitizeLengthValue(
														nextValue,
														defaults['column-width'].default
													),
												} )
											}
											__next40pxDefaultSize
											__nextHasNoMarginBottom
										/>
										<UnitControl
											label={ __( 'Column gap', 'alphalisting' ) }
											value={ sanitizedColumnGap }
											units={ LENGTH_UNITS }
											onChange={ ( nextValue ) =>
												setAttributes( {
													'column-gap': sanitizeLengthValue(
														nextValue,
														defaults['column-gap'].default
													),
												} )
											}
											__next40pxDefaultSize
											__nextHasNoMarginBottom
										/>
										{ subFills }
									</>
								) }
							</DisplayOptions.Slot>
						</PanelBody>

						<Extensions.Slot>
							{ ( subFills ) => ( <> { subFills } </> ) }
						</Extensions.Slot>

						{ fills }
					</>
				) }
			</AZInspectorControls.Slot>
		</InspectorControls>
	);

	const errors = applyFilters(
		'alphalisting-validation-errors',
		validationErrors
	);

	return (
		<>
			{ inspectorControls }

			{ errors.length > 0 ? (
				<Placeholder icon={ pin } label={ __( 'AlphaListing', 'alphalisting' ) }>
					{ __( 'The AlphaListing configuration is incomplete:', 'alphalisting' ) }
					<ul>
						{ errors.map( ( error, idx ) => (
							<li key={ idx }>{ error }</li>
						) ) }
					</ul>
				</Placeholder>
			) : (
				<ServerSideRender
					block="alphalisting/block"
					attributes={ attributes }
					LoadingResponsePlaceholder={ () => <Spinner /> }
					ErrorResponsePlaceholder={ () => (
						<Placeholder
							icon={ pin }
							label={ __( 'AlphaListing', 'alphalisting' ) }
						>
							{ __( 'Error Loading the listing...', 'alphalisting' ) }
						</Placeholder>
					) }
					EmptyResponsePlaceholder={ () => (
						<Placeholder
							icon={ pin }
							label={ __( 'AlphaListing', 'alphalisting' ) }
						>
							{ __(
								'The listing has returned an empty page. This is likely an error.',
								'alphalisting'
							) }
						</Placeholder>
					) }
				/>
			) }
		</>
	);
}

export default A_Z_Listing_Edit;
