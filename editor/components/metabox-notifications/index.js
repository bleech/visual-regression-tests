import { __, sprintf } from '@wordpress/i18n';
import { select } from '@wordpress/data';
import DOMPurify from 'dompurify';

const NotificationNewTestAdded = ( {} ) => {
	return (
		<>
			<div className="vrts-metabox-notice vrts-metabox-notice-is-success">
				<p>
					<strong>
						{ __(
							'You have added a new test',
							'visual-regression-tests'
						) }
					</strong>
				</p>
				<p
					dangerouslySetInnerHTML={ {
						__html: DOMPurify.sanitize(
							sprintf(
								/* translators: %s name of the page */
								__(
									'The Visual Regression Test for the page %s has been added!',
									'visual-regression-tests'
								),
								'<strong>' +
									select(
										'core/editor'
									).getEditedPostAttribute( 'title' ) +
									'</strong>'
							)
						),
					} }
				></p>
			</div>
		</>
	);
};

const NotificationUnlockMoreTests = ( {
	upgradeUrl = '',
	remainingTests = 0,
	totalTests = 0,
} ) => {
	return (
		<>
			<div className="vrts-metabox-notice vrts-metabox-notice-is-info">
				<p>
					<strong>
						{ __( 'Unlock more tests', 'visual-regression-tests' ) }
					</strong>
				</p>
				<p
					dangerouslySetInnerHTML={ {
						__html: DOMPurify.sanitize(
							sprintf(
								'%1$s %2$s',
								sprintf(
									/* translators: %1$s, %2$s: number of tests. */
									__(
										'Good work! You have added %1$s of %2$s available tests.',
										'visual-regression-tests'
									),
									totalTests - remainingTests,
									totalTests
								),
								sprintf(
									/* translators: %1$s, %2$s: link wrapper. */
									__(
										'Upgrade %1$shere%2$s to add more tests to your website!',
										'visual-regression-tests'
									),
									`<a href="${ upgradeUrl }" target="_blank">`,
									'</a>'
								)
							)
						),
					} }
				></p>
			</div>
		</>
	);
};

const NotificationUpgradeRequired = ( { upgradeUrl = '' } ) => {
	return (
		<>
			<div className="vrts-metabox-notice vrts-metabox-notice-is-error">
				<p>
					<strong>
						{ __(
							'Ready for an Upgrade?',
							'visual-regression-tests'
						) }
					</strong>
				</p>
				<p
					dangerouslySetInnerHTML={ {
						__html: DOMPurify.sanitize(
							sprintf(
								'%1$s <a href="%2$s" target="_blank" title="%3$s">%3$s</a>',
								__(
									'Looks like you need a bigger plan to add more tests.',
									'visual-regression-tests'
								),
								upgradeUrl, //admin_url( 'admin.php?page=vrts-upgrade' )
								__( 'Upgrade here!', 'visual-regression-tests' )
							)
						),
					} }
				></p>
			</div>
		</>
	);
};

const NotificationConnectionFailed = ( { pluginUrl = '' } ) => {
	return (
		<>
			<div className="vrts-metabox-notice vrts-metabox-notice-is-error">
				<p>
					<strong>
						{ __( 'Connection failed', 'visual-regression-tests' ) }
					</strong>
				</p>
				<p>
					{ __(
						'Something went wrong while trying to connect to the external service.',
						'visual-regression-tests'
					) }
				</p>
				<p
					dangerouslySetInnerHTML={ {
						__html: DOMPurify.sanitize(
							sprintf(
								'<a href="%1$s" title="%2$s">%2$s</a>',
								pluginUrl, //admin_url( 'admin.php?page=vrts' )
								__(
									'Go to plugin page',
									'visual-regression-tests'
								)
							)
						),
					} }
				></p>
			</div>
		</>
	);
};

export {
	NotificationNewTestAdded,
	NotificationUnlockMoreTests,
	NotificationUpgradeRequired,
	NotificationConnectionFailed,
};
