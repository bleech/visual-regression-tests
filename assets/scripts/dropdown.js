export default function Dropdown( $dropdown ) {
	const $dropdownOpen = $dropdown.querySelector(
		'[data-vrts-dropdown-open]'
	);

	const toggleContent = ( e ) => {
		const $el = e.currentTarget;
		const controls = $el.getAttribute( 'aria-controls' );
		const $controls = document.getElementById( controls );
		const isExpanded = $el.getAttribute( 'aria-expanded' ) === 'true';

		$el.setAttribute( 'aria-expanded', ! isExpanded );
		$controls.setAttribute( 'aria-hidden', isExpanded );
	};

	const closeDropdown = ( e ) => {
		if (
			$dropdown &&
			$dropdown !== e.target &&
			! $dropdown.contains( e.target )
		) {
			$dropdownOpen.setAttribute( 'aria-expanded', false );
			document
				.getElementById( $dropdownOpen.getAttribute( 'aria-controls' ) )
				.setAttribute( 'aria-hidden', true );
		}
	};

	$dropdownOpen?.addEventListener( 'click', toggleContent );

	// Close dropdown when clicking outside of it.
	document.addEventListener( 'click', closeDropdown );

	return () => {
		$dropdownOpen?.removeEventListener( 'click', toggleContent );
		document.removeEventListener( 'click', closeDropdown );
	};
}
