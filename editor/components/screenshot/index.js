import { __ } from '@wordpress/i18n';

const Screenshot = ( { url = '', finishDate = '', placeholderUrl = '' } ) => {
	return (
		<>
			<p className="figure-title">
				{ __( 'Snapshot', 'visual-regression-tests' ) }
			</p>
			<figure className="figure">
				{ url && (
					<a
						className="figure-link"
						href={ url }
						target="_blank"
						rel="noreferrer"
						title={ __(
							'View full snapshot image in new tab',
							'visual-regression-tests'
						) }
					>
						<img
							className="figure-image"
							src={ url === '' ? placeholderUrl : url }
							loading="lazy"
							alt={ __(
								'Visual Regression current state',
								'visual-regression-tests'
							) }
						/>
					</a>
				) }
				{ ! url && (
					<img
						className="figure-image"
						src={ placeholderUrl }
						alt={ __(
							'Visual Regression current state',
							'visual-regression-tests'
						) }
					/>
				) }

				<figcaption className="howto">
					{ finishDate && (
						<p>
							{ __(
								'Snapshot created on',
								'visual-regression-tests'
							) }{ ' ' }
							{ finishDate }
						</p>
					) }
					{ ! finishDate && (
						<p>
							{ __(
								'First Snapshot: in progress',
								'visual-regression-tests'
							) }{ ' ' }
							{ finishDate }
						</p>
					) }
					<p>
						{ __(
							'Snapshot gets auto-generated upon publishing or updating the page.',
							'visual-regression-tests'
						) }
					</p>
				</figcaption>
			</figure>
		</>
	);
};

export default Screenshot;
