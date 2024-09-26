class VrtsComparisons extends window.HTMLElement {
	constructor() {
		super();
		this.resolveElements();
		this.bindFunctions();
		this.bindEvents();
		this.init();
	}

	resolveElements() {
		this.$content = document.querySelector( '[data-vrts-fullscreen]' );
		this.$fullscreen = this.querySelector( '[data-vrts-fullscreen-open]' );
		this.$control = this.querySelector(
			'[data-vrts-comparisons-slider-control]'
		);
		this.$diffIndicator = this.querySelector(
			'[data-vrts-comparisons-diff-inidicator]'
		);
		this.$comparison = this.querySelector(
			'[data-vrts-comparisons-slot="comparison"] img'
		);
	}

	bindFunctions() {
		this.onFullscreenToggle = this.onFullscreenToggle.bind( this );
		this.onControlChange = this.onControlChange.bind( this );
		this.onFullScreenChange = this.onFullScreenChange.bind( this );
		this.onLoadComparison = this.onLoadComparison.bind( this );
	}

	bindEvents() {
		this.$fullscreen.addEventListener( 'click', this.onFullscreenToggle );
		this.$control.addEventListener( 'input', this.onControlChange );
		document.addEventListener(
			'fullscreenchange',
			this.onFullScreenChange
		);
	}

	init() {
		this.worker = new window.Worker(
			new URL( 'worker.js', import.meta.url )
		);
		this.worker.onmessage = this.onWorkerMessage.bind( this );
		if ( this.$comparison.complete ) {
			this.onLoadComparison();
		} else {
			this.$comparison.onload = this.onLoadComparison;
		}
	}

	onLoadComparison() {
		const imageData = this.createOffscreenCanvarImageData(
			this.$comparison
		);
		this.worker.postMessage( {
			action: 'analyzeImage',
			imageData,
		} );
	}

	createOffscreenCanvarImageData( image ) {
		const canvas = new window.OffscreenCanvas(
			image.naturalWidth,
			image.naturalHeight
		);
		const ctx = canvas.getContext( '2d' );
		ctx.drawImage( image, 0, 0 );
		return ctx.getImageData( 0, 0, canvas.width, canvas.height );
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

	onWorkerMessage( e ) {
		if ( e.data?.action === 'analyzedImage' ) {
			this.highlightPixels( e.data.coloredPixels );
		}
	}

	highlightPixels( pixels ) {
		const ctx = this.$diffIndicator.getContext( '2d' );
		ctx.clearRect( 0, 0, ctx.canvas.width, ctx.canvas.height );
		ctx.fillStyle = 'rgba(255, 0, 0, 0.5)';
		pixels.forEach( ( y ) => {
			ctx.fillRect(
				0,
				( y * ctx.canvas.height ) / this.$comparison.naturalHeight - 2,
				ctx.canvas.width,
				3
			);
		} );
	}

	connectedCallback() {}

	disconnectedCallback() {
		this.$fullscreen?.removeEventListener(
			'click',
			this.onFullscreenToggle
		);
		this.$control?.removeEventListener( 'input', this.onControlChange );
		document.removeEventListener(
			'fullscreenchange',
			this.onFullScreenChange
		);
		this.worker?.terminate();
		this.worker = null;
	}
}

window.customElements.define( 'vrts-comparisons', VrtsComparisons );
