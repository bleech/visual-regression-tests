class VrtsSettings extends window.HTMLElement {
	constructor() {
		super();
		this.resolveElements();
		this.bindFunctions();
		this.bindEvents();
	}

	resolveElements() {
		this.$proSettingsCheckboxes = this.querySelectorAll(
			'[data-a11y-dialog-show] input[type="checkbox"]'
		);
	}

	bindFunctions() {
		this.onCheckboxChange = this.onCheckboxChange.bind( this );
	}

	bindEvents() {
		this.$proSettingsCheckboxes?.forEach( ( item ) => {
			item.addEventListener( 'change', this.onCheckboxChange );
		} );
	}

	onCheckboxChange( e ) {
		e.preventDefault();
		e.currentTarget.checked = ! e.currentTarget.checked;
	}

	disconnectedCallback() {
		this.$proSettingsCheckboxes?.forEach( ( item ) => {
			item.removeEventListener( 'change', this.onCheckboxChange );
		} );
	}
}
window.customElements.define( 'vrts-settings', VrtsSettings );
