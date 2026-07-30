class VrtsTestRunsPage extends window.HTMLElement {
	constructor() {
		super();
		this.resolveElements();
		this.onRefreshed = this.onRefreshed.bind( this );
	}

	resolveElements() {
		this.$runsListTable = this.querySelector(
			'form .vrts-test-runs-list-table'
		);
	}

	connectedCallback() {
		this.highlightNewTestRuns();
		this.addEventListener( 'vrts-refreshed', this.onRefreshed );
	}

	disconnectedCallback() {
		this.removeEventListener( 'vrts-refreshed', this.onRefreshed );
	}

	onRefreshed() {
		this.resolveElements();
		this.highlightNewTestRuns();
	}

	highlightNewTestRuns() {
		const testRunIds = new Set(
			JSON.parse(
				window.localStorage.getItem( 'vrtsNewTestRuns' ) || '[]'
			)
		);

		const rows = this.$runsListTable.querySelectorAll(
			'tr[data-test-run-id]'
		);

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

		window.localStorage.setItem(
			'vrtsNewTestRuns',
			JSON.stringify( [ ...testRunIds ] )
		);
	}
}

window.customElements.define( 'vrts-test-runs-page', VrtsTestRunsPage );
