<div class="vrts-notice notice notice-error" data-view="<?php echo esc_attr( $data['view'] ); ?>">
	<?php wp_nonce_field( 'vrts_admin_notice_nonce' ); ?>
	<h3><?php esc_html_e( 'Invalid License Key', 'visual-regression-tests' ); ?></h3>
	<p>
		<?php
		printf(
			/* translators: %1$s, %2$s: link wrapper. */
			esc_html__( 'We could not verify the license key you entered. Please check for typos and try again. If the issue persists, %1$scontact us%2$s for assistance. We’re here to help!', 'visual-regression-tests' ),
			'<a href="https://vrts.app/contact/" target="_blank">',
			'</a>'
		);
		?>
	</p>
</div>
