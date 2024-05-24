// Native
import { Flex, Icon, ToggleControl } from '@wordpress/components';
import { useState, useEffect } from '@wordpress/element';
import { select, subscribe } from '@wordpress/data';
import { info as infoIcon } from '@wordpress/icons';
import { __, sprintf } from '@wordpress/i18n';

// Custom
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
	const testStatus = window.vrts_editor_vars.test_status;
	const screenshot = window.vrts_editor_vars.screenshot;

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
			setNewTest( false );
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

	return (
		<>
			<Flex gap={ 3 } style={ { marginBottom: 12 } }>
				<ToggleControl
					label={ __( 'Add to VRTs', 'visual-regression-tests' ) }
					checked={ test.id ? true : false }
					onChange={ test.id ? deleteTest : createTest }
					disabled={
						disabled ||
						loading ||
						( credits.remaining_tests === 0 && ! test.id )
					}
					__nextHasNoMarginBottom={ true }
				/>
				<span className="vrts-tooltip">
					<span className="vrts-tooltip-icon">
						<Icon icon={ infoIcon } size="20" />
					</span>
					<span className="vrts-tooltip-content">
						<span
							className="vrts-tooltip-content-inner"
							dangerouslySetInnerHTML={ {
								__html: sprintf(
									// translators: %1$s, %2$s: link wrapper.
									__(
										'Add this page to your Visual Regression Tests for consistent checks to ensure no visual changes go unnoticed. Explore the %1$sTests page%2$s in the VRTs plugin for an overview of all tests and their status.',
										'visual-regression-tests'
									),
									'<a href="' + pluginUrl + '">',
									'</a>'
								),
							} }
						></span>
					</span>
				</span>
			</Flex>
			{ metaboxNotification }
			{ test.id && (
				<>
					<div className="vrts-testing-status-wrapper">
						<p className="vrts-testing-status">
							<span>
								{ __(
									'Test Status',
									'visual-regression-tests'
								) }
							</span>
							<strong>
								<span
									className={ `vrts-testing-status--${ testStatus.class }` }
								>
									{ testStatus.text }
								</span>
							</strong>
						</p>
						<p
							className="vrts-testing-status-info"
							dangerouslySetInnerHTML={ {
								// This is safe because the content is sanitized in PHP.
								__html: testStatus.instructions,
							} }
						/>
					</div>
					<div className="vrts-testing-status-wrapper">
						<p className="vrts-testing-status">
							<span>
								{ __( 'Snapshot', 'visual-regression-tests' ) }
							</span>
							<span
								className="vrts-testing-status-info"
								dangerouslySetInnerHTML={ {
									// This is safe because the content is sanitized in PHP.
									__html: [ 'paused', 'waiting' ].includes(
										screenshot.status
									)
										? screenshot.text
										: screenshot.instructions,
								} }
							/>
						</p>
						<figure
							className="figure"
							dangerouslySetInnerHTML={ {
								// This is safe because the content is sanitized in PHP.
								__html: screenshot.screenshot,
							} }
						/>
					</div>
				</>
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
