import { createSlotFill } from '@wordpress/components';

export const { Fill, Slot } = createSlotFill( 'AlphaListingItemSelection' );

const ItemSelection = ( { children } ) => <Fill>{ children }</Fill>;

ItemSelection.Slot = Slot;

export default ItemSelection;
