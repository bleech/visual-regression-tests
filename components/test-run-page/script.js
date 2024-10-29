class VrtsTestRunPage extends window.HTMLElement {
	constructor() {
		super();
		this.bindFunctions();
		this.bindEvents();
		this.isScrolling = false;
	}

	bindFunctions() {
		this.onScroll = this.onScroll.bind( this );
		this.setOffset = this.setOffset.bind( this );
	}

	bindEvents() {
		document.addEventListener( 'scroll', this.onScroll );
	}

	onScroll() {
		if ( ! this.isScrolling ) {
			this.isScrolling = true;
			window.requestAnimationFrame( this.setOffset );
		}
	}

	setOffset() {
		const offset = Math.max( 0, this.offset - window.scrollY );

		this.style.setProperty(
			'--vrts-test-run-page-offset',
			`${ offset }px`
		);

		this.isScrolling = false;
	}

	connectedCallback() {
		const rect = this.getBoundingClientRect();
		const adminBarHeight =
			document.getElementById( 'wpadminbar' ).offsetHeight;
		this.offset = rect.top + window.scrollY - adminBarHeight;

		this.setOffset();
	}

	disconnectedCallback() {
		document.removeEventListener( 'scroll', this.onScroll );
	}
}

window.customElements.define( 'vrts-test-run-page', VrtsTestRunPage );
