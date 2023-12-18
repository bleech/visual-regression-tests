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
	if ( ! window.customElements.get( 'vrts-relative-time' ) ) {
		window.customElements.define(
			'vrts-relative-time',
			RelativeTimeElement
		);
	}
} );

class RelativeTimeElement extends window.HTMLElement {
	// constructor (element) {
	// 	this.element = element
	// 	// get date from utc timestamp

	// 	// this.time = new Date( element.getAttribute( 'time' ) );
	// 	// this.update();
	// }

	// observe attribute time
	static get observedAttributes() {
		return [ 'time' ];
	}

	// update time when attribute time changes
	attributeChangedCallback( name, oldValue, newValue ) {
		if ( name === 'time' ) {
			this.time = new Date( newValue );
			this.update();
		}
	}

	// connectedCallback() {
	// 	this.time = new Date( this.getAttribute( 'time' ) );
	// 	this.update();
	// }

	update() {
		this.innerText = `${ extractDate( this.time ) } at ${ extractTime(
			this.time
		) }`;
	}
}

function extractDate( inputDate ) {
	const { __ } = wp.i18n;
	const today = new Date();
	// Set the time to midnight for an accurate date comparison
	today.setHours( 0, 0, 0, 0 );

	// Create a Date object for the input date
	const comparisonDate = new Date( inputDate );
	comparisonDate.setHours( 0, 0, 0, 0 );

	// Calculate the difference in days
	const difference = ( comparisonDate - today ) / ( 1000 * 3600 * 24 );

	// Determine if the date is today, tomorrow, or yesterday
	if ( difference === 0 ) {
		return __( 'Today', 'visual-regression-testing' );
	} else if ( difference === 1 ) {
		return __( 'Tomorrow', 'visual-regression-testing' );
	} else if ( difference === -1 ) {
		return __( 'Yesterday', 'visual-regression-testing' );
	}
	return dateFormat( inputDate, 'Y/m/d' );
}

function extractTime( inputDate ) {
	return dateFormat( inputDate, 'g:i a' );
}

// format date like in php date_format
function dateFormat( date, format ) {
	const pad = ( number ) => ( number < 10 ? `0${ number }` : number );
	const d = pad( date.getDate() );
	const m = pad( date.getMonth() + 1 );
	const y = date.getFullYear();
	const Y = date.getFullYear();
	const H = date.getHours();
	const i = pad( date.getMinutes() );
	const s = date.getSeconds();
	const g = date.getHours() % 12 || 12;
	const a = date.getHours() >= 12 ? 'pm' : 'am';

	return format
		.replace( 'd', d )
		.replace( 'm', m )
		.replace( 'y', y )
		.replace( 'Y', Y )
		.replace( 'H', H )
		.replace( 'i', i )
		.replace( 's', s )
		.replace( 'g', g )
		.replace( 'a', a );
}
