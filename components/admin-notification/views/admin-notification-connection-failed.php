<div class="vrts-notice notice notice-error" data-view="<?php echo esc_attr( $data['view'] ); ?>">
	<h3><?php esc_html_e( 'Connection failed', 'visual-regression-tests' ); ?></h3>
	<p><?php esc_html_e( 'Something went wrong while connecting to the external service. If you just installed the plugin, refresh this page in a bit.', 'visual-regression-tests' ); ?></p>
	<p><?php esc_html_e( 'The website must be publicly accessible in order to set up and run the tests. Password protection or any kind of firewall might prevent the plugin from working correctly.', 'visual-regression-tests' ); ?></p>
	<form id="form-retry-connection" method="post">
		<?php wp_nonce_field( 'vrts_retry_connection_nonce' ); ?>
		<?php submit_button( esc_attr__( 'Retry connection', 'visual-regression-tests' ), 'admin-notice-button', 'submit_retry_connection', false ); ?>
	</form>
</div>
