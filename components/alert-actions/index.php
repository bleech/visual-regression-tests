<vrts-alert-actions class="vrts-alert-actions">
	<?php if ( ! empty( $data['test_settings']['test_id'] ) ) : ?>
		<button type="button" class="vrts-alert-actions__action" title="<?php esc_attr_e( 'Test settings', 'visual-regression-tests' ); ?>" data-vrts-test-id="<?php echo esc_attr( $data['test_settings']['test_id'] ); ?>" data-vrts-test-settings="<?php echo esc_attr( wp_json_encode( $data['test_settings']['settings'], JSON_HEX_AMP ) ); ?>" data-a11y-dialog-show="vrts-modal-test-settings">
			<?php vrts()->icon( 'settings' ); ?>
			<span class="vrts-alert-actions__label"><?php esc_html_e( 'Test settings', 'visual-regression-tests' ); ?></span>
		</button>
	<?php endif; ?>
	<?php /* Kept without UI so alerts are still marked as read on view. */ ?>
	<button type="button" hidden data-vrts-loading="false" data-vrts-action-state="<?php echo esc_attr( $data['alert']->alert_state ? 'secondary' : 'primary' ); ?>" data-vrts-alert-id="<?php echo esc_attr( $data['alert']->id ); ?>" data-vrts-alert-action="read-status"></button>
</vrts-alert-actions>
