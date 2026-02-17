const AI_SELECTOR_POOL = [
	{
		selector: '.cookie-banner',
		reason: 'Consent overlay, appears conditionally',
	},
	{
		selector: '.ads-container',
		reason: 'Ad content changes between page loads',
	},
	{
		selector: '#popup-overlay',
		reason: 'Modal popup, not always visible',
	},
	{
		selector: '.chat-widget',
		reason: 'Live chat state varies per visit',
	},
	{
		selector: '.notification-bar',
		reason: 'Dismissible banner, shown conditionally',
	},
	{
		selector: '.carousel-slide',
		reason: 'Rotating content changes on each load',
	},
	{
		selector: '.dynamic-counter',
		reason: 'Counter value updates in real time',
	},
	{
		selector: '#live-chat',
		reason: 'Chat window state is unpredictable',
	},
	{
		selector: '.video-autoplay',
		reason: 'Video frame differs on each capture',
	},
	{
		selector: '.social-feed',
		reason: 'Feed content refreshes dynamically',
	},
	{
		selector: '.rotating-banner',
		reason: 'Banner rotates between creatives',
	},
	{
		selector: '.countdown-timer',
		reason: 'Timer value changes every second',
	},
];

class VrtsTestSettings extends window.HTMLElement {
	constructor() {
		super();
		this.aiSelectors = [];
		this.resolveElements();
		this.bindFunctions();
		this.bindEvents();
	}

	resolveElements() {
		this.$modal = this.querySelector( 'vrts-modal' );
		this.$form = this.querySelector( '[data-vrts-test-settings-form]' );
		this.$textarea = this.$form.querySelector(
			'[name="hide_css_selectors"]'
		);
		this.$postIdInput = this.$form.querySelector( '[name="post_id"]' );
		this.$testIdInput = this.$form.querySelector( '[name="test_id"]' );
		this.$save = this.querySelector(
			'.vrts-test-settings-modal__save'
		);
		this.$spinner = this.$save.querySelector( '.spinner' );
		this.$success = this.querySelector(
			'.vrts-test-settings-modal__action-success'
		);
		this.$aiPanel = this.querySelector(
			'.vrts-test-settings-modal__ai-panel'
		);
		this.$aiCount = this.querySelector( '[data-ai-count]' );
		this.$aiToggle = this.querySelector( '[data-ai-toggle]' );
		this.$aiDetails = this.querySelector(
			'.vrts-test-settings-modal__ai-details'
		);
		this.$aiSuggest = this.querySelector(
			'.vrts-test-settings-modal__ai-suggest'
		);
		this.$aiSpinner = this.$aiSuggest.querySelector( '.spinner' );
		this.$aiButton = this.querySelector(
			'.vrts-test-settings-modal__ai-button'
		);
	}

	bindFunctions() {
		this.onButtonClick = this.onButtonClick.bind( this );
		this.onFormSubmit = this.onFormSubmit.bind( this );
		this.onModalClose = this.onModalClose.bind( this );
		this.onToggleClick = this.onToggleClick.bind( this );
		this.onAiSuggestClick = this.onAiSuggestClick.bind( this );
	}

	bindEvents() {
		document.addEventListener( 'click', this.onButtonClick );
		this.$form.addEventListener( 'submit', this.onFormSubmit );
		this.$modal.addEventListener( 'hide', this.onModalClose );
		this.$aiToggle.addEventListener( 'click', this.onToggleClick );
		this.$aiButton.addEventListener( 'click', this.onAiSuggestClick );
	}

	onButtonClick( e ) {
		const button = e.target.closest( '.vrts-test-settings-button' );
		if ( ! button ) {
			return;
		}

		const postId = button.getAttribute( 'data-post-id' );
		const testId = button.getAttribute( 'data-test-id' );
		const hiddenData = document.getElementById( 'inline_' + testId );
		const selectors =
			hiddenData?.querySelector( '.hide_css_selectors' )?.textContent ||
			'';
		const aiSeen =
			hiddenData?.querySelector( '.ai_selectors_seen' )?.textContent ||
			'1';
		const aiSelectorsRaw =
			hiddenData?.querySelector( '.ai_selectors' )?.textContent || '';

		this.$postIdInput.value = postId;
		this.$testIdInput.value = testId;
		this.$textarea.value = selectors;
		this.$success.classList.remove( 'is-active' );

		// Populate AI panel.
		this.aiSelectors = [];
		this.$aiDetails.classList.remove( 'is-open' );
		this.$aiToggle.classList.remove( 'is-open' );
		this.showAiPanel( aiSelectorsRaw );

		// Handle AI seen state.
		if ( aiSeen === '0' ) {
			// Mark as seen via REST.
			fetch(
				`${ window.vrts_admin_vars.rest_url }/tests/${ testId }/ai-seen`,
				{
					method: 'POST',
					headers: {
						'X-WP-Nonce': window.vrts_admin_vars.rest_nonce,
					},
				}
			);

			// Clear gradient on button and update tooltip.
			button.setAttribute( 'data-ai-seen', 'true' );
			button.title = button.getAttribute( 'aria-label' );

			// Update inline data.
			const aiSeenEl = hiddenData?.querySelector( '.ai_selectors_seen' );
			if ( aiSeenEl ) {
				aiSeenEl.textContent = '1';
			}
		}
	}

	showAiPanel( aiSelectorsRaw ) {
		let newSelectors = [];
		try {
			newSelectors =
				typeof aiSelectorsRaw === 'string' && aiSelectorsRaw
					? JSON.parse( aiSelectorsRaw )
					: aiSelectorsRaw || [];
		} catch ( err ) {
			newSelectors = [];
		}

		// Append new selectors, avoiding duplicates.
		const existingSet = new Set(
			this.aiSelectors.map( ( item ) => item.selector )
		);
		newSelectors.forEach( ( item ) => {
			if ( ! existingSet.has( item.selector ) ) {
				this.aiSelectors.push( item );
			}
		} );

		if ( this.aiSelectors.length > 0 ) {
			this.$aiPanel.hidden = false;
			this.$aiCount.textContent = this.aiSelectors.length;
			const rows = this.aiSelectors
				.map(
					( item ) =>
						`<div class="vrts-test-settings-modal__ai-row"><code>${ item.selector }</code><span class="vrts-test-settings-modal__ai-reason">${ item.reason }</span></div>`
				)
				.join( '' );
			this.$aiDetails.innerHTML = `<div class="vrts-test-settings-modal__ai-details-inner">${ rows }</div>`;
		} else {
			this.$aiPanel.hidden = true;
		}
	}

	onFormSubmit( e ) {
		e.preventDefault();
		const formData = new window.FormData( this.$form );
		const postId = formData.get( 'post_id' );
		const testId = formData.get( 'test_id' );

		this.$spinner.classList.add( 'is-active' );
		this.$success.classList.remove( 'is-active' );

		fetch( `${ window.vrts_admin_vars.rest_url }/tests/post/${ postId }`, {
			method: 'PUT',
			headers: {
				'X-WP-Nonce': window.vrts_admin_vars.rest_nonce,
			},
			body: new URLSearchParams( formData ),
		} )
			.then( ( response ) => response.json() )
			.then( () => {
				this.$spinner.classList.remove( 'is-active' );
				this.$success.classList.add( 'is-active' );
				setTimeout( () => {
					this.$success.classList.remove( 'is-active' );
				}, 5000 );

				// Update hidden inline data so next open shows fresh value.
				const hiddenData = document.getElementById(
					'inline_' + testId
				);
				if ( hiddenData ) {
					const el = hiddenData.querySelector(
						'.hide_css_selectors'
					);
					if ( el ) {
						el.textContent = formData.get( 'hide_css_selectors' );
					}
				}
			} );
	}

	onAiSuggestClick() {
		if ( this.$aiButton.classList.contains( 'is-loading' ) ) {
			return;
		}

		this.$aiButton.classList.add( 'is-loading' );
		this.$aiSpinner.classList.add( 'is-active' );

		setTimeout( () => {
			// Get existing selectors to avoid duplicates.
			const current = this.$textarea.value.trim();
			const existing = current
				? current.split( ',' ).map( ( s ) => s.trim() )
				: [];

			const available = AI_SELECTOR_POOL.filter(
				( item ) => ! existing.includes( item.selector )
			);

			if ( available.length === 0 ) {
				this.$aiButton.classList.remove( 'is-loading' );
				this.$aiSpinner.classList.remove( 'is-active' );
				return;
			}

			const count = Math.min(
				Math.floor( Math.random() * 3 ) + 1,
				available.length
			);
			const shuffled = [ ...available ].sort( () => Math.random() - 0.5 );
			const selected = shuffled.slice( 0, count );
			const newSelectors = selected
				.map( ( item ) => item.selector )
				.join( ', ' );

			if ( current ) {
				this.$textarea.value = current + ', ' + newSelectors;
			} else {
				this.$textarea.value = newSelectors;
			}

			this.showAiPanel( selected );
			this.$aiButton.classList.remove( 'is-loading' );
			this.$aiSpinner.classList.remove( 'is-active' );
		}, 3000 );
	}

	onToggleClick() {
		this.$aiDetails.classList.toggle( 'is-open' );
		this.$aiToggle.classList.toggle( 'is-open' );
	}

	onModalClose() {
		this.$success.classList.remove( 'is-active' );
		this.$aiDetails.classList.remove( 'is-open' );
		this.$aiToggle.classList.remove( 'is-open' );
		this.aiSelectors = [];
	}

	disconnectedCallback() {
		document.removeEventListener( 'click', this.onButtonClick );
		this.$form?.removeEventListener( 'submit', this.onFormSubmit );
		this.$modal?.removeEventListener( 'hide', this.onModalClose );
		this.$aiToggle?.removeEventListener( 'click', this.onToggleClick );
		this.$aiButton?.removeEventListener( 'click', this.onAiSuggestClick );
	}
}

window.customElements.define( 'vrts-test-settings', VrtsTestSettings );
