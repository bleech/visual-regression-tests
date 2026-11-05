import { __ } from '@wordpress/i18n';
import { useEffect } from '@wordpress/element';
import { Button, SVG, Path } from '@wordpress/components';

// Same icon as assets/icons/settings.svg. Fill is set on the paths because
// the button CSS sets `fill: currentColor` on the svg.
const settingsIcon = (
	<SVG width="24" height="24" viewBox="0 0 24 24">
		<Path
			d="M9 17.5C9 18.8807 7.88072 20 6.5 20C5.11928 20 4 18.8807 4 17.5C4 16.1193 5.11928 15 6.5 15C7.88072 15 9 16.1193 9 17.5Z"
			fill="none"
			stroke="currentColor"
			strokeWidth="2"
			strokeLinejoin="round"
		/>
		<Path
			d="M20 6.5C20 5.11928 18.8807 4 17.5 4C16.1193 4 15 5.11928 15 6.5C15 7.88072 16.1193 9 17.5 9C18.8807 9 20 7.88072 20 6.5Z"
			fill="none"
			stroke="currentColor"
			strokeWidth="2"
			strokeLinejoin="round"
		/>
		<Path
			d="M6.5 15V4M17.5 9V20"
			fill="none"
			stroke="currentColor"
			strokeWidth="2"
			strokeLinejoin="round"
		/>
	</SVG>
);

const Settings = ( { test = {} } ) => {
	useEffect( () => {
		if ( test.id && test.settings ) {
			document
				.querySelector( 'vrts-test-settings' )
				?.rememberSettings( String( test.id ), test.settings );
		}
	}, [ test.id, test.settings ] );

	return (
		<div className="settings">
			<Button
				variant="secondary"
				icon={ settingsIcon }
				iconSize={ 16 }
				data-vrts-test-id={ test.id }
				data-a11y-dialog-show="vrts-modal-test-settings"
			>
				{ __( 'Test settings', 'visual-regression-tests' ) }
			</Button>
		</div>
	);
};

export default Settings;
