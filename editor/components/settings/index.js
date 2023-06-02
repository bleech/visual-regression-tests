import { __, sprintf } from '@wordpress/i18n';
import { TextareaControl } from '@wordpress/components';
import { useCallback, useState } from '@wordpress/element';
import { debounce } from '@wordpress/compose';
import apiFetch from '@wordpress/api-fetch';
import DOMPurify from 'dompurify';

const Settings = ( { test = {}, setTest } ) => {
	const [ testState, setTestState ] = useState( {
		hide_css_selectors: '',
		...test,
	} );

	const updateTest = ( value ) => {
		const updatedTest = { ...testState, hide_css_selectors: value };
		test.hide_css_selectors = value;
		setTestState( updatedTest );
		debouncedSaveTestValue();
	};

	async function saveTestValue() {
		const { hide_css_selectors: hideCssSelectors } = test;
		try {
			const response = await apiFetch( {
				path: `/vrts/v1/tests/post/${ test.post_id }`,
				method: 'PUT',
				data: {
					test_id: test.id,
					hide_css_selectors: hideCssSelectors,
				},
			} );
			setTest( response );
		} catch ( error ) {
			console.log( error ); // eslint-disable-line no-console
		}
	}

	const debouncedSaveTestValue = useCallback(
		debounce( saveTestValue, 250 ),
		[]
	);

	return (
		<>
			<div className="settings">
				<p className="settings-title">
					{ __( 'Settings', 'visual-regression-tests' ) }
				</p>

				<TextareaControl
					placeholder={ __(
						'e.g.: .lottie, #ads',
						'visual-regression-tests'
					) }
					value={ testState.hide_css_selectors }
					onChange={ ( value ) => {
						updateTest( value );
					} }
					label={
						<>
							{ __(
								'Exclude elements on this page: ',
								'visual-regression-tests'
							) }
							<span
								dangerouslySetInnerHTML={ {
									__html: DOMPurify.sanitize(
										sprintf(
											/* translators: %s name of the page */
											__(
												'Add %1$sCSS selectors%2$s (as comma separated list) to exclude elements from VRTs when a new snapshot gets created.',
												'visual-regression-tests'
											),
											'<a href="https://developer.mozilla.org/en-US/docs/Web/CSS/CSS_Selectors" target="_blank">',
											'</a>'
										),
										{ ADD_ATTR: [ 'target' ] }
									),
								} }
							/>
						</>
					}
				/>
			</div>
		</>
	);
};

export default Settings;
