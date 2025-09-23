( function() {
	const settings = window.alphalisting_scroll_fix || {};
	const offset = typeof settings.offset === 'number' ? settings.offset : -120;

	function fixAlphaListingScroll() {
		document.addEventListener( 'click', function( event ) {
			let target = event.target;

			if ( target && target.nodeType !== 1 ) {
				target = target.parentElement;
			}

			while ( target && target.nodeType === 1 && target.tagName !== 'A' ) {
				target = target.parentElement;
			}

			if ( ! target || target.tagName !== 'A' ) {
				return;
			}

			const hash = target.hash || '';
			if ( ! hash.startsWith( '#letter-' ) ) {
				return;
			}

			event.preventDefault();
			const targetElement = document.querySelector( hash );
			if ( targetElement ) {
				targetElement.scrollIntoView();
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
