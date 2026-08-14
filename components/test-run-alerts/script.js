import {
	fetchPage,
	warmAlert,
	startNavigation,
	isCurrentNavigation,
} from '../../assets/scripts/page-cache';

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
	}

	resolveElements() {
		this.$heading = this.querySelector( '.vrts-test-run-alerts__heading' );
		this.$alerts = this.querySelectorAll( '[data-vrts-alert]' );
		this.$actionButtons = this.querySelectorAll(
			'[data-vrts-test-run-action]'
		);

		this.$runsMenuItems = [
			document.querySelector(
				'.vrts-admin-header [href*="page=vrts-runs"]'
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
		this.handleAlertWarm = this.handleAlertWarm.bind( this );
		this.handleAlertWarmCancel = this.handleAlertWarmCancel.bind( this );
		this.updateRunsCount = this.updateRunsCount.bind( this );
	}

	bindEvents() {
		this.$alerts?.forEach( ( item ) => {
			item.addEventListener( 'click', this.handleAlertClick );
			item.addEventListener( 'pointerenter', this.handleAlertWarm );
			item.addEventListener( 'pointerleave', this.handleAlertWarmCancel );
			item.addEventListener( 'focus', this.handleAlertWarm );
			item.addEventListener( 'blur', this.handleAlertWarmCancel );
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

					if ( id === 'receipt' ) {
						return;
					}

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

		if ( unreadAlerts > 0 && this.initialUnreadAlerts === 0 ) {
			unreadRuns += 1;
		} else if ( unreadAlerts === 0 && this.initialUnreadAlerts > 0 ) {
			unreadRuns -= 1;
		}

		this.$runsMenuItems.forEach( ( item ) => {
			if ( item ) {
				item.querySelector( '.update-plugins' )?.remove();
				item.textContent = item.textContent.replace( /\u00A0/g, '' );

				if ( unreadRuns > 0 ) {
					const $count = document.createElement( 'span' );
					const nbsp = document.createTextNode( '\u00A0' );
					$count.classList.add( 'update-plugins' );
					$count.textContent = unreadRuns;

					item.appendChild( nbsp );
					item.appendChild( $count );
				}
			}
		} );
	}

	handleAlertClick( e ) {
		e.preventDefault();
		const $el = e.currentTarget;
		const isCurrent = $el.getAttribute( 'data-vrts-current' ) === 'true';

		if ( isCurrent ) {
			return;
		}

		const href = $el.getAttribute( 'href' );
		const $content =
			document.querySelector( 'vrts-comparisons' ) ||
			document.querySelector( 'vrts-test-run-success' );
		const $pagination = document.querySelector(
			'vrts-test-run-pagination'
		);

		this.$alerts.forEach( ( item ) => {
			item.setAttribute( 'data-vrts-current', 'false' );
		} );

		$el.setAttribute( 'data-vrts-current', 'true' );

		const token = startNavigation();

		const timeout = setTimeout( () => {
			$content.setAttribute( 'data-vrts-loading', 'true' );
		}, 200 );

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
					$pagination.replaceWith( $newPagination );
				}
			} )
			.catch( () => {
				clearTimeout( timeout );

				if ( isCurrentNavigation( token ) ) {
					window.location.assign( href );
				}
			} );
	}

	handleAlertWarm( e ) {
		const $el = e.currentTarget;

		if ( $el.getAttribute( 'data-vrts-current' ) === 'true' ) {
			return;
		}

		const href = $el.getAttribute( 'href' );

		this.warmTimeouts = this.warmTimeouts || new Map();
		this.warmTimeouts.set(
			$el,
			setTimeout( () => warmAlert( href ), 100 )
		);
	}

	handleAlertWarmCancel( e ) {
		clearTimeout( this.warmTimeouts?.get( e.currentTarget ) );
		this.warmTimeouts?.delete( e.currentTarget );
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
				if ( ! response.ok ) {
					throw new Error( `HTTP ${ response.status }` );
				}

				return response.json();
			} )
			.then( () => {
				document
					.querySelectorAll( '[data-vrts-alert]' )
					.forEach( ( item ) => {
						item.setAttribute(
							'data-vrts-state',
							shouldSetAction ? 'read' : 'unread'
						);
					} );

				const loadingTimeoutTime =
					loadingElapsedTime > 0
						? Math.max( 400 - loadingElapsedTime, 0 )
						: 0;

				setTimeout( () => {
					$el.setAttribute( 'data-vrts-loading', 'false' );
					$el.setAttribute(
						'data-vrts-action-state',
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
		this.$alerts?.forEach( ( item ) => {
			item.removeEventListener( 'click', this.handleAlertClick );
			item.removeEventListener( 'pointerenter', this.handleAlertWarm );
			item.removeEventListener(
				'pointerleave',
				this.handleAlertWarmCancel
			);
			item.removeEventListener( 'focus', this.handleAlertWarm );
			item.removeEventListener( 'blur', this.handleAlertWarmCancel );
		} );
		this.$actionButtons?.forEach( ( item ) => {
			item.removeEventListener( 'click', this.handleActionClick );
		} );

		this.warmTimeouts?.forEach( ( timeout ) => clearTimeout( timeout ) );
	}
}

window.customElements.define( 'vrts-test-run-alerts', VrtsTestRunAlerts );
