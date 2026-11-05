// First line of the prompt, also shown as its preview.
const PROMPT_INTRO =
	'Help me configure this test to prevent noise and only alert on real changes.';

class VrtsTestSettings extends window.HTMLElement {
	constructor() {
		super();
		this.testId = null;
		this.editors = {};
		this.preloaded = {};
		this.loaded = null;
		this.bindFunctions();
	}

	resolveElements() {
		this.preloaded = {
			...JSON.parse(
				this.getAttribute( 'data-vrts-preloaded-settings' ) || '{}'
			),
			...this.preloaded,
		};
		this.$modal = this.querySelector( 'vrts-modal' );
		this.$form = this.querySelector( '[data-vrts-test-settings-form]' );
		this.$fieldset = this.$form.querySelector( 'fieldset' );
		this.$spinner = this.$form.querySelector( '.spinner' );
		this.$submit = this.$form.querySelector( '[type="submit"]' );
		this.$message = this.$form.querySelector(
			'[data-vrts-test-settings-message]'
		);
		this.$heading = this.querySelector(
			'[data-vrts-test-settings-heading]'
		);
		this.$subtitle = this.querySelector(
			'[data-vrts-test-settings-subtitle]'
		);
		this.$copyPrompt = this.$form.querySelector(
			'[data-vrts-copy-prompt]'
		);
		this.$copyStatus = this.querySelector(
			'#vrts-test-settings-agent-status'
		);
		this.$form.querySelector( '[data-vrts-prompt-preview]' ).textContent =
			PROMPT_INTRO;
		this.$codeTabs = [
			...this.$form.querySelectorAll( '[data-vrts-code-tab]' ),
		];
	}

	bindFunctions() {
		this.init = this.init.bind( this );
		this.onShow = this.onShow.bind( this );
		this.onHide = this.onHide.bind( this );
		this.onSubmit = this.onSubmit.bind( this );
		this.onChange = this.onChange.bind( this );
		this.onSnippet = this.onSnippet.bind( this );
		this.onCopyPrompt = this.onCopyPrompt.bind( this );
		this.onCodeTab = this.onCodeTab.bind( this );
		this.onCodeTabKey = this.onCodeTabKey.bind( this );
	}

	connectedCallback() {
		// The block editor loads this script before the modal markup is parsed.
		if ( document.readyState === 'loading' ) {
			document.addEventListener( 'DOMContentLoaded', () => this.init(), {
				once: true,
			} );
		} else {
			this.init();
		}
	}

	init() {
		this.resolveElements();
		this.$modal.addEventListener( 'show', this.onShow );
		this.$modal.addEventListener( 'hide', this.onHide );
		this.$form.addEventListener( 'submit', this.onSubmit );
		this.$form.addEventListener( 'input', this.onChange );
		this.$form.addEventListener( 'change', this.onChange );
		this.$form.addEventListener( 'change', this.onSnippet );
		this.$copyPrompt.addEventListener( 'click', this.onCopyPrompt );
		this.$codeTabs.forEach( ( $tab ) => {
			$tab.addEventListener( 'click', this.onCodeTab );
			$tab.addEventListener( 'keydown', this.onCodeTabKey );
		} );
	}

	disconnectedCallback() {
		this.$modal?.removeEventListener( 'show', this.onShow );
		this.$modal?.removeEventListener( 'hide', this.onHide );
		this.$form?.removeEventListener( 'submit', this.onSubmit );
		this.$form?.removeEventListener( 'input', this.onChange );
		this.$form?.removeEventListener( 'change', this.onChange );
		this.$form?.removeEventListener( 'change', this.onSnippet );
		this.$copyPrompt?.removeEventListener( 'click', this.onCopyPrompt );
		this.$codeTabs?.forEach( ( $tab ) => {
			$tab.removeEventListener( 'click', this.onCodeTab );
			$tab.removeEventListener( 'keydown', this.onCodeTabKey );
		} );
	}

	onCodeTab( event ) {
		this.selectCodeTab( event.currentTarget.dataset.vrtsCodeTab );
	}

	// Arrow keys move between tabs, as in the ARIA tabs pattern.
	onCodeTabKey( event ) {
		const step = { ArrowRight: 1, ArrowLeft: -1 }[ event.key ];
		if ( ! step ) {
			return;
		}
		event.preventDefault();
		const index = this.$codeTabs.indexOf( event.currentTarget );
		const $next =
			this.$codeTabs[
				( index + step + this.$codeTabs.length ) % this.$codeTabs.length
			];
		this.selectCodeTab( $next.dataset.vrtsCodeTab );
		$next.focus();
	}

	selectCodeTab( name ) {
		this.$codeTabs.forEach( ( $tab ) => {
			const selected = $tab.dataset.vrtsCodeTab === name;
			$tab.setAttribute( 'aria-selected', selected ? 'true' : 'false' );
			$tab.tabIndex = selected ? 0 : -1;
		} );
		this.$form
			.querySelectorAll( '[data-vrts-code-panel]' )
			.forEach( ( $panel ) =>
				$panel.classList.toggle(
					'is-active',
					$panel.dataset.vrtsCodePanel === name
				)
			);
		// CodeMirror cannot measure while hidden.
		this.editors[ name ]?.refresh();
	}

	initEditors() {
		if ( this.editors.custom_css || ! window.wp?.codeEditor ) {
			return;
		}

		const settings = JSON.parse(
			this.getAttribute( 'data-vrts-code-editor' ) || '{}'
		);
		const $hintContainer = this.$modal.querySelector(
			'.vrts-modal__content'
		);
		this.watchHints( $hintContainer );

		[
			[ 'custom_css', settings.css ],
			[ 'custom_js', settings.js ],
		].forEach( ( [ name, editorSettings ] ) => {
			if ( ! editorSettings ) {
				return;
			}

			// WP blurs on Tab after Escape only through these callbacks.
			editorSettings.onTabNext = ( cm ) => this.moveFocus( cm, 1 );
			editorSettings.onTabPrevious = ( cm ) => this.moveFocus( cm, -1 );

			editorSettings.codemirror = {
				...editorSettings.codemirror,
				// Scroll long lines instead of breaking selectors mid-token.
				lineWrapping: false,
				// Let Tab move focus instead of trapping keyboard users.
				extraKeys: {
					...editorSettings.codemirror?.extraKeys,
					Tab: false,
					'Shift-Tab': false,
				},
				// WP opens a completion list while typing. Its default home is
				// the body, where the modal covers it.
				hintOptions: {
					...editorSettings.codemirror?.hintOptions,
					container: $hintContainer,
				},
			};

			const $textarea = this.$form.elements[ name ];
			const editor = window.wp.codeEditor.initialize(
				$textarea,
				editorSettings
			).codemirror;

			editor
				.getInputField()
				.setAttribute( 'aria-labelledby', `${ $textarea.id }-label` );
			editor
				.getInputField()
				.setAttribute(
					'aria-describedby',
					$textarea.getAttribute( 'aria-describedby' )
				);

			editor.on( 'change', this.onChange );

			this.editors[ name ] = editor;
		} );
	}

	// WP's hint addon positions its list for a parent in the body: page
	// coordinates minus the container's offset from the body, which is off
	// by the admin bar's height, and it only checks the window for room, so
	// a long list extends the modal's scroll area. Place it from the cursor
	// instead, as soon as the addon sets it, also when it moves it on
	// editor scroll.
	watchHints( $container ) {
		const place = ( $hints ) => {
			if ( $hints.style.top === $hints.dataset.vrtsTop ) {
				return;
			}
			const editor = Object.values( this.editors ).find(
				( e ) => e.state.completionActive
			);
			const completion = editor?.state.completionActive;
			if ( ! completion?.data ) {
				return;
			}
			const cursor = editor.cursorCoords(
				completion.data.from,
				'window'
			);
			const box = $container.getBoundingClientRect();
			const { offsetWidth: width, offsetHeight: height } = $hints;
			const fitsBelow =
				cursor.bottom + height <= box.top + $container.clientHeight;
			const left = Math.min(
				cursor.left - box.left,
				$container.clientWidth - width
			);
			const top =
				( fitsBelow ? cursor.bottom : cursor.top - height ) - box.top;
			$hints.style.left = `${ left + $container.scrollLeft }px`;
			$hints.style.top = `${ top + $container.scrollTop }px`;
			$hints.dataset.vrtsTop = $hints.style.top;
		};
		const isHints = ( $node ) =>
			$node.classList?.contains( 'CodeMirror-hints' );

		new window.MutationObserver( ( records ) =>
			records.forEach( ( { target, addedNodes } ) =>
				[ target, ...addedNodes ].filter( isHints ).forEach( place )
			)
		).observe( $container, {
			childList: true,
			subtree: true,
			attributes: true,
			attributeFilter: [ 'style' ],
		} );
	}

	moveFocus( editor, step ) {
		const $items = [
			...this.$modal.querySelectorAll(
				'button, select, input, textarea, [tabindex]:not([tabindex="-1"])'
			),
		].filter( ( $item ) => ! $item.disabled && $item.offsetParent );
		const index = $items.indexOf( editor.getInputField() );

		$items[ index + step ]?.focus();
	}

	getValue( name ) {
		return this.editors[ name ]
			? this.editors[ name ].getValue()
			: this.$form.elements[ name ].value;
	}

	setValue( name, value ) {
		this.$form.elements[ name ].value = value;
		this.editors[ name ]?.setValue( value );
	}

	setDisabled( disabled ) {
		this.$fieldset.disabled = disabled;
		this.$submit.disabled = disabled;
		Object.values( this.editors ).forEach( ( editor ) =>
			editor.setOption( 'readOnly', disabled )
		);
	}

	setMessage( type ) {
		this.$message.textContent = type
			? this.$message.getAttribute( `data-${ type }` )
			: '';
		this.$message.classList.toggle( 'is-error', type?.endsWith( 'error' ) );
	}

	fill( settings ) {
		this.setValue( 'custom_css', settings.custom_css );
		this.setValue( 'custom_js', settings.custom_js );
		this.$heading.textContent = settings.title;
		this.$subtitle.textContent = settings.path;
		this.agent = settings.agent;
		this.loaded = this.getValues();
		// Open the file that has code; CSS when both or neither do.
		this.selectCodeTab(
			! settings.custom_css && settings.custom_js
				? 'custom_js'
				: 'custom_css'
		);
	}

	getValues() {
		return {
			custom_css: this.getValue( 'custom_css' ),
			custom_js: this.getValue( 'custom_js' ),
		};
	}

	isDirty() {
		return (
			!! this.loaded &&
			JSON.stringify( this.getValues() ) !== JSON.stringify( this.loaded )
		);
	}

	onChange() {
		this.setMessage( null );
	}

	onSnippet( e ) {
		const $select = e.target.closest( '[data-vrts-snippets]' );

		if ( ! $select?.value ) {
			return;
		}

		this.insertSnippet( $select.dataset.vrtsSnippets, $select.value );
		$select.value = '';
	}

	insertSnippet( name, snippet ) {
		const editor = this.editors[ name ];

		if ( ! editor ) {
			const $textarea = this.$form.elements[ name ];
			$textarea.value += ( $textarea.value ? '\n' : '' ) + snippet;
			$textarea.dispatchEvent(
				new window.Event( 'input', { bubbles: true } )
			);
			return;
		}

		// Append so a cursor in the middle of a line never splits code.
		const doc = editor.getDoc();
		const content = doc.getValue();
		let prefix = '';

		if ( content ) {
			prefix = content.endsWith( '\n' ) ? '\n' : '\n\n';
		}

		const start = content.length + prefix.length;

		doc.replaceRange(
			prefix + snippet,
			editor.posFromIndex( content.length )
		);

		// Select the placeholder selector so typing replaces it.
		const offset = snippet.indexOf( 'selector' );

		if ( offset !== -1 ) {
			doc.setSelection(
				editor.posFromIndex( start + offset ),
				editor.posFromIndex( start + offset + 'selector'.length )
			);
		}

		editor.scrollIntoView( editor.posFromIndex( start ) );
		editor.focus();
	}

	onCopyPrompt() {
		window.clearTimeout( this.copyTimeout );

		this.copyText( this.getPrompt() )
			.then( () => this.setCopyStatus( 'copied' ) )
			.catch( () => this.setCopyStatus( 'error' ) );
	}

	setCopyStatus( status ) {
		this.$copyPrompt.classList.toggle( 'is-copied', status === 'copied' );
		this.$copyStatus.textContent = this.$copyStatus.getAttribute(
			`data-${ status }`
		);
		this.copyTimeout = window.setTimeout( () => {
			this.$copyPrompt.classList.remove( 'is-copied' );
			this.$copyStatus.textContent = '';
		}, 2000 );
	}

	copyText( text ) {
		if ( window.isSecureContext && window.navigator.clipboard ) {
			return window.navigator.clipboard.writeText( text );
		}

		// The Clipboard API needs HTTPS. The textarea stays inside the modal
		// so its focus trap does not interfere.
		const $textarea = document.createElement( 'textarea' );
		$textarea.value = text;
		$textarea.setAttribute( 'readonly', '' );
		$textarea.style.position = 'fixed';
		$textarea.style.opacity = '0';
		this.$form.append( $textarea );
		$textarea.select();
		const copied = document.execCommand( 'copy' );
		$textarea.remove();
		this.$copyPrompt.focus();

		return copied ? Promise.resolve() : Promise.reject();
	}

	// Kept in English: it is read by the agent, not shown in the UI.
	getPrompt() {
		const agent = this.agent || {};
		const values = this.getValues();
		const threshold = agent.alert_threshold
			? `alert when more than ${ agent.alert_threshold }% of the most-changed screen differs`
			: 'alert on any changed pixel';
		const block = ( language, code ) =>
			code.trim()
				? `\`\`\`${ language }\n${ code.trim() }\n\`\`\``
				: '(empty)';
		const alerts = ( agent.alerts || [] ).map( ( alert, index ) => {
			const change = [
				`${ alert.pixels.toLocaleString( 'en-US' ) } px changed`,
				alert.percentage !== null &&
					`${ alert.percentage }% of the most-changed screen`,
			]
				.filter( Boolean )
				.join( ', ' );

			return [
				`${ index + 1 }. ${ ( alert.date || '' ).slice(
					0,
					10
				) }: ${ change }${
					alert.false_positive
						? ' (marked as false positive, so this change is noise)'
						: ''
				}`,
				`   Snapshot (before): ${ alert.snapshot }`,
				`   Screenshot (after): ${ alert.screenshot }`,
				`   Diff (changed pixels highlighted): ${ alert.diff }`,
				`   HTML as captured, before: ${ alert.snapshot_html }`,
				`   HTML as captured, after: ${ alert.screenshot_html }`,
			].join( '\n' );
		} );

		return [
			PROMPT_INTRO,
			'',
			'## What VRTs does',
			'I use VRTs (visual regression testing) on a WordPress page. On a schedule it takes a full-page screenshot, compares it pixel by pixel with a snapshot and alerts me when the difference is above the alert threshold. The custom CSS and JavaScript below are injected only into the VRTs browser; visitors never see them.',
			'',
			'The goal is not zero alerts. The test should alert when someone edits the page and stay quiet when nobody did. Your job is to remove false positives: differences that show up between captures even though nobody edited the site (ads, popups, sliders, videos, dates, counters, random or personalized content). An occasional alert I can dismiss costs less than a real change that goes unnoticed.',
			'',
			`Page: ${ agent.url || '' }`,
			`Alert threshold (project-wide, the same for every page): ${ threshold }`,
			`Project click selector (clicked before every screenshot of every page in this project): ${
				agent.click_selectors || 'none set'
			}`,
			'',
			'## How each screenshot is taken',
			'1. A fresh Chromium browser (no cookies, logged out, user agent contains "VRTs/") opens the page in a 1280x720 window, so cookie banners and popups show every time. "One screen" means this window.',
			'2. The Shift key is pressed once, to wake scripts that wait for user interaction (e.g. "delay JavaScript" in caching plugins).',
			'3. The first element matching the project click selector is clicked, once. It waits up to 10 seconds for a match and skips the click if none appears, so pages without a match wait those 10 seconds on every capture.',
			'4. The custom CSS is added as a style tag.',
			'5. It waits until the page and its resources have loaded.',
			'6. The custom JavaScript runs once, as the body of an async function in the page, so top-level await works. It has 15 seconds; after that the capture is stopped and fails. An error stops the rest of the script, but the screenshot is still taken. Navigating to another origin fails the screenshot.',
			'7. It scrolls down one screen at a time (up to 10,000 px) and back up, so lazy-loaded content appears, then waits for resources again plus 2 seconds.',
			'8. A full-page screenshot is taken and the DOM is saved as HTML. CSS animations and transitions are finished or reset for the capture, but JavaScript timers (clocks, sliders, counters) keep running until this moment.',
			'9. The screenshot is compared with the snapshot, and an alert is created when the difference is above the alert threshold. Saving new custom CSS or JavaScript takes a new snapshot.',
			'',
			'## Rules for the CSS and JavaScript',
			'- For each difference, ask: would it also appear between two captures with no one editing the site, whether minutes or days apart? If yes, it is noise: neutralize it. If no, it is a real change: do not add a rule for it. If unsure, leave it tested and say so.',
			'- Every rule creates a blind spot: whatever is hidden or frozen is no longer tested. Use as few rules as possible, each as narrow as possible. That includes the current rules: they are not evidence that an element is noise. Keep, narrow or remove each one.',
			'- Prefer freezing to hiding: a frozen element (a slider held on one slide, a video on its poster frame) is still tested, a hidden one is not. When hiding is the only option, hide the smallest element that contains the noise. If hiding it still lets its size change and move the content around it (e.g. a date inside a sentence), hide the smallest parent whose size is stable instead.',
			'- Freeze a slider where its position is drawn. If it moves a track with `transform`, pin the track with CSS (`transform: none !important`), which survives the slider being initialised again. For fade sliders, or loop modes that reorder slides, stop it through its API and go to the first slide. When unsure, do both, and say which one you expect to hold.',
			'- Do not hide or freeze the whole page, `main`, `body` or a large section. If the noise covers that much, explain why instead of adding a rule.',
			'- Use selectors that survive edits to the page: classes of the widget or library (e.g. `.swiper-wrapper`, `.jet-listing-grid`), not generated ids or page builder hashes (e.g. `.elementor-element-3f2a1b`, `[data-id="…"]`). If only a fragile selector is possible, say so.',
			'- Use `visibility: hidden !important` rather than `display: none`, so the layout does not shift. `!important` is fine and often needed to beat inline styles.',
			'- Prefer rules that keep working whatever the content is tomorrow. If a rule has to encode a specific value (a width, a height, a colour), say so, and say what happens when that value goes stale.',
			'- Cookie and consent banners: accept them, do not hide them, because accepting can load more content. That is done with the project click selector, not with custom CSS or JavaScript.',
			'- Prefer CSS. Use JavaScript only for what CSS cannot do: remove an element whose space must not stay, close a popup that is not a consent banner, or wait a bounded time for something that is expected to load. Keep it short, make it safe when an element is missing, and never navigate. Keep its steps independent, so one failing step does not stop the ones after it.',
			'- A resource that failed to load, a font that arrived late or a server error is not noise. Do not hide it; report it as a problem to fix on the site.',
			'- If the noise comes from the site in a way custom code cannot reliably fix (e.g. a caching plugin that delays JavaScript), say so and suggest the fix on the site, such as excluding the "VRTs/" user agent in that plugin.',
			'- If the remaining noise is tiny and unavoidable (anti-aliasing, sub-pixel shifts), say so and suggest a higher alert threshold instead of a fragile rule. The threshold applies to every page of the site, so say which small real changes it would also hide there.',
			'',
			'## Current custom CSS',
			block( 'css', values.custom_css ),
			'',
			'## Current custom JavaScript',
			block( 'js', values.custom_js ),
			'',
			'## Recent alerts',
			alerts.length
				? `Newest first. They may have been taken before the current custom CSS and JavaScript existed. The HTML files are the DOM at capture time, served gzip-compressed (curl needs --compressed).\n${ alerts.join(
						'\n'
				  ) }`
				: 'None yet.',
			'',
			'## What I need from you',
			'Open the page, then compare the images and the captured HTML of each alert to find what changed and why. If you cannot open some links, say which, and ask me to paste the diff image and the page HTML instead of guessing. Reply with:',
			'1. For each alert: noise, real change or loading problem, and what exactly changed',
			'2. The complete custom CSS (replaces the current one)',
			'3. The complete custom JavaScript (replaces the current one)',
			'4. For each rule: what noise it removes, what is no longer tested because of it, and your confidence: high if the alerts show the cause, low if it is a guess',
			'5. If a cookie or consent banner shows: a selector for its accept button, for the project click selector. Only the first matching element is clicked, so a comma-separated list is a set of alternatives, not a sequence; keep the current selector in it.',
			'Keep it as small as possible, and say so if nothing is needed. Removing one layer of noise can reveal the next (an accepted banner or a frozen slider can expose other changes), so I will come back with the alerts of the next runs.',
		].join( '\n' );
	}

	request( method, body ) {
		return fetch(
			`${ window.vrts_admin_vars.rest_url }/tests/${ this.testId }/settings`,
			{
				method,
				headers: {
					'X-WP-Nonce': window.vrts_admin_vars.rest_nonce,
					'Content-Type': 'application/json',
				},
				body: body ? JSON.stringify( body ) : undefined,
			}
		).then( ( response ) => {
			if ( ! response.ok ) {
				throw new Error( `HTTP ${ response.status }` );
			}

			return response.json();
		} );
	}

	rememberSettings( testId, settings ) {
		this.preloaded[ testId ] = settings;
		// Update every opener, including duplicates in an alert view.
		document
			.querySelectorAll( '[data-vrts-test-id]' )
			.forEach( ( $opener ) => {
				if ( $opener.getAttribute( 'data-vrts-test-id' ) === testId ) {
					$opener.setAttribute(
						'data-vrts-test-settings',
						JSON.stringify( settings )
					);
				}
			} );
	}

	onShow( e ) {
		const $opener = e.detail?.target?.closest( '[data-vrts-test-id]' );
		this.testId = $opener?.getAttribute( 'data-vrts-test-id' );

		// Every opener renders with its settings, so there is nothing to load.
		const settings =
			JSON.parse(
				$opener?.getAttribute( 'data-vrts-test-settings' ) || 'null'
			) || this.preloaded[ this.testId ];

		this.initEditors();
		this.fill(
			settings || {
				custom_css: '',
				custom_js: '',
				title: '',
				path: '',
			}
		);
		this.setDisabled( ! settings );
		this.setMessage( settings ? null : 'load-error' );
		if ( ! settings ) {
			this.loaded = null;
		}

		// a11y-dialog fires "show" while the modal is still hidden, so
		// CodeMirror can only measure its gutter on the next frame.
		window.requestAnimationFrame( () =>
			Object.values( this.editors ).forEach( ( editor ) =>
				editor.refresh()
			)
		);
	}

	onHide( e ) {
		// Escape inside a code editor should not close the modal.
		if (
			e.detail?.key === 'Escape' &&
			e.detail.target?.closest?.( '.CodeMirror' )
		) {
			e.preventDefault();
			return;
		}

		if (
			this.isDirty() &&
			// eslint-disable-next-line no-alert
			! window.confirm(
				this.$form.getAttribute( 'data-confirm-discard' )
			)
		) {
			e.preventDefault();
			return;
		}

		// A table refresh may have replaced the opener while the modal was
		// open, so return focus to its replacement (same id).
		const dialog = this.$modal.modal;
		if ( dialog?.previouslyFocused?.isConnected === false ) {
			dialog.previouslyFocused = document.getElementById(
				dialog.previouslyFocused.id
			);
		}

		this.testId = null;
		this.loaded = null;
	}

	onSubmit( e ) {
		e.preventDefault();

		const testId = this.testId;
		// Only a changed save takes a new snapshot; the server skips the rest.
		const changed = this.isDirty();

		this.setDisabled( true );
		this.$spinner.classList.add( 'is-active' );
		this.setMessage( null );

		this.request( 'PUT', this.getValues() )
			.then( ( settings ) => {
				this.rememberSettings( testId, settings );
				if ( testId !== this.testId ) {
					return;
				}

				this.fill( settings );
				this.setMessage( changed ? 'success-snapshot' : 'success' );
				document.dispatchEvent(
					new window.CustomEvent( 'vrts-refresh' )
				);
			} )
			.catch( () => {
				if ( testId === this.testId ) {
					this.setMessage( 'error' );
				}
			} )
			.finally( () => {
				if ( testId === this.testId ) {
					this.setDisabled( false );
					this.$spinner.classList.remove( 'is-active' );
				}
			} );
	}
}

window.customElements.define( 'vrts-test-settings', VrtsTestSettings );
