<vrts-test-run-page class="vrts-test-run-page">
	<div class="vrts-test-run-page__sidebar">
		<?php vrts()->component( 'test-run-alerts', $data ); ?>
	</div>
	<div class="vrts-test-run-page__content" data-vrts-fullscreen="false">
		<div class="vrts-test-run-page__content-heading">
			<?php vrts()->component( 'test-run-info', $data['run'] ); ?>
			<?php
			vrts()->component( 'test-run-pagination', [
				'run' => $data['run'],
				'pagination' => $data['pagination'],
				'is_receipt' => $data['is_receipt'],
			] );
			?>
		</div>
		<?php if ( $data['alerts'] && ! $data['is_receipt'] ) : ?>
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
			<?php
			vrts()->component( 'test-run-success', [
				'is_receipt' => $data['is_receipt'],
			] );
			?>
		<?php endif; ?>
	</div>
</vrts-test-run-page>
