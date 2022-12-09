<div class="vrts-notice notice notice-error" data-view="<?php echo esc_attr( $data['view'] ); ?>">
	<?php wp_nonce_field( 'vrts_admin_notice_nonce' ); ?>
	<h3><?php esc_html_e( 'License couldn\'t be added.', 'visual-regression-tests' ); ?></h3>
	<p><?php esc_html_e( 'License couldn\'t be added. Please check again if you added the correct license.', 'visual-regression-tests' ); ?></p>
</div>
