import { __ } from '@wordpress/i18n';
import { useMemo, useState } from '@wordpress/element';

const Screenshot = ( { url = '', finishDate = '', placeholderUrl = '' } ) => {
	const [ datetime, setDatetime ] = useState( '' );
	useMemo( () => {
		if ( finishDate && finishDate.length ) {
			const date = new Date( finishDate );
			setDatetime(
				date.toLocaleDateString() +
					' at ' +
					date.toLocaleTimeString( undefined, { timeStyle: 'short' } )
			);
		}
	}, [ finishDate ] );
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
					{ datetime && (
						<p>
							{ __(
								'Snapshot created on',
								'visual-regression-tests'
							) }{ ' ' }
							{ datetime }
						</p>
					) }
					{ ! datetime && (
						<p>
							{ __(
								'Snapshot: in progress',
								'visual-regression-tests'
							) }{ ' ' }
							{ datetime }
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
