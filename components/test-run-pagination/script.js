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
		this.handleKeyDown = this.handleKeyDown.bind( this );
	}

	bindEvents() {
		this.$buttons?.forEach( ( item ) => {
			item.addEventListener( 'click', this.handleClick );
		} );

		document.addEventListener( 'keydown', this.handleKeyDown );
	}

	handleClick( e ) {
		e.preventDefault();
		const $el = e.currentTarget;
		const nextAlertId = $el.getAttribute( 'data-vrts-alert-id' );
		const $nextAlert = document.getElementById(
			`vrts-alert-${ nextAlertId }`
		);

		if ( ! $nextAlert ) {
			return;
		}

		const href = $el.getAttribute( 'href' );
		const $comparisons = document.querySelector( 'vrts-comparisons' );
		const $sidebar = document.querySelector(
			'.vrts-test-run-page__sidebar'
		);

		this.$alerts.forEach( ( item ) => {
			item.setAttribute( 'data-vrts-current', 'false' );
		} );

		$nextAlert.setAttribute( 'data-vrts-current', 'true' );

		let loadingElapsedTime = 0;
		let interval = null;

		const timeout = setTimeout( () => {
			$comparisons.setAttribute( 'data-vrts-loading', 'true' );
			const loadingStartTime = window.Date.now();
			interval = setInterval( () => {
				loadingElapsedTime = window.Date.now() - loadingStartTime;
			}, 50 );
		}, 200 );

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

				const $newComparisons =
					$html.querySelector( 'vrts-comparisons' );
				const $newPagination = $html.querySelector(
					'vrts-test-run-pagination'
				);

				window.history.replaceState( {}, '', href );

				window.scrollTo( {
					top: $comparisons.offsetTop - 62,
					behavior: 'smooth',
				} );

				const loadingTimeoutTime =
					loadingElapsedTime > 0
						? Math.abs( loadingElapsedTime - 400 )
						: 0;

				setTimeout( () => {
					setTimeout( () => {
						$nextAlert.setAttribute( 'data-vrts-state', 'read' );
					}, 400 );

					if ( $newComparisons ) {
						$comparisons.replaceWith( $newComparisons );
					}

					if ( $newPagination ) {
						this.replaceWith( $newPagination );
					}
				}, loadingTimeoutTime );

				clearTimeout( timeout );
				clearInterval( interval );
			} );
	}

	handleKeyDown( e ) {
		if ( e.key === 'ArrowUp' ) {
			e.preventDefault();
			this.querySelector( '[data-vrts-pagination="prev"]' ).click();
		}

		if ( e.key === 'ArrowDown' ) {
			e.preventDefault();
			this.querySelector( '[data-vrts-pagination="next"]' ).click();
		}
	}

	disconnectedCallback() {
		this.$buttons?.forEach( ( item ) => {
			item.removeEventListener( 'click', this.handleClick );
		} );

		document.removeEventListener( 'keydown', this.handleKeyDown );
	}
}

window.customElements.define(
	'vrts-test-run-pagination',
	VrtsTestRunPagination
);
