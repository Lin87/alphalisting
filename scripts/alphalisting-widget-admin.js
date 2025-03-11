jQuery( function( $ ) {
	const wireup_alphalisting = function() {
		jQuery( '.alphalisting-widget' ).each( function( idx, el ) {
			el = $( el );
			
			const target_post = el.find( '.alphalisting-target-post' );
			const target_post_title = el.find(
				'.alphalisting-target-post-title'
			);
			const display_type = el.find( '.alphalisting-display-type' );
			const listing_post_type = el.find( '.alphalisting-post-type' );
			const listing_post_type_wrapper = el.find(
				'.alphalisting-post-type-wrapper'
			);
			const listing_parent_post = el.find( '.alphalisting-parent-post' );
			const listing_parent_post_title = el.find(
				'.alphalisting-parent-post-title'
			);
			const listing_parent_post_wrapper = el.find(
				'.alphalisting-parent-post-wrapper'
			);
			const listing_taxonomy = el.find( '.alphalisting-taxonomy' );
			const listing_taxonomy_wrapper = el.find(
				'.alphalisting-taxonomy-wrapper'
			);
			const listing_parent_term = el.find( '.alphalisting-parent-term' );
			const listing_parent_term_wrapper = el.find(
				'.alphalisting-parent-term-wrapper'
			);
			const listing_hide_empty_terms = el.find(
				'.alphalisting-hide-empty-terms'
			);
			const listing_hide_empty_terms_wrapper = el.find(
				'.alphalisting-hide-empty-terms-wrapper'
			);
			const listing_exclude_terms = el.find(
				'.alphalisting-exclude-terms'
			);
			const listing_exclude_terms_wrapper = el.find(
				'.alphalisting-exclude-terms-wrapper'
			);
			const listing_wpnonce = el.find(
				'#_posts_by_title_wpnonce'
			);

			const switch_taxonomy_or_posts = function() {
				if ( 'terms' === display_type.val() ) {
					listing_post_type.attr( 'disabled', 'disabled' );
					listing_post_type_wrapper.hide();
					listing_parent_post_title.attr( 'disabled', 'disabled' );
					listing_parent_post_wrapper.hide();
					listing_taxonomy.removeAttr( 'disabled' );
					listing_taxonomy_wrapper.show();
					listing_parent_term.removeAttr( 'disabled' );
					listing_parent_term_wrapper.show();
					listing_hide_empty_terms.removeAttr( 'disabled' );
					listing_hide_empty_terms_wrapper.show();
					listing_exclude_terms.removeAttr( 'disabled' );
					listing_exclude_terms_wrapper.show();
				} else {
					listing_post_type.removeAttr( 'disabled' );
					listing_post_type_wrapper.show();
					listing_parent_post_title.removeAttr( 'disabled' );
					listing_parent_post_wrapper.show();
					listing_taxonomy.attr( 'disabled', 'disabled' );
					listing_taxonomy_wrapper.hide();
					listing_parent_term.attr( 'disabled', 'disabled' );
					listing_parent_term_wrapper.hide();
					listing_hide_empty_terms.attr( 'disabled', 'disabled' );
					listing_hide_empty_terms_wrapper.hide();
					listing_exclude_terms.attr( 'disabled', 'disabled' );
					listing_exclude_terms_wrapper.hide();
				}
			};

			switch_taxonomy_or_posts();
			display_type.change( switch_taxonomy_or_posts );

			$( target_post_title ).autocomplete( {
				source( post_title, response ) {
					
					jQuery.ajax( {
						url: alphalisting_widget_admin.ajax_url,
						type: 'POST',
						dataType: 'json',
						data: {
							action: 'get_alphalisting_autocomplete_post_titles',
							_posts_by_title_wpnonce: listing_wpnonce.val(),
							post_type: '',
							post_title,
						},
						success( data ) {
							response( data );
						},
						error() {
							response();
						},
					} );
				},
				select( event, ui ) {
					event.preventDefault();
					target_post_title
						.find( '~ input[type="hidden"]' )
						.val( ui.item.value );
					target_post_title.val( ui.item.label );
				},
			} );

			$( listing_parent_post_title ).autocomplete( {
				source( post_title, response ) {
					jQuery.ajax( {
						url:
							alphalisting_widget_admin.ajax_url,
						type: 'POST',
						dataType: 'json',
						data: {
							action: 'get_alphalisting_autocomplete_post_titles',
							_posts_by_title_wpnonce: listing_wpnonce.val(),
							post_type: listing_post_type.val(),
							post_title,
						},
						success( data ) {
							response( data );
						},
						error() {
							response();
						},
					} );
				},
				select( event, ui ) {
					event.preventDefault();
					listing_parent_post_title
						.find( '~ input[type="hidden"]' )
						.val( ui.item.value );
					listing_parent_post_title.val( ui.item.label );
				},
			} );
		} );
	};

	wireup_alphalisting();
	$( document ).on( 'widget-updated widget-added', function() {
		wireup_alphalisting();
	} );
} );
