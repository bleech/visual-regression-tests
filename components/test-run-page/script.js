import 'img-comparison-slider';

document.addEventListener( 'DOMContentLoaded', function () {
	const $comparisons = document.querySelector( '.vrts-comparisons' );
	const $actions = document.querySelector( '.vrts-alert-actions' );
	const $actionOpen = $actions.querySelector( '[data-vrts-action-open]' );

	if ( $comparisons ) {
		initComparisonsTabs( $comparisons );
	}

	$actionOpen?.addEventListener( 'click', openAlertActions );

	// Close actions dropdown when clicking outside of it.
	document.addEventListener( 'click', ( e ) => {
		if (
			$actions &&
			$actions !== e.target &&
			! $actions.contains( e.target )
		) {
			$actionOpen.setAttribute( 'aria-expanded', false );
			document
				.getElementById( $actionOpen.getAttribute( 'aria-controls' ) )
				.setAttribute( 'aria-hidden', true );
		}
	} );

	const urlParams = new URLSearchParams( window.location.search );
	const currentAlertId = urlParams.get( 'alert_id' );

	if ( currentAlertId ) {
		const $alert = document.getElementById(
			`vrts-alert-${ currentAlertId }`
		);

		setTimeout( () => {
			$alert.setAttribute( 'data-state', 'read' );
		}, 1000 );
	}
} );

function openAlertActions( e ) {
	const $el = e.currentTarget;
	const controls = $el.getAttribute( 'aria-controls' );
	const $controls = document.getElementById( controls );
	const isExpanded = $el.getAttribute( 'aria-expanded' ) === 'true';

	$el.setAttribute( 'aria-expanded', ! isExpanded );
	$controls.setAttribute( 'aria-hidden', isExpanded );
}

function initComparisonsTabs( $comparisons ) {
	const $tabs = $comparisons.querySelectorAll( '[role="tab"]' );
	const $panels = $comparisons.querySelectorAll( '[role="tabpanel"]' );

	const toggleContent = function () {
		if ( this.getAttribute( 'aria-selected' ) === 'false' ) {
			$panels.forEach( ( item ) => {
				item.setAttribute( 'hidden', true );
			} );

			$tabs.forEach( ( item ) => {
				item.setAttribute( 'aria-selected', false );
			} );

			this.setAttribute( 'aria-selected', 'true' );

			const currentTab = this.getAttribute( 'aria-controls' );
			const tabContent = document.getElementById( currentTab );
			tabContent.removeAttribute( 'hidden' );
		}
	};

	$tabs.forEach( ( item ) => {
		item.addEventListener( 'click', toggleContent );
	} );
}
