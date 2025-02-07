import { createSlotFill } from '@wordpress/components';

export const { Fill, Slot } = createSlotFill( 'AlphaListingDisplayOptions' );

const DisplayOptions = ( { children } ) => <Fill>{ children }</Fill>;

DisplayOptions.Slot = Slot;

export default DisplayOptions;
