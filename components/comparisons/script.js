import Tabs from '../../assets/scripts/tabs';

class VrtsComparisons extends window.HTMLElement {
	constructor() {
		super();
		this.resolveElements();
		this.bindFunctions();
		this.bindEvents();
	}

	resolveElements() {
		this.$content = document.querySelector( '[data-vrts-fullscreen]' );
		this.$fullscreen = this.querySelector( '[data-vrts-fullscreen-open]' );
		this.$control = this.querySelector(
			'[data-vrts-comparisons-slider-control]'
		);
	}

	bindFunctions() {
		this.onFullscreenToggle = this.onFullscreenToggle.bind( this );
		this.onControlChange = this.onControlChange.bind( this );
		this.onFullScreenChange = this.onFullScreenChange.bind( this );
	}

	bindEvents() {
		this.$fullscreen.addEventListener( 'click', this.onFullscreenToggle );
		this.$control.addEventListener( 'input', this.onControlChange );
		document.addEventListener(
			'fullscreenchange',
			this.onFullScreenChange
		);
	}

	requestFullscreen( element ) {
		if ( element.requestFullscreen ) {
			element.requestFullscreen();
		} else if ( element.webkitRequestFullscreen ) {
			element.webkitRequestFullscreen();
		} else if ( element.msRequestFullscreen ) {
			element.msRequestFullscreen();
		}
	}

	onFullscreenToggle( e ) {
		e.preventDefault();

		if ( document.fullscreenElement === this.$content ) {
			document.exitFullscreen();
		} else {
			this.requestFullscreen( this.$content );
		}
	}

	onFullScreenChange() {
		this.$content.setAttribute(
			'data-vrts-fullscreen',
			document.fullscreenElement === this.$content
		);
	}

	onControlChange( e ) {
		this.style.setProperty(
			'--vrts-comparisons-slider-position',
			`${ e.target.value }%`
		);
	}

	connectedCallback() {
		this.tabs = Tabs( this );
	}

	disconnectedCallback() {
		this.tabs?.();
		this.$fullscreen?.removeEventListener( 'click', this.onFullscreenOpen );
		this.$control?.removeEventListener( 'input', this.onControlChange );
		document.removeEventListener(
			'fullscreenchange',
			this.onFullScreenChange
		);
	}
}

window.customElements.define( 'vrts-comparisons', VrtsComparisons );
