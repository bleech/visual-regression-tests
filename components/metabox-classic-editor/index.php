<?php
if ( ! $data['is_connected'] ) {
	include_once dirname( __FILE__ ) . '/metabox-notifications/metabox-notification-connection-failed.php';
	return;
}
?>

<?php wp_nonce_field( $data['nonce'], $data['nonce'] ); ?>
<p class="vrts-testing-toogle">
	<?php if ( 0 === intval( $data['remaining_tests'] ) && ! $data['run_tests_checked'] ) { ?>
		<input class="widefat" type="checkbox" id="<?php echo esc_attr( $data['field_test_status_key'] ); ?>" value="0" disabled />
	<?php } else { ?>
		<input class="widefat" type="checkbox" name="<?php echo esc_attr( $data['field_test_status_key'] ); ?>" id="<?php echo esc_attr( $data['field_test_status_key'] ); ?>" <?php checked( $data['run_tests_checked'] ); ?> value="1" />
	<?php } ?>
	<label for="<?php echo esc_attr( $data['field_test_status_key'] ); ?>"><strong><?php esc_html_e( 'Add to VRTs', 'visual-regression-tests' ); ?></strong></label>
	<span class="vrts-tooltip">
		<span class="vrts-tooltip-icon dashicons dashicons-info-outline"></span>
		<span class="vrts-tooltip-content">
			<span class="vrts-tooltip-content-inner">
				<?php
					printf(
						/* translators: %1$s, %2$s: link wrapper. */
						wp_kses_post( __( 'Add this page to your Visual Regression Tests for consistent checks to ensure no visual changes go unnoticed. Explore the %1$sTests page%2$s in the VRTs plugin for an overview of all tests and their status.', 'visual-regression-tests' ) ),
						'<a href="' . esc_url( $data['plugin_url'] ) . '">',
						'</a>'
					);
					?>
			</span>
		</span>
	</span>
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
	$test_status = $data['test_status'];
	$screenshot  = $data['screenshot'];
	?>
	<div class="vrts-testing-status-wrapper">
		<p class="vrts-testing-status">
			<span><?php esc_html_e( 'Test Status', 'visual-regression-tests' ); ?></span>
			<strong class="vrts-testing-status--<?php echo esc_attr( $test_status['class'] ); ?>"><?php echo wp_kses_post( $test_status['text'] ); ?></strong>
		</p>
		<p class="vrts-testing-status-info">
			<?php echo wp_kses_post( $test_status['instructions'] ); ?>
		</p>
	</div>

	<div class="vrts-testing-status-wrapper">
		<p class="vrts-testing-status">
			<span><?php esc_html_e( 'Snapshot', 'visual-regression-tests' ); ?></span>
			<span class="vrts-testing-status-info">
				<?php
					$text = in_array( $screenshot['status'], [ 'paused', 'waiting' ], true ) ? $screenshot['text'] : $screenshot['instructions'];
					echo wp_kses_post( $text );
				?>
			</span>
		</p>
		<figure class="figure">
			<?php echo wp_kses_post( $screenshot['screenshot'] ); ?>
		</figure>
	</div>

	<div class="settings">
		<input name="test_id" type="hidden" value="<?php echo esc_html( $data['test_settings']['test_id'] ); ?>"/>
		<label for="vrts-hide-css-selectors" class="settings-title">
			<span><?php esc_html_e( 'Hide elements from VRTs', 'visual-regression-tests' ); ?></span>
			<span class="vrts-tooltip">
				<span class="vrts-tooltip-icon dashicons dashicons-info-outline"></span>
				<span class="vrts-tooltip-content">
					<span class="vrts-tooltip-content-inner">
					<?php
					printf(
						/* translators: %1$s, %2$s: link wrapper. */
						esc_html__( 'Exclude elements on this page: Add %1$sCSS selectors%2$s (as comma separated list) to exclude elements from VRTs when a new snapshot gets created.', 'visual-regression-tests' ),
						'<a href="https://developer.mozilla.org/en-US/docs/Web/CSS/CSS_Selectors" target="_blank">',
						'</a>'
					);
					?>
					</span>
				</span>
			</span>
		</label>
		<textarea id="vrts-hide-css-selectors" name="hide_css_selectors" placeholder="<?php esc_html_e( 'e.g.: .lottie, #ads', 'visual-regression-tests' ); ?>" rows="4"><?php echo esc_html( $data['test_settings']['hide_css_selectors'] ); ?></textarea>
	</div>
<?php }//end if
?>
