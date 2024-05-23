import { resolveSelect, dispatch } from '@wordpress/data';
import { store as coreStore } from '@wordpress/core-data';
import { driver } from 'driver.js';
import { __ } from '@wordpress/i18n';

import '../styles/onboarding.scss';

if ( window.vrts_admin_vars.onboarding ) {
	const isHighlight = window.vrts_admin_vars.onboarding.steps.length === 1;
	const onboarding = driver( {
		overlayColor: 'rgba(44, 51, 56, 0.35)',
		stageRadius: 0,
		stagePadding: 0,
		popoverOffset: 20,
		allowClose: false,
		showProgress: ! isHighlight,
		popoverClass: isHighlight
			? 'vrts-onboarding-nonblocking'
			: 'vrts-onboarding',
		disableActiveInteraction: false,
		progressText: __(
			'{{current}} of {{total}}',
			'visual-regression-tests'
		),
		prevBtnText: __( 'Previous', 'visual-regression-tests' ),
		nextBtnText: __( 'Next', 'visual-regression-tests' ),
		doneBtnText: __( 'Got it!', 'visual-regression-tests' ),
		onPopoverRender: ( popover, { config, state } ) => {
			const steps = config.steps;
			const hasNextStep = steps[ state.activeIndex + 1 ];

			config.stagePadding =
				window.vrts_admin_vars.onboarding.steps[ state.activeIndex ]
					.padding || 0;

			popover.previousButton.classList.add(
				'button',
				'button-secondary',
				'button-large'
			);

			popover.nextButton.classList.add(
				'button',
				'button-primary',
				'button-large'
			);

			if ( ! hasNextStep ) {
				popover.nextButton.classList.add(
					'driver-popover-success-btn'
				);
			}

			if ( isHighlight ) {
				popover.previousButton.setAttribute( 'hidden', true );
			}
		},
		onNextClick: ( element, step, { config, state } ) => {
			const steps = config.steps;
			const hasNextStep = steps[ state.activeIndex + 1 ];

			if ( ! hasNextStep ) {
				saveOnboarding();
				onboarding.destroy();
			} else {
				onboarding.moveNext();
			}
		},
		onCloseClick: () => {
			saveOnboarding();
			onboarding.destroy();
		},
		steps: window.vrts_admin_vars.onboarding.steps.map( ( step ) => {
			return {
				element: step.element,
				popover: {
					title: step.title,
					description: step.description,
					side: step.side || 'left',
					align: step.align || 'start',
				},
			};
		} ),
	} );

	onboarding.drive();
}

async function saveOnboarding() {
	const {
		currentUserId,
		onboarding: { id: onboardingId },
	} = window.vrts_admin_vars;

	const userData = await resolveSelect( coreStore ).getEntityRecord(
		'root',
		'user',
		currentUserId
	);

	const onboarding = userData.vrts_onboarding || {};
	const completed = onboarding.completed || [];

	return dispatch( coreStore ).saveEntityRecord( 'root', 'user', {
		id: currentUserId,
		vrts_onboarding: {
			...onboarding,
			completed: [ ...completed, onboardingId ].filter(
				( value, index, self ) => self.indexOf( value ) === index
			),
		},
	} );
}
