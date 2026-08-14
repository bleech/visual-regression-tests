import {
	fetchPage,
	warmAlert,
	startNavigation,
	isCurrentNavigation,
} from '../../assets/scripts/page-cache';

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
		this.handleWarm = this.handleWarm.bind( this );
		this.handleWarmCancel = this.handleWarmCancel.bind( this );
	}

	bindEvents() {
		this.$buttons?.forEach( ( item ) => {
			item.addEventListener( 'click', this.handleClick );
			item.addEventListener( 'pointerenter', this.handleWarm );
			item.addEventListener( 'pointerleave', this.handleWarmCancel );
			item.addEventListener( 'focus', this.handleWarm );
			item.addEventListener( 'blur', this.handleWarmCancel );
		} );

		document.addEventListener( 'keydown', this.handleKeyDown );
	}

	connectedCallback() {
		const href = this.querySelector(
			'[data-vrts-pagination="next"]'
		)?.getAttribute( 'href' );

		if ( href ) {
			this.warmIdleId = window.requestIdleCallback
				? window.requestIdleCallback( () => warmAlert( href ) )
				: setTimeout( () => warmAlert( href ), 1000 );
		}
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

		const token = startNavigation();

		const timeout = setTimeout( () => {
			$content.setAttribute( 'data-vrts-loading', 'true' );
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

		fetchPage( href )
			.then( ( data ) => {
				clearTimeout( timeout );

				if ( ! isCurrentNavigation( token ) ) {
					return;
				}

				const parser = new window.DOMParser();
				const $html = parser.parseFromString( data, 'text/html' );

				const $newContent =
					$html.querySelector( 'vrts-comparisons' ) ||
					$html.querySelector( 'vrts-test-run-success' );
				const $newPagination = $html.querySelector(
					'vrts-test-run-pagination'
				);

				if ( ! $newContent ) {
					throw new Error( 'vrts: unexpected page response' );
				}

				window.history.replaceState( {}, '', href );

				this.scrollTo( $content.offsetTop - 62 );

				$content.replaceWith( $newContent );

				if ( $newPagination ) {
					this.replaceWith( $newPagination );
				}
			} )
			.catch( () => {
				clearTimeout( timeout );

				if ( isCurrentNavigation( token ) ) {
					window.location.assign( href );
				}
			} );
	}

	handleWarm( e ) {
		const $el = e.currentTarget;
		const href = $el.getAttribute( 'href' );

		this.warmTimeouts = this.warmTimeouts || new Map();
		this.warmTimeouts.set(
			$el,
			setTimeout( () => warmAlert( href ), 100 )
		);
	}

	handleWarmCancel( e ) {
		clearTimeout( this.warmTimeouts?.get( e.currentTarget ) );
		this.warmTimeouts?.delete( e.currentTarget );
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
			item.removeEventListener( 'pointerenter', this.handleWarm );
			item.removeEventListener( 'pointerleave', this.handleWarmCancel );
			item.removeEventListener( 'focus', this.handleWarm );
			item.removeEventListener( 'blur', this.handleWarmCancel );
		} );

		document.removeEventListener( 'keydown', this.handleKeyDown );

		this.warmTimeouts?.forEach( ( timeout ) => clearTimeout( timeout ) );

		if ( this.warmIdleId ) {
			if ( window.cancelIdleCallback ) {
				window.cancelIdleCallback( this.warmIdleId );
			} else {
				clearTimeout( this.warmIdleId );
			}
		}
	}
}

window.customElements.define(
	'vrts-test-run-pagination',
	VrtsTestRunPagination
);
