<div class="vrts-notice notice notice-info" data-view="<?php echo esc_attr( $data['view'] ); ?>">
	<?php wp_nonce_field( 'vrts_admin_notice_nonce' ); ?>
	<h3><?php esc_html_e( 'Testing has started 🚀', 'visual-regression-tests' ); ?></h3>
	<p><?php esc_html_e( 'New screenshots are being taken and compared with the previous version.', 'visual-regression-tests' ); ?></p><br>
	<p><?php esc_html_e( 'This may take a moment. You’ll receive an email if any visual changes are detected.', 'visual-regression-tests' ); ?></p>
</div>
