<div class="vrts-notice notice notice-error" data-view="<?php echo esc_attr( $data['view'] ); ?>">
	<?php wp_nonce_field( 'vrts_admin_notice_nonce' ); ?>
	<h3><?php esc_html_e( 'Test couldn’t be started', 'visual-regression-tests' ); ?></h3>
	<p><?php esc_html_e( 'A Test is already in progress. Please wait for the current Test to finish, then try again.', 'visual-regression-tests' ); ?></p>
</div>
