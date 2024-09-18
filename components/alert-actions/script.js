import Dropdown from '../../assets/scripts/dropdown';

class VrtsAlertActions extends window.HTMLElement {
	constructor() {
		super();
		this.dropdown = null;
	}

	connectedCallback() {
		this.dropdown = Dropdown( this );
	}

	disconnectedCallback() {
		this.dropdown?.();
	}
}

window.customElements.define( 'vrts-alert-actions', VrtsAlertActions );
