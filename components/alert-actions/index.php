<?php

use Vrts\Core\Utilities\Url_Helpers;

?>
<vrts-alert-actions class="vrts-alert-actions">
	<button type="button" data-vrts-dropdown-open class="vrts-alert-actions__trigger" aria-expanded="false" aria-controls="vrts-alert-actions-dropdown">
		<?php vrts()->icon( 'more-horizontal' ); ?>
	</button>
	<div id="vrts-alert-actions-dropdown" class="vrts-alert-actions__dropdown" aria-hidden="true">
		<button data-vrts-loading="false" data-vrts-action-state="<?php echo esc_attr( $data['alert']->is_false_positive ? 'secondary' : 'primary' ); ?>" data-vrts-alert-id="<?php echo esc_attr( $data['alert']->id ); ?>" data-vrts-alert-action="false-positive" class="vrts-alert-actions__dropdown-action vrts-action-button">
			<span class="vrts-action-button__icons">
				<span class="vrts-action-button__icon" data-vrts-action-state-primary><?php vrts()->icon( 'flag-outline' ); ?></span>
				<span class="vrts-action-button__icon" data-vrts-action-state-secondary><?php vrts()->icon( 'flag' ); ?></span>
				<span class="vrts-action-button__spinner"><?php vrts()->icon( 'spinner' ); ?></span>
			</span>
			<span class="vrts-action-button__info" data-vrts-action-state-primary><?php esc_html_e( 'Flag as false positive', 'visual-regression-tests' ); ?></span>
			<span class="vrts-action-button__info" data-vrts-action-state-secondary><?php esc_html_e( 'Unflag as false positive', 'visual-regression-tests' ); ?></span>
		</button>
		<button data-vrts-loading="false" data-vrts-action-state="<?php echo esc_attr( $data['alert']->alert_state ? 'secondary' : 'primary' ); ?>" data-vrts-alert-id="<?php echo esc_attr( $data['alert']->id ); ?>" data-vrts-alert-action="read-status" class="vrts-alert-actions__dropdown-action vrts-action-button">
			<span class="vrts-action-button__icons">
				<span class="vrts-action-button__icon" data-vrts-action-state-secondary><?php vrts()->icon( 'email-unread' ); ?></span>
				<span class="vrts-action-button__icon" data-vrts-action-state-primary><?php vrts()->icon( 'email-read' ); ?></span>
				<span class="vrts-action-button__spinner"><?php vrts()->icon( 'spinner' ); ?></span>
			</span>
			<span class="vrts-action-button__info" data-vrts-action-state-primary><?php esc_html_e( 'Mark as read', 'visual-regression-tests' ); ?></span>
			<span class="vrts-action-button__info" data-vrts-action-state-secondary><?php esc_html_e( 'Mark as unread', 'visual-regression-tests' ); ?></span>
		</button>
		<button type="button" class="vrts-alert-actions__dropdown-action" data-a11y-dialog-show="vrts-modal-hide-elements">
			<?php vrts()->icon( 'hidden' ); ?>
			<?php esc_html_e( 'Hide elements', 'visual-regression-tests' ); ?>
		</button>
	</div>

	<vrts-modal id="vrts-modal-hide-elements" class="vrts-modal vrts-alert-actions__modal" aria-hidden="true">
		<div class="vrts-modal__overlay" data-a11y-dialog-hide></div>
		<div class="vrts-modal__content" role="document">
			<button type="button" class="vrts-modal__close" data-a11y-dialog-hide aria-label="<?php esc_attr_e( 'Close', 'visual-regression-tests' ); ?>"></button>
			<h2 class="vrts-modal__title">
				<?php vrts()->icon( 'hidden' ); ?>
				<?php esc_html_e( 'Hide elements', 'visual-regression-tests' ); ?>
			</h2>
			<div class="vrts-modal__content-inner">
				<form data-vrts-hide-elements-form action="<?php echo esc_url( Url_Helpers::get_page_url( 'runs' ) ); ?>" method="put">
					<input type="hidden" name="post_id" value="<?php echo esc_attr( $data['alert']->post_id ); ?>">
					<input type="hidden" name="test_id" value="<?php echo esc_attr( $data['test_settings']['test_id'] ); ?>">
					<textarea class="widefat" name="hide_css_selectors" placeholder="<?php esc_html_e( 'e.g.: .lottie, #ads', 'visual-regression-tests' ); ?>" rows="4"><?php echo esc_html( $data['test_settings']['hide_css_selectors'] ); ?></textarea>
					<p class="description"><?php esc_html_e( 'Hide elements on snapshots to exclude them from comparisons.', 'visual-regression-tests' ); ?></p>
					<div class="vrts-alert-actions__modal-action">
						<button type="submit" class="button button-primary"><?php esc_html_e( 'Save Changes', 'visual-regression-tests' ); ?></button>
						<span class="spinner"></span>
						<span class="vrts-alert-actions__modal-action-success"><?php esc_html_e( 'Saved successfully.', 'visual-regression-tests' ); ?></span>
					</div>
				</form>
			</div>
		</div>
	</vrts-modal>
</vrts-alert-actions>
