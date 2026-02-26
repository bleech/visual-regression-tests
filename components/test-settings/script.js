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
		this.$save = this.querySelector( '.vrts-test-settings-modal__save' );
		this.$spinner = this.$save.querySelector( '.spinner' );
		this.$success = this.querySelector(
			'.vrts-test-settings-modal__action-success'
		);
		this.$aiPanel = this.querySelector(
			'.vrts-test-settings-modal__ai-panel'
		);
		this.$aiLabel = this.querySelector( '[data-ai-label]' );
		this.$aiToggle = this.querySelector( '[data-ai-toggle]' );
		this.$aiDetails = this.querySelector(
			'.vrts-test-settings-modal__ai-details'
		);
	}

	bindFunctions() {
		this.onButtonClick = this.onButtonClick.bind( this );
		this.onFormSubmit = this.onFormSubmit.bind( this );
		this.onModalClose = this.onModalClose.bind( this );
		this.onToggleClick = this.onToggleClick.bind( this );
	}

	bindEvents() {
		document.addEventListener( 'click', this.onButtonClick );
		this.$form.addEventListener( 'submit', this.onFormSubmit );
		this.$modal.addEventListener( 'hide', this.onModalClose );
		this.$aiToggle.addEventListener( 'click', this.onToggleClick );
	}

	onButtonClick( e ) {
		const button = e.target.closest( '.vrts-test-settings-button' );
		if ( ! button ) {
			return;
		}

		const postId = button.getAttribute( 'data-post-id' );
		const testId = button.getAttribute( 'data-test-id' );
		const testStatus = button.getAttribute( 'data-status' );
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
		this.$aiToggle.setAttribute( 'aria-expanded', 'false' );
		this.showAiPanel( aiSelectorsRaw, testStatus );

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

	showAiPanel( aiSelectorsRaw, testStatus ) {
		const hasAiMeta = aiSelectorsRaw !== '';
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
			this.$aiPanel.setAttribute( 'data-ai-state', 'results' );
			this.renderAiSelectors();
			this.updateAiLabel();
		} else if ( testStatus === 'waiting' && ! hasAiMeta ) {
			this.$aiPanel.hidden = false;
			this.$aiPanel.setAttribute( 'data-ai-state', 'loading' );
			this.$aiLabel.textContent = this.$aiPanel.dataset.textLoading;
		} else if ( hasAiMeta ) {
			this.$aiPanel.hidden = false;
			this.$aiPanel.setAttribute( 'data-ai-state', 'empty' );
			this.$aiLabel.textContent = this.$aiPanel.dataset.textEmpty;
		} else {
			this.$aiPanel.hidden = true;
		}
	}

	renderAiSelectors() {
		const items = this.aiSelectors
			.map(
				( item ) =>
					`<div class="vrts-test-settings-modal__ai-item"><code>${ item.selector }</code><p class="description">${ item.reason }</p></div>`
			)
			.join( '' );
		this.$aiDetails.innerHTML = `<div class="vrts-test-settings-modal__ai-details-inner">${ items }</div>`;
		const inner = this.$aiDetails.querySelector(
			'.vrts-test-settings-modal__ai-details-inner'
		);
		const updateFade = () => {
			const hasOverflow = inner.scrollHeight > inner.clientHeight;
			const atBottom =
				inner.scrollTop + inner.clientHeight >= inner.scrollHeight - 2;
			inner.classList.toggle( 'has-overflow', hasOverflow && ! atBottom );
		};
		inner.addEventListener( 'scroll', updateFade );
		window.requestAnimationFrame( updateFade );
	}

	updateAiLabel() {
		const total = this.aiSelectors.length;
		const aiSet = new Set(
			this.aiSelectors.map( ( item ) => item.selector )
		);
		const textareaSelectors = this.$textarea.value
			.split( ',' )
			.map( ( s ) => s.trim() )
			.filter( Boolean );
		const isExactMatch =
			textareaSelectors.length === aiSet.size &&
			textareaSelectors.every( ( s ) => aiSet.has( s ) );
		const key = isExactMatch ? 'Added' : 'Suggested';
		const plural = total === 1 ? 'Singular' : 'Plural';
		const template = this.$aiPanel.dataset[ `text${ key }${ plural }` ];
		this.$aiLabel.textContent = template.replace( '%d', total );
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

	onToggleClick() {
		const isOpen = this.$aiDetails.classList.toggle( 'is-open' );
		this.$aiToggle.classList.toggle( 'is-open' );
		this.$aiToggle.setAttribute( 'aria-expanded', String( isOpen ) );

		clearTimeout( this.animatingTimeout );
		if ( isOpen ) {
			this.$aiPanel.classList.add( 'is-animating' );
			this.animatingTimeout = setTimeout( () => {
				this.$aiPanel.classList.remove( 'is-animating' );
			}, 300 );
		} else {
			this.$aiPanel.classList.add( 'is-closing' );
			this.animatingTimeout = setTimeout( () => {
				this.$aiPanel.classList.remove( 'is-closing' );
			}, 300 );
		}
	}

	onModalClose() {
		this.$success.classList.remove( 'is-active' );
		this.$aiDetails.classList.remove( 'is-open' );
		this.$aiToggle.classList.remove( 'is-open' );
		this.$aiToggle.setAttribute( 'aria-expanded', 'false' );
		this.aiSelectors = [];
	}

	disconnectedCallback() {
		document.removeEventListener( 'click', this.onButtonClick );
		this.$form?.removeEventListener( 'submit', this.onFormSubmit );
		this.$modal?.removeEventListener( 'hide', this.onModalClose );
		this.$aiToggle?.removeEventListener( 'click', this.onToggleClick );
	}
}

window.customElements.define( 'vrts-test-settings', VrtsTestSettings );
