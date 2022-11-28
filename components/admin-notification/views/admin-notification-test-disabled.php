<div class="vrts-notice notice notice-error" data-view="<?php echo esc_attr( $data['view'] ); ?>">
	<?php wp_nonce_field( 'vrts_admin_notice_nonce' ); ?>
	<h3><?php esc_html_e( 'Test disabled successfully', 'visual-regression-tests' ); ?></h3>
	<p>
		<?php
		printf(
			/* translators: %s: the title of the page. */
			esc_html__( 'No more tests will be run for the following page: %s', 'visual-regression-tests' ),
			esc_html( $data['page_title'] )
		);
		?>
	</p>
	<form id="form-undo-test" method="post">
		<input type="hidden" name="post_id" value="<?php echo esc_attr( $data['post_id'] ); ?>">
		<?php wp_nonce_field( 'vrts_page_tests_nonce' ); ?>
		<?php submit_button( esc_attr__( 'Undo', 'visual-regression-tests' ), 'admin-notice-button', 'submit_add_new_test', false ); ?>
	</form>
</div>
