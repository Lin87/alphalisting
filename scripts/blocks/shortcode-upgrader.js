import { createBlock } from '@wordpress/blocks';
import { subscribe, select, dispatch } from '@wordpress/data';

import { parseShortcodeAttributes } from './shortcode-attributes';

const validBlocks = ( blocks ) => Array.isArray( blocks ) && blocks.length > 0;

const blockHandler = ( block ) => {
    const attributes = parseShortcodeAttributes( block.attributes.text );
    return createBlock( 'alphalisting/block', attributes );
};

const transform = ( block ) => {
    if (
        block.name === 'core/shortcode' &&
        typeof block.attributes.text === 'string' &&
        block.attributes.text.startsWith( '[alphalisting' )
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
