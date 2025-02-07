import { createSlotFill, PanelBody } from '@wordpress/components';

export const { Fill, Slot } = createSlotFill( 'AlphaListingInspectorControls' );

const AZInspectorControls = ( { children, title } ) => (
	<Fill>
		<PanelBody title={ title }>{ children }</PanelBody>
	</Fill>
);

AZInspectorControls.Slot = Slot;

export default AZInspectorControls;
