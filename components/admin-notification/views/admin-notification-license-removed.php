<div class="vrts-notice notice notice-error" data-view="<?php echo esc_attr( $data['view'] ); ?>">
	<?php wp_nonce_field( 'vrts_admin_notice_nonce' ); ?>
	<h3><?php esc_html_e( 'License removed.', 'visual-regression-tests' ); ?></h3>
	<p><?php esc_html_e( 'License has been removed. You\'ll continue to use the free tier.', 'visual-regression-tests' ); ?></p>
</div>
