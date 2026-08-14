class VrtsComparisons extends window.HTMLElement {
	constructor() {
		super();
		this.changeRegions = [];
		this.jumpDismissed = false;
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
		this.$base = this.querySelector(
			'[data-vrts-comparisons-slot="base"] img'
		);
		this.$header = this.querySelector( '.vrts-comparisons__header' );
		this.$jump = this.querySelector( '[data-vrts-comparisons-jump]' );
	}

	bindFunctions() {
		this.onFullscreenToggle = this.onFullscreenToggle.bind( this );
		this.onControlChange = this.onControlChange.bind( this );
		this.onFullScreenChange = this.onFullScreenChange.bind( this );
		this.onLoadComparison = this.onLoadComparison.bind( this );
		this.onJumpClick = this.onJumpClick.bind( this );
		this.onScroll = this.onScroll.bind( this );
	}

	bindEvents() {
		this.$fullscreen.addEventListener( 'click', this.onFullscreenToggle );
		this.$control.addEventListener( 'input', this.onControlChange );
		this.$jump?.addEventListener( 'click', this.onJumpClick );
		// Capture phase also catches scrolls of the fullscreen container.
		document.addEventListener( 'scroll', this.onScroll, {
			capture: true,
			passive: true,
		} );
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

	exitFullscreen() {
		if ( document.exitFullscreen ) {
			document.exitFullscreen();
		} else if ( document.webkitExitFullscreen ) {
			document.webkitExitFullscreen();
		} else if ( document.msExitFullscreen ) {
			document.msExitFullscreen();
		}
	}

	getFullscreenElement() {
		return (
			document.fullscreenElement ||
			document.webkitFullscreenElement ||
			document.msFullscreenElement
		);
	}

	onFullscreenToggle( e ) {
		e.preventDefault();

		if ( this.getFullscreenElement() === this.$content ) {
			this.exitFullscreen();
		} else {
			this.requestFullscreen( this.$content );
		}
	}

	onFullScreenChange() {
		this.$content.setAttribute(
			'data-vrts-fullscreen',
			this.getFullscreenElement() === this.$content
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
			this.setChangeRegions( e.data.coloredPixels );
		}
	}

	setChangeRegions( pixels ) {
		const regionGap = 100;
		const rows = [ ...pixels ].sort( ( a, b ) => a - b );

		this.changeRegions = rows.reduce( ( regions, y ) => {
			const last = regions[ regions.length - 1 ];
			if ( last && y - last.end <= regionGap ) {
				last.end = y;
			} else {
				regions.push( { start: y, end: y } );
			}
			return regions;
		}, [] );

		this.updateJumpState();
	}

	getJumpMetrics() {
		const $image =
			this.$comparison.clientHeight > 0 ? this.$comparison : this.$base;

		if ( ! $image?.naturalHeight || ! $image.clientHeight ) {
			return null;
		}

		const $fullscreen = this.getFullscreenElement();
		const adminBarHeight = $fullscreen
			? 0
			: document.getElementById( 'wpadminbar' )?.offsetHeight || 0;

		return {
			$fullscreen,
			scale: $image.clientHeight / $image.naturalHeight,
			imageTop: $image.getBoundingClientRect().top,
			topOffset:
				adminBarHeight + 62 + ( this.$header?.offsetHeight || 42 ) + 24,
			viewportBottom: window.innerHeight,
		};
	}

	getNextRegion( metrics ) {
		// The first region fully below the visible area.
		return this.changeRegions.find(
			( region ) =>
				metrics.imageTop + region.start * metrics.scale >
				metrics.viewportBottom
		);
	}

	isAnyRegionVisible( metrics ) {
		return this.changeRegions.some( ( region ) => {
			const top = metrics.imageTop + region.start * metrics.scale;
			const bottom = metrics.imageTop + region.end * metrics.scale;
			return bottom > metrics.topOffset && top < metrics.viewportBottom;
		} );
	}

	updateJumpState() {
		if ( ! this.$jump ) {
			return;
		}

		const metrics = this.changeRegions.length
			? this.getJumpMetrics()
			: null;

		if (
			metrics &&
			! this.jumpDismissed &&
			this.isAnyRegionVisible( metrics )
		) {
			this.jumpDismissed = true;
		}

		this.$jump.setAttribute(
			'data-vrts-visible',
			Boolean(
				! this.jumpDismissed && metrics && this.getNextRegion( metrics )
			)
		);
	}

	onScroll() {
		if ( this.scrollRaf ) {
			return;
		}

		this.scrollRaf = window.requestAnimationFrame( () => {
			this.scrollRaf = null;
			this.updateJumpState();
		} );
	}

	onJumpClick() {
		const metrics = this.getJumpMetrics();
		const region = metrics && this.getNextRegion( metrics );

		if ( region ) {
			this.scrollToRegion( region, metrics );
		}
	}

	scrollToRegion( region, metrics ) {
		const scrollPosition = metrics.$fullscreen
			? metrics.$fullscreen.scrollTop
			: window.scrollY;
		// Land the change a third into the area below the sticky headers.
		const visibleHeight = metrics.viewportBottom - metrics.topOffset;
		const top =
			scrollPosition +
			metrics.imageTop +
			region.start * metrics.scale -
			metrics.topOffset -
			visibleHeight / 3;

		( metrics.$fullscreen || window ).scrollTo( {
			top: Math.max( top, 0 ),
			behavior: 'smooth',
		} );
	}

	highlightPixels( pixels ) {
		const ctx = this.$diffIndicator.getContext( '2d' );
		this.$diffIndicator.width = this.$comparison.naturalWidth;
		this.$diffIndicator.height = this.$comparison.naturalHeight;
		ctx.clearRect(
			0,
			0,
			this.$comparison.naturalWidth,
			this.$comparison.naturalHeight
		);
		ctx.fillStyle = '#cc1818';
		pixels.forEach( ( y ) => {
			ctx.fillRect( 0, y - 2, ctx.canvas.width, 3 );
		} );
	}

	connectedCallback() {}

	disconnectedCallback() {
		this.$fullscreen?.removeEventListener(
			'click',
			this.onFullscreenToggle
		);
		this.$control?.removeEventListener( 'input', this.onControlChange );
		this.$jump?.removeEventListener( 'click', this.onJumpClick );
		document.removeEventListener( 'scroll', this.onScroll, {
			capture: true,
		} );

		if ( this.scrollRaf ) {
			window.cancelAnimationFrame( this.scrollRaf );
			this.scrollRaf = null;
		}

		document.removeEventListener(
			'fullscreenchange',
			this.onFullScreenChange
		);
		this.worker?.terminate();
		this.worker = null;
	}
}

window.customElements.define( 'vrts-comparisons', VrtsComparisons );
