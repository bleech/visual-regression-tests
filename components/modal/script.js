import A11yDialog from 'a11y-dialog';

class VrtsModal extends window.HTMLElement {
	connectedCallback() {
		this.modal = new A11yDialog( this );
	}

	disconnectedCallback() {
		this.modal.destroy();
	}
}

window.customElements.define( 'vrts-modal', VrtsModal );
