import { createBlock } from '@wordpress/blocks';
import { subscribe, select, dispatch } from '@wordpress/data';

import { parseShortcodeAttributes } from './shortcode-attributes';

const validBlocks = ( blocks ) => Array.isArray( blocks ) && blocks.length > 0;

const isAlphaListingShortcode = ( shortcodeText ) => {
    return typeof shortcodeText === 'string' && /^\s*\[alphalisting(?:\s|\]|$)/.test( shortcodeText );
};

const blockHandler = ( block ) => {
    const attributes = parseShortcodeAttributes( block.attributes.text.trim() );
    return createBlock( 'alphalisting/block', attributes );
};

const transform = ( block ) => {
    if (
        block.name === 'core/shortcode' &&
        isAlphaListingShortcode( block.attributes.text )
    ) {
        dispatch( 'core/block-editor' ).replaceBlocks( [ block.clientId ], [ blockHandler( block ) ] );
        return;
    }

    if ( validBlocks( block.innerBlocks ) ) {
        convertBlocks( block.innerBlocks );
    }
};

const convertBlocks = ( blocks ) => {
    for ( const block of blocks ) {
        transform( block );
    }
};

export default () => {
    const unsubscribe = subscribe( () => {
        const coreEditor = select( 'core/block-editor' );
        const blocks = coreEditor.getBlocks();

        if ( validBlocks( blocks ) ) {
            unsubscribe();
            convertBlocks( blocks );
        }
    } );
};
