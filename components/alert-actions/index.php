<vrts-alert-actions class="vrts-alert-actions">
	<button type="button" data-vrts-dropdown-open class="vrts-alert-actions__trigger" aria-expanded="false" aria-controls="vrts-alert-actions-dropdown">
		<?php vrts()->icon( 'more-horizontal' ); ?>
	</button>
	<div id="vrts-alert-actions-dropdown" class="vrts-alert-actions__dropdown" aria-hidden="true">
		<button data-vrts-loading="false" data-vrts-action-state="<?php echo esc_attr( $data->is_false_positive ? 'secondary' : 'primary' ); ?>" data-vrts-alert-id="<?php echo esc_attr( $data->id ); ?>" data-vrts-alert-action="false-positive" class="vrts-alert-actions__dropdown-action vrts-action-button">
			<span class="vrts-action-button__icons">
				<span class="vrts-action-button__icon"><?php vrts()->icon( 'flag' ); ?></span>
				<span class="vrts-action-button__spinner"><?php vrts()->icon( 'spinner' ); ?></span>
			</span>
			<span class="vrts-action-button__info" data-vrts-action-state-primary><?php esc_html_e( 'Flag as false positive', 'visual-regression-tests' ); ?></span>
			<span class="vrts-action-button__info" data-vrts-action-state-secondary><?php esc_html_e( 'Unflag as false positive', 'visual-regression-tests' ); ?></span>
		</button>
		<button data-vrts-loading="false" data-vrts-action-state="<?php echo esc_attr( $data->alert_state ? 'secondary' : 'primary' ); ?>" data-vrts-alert-id="<?php echo esc_attr( $data->id ); ?>" data-vrts-alert-action="read-status" class="vrts-alert-actions__dropdown-action vrts-action-button">
			<span class="vrts-action-button__icons">
				<span class="vrts-action-button__icon" data-vrts-action-state-secondary><?php vrts()->icon( 'email-unread' ); ?></span>
				<span class="vrts-action-button__icon" data-vrts-action-state-primary><?php vrts()->icon( 'email-read' ); ?></span>
				<span class="vrts-action-button__spinner"><?php vrts()->icon( 'spinner' ); ?></span>
			</span>
			<span class="vrts-action-button__info" data-vrts-action-state-primary><?php esc_html_e( 'Mark as read', 'visual-regression-tests' ); ?></span>
			<span class="vrts-action-button__info" data-vrts-action-state-secondary><?php esc_html_e( 'Mark as unread', 'visual-regression-tests' ); ?></span>
		</button>
		<button type="button" class="vrts-alert-actions__dropdown-action">
			<?php vrts()->icon( 'hidden' ); ?>
			<?php esc_html_e( 'Hide elements', 'visual-regression-tests' ); ?>
		</button>
	</div>
</vrts-alert-actions>
