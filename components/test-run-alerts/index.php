<?php

use Vrts\Models\Alert;

$unread_alerts = Alert::get_unread_count_by_test_run_ids( $data['run']->id );
$unread_count = $unread_alerts[0]->count ?? 0;

?>
<vrts-test-run-alerts class="vrts-test-run-alerts" data-vrts-current-alert="<?php echo esc_attr( isset( $data['alert']->id ) ? $data['alert']->id : 'false' ); ?>">
	<div class="vrts-test-run-alerts__heading">
		<a href="<?php echo esc_url( admin_url( 'admin.php?page=vrts-runs' ) ); ?>" class="vrts-test-run-alerts__heading-link">
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
				$alert_link = add_query_arg( [
					'run_id' => $data['run']->id,
					'alert_id' => $alert->id,
				], admin_url( 'admin.php?page=vrts-runs' ) );

				$parsed_tested_url = wp_parse_url( get_permalink( $alert->post_id ) );
				$tested_url = $parsed_tested_url['path'];

				?>
				<a
					id="vrts-alert-<?php echo esc_attr( $alert->id ); ?>"
					href="<?php echo esc_url( $alert_link ); ?>"
					class="vrts-test-run-alerts__card"
					data-vrts-alert
					data-current="<?php echo esc_attr( $data['alert']->id === $alert->id ? 'true' : 'false' ); ?>"
					data-state="<?php echo esc_attr( intval( $alert->alert_state ) === 0 ? 'unread' : 'read' ); ?>"
					data-false-positive="<?php echo esc_attr( $alert->is_false_positive ? 'true' : 'false' ); ?>">
					<figure class="vrts-test-run-alerts__card-figure">
						<img class="vrts-test-run-alerts__card-image" src="<?php echo esc_url( $alert->comparison_screenshot_url ); ?>" alt="<?php esc_attr_e( 'Comparison Screenshot', 'visual-regression-tests' ); ?>">
						<span class="vrts-test-run-alerts__card-flag"><?php vrts()->icon( 'flag' ); ?></span>
					</figure>
					<span class="vrts-test-run-alerts__card-title">
						<span class="vrts-test-run-alerts__card-title-inner"><?php echo get_the_title( $alert->post_id ); ?></span>
					</span>
					<span class="vrts-test-run-alerts__card-path"><?php echo esc_html( $tested_url ); ?></span>
				</a>
			<?php endforeach; ?>
			<script>
				const urlParams = new URLSearchParams( window.location.search );
				const currentAlertId = urlParams.get( 'alert_id' );

				if ( currentAlertId ) {
					const $sidebar = document.querySelector(
						'.vrts-test-run-page__sidebar'
					);
					const $alert = document.getElementById(
						`vrts-alert-${ currentAlertId }`
					);

					if ( $alert ) {
						$sidebar.scrollTo( {
							left: 0,
							top: $alert.offsetTop - 100,
						} );
					}
				}
			</script>
		</div>
	<?php endif; ?>
</vrts-test-run-alerts>
