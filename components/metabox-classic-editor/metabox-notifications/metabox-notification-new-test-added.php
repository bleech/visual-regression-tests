<div class="vrts-metabox-notice vrts-metabox-notice-is-success">
	<p><strong><?php esc_html_e( 'You have added a new test', 'visual-regression-tests' ); ?></strong></p>
	<p>
	<?php
		printf(
			/* translators: %s: page title. */
			esc_html__( 'The Visual Regression Test for the page %s has been added!', 'visual-regression-tests' ),
			'<strong>' . esc_html( get_the_title( $data['post_id'] ) ) . '</strong>'
		);
		?>
	</p>
</div>
