class VrtsTestRunAlerts extends window.HTMLElement {
	constructor() {
		super();
		this.resolveElements();
		this.bindFunctions();
		this.bindEvents();
		this.unreadAlerts = new Set();
		this.initialUnreadAlerts = 0;
		this.unreadRuns = parseInt(
			this.getAttribute( 'data-vrts-unread-runs' ),
			10
		);
		this.currentAlertId = this.getAttribute( 'data-vrts-current-alert' );
	}

	resolveElements() {
		this.$heading = this.querySelector( '.vrts-test-run-alerts__heading' );
		this.$alerts = this.querySelectorAll( '[data-vrts-alert]' );
		this.$actionButtons = this.querySelectorAll(
			'[data-vrts-test-run-action]'
		);

		this.$runsMenuItems = [
			document.querySelector(
				'.vrts_navigation_link[href*="page=vrts-runs"]'
			),
			document.querySelector(
				'#adminmenu .menu-top[href*="page=vrts"] .wp-menu-name'
			),
			document.querySelector(
				'#adminmenu .wp-submenu a[href*="page=vrts-runs"]'
			),
		];
	}

	bindFunctions() {
		this.handleAlertClick = this.handleAlertClick.bind( this );
		this.handleActionClick = this.handleActionClick.bind( this );
		this.updateRunsCount = this.updateRunsCount.bind( this );
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
		this.checkHeadingSticky();
		this.checkReadStatusChange();

		this.$alerts.forEach( ( item ) => {
			const isUnread =
				item.getAttribute( 'data-vrts-state' ) === 'unread';
			if ( isUnread ) {
				this.unreadAlerts.add( item.getAttribute( 'data-vrts-alert' ) );
			}
		} );

		this.initialUnreadAlerts = this.unreadAlerts.size;
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

					this.updateRunsCount( this.unreadAlerts.size );
				}
			} );
		} );

		this.$alerts.forEach( ( item ) => {
			observer.observe( item, {
				attributes: true,
			} );
		} );
	}

	updateRunsCount( unreadAlerts ) {
		let unreadRuns = this.unreadRuns;

		if ( unreadAlerts > 0 ) {
			if ( this.initialUnreadAlerts <= 1 ) {
				unreadRuns += 1;
			}
		} else if ( this.initialUnreadAlerts > 1 ) {
			unreadRuns -= 1;
		}

		this.$runsMenuItems.forEach( ( item ) => {
			if ( item ) {
				item.querySelector( '.update-plugins' )?.remove();

				if ( unreadRuns > 0 ) {
					const $count = document.createElement( 'span' );
					const nbsp = document.createTextNode( '\u00A0' );
					$count.classList.add( 'update-plugins' );
					$count.textContent = unreadRuns;
					item.textContent = item.textContent.replace(
						/\u00A0/g,
						''
					);
					item.appendChild( nbsp );
					item.appendChild( $count );
				}
			}
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
		}, 200 );

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
				return response.json();
			} )
			.then( () => {
				const loadingTimeoutTime =
					loadingElapsedTime > 0
						? Math.abs( loadingElapsedTime - 400 )
						: 0;

				setTimeout( () => {
					$el.setAttribute( 'data-vrts-loading', 'false' );
					$el.setAttribute(
						'data-vrts-action-state',
						shouldSetAction ? 'secondary' : 'primary'
					);

					const $alerts =
						document.querySelectorAll( '[data-vrts-alert]' );

					$alerts.forEach( ( item ) => {
						item.setAttribute(
							'data-vrts-state',
							shouldSetAction ? 'read' : 'unread'
						);
					} );
				}, loadingTimeoutTime );

				clearTimeout( timeout );
				clearInterval( interval );
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
