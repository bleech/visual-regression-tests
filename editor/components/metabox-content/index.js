// Native
import { ToggleControl } from '@wordpress/components';
import { useState, useEffect } from '@wordpress/element';
import { select, dispatch, subscribe } from '@wordpress/data';
import { __ } from '@wordpress/i18n';
import DOMPurify from 'dompurify';

// Custom
import Screenshot from 'editor/components/screenshot';
import {
	NotificationNewTestAdded,
	NotificationUnlockMoreTests,
	NotificationUpgradeRequired,
} from 'editor/components/metabox-notifications';

const MetaboxContent = () => {
	const hasPostAlert = window.vrts_editor_vars.has_post_alert;
	const postMetaKeyTestStatus = window.vrts_editor_vars.field_test_status_key;
	const targetScreenshotUrl = window.vrts_editor_vars.target_screenshot_url;
	const snapshotDate = window.vrts_editor_vars.snapshot_date;
	const testingStatusInstructions =
		window.vrts_editor_vars.testing_status_instructions;
	const placeholderImageDataUrl =
		window.vrts_editor_vars.placeholder_image_data_url;

	const remainingTests = parseInt( window.vrts_editor_vars.remaining_tests );
	const totalTests = parseInt( window.vrts_editor_vars.total_tests );
	const upgradeUrl = window.vrts_editor_vars.upgrade_url;
	const isNewTest = window.vrts_editor_vars.is_new_test;

	const postMeta = select( 'core/editor' ).getEditedPostAttribute( 'meta' );
	const runTestsValue =
		postMeta !== undefined && postMeta[ postMetaKeyTestStatus ]
			? postMeta[ postMetaKeyTestStatus ]
			: false;
	const [ runTestsIsChecked, setRunTestsChecked ] = useState( runTestsValue );
	const runTestsOnChange = ( value ) => {
		setRunTestsChecked( function ( checkedValue ) {
			return ! checkedValue;
		} );

		if ( value === true ) {
			window.vrts_editor_vars.remaining_tests--;
		} else {
			window.vrts_editor_vars.remaining_tests++;
		}

		dispatch( 'core/editor' ).editPost( {
			meta: {
				[ postMetaKeyTestStatus ]: value,
			},
		} );
	};

	const [ isSavingProcess, setSavingProcess ] = useState( false );
	const { isSavingPost } = select( 'core/editor' );
	subscribe( () => {
		if ( isSavingPost() ) {
			setSavingProcess( true );
		} else {
			setSavingProcess( false );
		}
	} );

	useEffect( () => {
		if ( isSavingProcess ) {
			const showAddedNewTestNotification = runTestsIsChecked;

			if ( true === showAddedNewTestNotification ) {
				window.vrts_editor_vars.is_new_test = true;
			} else {
				window.vrts_editor_vars.is_new_test = false;
			}
		}
	}, [ isSavingProcess ] );

	let metaboxNotification = null;
	if ( true === isNewTest ) {
		metaboxNotification = <NotificationNewTestAdded />;
	} else if ( remainingTests > 0 ) {
		metaboxNotification = (
			<NotificationUnlockMoreTests
				upgradeUrl={ upgradeUrl }
				remainingTests={ remainingTests }
				totalTests={ totalTests }
			/>
		);
	} else if ( remainingTests === 0 ) {
		metaboxNotification = (
			<NotificationUpgradeRequired upgradeUrl={ upgradeUrl } />
		);
	}

	return (
		<>
			<ToggleControl
				label={ __( 'Run Tests', 'visual-regression-tests' ) }
				help={ __(
					'Activate tests to get alerted about visual differences in comparison to the snapshot.',
					'visual-regression-tests'
				) }
				checked={ runTestsIsChecked }
				onChange={ runTestsOnChange }
				disabled={ remainingTests === 0 && runTestsIsChecked === false }
			/>

			{ metaboxNotification }

			{ runTestsIsChecked && (
				<>
					<div className="testing-status-wrapper">
						<p className="testing-status">
							<span>
								{ __( 'Status', 'visual-regression-tests' ) }
							</span>
							<strong>
								<span
									className={
										! hasPostAlert
											? 'testing-status--running'
											: 'testing-status--paused'
									}
								>
									{ ! hasPostAlert
										? __(
												'Running',
												'visual-regression-tests'
										  )
										: __(
												'Paused',
												'visual-regression-tests'
										  ) }
								</span>
							</strong>
						</p>
						<p
							className="howto"
							dangerouslySetInnerHTML={ {
								__html: DOMPurify.sanitize(
									testingStatusInstructions
								),
							} }
						></p>
					</div>
				</>
			) }
			{ runTestsIsChecked && (
				<Screenshot
					url={ targetScreenshotUrl }
					placeholderUrl={ placeholderImageDataUrl }
					finishDate={ snapshotDate }
				/>
			) }
		</>
	);
};

export default MetaboxContent;
