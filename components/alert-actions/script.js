class VrtsAlertActions extends window.HTMLElement {
	constructor() {
		super();
		this.setAsReadTimeout = null;
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
		this.onActionClick = this.onActionClick.bind( this );
	}

	bindEvents() {
		this.$actionButtons.forEach( ( item ) => {
			item.addEventListener( 'click', this.onActionClick );
		} );
	}

	connectedCallback() {
		this.syncStateFromSidebar();
		this.setAsReadOnView();
	}

	// Cached markup may carry stale button states; the sidebar card is the
	// live source of truth, so sync from it before setAsReadOnView() runs.
	syncStateFromSidebar() {
		this.$actionButtons.forEach( ( action ) => {
			const id = action.getAttribute( 'data-vrts-alert-id' );
			const $alert = document.getElementById( `vrts-alert-${ id }` );

			if ( ! $alert ) {
				return;
			}

			const type = action.getAttribute( 'data-vrts-alert-action' );

			if ( 'read-status' === type ) {
				const isRead =
					$alert.getAttribute( 'data-vrts-state' ) === 'read';
				this.setState( action, isRead ? 'secondary' : 'primary' );
			}

			if ( 'false-positive' === type ) {
				const isFalsePositive =
					$alert.getAttribute( 'data-vrts-false-positive' ) ===
					'true';
				this.setState(
					action,
					isFalsePositive ? 'secondary' : 'primary'
				);
			}
		} );
	}

	setState( $el, state ) {
		$el.setAttribute( 'data-vrts-action-state', state );

		if ( $el.hasAttribute( 'aria-pressed' ) ) {
			$el.setAttribute( 'aria-pressed', String( state === 'secondary' ) );
		}

		const title = $el.getAttribute( `data-title-${ state }` );

		if ( title ) {
			$el.setAttribute( 'title', title );
		}
	}

	setAsReadOnView() {
		this.$actionButtons.forEach( ( action ) => {
			const isReadStatusAction =
				action.getAttribute( 'data-vrts-alert-action' ) ===
				'read-status';
			const isUnread =
				action.getAttribute( 'data-vrts-action-state' ) === 'primary';

			if ( isReadStatusAction && isUnread ) {
				this.setAsReadTimeout = setTimeout( () => {
					action.click();
				}, 1000 );
			}
		} );
	}

	onActionClick( e ) {
		const $el = e.currentTarget;
		const isLoading = $el.getAttribute( 'data-vrts-loading' ) === 'true';
		const state = $el.getAttribute( 'data-vrts-action-state' );
		const isPrimary = state === 'primary';

		if ( isLoading ) {
			return;
		}

		const action = $el.getAttribute( 'data-vrts-alert-action' );
		const id = $el.getAttribute( 'data-vrts-alert-id' );

		this.handleAction( action, $el, id, isPrimary );
	}

	handleAction( action, $el, id, shouldSetAction ) {
		const restEndpoint = `${ window.vrts_admin_vars.rest_url }/alerts/${ id }/${ action }`;
		const method = shouldSetAction ? 'POST' : 'DELETE';

		let loadingElapsedTime = 0;
		let interval = null;

		const timeout = setTimeout( () => {
			$el.setAttribute( 'data-vrts-loading', 'true' );
			const loadingStartTime = window.Date.now();
			interval = setInterval( () => {
				loadingElapsedTime = window.Date.now() - loadingStartTime;
			}, 50 );
		}, 200 );

		fetch( restEndpoint, {
			method,
			headers: {
				'X-WP-Nonce': window.vrts_admin_vars.rest_nonce,
			},
		} )
			.then( ( response ) => {
				if ( ! response.ok ) {
					throw new Error( `HTTP ${ response.status }` );
				}

				return response.json();
			} )
			.then( () => {
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

				const loadingTimeoutTime =
					loadingElapsedTime > 0
						? Math.max( 400 - loadingElapsedTime, 0 )
						: 0;

				setTimeout( () => {
					$el.setAttribute( 'data-vrts-loading', 'false' );

					this.setState(
						$el,
						shouldSetAction ? 'secondary' : 'primary'
					);
				}, loadingTimeoutTime );

				clearTimeout( timeout );
				clearInterval( interval );
			} )
			.catch( () => {
				clearTimeout( timeout );
				clearInterval( interval );
				$el.setAttribute( 'data-vrts-loading', 'false' );
			} );
	}

	disconnectedCallback() {
		clearTimeout( this.setAsReadTimeout );
		this.$actionButtons?.forEach( ( item ) => {
			item.removeEventListener( 'click', this.onActionClick );
		} );
	}
}

window.customElements.define( 'vrts-alert-actions', VrtsAlertActions );
