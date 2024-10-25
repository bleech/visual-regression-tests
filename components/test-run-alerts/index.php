<?php

use Vrts\Models\Alert;
use Vrts\Core\Utilities\Image_Helpers;
use Vrts\Core\Utilities\Url_Helpers;

$unread_alerts = Alert::get_unread_count_by_test_run_ids( $data['run']->id );
$unread_count = $unread_alerts[0]->count ?? 0;
$unred_runs_count = Alert::get_total_items_grouped_by_test_run();
$current_alert_id = isset( $data['alert']->id ) ? $data['alert']->id : 0;

?>
<vrts-test-run-alerts
	class="vrts-test-run-alerts"
	data-vrts-current-alert="<?php echo esc_attr( $current_alert_id ? $current_alert_id : 'false' ); ?>"
	data-vrts-unread-runs="<?php echo esc_attr( $unred_runs_count ); ?>">
	<div class="vrts-test-run-alerts__heading">
		<a href="<?php echo esc_url( Url_Helpers::get_page_url( 'runs' ) ); ?>" class="vrts-test-run-alerts__heading-link">
			<?php vrts()->icon( 'chevron-left' ); ?>
			<?php esc_html_e( 'All Runs', 'visual-regression-tests' ); ?>
		</a>
		<?php if ( $data['alerts'] ) : ?>
			<button data-vrts-loading="false" data-vrts-action-state="<?php echo esc_attr( $unread_count > 0 ? 'primary' : 'secondary' ); ?>" data-vrts-test-run-id="<?php echo esc_attr( $data['run']->id ); ?>" data-vrts-test-run-action="read-status" class="vrts-test-run-alerts__heading-link vrts-test-run-alerts__heading-link--button vrts-action-button">
				<span class="vrts-action-button__icons">
					<span class="vrts-action-button__icon" data-vrts-action-state-secondary><?php vrts()->icon( 'email-unread' ); ?></span>
					<span class="vrts-action-button__icon" data-vrts-action-state-primary><?php vrts()->icon( 'email-read' ); ?></span>
					<span class="vrts-action-button__spinner"><?php vrts()->icon( 'spinner' ); ?></span>
				</span>
				<span class="vrts-action-button__info" data-vrts-action-state-primary><?php esc_html_e( 'Mark all as read', 'visual-regression-tests' ); ?></span>
				<span class="vrts-action-button__info" data-vrts-action-state-secondary><?php esc_html_e( 'Mark all as unread', 'visual-regression-tests' ); ?></span>
			</button>
		<?php endif; ?>
	</div>
	<?php if ( $data['alerts'] ) : ?>
		<div class="vrts-test-run-alerts__list">
			<?php
			foreach ( $data['alerts'] as $alert ) :
				$test = null;
				foreach ( $data['tests'] as $some_test ) {
					if ( $some_test['post_id'] === $alert->post_id ) {
						$test = $some_test;
						break;
					}
				}
				$alert_link = add_query_arg( [
					'run_id' => $data['run']->id,
					'alert_id' => $alert->id,
				], Url_Helpers::get_page_url( 'runs' ) );

				$alert_permalink = '';
				$alert_post_title = '';
				if ( $test ) {
					$alert_permalink = $test['permalink'];
					$alert_post_title = $test['post_title'];
				}
				if ( ! $alert_permalink ) {
					$alert_permalink = get_permalink( $alert->post_id );
				}
				if ( ! $alert_post_title ) {
					$alert_post_title = get_the_title( $alert->post_id ) ?: 'N/A';
				}
				$alert_relative_permalink = Url_Helpers::make_relative( $alert_permalink );

				?>
				<div class="vrts-test-run-alerts__card">
					<a
						id="vrts-alert-<?php echo esc_attr( $alert->id ); ?>"
						href="<?php echo esc_url( $alert_link ); ?>"
						class="vrts-test-run-alerts__card-link"
						data-vrts-alert="<?php echo esc_attr( $alert->id ); ?>"
						data-vrts-current="<?php echo esc_attr( $current_alert_id === $alert->id ? 'true' : 'false' ); ?>"
						data-vrts-state="<?php echo esc_attr( intval( $alert->alert_state ) === 0 ? 'unread' : 'read' ); ?>"
						data-vrts-false-positive="<?php echo esc_attr( $alert->is_false_positive ? 'true' : 'false' ); ?>">
						<figure class="vrts-test-run-alerts__card-figure">
							<img class="vrts-test-run-alerts__card-image" src="<?php echo esc_url( Image_Helpers::get_screenshot_url( $alert, 'comparison', 'preview' ) ); ?>" alt="<?php esc_attr_e( 'Difference', 'visual-regression-tests' ); ?>">
							<span class="vrts-test-run-alerts__card-flag"><?php vrts()->icon( 'flag' ); ?></span>
						</figure>
						<span class="vrts-test-run-alerts__card-title">
							<span class="vrts-test-run-alerts__card-title-inner"><?php echo esc_html( $alert_post_title ); ?></span>
						</span>
					</a>
					<a href="<?php echo esc_url( get_permalink( $alert->post_id ) ); ?>" target="_blank" class="vrts-test-run-alerts__card-path"><?php echo esc_html( $alert_relative_permalink ); ?></a>
				</div>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>
	<?php vrts()->component( 'test-run-receipt', $data ); ?>
	<script>
		const urlParams = new URLSearchParams( window.location.search );
		const currentAlertId = urlParams.get( 'alert_id' );

		if ( currentAlertId ) {
			const $sidebar = document.querySelector(
				'.vrts-test-run-page__sidebar'
			);

			let $alert = document.getElementById(
				`vrts-alert-${ currentAlertId }`
			);

			let offsetTop = 0;

			while ( $alert && $alert !== $sidebar ) {
				offsetTop += $alert.offsetTop;
				$alert = $alert.offsetParent;
			}

			if ( $alert ) {
				$sidebar.scrollTo( {
					left: 0,
					top: offsetTop - 82,
				} );
			}
		}
	</script>
</vrts-test-run-alerts>
