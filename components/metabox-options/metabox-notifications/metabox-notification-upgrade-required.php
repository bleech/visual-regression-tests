<div class="vrts-metabox-notice vrts-metabox-notice-is-error">
	<p><strong><?php esc_html_e( 'Ready for an Upgrade?', 'visual-regression-tests' ); ?></strong></p>
	<p>
		<?php
		printf(
			'%1$s <a href="%2$s" target="_blank" title="%3$s">%3$s</a>',
			esc_html__( 'Looks like you need a bigger plan to add more tests.', 'visual-regression-tests' ),
			esc_url( admin_url( 'admin.php?page=vrts-upgrade' ) ),
			esc_html__( 'Upgrade here!', 'visual-regression-tests' )
		);
		?>
	</p>
</div>
