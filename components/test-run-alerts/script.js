class VrtsTestRunAlerts extends window.HTMLElement {
	constructor() {
		super();
		this.resolveElements();
		this.bindFunctions();
		this.bindEvents();
	}

	resolveElements() {
		this.$alerts = this.querySelectorAll( '.vrts-test-run-alerts__card' );
		this.$actionButtons = this.querySelectorAll(
			'[data-vrts-test-run-action]'
		);
	}

	bindFunctions() {
		this.handleAlertClick = this.handleAlertClick.bind( this );
		this.handleActionClick = this.handleActionClick.bind( this );
	}

	bindEvents() {
		this.$alerts?.forEach( ( item ) => {
			item.addEventListener( 'click', this.handleAlertClick );
		} );
		this.$actionButtons?.forEach( ( item ) => {
			item.addEventListener( 'click', this.handleActionClick );
		} );
	}

	connectedCallback() {
		const currentAlertId = this.getAttribute( 'data-vrts-current-alert' );

		if ( false !== currentAlertId ) {
			const $alert = document.getElementById(
				`vrts-alert-${ currentAlertId }`
			);

			setTimeout( () => {
				$alert.setAttribute( 'data-state', 'read' );
			}, 1000 );
		}
	}

	handleAlertClick( e ) {
		e.preventDefault();
		const $el = e.currentTarget;
		const href = $el.getAttribute( 'href' );
		const $comparisons = document.querySelector( 'vrts-comparisons' );
		const $pagination = document.querySelector(
			'vrts-test-run-pagination'
		);

		this.$alerts.forEach( ( item ) => {
			item.setAttribute( 'data-current', 'false' );
		} );

		$el.setAttribute( 'data-current', 'true' );
		$comparisons.setAttribute( 'data-vrts-loading', 'true' );

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
					top: $comparisons.offsetTop - 62,
					behavior: 'smooth',
				} );

				if ( $newComparisons ) {
					$comparisons.replaceWith( $newComparisons );
				}

				if ( $newPagination ) {
					$pagination.replaceWith( $newPagination );
				}
			} );
	}

	handleActionClick( e ) {
		const $el = e.currentTarget;
		const isLoading = $el.getAttribute( 'data-vrts-loading' ) === 'true';
		const state = $el.getAttribute( 'data-vrts-action-state' );
		const isPrimary = state === 'primary';

		if ( isLoading ) {
			return;
		}

		$el.setAttribute( 'data-vrts-loading', 'true' );

		const action = $el.getAttribute( 'data-vrts-test-run-action' );
		const id = $el.getAttribute( 'data-vrts-test-run-id' );

		this.handleAction( action, $el, id, isPrimary );
	}

	handleAction( action, $el, id, shouldSetAction ) {
		const restEndpoint = `${ window.vrts_admin_vars.rest_url }/test-runs/${ id }/${ action }`;
		const method = shouldSetAction ? 'POST' : 'DELETE';

		fetch( restEndpoint, {
			method,
			headers: {
				'X-WP-Nonce': window.vrts_admin_vars.rest_nonce,
			},
		} )
			.then( ( response ) => {
				return response.json();
			} )
			.then( ( data ) => {
				// console.log( data );

				$el.setAttribute( 'data-vrts-loading', 'false' );
				$el.setAttribute(
					'data-vrts-action-state',
					shouldSetAction ? 'secondary' : 'primary'
				);

				const $alert = document.querySelectorAll(
					'.vrts-test-run-alerts__card'
				);

				$alert.forEach( ( item ) => {
					item.setAttribute(
						'data-state',
						shouldSetAction ? 'read' : 'unread'
					);
				} );
			} );
	}

	disconnectedCallback() {
		this.$alerts?.forEach( ( item ) => {
			item.removeEventListener( 'click', this.handleAlertClick );
		} );
		this.$actionButtons?.forEach( ( item ) => {
			item.removeEventListener( 'click', this.handleActionClick );
		} );
	}
}

window.customElements.define( 'vrts-test-run-alerts', VrtsTestRunAlerts );
