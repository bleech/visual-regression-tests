<div class="vrts-test-run-page">
	<div class="vrts-test-run-page__sidebar">
		<?php vrts()->component( 'test-run-alerts', $data ); ?>
	</div>
	<div class="vrts-test-run-page__content">
		<div class="vrts-test-run-page__content-heading">
			<?php vrts()->component( 'test-run-info', $data['run'] ); ?>
			<?php vrts()->component( 'test-run-pagination', $data['pagination'] ); ?>
		</div>
		<?php if ( $data['alerts'] ) : ?>
			<?php vrts()->component( 'comparisons', [
				'alert' => $data['alert'],
				'test_settings' => $data['test_settings'],
				] ); ?>
		<?php else : ?>
			<?php vrts()->component( 'test-run-empty' ); ?>
		<?php endif; ?>
	</div>
</div>
