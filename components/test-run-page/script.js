document.addEventListener( 'DOMContentLoaded', function () {
	const urlParams = new URLSearchParams( window.location.search );
	const currentAlertId = urlParams.get( 'alert_id' );

	if ( currentAlertId ) {
		const $alert = document.getElementById(
			`vrts-alert-${ currentAlertId }`
		);

		setTimeout( () => {
			$alert.setAttribute( 'data-state', 'read' );
		}, 1000 );
	}
} );
