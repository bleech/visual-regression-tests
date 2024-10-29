import lottie from 'lottie-web/build/player/lottie_light';

class VrtsTestRunSuccess extends window.HTMLElement {
	constructor() {
		super();
		this.resolveElements();
	}

	resolveElements() {
		this.$lottiePlayer = this.querySelector( '[vrts-lottie-player]' );
	}

	connectedCallback() {
		this.lottieAnimation = lottie.loadAnimation( {
			path: `${ window.vrts_admin_vars.pluginUrl }/assets/animations/success-check.json`,
			container: this.$lottiePlayer,
			renderer: 'svg',
			loop: false,
			autoplay: true,
		} );
	}

	disconnectedCallback() {
		this.lottieAnimation.destroy();
	}

	loaAnimation() {
		this.$lottiePlayer.play();
	}
}

window.customElements.define( 'vrts-test-run-success', VrtsTestRunSuccess );
