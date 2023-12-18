<div class="vrts-notice notice notice-info" data-view="<?php echo esc_attr( $data['view'] ); ?>">
	<?php wp_nonce_field( 'vrts_admin_notice_nonce' ); ?>
	<h3><?php esc_html_e( 'Testing has started', 'visual-regression-tests' ); ?></h3>
	<p>
	<?php esc_html_e( 'If the testing detects any visual differences, we will notify you via e-mail alerts.', 'visual-regression-tests' ); ?>
	</p>
</div>
