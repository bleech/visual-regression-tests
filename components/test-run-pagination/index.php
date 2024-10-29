<?php

use Vrts\Core\Utilities\Url_Helpers;

// don't show pagination if there is no previous or next alert.
if ( 0 === $data['pagination']['total'] ) {
	return;
}

?>
<vrts-test-run-pagination class="vrts-test-run-pagination">
	<span class="screen-reader-text"><?php esc_html_e( 'Current Page', 'visual-regression-tests' ); ?></span>
	<span class="vrts-test-run-pagination__text">
		<?php
		if ( $data['is_receipt'] ) {
			esc_html_e( 'Test Receipt', 'visual-regression-tests' );
		} else {
			printf(
				/* translators: %d: pages. */
				esc_html_x( 'Alert %1$d of %2$d', 'e.g. Alert 1 of 2', 'visual-regression-tests' ),
				esc_html( $data['pagination']['current'] ),
				esc_html( $data['pagination']['total'] )
			);
		}
		?>
	</span>
	<a data-vrts-pagination="prev" data-vrts-alert-id="<?php echo esc_attr( $data['pagination']['prev_alert_id'] ); ?>" class="button <?php echo ( 0 === $data['pagination']['prev_alert_id'] ) ? 'button-disabled' : ''; ?>"
		<?php echo ( 0 !== $data['pagination']['prev_alert_id'] ) ? 'href="' . esc_url( Url_Helpers::get_alert_page( $data['pagination']['prev_alert_id'], $data['run']->id ) ) . '"' : ''; ?>>
		<span class="screen-reader-text"><?php esc_html_e( 'Previous alert', 'visual-regression-tests' ); ?></span>
		<span aria-hidden="true">‹</span>
	</a>
	<a data-vrts-pagination="next" data-vrts-alert-id="<?php echo esc_attr( $data['pagination']['next_alert_id'] ); ?>" class="button <?php echo ( 0 === $data['pagination']['next_alert_id'] ) ? 'button-disabled' : ''; ?>"
		<?php echo ( 0 !== $data['pagination']['next_alert_id'] ) ? 'href="' . esc_url( Url_Helpers::get_alert_page( $data['pagination']['next_alert_id'], $data['run']->id ) ) . '"' : ''; ?>>
		<span class="screen-reader-text"><?php esc_html_e( 'Next alert', 'visual-regression-tests' ); ?></span>
		<span aria-hidden="true">›</span>
	</a>
</vrts-test-run-pagination>

