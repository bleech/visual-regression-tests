// import 'img-comparison-slider';
import Tabs from '../../assets/scripts/tabs';

class VrtsComparisons extends window.HTMLElement {
	constructor() {
		super();
		this.resolveElements();
		this.bindFunctions();
		this.bindEvents();
	}

	resolveElements() {
		this.$content = document.querySelector(
			'.vrts-test-run-page__content'
		);
		this.$fullscreen = this.querySelector( '[data-vrts-fullscreen-open]' );
		this.$control = this.querySelector(
			'[data-vrts-comparisons-slider-control]'
		);
	}

	bindFunctions() {
		this.handleFullscreenOpen = this.handleFullscreenOpen.bind( this );
		this.handleControlChange = this.handleControlChange.bind( this );
	}

	bindEvents() {
		this.$fullscreen.addEventListener( 'click', this.handleFullscreenOpen );
		this.$control.addEventListener( 'input', this.handleControlChange );

		document.addEventListener( 'fullscreenchange', () => {
			this.$content.setAttribute(
				'data-vrts-fullscreen',
				document.fullscreenElement === this.$content
			);
		} );
	}

	handleFullscreenOpen( e ) {
		e.preventDefault();
		this.$content.requestFullscreen();
	}

	handleControlChange( e ) {
		this.style.setProperty(
			'--vrts-comparisons-slider-control-position',
			`${ e.target.value }%`
		);
	}

	connectedCallback() {
		this.tabs = Tabs( this );
	}

	disconnectedCallback() {
		this.tabs?.();
		this.$fullscreen?.removeEventListener(
			'click',
			this.handleFullscreenOpen
		);
		this.$control?.removeEventListener( 'input', this.handleControlChange );
		document.removeEventListener( 'fullscreenchange' );
	}
}

window.customElements.define( 'vrts-comparisons', VrtsComparisons );
