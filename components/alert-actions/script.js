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
		this.$hideElementsForm = this.querySelector(
			'[data-vrts-hide-elements-form]'
		);
		this.$spinner = this.querySelector( '.spinner' );
		this.$success = this.querySelector(
			'.vrts-alert-actions__modal-action-success'
		);
	}

	bindFunctions() {
		this.onActionClick = this.onActionClick.bind( this );
		this.onHideElementsFormSubmit =
			this.onHideElementsFormSubmit.bind( this );
	}

	bindEvents() {
		this.$actionButtons.forEach( ( item ) => {
			item.addEventListener( 'click', this.onActionClick );
		} );
		this.$hideElementsForm.addEventListener(
			'submit',
			this.onHideElementsFormSubmit
		);
	}

	connectedCallback() {
		this.dropdown = Dropdown( this );
	}

	onHideElementsFormSubmit( e ) {
		e.preventDefault();
		const $form = e.currentTarget;
		const formData = new window.FormData( $form );
		const postId = formData.get( 'post_id' );

		this.$spinner.classList.add( 'is-active' );
		this.$success.classList.remove( 'is-active' );

		fetch( `${ window.vrts_admin_vars.rest_url }/tests/post/${ postId }`, {
			method: 'PUT',
			headers: {
				'X-WP-Nonce': window.vrts_admin_vars.rest_nonce,
			},
			body: new URLSearchParams( formData ),
		} )
			.then( ( response ) => {
				return response.json();
			} )
			.then( () => {
				this.$spinner.classList.remove( 'is-active' );
				this.$success.classList.add( 'is-active' );
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

					const $alert = document.getElementById(
						`vrts-alert-${ id }`
					);

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
				}, loadingTimeoutTime );

				clearTimeout( timeout );
				clearInterval( interval );
			} );
	}

	disconnectedCallback() {
		this.dropdown?.();
		this.$actionButtons?.forEach( ( item ) => {
			item.removeEventListener( 'click', this.onActionClick );
		} );
		this.$hideElementsForm?.removeEventListener(
			'submit',
			this.onHideElementsFormSubmit
		);
	}
}

window.customElements.define( 'vrts-alert-actions', VrtsAlertActions );
