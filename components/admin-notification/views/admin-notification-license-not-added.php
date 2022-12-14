<div class="vrts-notice notice notice-error" data-view="<?php echo esc_attr( $data['view'] ); ?>">
	<?php wp_nonce_field( 'vrts_admin_notice_nonce' ); ?>
	<h3><?php esc_html_e( 'Invalid License Key', 'visual-regression-tests' ); ?></h3>
	<p>
		<?php
		printf(
			/* translators: %1$s, %2$s: link wrapper. */
			esc_html__( 'The entered license key is no longer valid or not using the correct form. Please make sure to enter it in the format XXXXXXXX-XXXXXXXX-XXXXXXXX-XXXXXXXX. In case you are using the license key on a different website, please go into the settings page of this plugin on that page and deactivate the licence key. %1$sGet in touch%2$s to get further support.', 'visual-regression-tests' ),
			'<a href="mailto:products@bleech.de" target="_blank">',
			'</a>'
		);
		?>
	</p>
</div>
