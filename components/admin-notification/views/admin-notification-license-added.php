<div class="vrts-notice notice notice-success" data-view="<?php echo esc_attr( $data['view'] ); ?>">
	<?php wp_nonce_field( 'vrts_admin_notice_nonce' ); ?>
	<h3><?php esc_html_e( 'Thank you for Upgrading!', 'visual-regression-tests' ); ?></h3>
	<p><?php esc_html_e( 'You have successfully upgraded your plan.', 'visual-regression-tests' ); ?></p>
</div>
