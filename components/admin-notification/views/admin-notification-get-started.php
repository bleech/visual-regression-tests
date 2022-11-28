<div class="vrts-notice notice notice-info is-dismissible" data-view="<?php echo esc_attr( $data['view'] ); ?>">
	<?php wp_nonce_field( 'vrts_admin_notice_nonce' ); ?>
	<h3><?php esc_html_e( 'Let’s get started!', 'visual-regression-tests' ); ?></h3>
	<p><?php esc_html_e( 'Click “Add New“ to create a Visual Regression Test for your Website and find issues before others do.', 'visual-regression-tests' ); ?></p>
</div>
