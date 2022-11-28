/* global MutationObserver */
import 'img-comparison-slider';

const isVrtsEditAlertPage = document.querySelector( '.vrts_edit_alert_page' );

if ( isVrtsEditAlertPage ) {
	document.addEventListener( 'DOMContentLoaded', function () {
		initEditPageNavigation();
		initImageComparisonSlider();
	} );

	window.addEventListener( 'resize', function () {
		initImageComparisonSlider();
	} );
}

function initEditPageNavigation() {
	const tabs = document.querySelectorAll( '[data-tab]' );
	const content = document.getElementsByClassName( 'active' );

	const toggleContent = function () {
		if ( ! this.classList.contains( 'active' ) ) {
			Array.from( content ).forEach( ( item ) => {
				item.classList.remove( 'active' );
			} );

			this.classList.add( 'active' );
			const currentTab = this.getAttribute( 'data-tab' ),
				tabContent = document.getElementById( currentTab );
			tabContent.classList.add( 'active' );
		}
	};

	Array.from( tabs ).forEach( ( item ) => {
		item.addEventListener( 'click', toggleContent );
	} );
}

function initImageComparisonSlider() {
	const imgComparisonSlider = document.querySelector(
		'img-comparison-slider'
	);
	const img1 = document.querySelector(
		'.img-comparison-slider .figure-before .figure-image'
	);
	const img2 = document.querySelector(
		'.img-comparison-slider .figure-after .figure-image'
	);

	// Wait for images to load and setHeight.
	if ( img1.getAttribute( 'data-src' ) ) {
		img1.addEventListener(
			'load',
			() => {
				// Set img2 after img1 has loaded.
				img2.src = img2.getAttribute( 'data-src' );
				img2.removeAttribute( 'data-src' );
			},
			{ once: true }
		);
		img1.src = img1.getAttribute( 'data-src' );
		img1.removeAttribute( 'data-src' );

		img2.addEventListener(
			'load',
			() => {
				delay( 750 ).then( () => setHeight() );
			},
			{ once: true }
		);
	} else {
		delay( 500 ).then( () => setHeight() );
	}

	// Observe the slider tab and setHeight when it is the active tab.
	const tab = document.querySelector( '#comparison' );
	const options = {
		attributes: true,
	};
	const observer = new MutationObserver( mutationObserverCallback );
	observer.observe( tab, options );

	// Set the height of the slider after mutation and if classList contains active.
	function mutationObserverCallback( mutationList ) {
		mutationList.forEach( ( mutation ) => {
			if (
				mutation.type === 'attributes' &&
				mutation.attributeName === 'class' &&
				mutation.target.classList.contains( 'active' )
			) {
				setHeight();
			}
		} );
	}

	// Helper function to set the height of the slider.
	function setHeight() {
		const heights = [ img1.clientHeight, img2.clientHeight ];
		const height = Math.max( ...heights );
		imgComparisonSlider.style.height = height + 'px';
	}

	// Helper function to delay a function.
	function delay( time ) {
		return new Promise( ( resolve ) => setTimeout( resolve, time ) );
	}
}
