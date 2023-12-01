<?php
if ( ! $data['is_connected'] ) {
	include_once dirname( __FILE__ ) . '/metabox-notifications/metabox-notification-connection-failed.php';
	return;
}
?>

<?php wp_nonce_field( $data['nonce'], $data['nonce'] ); ?>
<p>
	<?php if ( 0 === intval( $data['remaining_tests'] ) && ! $data['run_tests_checked'] ) { ?>
		<input class="widefat" type="checkbox" id="<?php echo esc_attr( $data['field_test_status_key'] ); ?>" value="0" disabled />
	<?php } else { ?>
		<input class="widefat" type="checkbox" name="<?php echo esc_attr( $data['field_test_status_key'] ); ?>" id="<?php echo esc_attr( $data['field_test_status_key'] ); ?>" <?php checked( $data['run_tests_checked'] ); ?> value="1" />
	<?php } ?>
	<label for="<?php echo esc_attr( $data['field_test_status_key'] ); ?>"><strong><?php esc_html_e( 'Run tests', 'visual-regression-tests' ); ?></strong></label>
	<br />
	<span class="howto howto-run-tests"><?php esc_html_e( 'Activate tests to get alerted about visual differences in comparison to the snapshot.', 'visual-regression-tests' ); ?></span>
</p>

<?php

// Notification: New Test added.
if ( true === $data['is_new_test'] ) {
	include_once dirname( __FILE__ ) . '/metabox-notifications/metabox-notification-new-test-added.php';
} elseif ( 1 === intval( $data['remaining_tests'] ) ) {
	// Notification: Unlock more tests.
	include_once dirname( __FILE__ ) . '/metabox-notifications/metabox-notification-unlock-more-tests.php';
} elseif ( 0 === intval( $data['remaining_tests'] ) ) {
	// Notification: Unlock more tests.
	include_once dirname( __FILE__ ) . '/metabox-notifications/metabox-notification-upgrade-required.php';
}
?>

<?php
// Display details only when "Run Tests" checkbox is active.
if ( $data['run_tests_checked'] ) {
	$status_class_name = ( true === $data['has_post_alert'] ) || (bool) ! $data['test_status'] ? 'testing-status--paused' : 'testing-status--running';
	?>
	<div class="testing-status-wrapper">
		<p class="testing-status">
			<strong><?php esc_html_e( 'Status', 'visual-regression-tests' ); ?></strong>
			<span class="<?php echo esc_attr( $status_class_name ); ?>">
				<strong>
					<?php
					if ( true === $data['has_post_alert'] ) {
						esc_html_e( 'Paused', 'visual-regression-tests' );
					} elseif ( ! $data['test_status'] ) {
						esc_html_e( 'Disabled', 'visual-regression-tests' );
					} else {
						esc_html_e( 'Running', 'visual-regression-tests' );
					}
					?>
				</strong>
			</span>
		</p>
		<p class="howto">
			<?php echo wp_kses( $data['testing_status_instructions'], [ 'a' => [ 'href' => [] ] ] ); ?>
		</p>
	</div>

	<p class="figure-title"><strong><?php esc_html_e( 'Snapshot', 'visual-regression-tests' ); ?></strong></p>
	<figure class="figure">
		<?php if ( $data['base_screenshot_url'] ) { ?>
			<a class="figure-link" href="<?php echo esc_url( $data['base_screenshot_url'] ); ?>" target="_blank" rel="noreferrer" title="<?php esc_html_e( 'View full snapshot image in new tab', 'visual-regression-tests' ); ?>">
				<img class="figure-image" src="<?php echo esc_url( $data['base_screenshot_url'] ); ?>" loading="lazy" alt="<?php esc_html_e( 'Snapshot', 'visual-regression-tests' ); ?>" />
			</a>
		<?php } else { ?>
			<img class="figure-image" src="<?php echo esc_attr( $data['placeholder_image_data_url'] ); ?>" alt="<?php esc_html_e( 'Snapshot', 'visual-regression-tests' ); ?>" />
		<?php } ?>
		<figcaption class="howto">
			<?php if ( $data['base_screenshot_date'] && $data['base_screenshot_url'] ) { ?>
				<p><?php esc_html_e( 'Snapshot created on', 'visual-regression-tests' ); ?> <?php echo esc_html( $data['base_screenshot_date'] ); ?></p>
			<?php } else { ?>
				<p><?php esc_html_e( 'Snapshot: in progress', 'visual-regression-tests' ); ?></p>
			<?php } ?>
			<p><?php esc_html_e( 'Snapshot gets auto-generated upon publishing or updating the page.', 'visual-regression-tests' ); ?></p>
		</figcaption>
	</figure>

	<div class="settings">
		<input name="test_id" type="hidden" value="<?php echo esc_html( $data['test_settings']['test_id'] ); ?>"/>
		<p class="settings-title"><strong><?php esc_html_e( 'Settings', 'visual-regression-tests' ); ?></strong></p>
		<label class="howto">
		<?php
		esc_html_e( 'Exclude elements on this page: ', 'visual-regression-tests' );
		printf(
			/* translators: %1$s, %2$s: link wrapper. */
			esc_html__( 'Add %1$sCSS selectors%2$s (as comma separated list) to exclude elements from VRTs when a new snapshot gets created.', 'visual-regression-tests' ),
			'<a href="https://developer.mozilla.org/en-US/docs/Web/CSS/CSS_Selectors" target="_blank">',
			'</a>'
		);
		?>
		</label>
		<textarea name="hide_css_selectors" placeholder="<?php esc_html_e( 'e.g.: .lottie, #ads', 'visual-regression-tests' ); ?>" rows="4"><?php echo esc_html( $data['test_settings']['hide_css_selectors'] ); ?></textarea>
	</div>
<?php }//end if
?>
