import { attrs } from '@wordpress/shortcode';

const BOOLEAN_ATTRIBUTES = new Set( [
    'get-all-children',
    'group-numbers',
    'symbols-first',
    'back-to-top',
    'hide-empty',
    'hide-empty-terms',
] );

const NUMBER_ATTRIBUTES = new Set( [ 'columns', 'grouping', 'parent-post' ] );

const ARRAY_NUMBER_ATTRIBUTES = new Set( [ 'exclude-posts' ] );
const ARRAY_STRING_ATTRIBUTES = new Set( [ 'terms', 'exclude-terms' ] );
const SHORTCODE_DEFAULTS = {
    'post-type': 'page',
};

const isTruthy = ( value ) => {
    if ( typeof value === 'boolean' ) {
        return value;
    }

    if ( typeof value === 'number' ) {
        return value !== 0;
    }

    if ( typeof value !== 'string' ) {
        return false;
    }

    const normalized = value.trim().toLowerCase();

    return [ '1', 'true', 'yes', 'y', 'on' ].includes( normalized );
};

const toArray = ( value ) => {
    if ( Array.isArray( value ) ) {
        return value;
    }

    if ( typeof value !== 'string' ) {
        return [];
    }

    return value
        .split( ',' )
        .map( ( item ) => item.trim() )
        .filter( Boolean );
};

const normalizeAttributeValue = ( key, value ) => {
    if ( key === 'post-type' ) {
        const firstPostType = toArray( value )[ 0 ] ?? '';

        return firstPostType || SHORTCODE_DEFAULTS['post-type'];
    }

    if ( BOOLEAN_ATTRIBUTES.has( key ) ) {
        return isTruthy( value );
    }

    if ( NUMBER_ATTRIBUTES.has( key ) ) {
        if ( key === 'grouping' && typeof value === 'string' && value.trim().toLowerCase() === 'numbers' ) {
            return undefined;
        }

        const parsed = parseInt( value, 10 );

        return Number.isNaN( parsed ) ? undefined : parsed;
    }

    if ( ARRAY_NUMBER_ATTRIBUTES.has( key ) ) {
        return toArray( value )
            .map( ( item ) => parseInt( item, 10 ) )
            .filter( ( item ) => ! Number.isNaN( item ) );
    }

    if ( ARRAY_STRING_ATTRIBUTES.has( key ) ) {
        return toArray( value );
    }

    return value;
};

export const parseShortcodeAttributes = ( shortcodeText ) => {
    const parsedAttributes = attrs( shortcodeText );
    const namedAttributes = parsedAttributes?.named || parsedAttributes || {};

    const attributes = Object.entries( namedAttributes ).reduce( ( parsed, [ key, value ] ) => {
        const normalizedValue = normalizeAttributeValue( key, value );

        if ( typeof normalizedValue !== 'undefined' ) {
            parsed[ key ] = normalizedValue;
        }

        return parsed;
    }, {} );

    if ( typeof namedAttributes.grouping === 'string' && namedAttributes.grouping.trim().toLowerCase() === 'numbers' ) {
        attributes[ 'group-numbers' ] = true;
    }

    return attributes;
};
