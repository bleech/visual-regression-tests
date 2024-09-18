export default function Tabs( $element ) {
	const $tabs = $element.querySelectorAll( '[role="tab"]' );
	const $panels = $element.querySelectorAll( '[role="tabpanel"]' );

	const toggleContent = function () {
		if ( this.getAttribute( 'aria-selected' ) === 'false' ) {
			$panels.forEach( ( item ) => {
				item.setAttribute( 'hidden', true );
			} );

			$tabs.forEach( ( item ) => {
				item.setAttribute( 'aria-selected', false );
			} );

			this.setAttribute( 'aria-selected', 'true' );

			const currentTab = this.getAttribute( 'aria-controls' );
			const tabContent = document.getElementById( currentTab );
			tabContent.removeAttribute( 'hidden' );
		}
	};

	$tabs.forEach( ( item ) => {
		item.addEventListener( 'click', toggleContent );
	} );

	return () => {
		$tabs.forEach( ( item ) => {
			item.removeEventListener( 'click', toggleContent );
		} );
	};
}
