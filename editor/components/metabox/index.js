// Native
import { ToggleControl } from '@wordpress/components';
import { useState, useEffect } from '@wordpress/element';
import { select, subscribe } from '@wordpress/data';
import { __ } from '@wordpress/i18n';
import DOMPurify from 'dompurify';

// Custom
import Screenshot from 'editor/components/screenshot';
import {
	NotificationNewTestAdded,
	NotificationUnlockMoreTests,
	NotificationUpgradeRequired,
	NotificationConnectionFailed,
} from 'editor/components/metabox-notifications';
import Settings from 'editor/components/settings';

import apiFetch from '@wordpress/api-fetch';

const Metabox = () => {
	const upgradeUrl = window.vrts_editor_vars.upgrade_url;
	const pluginUrl = window.vrts_editor_vars.plugin_url;
	const testingStatusInstructions =
		window.vrts_editor_vars.testing_status_instructions;
	const placeholderImageDataUrl =
		window.vrts_editor_vars.placeholder_image_data_url;

	const postId = select( 'core/editor' ).getCurrentPostId();
	const [ postStatus, setPostStatus ] = useState(
		select( 'core/editor' ).getEditedPostAttribute( 'status' )
	);

	const [ loading, setLoading ] = useState( true );
	const [ disabled, setDisabled ] = useState( true );
	const [ test, setTest ] = useState( {} );
	const [ credits, setCredits ] = useState( {} );
	const [ newTest, setNewTest ] = useState( false );

	async function createTest() {
		setLoading( true );
		try {
			const response = await apiFetch( {
				path: `/vrts/v1/tests/post/${ postId }`,
				method: 'POST',
			} );
			setTest( response );
			if ( test.service_test_id ) {
				setCredits( {
					...credits,
					remaining_tests: credits.remaining_tests - 1,
				} );
			}
		} catch ( error ) {
			console.log( error ); // eslint-disable-line no-console
		}
		setLoading( false );
		setNewTest( true );
	}

	async function deleteTest() {
		setLoading( true );
		try {
			const previousServiceTestId = test.service_test_id;
			const response = await apiFetch( {
				path: `/vrts/v1/tests/post/${ postId }`,
				method: 'DELETE',
			} );
			setTest( response || {} );
			if ( previousServiceTestId ) {
				setCredits( {
					...credits,
					remaining_tests: credits.remaining_tests + 1,
				} );
			}
		} catch ( error ) {
			console.log( error ); // eslint-disable-line no-console
		}
		setLoading( false );
	}

	useEffect( () => {
		if ( 'auto-draft' === postStatus ) {
			setDisabled( true );
		} else {
			setDisabled( false );
		}
	}, [ postStatus ] );

	useEffect( () => {
		setLoading( true );
		async function fetchAndSetTest() {
			try {
				const response = await apiFetch( {
					path: `/vrts/v1/tests/post/${ postId }`,
				} );
				setTest( response );
			} catch ( error ) {
				console.log( error ); // eslint-disable-line no-console
			}
			setLoading( false );
		}
		fetchAndSetTest();
	}, [ postStatus ] );

	useEffect( () => {
		async function fetchAndSetCredits() {
			try {
				const response = await apiFetch( {
					path: `/vrts/v1/tests`,
				} );
				setCredits( response );
			} catch ( error ) {
				console.log( error ); // eslint-disable-line no-console
			}
		}
		setLoading( true );
		fetchAndSetCredits();
	}, [ postStatus ] );

	let wasSavingPost = select( 'core/editor' ).isSavingPost();

	useEffect( () => {
		subscribe( () => {
			const newPostStatus =
				select( 'core/editor' ).getEditedPostAttribute( 'status' );
			const isSavingPost = select( 'core/editor' ).isSavingPost();
			if (
				wasSavingPost &&
				! isSavingPost &&
				newPostStatus !== postStatus
			) {
				setPostStatus( newPostStatus );
			}
			wasSavingPost = isSavingPost;
		} );
	}, [] );

	let metaboxNotification = null;
	if ( true === newTest ) {
		metaboxNotification = <NotificationNewTestAdded />;
	} else if ( credits.remaining_tests === 1 ) {
		metaboxNotification = (
			<NotificationUnlockMoreTests
				upgradeUrl={ upgradeUrl }
				remainingTests={ credits.remaining_tests }
				totalTests={ credits.total_tests }
			/>
		);
	} else if ( credits.remaining_tests === 0 ) {
		metaboxNotification = (
			<NotificationUpgradeRequired upgradeUrl={ upgradeUrl } />
		);
	}

	const isConnected = window.vrts_editor_vars.is_connected;
	if ( ! isConnected ) {
		return <NotificationConnectionFailed pluginUrl={ pluginUrl } />;
	}

	let testingStatusText = __( 'Running', 'visual-regression-tests' );
	if ( test.current_alert_id ) {
		testingStatusText = __( 'Paused', 'visual-regression-tests' );
	} else if ( ! test.status ) {
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
				checked={ test.id ? true : false }
				onChange={ test.id ? deleteTest : createTest }
				disabled={
					disabled ||
					loading ||
					( credits.remaining_tests === 0 && ! test.id )
				}
			/>
			{ metaboxNotification }
			{ test.id && (
				<>
					<div className="testing-status-wrapper">
						<p className="testing-status">
							<span>
								{ __( 'Status', 'visual-regression-tests' ) }
							</span>
							<strong>
								<span
									className={
										test.current_alert_id || test.status
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
			{ test.id && !! test.status && (
				<Screenshot
					url={ test.base_screenshot_url }
					placeholderUrl={ placeholderImageDataUrl }
					finishDate={ test.base_screenshot_date }
				/>
			) }
			{ test.id && (
				<>
					<Settings test={ test } setTest={ setTest } />
				</>
			) }
		</>
	);
};

export default Metabox;
