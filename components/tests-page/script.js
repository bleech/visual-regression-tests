/* global jQuery, ajaxurl, inlineEditTest */

document
	.getElementById( 'show-modal-add-new' )
	?.addEventListener( 'click', () => {
		window.wpLink.open( 'input_hidden_internal_url' );
	} );

window.wp = window.wp || {};
( function ( $, wp ) {
	window.inlineEditTest = {
		/**
		 * Initializes the inline and bulk post editor.
		 *
		 * Binds event handlers to the Escape key to close the inline editor
		 * and to the save and close buttons. Changes DOM to be ready for inline
		 * editing. Adds event handler to bulk edit.
		 *
		 * @since 2.7.0
		 *
		 * @memberof inlineEditTest
		 *
		 * @return {void}
		 */
		init() {
			const t = this,
				qeRow = $( '#inline-edit' );

			t.type = 'test';
			// Test ID prefix.
			t.what = '#test-';

			/**
			 * Binds the Escape key to revert the changes and close the quick editor.
			 *
			 * @return {boolean} The result of revert.
			 */
			qeRow.on( 'keyup', function ( e ) {
				// Revert changes if Escape key is pressed.
				if ( e.which === 27 ) {
					return inlineEditTest.revert();
				}
			} );

			/**
			 * Reverts changes and close the quick editor if the cancel button is clicked.
			 *
			 * @return {boolean} The result of revert.
			 */
			$( '.cancel', qeRow ).on( 'click', function () {
				return inlineEditTest.revert();
			} );

			/**
			 * Saves changes in the quick editor if the save(named: update) button is clicked.
			 *
			 * @return {boolean} The result of save.
			 */
			$( '.save', qeRow ).on( 'click', function () {
				return inlineEditTest.save( this );
			} );

			/**
			 * If Enter is pressed, and the target is not the cancel button, save the post.
			 *
			 * @return {boolean} The result of save.
			 */
			$( 'td', qeRow ).on( 'keydown', function ( e ) {
				if (
					e.which === 13 &&
					! e.shiftKey &&
					! $( e.target ).hasClass( 'cancel' )
				) {
					return inlineEditTest.save( this );
				}
			} );

			/**
			 * Binds click event to the .editinline button which opens the quick editor.
			 */
			$( '#the-list' ).on( 'click', '.editinline', function () {
				$( this ).attr( 'aria-expanded', 'true' );
				inlineEditTest.edit( this );
			} );

			/**
			 * Adds onclick events to the apply buttons.
			 */
			$( '#doaction' ).on( 'click', function ( e ) {
				t.whichBulkButtonId = $( this ).attr( 'id' );
				const n = t.whichBulkButtonId.substr( 2 );

				if ( 'edit' === $( 'select[name="' + n + '"]' ).val() ) {
					e.preventDefault();
				} else if (
					$( 'form#posts-filter tr.inline-editor' ).length > 0
				) {
					t.revert();
				}
			} );
		},

		/**
		 * Toggles the quick edit window, hiding it when it's active and showing it when
		 * inactive.
		 *
		 * @since 2.7.0
		 *
		 * @memberof inlineEditTest
		 *
		 * @param {Object} el Element within a post table row.
		 */
		toggle( el ) {
			const t = this;
			// eslint-disable-next-line
				$( t.what + t.getId( el ) ).css( 'display' ) === 'none'
				? t.revert()
				: t.edit( el );
		},

		/**
		 * Creates a quick edit window for the post that has been clicked.
		 *
		 * @since 2.7.0
		 *
		 * @memberof inlineEditTest
		 *
		 * @param {number|Object} id The ID of the clicked post or an element within a post
		 *                           table row.
		 * @return {boolean} Always returns false at the end of execution.
		 */
		edit( id ) {
			const t = this;
			t.revert();

			if ( typeof id === 'object' ) {
				id = t.getId( id );
			}

			// Add the new edit row with an extra blank row underneath to maintain zebra striping.
			const editRow = $( '#inline-edit' ).clone( true );
			$( 'td', editRow ).attr(
				'colspan',
				$( 'th:visible, td:visible', '.widefat:first thead' ).length
			);

			// Remove the ID from the copied row and let the `for` attribute reference the hidden ID.
			$( 'td', editRow ).find( '#quick-edit-legend' ).removeAttr( 'id' );
			$( 'td', editRow )
				.find( 'p[id^="quick-edit-"]' )
				.removeAttr( 'id' );

			$( t.what + id )
				.removeClass( 'is-expanded' )
				.hide()
				.after( editRow )
				.after( '<tr class="hidden"></tr>' );

			// Populate fields in the quick edit window.
			const rowData = $( '#inline_' + id );
			const hideInputSelectorsText = $(
				'.hide_css_selectors',
				rowData
			).text();
			$( ':input[name="hide_css_selectors"]', editRow ).val(
				hideInputSelectorsText
			);

			$( editRow )
				.attr( 'id', 'edit-' + id )
				.addClass( 'inline-editor' )
				.show();
			$( ':input[name="hide_css_selectors"]', editRow ).trigger(
				'focus'
			);

			return false;
		},

		/**
		 * Saves the changes made in the quick edit window to the post.
		 * Ajax saving is only for Quick Edit and not for bulk edit.
		 *
		 * @since 2.7.0
		 *
		 * @param {number} id The ID for the post that has been changed.
		 * @return {boolean} False, so the form does not submit when pressing
		 *                   Enter on a focused field.
		 */
		save( id ) {
			if ( typeof id === 'object' ) {
				id = this.getId( id );
			}

			$( 'table.widefat .spinner' ).addClass( 'is-active' );

			const params = {
				action: 'vrts_test_quick_edit_save',
				test_id: id,
				hide_css_selectors: $(
					'#edit-' + id + ' [name="hide_css_selectors"]'
				).val(),
				nonce: $( '#_vrts_test_quick_edit_nonce' ).val(),
			};

			// Make Ajax request
			$.post(
				ajaxurl,
				params,
				function ( response ) {
					$( 'table.widefat .spinner' ).removeClass( 'is-active' );

					// Work with the response.
					response = $.parseJSON( response );

					if ( ! response.success ) {
						const $errorNotice = $(
							'#edit-' + id + ' .inline-edit-save .notice-error'
						);
						$errorNotice.removeClass( 'hidden' );
						$errorNotice.text( response.message );
						wp.a11y.speak( response.message );
						return;
					}

					$( '#inline_' + id + ' .hide_css_selectors' ).text(
						response.hide_css_selectors
					);

					// Hide the quick edit window.
					const $tableWideFat = $( '.widefat' );
					id = $( '.inline-editor', $tableWideFat ).attr( 'id' );

					if ( id ) {
						$( '.spinner', $tableWideFat ).removeClass(
							'is-active'
						);

						// Remove both the inline-editor and its hidden tr siblings.
						$( '#' + id )
							.siblings( 'tr.hidden' )
							.addBack()
							.remove();
						id = id.substr( id.lastIndexOf( '-' ) + 1 );

						// Show the post row and move focus back to the Quick Edit button.
						$( '#test-' + id )
							.fadeIn( 400 )
							.find( '.editinline' )
							.attr( 'aria-expanded', 'false' )
							.trigger( 'focus' );
					}

					wp.a11y.speak( response.message );

					// Update the snapshot column in the table and set the status of the snapshot.
					const snapshotStatus = response.snapshot_status;
					const $snapshotColumn = $(
						'#test-' + id + ' .base_screenshot_date'
					);
					if (
						null !== snapshotStatus &&
						undefined !== snapshotStatus &&
						'' !== snapshotStatus
					) {
						$snapshotColumn.text( snapshotStatus );
					}
				},
				'html'
			);

			// Prevent submitting the form when pressing Enter on a focused field.
			return false;
		},

		/**
		 * Hides and empties the Quick Edit and/or Bulk Edit windows.
		 *
		 * @since 2.7.0
		 *
		 * @memberof inlineEditTest
		 *
		 * @return {boolean} Always returns false.
		 */
		revert() {
			const $tableWideFat = $( '.widefat' );
			let id = $( '.inline-editor', $tableWideFat ).attr( 'id' );

			if ( id ) {
				$( '.spinner', $tableWideFat ).removeClass( 'is-active' );

				// Remove both the inline-editor and its hidden tr siblings.
				$( '#' + id )
					.siblings( 'tr.hidden' )
					.addBack()
					.remove();
				id = id.substr( id.lastIndexOf( '-' ) + 1 );

				// Show the post row and move focus back to the Quick Edit button.
				$( this.what + id )
					.show()
					.find( '.editinline' )
					.attr( 'aria-expanded', 'false' )
					.trigger( 'focus' );
			}

			return false;
		},

		/**
		 * Gets the ID for a the post that you want to quick edit from the row in the quick
		 * edit table.
		 *
		 * @since 2.7.0
		 *
		 * @memberof inlineEditTest
		 *
		 * @param {Object} o DOM row object to get the ID for.
		 * @return {string} The post ID extracted from the table row in the object.
		 */
		getId( o ) {
			const id = $( o ).closest( 'tr' ).attr( 'id' ),
				parts = id.split( '-' );
			return parts[ parts.length - 1 ];
		},
	};

	$( function () {
		if ( $( '.vrts_list_table_page' ).length ) {
			inlineEditTest.init();
		}
	} );
} )( jQuery, window.wp );
