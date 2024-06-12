document
	.querySelectorAll( '.vrts-show-test-run-details' )
	.forEach( ( element ) => {
		element.addEventListener( 'click', ( e ) => {
			e.preventDefault();
			const $row = element.closest( 'tr' );
			const isHidden = $row.getAttribute( 'data-vrts-test-run-details' ) === 'hidden';
			$row.setAttribute(
				'data-vrts-test-run-details',
				isHidden ? 'visible' : 'hidden'
			);
			console.log( 'Show test run details' );
		} );
	} );
