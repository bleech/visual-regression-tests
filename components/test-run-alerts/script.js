class VrtsTestRunAlerts extends window.HTMLElement {
	constructor() {
		super();
		this.resolveElements();
		this.bindFunctions();
		this.bindEvents();
	}

	resolveElements() {
		this.$actionButtons = this.querySelectorAll(
			'[data-vrts-test-run-action]'
		);
	}

	bindFunctions() {
		this.handleActionClick = this.handleActionClick.bind( this );
	}

	bindEvents() {
		this.$actionButtons.forEach( ( item ) => {
			item.addEventListener( 'click', this.handleActionClick );
		} );
	}

	connectedCallback() {
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
		this.$actionButtons?.forEach( ( item ) => {
			item.removeEventListener( 'click', this.handleActionClick );
		} );
	}
}

window.customElements.define( 'vrts-test-run-alerts', VrtsTestRunAlerts );
