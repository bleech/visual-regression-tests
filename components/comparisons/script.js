import 'img-comparison-slider';
import Tabs from '../../assets/scripts/tabs';

class VrtsComparisons extends window.HTMLElement {
	constructor() {
		super();
		this.tabs = null;
	}

	connectedCallback() {
		this.tabs = Tabs( this );
	}

	disconnectedCallback() {
		this.tabs?.();
	}
}

window.customElements.define( 'vrts-comparisons', VrtsComparisons );
