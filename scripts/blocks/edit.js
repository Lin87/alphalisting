/**
 * Retrieves the translation of text.
 *
 * @see https://developer.wordpress.org/block-editor/packages/packages-i18n/
 */

// import { get } from 'lodash';

import { Component, useEffect, useMemo, useState } from '@wordpress/element';
import {
	// BaseControl,
	FormTokenField,
	PanelBody,
	Placeholder,
	RangeControl,
	SelectControl,
	Spinner,
	ToggleControl,
	TextControl,
	// __experimentalUnitControl as UnitControl,
	// ToolbarGroup,
} from '@wordpress/components';
import ServerSideRender from '@wordpress/server-side-render';
import apiFetch from '@wordpress/api-fetch';
import { addQueryArgs } from '@wordpress/url';
import { __ } from '@wordpress/i18n';
import {
	InspectorControls,
	// BlockAlignmentToolbar,
	// BlockControls,
	// __experimentalImageSizeControl as ImageSizeControl,
} from '@wordpress/block-editor';
import { useSelect } from '@wordpress/data';
import { store as coreStore } from '@wordpress/core-data';
import {
	pin,
	// list,
	// grid
} from '@wordpress/icons';
import { addFilter, applyFilters } from '@wordpress/hooks';

import {v4 as uuid} from 'uuid';

/**
 * Internal dependencies
 */
import { MAX_POSTS_COLUMNS } from './constants';

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
		terms: defaults.terms.default,
	} ),
	5
);
addFilter(
	'alphalisting_selection_changed_for__post-type',
	'alphalisting',
	( attributes ) => ( {
		...attributes,
		taxonomy: defaults.taxonomy.default,
		terms: defaults.terms.default,
	} ),
	5
);
addFilter(
	'alphalisting_selection_changed_for__taxonomy',
	'alphalisting',
	( attributes ) => ( {
		...attributes,
		terms: defaults.terms.default,
	} ),
	5
);
const displayTypes = applyFilters(
	'alphalisting_display_types',
	[
		{ value: 'posts', label: __( 'Posts', 'alphalisting' ) },
		{ value: 'terms', label: __( 'Taxonomy Terms', 'alphalisting' ) },
	]
);
const defaultAlphabet = __( 'AÁÀÄÂaáàäâ,Bb,CÇcç,Dd,EÉÈËÊeéèëê,Ff,Gg,Hh,IÍÌÏÎiíìïî,Jj,Kk,Ll,Mm,Nn,OÓÒÖÔoóòöô,Pp,Qq,Rr,Ssß,Tt,UÚÙÜÛuúùüû,Vv,Ww,Xx,Yy,Zz', 'alphalisting' );

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
		if ( attributes['display'] === 'posts' && attributes['post-type'] && postTypesTaxonomiesMap ) {
			postTaxonomies = postTypesTaxonomiesMap[ attributes['post-type'] ];
		}
		return [ { label: '', slug: '' } ].concat(
			allTaxonomies
				.filter( ( tax ) => postTaxonomies.includes( tax.slug ) )
				.map( ( tax ) => ( {
					label: tax.name,
					value: tax.slug,
				} ) )
		);
	}, [ attributes['post-type'], postTypesTaxonomiesMap, allTaxonomies ]);

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


	const inspectorControls = (
		<InspectorControls>
			<AZInspectorControls.Slot>
				{ ( fills ) => (
					<>
						{/* <PanelBody
							title={ __( 'Featured image settings' ) }
						>
							<ToggleControl
								label={ __( 'Display featured image' ) }
								checked={ displayFeaturedImage }
								onChange={ ( value ) =>
									setAttributes( {
										displayFeaturedImage: value,
									} )
								}
							/>
							{ displayFeaturedImage && (
								<>
									<ImageSizeControl
										onChange={ ( value ) => {
											const newAttrs = {};
											if (
												value.hasOwnProperty(
													'width'
												)
											) {
												newAttrs.featuredImageSizeWidth =
													value.width;
											}
											if (
												value.hasOwnProperty(
													'height'
												)
											) {
												newAttrs.featuredImageSizeHeight =
													value.height;
											}
											setAttributes( newAttrs );
										} }
										slug={ featuredImageSizeSlug }
										width={ featuredImageSizeWidth }
										height={ featuredImageSizeHeight }
										imageWidth={ defaultImageWidth }
										imageHeight={ defaultImageHeight }
										imageSizeOptions={
											imageSizeOptions
										}
										onChangeImage={ ( value ) =>
											setAttributes( {
												featuredImageSizeSlug: value,
												featuredImageSizeWidth: undefined,
												featuredImageSizeHeight: undefined,
											} )
										}
									/>
									<BaseControl>
										<BaseControl.VisualLabel>
											{ __( 'Image alignment' ) }
										</BaseControl.VisualLabel>
										<BlockAlignmentToolbar
											value={ featuredImageAlign }
											onChange={ ( value ) =>
												setAttributes( {
													featuredImageAlign: value,
												} )
											}
											controls={ [
												'left',
												'center',
												'right',
											] }
											isCollapsed={ false }
										/>
									</BaseControl>
								</>
							) }
						</PanelBody> */}

						<PanelBody title={ __( 'Item selection', 'alphalisting' ) }>
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
											__nextHasNoMarginBottom={true}
										/>

										{ 'posts' === attributes.display && (
											<SelectControl
												label={ __( 'Post Type', 'alphalisting' ) }
												value={ attributes['post-type'] ?? defaults['post-type'].default }
												options={ postTypesSelectOptions }
												onChange={ ( value ) =>
													setAttributes(
														applyFilters(
															'alphalisting_selection_changed_for__post-type',
															{ 'post-type': value }
														)
													)
												}
												__nextHasNoMarginBottom={true}
											/>
										) }

										{ (
											'posts' === attributes.display &&
											postTypesMap && postTypesMap[ attributes.postType ]?.hierarchical
										) && (
											<PostParent
												pageId={ attributes.parentId ?? -1 }
												postTypeSlug={ attributes.postType ?? 'page' }
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
												__nextHasNoMarginBottom={true}
											/>
										) }

										{ 'posts' === attributes.display &&
											!! attributes.taxonomy && (
												<FormTokenField
													label={ __( 'Taxonomy terms', 'alphalisting' ) }
													value={ attributes.terms ?? [] }
													onChange={ ( value ) =>
														setAttributes( { terms: value } )
													}
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
											__nextHasNoMarginBottom={true}
										/>
										<TextControl
											label={ __( 'CSS class names', 'alphalisting' ) }
											value={ attributes.className ?? '' }
											onChange={ ( value ) =>
												setAttributes( { className: value } )
											}
											__nextHasNoMarginBottom={true}
										/>
										<TextControl
											label={ __( 'Alphabet', 'alphalisting' ) }
											value={ attributes.alphabet ?? defaults['alphabet'].default }
											onChange={ ( value ) =>
												setAttributes( { alphabet: value } )
											}
											__nextHasNoMarginBottom={true}
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
											__nextHasNoMarginBottom={true}
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
											__nextHasNoMarginBottom={true}
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
													__nextHasNoMarginBottom={true}
												/>
											) }

										<ToggleControl
											label={ __( 'Display symbols entry first', 'alphalisting' ) }
											checked={ !!attributes['symbols-first'] }
											onChange={ ( value ) =>
												setAttributes( { 'symbols-first': value } )
											}
											__nextHasNoMarginBottom={true}
										/>

										<RangeControl
											label={ __( 'Columns', 'alphalisting' ) }
											value={ attributes.columns ?? MAX_POSTS_COLUMNS }
											onChange={ ( value ) =>
												setAttributes( { columns: value } )
											}
											min={ 1 }
											max={ MAX_POSTS_COLUMNS }
											withInputField
											required
											__nextHasNoMarginBottom={true}
										/>
										{/* Waiting for UnitControl to stabilise in Gutenberg */}
										{/* <UnitControl
											label={ __( 'Column width', 'alphalisting' ) }
											value={ attributes['column-width'] }
											onChange={ ( value ) =>
												setAttributes( { 'column-width': value } )
											}
											required
										/>
										{/* Waiting for UnitControl to stabilise in Gutenberg */}
										{/* <UnitControl
											label={ __( 'Column gap', 'alphalisting' ) }
											value={ attributes['column-gap'] }
											onChange={ ( value ) =>
												setAttributes( { 'column-gap': value } )
											}
											required
										/> */}
										<TextControl
											label={ __( 'Column width', 'alphalisting' ) }
											value={ attributes['column-width'] ?? defaults['column-width'].default }
											onChange={ ( value ) =>
												setAttributes( { 'column-width': value } )
											}
											required
											__nextHasNoMarginBottom={true}
										/>
										<TextControl
											label={ __( 'Column gap', 'alphalisting' ) }
											value={ attributes['column-gap'] ?? defaults['column-gap'].default }
											onChange={ ( value ) =>
												setAttributes( { 'column-gap': value } )
											}
											required
											__nextHasNoMarginBottom={true}
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
