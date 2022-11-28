<div class="vrts-notice notice notice-success" data-view="<?php echo esc_attr( $data['view'] ); ?>">
	<?php wp_nonce_field( 'vrts_admin_notice_nonce' ); ?>
	<h3><?php esc_html_e( 'Test added successfully', 'visual-regression-tests' ); ?></h3>
	<p>
	<?php
	printf(
		/* translators: %s: the title of the page. */
		esc_html__( 'Tests will be run for the following page: %s', 'visual-regression-tests' ),
		esc_html( $data['page_title'] )
	);
	?>
	</p>
</div>
