<div class="vrts-notice notice notice-error" data-view="<?php echo esc_attr( $data['view'] ); ?>">
	<?php wp_nonce_field( 'vrts_admin_notice_nonce' ); ?>
	<h3><?php esc_html_e( 'Run could not be deleted', 'visual-regression-tests' ); ?></h3>
	<p><?php esc_html_e( 'Only finished runs can be deleted. Please try again.', 'visual-regression-tests' ); ?></p>
</div>
