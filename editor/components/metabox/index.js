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
	NotificationConnectionFailed,
	NotificationUrlsMismatch,
} from 'editor/components/metabox-notifications';

import apiFetch from '@wordpress/api-fetch';

const Metabox = () => {
	const hasPostAlert = window.vrts_editor_vars.has_post_alert;
	const targetScreenshotUrl = window.vrts_editor_vars.target_screenshot_url;
	const testStatus = window.vrts_editor_vars.test_status;
	const snapshotDate = window.vrts_editor_vars.snapshot_date;
	const testingStatusInstructions =
		window.vrts_editor_vars.testing_status_instructions;
	const placeholderImageDataUrl =
		window.vrts_editor_vars.placeholder_image_data_url;

	const [ remainingTests, setRemainingTests ] = useState(
		parseInt( window.vrts_editor_vars.remaining_tests )
	);
	const [ totalTests, setTotalTests ] = useState(
		parseInt( window.vrts_editor_vars.total_tests )
	);

	const upgradeUrl = window.vrts_editor_vars.upgrade_url;
	const pluginUrl = window.vrts_editor_vars.plugin_url;
	const isNewTest = window.vrts_editor_vars.is_new_test;

	const postMeta = select( 'core/editor' ).getEditedPostAttribute( 'meta' );
	const runTestsValue =
		postMeta !== undefined && postMeta._vrts_testing_status
			? postMeta._vrts_testing_status
			: false;
	const [ runTestsIsChecked, setRunTestsChecked ] = useState( runTestsValue );
	const runTestsOnChange = ( value ) => {
		setRunTestsChecked( function ( checkedValue ) {
			return ! checkedValue;
		} );

		dispatch( 'core/editor' ).editPost( {
			meta: {
				_vrts_testing_status: value,
			},
		} );
	};

	const { isSavingPost } = select( 'core/editor' );
	const [ showResults, setShowResults ] = useState( runTestsValue );
	const [ isSavingProcess, setSavingProcess ] = useState( false );
	const updatePost = async function () {
		const postId = select( 'core/editor' ).getCurrentPostId();
		const testId = await getTestId( postId );

		if ( true === runTestsIsChecked && null === testId ) {
			window.vrts_editor_vars.is_new_test = true;
			window.vrts_editor_vars.has_post_alert = false;
			window.vrts_editor_vars.test_status = true;
		} else {
			window.vrts_editor_vars.is_new_test = false;
		}

		setTimeout( () => {
			setRemainingAndTotalTestsFromApi();
		}, 2000 ); // Set a delay of 2 seconds, to be sure that the data from api is updated.

		setShowResults( runTestsIsChecked );
	};

	subscribe( () => {
		if ( isSavingPost() ) {
			setSavingProcess( true );
		} else {
			setSavingProcess( false );
		}
	} );

	useEffect( () => {
		if ( isSavingProcess ) {
			updatePost();
		}
	}, [ isSavingProcess ] );

	const getTestId = async ( postId ) => {
		try {
			const responseTestId = await apiFetch( {
				path: `/vrts/v1/tests/post/${ postId }`,
			} );
			const testId = await responseTestId.test_id;
			return testId;
		} catch ( error ) {
			throw error;
		}
	};

	const setRemainingAndTotalTestsFromApi = async () => {
		const responseRemainingTotalTests = await apiFetch( {
			path: `/vrts/v1/tests/`,
		} ).catch( ( error ) => {
			throw error;
		} );
		const restApiRemainingTests =
			await responseRemainingTotalTests.remaining_tests;
		const restApiTotalTests = await responseRemainingTotalTests.total_tests;
		if ( restApiRemainingTests !== null ) {
			setRemainingTests( parseInt( restApiRemainingTests ) );
		}
		if ( restApiTotalTests !== null ) {
			setTotalTests( parseInt( restApiTotalTests ) );
		}
	};

	let metaboxNotification = null;
	if ( true === isNewTest ) {
		metaboxNotification = <NotificationNewTestAdded />;
	} else if ( parseInt( remainingTests ) === 1 ) {
		metaboxNotification = (
			<NotificationUnlockMoreTests
				upgradeUrl={ upgradeUrl }
				remainingTests={ remainingTests }
				totalTests={ totalTests }
			/>
		);
	} else if ( parseInt( remainingTests ) === 0 ) {
		metaboxNotification = (
			<NotificationUpgradeRequired upgradeUrl={ upgradeUrl } />
		);
	}

	const isConnected = window.vrts_editor_vars.is_connected;
	const urlsMismatch = window.vrts_editor_vars.urls_mismatch;
	if ( urlsMismatch ) {
		return <NotificationUrlsMismatch />;
	}
	if ( ! isConnected ) {
		return <NotificationConnectionFailed pluginUrl={ pluginUrl } />;
	}

	let testingStatusText = __( 'Running', 'visual-regression-tests' );
	if ( hasPostAlert ) {
		testingStatusText = __( 'Paused', 'visual-regression-tests' );
	} else if ( ! testStatus ) {
		testingStatusText = __( 'Disabled', 'visual-regression-tests' );
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
				disabled={
					parseInt( remainingTests ) === 0 && ! runTestsIsChecked
				}
			/>

			{ metaboxNotification }

			{ showResults && (
				<>
					<div className="testing-status-wrapper">
						<p className="testing-status">
							<span>
								{ __( 'Status', 'visual-regression-tests' ) }
							</span>
							<strong>
								<span
									className={
										hasPostAlert || testStatus
											? 'testing-status--running'
											: 'testing-status--paused'
									}
								>
									{ testingStatusText }
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
			{ showResults && (
				<Screenshot
					url={ targetScreenshotUrl }
					placeholderUrl={ placeholderImageDataUrl }
					finishDate={ snapshotDate }
				/>
			) }
		</>
	);
};

export default Metabox;
