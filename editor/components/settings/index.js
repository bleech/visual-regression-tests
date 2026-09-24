import { __, sprintf } from '@wordpress/i18n';
import { Icon, SelectControl, TextareaControl } from '@wordpress/components';
import { useState } from '@wordpress/element';
import { info as infoIcon } from '@wordpress/icons';
import { dispatch, select } from '@wordpress/data';

const Settings = ( { test = {} } ) => {
	const [ testState, setTestState ] = useState( {
		hide_css_selectors: '',
		...test,
		alert_threshold: String( test.meta?.alert_threshold ?? '' ),
	} );
	const alertThresholdOptions =
		window.vrts_editor_vars?.alert_threshold_options ?? [];

	const editVrts = ( edits ) => {
		const current =
			select( 'core/editor' ).getEditedPostAttribute( 'vrts' ) || {};
		return dispatch( 'core/editor' ).editPost( {
			vrts: { ...current, ...edits },
		} );
	};

	const updateTest = ( value ) => {
		const updatedTest = { ...testState, hide_css_selectors: value };
		test.hide_css_selectors = value;
		setTestState( updatedTest );
		return editVrts( { hide_css_selectors: value } );
	};

	const updateAlertThreshold = ( value ) => {
		setTestState( { ...testState, alert_threshold: value } );
		return editVrts( { alert_threshold: value } );
	};

	return (
		<>
			<div className="settings">
				<p className="settings-title">
					{ __(
						'Hide elements from VRTs',
						'visual-regression-tests'
					) }
					<span className="vrts-tooltip">
						<span className="vrts-tooltip-icon">
							<Icon icon={ infoIcon } size="20" />
						</span>
						<span className="vrts-tooltip-content">
							<span
								className="vrts-tooltip-content-inner"
								dangerouslySetInnerHTML={ {
									__html: sprintf(
										/* translators: %1$s, %2$s: link wrapper. */
										__(
											'Exclude elements on this page: Add %1$sCSS selectors%2$s (as comma separated list) to exclude elements from VRTs when a new snapshot gets created.',
											'visual-regression-tests'
										),
										'<a href="https://developer.mozilla.org/en-US/docs/Web/CSS/CSS_Selectors" target="_blank">',
										'</a>'
									),
								} }
							></span>
						</span>
					</span>
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
				/>
			</div>

			<div className="settings">
				<p className="settings-title">
					{ __( 'Alert threshold', 'visual-regression-tests' ) }
					<span className="vrts-tooltip">
						<span className="vrts-tooltip-icon">
							<Icon icon={ infoIcon } size="20" />
						</span>
						<span className="vrts-tooltip-content">
							<span className="vrts-tooltip-content-inner">
								{ __(
									'Only alert when the most-changed screen of the page differs by more than this.',
									'visual-regression-tests'
								) }
							</span>
						</span>
					</span>
				</p>

				<SelectControl
					label={ __( 'Alert threshold', 'visual-regression-tests' ) }
					hideLabelFromVision
					value={ testState.alert_threshold }
					options={ alertThresholdOptions }
					onChange={ updateAlertThreshold }
				/>
			</div>
		</>
	);
};

export default Settings;
