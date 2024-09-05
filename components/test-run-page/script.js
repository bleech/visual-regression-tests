import 'img-comparison-slider';

const $comparisons = document.querySelector( '.vrts-comparisons ' );

document.addEventListener( 'DOMContentLoaded', function () {
	if ( $comparisons ) {
		initComparisonsTabs();
	}

	const urlParams = new URLSearchParams( window.location.search );
	const currentAlertId = urlParams.get( 'alert_id' );

	if ( currentAlertId ) {
		const $sidebar = document.querySelector(
			'.vrts-test-run-page__sidebar'
		);
		const $alert = document.getElementById(
			`vrts-alert-${ currentAlertId }`
		);

		if ( $alert ) {
			$sidebar.scrollTo( {
				behavior: 'smooth',
				left: 0,
				top: $alert.offsetTop - 100,
			} );
		}
	}
} );

function initComparisonsTabs() {
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
