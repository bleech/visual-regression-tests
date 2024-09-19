<?php

// don't show pagination if there is no previous or next alert
if ( 0 === $data['prev_alert_id'] && 0 === $data['next_alert_id'] ) {
	return;
}

?>
<vrts-test-run-pagination class="vrts-test-run-pagination">
	<span class="screen-reader-text"><?php esc_html_e( 'Current Page', 'visual-regression-tests' ); ?></span>
	<span class="vrts-test-run-pagination__text">
		<?php
		printf(
			/* translators: %d: pages. */
			esc_html_x( 'Alert %1$d of %2$d', 'e.g. Alert 1 of 2', 'visual-regression-tests' ),
			esc_html( $data['current'] ),
			esc_html( $data['total'] )
		);
		?>
	</span>
	<a data-alert-id="<?php echo esc_attr( $data['prev_alert_id'] ); ?>" class="button <?php echo ( 0 === $data['prev_alert_id'] ) ? 'button-disabled' : ''; ?>"
		<?php echo ( 0 !== $data['prev_alert_id'] ) ? 'href="' . esc_url( $data['prev_link'] ) . '"' : ''; ?>>
		<span class="screen-reader-text"><?php esc_html_e( 'Previous alert', 'visual-regression-tests' ); ?></span>
		<span aria-hidden="true">‹</span>
	</a>
	<a data-alert-id="<?php echo esc_attr( $data['next_alert_id'] ); ?>" class="button <?php echo ( 0 === $data['next_alert_id'] ) ? 'button-disabled' : ''; ?>"
		<?php echo ( 0 !== $data['next_alert_id'] ) ? 'href="' . esc_url( $data['next_link'] ) . '"' : ''; ?>>
		<span class="screen-reader-text"><?php esc_html_e( 'Next alert', 'visual-regression-tests' ); ?></span>
		<span aria-hidden="true">›</span>
	</a>
</vrts-test-run-pagination>

