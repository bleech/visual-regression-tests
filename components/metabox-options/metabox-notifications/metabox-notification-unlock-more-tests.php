<div class="vrts-metabox-notice vrts-metabox-notice-is-info">
	<p><strong><?php esc_html_e( 'Unlock more tests', 'visual-regression-tests' ); ?></strong></p>
	<p>
		<?php
		printf(
			'%s %s',
			sprintf(
				/* translators: %1$s, %2$s: number of tests. */
				esc_html__( 'Good work! You have added %1$s of %2$s available tests.', 'visual-regression-tests' ),
				intval( $data['total_tests'] ) - intval( $data['remaining_tests'] ),
				intval( $data['total_tests'] )
			),
			sprintf(
				/* translators: %1$s, %2$s: link wrapper. */
				esc_html__( 'Upgrade %1$shere%2$s to add more tests to your website!', 'visual-regression-tests' ),
				'<a href="' . esc_url( admin_url( 'admin.php?page=vrts-upgrade' ) ) . '" target="_blank">',
				'</a>'
			)
		)
		?>
	</p>
</div>
