<div class="vrts-notice notice notice-info" data-view="<?php echo esc_attr( $data['view'] ); ?>">
	<?php wp_nonce_field( 'vrts_admin_notice_nonce' ); ?>
	<h3><?php esc_html_e( 'Starting tests has failed', 'visual-regression-tests' ); ?></h3>
	<p><?php esc_html_e( 'There is most likely another test running.', 'visual-regression-tests' ); ?></p>
</div>
