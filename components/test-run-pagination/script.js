class VrtsTestRunPagination extends window.HTMLElement {
	constructor() {
		super();
		this.resolveElements();
		this.bindFunctions();
		this.bindEvents();
	}

	resolveElements() {
		this.$alerts = document.querySelectorAll( '[data-vrts-alert]' );
		this.$buttons = this.querySelectorAll( '.button' );
	}

	bindFunctions() {
		this.handleClick = this.handleClick.bind( this );
	}

	bindEvents() {
		this.$buttons?.forEach( ( item ) => {
			item.addEventListener( 'click', this.handleClick );
		} );
	}

	handleClick( e ) {
		e.preventDefault();
		const $el = e.currentTarget;
		const href = $el.getAttribute( 'href' );
		const nextAlertId = $el.getAttribute( 'data-alert-id' );
		const $nextAlert = document.getElementById(
			`vrts-alert-${ nextAlertId }`
		);
		const $comparisons = document.querySelector( 'vrts-comparisons' );
		const $sidebar = document.querySelector(
			'.vrts-test-run-page__sidebar'
		);

		this.$alerts.forEach( ( item ) => {
			item.setAttribute( 'data-current', 'false' );
		} );

		$nextAlert.setAttribute( 'data-current', 'true' );

		$comparisons.setAttribute( 'data-vrts-loading', 'true' );

		$sidebar.scrollTo( {
			top: $nextAlert.offsetTop - 100,
			behavior: 'smooth',
		} );

		fetch( href )
			.then( ( response ) => {
				return response.text();
			} )
			.then( ( data ) => {
				const parser = new window.DOMParser();
				const $html = parser.parseFromString( data, 'text/html' );

				const $newComparisons = $html.querySelector( 'vrts-comparisons' );
				const $newPagination = $html.querySelector(
					'vrts-test-run-pagination'
				);

				window.history.replaceState( {}, '', href );

				window.scrollTo( {
					top: 0,
					behavior: 'smooth',
				} );

				if ( $newComparisons ) {
					$comparisons.replaceWith( $newComparisons );
				}

				if ( $newPagination ) {
					this.replaceWith( $newPagination );
				}
			} );
	}

	disconnectedCallback() {
		this.$buttons?.forEach( ( item ) => {
			item.removeEventListener( 'click', this.handleClick );
		} );
	}
}

window.customElements.define(
	'vrts-test-run-pagination',
	VrtsTestRunPagination
);
