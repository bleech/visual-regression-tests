<div class="vrts-notice notice notice-success" data-view="<?php echo esc_attr( $data['view'] ); ?>">
	<?php wp_nonce_field( 'vrts_admin_notice_nonce' ); ?>
	<h3><?php esc_html_e( 'Run deleted', 'visual-regression-tests' ); ?></h3>
	<p><?php esc_html_e( 'The run and its detected changes have been deleted.', 'visual-regression-tests' ); ?></p>
</div>
