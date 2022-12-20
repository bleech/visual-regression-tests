<div class="vrts-notice notice notice-error" data-view="<?php echo esc_attr( $data['view'] ); ?>">
	<h3><?php esc_html_e( 'Connection failed', 'visual-regression-tests' ); ?></h3>
	<p><?php esc_html_e( 'Something went wrong while trying to connect to the external service.', 'visual-regression-tests' ); ?></p>
	<form id="form-retry-connection" method="post">
		<?php wp_nonce_field( 'vrts_retry_connection_nonce' ); ?>
		<?php submit_button( esc_attr__( 'Retry connection', 'visual-regression-tests' ), 'admin-notice-button', 'submit_retry_connection', false ); ?>
	</form>
</div>
