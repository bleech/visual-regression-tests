const POLL_INTERVAL = 5000;
const IDLE_POLL_INTERVAL = 30000;
const SELECTOR = '[data-vrts-refresh]';

( () => {
	if ( ! document.querySelector( SELECTOR ) ) {
		return;
	}

	// Remember the server-rendered HTML so client-side changes don't count
	// as differences.
	let lastHtml = document.querySelector( SELECTOR ).outerHTML;
	let pollTimeout = null;

	const isSafeToReplace = ( $refreshable ) =>
		! window.document.hidden &&
		// Quick edit is an unsaved editing session that cannot be restored
		// after a swap.
		! $refreshable.querySelector( 'tr.inline-editor' ) &&
		// A server-rendered search value means the visible results come from
		// a POST request, so a refetched GET would drop them.
		! $refreshable
			.querySelector( '.search-box input[type="search"]' )
			?.getAttribute( 'value' );

	const findTwin = ( $next, $field ) => {
		if ( $field.id ) {
			return $next.querySelector(
				`#${ window.CSS.escape( $field.id ) }`
			);
		}

		if ( $field.name ) {
			const isTick = [ 'checkbox', 'radio' ].includes( $field.type );
			const name = window.CSS.escape( $field.name );
			const value = window.CSS.escape( $field.value );

			return $next.querySelector(
				isTick
					? `[name="${ name }"][value="${ value }"]`
					: `[name="${ name }"]`
			);
		}

		return null;
	};

	const supportsSelection = ( $field ) =>
		$field.tagName === 'TEXTAREA' ||
		/^(text|search|tel|url|password)$/.test( $field.type );

	// Carry field and focus state over to the fetched region so a swap does
	// not wipe what the user is doing. Returns a callback that restores
	// focus (run it after the swap), or false when the focused element has
	// no counterpart and the swap should be postponed.
	const preserveState = ( $live, $next ) => {
		$live
			.querySelectorAll( 'input, select, textarea' )
			.forEach( ( $field ) => {
				if ( $field.type === 'hidden' ) {
					return;
				}

				const $twin = findTwin( $next, $field );

				if ( ! $twin ) {
					return;
				}

				if ( [ 'checkbox', 'radio' ].includes( $field.type ) ) {
					$twin.checked = $field.checked;
				} else {
					$twin.value = $field.value;
				}
			} );

		const $active = $live.ownerDocument.activeElement;

		if ( ! $live.contains( $active ) ) {
			return () => {};
		}

		const $twin = findTwin( $next, $active );

		if ( ! $twin ) {
			return false;
		}

		return () => {
			$twin.focus( { preventScroll: true } );

			if ( supportsSelection( $active ) && $twin.setSelectionRange ) {
				$twin.setSelectionRange(
					$active.selectionStart,
					$active.selectionEnd,
					$active.selectionDirection || 'none'
				);
			}
		};
	};

	const schedulePoll = () => {
		// The server marks the region "active" while runs or tests are in
		// progress; anything else polls at the slow idle cadence.
		const isActive =
			document
				.querySelector( SELECTOR )
				?.getAttribute( 'data-vrts-refresh' ) === 'active';

		window.clearTimeout( pollTimeout );
		pollTimeout = window.setTimeout(
			refresh,
			isActive ? POLL_INTERVAL : IDLE_POLL_INTERVAL
		);
	};

	async function refresh() {
		const $current = document.querySelector( SELECTOR );

		if ( ! $current || ! isSafeToReplace( $current ) ) {
			// Try again later without touching the page.
			schedulePoll();
			return;
		}

		try {
			const response = await window.fetch( window.location.href, {
				credentials: 'same-origin',
				headers: {
					'Cache-Control': 'no-cache',
					'X-VRTS-Refresh': '1',
				},
			} );

			if ( response.ok ) {
				const $next = new window.DOMParser()
					.parseFromString( await response.text(), 'text/html' )
					.querySelector( SELECTOR );

				// The user may have started interacting while the request
				// was in flight, so re-resolve the region and re-run the
				// guards before swapping.
				const $live = document.querySelector( SELECTOR );

				if (
					$next &&
					$live &&
					isSafeToReplace( $live ) &&
					lastHtml !== $next.outerHTML
				) {
					// Snapshot before user state gets copied into $next.
					const nextHtml = $next.outerHTML;
					const restoreFocus = preserveState( $live, $next );

					if ( false !== restoreFocus ) {
						lastHtml = nextHtml;
						$live.replaceWith( $next );
						restoreFocus();
						$next.dispatchEvent(
							new window.CustomEvent( 'vrts-refreshed', {
								bubbles: true,
							} )
						);
					}
				}
			}
		} catch ( error ) {
			// Network hiccup — try again on the next tick.
		}

		schedulePoll();
	}

	schedulePoll();
} )();
