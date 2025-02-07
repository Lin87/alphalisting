( function() {
	const offset = a_z_listing_scroll_fix.offset || -120;
	function fixAlphaListingScroll() {
		document.addEventListener( 'click', function( e ) {
			if ( e.target.href.startsWith( '#letter-' ) ) {
				e.preventDefault();
				document.querySelector( e.target.href ).scrollIntoView();
				window.scrollBy( 0, offset );
			}
		} );
	}
	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', fixAlphaListingScroll );
	} else {
		fixAlphaListingScroll();
	}
} )();
