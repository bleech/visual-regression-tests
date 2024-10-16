<vrts-test-run-page class="vrts-test-run-page">
	<div class="vrts-test-run-page__sidebar">
		<?php vrts()->component( 'test-run-alerts', $data ); ?>
	</div>
	<div class="vrts-test-run-page__content" data-vrts-fullscreen="false">
		<div class="vrts-test-run-page__content-heading">
			<?php vrts()->component( 'test-run-info', $data['run'] ); ?>
			<?php vrts()->component( 'test-run-pagination', $data['pagination'] ); ?>
		</div>
		<?php if ( $data['alerts'] ) : ?>
			<?php
			vrts()->component( 'comparisons', [
				'alert' => $data['alert'],
				'test_settings' => $data['test_settings'],
			] );
			?>
			<div class="vrts-test-run-page__content-navigation-info">
				<?php esc_html_e( 'Navigate with arrow keys', 'visual-regression-tests' ); ?>
				<?php vrts()->icon( 'arrow-up' ); ?>
				<?php vrts()->icon( 'arrow-down' ); ?>
			</div>
		<?php else : ?>
			<?php vrts()->component( 'test-run-empty' ); ?>
		<?php endif; ?>
	</div>
</vrts-test-run-page>
