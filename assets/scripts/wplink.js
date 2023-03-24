/* eslint-disable */
/**
 * Customized version of wp-includes/js/wplink.js
 */

( function ( $, wpLinkL10n, wp ) {
	let editor,
		searchTimer,
		River,
		Query,
		correctedURL,
		emailRegexp = /^[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,63}$/i,
		urlRegexp = /^(https?|ftp):\/\/[A-Z0-9.-]+\.[A-Z]{2,63}[^ "]*$/i,
		inputs = {},
		rivers = {},
		isTouch = 'ontouchend' in document;

	window.wpLink = {
		timeToTriggerRiver: 150,
		minRiverAJAXDuration: 200,
		riverBottomThreshold: 5,
		keySensitivity: 100,
		lastSearch: '',
		textarea: '',
		modalOpen: false,

		init() {
			inputs.wrap = $( '#wp-link-wrap' );
			inputs.dialog = $( '#wp-link' );
			inputs.backdrop = $( '#wp-link-backdrop' );
			inputs.submit = $( '#wp-link-submit' );
			inputs.close = $( '#wp-link-close' );

			// Input.
			inputs.url = $( '#wp-link-url' );
			inputs.url_post_id = $( '#wp-link-id' );
			inputs.nonce = $( '#_ajax_linking_nonce' );
			inputs.search = $( '#wp-link-search' );

			// Build rivers.
			rivers.search = new River( $( '#search-results' ) );
			rivers.recent = new River( $( '#most-recent-results' ) );
			rivers.elements = inputs.dialog.find( '.query-results' );

			// Get search notice text.
			inputs.queryNotice = $( '#query-notice-message' );
			inputs.queryNoticeTextDefault = inputs.queryNotice.find(
				'.query-notice-default'
			);
			inputs.queryNoticeTextHint =
				inputs.queryNotice.find( '.query-notice-hint' );

			// Bind event handlers.
			inputs.dialog.on( 'keydown', wpLink.keydown );
			inputs.dialog.on( 'keyup', wpLink.keyup );

			inputs.close
				.add( inputs.backdrop )
				.add( '#wp-link-cancel button' )
				.on( 'click', function ( event ) {
					event.preventDefault();
					wpLink.close();
				} );

			rivers.elements.on( 'river-select', wpLink.updateFields );

			// Display 'hint' message when search field or 'query-results' box are focused.
			inputs.search
				.on( 'focus.wplink', function () {
					inputs.queryNoticeTextDefault.hide();
					inputs.queryNoticeTextHint
						.removeClass( 'screen-reader-text' )
						.show();
				} )
				.on( 'blur.wplink', function () {
					inputs.queryNoticeTextDefault.show();
					inputs.queryNoticeTextHint
						.addClass( 'screen-reader-text' )
						.hide();
				} );

			inputs.search.on( 'keyup input', function ( event ) {
				window.clearTimeout( searchTimer );
				searchTimer = window.setTimeout( function () {
					wpLink.searchInternalLinks();
				}, 500 );
			} );

			inputs.search.on( 'keypress', function ( event ) {
				if ( event.key === 'Enter' || event.keyCode === 13 ) {
					event.preventDefault();
					inputs.search.trigger( 'keyup input' );
				}
			} );

			inputs.url.on( 'paste', function () {
				setTimeout( wpLink.correctURL, 0 );
			} );

			inputs.url.on( 'blur', wpLink.correctURL );
		},

		// If URL wasn't corrected last time and doesn't start with http:, https:, ? # or /, prepend http://.
		correctURL() {
			const url = inputs.url.val().trim();

			if (
				url &&
				correctedURL !== url &&
				! /^(?:[a-z]+:|#|\?|\.|\/)/.test( url )
			) {
				inputs.url.val( 'http://' + url );
				correctedURL = url;
			}
		},

		open( url, text ) {
			let ed,
				$body = $( document.body );

			$body.addClass( 'modal-open' );
			wpLink.modalOpen = true;

			inputs.wrap.show();
			inputs.backdrop.show();

			wpLink.refresh( url, text );

			$( document ).trigger( 'wplink-open', inputs.wrap );
		},

		refresh( url, text ) {
			let linkText = '';

			// Refresh rivers (clear links, check visibility).
			rivers.search.refresh();
			rivers.recent.refresh();

			// For the Text editor the "Link text" field is always shown.
			if ( ! inputs.wrap.hasClass( 'has-text-field' ) ) {
				inputs.wrap.addClass( 'has-text-field' );
			}

			if ( document.selection ) {
				// Old IE.
				linkText = document.selection.createRange().text || text || '';
			} else if (
				typeof this.textarea.selectionStart !== 'undefined' &&
				this.textarea.selectionStart !== this.textarea.selectionEnd
			) {
				// W3C.
				text =
					this.textarea.value.substring(
						this.textarea.selectionStart,
						this.textarea.selectionEnd
					) ||
					text ||
					'';
			}

			wpLink.setDefaultValues();

			if ( isTouch ) {
				// Close the onscreen keyboard.
				inputs.url.trigger( 'focus' ).trigger( 'blur' );
			} else {
				/*
				 * Focus the URL field and highlight its contents.
				 * If this is moved above the selection changes,
				 * IE will show a flashing cursor over the dialog.
				 */
				window.setTimeout( function () {
					inputs.url[ 0 ].select();
					inputs.url.trigger( 'focus' );
				} );
			}

			// Load the most recent results if this is the first time opening the panel.
			if ( ! rivers.recent.ul.children().length ) {
				rivers.recent.ajax();
			}

			correctedURL = inputs.url.val().replace( /^http:\/\//, '' );
		},

		close( reset ) {
			$( document.body ).removeClass( 'modal-open' );
			wpLink.modalOpen = false;

			inputs.backdrop.hide();
			inputs.wrap.hide();

			correctedURL = false;

			$( document ).trigger( 'wplink-close', inputs.wrap );
		},

		updateFields( e, li ) {
			inputs.url.val( li.children( '.item-permalink' ).val() );
			inputs.url_post_id.val( li.children( '.item-id' ).val() );
		},

		setDefaultValues() {
			// Empty the search field and swap the "rivers".
			inputs.search.val( '' );
			wpLink.searchInternalLinks();
		},

		searchInternalLinks() {
			let waiting,
				search = inputs.search.val() || '',
				minInputLength = parseInt( wpLinkL10n.minInputLength, 10 ) || 3;

			if ( search.length >= minInputLength ) {
				rivers.recent.hide();
				rivers.search.show();

				// Don't search if the keypress didn't change the title.
				if ( wpLink.lastSearch == search ) return;

				wpLink.lastSearch = search;
				waiting = inputs.search
					.parent()
					.find( '.spinner' )
					.addClass( 'is-active' );

				rivers.search.change( search );
				rivers.search.ajax( function () {
					waiting.removeClass( 'is-active' );
				} );
			} else {
				rivers.search.hide();
				rivers.recent.show();
			}
		},

		next() {
			rivers.search.next();
			rivers.recent.next();
		},

		prev() {
			rivers.search.prev();
			rivers.recent.prev();
		},

		keydown( event ) {
			let fn, id;

			// Escape key.
			if ( 27 === event.keyCode ) {
				wpLink.close();
				event.stopImmediatePropagation();
				// Tab key.
			} else if ( 9 === event.keyCode ) {
				id = event.target.id;

				// wp-link-submit must always be the last focusable element in the dialog.
				// Following focusable elements will be skipped on keyboard navigation.
				if ( id === 'wp-link-submit' && ! event.shiftKey ) {
					inputs.close.trigger( 'focus' );
					event.preventDefault();
				} else if ( id === 'wp-link-close' && event.shiftKey ) {
					inputs.submit.trigger( 'focus' );
					event.preventDefault();
				}
			}

			// Up Arrow and Down Arrow keys.
			if (
				event.shiftKey ||
				( 38 !== event.keyCode && 40 !== event.keyCode )
			) {
				return;
			}

			if (
				document.activeElement &&
				( document.activeElement.id === 'link-title-field' ||
					document.activeElement.id === 'url-field' )
			) {
				return;
			}

			// Up Arrow key.
			fn = 38 === event.keyCode ? 'prev' : 'next';
			clearInterval( wpLink.keyInterval );
			wpLink[ fn ]();
			wpLink.keyInterval = setInterval(
				wpLink[ fn ],
				wpLink.keySensitivity
			);
			event.preventDefault();
		},

		keyup( event ) {
			// Up Arrow and Down Arrow keys.
			if ( 38 === event.keyCode || 40 === event.keyCode ) {
				clearInterval( wpLink.keyInterval );
				event.preventDefault();
			}
		},

		delayedCallback( func, delay ) {
			let timeoutTriggered, funcTriggered, funcArgs, funcContext;

			if ( ! delay ) return func;

			setTimeout( function () {
				if ( funcTriggered ) return func.apply( funcContext, funcArgs );
				// Otherwise, wait.
				timeoutTriggered = true;
			}, delay );

			return function () {
				if ( timeoutTriggered ) return func.apply( this, arguments );
				// Otherwise, wait.
				funcArgs = arguments;
				funcContext = this;
				funcTriggered = true;
			};
		},
	};

	River = function ( element, search ) {
		const self = this;
		this.element = element;
		this.ul = element.children( 'ul' );
		this.contentHeight = element.children( '#link-selector-height' );
		this.waiting = element.find( '.river-waiting' );

		this.change( search );
		this.refresh();

		$( '#wp-link .query-results, #wp-link #link-selector' ).on(
			'scroll',
			function () {
				self.maybeLoad();
			}
		);
		element.on( 'click', 'li', function ( event ) {
			self.select( $( this ), event );
		} );
	};

	$.extend( River.prototype, {
		refresh() {
			this.deselect();
			this.visible = this.element.is( ':visible' );
		},
		show() {
			if ( ! this.visible ) {
				this.deselect();
				this.element.show();
				this.visible = true;
			}
		},
		hide() {
			this.element.hide();
			this.visible = false;
		},
		// Selects a list item and triggers the river-select event.
		select( li, event ) {
			let liHeight, elHeight, liTop, elTop;

			if ( li.hasClass( 'unselectable' ) || li == this.selected ) return;

			this.deselect();
			this.selected = li.addClass( 'selected' );
			// Make sure the element is visible.
			liHeight = li.outerHeight();
			elHeight = this.element.height();
			liTop = li.position().top;
			elTop = this.element.scrollTop();

			if ( liTop < 0 )
				// Make first visible element.
				this.element.scrollTop( elTop + liTop );
			else if ( liTop + liHeight > elHeight )
				// Make last visible element.
				this.element.scrollTop( elTop + liTop - elHeight + liHeight );

			// Trigger the river-select event.
			this.element.trigger( 'river-select', [ li, event, this ] );

			// Focus submit button.
			inputs.submit.focus();
		},
		deselect() {
			if ( this.selected ) this.selected.removeClass( 'selected' );
			this.selected = false;
		},
		prev() {
			if ( ! this.visible ) return;

			let to;
			if ( this.selected ) {
				to = this.selected.prev( 'li' );
				if ( to.length ) this.select( to );
			}
		},
		next() {
			if ( ! this.visible ) return;

			const to = this.selected
				? this.selected.next( 'li' )
				: $( 'li:not(.unselectable):first', this.element );
			if ( to.length ) this.select( to );
		},
		ajax( callback ) {
			const self = this,
				delay = this.query.page == 1 ? 0 : wpLink.minRiverAJAXDuration,
				response = wpLink.delayedCallback( function (
					results,
					params
				) {
					self.process( results, params );
					if ( callback ) callback( results, params );
				},
				delay );

			this.query.ajax( response );
		},
		change( search ) {
			if ( this.query && this._search == search ) return;

			this._search = search;
			this.query = new Query( search );
			this.element.scrollTop( 0 );
		},
		process( results, params ) {
			let list = '',
				alt = true,
				classes = '',
				firstPage = params.page == 1;

			if ( ! results ) {
				if ( firstPage ) {
					list +=
						'<li class="unselectable no-matches-found"><span class="item-title"><em>' +
						wpLinkL10n.noMatchesFound +
						'</em></span></li>';
				}
			} else {
				$.each( results, function () {
					classes = alt ? 'alternate' : '';
					classes += this.title ? '' : ' no-title';
					classes +=
						this.run_tests_status
							? ' vrts-tests--active'
							: '';
					list += classes ? '<li class="' + classes + '">' : '<li>';

					if ( ! this.run_tests_status ) {
						list +=
							'<input type="hidden" class="item-permalink" value="' +
							this.permalink +
							'" />';
						list +=
							'<input type="hidden" class="item-id" value="' +
							this.ID +
							'" />';
					}
					list += '<span class="item-title"><strong>';
					list += this.title ? this.title : wpLinkL10n.noTitle;
					list += '</strong><br><span class="item-permalink">';
					list += this.permalink
						? this.permalink
						: wpLinkL10n.noTitle;
					list += '</span>';
					list +=
						'</span><span class="item-info">' +
						this.info +
						'</span></li>';
					alt = ! alt;
				} );
			}

			this.ul[ firstPage ? 'html' : 'append' ]( list );
		},
		maybeLoad() {
			const self = this,
				el = this.element,
				bottom = el.scrollTop() + el.height();

			if (
				! this.query.ready() ||
				bottom <
					this.contentHeight.height() - wpLink.riverBottomThreshold
			)
				return;

			setTimeout( function () {
				const newTop = el.scrollTop(),
					newBottom = newTop + el.height();

				if (
					! self.query.ready() ||
					newBottom <
						self.contentHeight.height() -
							wpLink.riverBottomThreshold
				)
					return;

				self.waiting.addClass( 'is-active' );
				el.scrollTop( newTop + self.waiting.outerHeight() );

				self.ajax( function () {
					self.waiting.removeClass( 'is-active' );
				} );
			}, wpLink.timeToTriggerRiver );
		},
	} );

	Query = function ( search ) {
		this.page = 1;
		this.allLoaded = false;
		this.querying = false;
		this.search = search;
	};

	$.extend( Query.prototype, {
		ready() {
			return ! ( this.querying || this.allLoaded );
		},
		ajax( callback ) {
			const self = this,
				query = {
					action: 'wp-link-ajax',
					vrts_filter_query: true,
					page: this.page,
					_ajax_linking_nonce: inputs.nonce.val(),
				};

			if ( this.search ) query.search = this.search;

			this.querying = true;

			$.post(
				window.ajaxurl,
				query,
				function ( r ) {
					self.page++;
					self.querying = false;
					self.allLoaded = ! r;
					callback( r, query );
				},
				'json'
			);
		},
	} );

	$( wpLink.init );
} )( jQuery, window.wpLinkL10n, window.wp );
