/* global jQuery, ajaxurl */
jQuery( document ).ready( function ( $ ) {
	$( document ).on( 'click', '.vrts-notice .notice-dismiss', ( event ) => {
		if ( ajaxurl ) {
			const nonce =
				event.target.parentElement.querySelector( '#_wpnonce' ).value;
			const view = event.target.parentElement.dataset.view;

			const data = {
				action: 'vrts_admin_notice_dismiss',
				url: ajaxurl,
				security: nonce,
				view,
			};

			$.ajax( {
				type: 'POST',
				url: ajaxurl,
				data,
			} );
		}
	} );
} );
