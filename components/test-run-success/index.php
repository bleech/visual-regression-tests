<vrts-test-run-success class="vrts-test-run-success postbox">
	<div class="vrts-test-run-success__inner">
		<div class="vrts-test-run-success__lottie-player" vrts-lottie-player></div>
		<div class="vrts-test-run-success__content">
			<?php if ( $data['is_receipt'] ) : ?>
				<p><?php esc_html_e( 'Congrats – you have seen all the changes in this Run!', 'visual-regression-tests' ); ?></p>
			<?php else : ?>
				<p><?php esc_html_e( 'Smooth sailing – no changes found!', 'visual-regression-tests' ); ?></p>
			<?php endif; ?>
			<p><?php esc_html_e( "You're good to go.", 'visual-regression-tests' ); ?></p>
		</div>
	</div>
	<span class="vrts-gradient-loader"></span>
</vrts-test-run-success>
