import Dropdown from '../../assets/scripts/dropdown';

class VrtsAlertActions extends window.HTMLElement {
	constructor() {
		super();
		this.dropdown = null;
		this.resolveElements();
		this.bindFunctions();
		this.bindEvents();
	}

	resolveElements() {
		this.$actionButtons = this.querySelectorAll(
			'[data-vrts-alert-action]'
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
		this.dropdown = Dropdown( this );
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

		const action = $el.getAttribute( 'data-vrts-alert-action' );
		const id = $el.getAttribute( 'data-vrts-alert-id' );

		this.handleAction( action, $el, id, isPrimary );
	}

	handleAction( action, $el, id, shouldSetAction ) {
		const restEndpoint = `${ window.vrts_admin_vars.rest_url }/alerts/${ id }/${ action }`;
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

				const $alert = document.getElementById( `vrts-alert-${ id }` );

				if ( $alert ) {
					if ( 'false-positive' === action ) {
						$alert.setAttribute(
							'data-vrts-false-positive',
							shouldSetAction ? 'true' : 'false'
						);
					}

					if ( 'read-status' === action ) {
						$alert.setAttribute(
							'data-vrts-state',
							shouldSetAction ? 'read' : 'unread'
						);
					}
				}
			} );
	}

	disconnectedCallback() {
		this.dropdown?.();
		this.$actionButtons?.forEach( ( item ) => {
			item.removeEventListener( 'click', this.handleActionClick );
		} );
	}
}

window.customElements.define( 'vrts-alert-actions', VrtsAlertActions );
