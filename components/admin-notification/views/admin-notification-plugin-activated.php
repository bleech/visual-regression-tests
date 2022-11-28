<div class="notice updated is-dismissible" data-view="<?php echo esc_attr( $data['view'] ); ?>">
	<p>
		<strong><?php esc_html_e( 'VRTs Plugin successfully activated!', 'visual-regression-tests' ); ?></strong>
		<?php
			printf(
				/* translators: %1$s, %2$s and %3$s, %4$s: link wrapper. */
				esc_html__( 'Start to %1$sconfigure tests%2$s, or check the customization options in the %3$splugin settings%4$s.', 'visual-regression-tests' ),
				'<a href="' . esc_url( admin_url( 'admin.php?page=vrts' ) ) . '">',
				'</a>',
				'<a href="' . esc_url( admin_url( 'admin.php?page=vrts-settings' ) ) . '">',
				'</a>'
			);
			?>
	</p>
</div>
