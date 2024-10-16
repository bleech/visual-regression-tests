/**
 * @param {HTMLTableElement} table
 */
function highlightNewTestRuns( table ) {
	if ( ! table ) {
		return;
	}
	const { localStorage } = window;
	const testRunIds = new Set(
		JSON.parse( localStorage.getItem( 'vrtsNewTestRuns' ) || '[]' )
	);
	const rows = table.querySelectorAll( 'tr[data-test-run-id]' );

	let staggerTimeout = 0;

	rows.forEach( ( row ) => {
		const testRunId = row.getAttribute( 'data-test-run-id' );

		if ( row.getAttribute( 'data-test-run-new' ) === 'true' ) {
			if ( ! testRunIds.has( testRunId ) ) {
				testRunIds.add( testRunId );
				setTimeout( () => {
					row.classList.add( 'test-run-highlighted' );
				}, staggerTimeout );
				staggerTimeout += 200;
			}
		} else if ( testRunIds.has( testRunId ) ) {
			testRunIds.delete( testRunId );
		}
	} );

	localStorage.setItem(
		'vrtsNewTestRuns',
		JSON.stringify( [ ...testRunIds ] )
	);
}

highlightNewTestRuns(
	document.querySelector( 'form .vrts-test-runs-list-table' )
);
