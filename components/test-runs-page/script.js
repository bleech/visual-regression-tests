// document
// 	.querySelectorAll( '.vrts-show-test-run-details' )
// 	.forEach( ( element ) => {
// 		element.addEventListener( 'click', ( e ) => {
// 			e.preventDefault();
// 			const $row = element.closest( 'tr' );
// 			const isHidden =
// 				$row.getAttribute( 'data-vrts-test-run-details' ) === 'hidden';
// 			$row.setAttribute(
// 				'data-vrts-test-run-details',
// 				isHidden ? 'visible' : 'hidden'
// 			);
// 		} );
// 	} );

/**
 * @param {HTMLTableElement} table
 */
function highlightNewTestRuns( table ) {
	if ( ! table ) {
		return;
	}
	const { localStorage } = window;
	const testRunIds = JSON.parse(
		localStorage.getItem( 'vrtsQueuedTestIds' ) || '[]'
	);
	const rows = table.querySelectorAll( 'tr[data-test-run-id]' );
	let i = 0;
	rows.forEach( ( row ) => {
		const testRunId = row.getAttribute( 'data-test-run-id' );
		if ( testRunIds.includes( testRunId ) ) {
			setTimeout( () => {
				row.classList.add( 'test-run-highlighted' );
			}, i * 200 );
			i += 1;
		}
	} );
}

/**
 * @param {HTMLTableElement} table
 */
function saveQueuedTestIds( table ) {
	if ( ! table ) {
		return;
	}
	const { localStorage } = window;

	const rows = table.querySelectorAll( 'tr[data-test-run-id]' );
	const newTestRunIds = [];
	rows.forEach( ( row ) => {
		const testRunId = row.getAttribute( 'data-test-run-id' );
		newTestRunIds.push( testRunId );
	} );
	localStorage.setItem(
		'vrtsQueuedTestIds',
		JSON.stringify( newTestRunIds )
	);
}

function navigateToTestRun( table ) {
	if ( ! table ) {
		return;
	}
	// TODO: maybe add touchend as well?
	table.addEventListener( 'click', ( event ) => {
		// console.log('click', event.target, event.currentTarget);
		const target = event.target;
		if ( target.tagName === 'TD' ) {
			const newClickEvent = new window.MouseEvent( 'click', {
				bubbles: true,
				cancelable: true,
				view: window,
				ctrlKey: event.ctrlKey,
				shiftKey: event.shiftKey,
				altKey: event.altKey,
				metaKey: event.metaKey,
				button: event.button, // Preserve which mouse button was pressed
				clientX: event.clientX, // Preserve mouse position
				clientY: event.clientY,
			} );
			target
				.closest( 'tr' )
				.querySelector( 'a' )
				.dispatchEvent( newClickEvent );
		}
	});
}

highlightNewTestRuns(
	document.querySelector( 'form .vrts-test-runs-list-table' )
);
saveQueuedTestIds(
	document.querySelector( '.vrts-test-runs-list-queue-table' )
);

navigateToTestRun(
	document.querySelector( 'form .vrts-test-runs-list-table' )
);
