<div class="vrts-notice notice notice-info is-dismissible" data-view="<?php echo esc_attr( $data['view'] ); ?>">
	<h3><?php esc_html_e( 'Manual testing has started', 'visual-regression-tests' ); ?></h3>
	<?php if ( $data['post_id'] ) { ?>
		<p><?php printf(
			/* translators: %s: the title of the page. */
			esc_html__( 'A manual test has been started for the following page: %s.', 'visual-regression-tests' ),
			esc_html( $data['page_title'] )
		); ?></p>
	<?php } else { ?>
		<p><?php esc_html_e( 'Manual tests have started for all running tests.', 'visual-regression-tests' ); ?></p>
	<?php } ?>
	<p><?php esc_html_e( 'If the manual testing detects any visual differences, we will notify you via e-mail alerts.', 'visual-regression-tests' ); ?></p>
</div>
