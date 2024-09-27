class VrtsTestRunAlerts extends window.HTMLElement {
	constructor() {
		super();
		this.resolveElements();
		this.bindFunctions();
		this.bindEvents();
		this.unreadAlerts = new Set();
		this.currentAlertId = this.getAttribute( 'data-vrts-current-alert' );
	}

	resolveElements() {
		this.$heading = this.querySelector( '.vrts-test-run-alerts__heading' );
		this.$alerts = this.querySelectorAll( '[data-vrts-alert]' );
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
		this.setCurrentAlertReadStatus();
		this.checkHeadingSticky();
		this.checkReadStatusChange();

		this.$alerts.forEach( ( item ) => {
			const isUnread =
				item.getAttribute( 'data-vrts-state' ) === 'unread';
			if ( isUnread ) {
				this.unreadAlerts.add( item.getAttribute( 'data-vrts-alert' ) );
			}
		} );
	}

	setCurrentAlertReadStatus() {
		if ( this.currentAlertId ) {
			const $alert = document.getElementById(
				`vrts-alert-${ this.currentAlertId }`
			);

			setTimeout( () => {
				$alert.setAttribute( 'data-vrts-state', 'read' );
			}, 500 );
		}
	}

	checkHeadingSticky() {
		const checkIsSticky = ( entries ) => {
			const isSticky = ! entries[ 0 ].isIntersecting;
			this.$heading.setAttribute( 'data-is-sticky', isSticky );
		};

		const observer = new window.IntersectionObserver( checkIsSticky, {
			root: document,
			threshold: [ 1 ],
		} );

		observer.observe( this.$heading );
	}

	checkReadStatusChange() {
		const observer = new window.MutationObserver( ( mutations ) => {
			mutations.forEach( ( mutation ) => {
				if (
					mutation.type === 'attributes' &&
					mutation.attributeName === 'data-vrts-state'
				) {
					const id =
						mutation.target.getAttribute( 'data-vrts-alert' );
					const state =
						mutation.target.getAttribute( 'data-vrts-state' );

					if ( 'unread' === state ) {
						this.unreadAlerts.add( id );
					} else {
						this.unreadAlerts.delete( id );
					}

					this.querySelector(
						'[data-vrts-test-run-action="read-status"]'
					).setAttribute(
						'data-vrts-action-state',
						this.unreadAlerts.size > 0 ? 'primary' : 'secondary'
					);
				}
			} );
		} );

		this.$alerts.forEach( ( item ) => {
			observer.observe( item, {
				attributes: true,
			} );
		} );
	}

	handleAlertClick( e ) {
		e.preventDefault();
		const $el = e.currentTarget;
		const id = $el.getAttribute( 'data-vrts-alert' );

		if ( this.currentAlertId === id ) {
			return;
		}

		this.currentAlertId = id;

		const href = $el.getAttribute( 'href' );
		const $comparisons = document.querySelector( 'vrts-comparisons' );
		const $pagination = document.querySelector(
			'vrts-test-run-pagination'
		);

		this.$alerts.forEach( ( item ) => {
			item.setAttribute( 'data-vrts-current', 'false' );
		} );

		$el.setAttribute( 'data-vrts-current', 'true' );

		const timeout = setTimeout( () => {
			$comparisons.setAttribute( 'data-vrts-loading', 'true' );
		}, 150 );

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

				$el.setAttribute( 'data-vrts-state', 'read' );

				if ( $newComparisons ) {
					$comparisons.replaceWith( $newComparisons );
				}

				if ( $newPagination ) {
					$pagination.replaceWith( $newPagination );
				}

				clearTimeout( timeout );
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

		const action = $el.getAttribute( 'data-vrts-test-run-action' );
		const id = $el.getAttribute( 'data-vrts-test-run-id' );

		this.handleAction( action, $el, id, isPrimary );
	}

	handleAction( action, $el, id, shouldSetAction ) {
		const restEndpoint = `${ window.vrts_admin_vars.rest_url }/test-runs/${ id }/${ action }`;
		const method = shouldSetAction ? 'POST' : 'DELETE';

		const timeout = setTimeout( () => {
			$el.setAttribute( 'data-vrts-loading', 'true' );
		}, 150 );

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

				const $alerts = document.querySelectorAll(
					'.vrts-test-run-alerts__card'
				);

				$alerts.forEach( ( item ) => {
					item.setAttribute(
						'data-vrts-state',
						shouldSetAction ? 'read' : 'unread'
					);
				} );

				clearTimeout( timeout );
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
