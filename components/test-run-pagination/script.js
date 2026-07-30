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
		let $nextAlert = document.getElementById(
			`vrts-alert-${ nextAlertId }`
		);

		if ( ! $nextAlert ) {
			return;
		}

		const href = $el.getAttribute( 'href' );
		const $content =
			document.querySelector( 'vrts-comparisons' ) ||
			document.querySelector( 'vrts-test-run-success' );
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
			$content.setAttribute( 'data-vrts-loading', 'true' );
			const loadingStartTime = window.Date.now();
			interval = setInterval( () => {
				loadingElapsedTime = window.Date.now() - loadingStartTime;
			}, 50 );
		}, 200 );

		let offsetTop = 0;

		while ( $nextAlert && $nextAlert !== $sidebar ) {
			offsetTop += $nextAlert.offsetTop;
			$nextAlert = $nextAlert.offsetParent;
		}

		$sidebar.scrollTo( {
			top: offsetTop - 82,
			behavior: 'smooth',
		} );

		fetch( href )
			.then( ( response ) => {
				return response.text();
			} )
			.then( ( data ) => {
				const parser = new window.DOMParser();
				const $html = parser.parseFromString( data, 'text/html' );

				const $newContent =
					$html.querySelector( 'vrts-comparisons' ) ||
					$html.querySelector( 'vrts-test-run-success' );
				const $newPagination = $html.querySelector(
					'vrts-test-run-pagination'
				);

				window.history.replaceState( {}, '', href );

				this.scrollTo( $content.offsetTop - 62 );

				const loadingTimeoutTime =
					loadingElapsedTime > 0
						? Math.abs( loadingElapsedTime - 400 )
						: 0;

				setTimeout( () => {
					if ( $newContent ) {
						$content.replaceWith( $newContent );
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
		if ( e.key !== 'ArrowUp' && e.key !== 'ArrowDown' ) {
			return;
		}

		// Leave arrow keys alone while a form field has focus, e.g. the
		// comparison slider.
		if (
			e.target.matches?.( 'input, textarea, select' ) ||
			e.target.isContentEditable
		) {
			return;
		}

		e.preventDefault();

		const direction = e.key === 'ArrowUp' ? 'prev' : 'next';

		// Scroll to the next change of the current comparison first; switch
		// the alert only when there are no more changes in that direction.
		const $jump = document.querySelector(
			`[data-vrts-comparisons-jump="${ direction }"]`
		);

		if ( $jump && ! $jump.disabled ) {
			$jump.click();
			return;
		}

		this.querySelector( `[data-vrts-pagination="${ direction }"]` ).click();
	}

	scrollTo( offset ) {
		const $el =
			document.fullscreenElement ||
			document.webkitFullscreenElement ||
			document.msFullscreenElement ||
			window;

		$el.scrollTo( {
			top: offset,
			behavior: 'smooth',
		} );
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
