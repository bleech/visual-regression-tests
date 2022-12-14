<div class="vrts-notice notice notice-error" data-view="<?php echo esc_attr( $data['view'] ); ?>">
	<?php wp_nonce_field( 'vrts_admin_notice_nonce' ); ?>
	<h3><?php esc_html_e( 'Invalid License Key', 'visual-regression-tests' ); ?></h3>
	<p>
		<?php
		printf(
			/* translators: %1$s, %2$s: link wrapper. */
			esc_html__( 'We couldn\'t verify the license key that you entered. Please check the input for typos and try entering it again. A license key can only be used on one website at a time. In case you\'re using the license key on another website already, you need to remove it there first. Please don\'t hesitate to %1$scontact us%2$s for support.', 'visual-regression-tests' ),
			'<a href="mailto:products@bleech.de" target="_blank">',
			'</a>'
		);
		?>
	</p>
</div>
